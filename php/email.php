<?php
/**
 * Email sending functions
 */

require_once __DIR__ . '/config.php';

/**
 * Send email using PHP mail() function
 * In production, consider using a service like SendGrid, Mailgun, or AWS SES
 */
function sendEmail($to, $subject, $message, $fromEmail = null, $fromName = null) {
    $fromEmail = $fromEmail ?: env('MAIL_FROM_EMAIL', 'noreply@' . parse_url(APP_URL, PHP_URL_HOST));
    $fromName = $fromName ?: env('MAIL_FROM_NAME', 'CV App');

    // #region agent log
    debugLog([
        'id' => 'log_' . time() . '_' . uniqid(),
        'timestamp' => time() * 1000,
        'location' => 'email.php:12',
        'message' => 'sendEmail called',
        'data' => [
            'to' => $to,
            'subject' => $subject,
            'fromEmail' => $fromEmail,
            'fromName' => $fromName,
            'messageLength' => strlen($message),
            'appUrl' => APP_URL ?? 'not set'
        ],
        'sessionId' => 'email-debug',
        'runId' => 'run1',
        'hypothesisId' => 'E1,E2,E3'
    ]);
    // #endregion

    $headers = [
        'MIME-Version: 1.0',
        'Content-type: text/html; charset=UTF-8',
        'From: ' . $fromName . ' <' . $fromEmail . '>',
        'Reply-To: ' . $fromEmail,
        'X-Mailer: PHP/' . phpversion()
    ];

    $headersString = implode("\r\n", $headers);

    // #region agent log
    debugLog([
        'id' => 'log_' . time() . '_' . uniqid(),
        'timestamp' => time() * 1000,
        'location' => 'email.php:25',
        'message' => 'About to call mail()',
        'data' => [
            'to' => $to,
            'subject' => $subject,
            'headersPreview' => substr($headersString, 0, 200),
            'headersLength' => strlen($headersString)
        ],
        'sessionId' => 'email-debug',
        'runId' => 'run1',
        'hypothesisId' => 'E1,E2'
    ]);
    // #endregion

    $result = @mail($to, $subject, $message, $headersString);
    
    // Get last error if mail() failed
    $lastError = error_get_last();
    
    // #region agent log
    debugLog([
        'id' => 'log_' . time() . '_' . uniqid(),
        'timestamp' => time() * 1000,
        'location' => 'email.php:26',
        'message' => 'mail() result',
        'data' => [
            'result' => $result,
            'resultType' => gettype($result),
            'lastError' => $lastError,
            'phpMailEnabled' => function_exists('mail'),
            'to' => $to
        ],
        'sessionId' => 'email-debug',
        'runId' => 'run1',
        'hypothesisId' => 'E1,E2,E3'
    ]);
    // #endregion

    return $result;
}

/**
 * Send email verification email
 */
function sendVerificationEmail($email, $fullName, $verificationToken) {
    $verifyUrl = APP_URL . '/verify-email.php?token=' . urlencode($verificationToken);

    $subject = 'Verify your email address';

    $message = '
    <!DOCTYPE html>
    <html>
    <head>
        <meta charset="UTF-8">
        <style>
            body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
            .container { max-width: 600px; margin: 0 auto; padding: 20px; }
            .button { display: inline-block; padding: 12px 24px; background-color: #2563eb; color: #ffffff; text-decoration: none; border-radius: 5px; margin: 20px 0; }
            .button:hover { background-color: #1d4ed8; }
            .footer { margin-top: 30px; font-size: 12px; color: #666; }
        </style>
    </head>
    <body>
        <div class="container">
            <h1>Welcome to CV App!</h1>
            <p>Hello ' . htmlspecialchars($fullName ?: 'there') . ',</p>
            <p>Thank you for registering with CV App. Please verify your email address by clicking the button below:</p>
            <p><a href="' . htmlspecialchars($verifyUrl) . '" class="button">Verify Email Address</a></p>
            <p>Or copy and paste this link into your browser:</p>
            <p style="word-break: break-all; color: #2563eb;">' . htmlspecialchars($verifyUrl) . '</p>
            <p>This link will expire in 24 hours.</p>
            <p>If you did not create an account, please ignore this email.</p>
            <div class="footer">
                <p>Best regards,<br>The CV App Team</p>
            </div>
        </div>
    </body>
    </html>';

    return sendEmail($email, $subject, $message);
}

/**
 * Send password reset email
 */
function sendPasswordResetEmail($email, $fullName, $resetToken) {
    $resetUrl = APP_URL . '/reset-password.php?token=' . urlencode($resetToken);

    $subject = 'Reset your password';

    $message = '
    <!DOCTYPE html>
    <html>
    <head>
        <meta charset="UTF-8">
        <style>
            body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
            .container { max-width: 600px; margin: 0 auto; padding: 20px; }
            .button { display: inline-block; padding: 12px 24px; background-color: #2563eb; color: #ffffff; text-decoration: none; border-radius: 5px; margin: 20px 0; }
            .button:hover { background-color: #1d4ed8; }
            .footer { margin-top: 30px; font-size: 12px; color: #666; }
        </style>
    </head>
    <body>
        <div class="container">
            <h1>Password Reset Request</h1>
            <p>Hello ' . htmlspecialchars($fullName ?: 'there') . ',</p>
            <p>We received a request to reset your password. Click the button below to choose a new password:</p>
            <p><a href="' . htmlspecialchars($resetUrl) . '" class="button">Reset Password</a></p>
            <p>If the button does not work, copy and paste this link into your browser:</p>
            <p style="word-break: break-all; color: #2563eb;">' . htmlspecialchars($resetUrl) . '</p>
            <p>This link will expire in 1 hour. If you did not request a password reset, you can safely ignore this email.</p>
            <div class="footer">
                <p>Best regards,<br>The CV App Team</p>
            </div>
        </div>
    </body>
    </html>';

    return sendEmail($email, $subject, $message);
}

/**
 * Send username reminder email
 */
function sendUsernameReminderEmail($email, $fullName, $username) {
    $subject = 'Your CV App username';

    $message = '
    <!DOCTYPE html>
    <html>
    <head>
        <meta charset="UTF-8">
        <style>
            body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
            .container { max-width: 600px; margin: 0 auto; padding: 20px; }
            .footer { margin-top: 30px; font-size: 12px; color: #666; }
        </style>
    </head>
    <body>
        <div class="container">
            <h1>Your CV App Username</h1>
            <p>Hello ' . htmlspecialchars($fullName ?: 'there') . ',</p>
            <p>You recently asked for a reminder of your username. Here it is:</p>
            <p style="font-size: 20px; font-weight: bold; color: #2563eb;">' . htmlspecialchars($username) . '</p>
            <p>You can log in at <a href="' . htmlspecialchars(APP_URL) . '">' . htmlspecialchars(APP_URL) . '</a> using your email address and this username.</p>
            <p>If you did not request this email, you can ignore it.</p>
            <div class="footer">
                <p>Best regards,<br>The CV App Team</p>
            </div>
        </div>
    </body>
    </html>';

    return sendEmail($email, $subject, $message);
}

/**
 * Send candidate invitation email
 * @param string $email Recipient email
 * @param string|null $fullName Recipient full name
 * @param string $organisationName Organisation name
 * @param string $inviterName Name of person sending invitation
 * @param string $token Invitation token
 * @param string|null $personalMessage Optional personal message
 * @param string|null $organisationEmail Optional organisation email address to send from
 * @param string|null $organisationEmailName Optional organisation email display name
 */
function sendCandidateInvitationEmail($email, $fullName, $organisationName, $inviterName, $token, $personalMessage = null, $organisationEmail = null, $organisationEmailName = null) {
    // #region agent log
    debugLog([
        'id' => 'log_' . time() . '_' . uniqid(),
        'timestamp' => time() * 1000,
        'location' => 'email.php:148',
        'message' => 'sendCandidateInvitationEmail called',
        'data' => [
            'email' => $email,
            'fullName' => $fullName,
            'organisationName' => $organisationName,
            'inviterName' => $inviterName,
            'tokenLength' => strlen($token),
            'hasPersonalMessage' => !empty($personalMessage),
            'appUrl' => APP_URL ?? 'not set'
        ],
        'sessionId' => 'email-debug',
        'runId' => 'run1',
        'hypothesisId' => 'E1,E2,E3,E4'
    ]);
    // #endregion
    
    $acceptUrl = APP_URL . '/accept-invitation.php?token=' . urlencode($token) . '&type=candidate';

    $subject = 'You\'ve been invited to create your CV with ' . $organisationName;

    $personalMessageHtml = $personalMessage
        ? '<div style="background-color: #f3f4f6; padding: 15px; border-radius: 5px; margin: 20px 0;">
               <p style="margin: 0; font-style: italic;">"' . htmlspecialchars($personalMessage) . '"</p>
               <p style="margin: 10px 0 0 0; font-size: 12px; color: #666;">- ' . htmlspecialchars($inviterName) . '</p>
           </div>'
        : '';

    $message = '
    <!DOCTYPE html>
    <html>
    <head>
        <meta charset="UTF-8">
        <style>
            body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
            .container { max-width: 600px; margin: 0 auto; padding: 20px; }
            .button { display: inline-block; padding: 12px 24px; background-color: #2563eb; color: #ffffff; text-decoration: none; border-radius: 5px; margin: 20px 0; }
            .button:hover { background-color: #1d4ed8; }
            .footer { margin-top: 30px; font-size: 12px; color: #666; }
        </style>
    </head>
    <body>
        <div class="container">
            <h1>You\'re Invited!</h1>
            <p>Hello ' . htmlspecialchars($fullName ?: 'there') . ',</p>
            <p><strong>' . htmlspecialchars($inviterName) . '</strong> from <strong>' . htmlspecialchars($organisationName) . '</strong> has invited you to create your professional CV using our platform.</p>
            ' . $personalMessageHtml . '
            <p>Click the button below to accept the invitation and get started:</p>
            <p><a href="' . htmlspecialchars($acceptUrl) . '" class="button">Accept Invitation</a></p>
            <p>Or copy and paste this link into your browser:</p>
            <p style="word-break: break-all; color: #2563eb;">' . htmlspecialchars($acceptUrl) . '</p>
            <p>This invitation will expire in 7 days.</p>
            <div class="footer">
                <p>Best regards,<br>The ' . htmlspecialchars($organisationName) . ' Team</p>
            </div>
        </div>
    </body>
    </html>';

    // #region agent log
    debugLog([
        'id' => 'log_' . time() . '_' . uniqid(),
        'timestamp' => time() * 1000,
        'location' => 'email.php:274',
        'message' => 'About to call sendEmail for candidate invitation',
        'data' => [
            'email' => $email,
            'subject' => $subject,
            'messageLength' => strlen($message),
            'acceptUrl' => $acceptUrl
        ],
        'sessionId' => 'email-debug',
        'runId' => 'run1',
        'hypothesisId' => 'E1,E2'
    ]);
    // #endregion

    // Use organisation email if provided, otherwise fall back to default
    $fromEmail = $organisationEmail;
    $fromName = $organisationEmailName ?: ($organisationName . ' Team');
    
    $emailResult = sendEmail($email, $subject, $message, $fromEmail, $fromName);
    
    // #region agent log
    debugLog([
        'id' => 'log_' . time() . '_' . uniqid(),
        'timestamp' => time() * 1000,
        'location' => 'email.php:292',
        'message' => 'sendEmail returned for candidate invitation',
        'data' => [
            'emailResult' => $emailResult,
            'emailResultType' => gettype($emailResult),
            'email' => $email
        ],
        'sessionId' => 'email-debug',
        'runId' => 'run1',
        'hypothesisId' => 'E1,E2,E3'
    ]);
    // #endregion

    return $emailResult;
}

/**
 * Send team member invitation email
 * @param string $email Recipient email
 * @param string $organisationName Organisation name
 * @param string $role Team member role
 * @param string $inviterName Name of person sending invitation
 * @param string $token Invitation token
 * @param string|null $personalMessage Optional personal message
 * @param string|null $organisationEmail Optional organisation email address to send from
 * @param string|null $organisationEmailName Optional organisation email display name
 */
function sendTeamInvitationEmail($email, $organisationName, $role, $inviterName, $token, $personalMessage = null, $organisationEmail = null, $organisationEmailName = null) {
    $acceptUrl = APP_URL . '/accept-invitation.php?token=' . urlencode($token) . '&type=team';

    $roleDescriptions = [
        'admin' => 'an Administrator with full access to manage candidates and team settings',
        'recruiter' => 'a Recruiter with the ability to invite and manage candidates',
        'viewer' => 'a Viewer with read-only access to candidate information'
    ];

    $roleDescription = $roleDescriptions[$role] ?? 'a team member';

    $subject = 'You\'ve been invited to join ' . $organisationName;

    $personalMessageHtml = $personalMessage
        ? '<div style="background-color: #f3f4f6; padding: 15px; border-radius: 5px; margin: 20px 0;">
               <p style="margin: 0; font-style: italic;">"' . htmlspecialchars($personalMessage) . '"</p>
               <p style="margin: 10px 0 0 0; font-size: 12px; color: #666;">- ' . htmlspecialchars($inviterName) . '</p>
           </div>'
        : '';

    $message = '
    <!DOCTYPE html>
    <html>
    <head>
        <meta charset="UTF-8">
        <style>
            body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
            .container { max-width: 600px; margin: 0 auto; padding: 20px; }
            .button { display: inline-block; padding: 12px 24px; background-color: #2563eb; color: #ffffff; text-decoration: none; border-radius: 5px; margin: 20px 0; }
            .button:hover { background-color: #1d4ed8; }
            .footer { margin-top: 30px; font-size: 12px; color: #666; }
            .role-badge { display: inline-block; background-color: #dbeafe; color: #1e40af; padding: 4px 12px; border-radius: 15px; font-weight: bold; }
        </style>
    </head>
    <body>
        <div class="container">
            <h1>Join Our Team!</h1>
            <p>Hello,</p>
            <p><strong>' . htmlspecialchars($inviterName) . '</strong> has invited you to join <strong>' . htmlspecialchars($organisationName) . '</strong> as ' . $roleDescription . '.</p>
            <p>Your role: <span class="role-badge">' . ucfirst(htmlspecialchars($role)) . '</span></p>
            ' . $personalMessageHtml . '
            <p>Click the button below to accept the invitation and join the team:</p>
            <p><a href="' . htmlspecialchars($acceptUrl) . '" class="button">Accept Invitation</a></p>
            <p>Or copy and paste this link into your browser:</p>
            <p style="word-break: break-all; color: #2563eb;">' . htmlspecialchars($acceptUrl) . '</p>
            <p>This invitation will expire in 7 days.</p>
            <div class="footer">
                <p>Best regards,<br>The ' . htmlspecialchars($organisationName) . ' Team</p>
            </div>
        </div>
    </body>
    </html>';

    // Use organisation email if provided, otherwise fall back to default
    $fromEmail = $organisationEmail;
    $fromName = $organisationEmailName ?: ($organisationName . ' Team');
    
    return sendEmail($email, $subject, $message, $fromEmail, $fromName);
}
