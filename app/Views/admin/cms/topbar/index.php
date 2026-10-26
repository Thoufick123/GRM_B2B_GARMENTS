<div class="mb-8 relative z-10">
    <!-- Breadcrumbs -->
    <div class="text-sm font-medium text-gray-400 mb-2 font-serif tracking-wide">
        Dashboard <span class="mx-1 text-gray-300">›</span> CMS Management <span class="mx-1 text-gray-300">›</span> <span class="text-brand-700">Top Announcement Bars</span>
    </div>
    
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h2 class="text-5xl font-display font-extrabold text-gray-900 tracking-tight">Top Announcement Bars</h2>
            <p class="text-sm text-gray-500 mt-1">Manage dynamic top bars with slider, marquee animations, sticky announcements, and dynamic color pickers.</p>
        </div>
        <?php if ($this->hasPermission('cms_topbars', 'create')): ?>
        <a href="<?= BASE_URL ?>/admin/cms/topbars/create" class="bg-[#F25996] hover:bg-[#e04481] text-white px-6 py-3 rounded-full font-bold text-xs shadow-sm transition-all flex items-center gap-2 uppercase tracking-wider">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
            Add Top Bar
        </a>
        <?php endif; ?>
    </div>
</div>

<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
    <?php if(empty($topbars)): ?>
        <div class="col-span-full py-12 text-center text-gray-400 bg-white rounded-[2rem] border border-gray-100 shadow-sm">
            <svg class="w-12 h-12 mx-auto mb-4 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5.882V19.24a1.76 1.76 0 01-3.417.592l-2.147-6.15M18 13a3 3 0 100-6M5.436 13.683A4.001 4.001 0 017 6h1.832c4.1 0 7.625-1.234 9.168-3v14c-1.543-1.766-5.067-3-9.168-3H7a3.988 3.988 0 01-1.564-.317z"></path></svg>
            <p class="text-lg font-medium text-gray-500">No Top Announcement Bars Found</p>
            <p class="text-sm mt-1">Click "Add Top Bar" to create your first announcement bar.</p>
        </div>
    <?php else: ?>
        <?php foreach($topbars as $tb): 
            $content = $tb['content'];
            $title = $content['title'] ?? 'Untitled Top Bar';
            $isActive = $tb['is_active'];
            $settings = $content['global_settings'] ?? [];
            $bgColor = $settings['bg_color'] ?? '#3f4c38';
            $textColor = $settings['text_color'] ?? '#ffffff';
            $mode = ucfirst($settings['mode'] ?? 'slider');
            $announcementsCount = count($content['announcements'] ?? []);
        ?>
        <div class="bg-white rounded-[1.5rem] overflow-hidden shadow-custom border border-gray-100 flex flex-col transition-transform hover:-translate-y-1 duration-300">
            <!-- Visual Bar Preview Header -->
            <div class="px-4 py-3 flex items-center justify-between text-xs font-semibold" style="background-color: <?= htmlspecialchars($bgColor) ?>; color: <?= htmlspecialchars($textColor) ?>;">
                <span class="truncate flex-1 font-medium">
                    <?= !empty($content['announcements'][0]['text']) ? htmlspecialchars($content['announcements'][0]['text']) : 'Announcement preview...' ?>
                </span>
                <span class="text-[10px] uppercase font-bold px-2 py-0.5 rounded bg-white/20 ml-2 flex-shrink-0"><?= $mode ?></span>
            </div>

            <!-- Details Area -->
            <div class="p-5 flex-1 flex flex-col justify-between">
                <div>
                    <div class="flex justify-between items-start mb-2">
                        <div>
                            <h3 class="font-serif font-bold text-gray-900 text-lg line-clamp-1"><?= htmlspecialchars($title) ?></h3>
                        </div>
                        <?php if($isActive): ?>
                            <span class="bg-green-100 text-green-700 text-[10px] font-bold px-2.5 py-1 rounded-full uppercase tracking-wider">Active</span>
                        <?php else: ?>
                            <span class="bg-gray-100 text-gray-500 text-[10px] font-bold px-2.5 py-1 rounded-full uppercase tracking-wider">Inactive</span>
                        <?php endif; ?>
                    </div>
                    <p class="text-xs text-gray-500 mt-1"><?= $announcementsCount ?> Announcement <?= $announcementsCount === 1 ? 'Message' : 'Messages' ?></p>
                </div>
                
                <div class="mt-5 pt-4 border-t border-gray-100 flex items-center justify-between">
                    <span class="text-xs text-gray-400 font-mono">BG: <?= htmlspecialchars($bgColor) ?></span>
                    <!-- Actions -->
                    <div class="flex items-center gap-1.5">
                        <?php if ($this->hasPermission('cms_topbars', 'edit')): ?>
                        <a href="<?= BASE_URL ?>/admin/cms/topbars/edit?id=<?= $tb['id'] ?>" class="inline-flex items-center justify-center p-1 text-blue-600 hover:text-blue-800 transition-colors" title="Edit">
                            <i class="bi bi-pencil-square text-lg"></i>
                        </a>
                        <?php endif; ?>
                        <?php if ($this->hasPermission('cms_topbars', 'delete')): ?>
                        <form action="<?= BASE_URL ?>/admin/cms/topbars/delete" method="POST" class="inline delete-form">
                            <input type="hidden" name="id" value="<?= $tb['id'] ?>">
                            <button type="submit" class="inline-flex items-center justify-center p-1 text-red-600 hover:text-red-800 transition-colors" title="Delete" onclick="return confirm('Are you sure you want to delete this Top Announcement Bar?')">
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
.font-serif { font-family: 'Playfair Display', serif; }
.shadow-custom { box-shadow: 0 4px 20px rgba(0,0,0,0.03); }
</style>
