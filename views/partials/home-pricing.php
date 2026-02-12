<?php
// Shared pricing section. Resume.co-style: 3 plans only — Free, 7-day trial, 3-month.
$pricingUseRegisterModal = $pricingUseRegisterModal ?? false;
$pricingCards = [
    [
        'label' => 'Basic access',
        'price' => '£0',
        'detail' => 'Free',
        'highlight' => false,
        'badge' => null,
        'features' => [
            'CV & Cover Letter Builder',
            'ATS-friendly templates',
            'Resume sharing',
            'PDF export',
            'Limited job tracking & AI',
            'Keep your account & data — no payment',
        ],
        'button' => ['text' => 'Create account', 'href' => '/#auth-section', 'dataOpenRegister' => true],
    ],
    [
        'label' => '7-day unlimited access',
        'price' => '£1.95',
        'detail' => 'After 7 days, renews to £22/month. Cancel anytime.',
        'highlight' => true,
        'badge' => 'Most popular',
        'features' => [
            'CV & Cover Letter Builder',
            'ATS-friendly templates',
            'Resume sharing',
            'Unlimited downloads',
            'Unlimited AI-tailoring',
            'Unlimited AI cover letters',
        ],
        'button' => ['text' => 'Create account', 'href' => '/#auth-section', 'dataOpenRegister' => true, 'requiresAccount' => true],
    ],
    [
        'label' => '3-month unlimited access',
        'price' => '£27.88',
        'detail' => 'One-time payment today — save 66%',
        'highlight' => false,
        'badge' => 'Best value',
        'features' => [
            'CV & Cover Letter Builder',
            'ATS-friendly templates',
            'Resume sharing',
            'Unlimited downloads',
            'Unlimited AI-tailoring',
            'Unlimited AI cover letters',
        ],
        'button' => ['text' => 'Create account', 'href' => '/#auth-section', 'dataOpenRegister' => true, 'requiresAccount' => true],
    ],
];
// Remove null feature lines
foreach ($pricingCards as &$card) {
    $card['features'] = array_values(array_filter($card['features']));
}
unset($card);
?>
<div class="bg-gray-900 py-16 sm:py-24" id="pricing">
    <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
        <div class="max-w-2xl text-center mx-auto">
            <h2 class="text-3xl font-bold tracking-tight text-white sm:text-4xl">Find the plan that fits your job search best</h2>
            <p class="mt-4 text-lg text-gray-300">
                Free CV builder with job tracking. Upgrade for unlimited AI, templates, and PDF downloads.
            </p>
        </div>
        <div class="mt-12 grid gap-6 sm:grid-cols-1 md:grid-cols-3 max-w-4xl mx-auto">
            <?php foreach ($pricingCards as $card):
                $classes = $card['highlight']
                    ? 'border-blue-500 ring-1 ring-blue-200 bg-white text-gray-900'
                    : 'border-gray-700 bg-gray-800 text-gray-100';
            ?>
                <div class="flex flex-col rounded-2xl border <?php echo $classes; ?> p-8 shadow-xl relative">
                    <?php if (!empty($card['badge'])): ?>
                        <div class="absolute -top-3 left-1/2 -translate-x-1/2">
                            <span class="inline-flex items-center rounded-full bg-blue-600 px-3 py-1 text-xs font-semibold text-white shadow-lg">
                                <?php echo e($card['badge']); ?>
                            </span>
                        </div>
                    <?php endif; ?>
                    <div>
                        <h3 class="text-xl font-semibold"><?php echo e($card['label']); ?></h3>
                        <div class="mt-6 flex flex-wrap items-baseline gap-2">
                            <?php if (!empty($card['was_price'])): ?>
                                <span class="text-lg text-gray-400 line-through"><?php echo e($card['was_price']); ?></span>
                            <?php endif; ?>
                            <span class="text-3xl font-bold"><?php echo e($card['price']); ?></span>
                            <span class="text-sm text-gray-400"><?php echo e($card['detail']); ?></span>
                        </div>
                    </div>
                    <ul class="mt-8 space-y-3 text-sm flex-1">
                        <?php foreach ($card['features'] as $feature): ?>
                            <li class="flex items-start gap-2">
                                <svg class="h-5 w-5 text-blue-400 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                </svg>
                                <span><?php echo e($feature); ?></span>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                    <div class="mt-8">
                        <?php
                        $buttonHref = $card['button']['href'] ?? '/#auth-section';
                        $requiresAccount = $card['button']['requiresAccount'] ?? false;
                        $useModal = $pricingUseRegisterModal && !empty($card['button']['dataOpenRegister']);
                        ?>
                        <?php if ($useModal): ?>
                        <button type="button" data-open-register
                           class="inline-flex w-full items-center justify-center rounded-lg px-4 py-2 text-sm font-semibold transition
                           <?php echo $card['highlight']
                               ? 'bg-blue-600 text-white hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 focus:ring-offset-white'
                               : 'bg-white text-gray-900 hover:bg-gray-100 focus:outline-none focus:ring-2 focus:ring-white focus:ring-offset-2 focus:ring-offset-gray-800'; ?>">
                            <?php echo e($card['button']['text']); ?>
                        </button>
                        <?php else: ?>
                        <a href="<?php echo e($buttonHref); ?>"
                           class="inline-flex w-full items-center justify-center rounded-lg px-4 py-2 text-sm font-semibold transition
                           <?php echo $card['highlight']
                               ? 'bg-blue-600 text-white hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 focus:ring-offset-white'
                               : 'bg-white text-gray-900 hover:bg-gray-100 focus:outline-none focus:ring-2 focus:ring-white focus:ring-offset-2 focus:ring-offset-gray-800'; ?>">
                            <?php echo e($card['button']['text']); ?>
                        </a>
                        <?php endif; ?>
                        <?php if ($requiresAccount): ?>
                            <p class="mt-2 text-xs text-center <?php echo $card['highlight'] ? 'text-gray-600' : 'text-gray-400'; ?>">
                                Create a free account first, then upgrade from your dashboard
                            </p>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
        <div class="mt-12 rounded-xl border border-gray-700 bg-gray-800 p-6 sm:p-8 text-center">
            <h3 class="text-lg font-semibold text-white">For organisations &amp; agencies</h3>
            <p class="mt-2 text-gray-300">
                Recruitment agencies: manage candidates, team collaboration, and branding in one platform.
            </p>
            <ul class="mt-4 flex flex-wrap justify-center gap-x-6 gap-y-1 text-sm text-gray-400">
                <li>Custom candidate and team limits</li>
                <li>White-label branding and support</li>
            </ul>
            <a href="/organisations.php" class="mt-6 inline-flex items-center justify-center rounded-lg bg-white px-5 py-2.5 text-sm font-semibold text-gray-900 hover:bg-gray-100 focus:outline-none focus:ring-2 focus:ring-white focus:ring-offset-2 focus:ring-offset-gray-800">
                Contact us to set up your organisation
            </a>
        </div>
        <p class="mt-8 text-center text-sm text-gray-400">
            Secure payments powered by Stripe. Your card is safe; cancel anytime from your billing portal.
        </p>
    </div>
</div>
