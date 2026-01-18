<?php
/**
 * Create Candidate Invitation
 * Standardized form page for inviting candidates
 */

require_once __DIR__ . '/../../php/helpers.php';

// Require authentication and recruiter access
$org = requireOrganisationAccess('recruiter');

$user = getCurrentUser();
$error = getFlash('error');
$success = getFlash('success');

// Check if organisation can add more candidates
if (!canAddCandidate($org['organisation_id'])) {
    setFlash('error', 'Your organisation has reached its candidate limit. Please request a limit increase in Settings.');
    redirect('/agency/candidates.php');
}

// Get team members for assignment dropdown (if admin/owner)
$teamMembers = [];
if (in_array($org['role'], ['owner', 'admin'])) {
    $teamMembers = getOrganisationTeamMembers($org['organisation_id']);
}

// Handle form submission
if (isPost()) {
    if (!verifyCsrfToken(post(CSRF_TOKEN_NAME))) {
        setFlash('error', 'Invalid security token. Please try again.');
        redirect('/agency/candidates/create.php');
    }

    $email = sanitizeInput(post('email'));
    $fullName = sanitizeInput(post('full_name'));
    $message = sanitizeInput(post('message'));
    $assignedRecruiter = sanitizeInput(post('assigned_recruiter'));

    // Validate email
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        setFlash('error', 'Please enter a valid email address.');
        redirect('/agency/candidates/create.php');
    }

    // Set assigned recruiter (default to current user if recruiter, or selected if admin)
    if ($org['role'] === 'recruiter') {
        $assignedRecruiter = getUserId();
    } elseif (empty($assignedRecruiter)) {
        $assignedRecruiter = getUserId();
    }

    // Create invitation
    $result = createCandidateInvitation(
        $org['organisation_id'],
        $email,
        getUserId(),
        $fullName,
        $assignedRecruiter,
        $message
    );

    if ($result['success']) {
        // Check if we're on localhost and email might not be sent
        $emailSent = $result['email_sent'] ?? false;
        $isLocalhost = (parse_url(APP_URL, PHP_URL_HOST) === 'localhost' || 
                       strpos(parse_url(APP_URL, PHP_URL_HOST), '127.0.0.1') === 0 ||
                       parse_url(APP_URL, PHP_URL_HOST) === 'localhost:8000');
        
        if ($isLocalhost && !$emailSent) {
            setFlash('warning', 'Invitation created successfully, but email delivery may not work on localhost. The invitation link can be shared manually from the pending invitations list.');
        } elseif (!$emailSent) {
            setFlash('warning', 'Invitation created successfully, but the email may not have been sent. Please check your email configuration.');
        } else {
            setFlash('success', 'Invitation sent successfully to ' . $email);
        }
        redirect('/agency/candidates.php');
    } else {
        setFlash('error', $result['error']);
        redirect('/agency/candidates/create.php');
    }
}

// Get pending invitations
$pendingInvitations = getPendingCandidateInvitations($org['organisation_id']);

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <?php partial('head', [
        'pageTitle' => 'Invite Candidate | ' . e($org['organisation_name']),
        'metaDescription' => 'Send an invitation to a candidate to create their CV.',
        'canonicalUrl' => APP_URL . '/agency/candidates/create.php',
        'metaNoindex' => true,
    ]); ?>
</head>
<body class="bg-gray-50">
    <?php partial('agency/header'); ?>

    <main id="main-content" class="py-6">
        <!-- Error/Success Messages -->
        <?php if ($error): ?>
            <div class="max-w-2xl mx-auto px-4 sm:px-6 lg:px-8 mb-6">
                <div class="rounded-md bg-red-50 p-4">
                    <p class="text-sm font-medium text-red-800"><?php echo e($error); ?></p>
                </div>
            </div>
        <?php endif; ?>

        <?php if ($success): ?>
            <div class="max-w-2xl mx-auto px-4 sm:px-6 lg:px-8 mb-6">
                <div class="rounded-md bg-green-50 p-4">
                    <p class="text-sm font-medium text-green-800"><?php echo e($success); ?></p>
                </div>
            </div>
        <?php endif; ?>

        <!-- Form Card -->
        <?php ob_start(); ?>
        <form method="POST" class="space-y-6">
            <input type="hidden" name="<?php echo CSRF_TOKEN_NAME; ?>" value="<?php echo csrfToken(); ?>">

            <?php partial('forms/form-field', [
                'type' => 'email',
                'name' => 'email',
                'label' => 'Email Address',
                'required' => true,
                'placeholder' => 'candidate@example.com',
                'help' => 'The candidate will receive an invitation email at this address.'
            ]); ?>

            <?php partial('forms/form-field', [
                'type' => 'text',
                'name' => 'full_name',
                'label' => 'Full Name',
                'placeholder' => 'John Smith',
                'help' => 'Optional. This will pre-fill the candidate\'s name in the invitation.'
            ]); ?>

            <?php if (in_array($org['role'], ['owner', 'admin']) && !empty($teamMembers)): ?>
                <?php
                $recruiterOptions = [getUserId() => 'Myself'];
                foreach ($teamMembers as $member) {
                    if ($member['user_id'] !== getUserId() && in_array($member['role'], ['owner', 'admin', 'recruiter'])) {
                        $recruiterOptions[$member['user_id']] = ($member['full_name'] ?? $member['email']) . ' (' . ucfirst($member['role']) . ')';
                    }
                }
                ?>
                <?php partial('forms/form-field', [
                    'type' => 'select',
                    'name' => 'assigned_recruiter',
                    'label' => 'Assign to Recruiter',
                    'options' => $recruiterOptions,
                    'value' => getUserId(),
                    'help' => 'Select who will manage this candidate.'
                ]); ?>
            <?php endif; ?>

            <?php partial('forms/form-field', [
                'type' => 'textarea',
                'name' => 'message',
                'label' => 'Personal Message',
                'rows' => 3,
                'placeholder' => 'We\'re excited to help you create your professional CV...',
                'help' => 'Optional. Add a personal note to the invitation email.'
            ]); ?>

            <?php partial('forms/form-actions', [
                'submitText' => 'Send Invitation',
                'cancelUrl' => '/agency/candidates.php',
                'cancelText' => 'Cancel'
            ]); ?>
        </form>
        <?php $formContent = ob_get_clean(); ?>

        <?php partial('forms/form-card', [
            'title' => 'Invite Candidate',
            'description' => 'Send an invitation to a candidate to create their CV with your organisation.',
            'backUrl' => '/agency/candidates.php',
            'backText' => 'Back to candidates',
            'content' => $formContent
        ]); ?>

        <!-- Pending Invitations -->
        <?php if (!empty($pendingInvitations)): ?>
            <div class="max-w-2xl mx-auto px-4 sm:px-6 lg:px-8 mt-8">
                <h2 class="text-lg font-medium text-gray-900 mb-4">Pending Invitations</h2>
                <div class="bg-white shadow rounded-lg overflow-hidden">
                    <ul class="divide-y divide-gray-200">
                        <?php foreach ($pendingInvitations as $invitation): ?>
                            <li class="p-4">
                                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                                    <div>
                                        <p class="text-sm font-medium text-gray-900"><?php echo e($invitation['email']); ?></p>
                                        <p class="text-xs text-gray-500 mt-1">
                                            <?php if ($invitation['full_name']): ?>
                                                <?php echo e($invitation['full_name']); ?> -
                                            <?php endif; ?>
                                            Invited <?php echo date('j M Y', strtotime($invitation['created_at'])); ?>
                                            - Expires <?php echo date('j M Y', strtotime($invitation['expires_at'])); ?>
                                        </p>
                                    </div>
                                    <div class="flex items-center gap-2">
                                        <form method="POST" action="/api/agency/resend-invitation.php" class="inline">
                                            <input type="hidden" name="<?php echo CSRF_TOKEN_NAME; ?>" value="<?php echo csrfToken(); ?>">
                                            <input type="hidden" name="invitation_id" value="<?php echo e($invitation['id']); ?>">
                                            <input type="hidden" name="type" value="candidate">
                                            <button type="submit" class="text-sm text-blue-600 hover:text-blue-800 px-3 py-1 rounded hover:bg-blue-50">Resend</button>
                                        </form>
                                        <form method="POST" action="/api/agency/cancel-invitation.php" class="inline" onsubmit="return confirm('Cancel this invitation?');">
                                            <input type="hidden" name="<?php echo CSRF_TOKEN_NAME; ?>" value="<?php echo csrfToken(); ?>">
                                            <input type="hidden" name="invitation_id" value="<?php echo e($invitation['id']); ?>">
                                            <input type="hidden" name="type" value="candidate">
                                            <button type="submit" class="text-sm text-red-600 hover:text-red-800 px-3 py-1 rounded hover:bg-red-50">Cancel</button>
                                        </form>
                                    </div>
                                </div>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            </div>
        <?php endif; ?>

    </main>

    <?php partial('footer'); ?>
</body>
</html>

