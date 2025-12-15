<?php
require_once __DIR__ . '/../../php/helpers.php';

$pageTitle = '20+ Legitimate Ways to Earn Money Online & From Home in 2025';
$metaDescription = 'Practical, flexible, and legitimate ways to earn extra money from home in 2025—covering quick wins, remote work, creative ideas, and long-term income builders.';
$canonicalUrl = APP_URL . '/resources/extra-income/legitimate-ways-to-earn-money-online.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <?php partial('head', [
        'pageTitle' => $pageTitle . ' | Simple CV Builder',
        'metaDescription' => $metaDescription,
        'canonicalUrl' => $canonicalUrl,
    ]); ?>
</head>
<body class="bg-slate-50 text-slate-900">
<?php partial('header'); ?>

<main>
    <article class="relative overflow-hidden bg-gradient-to-br from-slate-950 via-slate-900 to-slate-800 text-slate-100">
        <div class="absolute inset-0 opacity-30">
            <div class="absolute inset-y-0 left-1/2 -translate-x-1/2 w-[80%] rounded-full bg-sky-500/10 blur-3xl"></div>
            <div class="absolute -bottom-32 right-0 h-64 w-64 rounded-full bg-blue-400/20 blur-3xl"></div>
        </div>
        <div class="relative max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 py-24">
            <header class="space-y-8">
                <div class="inline-flex items-center rounded-full border border-white/20 bg-white/5 px-4 py-1 text-xs font-semibold uppercase tracking-[0.3em] text-white/80">
                    Extra Income
                </div>
                <h1 class="text-4xl font-semibold tracking-tight sm:text-5xl"><?php echo e($pageTitle); ?></h1>
                <p class="text-lg text-slate-200 max-w-3xl leading-relaxed">
                    No gimmicks—just honest, practical ways to boost your income from home. Whether you need £50 to cover rising costs or want to build a significant second income, these 20+ ideas are genuinely working for people right now.
                </p>
                <div class="flex flex-col gap-3 sm:flex-row sm:items-center">
                    <a href="/resources/extra-income/" class="inline-flex items-center justify-center rounded-lg bg-white px-5 py-2 text-sm font-semibold text-slate-900 shadow hover:bg-slate-100">
                        Back to extra income ideas
                    </a>
                    <a href="#quick-wins" class="inline-flex items-center justify-center rounded-lg border border-white/40 px-5 py-2 text-sm font-semibold text-white hover:bg-white/10">
                        Jump to quick wins
                    </a>
                </div>
            </header>
        </div>
    </article>

    <section class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 py-16 space-y-14 text-base leading-relaxed text-slate-700">
        <div class="rounded-3xl border border-slate-200 bg-white/90 p-8 shadow-xl shadow-slate-900/5 backdrop-blur">
            <div class="rounded-2xl border border-slate-200 bg-slate-50 px-6 py-5 text-base text-slate-700 leading-relaxed">
                <p><strong>Looking for flexible work that fits around your schedule?</strong> Whether you’re between jobs, need extra income alongside your current role, or want to work from the comfort of your home, there are numerous legitimate opportunities available. This guide covers proven methods that real people are using to earn anywhere from £50 to £1,000+ per month.</p>
                <p class="mt-3">No get-rich-quick schemes here—just honest, practical ways to boost your income while maintaining the flexibility you need.</p>
            </div>

            <h2 id="before-you-start" class="mt-10 text-2xl font-semibold text-slate-900">Before You Start: Essential Tips</h2>
            <div class="mt-4 rounded-2xl border border-slate-200 bg-white px-6 py-5 text-base text-slate-700 space-y-4 shadow-sm">
                <h3 class="text-lg font-semibold text-slate-900">Protect Yourself Online</h3>
                <ul class="list-disc pl-5 space-y-2">
                    <li><strong>Create a dedicated email address</strong> for online earning activities to keep your main inbox organised.</li>
                    <li><strong>Never pay upfront fees</strong> for legitimate earning opportunities—if they’re asking for money, it’s likely a scam.</li>
                    <li><strong>Withdraw earnings promptly</strong>—these platforms aren’t banks, so don’t let large sums accumulate.</li>
                    <li><strong>Check tax implications</strong>—earnings may need to be reported to HMRC, even for small amounts.</li>
                    <li><strong>Read reviews</strong> from real users before signing up to any platform.</li>
                </ul>
            </div>

            <div class="mt-6 rounded-2xl border border-slate-800 bg-slate-900 px-6 py-6 text-base text-slate-100 shadow-md">
                <strong class="font-semibold text-white">Important:</strong> Be wary of opportunities that promise unrealistic earnings, require personal financial information upfront, or ask you to recruit others. Legitimate opportunities pay you for work completed, not for signing up.
            </div>
        </div>

        <!-- Quick wins -->
        <section id="quick-wins">
            <h2 class="text-2xl font-semibold text-slate-900">Quick Wins: Easy Money for Minimal Effort</h2>
            <div class="mt-6 space-y-8">
                <?php
                $quickWins = [
                    [
                        'title' => 'Online Surveys & Market Research',
                        'earning' => '£50–£300+ per month',
                        'time' => '15 minutes to 1 hour per task',
                        'intro' => 'Companies need consumer opinions to improve their products and services. By sharing your views through online surveys, you can earn cash, vouchers, or rewards. While you won’t get rich, it’s an easy way to earn during downtime.',
                        'steps' => [
                            'Sign up to multiple survey sites to maximise opportunities.',
                            'Complete your profile honestly to receive relevant surveys.',
                            'Typical surveys pay £1–£5 and take 10–30 minutes.',
                            'Cash out via PayPal, bank transfer, or gift cards.',
                        ],
                        'platforms' => ['Prolific', 'Swagbucks', 'Qmee', 'YouGov', 'Toluna'],
                        'tip' => 'Sign up to 5-10 survey sites simultaneously. Not every site will have surveys available daily, so having multiple accounts ensures a steady stream of opportunities.',
                    ],
                    [
                        'title' => 'Focus Groups & Market Research Sessions',
                        'earning' => '£40–£250 per session',
                        'time' => '1–3 hours per session',
                        'intro' => 'Focus groups pay significantly more than online surveys because they require more engagement. Companies want to dive deeper into consumer opinions through group discussions or one-to-one interviews.',
                        'steps' => [
                            'Sessions typically run online via Zoom or in-person in major cities.',
                            'Discuss products, services, advertising, or consumer trends.',
                            'Payment usually made within 2–4 weeks after participation.',
                            'May need to fit specific demographic criteria.',
                        ],
                        'platforms' => ['QMR', 'Trend Market Research', 'Saros Research', 'Angelfish Opinions'],
                        'tip' => 'Apply immediately when you receive invitations—popular sessions fill up within hours. Being honest in screening questions increases your chances of being selected.',
                    ],
                    [
                        'title' => 'Cashback Sites & Apps',
                        'earning' => '£100–£500+ per year',
                        'time' => 'Two minutes per purchase',
                        'intro' => 'If you’re shopping online anyway, why not get paid for it? Cashback sites earn commission from retailers and share a portion with you. It’s essentially free money for purchases you’d make regardless.',
                        'steps' => [
                            'Always check cashback sites before ordering online.',
                            'Stack with cashback/points credit cards for double benefits.',
                            'Compare rates across multiple sites.',
                            'Look for special offers and increased rates.',
                        ],
                        'platforms' => ['TopCashback', 'Quidco', 'Airtime Rewards'],
                    ],
                ];

                foreach ($quickWins as $method): ?>
                    <section class="rounded-2xl border border-slate-200 bg-white p-8 shadow-lg shadow-slate-900/5 transition hover:-translate-y-0.5 hover:shadow-xl">
                        <h3 class="text-xl font-semibold text-slate-900"><?php echo e($method['title']); ?></h3>
                        <div class="mt-3 flex flex-wrap gap-3 text-sm">
                            <span class="rounded-full border border-slate-200 bg-slate-100 px-3 py-1 font-medium text-slate-700">Earning potential: <?php echo e($method['earning']); ?></span>
                            <span class="rounded-full border border-slate-200 bg-slate-100 px-3 py-1 font-medium text-slate-700">Time commitment: <?php echo e($method['time']); ?></span>
                        </div>
                        <p class="mt-4 text-base text-slate-600"><?php echo e($method['intro']); ?></p>
                        <?php if (!empty($method['steps'])): ?>
                            <p class="mt-4 text-base font-semibold text-slate-900">How it works:</p>
                            <ul class="mt-2 list-disc pl-5 space-y-2 text-base text-slate-600">
                                <?php foreach ($method['steps'] as $step): ?>
                                    <li><?php echo e($step); ?></li>
                                <?php endforeach; ?>
                            </ul>
                        <?php endif; ?>
                        <?php if (!empty($method['platforms'])): ?>
                            <p class="mt-4 text-base font-semibold text-slate-900">Top platforms:</p>
                            <ul class="mt-2 list-disc pl-5 space-y-2 text-base text-slate-600">
                                <?php foreach ($method['platforms'] as $platform): ?>
                                    <li><?php echo e($platform); ?></li>
                                <?php endforeach; ?>
                            </ul>
                        <?php endif; ?>
                        <?php if (!empty($method['tip'])): ?>
                            <div class="mt-4 rounded-xl border border-blue-200 bg-blue-50 px-4 py-4 text-base text-slate-600">
                                <strong class="text-slate-900">Pro tip:</strong> <?php echo e($method['tip']); ?>
                            </div>
                        <?php endif; ?>
                    </section>
                <?php endforeach; ?>
            </div>
        </section>

        <!-- Flexible remote work -->
        <section id="flexible-remote">
            <h2 class="text-2xl font-semibold text-slate-900">Flexible Remote Work Opportunities</h2>
            <?php
            $remoteOptions = [
                [
                    'title' => 'Freelance Writing & Content Creation',
                    'earning' => '£15–£100+ per hour',
                    'time' => 'Flexible, project-based',
                    'intro' => 'If you have a way with words, freelance writing can provide steady income. Businesses constantly need blog posts, website copy, product descriptions, social media content, and more.',
                    'steps' => [
                        'Build a portfolio with 3–5 sample articles (even unpublished ones).',
                        'Start with lower rates to gain experience and reviews.',
                        'Specialise in a niche (tech, finance, health, travel) to command higher rates.',
                        'Create profiles on freelance platforms.',
                    ],
                    'platforms' => ['Upwork', 'Fiverr', 'Freelancer.com', 'Contently', 'ProBlogger Job Board'],
                    'quote' => '"I started freelance writing as a side hustle while working full-time. Within six months, I was earning enough to go full-time freelance. Now I earn more than my old salary while working from my sofa." – Sarah, Content Writer',
                ],
                [
                    'title' => 'Virtual Assistant Services',
                    'earning' => '£12–£35 per hour',
                    'time' => '5–40 hours per week',
                    'intro' => 'Virtual assistants provide administrative support to businesses and entrepreneurs remotely. Tasks might include email management, scheduling, data entry, customer service, social media management, and basic bookkeeping.',
                    'steps' => [
                        'Proficiency in Microsoft Office or Google Workspace.',
                        'Strong written and verbal communication.',
                        'Organisation and attention to detail.',
                        'Basic social media knowledge.',
                        'Ability to work independently.',
                    ],
                    'platformsTitle' => 'Where to find work:',
                    'platforms' => ['Belay Solutions', 'Time Etc', 'Upwork', 'VA networking groups'],
                ],
                [
                    'title' => 'Online Tutoring & Teaching',
                    'earning' => '£15–£40 per hour',
                    'time' => 'Flexible scheduling',
                    'intro' => 'Share your knowledge by teaching students online. Whether it’s academic subjects, English as a foreign language, or specialised skills, there’s demand for quality tutors.',
                    'stepsTitle' => 'Options to explore:',
                    'steps' => [
                        'Teaching English online – high demand from international students.',
                        'Academic tutoring – help UK students with GCSE, A-Levels, or university subjects.',
                        'Skills teaching – music, coding, languages, professional skills.',
                    ],
                    'platforms' => ['Tutorful', 'MyTutor', 'Preply', 'Cambly'],
                ],
                [
                    'title' => 'Data Entry & Transcription',
                    'earning' => '£8–£20 per hour',
                    'time' => 'Flexible, project-based',
                    'intro' => 'If you’re a fast typist with good attention to detail, data entry and transcription work can provide steady income. The work is straightforward and can be done entirely from home.',
                    'stepsTitle' => 'Types of work available:',
                    'steps' => [
                        'General transcription – convert audio recordings to text.',
                        'Data entry – input information into databases or spreadsheets.',
                        'Specialised transcription – medical or legal (requires training but pays more).',
                    ],
                    'platforms' => ['Rev', 'TranscribeMe', 'Clickworker', 'Amazon MTurk'],
                ],
            ];
            ?>
            <div class="mt-6 space-y-8">
                <?php foreach ($remoteOptions as $option): ?>
                    <section class="rounded-2xl border border-slate-200 bg-white p-8 shadow-lg shadow-slate-900/5 transition hover:-translate-y-0.5 hover:shadow-xl">
                        <h3 class="text-xl font-semibold text-slate-900"><?php echo e($option['title']); ?></h3>
                        <div class="mt-3 flex flex-wrap gap-3 text-sm">
                            <span class="rounded-full border border-slate-200 bg-slate-100 px-3 py-1 font-medium text-slate-700">Earning potential: <?php echo e($option['earning']); ?></span>
                            <span class="rounded-full border border-slate-200 bg-slate-100 px-3 py-1 font-medium text-slate-700">Time commitment: <?php echo e($option['time']); ?></span>
                        </div>
                        <p class="mt-4 text-base text-slate-600"><?php echo e($option['intro']); ?></p>
                        <?php if (!empty($option['steps'])): ?>
                            <p class="mt-4 text-base font-semibold text-slate-900"><?php echo e($option['stepsTitle'] ?? 'Getting started:'); ?></p>
                            <ul class="mt-2 list-disc pl-5 space-y-2 text-base text-slate-600">
                                <?php foreach ($option['steps'] as $step): ?>
                                    <li><?php echo e($step); ?></li>
                                <?php endforeach; ?>
                            </ul>
                        <?php endif; ?>
                        <?php if (!empty($option['platforms'])): ?>
                            <p class="mt-4 text-base font-semibold text-slate-900"><?php echo e($option['platformsTitle'] ?? 'Where to find work:'); ?></p>
                            <ul class="mt-2 list-disc pl-5 space-y-2 text-base text-slate-600">
                                <?php foreach ($option['platforms'] as $platform): ?>
                                    <li><?php echo e($platform); ?></li>
                                <?php endforeach; ?>
                            </ul>
                        <?php endif; ?>
                        <?php if (!empty($option['quote'])): ?>
                            <div class="mt-4 rounded-xl border border-slate-200 bg-slate-50 px-4 py-4 text-base italic text-slate-600">
                                <?php echo e($option['quote']); ?>
                            </div>
                        <?php endif; ?>
                    </section>
                <?php endforeach; ?>
            </div>
        </section>

        <!-- Additional sections replicating content -->
        <section id="creative-ideas">
            <h2 class="text-2xl font-semibold text-slate-900">Creative & Specialised Opportunities</h2>
            <div class="mt-6 space-y-8">
                <div class="rounded-2xl border border-slate-200 bg-white p-8 shadow-lg shadow-slate-900/5">
                    <h3 class="text-xl font-semibold text-slate-900">Selling Digital Products</h3>
                    <div class="mt-3 flex flex-wrap gap-3 text-sm">
                        <span class="rounded-full border border-slate-200 bg-slate-100 px-3 py-1 font-medium text-slate-700">Earning potential: £50–£5,000+ per month</span>
                        <span class="rounded-full border border-slate-200 bg-slate-100 px-3 py-1 font-medium text-slate-700">Time commitment: High upfront, then passive</span>
                    </div>
                    <p class="mt-4 text-base text-slate-600">Create once, sell repeatedly. Digital products require upfront work but can generate passive income for months or years.</p>
                    <ul class="mt-3 list-disc pl-5 space-y-2 text-base text-slate-600">
                        <li>Online courses on Teachable or Udemy.</li>
                        <li>eBooks via Amazon Kindle Direct Publishing.</li>
                        <li>Printables & templates on Etsy.</li>
                        <li>Stock photography on Shutterstock or Adobe Stock.</li>
                        <li>Graphic design templates on Creative Market.</li>
                    </ul>
                </div>
                <div class="rounded-2xl border border-slate-200 bg-white p-8 shadow-lg shadow-slate-900/5">
                    <h3 class="text-xl font-semibold text-slate-900">Website & User Testing</h3>
                    <div class="mt-3 flex flex-wrap gap-3 text-sm">
                        <span class="rounded-full border border-slate-200 bg-slate-100 px-3 py-1 font-medium text-slate-700">Earning potential: £5–£60 per test</span>
                        <span class="rounded-full border border-slate-200 bg-slate-100 px-3 py-1 font-medium text-slate-700">Time commitment: 10–30 minutes per test</span>
                    </div>
                    <p class="mt-4 text-base text-slate-600">Companies pay for feedback on their websites and apps. Navigate sites while speaking your thoughts aloud to help them improve user experience.</p>
                    <ul class="mt-3 list-disc pl-5 space-y-2 text-base text-slate-600">
                        <li>UserTesting – £8–£10 per 20-minute test.</li>
                        <li>Userlytics – some moderated tests pay £50+.</li>
                        <li>TryMyUI – regular testing opportunities.</li>
                        <li>TestingTime – live moderated sessions pay more.</li>
                    </ul>
                </div>
                <div class="rounded-2xl border border-slate-200 bg-white p-8 shadow-lg shadow-slate-900/5">
                    <h3 class="text-xl font-semibold text-slate-900">Social Media Management</h3>
                    <div class="mt-3 flex flex-wrap gap-3 text-sm">
                        <span class="rounded-full border border-slate-200 bg-slate-100 px-3 py-1 font-medium text-slate-700">Earning potential: £15–£50+ per hour</span>
                        <span class="rounded-full border border-slate-200 bg-slate-100 px-3 py-1 font-medium text-slate-700">Time commitment: 5–30 hours per week per client</span>
                    </div>
                    <p class="mt-4 text-base text-slate-600">Small businesses need help managing their social media presence but can’t afford full-time staff.</p>
                    <ul class="mt-3 list-disc pl-5 space-y-2 text-base text-slate-600">
                        <li>Creating and scheduling posts across platforms.</li>
                        <li>Engaging with followers and responding to comments.</li>
                        <li>Growing follower counts organically.</li>
                        <li>Reporting on metrics and analytics.</li>
                    </ul>
                </div>
            </div>
        </section>

        <section id="phone-tasks">
            <h2 class="text-2xl font-semibold text-slate-900">Phone & Computer-Based Tasks</h2>
            <div class="mt-6 space-y-8">
                <div class="rounded-2xl border border-slate-200 bg-white p-8 shadow-lg shadow-slate-900/5">
                    <h3 class="text-xl font-semibold text-slate-900">Get Paid to Search the Web</h3>
                    <div class="mt-3 flex flex-wrap gap-3 text-sm">
                        <span class="rounded-full border border-slate-200 bg-slate-100 px-3 py-1 font-medium text-slate-700">Earning potential: £50–£150 per year</span>
                        <span class="rounded-full border border-slate-200 bg-slate-100 px-3 py-1 font-medium text-slate-700">Time commitment: Passive</span>
                    </div>
                    <p class="mt-4 text-base text-slate-600">Install browser extensions that reward you for searches you’d perform anyway.</p>
                    <ul class="mt-3 list-disc pl-5 space-y-2 text-base text-slate-600">
                        <li>Qmee – no minimum payout, instant withdrawal.</li>
                        <li>Swagbucks – earn points for searches & shopping.</li>
                        <li>Microsoft Rewards – earn points using Bing.</li>
                    </ul>
                </div>
                <div class="rounded-2xl border border-slate-200 bg-white p-8 shadow-lg shadow-slate-900/5">
                    <h3 class="text-xl font-semibold text-slate-900">App & Game Testing</h3>
                    <div class="mt-3 flex flex-wrap gap-3 text-sm">
                        <span class="rounded-full border border-slate-200 bg-slate-100 px-3 py-1 font-medium text-slate-700">Earning potential: £1–£10 per test</span>
                        <span class="rounded-full border border-slate-200 bg-slate-100 px-3 py-1 font-medium text-slate-700">Time commitment: 5–20 minutes per test</span>
                    </div>
                    <p class="mt-4 text-base text-slate-600">Mobile apps need testing before launch. Use apps, identify bugs, and submit feedback straight from your phone.</p>
                    <ul class="mt-3 list-disc pl-5 space-y-2 text-base text-slate-600">
                        <li>BetaTesting – test apps and websites.</li>
                        <li>Testbirds – wide range of digital products.</li>
                        <li>uTest – large testing community with regular work.</li>
                    </ul>
                </div>
            </div>
        </section>

        <section id="long-term">
            <h2 class="text-2xl font-semibold text-slate-900">Longer-Term Income Builders</h2>
            <div class="mt-6 space-y-8">
                <div class="rounded-2xl border border-slate-200 bg-white p-8 shadow-lg shadow-slate-900/5">
                    <h3 class="text-xl font-semibold text-slate-900">Start a Blog or YouTube Channel</h3>
                    <div class="mt-3 flex flex-wrap gap-3 text-sm">
                        <span class="rounded-full border border-slate-200 bg-slate-100 px-3 py-1 font-medium text-slate-700">Earning potential: £0–£10,000+ per month</span>
                        <span class="rounded-full border border-slate-200 bg-slate-100 px-3 py-1 font-medium text-slate-700">Time commitment: 10–40 hours per week</span>
                    </div>
                    <p class="mt-4 text-base text-slate-600">Creating content takes significant time before generating income, but successful creators can earn substantial passive income.</p>
                    <ul class="mt-3 list-disc pl-5 space-y-2 text-base text-slate-600">
                        <li>Expect 6–12 months before meaningful income.</li>
                        <li>YouTube requires 1,000 subscribers & 4,000 watch hours to monetise.</li>
                        <li>Consistency and niche focus are crucial.</li>
                    </ul>
                </div>
                <div class="rounded-2xl border border-slate-200 bg-white p-8 shadow-lg shadow-slate-900/5">
                    <h3 class="text-xl font-semibold text-slate-900">Affiliate Marketing</h3>
                    <div class="mt-3 flex flex-wrap gap-3 text-sm">
                        <span class="rounded-full border border-slate-200 bg-slate-100 px-3 py-1 font-medium text-slate-700">Earning potential: £50–£5,000+ per month</span>
                        <span class="rounded-full border border-slate-200 bg-slate-100 px-3 py-1 font-medium text-slate-700">Time commitment: Ongoing content creation</span>
                    </div>
                    <p class="mt-4 text-base text-slate-600">Promote products you genuinely use and earn commission. Works brilliantly alongside blogging, YouTube, or social media.</p>
                    <ul class="mt-3 list-disc pl-5 space-y-2 text-base text-slate-600">
                        <li>Amazon Associates – 1–10% commission on products.</li>
                        <li>ShareASale & Awin – networks with thousands of merchants.</li>
                        <li>CJ Affiliate – large brands and established companies.</li>
                    </ul>
                </div>
            </div>
        </section>

        <section id="selling-renting">
            <h2 class="text-2xl font-semibold text-slate-900">Selling & Renting What You Have</h2>
            <div class="mt-6 space-y-8">
                <div class="rounded-2xl border border-slate-200 bg-white p-8 shadow-lg shadow-slate-900/5">
                    <h3 class="text-xl font-semibold text-slate-900">Sell Unwanted Items Online</h3>
                    <div class="mt-3 flex flex-wrap gap-3 text-sm">
                        <span class="rounded-full border border-slate-200 bg-slate-100 px-3 py-1 font-medium text-slate-700">Earning potential: £100–£1,000+ (one-time)</span>
                        <span class="rounded-full border border-slate-200 bg-slate-100 px-3 py-1 font-medium text-slate-700">Time commitment: Few hours to photograph & list</span>
                    </div>
                    <p class="mt-4 text-base text-slate-600">Everyone has items gathering dust. Turn clutter into cash through online marketplaces.</p>
                    <ul class="mt-3 list-disc pl-5 space-y-2 text-base text-slate-600">
                        <li>eBay – electronics, collectibles, branded items.</li>
                        <li>Facebook Marketplace – local sales, furniture, large items.</li>
                        <li>Vinted / Depop – clothing and fashion.</li>
                        <li>Music Magpie – quick sale of books, CDs, DVDs, tech.</li>
                    </ul>
                </div>
                <div class="rounded-2xl border border-slate-200 bg-white p-8 shadow-lg shadow-slate-900/5">
                    <h3 class="text-xl font-semibold text-slate-900">Rent Out Your Assets</h3>
                    <div class="mt-3 flex flex-wrap gap-3 text-sm">
                        <span class="rounded-full border border-slate-200 bg-slate-100 px-3 py-1 font-medium text-slate-700">Earning potential: £50–£500+ per month</span>
                        <span class="rounded-full border border-slate-200 bg-slate-100 px-3 py-1 font-medium text-slate-700">Time commitment: Minimal ongoing effort</span>
                    </div>
                    <ul class="mt-4 list-disc pl-5 space-y-2 text-base text-slate-600">
                        <li>Spare room – advertise on SpareRoom or take on a lodger.</li>
                        <li>Parking space – list on JustPark or YourParkingSpace.</li>
                        <li>Car – rent on Turo or Getaround when not in use.</li>
                        <li>Equipment & tools – rent on Fat Llama.</li>
                        <li>Designer clothing – list on By Rotation.</li>
                    </ul>
                </div>
            </div>
        </section>

        <section id="professional-services">
            <h2 class="text-2xl font-semibold text-slate-900">Specialised Skills & Professional Services</h2>
            <div class="mt-6 space-y-8">
                <div class="rounded-2xl border border-slate-200 bg-white p-8 shadow-lg shadow-slate-900/5">
                    <h3 class="text-xl font-semibold text-slate-900">Bookkeeping & Accounting</h3>
                    <div class="mt-3 flex flex-wrap gap-3 text-sm">
                        <span class="rounded-full border border-slate-200 bg-slate-100 px-3 py-1 font-medium text-slate-700">Earning potential: £20–£50 per hour</span>
                        <span class="rounded-full border border-slate-200 bg-slate-100 px-3 py-1 font-medium text-slate-700">Time commitment: Project-based or retainers</span>
                    </div>
                    <p class="mt-4 text-base text-slate-600">Small businesses often outsource bookkeeping. If you’re comfortable with numbers or can learn software like QuickBooks or Xero, there’s steady demand.</p>
                </div>
                <div class="rounded-2xl border border-slate-200 bg-white p-8 shadow-lg shadow-slate-900/5">
                    <h3 class="text-xl font-semibold text-slate-900">Graphic Design & Creative Services</h3>
                    <div class="mt-3 flex flex-wrap gap-3 text-sm">
                        <span class="rounded-full border border-slate-200 bg-slate-100 px-3 py-1 font-medium text-slate-700">Earning potential: £20–£100+ per hour</span>
                        <span class="rounded-full border border-slate-200 bg-slate-100 px-3 py-1 font-medium text-slate-700">Time commitment: Project-based</span>
                    </div>
                    <p class="mt-4 text-base text-slate-600">From logo design to social media graphics, businesses constantly need visual content. Even beginners can find work for simple projects.</p>
                    <ul class="mt-3 list-disc pl-5 space-y-2 text-base text-slate-600">
                        <li>Fiverr – set packages at various price points.</li>
                        <li>99designs – design contests and freelance projects.</li>
                        <li>Upwork – build direct client relationships.</li>
                        <li>Dribbble – portfolio that can lead to inbound work.</li>
                    </ul>
                </div>
                <div class="rounded-2xl border border-slate-200 bg-white p-8 shadow-lg shadow-slate-900/5">
                    <h3 class="text-xl font-semibold text-slate-900">Proofreading & Editing</h3>
                    <div class="mt-3 flex flex-wrap gap-3 text-sm">
                        <span class="rounded-full border border-slate-200 bg-slate-100 px-3 py-1 font-medium text-slate-700">Earning potential: £15–£40 per hour</span>
                        <span class="rounded-full border border-slate-200 bg-slate-100 px-3 py-1 font-medium text-slate-700">Time commitment: Flexible, project-based</span>
                    </div>
                    <p class="mt-4 text-base text-slate-600">If you have excellent grammar and attention to detail, proofreading can provide steady work. Authors, students, and businesses all need editing services.</p>
                    <ul class="mt-3 list-disc pl-5 space-y-2 text-base text-slate-600">
                        <li>Reedsy – connects editors with authors.</li>
                        <li>Scribendi – ongoing editing jobs.</li>
                        <li>Upwork – general freelance marketplace.</li>
                        <li>Cactus Communications – academic editing roles.</li>
                    </ul>
                </div>
                <div class="rounded-2xl border border-slate-200 bg-white p-8 shadow-lg shadow-slate-900/5">
                    <h3 class="text-xl font-semibold text-slate-900">Programming & Web Development</h3>
                    <div class="mt-3 flex flex-wrap gap-3 text-sm">
                        <span class="rounded-full border border-slate-200 bg-slate-100 px-3 py-1 font-medium text-slate-700">Earning potential: £25–£150+ per hour</span>
                        <span class="rounded-full border border-slate-200 bg-slate-100 px-3 py-1 font-medium text-slate-700">Time commitment: Project-based</span>
                    </div>
                    <p class="mt-4 text-base text-slate-600">Developers are in high demand, especially those fluent in popular tech stacks. Even junior developers can land remote gigs with a solid portfolio.</p>
                    <ul class="mt-3 list-disc pl-5 space-y-2 text-base text-slate-600">
                        <li>Learn via freeCodeCamp, The Odin Project, or Codecademy.</li>
                        <li>Focus on JavaScript, React, Python, or WordPress.</li>
                        <li>Build portfolio projects to showcase abilities.</li>
                        <li>Start with small freelance projects to gain experience.</li>
                    </ul>
                </div>
            </div>
        </section>

        <section id="considerations" class="space-y-6">
            <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-lg shadow-slate-900/5">
                <h3 class="text-lg font-semibold text-slate-900">Tax Implications</h3>
                <p class="mt-2 text-base text-slate-600">Any income earned—online or offline—may need to be declared to HMRC.</p>
                <ul class="mt-4 list-disc pl-5 space-y-2 text-base text-slate-600">
                    <li>If your side income exceeds £1,000 per year, register for Self Assessment.</li>
                    <li>Keep detailed records of income and expenses.</li>
                    <li>You may be able to deduct business expenses.</li>
                    <li>Consider consulting an accountant as earnings grow.</li>
                </ul>
            </div>
            <div class="rounded-2xl border border-slate-800 bg-slate-900 px-6 py-6 text-base text-slate-200 shadow-xl">
                <h3 class="text-lg font-semibold text-white">Building a Sustainable Income Strategy</h3>
                <p class="mt-3"><strong>Combine multiple methods:</strong> don’t rely on a single stream—many successful earners mix 3–5 different approaches.</p>
                <p><strong>Start small and scale:</strong> begin with easier options like surveys and cashback, then move toward higher-paying skills.</p>
                <p><strong>Invest in skills:</strong> writing, design, coding, and marketing command better rates.</p>
                <p><strong>Track everything:</strong> log time vs. earnings to double down on what works.</p>
                <p><strong>Be patient:</strong> most people earn £50–£200 per month initially and scale up over 6–12 months.</p>
            </div>
        </section>

        <section id="getting-started" class="rounded-3xl border border-slate-900 bg-slate-950 p-8 text-slate-200 shadow-xl">
            <h2 class="text-2xl font-semibold text-white">Ready to Get Started?</h2>
            <p class="text-base leading-relaxed">
                Pick two or three methods that align with your skills and availability. Commit to trying them for at least a month, track your results, and iterate. Legitimate opportunities won’t make you rich overnight, but they can provide meaningful supplemental income and, in some cases, grow into full-time careers.
            </p>
            <div class="rounded-2xl border border-white/10 bg-white/5 px-6 py-5 text-base text-slate-100">
                <h3 class="text-lg font-semibold text-white">Need Help Presenting Your Skills?</h3>
                <p class="mt-2 leading-relaxed text-slate-100/80">Whether you’re applying for remote positions or building a freelance portfolio, having a professional CV matters. Use <a href="/" class="text-sky-300 underline hover:text-sky-200">Simple CV Builder</a> to craft an impressive, shareable CV in minutes.</p>
            </div>
        </section>

        <section id="disclaimer" class="rounded-2xl border border-slate-200 bg-white p-6 shadow-lg shadow-slate-900/5">
            <p class="text-xs text-slate-600 leading-relaxed">
                <strong class="text-slate-900">Disclaimer:</strong> Earnings mentioned are estimates based on typical user experiences and can vary significantly. We’ve done our best to list legitimate opportunities, but always conduct your own research before signing up. Simple CV Builder is not affiliated with any of the platforms mentioned and receives no compensation for including them in this guide.
            </p>
            <p class="mt-3 text-xs text-slate-500">Last updated: November 2025.</p>
        </section>
    </section>

    <?php partial('resources-footer-cta'); ?>
</main>

<?php partial('footer'); ?>
<?php partial('auth-modals'); ?>
</body>
</html>
