<?php
// Auth modals and JavaScript for login/register functionality
// This partial can be included on any page that needs login/register buttons

// Get flash messages if available (for initial modal state)
$error = getFlash('error') ?: null;
$success = getFlash('success') ?: null;
$needsVerification = getFlash('needs_verification') ?: false;
$verificationEmail = getFlash('verification_email') ?: null;
$oldLoginEmail = getFlash('old_login_email') ?: null;
?>

<!-- Auth Modals -->
<div data-modal="login" class="fixed inset-0 z-50 hidden overflow-y-auto" role="dialog" aria-modal="true" aria-labelledby="login-modal-title">
    <div class="flex min-h-full items-center justify-center px-4 py-10 text-center sm:block sm:p-0">
        <div class="fixed inset-0 bg-gray-900/60 transition-opacity" data-close-modal></div>

        <div class="relative inline-block w-full max-w-md transform rounded-2xl bg-white px-6 py-6 text-left align-bottom shadow-xl transition-all sm:my-8 sm:align-middle sm:p-8">
            <button type="button" class="absolute right-4 top-4 text-gray-400 hover:text-gray-600" data-close-modal aria-label="Close">
                <svg class="h-5 w-5" viewBox="0 0 24 24" stroke="currentColor" fill="none">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
            <div class="mb-4">
                <h3 class="text-2xl font-semibold text-gray-900" id="login-modal-title">Welcome back</h3>
                <p class="mt-1 text-sm text-gray-500">Log in to continue editing and sharing your CV.</p>
            </div>
            <div data-modal-message class="mb-4 hidden rounded-md border px-4 py-3 text-sm font-medium"></div>
            <form method="POST" action="/">
                <input type="hidden" name="action" value="login">
                <input type="hidden" name="<?php echo CSRF_TOKEN_NAME; ?>" value="<?php echo csrfToken(); ?>">

                <div class="mb-4">
                    <label for="modal-login-email" class="block text-sm font-medium text-gray-700 mb-2">Email</label>
                    <input type="email"
                           id="modal-login-email"
                           name="email"
                           value="<?php echo isset($oldLoginEmail) ? e($oldLoginEmail) : ''; ?>"
                           required
                           class="w-full rounded-md border border-gray-300 px-3 py-2 shadow-sm focus:border-blue-500 focus:outline-none focus:ring-blue-500">
                </div>

                <div class="mb-4">
                    <label for="modal-login-password" class="block text-sm font-medium text-gray-700 mb-2">Password</label>
                    <input type="password"
                           id="modal-login-password"
                           name="password"
                           required
                           class="w-full rounded-md border border-gray-300 px-3 py-2 shadow-sm focus:border-blue-500 focus:outline-none focus:ring-blue-500">
                </div>

                <button type="submit" class="w-full rounded-md bg-blue-600 px-4 py-2 text-sm font-semibold text-white shadow hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2">
                    Log in
                </button>

                <div class="mt-4 text-right text-xs text-gray-500 space-y-1">
                    <div>
                        <a href="/forgot-password.php" class="text-blue-600 hover:text-blue-800">Forgot your password?</a>
                    </div>
                    <div>
                        <a href="/forgot-username.php" class="text-blue-600 hover:text-blue-800">Forgot your username?</a>
                    </div>
                    <div>
                        <a href="/resend-verification.php" class="text-blue-600 hover:text-blue-800">Resend verification email</a>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

<div data-modal="register" class="fixed inset-0 z-50 hidden overflow-y-auto" role="dialog" aria-modal="true" aria-labelledby="register-modal-title">
    <div class="flex min-h-full items-center justify-center px-4 py-10 text-center sm:block sm:p-0">
        <div class="fixed inset-0 bg-gray-900/60 transition-opacity" data-close-modal></div>

        <div class="relative inline-block w-full max-w-lg transform rounded-2xl bg-white px-6 py-6 text-left align-bottom shadow-xl transition-all sm:my-8 sm:align-middle sm:p-8">
            <button type="button" class="absolute right-4 top-4 text-gray-400 hover:text-gray-600" data-close-modal aria-label="Close">
                <svg class="h-5 w-5" viewBox="0 0 24 24" stroke="currentColor" fill="none">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
            <div class="mb-4">
                <h3 class="text-2xl font-semibold text-gray-900" id="register-modal-title">Create your free account</h3>
                <p class="mt-1 text-sm text-gray-500">We'll guide you through building a standout CV in minutes.</p>
            </div>
            <div data-modal-message class="mb-4 hidden rounded-md border px-4 py-3 text-sm font-medium"></div>
            <form method="POST" action="/">
                <input type="hidden" name="action" value="register">
                <input type="hidden" name="<?php echo CSRF_TOKEN_NAME; ?>" value="<?php echo csrfToken(); ?>">

                <div class="grid gap-4 sm:grid-cols-2">
                    <div class="sm:col-span-2">
                        <label for="modal-register-full-name" class="block text-sm font-medium text-gray-700 mb-2">Full name</label>
                        <input type="text"
                               id="modal-register-full-name"
                               name="full_name"
                               required
                               class="w-full rounded-md border border-gray-300 px-3 py-2 shadow-sm focus:border-blue-500 focus:outline-none focus:ring-blue-500">
                    </div>
                    <div class="sm:col-span-2">
                        <label for="modal-register-email" class="block text-sm font-medium text-gray-700 mb-2">Email</label>
                        <input type="email"
                               id="modal-register-email"
                               name="email"
                               required
                               class="w-full rounded-md border border-gray-300 px-3 py-2 shadow-sm focus:border-blue-500 focus:outline-none focus:ring-blue-500">
                    </div>
                    <div>
                        <label for="modal-register-password" class="block text-sm font-medium text-gray-700 mb-2">Password</label>
                        <input type="password"
                               id="modal-register-password"
                               name="password"
                               minlength="<?php echo PASSWORD_MIN_LENGTH; ?>"
                               required
                               class="w-full rounded-md border border-gray-300 px-3 py-2 shadow-sm focus:border-blue-500 focus:outline-none focus:ring-blue-500">
                    </div>
                    <div>
                        <label for="modal-register-password-confirm" class="block text-sm font-medium text-gray-700 mb-2">Confirm password</label>
                        <input type="password"
                               id="modal-register-password-confirm"
                               name="password_confirm"
                               required
                               class="w-full rounded-md border border-gray-300 px-3 py-2 shadow-sm focus:border-blue-500 focus:outline-none focus:ring-blue-500">
                    </div>
                </div>

                <p class="mt-3 text-xs text-gray-500">
                    Passwords must be at least <?php echo PASSWORD_MIN_LENGTH; ?> characters and include lowercase, uppercase, and a number.
                </p>

                <button type="submit" class="mt-6 w-full rounded-md bg-blue-600 px-4 py-2 text-sm font-semibold text-white shadow hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2">
                    Create your account
                </button>

                <p class="mt-4 text-xs text-gray-500 text-center">
                    By signing up you agree to our <a href="/terms.php" class="text-blue-600 underline hover:text-blue-800">Terms of Service</a> and <a href="/privacy.php" class="text-blue-600 underline hover:text-blue-800">Privacy Policy</a>.
                </p>
            </form>
        </div>
    </div>
</div>

<script>
    (() => {
        const body = document.body;
        const modalMap = new Map();

        document.querySelectorAll('[data-modal]').forEach((modal) => {
            modalMap.set(modal.getAttribute('data-modal'), modal);
        });

        const messageVariants = {
            success: 'border-green-200 bg-green-50 text-green-800',
            error: 'border-red-200 bg-red-50 text-red-800'
        };

        const toggleBodyScroll = (disable) => {
            body.classList.toggle('overflow-hidden', disable);
        };

        const closeModal = (modal) => {
            if (!modal) return;
            modal.classList.add('hidden');
            modal.setAttribute('aria-hidden', 'true');
            toggleBodyScroll(false);
        };

        const openModal = (modalName, options = {}) => {
            const modal = modalMap.get(modalName);
            if (!modal) return;

            modal.classList.remove('hidden');
            modal.setAttribute('aria-hidden', 'false');
            toggleBodyScroll(true);

            const messageBox = modal.querySelector('[data-modal-message]');
            if (messageBox) {
                if (options.message) {
                    const classes = messageVariants[options.variant] || messageVariants.error;
                    messageBox.className = `mb-4 rounded-md border px-4 py-3 text-sm font-medium ${classes}`;
                    messageBox.innerHTML = options.message;
                    messageBox.classList.remove('hidden');
                } else {
                    messageBox.classList.add('hidden');
                    messageBox.innerHTML = '';
                }
            }

            const firstInput = modal.querySelector('input[type="email"], input[type="text"], input[type="password"]');
            if (firstInput) {
                setTimeout(() => firstInput.focus(), 80);
            }
        };

        document.querySelectorAll('[data-open-login]').forEach((trigger) => {
            trigger.addEventListener('click', () => openModal('login'));
        });

        document.querySelectorAll('[data-open-register]').forEach((trigger) => {
            trigger.addEventListener('click', () => openModal('register'));
        });

        document.querySelectorAll('[data-close-modal]').forEach((closeTrigger) => {
            closeTrigger.addEventListener('click', (event) => {
                const modal = event.target.closest('[data-modal]');
                closeModal(modal);
            });
        });

        window.addEventListener('keydown', (event) => {
            if (event.key === 'Escape') {
                modalMap.forEach((modal) => {
                    if (!modal.classList.contains('hidden')) {
                        closeModal(modal);
                    }
                });
            }
        });

        <?php
        // Only show initial modal state if we're on the homepage (where flash messages are set)
        // For other pages, modals will only open when buttons are clicked
        $currentPage = basename($_SERVER['PHP_SELF'] ?? 'index.php');
        if ($currentPage === 'index.php'):
            $initialModal = null;
            if (!empty($success)) {
                $initialModal = 'login';
            } elseif (!empty($error)) {
                $initialModal = !empty($oldLoginEmail) ? 'login' : 'register';
            }

            $initialMessage = '';
            $initialVariant = null;
            if (!empty($success)) {
                $initialMessage = e($success);
                $initialVariant = 'success';
            } elseif (!empty($error)) {
                $initialMessage = e($error);
                if (!empty($needsVerification) && !empty($verificationEmail)) {
                    $initialMessage .= '<br><span class="font-normal text-xs">Need a new verification email? <a href="/resend-verification.php?email=' . urlencode($verificationEmail) . '" class="underline">Click here</a>.</span>';
                }
                $initialVariant = 'error';
            }

            $authState = [
                'open' => $initialModal,
                'message' => $initialMessage,
                'variant' => $initialVariant,
            ];
        else:
            $authState = [
                'open' => null,
                'message' => '',
                'variant' => null,
            ];
        endif;

        echo 'const authState = ' . json_encode($authState) . ';';
        ?>

        if (authState.open) {
            openModal(authState.open, {
                message: authState.message,
                variant: authState.variant
            });
        }
    })();
</script>
