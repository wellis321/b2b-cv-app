<?php
/**
 * AI Service Abstraction Layer
 * Supports Ollama (local, free) and cloud APIs (OpenAI, Anthropic, Gemini, Grok)
 */

require_once __DIR__ . '/config.php';
require_once __DIR__ . '/utils.php';
require_once __DIR__ . '/encryption.php';
require_once __DIR__ . '/authorisation.php';

class AIService {
    private $service;
    private $config;
    
    public function __construct($userId = null) {
        // Check for user-specific settings first
        $userService = null;
        $userOllamaUrl = null;
        $userOllamaModel = null;
        
        if ($userId) {
            // Check if AI settings columns exist before querying
            try {
                $user = db()->fetchOne(
                    "SELECT ai_service_preference, ollama_base_url, ollama_model, openai_api_key, anthropic_api_key, gemini_api_key, grok_api_key, browser_ai_model FROM profiles WHERE id = ?",
                    [$userId]
                );
                if ($user) {
                    $userService = $user['ai_service_preference'] ?? null;
                    $userOllamaUrl = $user['ollama_base_url'] ?? null;
                    $userOllamaModel = $user['ollama_model'] ?? null;
                    $userOpenAiKey = $user['openai_api_key'] ?? null;
                    $userAnthropicKey = $user['anthropic_api_key'] ?? null;
                    $userGeminiKey = $user['gemini_api_key'] ?? null;
                    $userGrokKey = $user['grok_api_key'] ?? null;
                    $userBrowserModel = $user['browser_ai_model'] ?? null;
                }
            } catch (Exception $e) {
                // Columns don't exist yet - migration not run
                // Log error but continue with default settings
                error_log("AI settings columns not found. Please run migrations: database/20250121_add_user_ai_settings.sql, database/20250125_add_user_ai_api_keys.sql, and database/20250127_add_gemini_grok_api_keys.sql - " . $e->getMessage());
                $userService = null;
                $userOllamaUrl = null;
                $userOllamaModel = null;
                $userOpenAiKey = null;
                $userAnthropicKey = null;
                $userGeminiKey = null;
                $userGrokKey = null;
                $userBrowserModel = null;
            }
        } else {
            $userOpenAiKey = null;
            $userAnthropicKey = null;
            $userGeminiKey = null;
            $userGrokKey = null;
            $userBrowserModel = null;
        }
        
        // Check organization settings if user has no personal settings
        $orgService = null;
        $orgOllamaUrl = null;
        $orgOllamaModel = null;
        $orgOpenAiKey = null;
        $orgAnthropicKey = null;
        $orgGeminiKey = null;
        $orgGrokKey = null;
        $orgBrowserModel = null;
        $organisationId = null;
        
        // Only check organization settings if user has no personal preference
        if (!$userService && $userId) {
            try {
                // Get user's organization
                $userOrg = getUserOrganisation($userId);
                if ($userOrg && !empty($userOrg['organisation_id'])) {
                    $organisationId = $userOrg['organisation_id'];
                    // Check if organization has AI enabled
                    $org = db()->fetchOne(
                        "SELECT org_ai_service_preference, org_ai_enabled, org_ollama_base_url, org_ollama_model, 
                                org_openai_api_key, org_anthropic_api_key, org_gemini_api_key, org_grok_api_key, 
                                org_browser_ai_model 
                         FROM organisations 
                         WHERE id = ? AND org_ai_enabled = 1",
                        [$organisationId]
                    );
                    
                    if ($org) {
                        $orgService = $org['org_ai_service_preference'] ?? null;
                        $orgOllamaUrl = $org['org_ollama_base_url'] ?? null;
                        $orgOllamaModel = $org['org_ollama_model'] ?? null;
                        $orgOpenAiKey = $org['org_openai_api_key'] ?? null;
                        $orgAnthropicKey = $org['org_anthropic_api_key'] ?? null;
                        $orgGeminiKey = $org['org_gemini_api_key'] ?? null;
                        $orgGrokKey = $org['org_grok_api_key'] ?? null;
                        $orgBrowserModel = $org['org_browser_ai_model'] ?? null;
                    }
                }
            } catch (Exception $e) {
                // Organization AI columns may not exist yet - log but continue
                error_log("Organization AI settings columns not found. Please run migration: database/20250128_add_organisation_ai_settings.sql - " . $e->getMessage());
            }
        }
        
        // Priority: User settings > Organization settings > Environment defaults
        if ($userService) {
            $service = $userService;
        } elseif ($orgService) {
            $service = $orgService;
        } else {
            $service = env('AI_SERVICE', 'ollama');
        }
        
        // Normalize service name
        $service = trim($service);
        if (($commentPos = strpos($service, '#')) !== false) {
            $service = trim(substr($service, 0, $commentPos));
        }
        $this->service = strtolower($service);
        
        // Decrypt user API keys if present (only decrypt when needed, not stored in memory)
        $decryptedOpenAiKey = null;
        $decryptedAnthropicKey = null;
        $decryptedGeminiKey = null;
        $decryptedGrokKey = null;
        
        if (!empty($userOpenAiKey)) {
            $decryptedOpenAiKey = decryptApiKey($userOpenAiKey);
            if ($decryptedOpenAiKey === false) {
                error_log("Failed to decrypt user OpenAI API key for user: " . $userId);
            }
        }
        
        if (!empty($userAnthropicKey)) {
            $decryptedAnthropicKey = decryptApiKey($userAnthropicKey);
            if ($decryptedAnthropicKey === false) {
                error_log("Failed to decrypt user Anthropic API key for user: " . $userId);
            }
        }
        
        if (!empty($userGeminiKey)) {
            $decryptedGeminiKey = decryptApiKey($userGeminiKey);
            if ($decryptedGeminiKey === false) {
                error_log("Failed to decrypt user Gemini API key for user: " . $userId);
            }
        }
        
        if (!empty($userGrokKey)) {
            $decryptedGrokKey = decryptApiKey($userGrokKey);
            if ($decryptedGrokKey === false) {
                error_log("Failed to decrypt user Grok API key for user: " . $userId);
            }
        }
        
        // Decrypt organization API keys if no user keys and org settings exist
        $decryptedOrgOpenAiKey = null;
        $decryptedOrgAnthropicKey = null;
        $decryptedOrgGeminiKey = null;
        $decryptedOrgGrokKey = null;
        
        if (empty($userOpenAiKey) && !empty($orgOpenAiKey)) {
            $decryptedOrgOpenAiKey = decryptApiKey($orgOpenAiKey);
            if ($decryptedOrgOpenAiKey === false) {
                error_log("Failed to decrypt organization OpenAI API key for org: " . ($organisationId ?? 'unknown'));
            }
        }
        
        if (empty($userAnthropicKey) && !empty($orgAnthropicKey)) {
            $decryptedOrgAnthropicKey = decryptApiKey($orgAnthropicKey);
            if ($decryptedOrgAnthropicKey === false) {
                error_log("Failed to decrypt organization Anthropic API key for org: " . ($organisationId ?? 'unknown'));
            }
        }
        
        if (empty($userGeminiKey) && !empty($orgGeminiKey)) {
            $decryptedOrgGeminiKey = decryptApiKey($orgGeminiKey);
            if ($decryptedOrgGeminiKey === false) {
                error_log("Failed to decrypt organization Gemini API key for org: " . ($organisationId ?? 'unknown'));
            }
        }
        
        if (empty($userGrokKey) && !empty($orgGrokKey)) {
            $decryptedOrgGrokKey = decryptApiKey($orgGrokKey);
            if ($decryptedOrgGrokKey === false) {
                error_log("Failed to decrypt organization Grok API key for org: " . ($organisationId ?? 'unknown'));
            }
        }
        
        // Build config with priority: User settings > Organization settings > Environment defaults
        $this->config = [
            'ollama' => [
                'base_url' => $userOllamaUrl ?: ($orgOllamaUrl ?: env('OLLAMA_BASE_URL', 'http://localhost:11434')),
                'model' => $userOllamaModel ?: ($orgOllamaModel ?: env('OLLAMA_MODEL', 'llama3.2:3b')),
            ],
            'openai' => [
                'api_key' => $decryptedOpenAiKey ?: ($decryptedOrgOpenAiKey ?: env('OPENAI_API_KEY', '')),
                'model' => env('OPENAI_MODEL', 'gpt-4-turbo-preview'),
                'base_url' => 'https://api.openai.com/v1',
            ],
            'anthropic' => [
                'api_key' => $decryptedAnthropicKey ?: ($decryptedOrgAnthropicKey ?: env('ANTHROPIC_API_KEY', '')),
                'model' => env('ANTHROPIC_MODEL', 'claude-3-opus-20240229'),
                'base_url' => 'https://api.anthropic.com/v1',
            ],
            'gemini' => [
                'api_key' => $decryptedGeminiKey ?: ($decryptedOrgGeminiKey ?: env('GEMINI_API_KEY', '')),
                'model' => env('GEMINI_MODEL', 'gemini-pro'),
                'base_url' => 'https://generativelanguage.googleapis.com/v1beta',
            ],
            'grok' => [
                'api_key' => $decryptedGrokKey ?: ($decryptedOrgGrokKey ?: env('GROK_API_KEY', '')),
                'model' => env('GROK_MODEL', 'grok-beta'),
                'base_url' => 'https://api.x.ai/v1',
            ],
            'browser' => [
                'model' => $userBrowserModel ?: ($orgBrowserModel ?: 'llama3.2'),
                'model_type' => 'webllm', // or 'tensorflow.js'
            ],
        ];
    }
    
    /**
     * Rewrite CV sections to match a job description
     * @param array $cvData The CV data to rewrite
     * @param string|array $jobDescription Job description text, or array of file contents, or both
     * @param array $options Additional options
     */
    public function rewriteCvForJob($cvData, $jobDescription, $options = []) {
        // Handle file content if provided
        $combinedDescription = '';
        
        if (is_array($jobDescription)) {
            // Array of file contents
            $combinedDescription = implode("\n\n--- File Content ---\n\n", array_filter($jobDescription));
        } else {
            // String job description
            $combinedDescription = $jobDescription;
        }
        
        // If options contain file_contents, combine with description
        if (!empty($options['file_contents']) && is_array($options['file_contents'])) {
            $fileContents = implode("\n\n--- File Content ---\n\n", array_filter($options['file_contents']));
            if (!empty($combinedDescription)) {
                $combinedDescription .= "\n\n--- Additional File Content ---\n\n" . $fileContents;
            } else {
                $combinedDescription = $fileContents;
            }
        }
        
        $prompt = $this->buildCvRewritePrompt($cvData, $combinedDescription, $options);
        $response = $this->callAI($prompt, [
            'temperature' => 0.7,
            'max_tokens' => 8000,
        ]);
        
        if (!$response['success']) {
            return $response;
        }
        
        // Parse JSON response
        $rewritten = $this->parseJsonResponse($response['content']);
        
        if (!$rewritten) {
            return [
                'success' => false,
                'error' => 'Failed to parse AI response. The AI may not have returned valid JSON.',
                'raw_response' => $response['content']
            ];
        }
        
        return [
            'success' => true,
            'cv_data' => $rewritten,
            'raw_response' => $response['content']
        ];
    }
    
    /**
     * Generate a custom CV template based on user description
     * @param array $cvData The CV data structure
     * @param string $userDescription User's description of desired design
     * @param array $options Additional options (layout preferences, image path, URL, etc.)
     */
    public function generateCvTemplate($cvData, $userDescription, $options = []) {
        // #region agent log
        file_put_contents('/Users/wellis/Desktop/Cursor/b2b-cv-app/.cursor/debug.log', json_encode(['id'=>'log_'.time().'_entry','timestamp'=>time()*1000,'location'=>'ai-service.php:137','message'=>'generateCvTemplate entry','data'=>['hasDescription'=>!empty($userDescription),'hasUrl'=>!empty($options['reference_url']),'hasImage'=>!empty($options['reference_image_path']),'service'=>$this->service],'sessionId'=>'debug-session','runId'=>'run1','hypothesisId'=>'A,B,C,D'])."\n", FILE_APPEND);
        // #endregion
        
        $prompt = $this->buildTemplateGenerationPrompt($cvData, $userDescription, $options);
        
        // Prepare image data if provided
        $imageData = null;
        if (!empty($options['reference_image_path']) && file_exists($options['reference_image_path'])) {
            $imageData = [
                'path' => $options['reference_image_path'],
                'base64' => base64_encode(file_get_contents($options['reference_image_path'])),
                'mime_type' => mime_content_type($options['reference_image_path'])
            ];
        }
        
        $response = $this->callAI($prompt, [
            'temperature' => 0.7,
            'max_tokens' => 8000, // HTML/CSS can be long
            'image_data' => $imageData
        ]);
        
        // #region agent log
        file_put_contents('/Users/wellis/Desktop/Cursor/b2b-cv-app/.cursor/debug.log', json_encode(['id'=>'log_'.time().'_ai_response','timestamp'=>time()*1000,'location'=>'ai-service.php:156','message'=>'AI service response','data'=>['success'=>$response['success']??false,'hasContent'=>!empty($response['content']??''),'contentLength'=>strlen($response['content']??''),'contentPreview'=>substr($response['content']??'',0,200),'error'=>$response['error']??null],'sessionId'=>'debug-session','runId'=>'run1','hypothesisId'=>'A,B,C,D'])."\n", FILE_APPEND);
        // #endregion
        
        if (!$response['success']) {
            return $response;
        }
        
        // Parse JSON response
        $template = $this->parseJsonResponse($response['content']);
        
        // #region agent log
        file_put_contents('/Users/wellis/Desktop/Cursor/b2b-cv-app/.cursor/debug.log', json_encode(['id'=>'log_'.time().'_parse_result','timestamp'=>time()*1000,'location'=>'ai-service.php:163','message'=>'JSON parse result','data'=>['parsed'=>!empty($template),'isArray'=>is_array($template),'hasHtml'=>!empty($template['html']??''),'hasCss'=>!empty($template['css']??''),'jsonError'=>json_last_error_msg()],'sessionId'=>'debug-session','runId'=>'run1','hypothesisId'=>'A,B,E,F'])."\n", FILE_APPEND);
        // #endregion
        
        if (!$template) {
            // Log the raw response for debugging
            error_log("Template Generation - Failed to parse JSON. Raw response (first 2000 chars): " . substr($response['content'], 0, 2000));
            error_log("Template Generation - JSON error: " . json_last_error_msg());
            
            return [
                'success' => false,
                'error' => 'Failed to parse AI response. The AI may not have returned valid JSON. Please try again with a more specific description.',
                'raw_response' => substr($response['content'], 0, 500) // Include first 500 chars for debugging
            ];
        }
        
        // Validate template structure
        $validation = $this->validateTemplate($template);
        if (!$validation['valid']) {
            return [
                'success' => false,
                'error' => 'Generated template failed validation: ' . $validation['error'],
                'raw_response' => $response['content']
            ];
        }
        
        return [
            'success' => true,
            'html' => $template['html'] ?? '',
            'css' => $template['css'] ?? '',
            'instructions' => $template['instructions'] ?? '',
            'raw_response' => $response['content']
        ];
    }
    
    /**
     * Assess CV quality and provide recommendations
     */
    public function assessCvQuality($cvData, $jobDescription = null) {
        $prompt = $this->buildQualityAssessmentPrompt($cvData, $jobDescription);
        $response = $this->callAI($prompt, [
            'temperature' => 0.3,
            'max_tokens' => 2000,
        ]);
        
        if (!$response['success']) {
            return $response;
        }
        
        // Parse JSON response
        $assessment = $this->parseJsonResponse($response['content']);
        
        if (!$assessment) {
            return [
                'success' => false,
                'error' => 'Failed to parse AI response. The AI may not have returned valid JSON.',
                'raw_response' => $response['content']
            ];
        }
        
        // Validate assessment structure
        $assessment = $this->validateAssessment($assessment);
        
        return [
            'success' => true,
            'assessment' => $assessment,
            'raw_response' => $response['content']
        ];
    }
    
    /**
     * Extract keywords from job description
     */
    public function extractJobKeywords($jobDescription) {
        $prompt = "Extract the most important keywords, skills, and requirements from this job description. Return a JSON array of strings.\n\nJob Description:\n" . $jobDescription;
        
        $response = $this->callAI($prompt, [
            'temperature' => 0.2,
            'max_tokens' => 500,
        ]);
        
        if (!$response['success']) {
            return $response;
        }
        
        $keywords = $this->parseJsonResponse($response['content']);
        
        return [
            'success' => true,
            'keywords' => is_array($keywords) ? $keywords : []
        ];
    }
    
    /**
     * Generate improvement suggestions based on assessment
     */
    public function suggestImprovements($cvData, $assessment) {
        $prompt = "Based on this CV quality assessment, provide specific, actionable improvement suggestions for each weakness identified.\n\n";
        $prompt .= "CV Assessment:\n" . json_encode($assessment, JSON_PRETTY_PRINT) . "\n\n";
        $prompt .= "Return a JSON object with an 'improvements' array, where each item has 'section', 'issue', and 'suggestion' fields.";
        
        $response = $this->callAI($prompt, [
            'temperature' => 0.5,
            'max_tokens' => 1500,
        ]);
        
        if (!$response['success']) {
            return $response;
        }
        
        $suggestions = $this->parseJsonResponse($response['content']);
        
        return [
            'success' => true,
            'suggestions' => $suggestions['improvements'] ?? []
        ];
    }
    
    /**
     * Build prompt for CV rewriting
     */
    private function buildCvRewritePrompt($cvData, $jobDescription, $options = []) {
        // Get sections to rewrite from options, default to all standard sections
        $sectionsToRewrite = $options['sections_to_rewrite'] ?? ['professional_summary', 'work_experience', 'skills'];
        
        // Get custom instructions from user or use defaults
        $customInstructions = $options['custom_instructions'] ?? null;
        
        $prompt = "You are a professional CV writer. Rewrite the following CV sections to better match this job description while maintaining factual accuracy.\n\n";
        $prompt .= "Job Description:\n" . $jobDescription . "\n\n";
        $prompt .= "Current CV Data:\n";
        
        if (!empty($cvData['professional_summary'])) {
            $prompt .= "- Professional Summary: " . ($cvData['professional_summary']['description'] ?? '') . "\n";
        }
        
        if (!empty($cvData['work_experience'])) {
            $prompt .= "- Work Experience:\n";
            foreach ($cvData['work_experience'] as $work) {
                $prompt .= "  * " . ($work['position'] ?? '') . " at " . ($work['company_name'] ?? '') . "\n";
                if (!empty($work['description'])) {
                    $prompt .= "    Description: " . $work['description'] . "\n";
                }
                if (!empty($work['responsibility_categories'])) {
                    foreach ($work['responsibility_categories'] as $cat) {
                        $prompt .= "    " . ($cat['name'] ?? '') . ":\n";
                        if (!empty($cat['items'])) {
                            foreach ($cat['items'] as $item) {
                                $prompt .= "      - " . ($item['content'] ?? '') . "\n";
                            }
                        }
                    }
                }
            }
        }
        
        if (!empty($cvData['skills'])) {
            $skills = array_map(function($s) { return $s['name']; }, $cvData['skills']);
            $prompt .= "- Skills: " . implode(', ', $skills) . "\n";
        }
        
        if (!empty($cvData['education'])) {
            $prompt .= "- Education:\n";
            foreach ($cvData['education'] as $edu) {
                $prompt .= "  * " . ($edu['degree'] ?? '') . " in " . ($edu['field_of_study'] ?? '') . " from " . ($edu['institution'] ?? '') . "\n";
                if (!empty($edu['description'])) {
                    $prompt .= "    Description: " . ($edu['description'] ?? '') . "\n";
                }
            }
        }
        
        if (!empty($cvData['projects'])) {
            $prompt .= "- Projects:\n";
            foreach ($cvData['projects'] as $proj) {
                $prompt .= "  * " . ($proj['title'] ?? '') . ": " . ($proj['description'] ?? '') . "\n";
            }
        }
        
        if (!empty($cvData['certifications'])) {
            $prompt .= "- Certifications:\n";
            foreach ($cvData['certifications'] as $cert) {
                $prompt .= "  * " . ($cert['name'] ?? '') . " from " . ($cert['issuer'] ?? '') . "\n";
            }
        }
        
        if (!empty($cvData['professional_memberships'])) {
            $prompt .= "- Professional Memberships:\n";
            foreach ($cvData['professional_memberships'] as $membership) {
                $prompt .= "  * " . ($membership['name'] ?? '') . " - " . ($membership['organisation'] ?? '') . "\n";
            }
        }
        
        if (!empty($cvData['interests'])) {
            $prompt .= "- Interests:\n";
            foreach ($cvData['interests'] as $interest) {
                $prompt .= "  * " . ($interest['name'] ?? '') . ($interest['description'] ? ': ' . $interest['description'] : '') . "\n";
            }
        }
        
        // Build default instructions
        $defaultInstructions = "1. Maintain factual accuracy - do not invent experiences, dates, or qualifications\n";
        $defaultInstructions .= "2. ENHANCE and EXPAND content with relevant details, achievements, and metrics. Do NOT simplify or reduce content. Preserve all original information while adding job-relevant details.\n";
        $defaultInstructions .= "3. Emphasize relevant skills and experiences that match the job description\n";
        $defaultInstructions .= "4. Use keywords from the job description naturally throughout\n";
        $defaultInstructions .= "5. Keep the same structure and format\n";
        $defaultInstructions .= "6. Maintain professional tone\n";
        $defaultInstructions .= "7. For work experience, rewrite descriptions and responsibility items to highlight relevant achievements with specific examples and quantifiable results\n";
        $defaultInstructions .= "8. For professional summary, tailor it to emphasize alignment with the job while maintaining or increasing detail level\n";
        $defaultInstructions .= "9. Ensure skills section includes relevant keywords from the job description\n";
        $defaultInstructions .= "10. When rewriting, add context, metrics, and achievements where appropriate - make content more compelling, not less\n";
        
        // Merge custom instructions if provided
        $instructions = $defaultInstructions;
        if (!empty($customInstructions)) {
            $instructions = $defaultInstructions . "\n\nAdditional User Instructions:\n" . $customInstructions;
        }
        
        $prompt .= "\nInstructions:\n" . $instructions . "\n\n";
        
        // Build JSON structure based on sections to rewrite
        $prompt .= "CRITICAL: You MUST return ALL requested sections in the JSON response. Do not omit any section that is requested.\n\n";
        $prompt .= "Return a JSON object with the rewritten sections. Structure:\n";
        $prompt .= "{\n";
        
        if (in_array('professional_summary', $sectionsToRewrite)) {
            $prompt .= "  \"professional_summary\": {\"description\": \"...\"},\n";
        }
        
        if (in_array('work_experience', $sectionsToRewrite)) {
            $prompt .= "  \"work_experience\": [{\"id\": \"...\", \"description\": \"...\", \"responsibility_categories\": [{\"name\": \"...\", \"items\": [{\"content\": \"...\"}]}]}],\n";
        }
        
        if (in_array('skills', $sectionsToRewrite)) {
            $prompt .= "  \"skills\": [{\"name\": \"...\", \"category\": \"...\"}],\n";
        }
        
        if (in_array('education', $sectionsToRewrite)) {
            $prompt .= "  \"education\": [{\"id\": \"...\", \"description\": \"...\"}],\n";
        }
        
        if (in_array('projects', $sectionsToRewrite)) {
            $prompt .= "  \"projects\": [{\"id\": \"...\", \"description\": \"...\"}],\n";
        }
        
        if (in_array('certifications', $sectionsToRewrite)) {
            $prompt .= "  \"certifications\": [{\"id\": \"...\", \"description\": \"...\"}],\n";
        }
        
        if (in_array('professional_memberships', $sectionsToRewrite)) {
            $prompt .= "  \"professional_memberships\": [{\"id\": \"...\", \"description\": \"...\"}],\n";
        }
        
        if (in_array('interests', $sectionsToRewrite)) {
            $prompt .= "  \"interests\": [{\"id\": \"...\", \"description\": \"...\"}],\n";
        }
        
        $prompt .= "}\n";
        $prompt .= "\nIMPORTANT: You MUST include ALL requested sections in your response. Keep original IDs for all items. Enhance content with more detail, achievements, and metrics - do not reduce or simplify.";
        
        return $prompt;
    }
    
    /**
     * Build prompt for quality assessment
     */
    private function buildQualityAssessmentPrompt($cvData, $jobDescription = null) {
        $prompt = "You are a CV assessment system. Your response MUST be valid JSON only. Do not include any markdown formatting, explanatory text, or code blocks. Return ONLY a valid JSON object.\n\n";
        
        // Format CV data for prompt
        $cvText = $this->formatCvForPrompt($cvData);
        $prompt .= "CV Data:\n" . $cvText . "\n\n";
        
        if ($jobDescription) {
            $prompt .= "Job Description:\n" . $jobDescription . "\n\n";
        }
        
        $prompt .= "Assess the following (provide scores 0-100):\n";
        $prompt .= "1. Overall quality - completeness, professionalism, clarity\n";
        $prompt .= "2. ATS compatibility - formatting, keywords, structure\n";
        $prompt .= "3. Content quality - relevance, impact, specificity\n";
        $prompt .= "4. Formatting - consistency, readability, structure\n";
        if ($jobDescription) {
            $prompt .= "5. Keyword matching - alignment with job requirements\n";
        }
        
        $prompt .= "\nCRITICAL: Analyze employment history for:\n";
        $prompt .= "- Gaps between jobs (periods with no employment listed)\n";
        $prompt .= "- Missing or incomplete dates (start dates, end dates)\n";
        $prompt .= "- Overlapping employment dates (if any)\n";
        $prompt .= "- Unexplained periods that should be addressed in the CV\n";
        $prompt .= "For any gaps or missing dates found, include specific recommendations in the weaknesses and enhanced_recommendations sections.\n";
        
        $prompt .= "\nProvide:\n";
        $prompt .= "- Scores for each category (0-100)\n";
        $prompt .= "- Strengths (array of strings)\n";
        $prompt .= "- Weaknesses (array of strings)\n";
        $prompt .= "- Enhanced Recommendations (array of objects with detailed suggestions)\n\n";
        
        $prompt .= "For each recommendation, provide:\n";
        $prompt .= "1. The issue/problem identified\n";
        $prompt .= "2. A clear suggestion for improvement\n";
        $prompt .= "3. Examples or options showing what the improvement could look like\n";
        $prompt .= "4. For content that can be improved (like professional summary, work descriptions), provide an AI-generated improved version based on the actual CV content\n";
        $prompt .= "5. Indicate whether the improvement can be automatically applied\n\n";
        
        $prompt .= "IMPORTANT: For recommendations about:\n";
        $prompt .= "- Professional summary: You MUST generate an actual improved version with quantifiable achievements based on the actual work experience in the CV. Extract real achievements, metrics, and responsibilities from the work experience section and incorporate them into the professional summary. Write the complete professional summary text (2-4 sentences) that the user can directly copy and use. DO NOT use placeholder text, brackets, or descriptions like '[Improved professional summary text based on actual CV content]' - write the actual improved summary text.\n";
        $prompt .= "- Work experience descriptions: Generate improved versions with metrics and achievements based on the actual responsibilities and work described. Write the complete improved description text.\n";
        $prompt .= "- Skills section: Suggest specific skills to add based on the job description\n";
        $prompt .= "- Formatting issues: Provide examples of correct formatting\n";
        $prompt .= "- Education/certifications: Provide guidance only (do not generate fake qualifications)\n";
        $prompt .= "- Employment gaps: Identify specific gaps and suggest how to address them (e.g., 'Add explanation for 6-month gap between X and Y', 'Consider adding a brief note about career break/travel/education during this period')\n";
        $prompt .= "- Missing dates: Flag any work experience entries missing start or end dates and recommend adding them\n\n";
        $prompt .= "CRITICAL RULES FOR ai_generated_improvement:\n";
        $prompt .= "1. You MUST write the actual improved text, not a description or placeholder\n";
        $prompt .= "2. DO NOT use brackets [ ] or placeholder text\n";
        $prompt .= "3. DO NOT write 'Here is an improved version:' or similar - just write the actual text\n";
        $prompt .= "4. The text must be complete and ready to use (e.g., for professional summary, write 2-4 complete sentences)\n";
        $prompt .= "5. If you cannot generate actual improved text based on the CV content, set ai_generated_improvement to null\n";
        $prompt .= "6. Extract real information from the CV (job titles, companies, achievements, metrics) and use it in the improvement\n\n";
        
        $prompt .= "\n\nCRITICAL INSTRUCTIONS:\n";
        $prompt .= "1. You MUST return ONLY valid JSON - no markdown, no explanations, no code blocks\n";
        $prompt .= "2. Do NOT start with \"Here is\" or any explanatory text\n";
        $prompt .= "3. Do NOT use markdown formatting (**bold**, lists with -, etc.)\n";
        $prompt .= "4. Your response must start with { and end with }\n";
        $prompt .= "5. Return ONLY the JSON object, nothing else\n\n";
        
        $prompt .= "Required JSON format (start your response with this exact structure):\n";
        $prompt .= "{\n";
        $prompt .= "  \"overall_score\": 85,\n";
        $prompt .= "  \"ats_score\": 80,\n";
        $prompt .= "  \"content_score\": 90,\n";
        $prompt .= "  \"formatting_score\": 75,\n";
        if ($jobDescription) {
            $prompt .= "  \"keyword_match_score\": 85,\n";
        }
        $prompt .= "  \"strengths\": [\"...\", \"...\"],\n";
        $prompt .= "  \"weaknesses\": [\"...\", \"...\"],\n";
        $prompt .= "  \"recommendations\": [\"...\", \"...\"],\n";
        $prompt .= "  \"enhanced_recommendations\": [\n";
        $prompt .= "    {\n";
        $prompt .= "      \"issue\": \"Lack of quantifiable achievements in professional summary\",\n";
        $prompt .= "      \"suggestion\": \"Add specific numbers and percentages to quantify achievements\",\n";
        $prompt .= "      \"examples\": [\"Increased sales by 25% over 2 years\", \"Managed team of 10 people\", \"Reduced costs by $50,000 annually\"],\n";
        $prompt .= "      \"ai_generated_improvement\": \"Experienced marketing professional with 8 years of expertise in digital marketing and team leadership. Successfully increased sales revenue by 25% over 2 years through strategic campaign development. Managed a team of 10 marketing specialists and reduced operational costs by $50,000 annually through process optimisation.\",\n";
        $prompt .= "      \"can_apply\": true,\n";
        $prompt .= "      \"improvement_type\": \"professional_summary\"\n";
        $prompt .= "    },\n";
        $prompt .= "    {\n";
        $prompt .= "      \"issue\": \"No mention of education or certifications\",\n";
        $prompt .= "      \"suggestion\": \"Include education and relevant certifications\",\n";
        $prompt .= "      \"examples\": [\"Add degree, institution, graduation year\", \"List relevant professional certifications\", \"Include ongoing education or training\"],\n";
        $prompt .= "      \"ai_generated_improvement\": null,\n";
        $prompt .= "      \"can_apply\": false,\n";
        $prompt .= "      \"improvement_type\": \"guidance_only\"\n";
        $prompt .= "    }\n";
        $prompt .= "  ]\n";
        $prompt .= "}\n\n";
        
        $prompt .= "REMEMBER: Return ONLY the JSON object above, starting with { and ending with }. No markdown, no explanations, no other text.\n";
        
        return $prompt;
    }
    
    /**
     * Format CV data as text for prompts
     * Truncates very long content to prevent model overload
     */
    private function formatCvForPrompt($cvData) {
        $text = "";
        $maxLength = 2000; // Limit total CV text to prevent prompt from being too long
        
        if (!empty($cvData['professional_summary']['description'])) {
            $summary = substr($cvData['professional_summary']['description'], 0, 500);
            $text .= "Professional Summary: " . $summary . "\n\n";
        }
        
        if (!empty($cvData['work_experience'])) {
            $text .= "Work Experience (in chronological order, most recent first):\n";
            $workCount = 0;
            $workEntries = [];
            foreach ($cvData['work_experience'] as $work) {
                if (strlen($text) > $maxLength) break;
                $workCount++;
                
                // Include dates for gap analysis
                $startDate = !empty($work['start_date']) ? date('Y-m', strtotime($work['start_date'])) : 'Unknown start';
                $endDate = !empty($work['end_date']) ? date('Y-m', strtotime($work['end_date'])) : 'Present';
                
                $entry = "- " . ($work['position'] ?? '') . " at " . ($work['company_name'] ?? '') . " (" . $startDate . " to " . $endDate . ")\n";
                if (!empty($work['description'])) {
                    $desc = substr($work['description'], 0, 300);
                    $entry .= "  " . $desc . "\n";
                }
                $text .= $entry;
                
                // Store for gap analysis
                if (!empty($work['start_date']) && !empty($work['end_date'])) {
                    $workEntries[] = [
                        'start' => strtotime($work['start_date']),
                        'end' => strtotime($work['end_date']),
                        'position' => $work['position'] ?? '',
                        'company' => $work['company_name'] ?? ''
                    ];
                }
            }
            if ($workCount < count($cvData['work_experience'])) {
                $text .= "... (" . (count($cvData['work_experience']) - $workCount) . " more entries)\n";
            }
            
            // Add gap analysis if we have multiple entries with dates
            if (count($workEntries) > 1) {
                // Sort by end date (most recent first)
                usort($workEntries, function($a, $b) {
                    return $b['end'] - $a['end'];
                });
                
                $gaps = [];
                for ($i = 0; $i < count($workEntries) - 1; $i++) {
                    $currentEnd = $workEntries[$i]['end'];
                    $nextStart = $workEntries[$i + 1]['start'];
                    $gapDays = ($nextStart - $currentEnd) / (60 * 60 * 24);
                    
                    // Flag gaps of more than 1 month (30 days)
                    if ($gapDays > 30) {
                        $gapMonths = round($gapDays / 30);
                        $gaps[] = [
                            'months' => $gapMonths,
                            'after' => $workEntries[$i]['position'] . ' at ' . $workEntries[$i]['company'],
                            'before' => $workEntries[$i + 1]['position'] . ' at ' . $workEntries[$i + 1]['company'],
                            'gap_start' => date('Y-m', $currentEnd),
                            'gap_end' => date('Y-m', $nextStart)
                        ];
                    }
                }
                
                if (!empty($gaps)) {
                    $text .= "\nDate Gaps Identified:\n";
                    foreach ($gaps as $gap) {
                        $text .= "  - " . $gap['months'] . " month gap between " . $gap['after'] . " (ended " . $gap['gap_start'] . ") and " . $gap['before'] . " (started " . $gap['gap_end'] . ")\n";
                    }
                }
            }
            
            $text .= "\n";
        }
        
        if (!empty($cvData['skills'])) {
            $skills = array_map(function($s) { return $s['name']; }, array_slice($cvData['skills'], 0, 20));
            $text .= "Skills: " . implode(', ', $skills);
            if (count($cvData['skills']) > 20) {
                $text .= " (and " . (count($cvData['skills']) - 20) . " more)";
            }
            $text .= "\n\n";
        }
        
        if (!empty($cvData['education'])) {
            $text .= "Education:\n";
            foreach (array_slice($cvData['education'], 0, 5) as $edu) {
                $text .= "- " . ($edu['degree'] ?? '') . " from " . ($edu['institution'] ?? '') . "\n";
            }
            if (count($cvData['education']) > 5) {
                $text .= "... (" . (count($cvData['education']) - 5) . " more entries)\n";
            }
            $text .= "\n";
        }
        
        return $text;
    }
    
    /**
     * Call AI service (Ollama, OpenAI, Anthropic, or Browser)
     */
    private function callAI($prompt, $options = []) {
        try {
            $imageData = $options['image_data'] ?? null;
            
            switch ($this->service) {
                case 'ollama':
                    // Ollama doesn't support vision yet, so we'll just use the prompt
                    // In the future, we could add vision support when available
                    return $this->callOllama($prompt, $options);
                case 'openai':
                    return $this->callOpenAI($prompt, $options, $imageData);
                case 'anthropic':
                    return $this->callAnthropic($prompt, $options, $imageData);
                case 'gemini':
                    return $this->callGemini($prompt, $options, $imageData);
                case 'grok':
                    return $this->callGrok($prompt, $options, $imageData);
                case 'browser':
                    // Browser AI runs client-side - return special response
                    return $this->callBrowserAI($prompt, $options);
                default:
                    return [
                        'success' => false,
                        'error' => 'Unknown AI service: ' . $this->service
                    ];
            }
        } catch (Exception $e) {
            error_log("AI Service Error: " . $e->getMessage());
            return [
                'success' => false,
                'error' => 'AI service error: ' . $e->getMessage()
            ];
        }
    }
    
    /**
     * Handle browser-based AI (signals frontend to execute client-side)
     * @param string $prompt The prompt to send
     * @param array $options Additional options
     * @return array Response indicating browser execution should occur
     */
    private function callBrowserAI($prompt, $options = []) {
        return [
            'success' => true,
            'browser_execution' => true,
            'prompt' => $prompt,
            'model' => $this->config['browser']['model'] ?? 'llama3.2',
            'model_type' => $this->config['browser']['model_type'] ?? 'webllm',
            'options' => $options,
            'message' => 'Browser AI execution required. Frontend will handle this request.'
        ];
    }
    
    /**
     * Call Ollama API (local, free)
     * Note: Ollama doesn't support vision yet, so images are ignored
     */
    private function callOllama($prompt, $options = []) {
        $url = $this->config['ollama']['base_url'] . '/api/generate';
        $model = $this->config['ollama']['model'];
        
        // Add system prompt for JSON-only responses (Ollama supports system prompts)
        $systemPrompt = "You are a CV assessment system. You MUST respond with valid JSON only. Do not include markdown formatting, explanatory text, or code blocks. Your response must start with { and end with }. Return ONLY the JSON object.";
        
        $data = [
            'model' => $model,
            'system' => $systemPrompt,
            'prompt' => $prompt,
            'stream' => false,
            'options' => [
                'temperature' => $options['temperature'] ?? 0.3, // Lower temperature for more structured output
                'num_predict' => $options['max_tokens'] ?? 2000,
            ]
        ];
        
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
        ]);
        curl_setopt($ch, CURLOPT_TIMEOUT, 180); // 3 minute timeout for local models (Ollama can be slow)
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10); // 10 second connection timeout
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        $curlErrno = curl_errno($ch);
        curl_close($ch);
        
        if ($curlErrno === CURLE_OPERATION_TIMEOUTED) {
            return [
                'success' => false,
                'error' => 'Ollama request timed out. The model may be taking too long to respond. Try using a smaller model or check Ollama is running properly.'
            ];
        }
        
        if ($error) {
            return [
                'success' => false,
                'error' => 'Ollama connection error: ' . $error . ' (Make sure Ollama is running: ollama serve)'
            ];
        }
        
        if ($httpCode !== 200) {
            $errorMsg = 'Ollama API error: HTTP ' . $httpCode;
            if ($response) {
                $errorData = json_decode($response, true);
                if (isset($errorData['error'])) {
                    $errorMsg .= ' - ' . $errorData['error'];
                } elseif ($httpCode === 500) {
                    $errorMsg .= ' - Server error. The model may have run out of memory or the prompt may be too long. Try using a smaller model or reducing CV content.';
                }
            } else {
                if ($httpCode === 500) {
                    $errorMsg .= ' - Server error. Ollama may have crashed or run out of memory. Check Ollama logs.';
                }
            }
            error_log("Ollama API Error (HTTP $httpCode): " . substr($response, 0, 500));
            return [
                'success' => false,
                'error' => $errorMsg,
                'response' => substr($response, 0, 200) // Limit response length for logging
            ];
        }
        
        $result = json_decode($response, true);
        
        if (!$result || !isset($result['response'])) {
            return [
                'success' => false,
                'error' => 'Invalid Ollama response format. Response: ' . substr($response, 0, 200),
                'response' => substr($response, 0, 200)
            ];
        }
        
        return [
            'success' => true,
            'content' => $result['response']
        ];
    }
    
    /**
     * Call OpenAI API
     */
    private function callOpenAI($prompt, $options = [], $imageData = null) {
        $apiKey = $this->config['openai']['api_key'];
        if (empty($apiKey)) {
            return [
                'success' => false,
                'error' => 'OpenAI API key not configured'
            ];
        }
        
        $url = $this->config['openai']['base_url'] . '/chat/completions';
        
        // Build content array - support text and images
        $content = [$prompt];
        if ($imageData) {
            $content[] = [
                'type' => 'image_url',
                'image_url' => [
                    'url' => 'data:' . $imageData['mime_type'] . ';base64,' . $imageData['base64']
                ]
            ];
        }
        
        // Use vision model if image is provided
        $model = $this->config['openai']['model'];
        if ($imageData) {
            // Switch to vision-capable model if not already using one
            if (strpos($model, 'gpt-4') === false && strpos($model, 'gpt-4o') === false) {
                $model = 'gpt-4o';
            }
        }
        
        $data = [
            'model' => $model,
            'messages' => [
                [
                    'role' => 'system',
                    'content' => 'You are a professional CV template designer. Analyze images and design references to create CV templates. Always return valid JSON responses.'
                ],
                [
                    'role' => 'user',
                    'content' => $content
                ]
            ],
            'temperature' => $options['temperature'] ?? 0.7,
            'max_tokens' => $options['max_tokens'] ?? 4000,
        ];
        
        // Only add response_format for non-vision models or if explicitly requested
        if (!$imageData) {
            $data['response_format'] = ['type' => 'json_object'];
        }
        
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
            'Authorization: Bearer ' . $apiKey
        ]);
        curl_setopt($ch, CURLOPT_TIMEOUT, 60);
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        curl_close($ch);
        
        if ($error) {
            return [
                'success' => false,
                'error' => 'OpenAI connection error: ' . $error
            ];
        }
        
        if ($httpCode !== 200) {
            return [
                'success' => false,
                'error' => 'OpenAI API error: HTTP ' . $httpCode,
                'response' => $response
            ];
        }
        
        $result = json_decode($response, true);
        
        if (!$result || !isset($result['choices'][0]['message']['content'])) {
            return [
                'success' => false,
                'error' => 'Invalid OpenAI response format',
                'response' => $response
            ];
        }
        
        return [
            'success' => true,
            'content' => $result['choices'][0]['message']['content']
        ];
    }
    
    /**
     * Call Anthropic API
     */
    private function callAnthropic($prompt, $options = [], $imageData = null) {
        $apiKey = $this->config['anthropic']['api_key'];
        if (empty($apiKey)) {
            return [
                'success' => false,
                'error' => 'Anthropic API key not configured'
            ];
        }
        
        $url = $this->config['anthropic']['base_url'] . '/messages';
        
        // Build content array - support text and images
        $content = [
            [
                'type' => 'text',
                'text' => $prompt
            ]
        ];
        
        if ($imageData) {
            $content[] = [
                'type' => 'image',
                'source' => [
                    'type' => 'base64',
                    'media_type' => $imageData['mime_type'],
                    'data' => $imageData['base64']
                ]
            ];
        }
        
        $data = [
            'model' => $this->config['anthropic']['model'],
            'max_tokens' => $options['max_tokens'] ?? 4000,
            'temperature' => $options['temperature'] ?? 0.7,
            'system' => 'You are a professional CV template designer. Analyze images and design references to create CV templates. Always return valid JSON responses.',
            'messages' => [
                [
                    'role' => 'user',
                    'content' => $content
                ]
            ]
        ];
        
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
            'x-api-key: ' . $apiKey,
            'anthropic-version: 2023-06-01'
        ]);
        curl_setopt($ch, CURLOPT_TIMEOUT, 60);
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        curl_close($ch);
        
        if ($error) {
            return [
                'success' => false,
                'error' => 'Anthropic connection error: ' . $error
            ];
        }
        
        if ($httpCode !== 200) {
            return [
                'success' => false,
                'error' => 'Anthropic API error: HTTP ' . $httpCode,
                'response' => $response
            ];
        }
        
        $result = json_decode($response, true);
        
        if (!$result || !isset($result['content'][0]['text'])) {
            return [
                'success' => false,
                'error' => 'Invalid Anthropic response format',
                'response' => $response
            ];
        }
        
        return [
            'success' => true,
            'content' => $result['content'][0]['text']
        ];
    }
    
    /**
     * Call Google Gemini API
     */
    private function callGemini($prompt, $options = [], $imageData = null) {
        $apiKey = $this->config['gemini']['api_key'];
        if (empty($apiKey)) {
            return [
                'success' => false,
                'error' => 'Gemini API key not configured'
            ];
        }
        
        $model = $this->config['gemini']['model'];
        $url = $this->config['gemini']['base_url'] . '/models/' . $model . ':generateContent?key=' . urlencode($apiKey);
        
        // Build content parts - support text and images
        $parts = [
            ['text' => $prompt]
        ];
        
        if ($imageData) {
            $parts[] = [
                'inline_data' => [
                    'mime_type' => $imageData['mime_type'],
                    'data' => $imageData['base64']
                ]
            ];
            // Use vision model if image is provided
            if (strpos($model, 'vision') === false && strpos($model, 'gemini-pro-vision') === false) {
                $model = 'gemini-pro-vision';
                $url = $this->config['gemini']['base_url'] . '/models/' . $model . ':generateContent?key=' . urlencode($apiKey);
            }
        }
        
        $data = [
            'contents' => [
                [
                    'parts' => $parts
                ]
            ],
            'generationConfig' => [
                'temperature' => $options['temperature'] ?? 0.7,
                'maxOutputTokens' => $options['max_tokens'] ?? 4000,
            ]
        ];
        
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
        ]);
        curl_setopt($ch, CURLOPT_TIMEOUT, 60);
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        curl_close($ch);
        
        if ($error) {
            return [
                'success' => false,
                'error' => 'Gemini connection error: ' . $error
            ];
        }
        
        if ($httpCode !== 200) {
            $errorData = json_decode($response, true);
            $errorMessage = $errorData['error']['message'] ?? 'Gemini API error: HTTP ' . $httpCode;
            return [
                'success' => false,
                'error' => $errorMessage,
                'response' => $response
            ];
        }
        
        $result = json_decode($response, true);
        
        if (!$result || !isset($result['candidates'][0]['content']['parts'][0]['text'])) {
            return [
                'success' => false,
                'error' => 'Invalid Gemini response format',
                'response' => $response
            ];
        }
        
        return [
            'success' => true,
            'content' => $result['candidates'][0]['content']['parts'][0]['text']
        ];
    }
    
    /**
     * Call xAI Grok API
     */
    private function callGrok($prompt, $options = [], $imageData = null) {
        $apiKey = $this->config['grok']['api_key'];
        if (empty($apiKey)) {
            return [
                'success' => false,
                'error' => 'Grok API key not configured'
            ];
        }
        
        $url = $this->config['grok']['base_url'] . '/chat/completions';
        $model = $this->config['grok']['model'];
        
        // Build messages array
        $messages = [
            [
                'role' => 'system',
                'content' => 'You are a professional CV writer and analyst. Always return valid JSON responses when requested.'
            ],
            [
                'role' => 'user',
                'content' => $prompt
            ]
        ];
        
        // Note: Grok API may support images in future, but for now we'll use text only
        // If imageData is provided, we'll include it as a note in the prompt
        if ($imageData) {
            $messages[1]['content'] = $prompt . "\n\n[Note: An image reference was provided but Grok API image support is not yet implemented in this integration]";
        }
        
        $data = [
            'model' => $model,
            'messages' => $messages,
            'temperature' => $options['temperature'] ?? 0.7,
            'max_tokens' => $options['max_tokens'] ?? 4000,
        ];
        
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
            'Authorization: Bearer ' . $apiKey
        ]);
        curl_setopt($ch, CURLOPT_TIMEOUT, 60);
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        curl_close($ch);
        
        if ($error) {
            return [
                'success' => false,
                'error' => 'Grok connection error: ' . $error
            ];
        }
        
        if ($httpCode !== 200) {
            $errorData = json_decode($response, true);
            $errorMessage = $errorData['error']['message'] ?? 'Grok API error: HTTP ' . $httpCode;
            return [
                'success' => false,
                'error' => $errorMessage,
                'response' => $response
            ];
        }
        
        $result = json_decode($response, true);
        
        if (!$result || !isset($result['choices'][0]['message']['content'])) {
            return [
                'success' => false,
                'error' => 'Invalid Grok response format',
                'response' => $response
            ];
        }
        
        return [
            'success' => true,
            'content' => $result['choices'][0]['message']['content']
        ];
    }
    
    /**
     * Build prompt for CV template generation
     */
    private function buildTemplateGenerationPrompt($cvData, $userDescription, $options = []) {
        $prompt = "You are a professional CV template designer. Generate a custom CV template based on the user's description.\n\n";
        
        $prompt .= "User's Design Description:\n" . $userDescription . "\n\n";
        
        $prompt .= "CV Data Structure Available:\n";
        $prompt .= "- Profile: full_name, email, phone, location, linkedin_url, bio, photo_url\n";
        $prompt .= "- Professional Summary: description\n";
        $prompt .= "- Work Experience: company_name, position, start_date, end_date, description, responsibility_categories\n";
        $prompt .= "- Education: degree, institution, field_of_study, start_date, end_date\n";
        $prompt .= "- Skills: name, category, level (grouped by category)\n";
        $prompt .= "- Projects: title, description, technologies, url\n";
        $prompt .= "- Certifications: name, issuer, date_obtained, expiry_date\n";
        $prompt .= "- Professional Memberships: name, organisation, start_date\n";
        $prompt .= "- Interests: name, description\n\n";
        
        $prompt .= "Requirements:\n";
        $prompt .= "1. Must use Tailwind CSS classes (no inline styles except where necessary)\n";
        $prompt .= "2. Must be responsive (mobile-friendly)\n";
        $prompt .= "3. Must be print-friendly (use print media queries)\n";
        $prompt .= "4. Must maintain accessibility (proper headings, alt text, semantic HTML)\n";
        $prompt .= "5. Include all CV sections mentioned above\n";
        $prompt .= "6. Use PHP variables for dynamic content: <?php echo e(\$variable); ?>\n";
        $prompt .= "7. For arrays/loops, use PHP foreach: <?php foreach (\$array as \$item): ?>...<?php endforeach; ?>\n";
        $prompt .= "8. For conditional display: <?php if (!empty(\$data)): ?>...<?php endif; ?>\n";
        $prompt .= "9. Use the formatCvDate() function for dates: <?php echo formatCvDate(\$date); ?>\n\n";
        
        if (!empty($options['layout_preference'])) {
            $prompt .= "Layout Preference: " . $options['layout_preference'] . "\n\n";
        }
        
        if (!empty($options['color_scheme'])) {
            $prompt .= "Color Scheme: " . $options['color_scheme'] . "\n\n";
        }
        
        $prompt .= "CRITICAL: You MUST return ONLY valid JSON. No markdown, no explanations, no code blocks - just pure JSON.\n\n";
        $prompt .= "Output Format (JSON only):\n";
        $prompt .= "{\n";
        $prompt .= "  \"html\": \"Complete HTML structure with PHP variables for CV data\",\n";
        $prompt .= "  \"css\": \"Additional custom CSS (if needed, otherwise empty string)\",\n";
        $prompt .= "  \"instructions\": \"Brief description of the template design\"\n";
        $prompt .= "}\n\n";
        
        $prompt .= "IMPORTANT JSON REQUIREMENTS:\n";
        $prompt .= "- Return ONLY the JSON object, nothing else\n";
        $prompt .= "- Escape all quotes inside strings using backslash: \\\"\n";
        $prompt .= "- Escape all backslashes: \\\\\n";
        $prompt .= "- Do NOT wrap the JSON in markdown code blocks\n";
        $prompt .= "- Do NOT include any text before or after the JSON\n";
        $prompt .= "- Ensure all strings are properly quoted\n";
        $prompt .= "- Ensure all brackets and braces are properly matched\n\n";
        
        $prompt .= "Template Requirements:\n";
        $prompt .= "- The HTML should be a complete, self-contained template section\n";
        $prompt .= "- Use Tailwind utility classes for styling\n";
        $prompt .= "- Ensure proper semantic HTML structure\n";
        $prompt .= "- Make it visually appealing and professional\n";
        $prompt .= "- Include proper spacing and typography\n";
        $prompt .= "- The template will be inserted into a page that already has header/footer\n";
        $prompt .= "- Use \$profile for profile data, \$cvData for CV sections\n";
        $prompt .= "- Example: <?php echo e(\$profile['full_name']); ?>\n";
        $prompt .= "- Example: <?php foreach (\$cvData['work_experience'] as \$work): ?>...<?php endforeach; ?>\n";
        
        return $prompt;
    }
    
    /**
     * Generate a custom homepage template based on organisation description or URL reference
     * @param array $orgData The organisation data structure
     * @param string $userDescription User's description of desired design
     * @param array $options Additional options (reference URL, image path, etc.)
     */
    public function generateHomepageTemplate($orgData, $userDescription, $options = []) {
        $prompt = $this->buildHomepageTemplatePrompt($orgData, $userDescription, $options);
        
        // Prepare image data if provided
        $imageData = null;
        if (!empty($options['reference_image_path']) && file_exists($options['reference_image_path'])) {
            $imageData = [
                'path' => $options['reference_image_path'],
                'base64' => base64_encode(file_get_contents($options['reference_image_path'])),
                'mime_type' => mime_content_type($options['reference_image_path'])
            ];
        }
        
        $response = $this->callAI($prompt, [
            'temperature' => 0.7,
            'max_tokens' => 8000, // HTML/CSS can be long
            'image_data' => $imageData
        ]);
        
        if (!$response['success']) {
            return [
                'success' => false,
                'error' => $response['error'] ?? 'Template generation failed',
                'raw_response' => $response['raw_response'] ?? null
            ];
        }
        
        $content = $response['content'] ?? '';
        $template = $this->parseJsonResponse($content);
        
        if (!$template || !isset($template['html'])) {
            return [
                'success' => false,
                'error' => 'Failed to parse AI response. The AI may not have returned valid JSON.',
                'raw_response' => $content
            ];
        }
        
        // Validate template
        $validation = $this->validateHomepageTemplate($template);
        if (!$validation['valid']) {
            return [
                'success' => false,
                'error' => $validation['error'] ?? 'Template validation failed',
                'raw_response' => $content
            ];
        }
        
        return [
            'success' => true,
            'html' => $template['html'],
            'css' => $template['css'] ?? '',
            'instructions' => $template['instructions'] ?? ''
        ];
    }
    
    /**
     * Build prompt for homepage template generation
     */
    private function buildHomepageTemplatePrompt($orgData, $userDescription, $options = []) {
        $prompt = "You are a professional website designer. Generate a custom homepage template for a recruitment agency organisation based on the user's description.\n\n";
        
        $prompt .= "User's Design Description:\n" . $userDescription . "\n\n";
        
        if (!empty($options['reference_url'])) {
            $prompt .= "Reference URL: " . $options['reference_url'] . "\n";
            $prompt .= "Use this URL as inspiration for the design, layout, and styling. Adapt it for a recruitment agency homepage.\n\n";
        }
        
        $prompt .= "Organisation Data Available:\n";
        $prompt .= "- Name: " . ($orgData['name'] ?? 'N/A') . "\n";
        $prompt .= "- Slug: " . ($orgData['slug'] ?? 'N/A') . "\n";
        $prompt .= "- Logo URL: " . ($orgData['logo_url'] ?? 'Not set') . "\n";
        $prompt .= "- Primary Colour: " . ($orgData['primary_colour'] ?? '#4338ca') . "\n";
        $prompt .= "- Secondary Colour: " . ($orgData['secondary_colour'] ?? '#7e22ce') . "\n";
        $prompt .= "- Candidate Count: " . ($orgData['candidate_count'] ?? 0) . "\n";
        $prompt .= "- Public URL: " . ($orgData['public_url'] ?? '') . "\n\n";
        
        $prompt .= "Requirements:\n";
        $prompt .= "1. Must use Tailwind CSS classes (no inline styles except where necessary)\n";
        $prompt .= "2. Must be responsive (mobile-friendly)\n";
        $prompt .= "3. Must maintain accessibility (proper headings, alt text, semantic HTML)\n";
        $prompt .= "4. Use placeholder variables for dynamic content: {{organisation_name}}, {{logo_url}}, {{primary_colour}}, {{secondary_colour}}, {{candidate_count}}, {{public_url}}\n";
        $prompt .= "5. Include sections suitable for a recruitment agency (hero, features, testimonials, call-to-action)\n";
        $prompt .= "6. Make it visually appealing and professional\n";
        $prompt .= "7. Use the organisation's primary and secondary colours for branding\n";
        $prompt .= "8. The template will be inserted into a page that already has header/footer\n\n";
        
        $prompt .= "CRITICAL: You MUST return ONLY valid JSON. No markdown, no explanations, no code blocks - just pure JSON.\n\n";
        $prompt .= "Output Format (JSON only):\n";
        $prompt .= "{\n";
        $prompt .= "  \"html\": \"Complete HTML structure with placeholder variables\",\n";
        $prompt .= "  \"css\": \"Additional custom CSS (if needed, otherwise empty string)\",\n";
        $prompt .= "  \"instructions\": \"Brief description of the template design\"\n";
        $prompt .= "}\n\n";
        
        $prompt .= "IMPORTANT JSON REQUIREMENTS:\n";
        $prompt .= "- Return ONLY the JSON object, nothing else\n";
        $prompt .= "- Escape all quotes inside strings using backslash: \\\"\n";
        $prompt .= "- Escape all backslashes: \\\\\n";
        $prompt .= "- Do NOT wrap the JSON in markdown code blocks\n";
        $prompt .= "- Do NOT include any text before or after the JSON\n";
        $prompt .= "- Ensure all strings are properly quoted\n";
        $prompt .= "- Ensure all brackets and braces are properly matched\n\n";
        
        $prompt .= "Template Requirements:\n";
        $prompt .= "- The HTML should be a complete, self-contained homepage section\n";
        $prompt .= "- Use Tailwind utility classes for styling\n";
        $prompt .= "- Ensure proper semantic HTML structure\n";
        $prompt .= "- Replace dynamic values with placeholders like {{organisation_name}}\n";
        $prompt .= "- Example: <h1>{{organisation_name}}</h1>\n";
        $prompt .= "- Example: <div style=\"background-color: {{primary_colour}}\">...</div>\n";
        
        return $prompt;
    }
    
    /**
     * Validate generated homepage template for security and structure
     */
    private function validateHomepageTemplate($template) {
        if (!isset($template['html']) || empty($template['html'])) {
            return ['valid' => false, 'error' => 'HTML content is missing'];
        }
        
        // Check for dangerous patterns
        $dangerousPatterns = [
            '/<script/i',
            '/javascript:/i',
            '/on\w+\s*=/i', // onclick, onload, etc.
            '/<iframe/i',
            '/<object/i',
            '/<embed/i',
            '/@import/i', // CSS imports
            '/url\(javascript:/i',
            '/expression\(/i', // CSS expressions
        ];
        
        $html = $template['html'];
        $css = $template['css'] ?? '';
        $combined = $html . $css;
        
        foreach ($dangerousPatterns as $pattern) {
            if (preg_match($pattern, $combined)) {
                return ['valid' => false, 'error' => 'Template contains potentially dangerous code'];
            }
        }
        
        // Basic HTML structure check
        if (!preg_match('/<div|<section|<article/i', $html)) {
            return ['valid' => false, 'error' => 'Template must include proper HTML structure'];
        }
        
        return ['valid' => true];
    }
    
    /**
     * Validate generated template for security and structure
     */
    private function validateTemplate($template) {
        if (!isset($template['html']) || empty($template['html'])) {
            return ['valid' => false, 'error' => 'HTML content is missing'];
        }
        
        // Check for dangerous patterns
        $dangerousPatterns = [
            '/<script/i',
            '/javascript:/i',
            '/on\w+\s*=/i', // onclick, onload, etc.
            '/<iframe/i',
            '/<object/i',
            '/<embed/i',
            '/@import/i', // CSS imports
            '/url\(javascript:/i',
            '/expression\(/i', // CSS expressions
        ];
        
        $html = $template['html'];
        $css = $template['css'] ?? '';
        $combined = $html . $css;
        
        foreach ($dangerousPatterns as $pattern) {
            if (preg_match($pattern, $combined)) {
                return ['valid' => false, 'error' => 'Template contains potentially dangerous code'];
            }
        }
        
        // Check for required PHP variables (at least some should be present)
        if (!preg_match('/<\?php/', $html)) {
            return ['valid' => false, 'error' => 'Template must include PHP code for dynamic content'];
        }
        
        // Basic HTML structure check
        if (!preg_match('/<div|<section|<article/i', $html)) {
            return ['valid' => false, 'error' => 'Template must include proper HTML structure'];
        }
        
        return ['valid' => true];
    }
    
    /**
     * Parse JSON from AI response (handles markdown code blocks)
     */
    private function parseJsonResponse($content) {
        if (empty($content)) {
            return null;
        }
        
        $originalContent = $content;
        
        // Remove markdown code blocks if present
        $content = preg_replace('/```json\s*/i', '', $content);
        $content = preg_replace('/```\s*/', '', $content);
        $content = trim($content);
        
        // Remove explanatory text before JSON (e.g., "Here is the custom CV template in JSON format:")
        // Look for common patterns and remove everything before the first {
        if (preg_match('/^[^{]*(\{[\s\S]*\})/s', $content, $matches)) {
            $content = $matches[1];
        }
        
        // Try to decode first (may work if AI properly escaped)
        $decoded = json_decode($content, true);
        $firstError = json_last_error();
        
        if ($firstError !== JSON_ERROR_NONE) {
            // Fix control characters: escape newlines, tabs, and other control chars in string values
            // This is a more sophisticated fix that handles unescaped control characters
            $fixed = $this->fixJsonControlCharacters($content);
            
            // Try again with fixed content
            $decoded = json_decode($fixed, true);
            $secondError = json_last_error();
            
            // If still failing, try additional fixes
            if ($secondError !== JSON_ERROR_NONE) {
                // Fix trailing commas
                $fixed = preg_replace('/,\s*}/', '}', $fixed);
                $fixed = preg_replace('/,\s*]/', ']', $fixed);
                
                $decoded = json_decode($fixed, true);
            }
        }
        
        return $decoded;
    }
    
    /**
     * Fix unescaped control characters in JSON string values
     * Escapes newlines, tabs, and other control characters that break JSON parsing
     */
    private function fixJsonControlCharacters($json) {
        $result = '';
        $inString = false;
        $escapeNext = false;
        $i = 0;
        $len = strlen($json);
        
        while ($i < $len) {
            $char = $json[$i];
            
            if ($escapeNext) {
                $result .= $char;
                $escapeNext = false;
                $i++;
                continue;
            }
            
            if ($char === '\\') {
                $result .= $char;
                $escapeNext = true;
                $i++;
                continue;
            }
            
            if ($char === '"') {
                $inString = !$inString;
                $result .= $char;
                $i++;
                continue;
            }
            
            if ($inString) {
                // Inside a string - escape control characters
                if (ord($char) < 32 && $char !== "\t" && $char !== "\n" && $char !== "\r") {
                    // Control character (except tab, newline, carriage return which we'll handle)
                    $result .= '\\u' . sprintf('%04x', ord($char));
                } elseif ($char === "\n") {
                    $result .= '\\n';
                } elseif ($char === "\r") {
                    $result .= '\\r';
                } elseif ($char === "\t") {
                    $result .= '\\t';
                } else {
                    $result .= $char;
                }
            } else {
                // Outside string - keep as is
                $result .= $char;
            }
            
            $i++;
        }
        
        return $result;
    }
    
    /**
     * Validate and normalize assessment structure
     */
    private function validateAssessment($assessment) {
        $validated = [
            'overall_score' => isset($assessment['overall_score']) ? (int)$assessment['overall_score'] : 0,
            'ats_score' => isset($assessment['ats_score']) ? (int)$assessment['ats_score'] : 0,
            'content_score' => isset($assessment['content_score']) ? (int)$assessment['content_score'] : 0,
            'formatting_score' => isset($assessment['formatting_score']) ? (int)$assessment['formatting_score'] : 0,
            'keyword_match_score' => isset($assessment['keyword_match_score']) ? (int)$assessment['keyword_match_score'] : null,
            'strengths' => isset($assessment['strengths']) && is_array($assessment['strengths']) ? $assessment['strengths'] : [],
            'weaknesses' => isset($assessment['weaknesses']) && is_array($assessment['weaknesses']) ? $assessment['weaknesses'] : [],
            'recommendations' => isset($assessment['recommendations']) && is_array($assessment['recommendations']) ? $assessment['recommendations'] : [],
            'enhanced_recommendations' => isset($assessment['enhanced_recommendations']) && is_array($assessment['enhanced_recommendations']) ? $assessment['enhanced_recommendations'] : [],
        ];
        
        // Validate enhanced recommendations structure
        if (!empty($validated['enhanced_recommendations'])) {
            foreach ($validated['enhanced_recommendations'] as &$rec) {
                if (!is_array($rec)) {
                    $rec = ['issue' => (string)$rec, 'suggestion' => '', 'examples' => [], 'can_apply' => false];
                } else {
                    // Filter out placeholder text from AI-generated improvements
                    $aiImprovement = $rec['ai_generated_improvement'] ?? null;
                    if ($aiImprovement) {
                        // List of placeholder patterns to detect and reject
                        $placeholderPatterns = [
                            '/\[Improved.*?\]/i',
                            '/\[.*?based on.*?CV.*?\]/i',
                            '/\[.*?text.*?\]/i',
                            '/placeholder/i',
                            '/example.*?text/i',
                            '/improved.*?version.*?here/i',
                            '/your.*?improved.*?text.*?here/i',
                        ];
                        
                        $isPlaceholder = false;
                        foreach ($placeholderPatterns as $pattern) {
                            if (preg_match($pattern, $aiImprovement)) {
                                $isPlaceholder = true;
                                break;
                            }
                        }
                        
                        // Also check if it's too short or looks like a description rather than actual content
                        if (!$isPlaceholder && (strlen(trim($aiImprovement)) < 50 || 
                            preg_match('/^(This|Here|The|An|A)\s+(improved|better|enhanced)/i', trim($aiImprovement)))) {
                            $isPlaceholder = true;
                        }
                        
                        if ($isPlaceholder) {
                            // Remove placeholder - don't show it to users
                            $aiImprovement = null;
                            // If it was marked as can_apply, change it to guidance_only
                            if (($rec['can_apply'] ?? false) && ($rec['improvement_type'] ?? '') === 'professional_summary') {
                                $rec['can_apply'] = false;
                                $rec['improvement_type'] = 'guidance_only';
                            }
                        }
                    }
                    
                    $rec = [
                        'issue' => $rec['issue'] ?? '',
                        'suggestion' => $rec['suggestion'] ?? '',
                        'examples' => isset($rec['examples']) && is_array($rec['examples']) ? $rec['examples'] : [],
                        'ai_generated_improvement' => $aiImprovement,
                        'can_apply' => isset($rec['can_apply']) ? (bool)$rec['can_apply'] : false,
                        'improvement_type' => $rec['improvement_type'] ?? 'guidance_only',
                    ];
                }
            }
        }
        
        // Clamp scores to 0-100
        foreach (['overall_score', 'ats_score', 'content_score', 'formatting_score', 'keyword_match_score'] as $key) {
            if ($validated[$key] !== null) {
                $validated[$key] = max(0, min(100, $validated[$key]));
            }
        }
        
        return $validated;
    }
}

/**
 * Get AI service instance
 */
function getAIService($userId = null) {
    // If no user ID provided, try to get current user
    if (!$userId && function_exists('getCurrentUser')) {
        try {
            $user = getCurrentUser();
            $userId = $user['id'] ?? null;
        } catch (Exception $e) {
            // User not logged in or function not available
            $userId = null;
        }
    }
    
    // Create instance with user ID for user-specific settings
    return new AIService($userId);
}

