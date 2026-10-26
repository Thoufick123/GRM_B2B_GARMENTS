<div class="mb-8 relative z-10">
    <!-- Breadcrumbs -->
    <div class="text-sm font-medium text-gray-400 mb-2 font-serif tracking-wide">
        Dashboard <span class="mx-1 text-gray-300">›</span> CMS Management <span class="mx-1 text-gray-300">›</span> <span class="text-brand-700">Reviews</span>
    </div>
    
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h2 class="text-4xl sm:text-5xl font-display font-extrabold text-gray-900 tracking-tight">Review Sections</h2>
            <p class="text-sm text-gray-500 mt-1">Configure customer review carousels and animation effects for your landing page.</p>
        </div>
        <div class="flex items-center gap-3">
            <?php if ($this->hasPermission('cms_builder', 'view') || $this->hasAnyPermission('cms_builder')): ?>
            <a href="<?= BASE_URL ?>/admin/cms/builder" class="bg-gray-100 hover:bg-gray-200 text-gray-700 px-5 py-3 rounded-full font-bold text-xs shadow-sm transition-all flex items-center gap-2 uppercase tracking-wider">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path></svg>
                Layout Builder
            </a>
            <?php endif; ?>
            <?php if ($this->hasPermission('cms_reviews', 'create')): ?>
            <a href="<?= BASE_URL ?>/admin/cms/reviews/create" class="bg-[#F25996] hover:bg-[#e04481] text-white px-6 py-3 rounded-full font-bold text-xs shadow-sm transition-all flex items-center gap-2 uppercase tracking-wider">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                Add Review Section
            </a>
            <?php endif; ?>
        </div>
    </div>
</div>

<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
    <?php if(empty($sections)): ?>
        <div class="col-span-full py-16 text-center text-gray-400 bg-white rounded-[2rem] border border-gray-100 shadow-sm">
            <div class="w-16 h-16 mx-auto mb-4 bg-pink-50 text-[#F25996] rounded-2xl flex items-center justify-center">
                <svg class="w-8 h-8" fill="currentColor" viewBox="0 0 24 24"><path d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z"/></svg>
            </div>
            <p class="text-xl font-bold text-gray-800">No Review Sections Found</p>
            <p class="text-sm text-gray-500 mt-2 max-w-md mx-auto">Create a review section to showcase customer feedback with animations like fade, slide, or zoom on your storefront.</p>
            <?php if ($this->hasPermission('cms_reviews', 'create')): ?>
            <a href="<?= BASE_URL ?>/admin/cms/reviews/create" class="inline-flex items-center gap-2 mt-6 bg-[#F25996] hover:bg-[#e04481] text-white px-6 py-2.5 rounded-full font-bold text-xs uppercase tracking-wider transition-all">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                Create First Review Section
            </a>
            <?php endif; ?>
        </div>
    <?php else: ?>
        <?php foreach($sections as $sec): 
            $content = $sec['content'] ?? [];
            $title = !empty($content['title']) ? $content['title'] : 'Customer Reviews';
            $subtitle = !empty($content['subtitle']) ? $content['subtitle'] : 'What our satisfied customers say';
            $animation = $content['animation'] ?? 'fade';
            $selectedIds = $content['selected_review_ids'] ?? [];
            $reviewCount = count($selectedIds);
            $isActive = (bool)$sec['is_active'];
            $autoplay = !empty($content['autoplay']);
            
            $animationLabels = [
                'fade' => ['label' => 'Fade Animation', 'badge' => 'bg-purple-50 text-purple-700 border-purple-200'],
                'slide_right' => ['label' => 'Slide Right', 'badge' => 'bg-blue-50 text-blue-700 border-blue-200'],
                'slide_left' => ['label' => 'Slide Left', 'badge' => 'bg-indigo-50 text-indigo-700 border-indigo-200'],
                'zoom' => ['label' => 'Zoom Effect', 'badge' => 'bg-emerald-50 text-emerald-700 border-emerald-200'],
            ];
            $animInfo = $animationLabels[$animation] ?? $animationLabels['fade'];
        ?>
        <div class="bg-white rounded-[1.5rem] overflow-hidden shadow-custom border border-gray-100 flex flex-col transition-all hover:-translate-y-1 hover:shadow-lg duration-300">
            <!-- Header preview bar -->
            <div class="p-6 bg-gradient-to-br from-gray-50 to-pink-50/30 border-b border-gray-100 flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-xl bg-white shadow-sm flex items-center justify-center text-[#F25996]">
                        <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24"><path d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z"/></svg>
                    </div>
                    <div>
                        <span class="text-[11px] font-bold uppercase tracking-wider text-gray-400">Section ID</span>
                        <p class="text-xs font-mono font-medium text-gray-700"><?= htmlspecialchars($sec['id']) ?></p>
                    </div>
                </div>
                <div>
                    <?php if($isActive): ?>
                        <span class="bg-emerald-100 text-emerald-800 text-[10px] font-bold px-3 py-1 rounded-full uppercase tracking-wider inline-flex items-center gap-1.5">
                            <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                            Active
                        </span>
                    <?php else: ?>
                        <span class="bg-gray-100 text-gray-500 text-[10px] font-bold px-3 py-1 rounded-full uppercase tracking-wider inline-flex items-center gap-1.5">
                            <span class="w-1.5 h-1.5 rounded-full bg-gray-400"></span>
                            Inactive
                        </span>
                    <?php endif; ?>
                </div>
            </div>
            
            <!-- Details Area -->
            <div class="p-6 flex-1 flex flex-col justify-between">
                <div>
                    <h3 class="font-serif font-bold text-gray-900 text-lg line-clamp-1 mb-1"><?= htmlspecialchars($title) ?></h3>
                    <p class="text-xs text-gray-500 line-clamp-2 mb-4"><?= htmlspecialchars($subtitle) ?></p>

                    <div class="space-y-2.5 pt-2">
                        <!-- Animation Tag -->
                        <div class="flex items-center justify-between text-xs">
                            <span class="text-gray-500 font-medium">Animation:</span>
                            <span class="text-[11px] font-bold px-2.5 py-0.5 rounded-md border <?= $animInfo['badge'] ?>">
                                <?= htmlspecialchars($animInfo['label']) ?>
                            </span>
                        </div>

                        <!-- Selected Reviews Count -->
                        <div class="flex items-center justify-between text-xs">
                            <span class="text-gray-500 font-medium">Included Reviews:</span>
                            <span class="font-semibold text-gray-900 bg-gray-50 px-2 py-0.5 rounded border border-gray-100">
                                <?= $reviewCount ?> <?= $reviewCount === 1 ? 'Review' : 'Reviews' ?>
                            </span>
                        </div>

                        <!-- Autoplay Setting -->
                        <div class="flex items-center justify-between text-xs">
                            <span class="text-gray-500 font-medium">Autoplay:</span>
                            <span class="font-semibold <?= $autoplay ? 'text-blue-600' : 'text-gray-400' ?>">
                                <?= $autoplay ? 'Enabled (' . ($content['autoplay_speed'] ?? 4) . 's)' : 'Disabled' ?>
                            </span>
                        </div>
                    </div>
                </div>
                
                <!-- Bottom Action Row -->
                <div class="mt-6 pt-4 border-t border-gray-100 flex items-center justify-between">
                    <span class="text-[11px] text-gray-400 font-medium">
                        Layout Component
                    </span>
                    <div class="flex items-center gap-2">
                        <?php if ($this->hasPermission('cms_reviews', 'edit')): ?>
                        <a href="<?= BASE_URL ?>/admin/cms/reviews/edit?id=<?= $sec['id'] ?>" class="inline-flex items-center justify-center p-1.5 text-blue-600 hover:text-blue-800 hover:bg-blue-50 rounded-lg transition-colors" title="Edit Review Section">
                            <i class="bi bi-pencil-square text-lg"></i>
                        </a>
                        <?php endif; ?>
                        <?php if ($this->hasPermission('cms_reviews', 'delete')): ?>
                        <form action="<?= BASE_URL ?>/admin/cms/reviews/delete" method="POST" class="inline" onsubmit="return confirm('Are you sure you want to delete this Review section?');">
                            <input type="hidden" name="id" value="<?= htmlspecialchars($sec['id']) ?>">
                            <button type="submit" class="inline-flex items-center justify-center p-1.5 text-red-600 hover:text-red-800 hover:bg-red-50 rounded-lg transition-colors" title="Delete Review Section">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                            </button>
                        </form>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
    <?php endif; ?>
</div>

<style>
.font-serif {
    font-family: 'Playfair Display', serif;
}
.shadow-custom { box-shadow: 0 4px 20px rgba(0,0,0,0.03); }
</style>
