<?php
/**
 * CV Variants Management Page
 * List, create, and manage CV variants (job-specific CVs)
 */

require_once __DIR__ . '/php/helpers.php';

requireAuth();

$user = getCurrentUser();
$error = getFlash('error');
$success = getFlash('success');

// Get or create master variant first (ensures it exists)
$masterVariantId = getOrCreateMasterVariant($user['id']);

// Get all CV variants for user (should now include master)
$variants = getUserCvVariants($user['id']);

// Handle delete
if (isPost() && isset($_POST['action']) && $_POST['action'] === 'delete') {
    $token = post(CSRF_TOKEN_NAME);
    if (!verifyCsrfToken($token)) {
        setFlash('error', 'Invalid security token.');
        redirect('/cv-variants.php');
    }
    
    $variantId = post('variant_id');
    $result = deleteCvVariant($variantId, $user['id']);
    
    if ($result['success']) {
        setFlash('success', 'CV variant deleted successfully.');
    } else {
        setFlash('error', $result['error'] ?? 'Failed to delete CV variant.');
    }
    
    redirect('/cv-variants.php');
}

// Handle rename
if (isPost() && isset($_POST['action']) && $_POST['action'] === 'rename') {
    $token = post(CSRF_TOKEN_NAME);
    if (!verifyCsrfToken($token)) {
        setFlash('error', 'Invalid security token.');
        redirect('/cv-variants.php');
    }
    
    $variantId = post('variant_id');
    $variantName = post('variant_name');
    
    if (empty($variantName)) {
        setFlash('error', 'Variant name cannot be empty.');
        redirect('/cv-variants.php');
    }
    
    $result = updateCvVariantName($variantId, $variantName, $user['id']);
    
    if ($result['success']) {
        setFlash('success', 'Variant name updated successfully.');
    } else {
        setFlash('error', $result['error'] ?? 'Failed to update variant name.');
    }
    
    redirect('/cv-variants.php');
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <?php partial('head', [
        'pageTitle' => 'CV Variants | Simple CV Builder',
        'metaDescription' => 'Manage your CV variants for different job applications.',
        'canonicalUrl' => APP_URL . '/cv-variants.php',
        'metaNoindex' => true,
    ]); ?>
</head>
<body class="bg-gray-50">
    <?php partial('header'); ?>

    <main id="main-content" class="py-6">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <!-- Page Header -->
            <div class="mb-6 flex justify-between items-center">
                <div>
                    <h1 class="text-3xl font-bold text-gray-900">CV Variants</h1>
                    <p class="mt-1 text-sm text-gray-500">Manage different versions of your CV for specific job applications</p>
                </div>
                <div class="flex space-x-3">
                    <a href="/ai-settings.php" class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 transition-colors">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                        </svg>
                        AI Settings
                    </a>
                    <a href="/cv-variants/rewrite.php" class="inline-flex items-center px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
                        </svg>
                        Generate AI CV
                    </a>
                </div>
            </div>

            <?php if ($error): ?>
                <div class="mb-4 bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-lg">
                    <?php echo e($error); ?>
                </div>
            <?php endif; ?>

            <?php if ($success): ?>
                <div class="mb-4 bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded-lg">
                    <?php echo e($success); ?>
                </div>
            <?php endif; ?>

            <!-- AI Features Info Box -->
            <div class="mb-6 bg-gradient-to-r from-purple-50 to-blue-50 border border-purple-200 rounded-lg p-6">
                <div class="flex items-start">
                    <div class="flex-shrink-0">
                        <svg class="h-6 w-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
                        </svg>
                    </div>
                    <div class="ml-4 flex-1">
                        <h3 class="text-lg font-semibold text-gray-900 mb-2">AI-Powered CV Features</h3>
                        <p class="text-sm text-gray-700 mb-3">
                            Use artificial intelligence to create job-specific CVs and get quality feedback. Our AI analyzes job descriptions and tailors your CV to match each application perfectly.
                        </p>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-4">
                            <div class="flex items-start">
                                <svg class="h-5 w-5 text-purple-500 mr-2 mt-0.5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                                </svg>
                                <div>
                                    <p class="text-sm font-medium text-gray-900">AI CV Rewriting</p>
                                    <p class="text-xs text-gray-600">Generate tailored CVs automatically from job descriptions</p>
                                </div>
                            </div>
                            <div class="flex items-start">
                                <svg class="h-5 w-5 text-blue-500 mr-2 mt-0.5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                                </svg>
                                <div>
                                    <p class="text-sm font-medium text-gray-900">Quality Assessment</p>
                                    <p class="text-xs text-gray-600">Get AI-powered scores and improvement suggestions</p>
                                </div>
                            </div>
                        </div>
                        <div class="mt-4 flex flex-wrap gap-3">
                            <a href="/cv-variants/rewrite.php" class="inline-flex items-center px-3 py-2 bg-purple-600 text-white text-sm font-medium rounded-lg hover:bg-purple-700 transition-colors">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
                                </svg>
                                Generate AI CV
                            </a>
                            <a href="/cv-quality.php" class="inline-flex items-center px-3 py-2 border border-purple-600 text-purple-600 text-sm font-medium rounded-lg hover:bg-purple-50 transition-colors">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                                Assess Quality
                            </a>
<?php /* Template customizer temporarily hidden
                            <a href="/cv-template-customizer.php" class="inline-flex items-center px-3 py-2 border border-purple-600 text-purple-600 text-sm font-medium rounded-lg hover:bg-purple-50 transition-colors">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21a4 4 0 01-4-4V5a2 2 0 012-2h4a2 2 0 012 2v12a4 4 0 01-4 4zm0 0h12a2 2 0 002-2v-4a2 2 0 00-2-2h-2.343M11 7.343l1.657-1.657a2 2 0 012.828 0l2.829 2.829a2 2 0 010 2.828l-8.486 8.485M7 17h.01" />
                                </svg>
                                Customise Template
                            </a>
*/ ?>
                            <a href="/resources/ai/setup-ollama.php" class="inline-flex items-center px-3 py-2 text-purple-600 text-sm font-medium hover:text-purple-700 transition-colors">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 20l4-16m4 4l4 4-4 4M6 16l-4-4 4-4" />
                                </svg>
                                Setup Local AI
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            <!-- CV Variants List -->
            <div class="bg-white rounded-lg shadow overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Name</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Job Application</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Type</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Created</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            <?php if (empty($variants)): ?>
                                <tr>
                                    <td colspan="5" class="px-6 py-8 text-center text-gray-500">
                                        No CV variants found. Create your first variant using AI rewriting.
                                    </td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($variants as $variant): ?>
                                    <tr class="hover:bg-gray-50">
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="flex items-center">
                                                <div>
                                                    <div class="text-sm font-medium text-gray-900">
                                                        <?php echo e($variant['variant_name']); ?>
                                                        <?php if ($variant['is_master']): ?>
                                                            <span class="ml-2 inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-blue-100 text-blue-800">Master</span>
                                                        <?php endif; ?>
                                                        <?php if ($variant['ai_generated']): ?>
                                                            <span class="ml-2 inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-purple-100 text-purple-800">AI</span>
                                                        <?php endif; ?>
                                                    </div>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                            <?php if ($variant['job_application_id']): ?>
                                                <a href="/job-applications.php?highlight=<?php echo e($variant['job_application_id']); ?>" class="text-blue-600 hover:text-blue-800">
                                                    <?php echo e($variant['job_title'] ?? 'Untitled Job'); ?>
                                                </a>
                                                <div class="text-xs text-gray-400"><?php echo e($variant['company_name'] ?? ''); ?></div>
                                            <?php else: ?>
                                                <span class="text-gray-400">—</span>
                                            <?php endif; ?>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                            <?php if ($variant['is_master']): ?>
                                                Master CV
                                            <?php elseif ($variant['ai_generated']): ?>
                                                AI-Generated
                                            <?php else: ?>
                                                Custom
                                            <?php endif; ?>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                            <?php echo date('d/m/Y', strtotime($variant['created_at'])); ?>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                            <div class="flex items-center space-x-2">
                                                <a href="/cv.php?variant_id=<?php echo e($variant['id']); ?>" 
                                                   class="text-blue-600 hover:text-blue-900" 
                                                   title="View CV">
                                                    View
                                                </a>
                                                <a href="/cv-quality.php?variant_id=<?php echo e($variant['id']); ?>" 
                                                   class="text-green-600 hover:text-green-900" 
                                                   title="Assess Quality">
                                                    Assess
                                                </a>
                                                <?php if (!$variant['is_master']): ?>
                                                    <button onclick="editVariantName('<?php echo e($variant['id']); ?>', '<?php echo e(addslashes($variant['variant_name'])); ?>')" 
                                                            class="text-gray-600 hover:text-gray-900" 
                                                            title="Rename">
                                                        Rename
                                                    </button>
                                                    <button onclick="deleteVariant('<?php echo e($variant['id']); ?>', '<?php echo e(addslashes($variant['variant_name'])); ?>')" 
                                                            class="text-red-600 hover:text-red-900" 
                                                            title="Delete">
                                                        Delete
                                                    </button>
                                                <?php endif; ?>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </main>

    <?php partial('footer'); ?>

    <!-- Delete Confirmation Modal -->
    <div id="deleteModal" class="hidden fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
        <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
            <div class="mt-3">
                <h3 class="text-lg font-medium text-gray-900 mb-2">Delete CV Variant</h3>
                <p class="text-sm text-gray-500 mb-4">Are you sure you want to delete "<span id="deleteVariantName"></span>"? This action cannot be undone.</p>
                <form method="POST" id="deleteForm">
                    <input type="hidden" name="<?php echo CSRF_TOKEN_NAME; ?>" value="<?php echo generateCsrfToken(); ?>">
                    <input type="hidden" name="action" value="delete">
                    <input type="hidden" name="variant_id" id="deleteVariantId">
                    <div class="flex justify-end space-x-3">
                        <button type="button" onclick="closeDeleteModal()" class="px-4 py-2 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300">
                            Cancel
                        </button>
                        <button type="submit" class="px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700">
                            Delete
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Rename Modal -->
    <div id="renameModal" class="hidden fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
        <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
            <div class="mt-3">
                <h3 class="text-lg font-medium text-gray-900 mb-2">Rename CV Variant</h3>
                <form method="POST" id="renameForm" class="mt-4">
                    <input type="hidden" name="<?php echo CSRF_TOKEN_NAME; ?>" value="<?php echo generateCsrfToken(); ?>">
                    <input type="hidden" name="action" value="rename">
                    <input type="hidden" name="variant_id" id="renameVariantId">
                    <div class="mb-4">
                        <label for="variant_name" class="block text-sm font-medium text-gray-700 mb-1">Variant Name</label>
                        <input type="text" 
                               id="variant_name" 
                               name="variant_name" 
                               required
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                    </div>
                    <div class="flex justify-end space-x-3">
                        <button type="button" onclick="closeRenameModal()" class="px-4 py-2 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300">
                            Cancel
                        </button>
                        <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">
                            Save
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        function deleteVariant(variantId, variantName) {
            document.getElementById('deleteVariantId').value = variantId;
            document.getElementById('deleteVariantName').textContent = variantName;
            document.getElementById('deleteModal').classList.remove('hidden');
        }

        function closeDeleteModal() {
            document.getElementById('deleteModal').classList.add('hidden');
        }

        function editVariantName(variantId, currentName) {
            document.getElementById('renameVariantId').value = variantId;
            document.getElementById('variant_name').value = currentName;
            document.getElementById('renameModal').classList.remove('hidden');
        }

        function closeRenameModal() {
            document.getElementById('renameModal').classList.add('hidden');
        }
    </script>
</body>
</html>

