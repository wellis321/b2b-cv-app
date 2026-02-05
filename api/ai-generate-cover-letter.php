<?php
/**
 * AI Cover Letter Generation API Endpoint
 * Generates personalized cover letters based on job application and CV data
 */

define('SKIP_CANONICAL_REDIRECT', true);

ob_start();

// Increase timeout for AI processing (Ollama/local models can take 1–2 minutes)
@set_time_limit(300);
@ini_set('max_execution_time', '300');

require_once __DIR__ . '/../php/helpers.php';
require_once __DIR__ . '/../php/ai-service.php';
require_once __DIR__ . '/../php/cover-letters.php';
require_once __DIR__ . '/../php/cv-data.php';
require_once __DIR__ . '/../php/job-applications.php';
require_once __DIR__ . '/../php/cv-variants.php';

header('Content-Type: application/json');

// Only allow POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    ob_end_clean();
    echo json_encode(['success' => false, 'error' => 'Method not allowed']);
    exit;
}

// Check authentication
$user = getCurrentUser();
if (!$user) {
    http_response_code(401);
    ob_end_clean();
    echo json_encode(['success' => false, 'error' => 'Unauthorized']);
    exit;
}

// Verify CSRF token
if (!verifyCsrfToken($_POST['csrf_token'] ?? '')) {
    http_response_code(403);
    ob_end_clean();
    echo json_encode(['success' => false, 'error' => 'Invalid CSRF token']);
    exit;
}

try {
    $jobApplicationId = $_POST['job_application_id'] ?? null;
    $cvVariantId = $_POST['cv_variant_id'] ?? null;
    $customInstructions = $_POST['custom_instructions'] ?? null;
    $browserAiResult = $_POST['cover_letter_text'] ?? null; // Browser AI result (already generated text)
    $forceServerAI = isset($_POST['force_server_ai']) && $_POST['force_server_ai'] === '1'; // Force server-side AI
    
    if (!$jobApplicationId) {
        throw new Exception('Job application ID is required');
    }
    
    // Verify job application belongs to user
    $jobApplication = getJobApplication($jobApplicationId, $user['id']);
    if (!$jobApplication) {
        throw new Exception('Job application not found');
    }
    
    // If browser AI result is provided, just save it (browser AI already executed client-side)
    if ($browserAiResult) {
        $coverLetterText = trim($browserAiResult);
        error_log("Browser AI cover letter text received. Length: " . strlen($coverLetterText) . " First 200 chars: " . substr($coverLetterText, 0, 200));
        
        if (empty($coverLetterText)) {
            throw new Exception('Cover letter text cannot be empty');
        }
        
        // Save or update cover letter in database
        $coverLetterResult = createCoverLetter($user['id'], $jobApplicationId, $coverLetterText);
        
        if (!$coverLetterResult['success']) {
            throw new Exception($coverLetterResult['error'] ?? 'Failed to save cover letter');
        }
        
        // Get the saved cover letter
        $coverLetter = getCoverLetterByApplication($jobApplicationId, $user['id']);
        
        if (!$coverLetter) {
            throw new Exception('Failed to retrieve saved cover letter');
        }
        
        error_log("Cover letter saved successfully. ID: " . $coverLetter['id'] . " Text length: " . strlen($coverLetter['cover_letter_text']));
        
        ob_end_clean();
        echo json_encode([
            'success' => true,
            'cover_letter_id' => $coverLetter['id'],
            'cover_letter_text' => $coverLetter['cover_letter_text'],
            'message' => 'Cover letter saved successfully'
        ]);
        exit;
    }
    
    // Load CV data
    $cvData = null;
    if ($cvVariantId) {
        // Load from variant
        $variant = getCvVariant($cvVariantId, $user['id']);
        if (!$variant) {
            throw new Exception('CV variant not found');
        }
        require_once __DIR__ . '/../php/cv-variants.php';
        $cvData = loadCvVariantData($cvVariantId);
    } else {
        // Load master CV
        $cvData = loadCvData($user['id']);
    }
    
    if (!$cvData || empty($cvData)) {
        throw new Exception('No CV data found. Please create your CV first.');
    }
    
    // Initialize AI service
    // If force_server_ai is set, temporarily override browser AI preference to use a cloud service
    $tempOverrideApplied = false;
    $originalPreference = null;
    $selectedCloudService = null;
    
    // #region agent log
    error_log(json_encode(['id'=>'log_'.time().'_force_check','timestamp'=>time()*1000,'location'=>'api/ai-generate-cover-letter.php:123','message'=>'Force server AI check','data'=>['forceServerAI'=>$forceServerAI,'userId'=>$user['id']],'sessionId'=>'debug-session','runId'=>'run1','hypothesisId'=>'B']));
    // #endregion
    
    if ($forceServerAI) {
        $tempUser = db()->fetchOne(
            "SELECT ai_service_preference, openai_api_key, anthropic_api_key, gemini_api_key, grok_api_key FROM profiles WHERE id = ?",
            [$user['id']]
        );
        if ($tempUser) {
            $originalPreference = $tempUser['ai_service_preference'];
            
            // If user has browser AI preference, we need to override it
            if ($tempUser['ai_service_preference'] === 'browser') {
                // Find first available cloud service with API key
                $cloudService = null;
                if (!empty($tempUser['openai_api_key'])) {
                    $cloudService = 'openai';
                } elseif (!empty($tempUser['anthropic_api_key'])) {
                    $cloudService = 'anthropic';
                } elseif (!empty($tempUser['gemini_api_key'])) {
                    $cloudService = 'gemini';
                } elseif (!empty($tempUser['grok_api_key'])) {
                    $cloudService = 'grok';
                }
                
                if ($cloudService) {
                    // Temporarily override to use cloud service
                    $selectedCloudService = $cloudService;
                    // #region agent log
                    error_log(json_encode(['id'=>'log_'.time().'_user_cloud','timestamp'=>time()*1000,'location'=>'api/ai-generate-cover-letter.php:145','message'=>'Found user cloud service','data'=>['cloudService'=>$cloudService,'userId'=>$user['id']],'sessionId'=>'debug-session','runId'=>'run1','hypothesisId'=>'B']));
                    // #endregion
                    db()->update('profiles', ['ai_service_preference' => $cloudService], 'id = ?', [$user['id']]);
                    $tempOverrideApplied = true;
                } else {
                    // No cloud API keys - check organization settings
                    $userOrg = getUserOrganisation($user['id']);
                    if ($userOrg && !empty($userOrg['organisation_id'])) {
                        $org = db()->fetchOne(
                            "SELECT org_ai_service_preference, org_openai_api_key, org_anthropic_api_key, org_gemini_api_key, org_grok_api_key 
                             FROM organisations 
                             WHERE id = ? AND org_ai_enabled = 1",
                            [$userOrg['organisation_id']]
                        );
                        
                        if ($org) {
                            // Check if org has cloud service preference or API keys
                            if (!empty($org['org_ai_service_preference']) && $org['org_ai_service_preference'] !== 'browser' && $org['org_ai_service_preference'] !== 'ollama') {
                                $cloudService = $org['org_ai_service_preference'];
                            } elseif (!empty($org['org_openai_api_key'])) {
                                $cloudService = 'openai';
                            } elseif (!empty($org['org_anthropic_api_key'])) {
                                $cloudService = 'anthropic';
                            } elseif (!empty($org['org_gemini_api_key'])) {
                                $cloudService = 'gemini';
                            } elseif (!empty($org['org_grok_api_key'])) {
                                $cloudService = 'grok';
                            }
                            
                            if ($cloudService) {
                                // Temporarily override to use cloud service
                                $selectedCloudService = $cloudService;
                                // #region agent log
                                error_log(json_encode(['id'=>'log_'.time().'_org_cloud','timestamp'=>time()*1000,'location'=>'api/ai-generate-cover-letter.php:170','message'=>'Found org cloud service','data'=>['cloudService'=>$cloudService,'userId'=>$user['id']],'sessionId'=>'debug-session','runId'=>'run1','hypothesisId'=>'B']));
                                // #endregion
                                db()->update('profiles', ['ai_service_preference' => $cloudService], 'id = ?', [$user['id']]);
                                $tempOverrideApplied = true;
                            }
                        }
                    }
                    
                    // If still no cloud service found, we'll let it fail with a clear error
                    if (!$cloudService) {
                        // #region agent log
                        error_log(json_encode(['id'=>'log_'.time().'_no_cloud','timestamp'=>time()*1000,'location'=>'api/ai-generate-cover-letter.php:180','message'=>'No cloud service found','data'=>['userId'=>$user['id']],'sessionId'=>'debug-session','runId'=>'run1','hypothesisId'=>'B']));
                        // #endregion
                        throw new Exception('No cloud AI service configured. Please configure a cloud AI service (OpenAI, Anthropic, Gemini, or Grok) with API keys in Settings → AI Settings.');
                    }
                }
            }
        }
    }
    
    // #region agent log
    error_log(json_encode(['id'=>'log_'.time().'_service_selected','timestamp'=>time()*1000,'location'=>'api/ai-generate-cover-letter.php:188','message'=>'Service selection complete','data'=>['tempOverrideApplied'=>$tempOverrideApplied,'selectedCloudService'=>$selectedCloudService,'originalPreference'=>$originalPreference],'sessionId'=>'debug-session','runId'=>'run1','hypothesisId'=>'B']));
    // #endregion
    
    $aiService = new AIService($user['id']);
    
    // #region agent log
    error_log(json_encode(['id'=>'log_'.time().'_entry','timestamp'=>time()*1000,'location'=>'api/ai-generate-cover-letter.php:120','message'=>'Before generateCoverLetter call','data'=>['userId'=>$user['id'],'hasCvData'=>!empty($cvData),'hasJobApp'=>!empty($jobApplication),'forceServerAI'=>$forceServerAI,'tempOverrideApplied'=>$tempOverrideApplied],'sessionId'=>'debug-session','runId'=>'run1','hypothesisId'=>'B']));
    // #endregion
    
    // Generate cover letter
    $result = $aiService->generateCoverLetter($cvData, $jobApplication, [
        'custom_instructions' => $customInstructions
    ]);
    
    // Restore original preference if we overrode it
    if ($tempOverrideApplied && $originalPreference !== null) {
        db()->update('profiles', ['ai_service_preference' => $originalPreference], 'id = ?', [$user['id']]);
    }
    
    // #region agent log
    error_log(json_encode(['id'=>'log_'.time().'_result','timestamp'=>time()*1000,'location'=>'api/ai-generate-cover-letter.php:127','message'=>'After generateCoverLetter call','data'=>['success'=>$result['success']??false,'browser_execution'=>$result['browser_execution']??false,'hasPrompt'=>isset($result['prompt']),'hasCoverLetterText'=>isset($result['cover_letter_text']),'error'=>$result['error']??null],'sessionId'=>'debug-session','runId'=>'run1','hypothesisId'=>'B']));
    // #endregion
    
    if (!$result['success']) {
        throw new Exception($result['error'] ?? 'Failed to generate cover letter');
    }
    
    // Check if this is browser execution mode
    if (isset($result['browser_execution']) && $result['browser_execution']) {
        // #region agent log
        error_log(json_encode(['id'=>'log_'.time().'_browser','timestamp'=>time()*1000,'location'=>'api/ai-generate-cover-letter.php:132','message'=>'Browser execution path','data'=>['promptLength'=>strlen($result['prompt']??''),'model'=>$result['model']??null],'sessionId'=>'debug-session','runId'=>'run1','hypothesisId'=>'B']));
        // #endregion
        // Browser AI - return prompt and instructions for frontend execution
        ob_end_clean();
        echo json_encode([
            'success' => true,
            'browser_execution' => true,
            'prompt' => $result['prompt'] ?? '',
            'model' => $result['model'] ?? 'llama3.2',
            'model_type' => $result['model_type'] ?? 'webllm',
            'cv_data' => $cvData,
            'job_application' => $jobApplication,
            'job_application_id' => $jobApplicationId,
            'message' => 'Browser AI execution required. Frontend will handle this request.'
        ]);
        exit;
    }
    
    $coverLetterText = $result['cover_letter_text'];
    
    // Save or update cover letter in database
    $coverLetterResult = createCoverLetter($user['id'], $jobApplicationId, $coverLetterText);
    
    if (!$coverLetterResult['success']) {
        throw new Exception($coverLetterResult['error'] ?? 'Failed to save cover letter');
    }
    
    // Get the saved cover letter
    $coverLetter = getCoverLetterByApplication($jobApplicationId, $user['id']);
    
    ob_end_clean();
    echo json_encode([
        'success' => true,
        'cover_letter_id' => $coverLetter['id'],
        'cover_letter_text' => $coverLetter['cover_letter_text'],
        'message' => 'Cover letter generated successfully'
    ]);
    
} catch (Exception $e) {
    ob_end_clean();
    http_response_code(500);
    error_log("AI Generate Cover Letter Error: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}

