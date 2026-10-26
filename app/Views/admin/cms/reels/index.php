<div class="mb-8 relative z-10">
    <!-- Breadcrumbs -->
    <div class="text-sm font-medium text-gray-400 mb-2 font-serif tracking-wide">
        Dashboard <span class="mx-1 text-gray-300">›</span> CMS Management <span class="mx-1 text-gray-300">›</span> <span class="text-[#F25996] font-bold">Reels Container</span>
    </div>
    
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h2 class="text-4xl sm:text-5xl font-display font-extrabold text-gray-900 tracking-tight flex items-center gap-3">
                <span>Reels Containers</span>
                <span class="text-xs bg-rose-50 text-rose-600 border border-rose-100 px-3 py-1 rounded-full uppercase tracking-widest font-black">CMS Module</span>
            </h2>
            <p class="text-sm text-gray-500 mt-1">Configure responsive video reels, mobile/desktop view heights in VH, display counts, and Cloudinary media assets.</p>
        </div>
        <div class="flex items-center gap-3">
            <?php if ($this->hasPermission('cms_builder', 'view') || $this->hasAnyPermission('cms_builder')): ?>
            <a href="<?= BASE_URL ?>/admin/cms/builder" class="bg-white hover:bg-gray-50 text-gray-700 border border-gray-200 px-5 py-2.5 rounded-full font-bold text-xs shadow-sm transition-all flex items-center gap-2 uppercase tracking-wider">
                <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 5a1 1 0 011-1h14a1 1 0 011 1v2a1 1 0 01-1 1H5a1 1 0 01-1-1V5zM4 13a1 1 0 011-1h6a1 1 0 011 1v6a1 1 0 01-1 1H5a1 1 0 01-1-1v-6zM16 13a1 1 0 011-1h2a1 1 0 011 1v6a1 1 0 01-1 1h-2a1 1 0 01-1-1v-6z"></path></svg>
                Layout Builder
            </a>
            <?php endif; ?>
            <?php if ($this->hasPermission('cms_reels', 'create')): ?>
            <a href="<?= BASE_URL ?>/admin/cms/reels/create" class="bg-[#F25996] hover:bg-[#e04481] text-white px-6 py-2.5 rounded-full font-bold text-xs shadow-md shadow-[#F25996]/20 transition-all flex items-center gap-2 uppercase tracking-wider">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                Add Reels Container
            </a>
            <?php endif; ?>
        </div>
    </div>
</div>

<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
    <?php if(empty($containers)): ?>
        <div class="col-span-full py-16 text-center text-gray-400 bg-white rounded-3xl border border-gray-100 shadow-sm p-8">
            <div class="w-16 h-16 mx-auto mb-4 bg-pink-50 text-[#F25996] rounded-2xl flex items-center justify-center">
                <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z"></path></svg>
            </div>
            <p class="text-xl font-bold text-gray-800">No Reels Containers Configured</p>
            <p class="text-sm text-gray-500 mt-1 max-w-md mx-auto">Create your first Reels Container with custom desktop/mobile heights, view counts, and Cloudinary video reels.</p>
            <?php if ($this->hasPermission('cms_reels', 'create')): ?>
            <a href="<?= BASE_URL ?>/admin/cms/reels/create" class="inline-flex items-center gap-2 mt-6 bg-[#F25996] hover:bg-[#e04481] text-white px-6 py-2.5 rounded-full font-bold text-xs uppercase tracking-wider shadow-md">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                Create First Reels Container
            </a>
            <?php endif; ?>
        </div>
    <?php else: ?>
        <?php foreach($containers as $c): 
            $content = $c['content'];
            $title = !empty($content['title']) ? $content['title'] : 'Untitled Reels Container';
            $subtitle = !empty($content['subtitle']) ? $content['subtitle'] : '';
            $items = !empty($content['items']) && is_array($content['items']) ? $content['items'] : [];
            $reelsCount = count($items);
            
            // First item preview
            $firstItem = $items[0] ?? null;
            $previewPoster = !empty($firstItem['desktop_image']) ? $firstItem['desktop_image'] : (!empty($firstItem['mobile_image']) ? $firstItem['mobile_image'] : '');
            $previewVideo = !empty($firstItem['video_url']) ? $firstItem['video_url'] : '';
            
            $deskHeight = htmlspecialchars($content['desktop_height_vh'] ?? '70');
            $mobHeight = htmlspecialchars($content['mobile_height_vh'] ?? '55');
            $deskCols = htmlspecialchars((string)($content['desktop_count'] ?? $content['desktop_view_count'] ?? $content['cards_desktop'] ?? '4'));
            $tabCols = htmlspecialchars((string)($content['tablet_view_count'] ?? $content['cards_tablet'] ?? '2'));
            $mobCols = htmlspecialchars((string)($content['mobile_count'] ?? $content['mobile_view_count'] ?? $content['cards_mobile'] ?? '2'));
            $isActive = (bool)$c['is_active'];
        ?>
        <div class="bg-white rounded-3xl overflow-hidden shadow-sm border border-gray-100 flex flex-col transition-all hover:shadow-lg hover:-translate-y-1 duration-300 relative group">
            <!-- Media Preview Area -->
            <div class="h-56 bg-gray-900 relative overflow-hidden flex items-center justify-center">
                <?php if (!empty($previewVideo)): ?>
                    <video src="<?= htmlspecialchars($previewVideo) ?>" class="w-full h-full object-cover opacity-80" muted playsinline loop onmouseover="this.play()" onmouseout="this.pause()"></video>
                    <div class="absolute inset-0 bg-gradient-to-t from-gray-950/80 via-transparent to-black/20 pointer-events-none"></div>
                    <div class="absolute top-3 left-3 bg-black/60 backdrop-blur-md text-white text-[10px] font-bold px-2.5 py-1 rounded-full flex items-center gap-1.5 border border-white/20">
                        <span class="w-2 h-2 rounded-full bg-red-500 animate-pulse"></span>
                        Reel Video
                    </div>
                <?php elseif (!empty($previewPoster)): ?>
                    <img src="<?= htmlspecialchars($previewPoster) ?>" class="w-full h-full object-cover" alt="Poster">
                    <div class="absolute inset-0 bg-gradient-to-t from-gray-950/70 via-transparent to-transparent pointer-events-none"></div>
                <?php else: ?>
                    <div class="flex flex-col items-center justify-center text-gray-500">
                        <svg class="w-12 h-12 text-gray-600 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z"></path></svg>
                        <span class="text-xs font-semibold">No media uploaded</span>
                    </div>
                <?php endif; ?>

                <div class="absolute bottom-3 left-3 right-3 flex items-center justify-between text-white">
                    <span class="text-xs font-bold bg-white/20 backdrop-blur px-2.5 py-1 rounded-lg border border-white/20">
                        🎬 <?= $reelsCount ?> <?= $reelsCount === 1 ? 'Reel' : 'Reels' ?>
                    </span>
                    <?php if ($this->hasPermission('cms_reels', 'edit')): ?>
                    <button type="button" onclick="toggleReelActive('<?= htmlspecialchars($c['id']) ?>', this)" class="text-xs font-bold px-3 py-1 rounded-full transition-all <?= $isActive ? 'bg-emerald-500 text-white' : 'bg-gray-600/80 text-gray-200' ?>">
                        <?= $isActive ? '● Published' : '○ Draft' ?>
                    </button>
                    <?php else: ?>
                    <span class="text-xs font-bold px-3 py-1 rounded-full <?= $isActive ? 'bg-emerald-500 text-white' : 'bg-gray-600/80 text-gray-200' ?>">
                        <?= $isActive ? '● Published' : '○ Draft' ?>
                    </span>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Content Area -->
            <div class="p-5 flex-1 flex flex-col justify-between">
                <div>
                    <h3 class="font-display font-bold text-gray-900 text-base line-clamp-1 mb-1"><?= htmlspecialchars($title) ?></h3>
                    <?php if($subtitle): ?>
                        <p class="text-xs text-gray-500 line-clamp-1 mb-3"><?= htmlspecialchars($subtitle) ?></p>
                    <?php endif; ?>

                    <!-- Height & View Count Badges -->
                    <div class="grid grid-cols-2 gap-2 mt-3 pt-3 border-t border-gray-100 text-xs">
                        <div class="bg-gray-50 p-2 rounded-xl">
                            <span class="text-[10px] uppercase font-bold text-gray-400 block">Desktop</span>
                            <span class="font-bold text-gray-800"><?= $deskHeight ?>vh <span class="text-gray-400 font-normal">• <?= $deskCols ?> visible</span></span>
                        </div>
                        <div class="bg-gray-50 p-2 rounded-xl">
                            <span class="text-[10px] uppercase font-bold text-gray-400 block">Mobile</span>
                            <span class="font-bold text-gray-800"><?= $mobHeight ?>vh <span class="text-gray-400 font-normal">• <?= $mobCols ?> visible</span></span>
                        </div>
                    </div>
                </div>

                <!-- Footer Actions -->
                <div class="mt-5 pt-3 border-t border-gray-100 flex items-center justify-between">
                    <span class="text-[11px] font-mono text-gray-400"><?= htmlspecialchars($c['id']) ?></span>
                    <div class="flex items-center gap-1">
                        <?php if ($this->hasPermission('cms_reels', 'edit')): ?>
                        <a href="<?= BASE_URL ?>/admin/cms/reels/edit?id=<?= urlencode($c['id']) ?>" class="p-2 text-blue-600 hover:text-blue-800 hover:bg-blue-50 rounded-xl transition-all" title="Edit Reels Container">
                            <i class="bi bi-pencil-square text-base"></i>
                        </a>
                        <?php endif; ?>
                        <?php if ($this->hasPermission('cms_reels', 'delete')): ?>
                        <button type="button" onclick="confirmDeleteReels('<?= htmlspecialchars($c['id']) ?>', '<?= htmlspecialchars(addslashes($title)) ?>')" class="p-2 text-red-500 hover:text-red-700 hover:bg-red-50 rounded-xl transition-all" title="Delete">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                        </button>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
    <?php endif; ?>
</div>

<!-- Delete Form Modal -->
<form id="delete-form" method="POST" action="<?= BASE_URL ?>/admin/cms/reels/delete" class="hidden">
    <input type="hidden" name="id" id="delete-id">
</form>

<script>
function toggleReelActive(id, btn) {
    btn.disabled = true;
    const formData = new FormData();
    formData.append('id', id);

    fetch('<?= BASE_URL ?>/admin/cms/reels/toggle-status', {
        method: 'POST',
        body: formData,
        headers: {
            'X-Requested-With': 'XMLHttpRequest'
        }
    })
    .then(r => r.json())
    .then(res => {
        btn.disabled = false;
        if (res.success) {
            if (res.is_active) {
                btn.className = 'text-xs font-bold px-3 py-1 rounded-full transition-all bg-emerald-500 text-white';
                btn.innerHTML = '● Published';
            } else {
                btn.className = 'text-xs font-bold px-3 py-1 rounded-full transition-all bg-gray-600/80 text-gray-200';
                btn.innerHTML = '○ Draft';
            }
        }
    })
    .catch(() => {
        btn.disabled = false;
    });
}

function confirmDeleteReels(id, title) {
    if (confirm(`Are you sure you want to delete the reels container "${title}"? This will also remove it from the Storefront layout.`)) {
        document.getElementById('delete-id').value = id;
        document.getElementById('delete-form').submit();
    }
}
</script>
