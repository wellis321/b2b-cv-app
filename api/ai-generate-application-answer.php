<?php
/**
 * AI Application Answer Generation API
 * Generates tailored answers to application form questions using job description and CV
 */

ob_start();
set_time_limit(120);

require_once __DIR__ . '/../php/helpers.php';
require_once __DIR__ . '/../php/ai-service.php';
require_once __DIR__ . '/../php/cv-data.php';
require_once __DIR__ . '/../php/job-applications.php';
require_once __DIR__ . '/../php/cv-variants.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    ob_end_clean();
    echo json_encode(['success' => false, 'error' => 'Method not allowed']);
    exit;
}

if (!isLoggedIn()) {
    http_response_code(401);
    ob_end_clean();
    echo json_encode(['success' => false, 'error' => 'Unauthorized']);
    exit;
}

if (!verifyCsrfToken($_POST['csrf_token'] ?? '')) {
    http_response_code(403);
    ob_end_clean();
    echo json_encode(['success' => false, 'error' => 'Invalid CSRF token']);
    exit;
}

$userId = getUserId();
$applicationId = $_POST['application_id'] ?? null;
$questionId = $_POST['question_id'] ?? null;
$questionText = $_POST['question_text'] ?? null;
$cvVariantId = $_POST['cv_variant_id'] ?? null;
$postAnswerInstructions = isset($_POST['answer_instructions']) ? trim($_POST['answer_instructions']) : null;
$browserAiResult = trim($_POST['answer_text'] ?? ''); // When browser AI already ran, client sends answer_text

if (!$applicationId) {
    ob_end_clean();
    echo json_encode(['success' => false, 'error' => 'Application ID is required']);
    exit;
}

$job = getJobApplication($applicationId, $userId);
if (!$job) {
    ob_end_clean();
    echo json_encode(['success' => false, 'error' => 'Job application not found']);
    exit;
}

// If client sends answer_text (browser AI already executed), save and return
if ($browserAiResult !== '' && $questionId) {
    $updated = updateJobApplicationQuestionAnswer($questionId, $userId, $browserAiResult);
    if (!$updated['success']) {
        ob_end_clean();
        echo json_encode(['success' => false, 'error' => $updated['error'] ?? 'Failed to save answer']);
        exit;
    }
    ob_end_clean();
    echo json_encode(['success' => true, 'answer_text' => $browserAiResult]);
    exit;
}

// Resolve question text
if ($questionId) {
    $questions = getJobApplicationQuestions($applicationId, $userId);
    $found = null;
    foreach ($questions as $q) {
        if ($q['id'] === $questionId) {
            $found = $q;
            break;
        }
    }
    if (!$found) {
        ob_end_clean();
        echo json_encode(['success' => false, 'error' => 'Question not found']);
        exit;
    }
    $questionText = $found['question_text'];
    $answerInstructions = isset($found['answer_instructions']) ? $found['answer_instructions'] : null;
} else {
    $answerInstructions = null;
}
if (!isset($questionText) || $questionText === null || trim($questionText) === '') {
    ob_end_clean();
    echo json_encode(['success' => false, 'error' => 'Question text or question_id is required']);
    exit;
} else {
    $questionText = trim($questionText);
}
if (!isset($answerInstructions)) {
    $answerInstructions = null;
}
// Allow client to override instructions for this request (e.g. from form before save)
if ($postAnswerInstructions !== null && $postAnswerInstructions !== '') {
    $answerInstructions = $postAnswerInstructions;
}

// Load CV data
if ($cvVariantId) {
    $variant = getCvVariant($cvVariantId, $userId);
    if (!$variant) {
        ob_end_clean();
        echo json_encode(['success' => false, 'error' => 'CV variant not found']);
        exit;
    }
    $cvData = loadCvVariantData($cvVariantId);
} else {
    $cvData = loadCvData($userId);
}

if (!$cvData || empty($cvData)) {
    ob_end_clean();
    echo json_encode(['success' => false, 'error' => 'No CV data found. Please create your CV first.']);
    exit;
}

$aiService = new AIService($userId);
$result = $aiService->generateApplicationAnswer($cvData, $job, $questionText, [
    'answer_instructions' => $answerInstructions,
]);

if (!$result['success']) {
    ob_end_clean();
    echo json_encode(['success' => false, 'error' => $result['error'] ?? 'Failed to generate answer']);
    exit;
}

if (isset($result['browser_execution']) && $result['browser_execution']) {
    ob_end_clean();
    echo json_encode([
        'success' => true,
        'browser_execution' => true,
        'prompt' => $result['prompt'] ?? '',
        'model' => $result['model'] ?? 'llama3.2',
        'model_type' => $result['model_type'] ?? 'webllm',
        'question_id' => $questionId,
        'application_id' => $applicationId,
    ]);
    exit;
}

$answerText = $result['answer_text'] ?? '';
if ($questionId) {
    updateJobApplicationQuestionAnswer($questionId, $userId, $answerText);
}

ob_end_clean();
echo json_encode(['success' => true, 'answer_text' => $answerText]);
