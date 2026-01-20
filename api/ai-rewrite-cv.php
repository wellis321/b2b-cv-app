<?php
/**
 * AI CV Rewriting API Endpoint
 * Generates job-specific CV variants using AI
 */

// Prevent canonical redirect
define('SKIP_CANONICAL_REDIRECT', true);

// Start output buffering to prevent any output before JSON
ob_start();

// Increase timeout for AI processing (Ollama can take 30-60 seconds)
set_time_limit(180); // 3 minutes
ini_set('max_execution_time', 180);

require_once __DIR__ . '/../php/helpers.php';

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
    // #region agent log
    file_put_contents('/Users/wellis/Desktop/Cursor/b2b-cv-app/.cursor/debug.log', json_encode(['location'=>'api/ai-rewrite-cv.php:46','message'=>'API request started','data'=>['postKeys'=>array_keys($_POST),'hasJobAppId'=>isset($_POST['job_application_id']),'hasVariantId'=>isset($_POST['cv_variant_id'])],'timestamp'=>time()*1000,'sessionId'=>'debug-session','runId'=>'run1','hypothesisId'=>'E'])."\n", FILE_APPEND);
    // #endregion
    
    $jobApplicationId = $_POST['job_application_id'] ?? null;
    $sourceVariantId = $_POST['cv_variant_id'] ?? null;
    $variantName = $_POST['variant_name'] ?? 'AI-Generated CV';
    
    // Load job application if provided
    $jobDescription = null;
    $fileContents = [];
    
    if ($jobApplicationId) {
        require_once __DIR__ . '/../php/job-applications.php';
        
        $jobApp = db()->fetchOne(
            "SELECT * FROM job_applications WHERE id = ? AND user_id = ?",
            [$jobApplicationId, $user['id']]
        );
        
        if (!$jobApp) {
            throw new Exception('Job application not found');
        }
        
        // Get job description from text field
        $jobDescription = $jobApp['job_description'] ?? $jobApp['notes'] ?? '';
        
        // Get files and extract their content
        $filesWithText = getJobApplicationFilesForAI($jobApplicationId, $user['id']);
        foreach ($filesWithText as $fileData) {
            if (!empty($fileData['text'])) {
                $fileContents[] = $fileData['text'];
            }
        }
        
        // Check if variant already exists for this job
        $existingVariant = db()->fetchOne(
            "SELECT id FROM cv_variants WHERE job_application_id = ? AND user_id = ?",
            [$jobApplicationId, $user['id']]
        );
        
        if ($existingVariant) {
            ob_end_clean();
            echo json_encode([
                'success' => false,
                'error' => 'CV variant already exists for this job application',
                'variant_id' => $existingVariant['id']
            ]);
            exit;
        }
    } else {
        // Job description provided directly
        $jobDescription = $_POST['job_description'] ?? '';
    }
    
    // Combine job description with file contents
    $combinedDescription = $jobDescription;
    if (!empty($fileContents)) {
        if (!empty($combinedDescription)) {
            $combinedDescription .= "\n\n--- Additional Information from Uploaded Files ---\n\n";
        }
        $combinedDescription .= implode("\n\n--- File Content ---\n\n", $fileContents);
    }
    
    if (empty($combinedDescription)) {
        throw new Exception('Job description or file content is required');
    }
    
    // Load source CV data
    $cvData = null;
    if ($sourceVariantId) {
        // Load from variant
        $variant = getCvVariant($sourceVariantId, $user['id']);
        if (!$variant) {
            throw new Exception('Source CV variant not found');
        }
        $cvData = loadCvVariantData($sourceVariantId);
    } else {
        // Load master CV
        $cvData = loadCvData($user['id']);
    }
    
    if (!$cvData || empty($cvData)) {
        throw new Exception('No CV data found');
    }
    
    // Get sections to rewrite from POST (default to standard sections)
    $sectionsToRewrite = $_POST['sections_to_rewrite'] ?? ['professional_summary', 'work_experience', 'skills'];
    if (is_string($sectionsToRewrite)) {
        // If it's a JSON string, decode it
        $decoded = json_decode($sectionsToRewrite, true);
        if (is_array($decoded)) {
            $sectionsToRewrite = $decoded;
        } else {
            // If it's a comma-separated string, convert to array
            $sectionsToRewrite = array_filter(array_map('trim', explode(',', $sectionsToRewrite)));
        }
    }
    // Ensure professional_summary is always included
    if (!in_array('professional_summary', $sectionsToRewrite)) {
        $sectionsToRewrite[] = 'professional_summary';
    }
    
    // Get prompt instructions mode and custom instructions
    $promptMode = $_POST['prompt_instructions_mode'] ?? 'default';
    $customInstructions = null;
    
    if ($promptMode === 'saved') {
        // Use saved custom instructions from user's profile
        $userProfile = db()->fetchOne(
            "SELECT cv_rewrite_prompt_instructions FROM profiles WHERE id = ?",
            [$user['id']]
        );
        $customInstructions = $userProfile['cv_rewrite_prompt_instructions'] ?? null;
    } elseif ($promptMode === 'custom') {
        // Use custom instructions provided for this generation only
        $customInstructions = trim($_POST['prompt_custom_text'] ?? '');
        if (empty($customInstructions)) {
            throw new Exception('Custom instructions are required when "custom" mode is selected');
        }
        // Validate length
        if (strlen($customInstructions) > 2000) {
            throw new Exception('Custom instructions must be 2000 characters or less');
        }
    }
    // If mode is 'default', $customInstructions remains null and defaults will be used
    
    // Check if this is a browser AI result being saved (from client-side execution)
    $browserAiResult = $_POST['browser_ai_result'] ?? null;
    $forceServerAI = isset($_POST['force_server_ai']) && $_POST['force_server_ai'] === '1';
    
    if ($browserAiResult && !$forceServerAI) {
        // Browser AI already executed client-side - parse and use the result
        $rewrittenData = json_decode($browserAiResult, true);
        if (!$rewrittenData || !is_array($rewrittenData)) {
            throw new Exception('Invalid browser AI result format');
        }
        
        // #region agent log
        file_put_contents('/Users/wellis/Desktop/Cursor/b2b-cv-app/.cursor/debug.log', json_encode(['location'=>'api/ai-rewrite-cv.php:178','message'=>'Browser AI result parsed','data'=>['rewrittenDataKeys'=>array_keys($rewrittenData),'hasWorkExperience'=>isset($rewrittenData['work_experience']),'workExpCount'=>isset($rewrittenData['work_experience'])?count($rewrittenData['work_experience']):0,'workExpSample'=>isset($rewrittenData['work_experience'][0])?['id'=>$rewrittenData['work_experience'][0]['id']??null,'position'=>$rewrittenData['work_experience'][0]['position']??null,'company'=>$rewrittenData['work_experience'][0]['company_name']??null,'hasDesc'=>isset($rewrittenData['work_experience'][0]['description'])]:null],'timestamp'=>time()*1000,'sessionId'=>'debug-session','runId'=>'run1','hypothesisId'=>'Q'])."\n", FILE_APPEND);
        // #endregion
    } else {
        // Server-side AI execution (either normal flow or forced fallback)
        // Get AI service with user ID for user-specific settings
        
        // #region agent log
        file_put_contents('/Users/wellis/Desktop/Cursor/b2b-cv-app/.cursor/debug.log', json_encode(['location'=>'api/ai-rewrite-cv.php:179','message'=>'Before getAIService call','data'=>['userId'=>$user['id'],'function_exists'=>function_exists('getAIService'),'forceServerAI'=>$forceServerAI],'timestamp'=>time()*1000,'sessionId'=>'debug-session','runId'=>'run1','hypothesisId'=>'A'])."\n", FILE_APPEND);
        // #endregion
        
        if (!function_exists('getAIService')) {
            throw new Exception('getAIService function not found. Check if ai-service.php is loaded.');
        }
        
        // If forcing server AI, temporarily override the user's preference
        $originalService = null;
        if ($forceServerAI) {
            // Get the AI service but we'll need to override browser preference
            // For now, get the service and check if we need to switch
            $aiService = getAIService($user['id']);
            if ($aiService->service === 'browser') {
                // Need to use a different service - try to get first available cloud/local service
                // This is a simplified approach - in production you might want to check available services
                throw new Exception('Browser AI is selected but failed to load. Please configure Ollama or a cloud AI service (OpenAI, Anthropic, etc.) in Settings > AI Settings.');
            }
        } else {
            $aiService = getAIService($user['id']);
        }
        
        // #region agent log
        file_put_contents('/Users/wellis/Desktop/Cursor/b2b-cv-app/.cursor/debug.log', json_encode(['location'=>'api/ai-rewrite-cv.php:186','message'=>'After getAIService call','data'=>['aiServiceType'=>get_class($aiService),'hasRewriteMethod'=>method_exists($aiService,'rewriteCvForJob')],'timestamp'=>time()*1000,'sessionId'=>'debug-session','runId'=>'run1','hypothesisId'=>'B'])."\n", FILE_APPEND);
        // #endregion
        
        // Call AI to rewrite CV with combined description (text + file contents)
        // Pass sections to rewrite and custom instructions
        
        // #region agent log
        file_put_contents('/Users/wellis/Desktop/Cursor/b2b-cv-app/.cursor/debug.log', json_encode(['location'=>'api/ai-rewrite-cv.php:193','message'=>'Before rewriteCvForJob call','data'=>['cvDataKeys'=>array_keys($cvData),'combinedDescLength'=>strlen($combinedDescription),'sectionsToRewrite'=>$sectionsToRewrite],'timestamp'=>time()*1000,'sessionId'=>'debug-session','runId'=>'run1','hypothesisId'=>'C'])."\n", FILE_APPEND);
        // #endregion
        
        $result = $aiService->rewriteCvForJob($cvData, $combinedDescription, [
            'sections_to_rewrite' => $sectionsToRewrite,
            'custom_instructions' => $customInstructions
        ]);
        
        // #region agent log
        file_put_contents('/Users/wellis/Desktop/Cursor/b2b-cv-app/.cursor/debug.log', json_encode(['location'=>'api/ai-rewrite-cv.php:200','message'=>'After rewriteCvForJob call','data'=>['resultSuccess'=>($result['success']??false),'hasBrowserExecution'=>isset($result['browser_execution']),'hasError'=>isset($result['error'])],'timestamp'=>time()*1000,'sessionId'=>'debug-session','runId'=>'run1','hypothesisId'=>'D'])."\n", FILE_APPEND);
        // #endregion
        
        // Check if this is browser execution mode
        if (isset($result['browser_execution']) && $result['browser_execution']) {
            // Browser AI - return prompt and instructions for frontend execution
            
            // #region agent log
            file_put_contents('/Users/wellis/Desktop/Cursor/b2b-cv-app/.cursor/debug.log', json_encode(['location'=>'api/ai-rewrite-cv.php:218','message'=>'Returning browser_execution response','data'=>['hasPrompt'=>isset($result['prompt']),'promptLength'=>strlen($result['prompt']??''),'model'=>$result['model']??null,'modelType'=>$result['model_type']??null],'timestamp'=>time()*1000,'sessionId'=>'debug-session','runId'=>'run1','hypothesisId'=>'J'])."\n", FILE_APPEND);
            // #endregion
            
            ob_end_clean();
            echo json_encode([
                'success' => true,
                'browser_execution' => true,
                'prompt' => $result['prompt'] ?? '',
                'model' => $result['model'] ?? 'llama3.2',
                'model_type' => $result['model_type'] ?? 'webllm',
                'cv_data' => $cvData,
                'job_description' => $combinedDescription,
                'message' => 'Browser AI execution required. Frontend will handle this request.'
            ]);
            exit;
        }
        
        if (!$result['success']) {
            throw new Exception($result['error'] ?? 'AI rewriting failed');
        }
        
        $rewrittenData = $result['cv_data'];
    }
    
    // Check for duplicates before creating (check by name as well as job_application_id)
    if ($jobApplicationId) {
        $existingVariant = db()->fetchOne(
            "SELECT id FROM cv_variants WHERE job_application_id = ? AND user_id = ?",
            [$jobApplicationId, $user['id']]
        );
        
        if ($existingVariant) {
            ob_end_clean();
            echo json_encode([
                'success' => false,
                'error' => 'CV variant already exists for this job application',
                'variant_id' => $existingVariant['id']
            ]);
            exit;
        }
    }
    
    // Create new CV variant
    $newVariant = createCvVariant(
        $user['id'],
        $sourceVariantId,
        $variantName,
        $jobApplicationId
    );
    
    if (!$newVariant['success']) {
        throw new Exception($newVariant['error'] ?? 'Failed to create CV variant');
    }
    
    $newVariantId = $newVariant['variant_id'];
    
    // Merge AI-rewritten data with original data
    // IMPORTANT: The AI only returns rewritten sections based on user selection
    // We must preserve all other sections from the original CV data
    
    // Update professional summary if rewritten
    if (isset($rewrittenData['professional_summary'])) {
        $cvData['professional_summary'] = array_merge(
            $cvData['professional_summary'] ?? ['description' => ''],
            $rewrittenData['professional_summary']
        );
    }
    
    // Update work experience if rewritten
    if (isset($rewrittenData['work_experience']) && is_array($rewrittenData['work_experience'])) {
        // #region agent log
        file_put_contents('/Users/wellis/Desktop/Cursor/b2b-cv-app/.cursor/debug.log', json_encode(['location'=>'api/ai-rewrite-cv.php:291','message'=>'Starting work experience merge','data'=>['rewrittenCount'=>count($rewrittenData['work_experience']),'originalCount'=>count($cvData['work_experience']??[]),'rewrittenWorkIds'=>array_column($rewrittenData['work_experience'],'id'),'originalWorkIds'=>array_column($cvData['work_experience']??[],'id')],'timestamp'=>time()*1000,'sessionId'=>'debug-session','runId'=>'run1','hypothesisId'=>'K'])."\n", FILE_APPEND);
        // #endregion
        
        foreach ($rewrittenData['work_experience'] as $rewrittenWork) {
            $found = false;
            $matchType = null;
            
            foreach ($cvData['work_experience'] as &$work) {
                // Match by variant ID if provided
                $idMatch = isset($rewrittenWork['id']) && isset($work['id']) && $work['id'] === $rewrittenWork['id'];
                
                // Match by original_work_experience_id (if AI returns master CV ID)
                $originalIdMatch = false;
                if (isset($rewrittenWork['id']) && isset($work['original_work_experience_id'])) {
                    $originalIdMatch = $work['original_work_experience_id'] === $rewrittenWork['id'];
                }
                
                // Fallback: match by position and company name (case-insensitive)
                $positionMatch = !empty($rewrittenWork['position']) && !empty($work['position']) && 
                                strtolower(trim($work['position'])) === strtolower(trim($rewrittenWork['position']));
                $companyMatch = !empty($rewrittenWork['company_name']) && !empty($work['company_name']) && 
                               strtolower(trim($work['company_name'])) === strtolower(trim($rewrittenWork['company_name']));
                
                if ($idMatch || $originalIdMatch || ($positionMatch && $companyMatch)) {
                    // Determine match type for logging
                    $matchType = 'unknown';
                    if ($idMatch) $matchType = 'variant_id';
                    elseif ($originalIdMatch) $matchType = 'original_work_experience_id';
                    elseif ($positionMatch && $companyMatch) $matchType = 'position+company';
                    
                    // #region agent log
                    file_put_contents('/Users/wellis/Desktop/Cursor/b2b-cv-app/.cursor/debug.log', json_encode(['location'=>'api/ai-rewrite-cv.php:315','message'=>'Work experience match found','data'=>['matchType'=>$matchType,'rewrittenId'=>$rewrittenWork['id']??null,'variantId'=>$work['id']??null,'originalWorkExperienceId'=>$work['original_work_experience_id']??null,'rewrittenPosition'=>$rewrittenWork['position']??null,'originalPosition'=>$work['position']??null,'rewrittenCompany'=>$rewrittenWork['company_name']??null,'originalCompany'=>$work['company_name']??null,'hasDescription'=>isset($rewrittenWork['description']),'hasResponsibilityCategories'=>isset($rewrittenWork['responsibility_categories'])],'timestamp'=>time()*1000,'sessionId'=>'debug-session','runId'=>'run1','hypothesisId'=>'L'])."\n", FILE_APPEND);
                    // #endregion
                    
                    // Update description if provided
                    if (isset($rewrittenWork['description'])) {
                        $oldDesc = $work['description'] ?? '';
                        $work['description'] = $rewrittenWork['description'];
                        
                        // #region agent log
                        file_put_contents('/Users/wellis/Desktop/Cursor/b2b-cv-app/.cursor/debug.log', json_encode(['location'=>'api/ai-rewrite-cv.php:318','message'=>'Updated work description','data'=>['workId'=>$work['id']??null,'oldDescLength'=>strlen($oldDesc),'newDescLength'=>strlen($rewrittenWork['description']),'descChanged'=>$oldDesc!==$rewrittenWork['description']],'timestamp'=>time()*1000,'sessionId'=>'debug-session','runId'=>'run1','hypothesisId'=>'M'])."\n", FILE_APPEND);
                        // #endregion
                    }
                    
                    // Update responsibility categories if provided
                    if (isset($rewrittenWork['responsibility_categories']) && is_array($rewrittenWork['responsibility_categories'])) {
                        $oldCategories = $work['responsibility_categories'] ?? [];
                        $work['responsibility_categories'] = $rewrittenWork['responsibility_categories'];
                        
                        // #region agent log
                        file_put_contents('/Users/wellis/Desktop/Cursor/b2b-cv-app/.cursor/debug.log', json_encode(['location'=>'api/ai-rewrite-cv.php:328','message'=>'Updated responsibility categories','data'=>['workId'=>$work['id']??null,'oldCategoryCount'=>count($oldCategories),'newCategoryCount'=>count($rewrittenWork['responsibility_categories'])],'timestamp'=>time()*1000,'sessionId'=>'debug-session','runId'=>'run1','hypothesisId'=>'N'])."\n", FILE_APPEND);
                        // #endregion
                    }
                    
                    $found = true;
                    $matchType = $idMatch ? 'id' : 'position+company';
                    break;
                }
            }
            
            if (!$found) {
                // #region agent log
                file_put_contents('/Users/wellis/Desktop/Cursor/b2b-cv-app/.cursor/debug.log', json_encode(['location'=>'api/ai-rewrite-cv.php:340','message'=>'Work experience NOT found','data'=>['rewrittenId'=>$rewrittenWork['id']??null,'rewrittenPosition'=>$rewrittenWork['position']??null,'rewrittenCompany'=>$rewrittenWork['company_name']??null,'originalPositions'=>array_column($cvData['work_experience']??[],'position'),'originalCompanies'=>array_column($cvData['work_experience']??[],'company_name')],'timestamp'=>time()*1000,'sessionId'=>'debug-session','runId'=>'run1','hypothesisId'=>'O'])."\n", FILE_APPEND);
                // #endregion
            }
        }
        
        // #region agent log
        file_put_contents('/Users/wellis/Desktop/Cursor/b2b-cv-app/.cursor/debug.log', json_encode(['location'=>'api/ai-rewrite-cv.php:348','message'=>'Work experience merge complete','data'=>['finalWorkCount'=>count($cvData['work_experience']??[])],'timestamp'=>time()*1000,'sessionId'=>'debug-session','runId'=>'run1','hypothesisId'=>'P'])."\n", FILE_APPEND);
        // #endregion
    }
    
    // Update skills if rewritten
    if (isset($rewrittenData['skills']) && is_array($rewrittenData['skills'])) {
        // Merge with existing skills, prioritising rewritten ones
        $existingSkillNames = array_map(function($s) { return strtolower($s['name']); }, $cvData['skills'] ?? []);
        $newSkills = [];
        
        foreach ($rewrittenData['skills'] as $skill) {
            $skillName = is_array($skill) ? $skill['name'] : $skill;
            if (!in_array(strtolower($skillName), $existingSkillNames)) {
                $newSkills[] = is_array($skill) ? $skill : ['name' => $skill, 'category' => null];
            }
        }
        
        // Add new skills to existing
        $cvData['skills'] = array_merge($cvData['skills'] ?? [], $newSkills);
    }
    
    // Update education if rewritten
    if (isset($rewrittenData['education']) && is_array($rewrittenData['education'])) {
        foreach ($rewrittenData['education'] as $rewrittenEdu) {
            foreach ($cvData['education'] as &$edu) {
                if (isset($rewrittenEdu['id']) && $edu['id'] === $rewrittenEdu['id']) {
                    if (isset($rewrittenEdu['description'])) {
                        $edu['description'] = $rewrittenEdu['description'];
                    }
                    break;
                }
            }
        }
    }
    
    // Update projects if rewritten
    if (isset($rewrittenData['projects']) && is_array($rewrittenData['projects'])) {
        foreach ($rewrittenData['projects'] as $rewrittenProj) {
            $found = false;
            foreach ($cvData['projects'] as &$proj) {
                // Match by ID if provided, otherwise match by title
                $idMatch = isset($rewrittenProj['id']) && isset($proj['id']) && $proj['id'] === $rewrittenProj['id'];
                $titleMatch = !empty($rewrittenProj['title']) && !empty($proj['title']) && 
                             strtolower(trim($proj['title'])) === strtolower(trim($rewrittenProj['title']));
                
                if ($idMatch || $titleMatch) {
                    // Update description if provided
                    if (isset($rewrittenProj['description'])) {
                        $proj['description'] = $rewrittenProj['description'];
                    }
                    // Preserve variant project ID and original_project_id so saveCvVariantData can find and update it
                    // Don't modify $rewrittenProj here - we're updating $proj which is already in $cvData
                    $found = true;
                    break;
                }
            }
            // If project not found, it means the AI returned a project that doesn't exist in the original CV
            // We don't add new projects during rewrite - only update existing ones
            // So we ignore unmatched projects from the AI response
        }
    }
    
    // Update certifications if rewritten
    if (isset($rewrittenData['certifications']) && is_array($rewrittenData['certifications'])) {
        foreach ($rewrittenData['certifications'] as $rewrittenCert) {
            foreach ($cvData['certifications'] as &$cert) {
                if (isset($rewrittenCert['id']) && $cert['id'] === $rewrittenCert['id']) {
                    if (isset($rewrittenCert['description'])) {
                        $cert['description'] = $rewrittenCert['description'];
                    }
                    break;
                }
            }
        }
    }
    
    // Update professional memberships if rewritten
    if (isset($rewrittenData['professional_memberships']) && is_array($rewrittenData['professional_memberships'])) {
        foreach ($rewrittenData['professional_memberships'] as $rewrittenMembership) {
            foreach ($cvData['professional_memberships'] as &$membership) {
                if (isset($rewrittenMembership['id']) && $membership['id'] === $rewrittenMembership['id']) {
                    if (isset($rewrittenMembership['description'])) {
                        $membership['description'] = $rewrittenMembership['description'];
                    }
                    break;
                }
            }
        }
    }
    
    // Update interests if rewritten
    if (isset($rewrittenData['interests']) && is_array($rewrittenData['interests'])) {
        foreach ($rewrittenData['interests'] as $rewrittenInterest) {
            foreach ($cvData['interests'] as &$interest) {
                if (isset($rewrittenInterest['id']) && $interest['id'] === $rewrittenInterest['id']) {
                    if (isset($rewrittenInterest['description'])) {
                        $interest['description'] = $rewrittenInterest['description'];
                    }
                    break;
                }
            }
        }
    }
    
    // Preserve all other sections from original CV that weren't rewritten
    // These sections should already be in $cvData from loadCvData() or loadCvVariantData()
    // But ensure they're not accidentally removed if AI response doesn't include them
    
    // Mark as AI-generated
    db()->update('cv_variants',
        ['ai_generated' => true],
        'id = ?',
        [$newVariantId]
    );
    
    // Save the merged CV data to the new variant
    $saveResult = saveCvVariantData($newVariantId, $cvData);
    
    if (!$saveResult['success']) {
        throw new Exception($saveResult['error'] ?? 'Failed to save rewritten CV');
    }
    
    // Clean output buffer and return success
    ob_end_clean();
    echo json_encode([
        'success' => true,
        'variant_id' => $newVariantId,
        'message' => 'CV successfully rewritten for this job'
    ]);
    
} catch (Exception $e) {
    ob_end_clean();
    http_response_code(500);
    $errorMessage = $e->getMessage();
    $errorTrace = $e->getTraceAsString();
    error_log("AI Rewrite CV Error: " . $errorMessage);
    error_log("AI Rewrite CV Trace: " . $errorTrace);
    
    // #region agent log
    file_put_contents('/Users/wellis/Desktop/Cursor/b2b-cv-app/.cursor/debug.log', json_encode(['location'=>'api/ai-rewrite-cv.php:393','message'=>'Exception caught','data'=>['error'=>$errorMessage,'trace'=>substr($errorTrace,0,500),'file'=>$e->getFile(),'line'=>$e->getLine()],'timestamp'=>time()*1000,'sessionId'=>'debug-session','runId'=>'run1','hypothesisId'=>'A'])."\n", FILE_APPEND);
    // #endregion
    
    echo json_encode([
        'success' => false,
        'error' => $errorMessage,
        'debug' => APP_ENV === 'development' ? [
            'file' => $e->getFile(),
            'line' => $e->getLine(),
            'trace' => substr($errorTrace, 0, 200)
        ] : null
    ]);
}

