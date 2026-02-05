<?php
/**
 * Job Application Questions API
 * CRUD for application form questions and answers
 */

require_once __DIR__ . '/../php/helpers.php';
require_once __DIR__ . '/../php/job-applications.php';

header('Content-Type: application/json');

if (!isLoggedIn()) {
    http_response_code(401);
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

$userId = getUserId();
$method = $_SERVER['REQUEST_METHOD'];
$input = [];
if ($method === 'GET') {
    $applicationId = $_GET['application_id'] ?? null;
} else {
    $raw = file_get_contents('php://input');
    if ($raw !== false) {
        $decoded = json_decode($raw, true);
        $input = is_array($decoded) ? $decoded : [];
    }
    if (empty($input)) {
        if ($method === 'POST' || $method === 'PATCH' || $method === 'PUT') {
            $input = $_POST;
        }
    }
    $applicationId = $input['application_id'] ?? null;
    $questionId = $input['question_id'] ?? null;
    $questionText = $input['question_text'] ?? null;
    $answerText = $input['answer_text'] ?? null;
    $answerInstructions = isset($input['answer_instructions']) ? $input['answer_instructions'] : null;
    $sortOrder = isset($input['sort_order']) ? (int) $input['sort_order'] : 0;
}

$csrfToken = $input['csrf_token'] ?? $_GET['csrf_token'] ?? '';

try {
    switch ($method) {
        case 'GET':
            if (!$applicationId) {
                http_response_code(400);
                echo json_encode(['error' => 'application_id required']);
                exit;
            }
            $job = getJobApplication($applicationId, $userId);
            if (!$job) {
                http_response_code(404);
                echo json_encode(['error' => 'Application not found']);
                exit;
            }
            $questions = getJobApplicationQuestions($applicationId, $userId);
            echo json_encode(['success' => true, 'questions' => $questions]);
            break;

        case 'POST':
            if (!verifyCsrfToken($csrfToken)) {
                http_response_code(403);
                echo json_encode(['error' => 'Invalid CSRF token']);
                exit;
            }
            if (!$applicationId || $questionText === null || trim($questionText) === '') {
                http_response_code(400);
                echo json_encode(['error' => 'application_id and question_text required']);
                exit;
            }
            $result = addJobApplicationQuestion($applicationId, $userId, trim($questionText), $sortOrder, $answerInstructions);
            if (!$result['success']) {
                http_response_code(400);
                echo json_encode(['error' => $result['error']]);
                exit;
            }
            http_response_code(201);
            echo json_encode(['success' => true, 'id' => $result['id']]);
            break;

        case 'PATCH':
        case 'PUT':
            if (!verifyCsrfToken($csrfToken)) {
                http_response_code(403);
                echo json_encode(['error' => 'Invalid CSRF token']);
                exit;
            }
            if (!$questionId) {
                http_response_code(400);
                echo json_encode(['error' => 'question_id required']);
                exit;
            }
            $fields = [];
            if (array_key_exists('answer_text', $input)) {
                $fields['answer_text'] = $input['answer_text'];
            }
            if (array_key_exists('answer_instructions', $input)) {
                $fields['answer_instructions'] = $input['answer_instructions'];
            }
            if (empty($fields)) {
                http_response_code(400);
                echo json_encode(['error' => 'Provide answer_text and/or answer_instructions']);
                exit;
            }
            $result = updateJobApplicationQuestionFields($questionId, $userId, $fields);
            if (!$result['success']) {
                http_response_code(400);
                echo json_encode(['error' => $result['error']]);
                exit;
            }
            echo json_encode(['success' => true]);
            break;

        case 'DELETE':
            if (!verifyCsrfToken($csrfToken)) {
                http_response_code(403);
                echo json_encode(['error' => 'Invalid CSRF token']);
                exit;
            }
            $qid = $questionId ?? ($input['question_id'] ?? $_GET['question_id'] ?? null);
            if (!$qid) {
                http_response_code(400);
                echo json_encode(['error' => 'question_id required']);
                exit;
            }
            $result = deleteJobApplicationQuestion($qid, $userId);
            if (!$result['success']) {
                http_response_code(400);
                echo json_encode(['error' => $result['error']]);
                exit;
            }
            echo json_encode(['success' => true]);
            break;

        default:
            http_response_code(405);
            echo json_encode(['error' => 'Method not allowed']);
    }
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Internal server error']);
}
