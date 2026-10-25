<?php
$terms = \App\Services\TermsAndConditions::getContent();
$escape = static function (string $value): string {
    return htmlspecialchars($value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
};
?>

<div class="min-h-screen bg-slate-50/60 pb-16">
    <div class="bg-white border-b border-gray-200/80">
        <div class="w-full max-w-full mx-auto px-2 sm:px-6 lg:px-8 py-3.5">
            <nav class="flex items-center space-x-2 text-sm font-medium">
                <a href="<?= BASE_URL ?>/" class="text-[#F25996] hover:text-[#d8407d] font-semibold transition-colors">Home</a>
                <span class="text-gray-300">›</span>
                <span class="text-[#F25996] font-bold">Terms &amp; Conditions</span>
            </nav>
        </div>
    </div>

    <div class="w-full max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 pt-8">
        <div class="relative overflow-hidden rounded-3xl border border-pink-200/70 shadow-sm p-6 sm:p-10"
             style="background: linear-gradient(135deg, #fdf2f7 0%, #fce7f3 55%, #fdf4f8 100%);">
            <span class="text-xs font-extrabold tracking-widest uppercase text-[#F25996] bg-pink-100/80 px-3 py-1 rounded-full border border-pink-200/60">
                Official B2B Agreement
            </span>
            <h1 class="mt-4 text-3xl sm:text-4xl font-extrabold text-[#0f2963] font-display tracking-tight">
                <?= $escape($terms['title']) ?>
            </h1>
            <p class="mt-4 text-slate-700 leading-relaxed"><?= $escape($terms['introduction']) ?></p>
        </div>

        <div class="mt-8 bg-white rounded-3xl border border-gray-200/90 shadow-sm p-6 sm:p-10">
            <div class="space-y-7 divide-y divide-gray-100">
                <?php foreach ($terms['sections'] as $section): ?>
                    <section class="pt-7 first:pt-0">
                        <div class="flex items-start gap-3 mb-3">
                            <span class="inline-flex items-center justify-center w-8 h-8 rounded-xl bg-pink-50 border border-pink-200/80 text-[#F25996] font-extrabold text-sm shrink-0">
                                <?= (int)$section['number'] ?>
                            </span>
                            <h2 class="text-lg sm:text-xl font-bold text-[#0f2963] tracking-tight">
                                <?= $escape($section['title']) ?>
                            </h2>
                        </div>
                        <div class="space-y-2 pl-11 text-slate-700 text-sm sm:text-base leading-relaxed">
                            <?php foreach ($section['paragraphs'] as $paragraph): ?>
                                <p><?= $escape($paragraph) ?></p>
                            <?php endforeach; ?>
                        </div>
                    </section>
                <?php endforeach; ?>
            </div>

            <div class="mt-8 rounded-2xl bg-pink-50 border border-pink-200 p-5 flex flex-col sm:flex-row items-center justify-between gap-4">
                <p class="text-slate-700 text-sm sm:text-base">
                    If you have any questions about these terms, please contact our support team before placing an order.
                </p>
                <a href="mailto:grmb2bsupport@gmail.com" class="inline-flex items-center justify-center bg-[#F25996] hover:bg-[#d8407d] text-white font-bold px-6 py-3 rounded-xl text-sm whitespace-nowrap">
                    Contact Us
                </a>
            </div>
        </div>
    </div>
</div>
