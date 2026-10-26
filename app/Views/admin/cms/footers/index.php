<?php
// List all footer configurations
?>
<style>
@import url('https://fonts.googleapis.com/css2?family=Playfair+Display:wght@600;700&family=Inter:wght@400;500;600;700&display=swap');
.font-serif { font-family: 'Playfair Display', serif; }
</style>

<div class="mb-8 relative z-10">
    <!-- Breadcrumbs -->
    <div class="text-sm font-medium text-gray-400 mb-2 font-serif tracking-wide">
        Dashboard <span class="mx-1 text-gray-300">›</span> CMS Management <span class="mx-1 text-gray-300">›</span> <span class="text-brand-700">Footer Settings</span>
    </div>
    
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h2 class="text-5xl font-display font-extrabold text-gray-900 tracking-tight">Footer Settings</h2>
            <p class="text-sm text-gray-500 mt-1">Manage your storefront footer configuration.</p>
        </div>
        <?php if ($this->hasPermission('cms_footers', 'create')): ?>
        <a href="<?= BASE_URL ?>/admin/cms/footers/create" class="bg-[#F25996] hover:bg-[#e04481] text-white px-6 py-3 rounded-full font-bold text-xs shadow-sm transition-all flex items-center gap-2 uppercase tracking-wider">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
            Add Footer
        </a>
        <?php endif; ?>
    </div>
</div>

<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
    <?php if (empty($footers)): ?>
        <div class="col-span-full py-12 text-center text-gray-400 bg-white rounded-[2rem] border border-gray-100 shadow-sm">
            <svg class="w-12 h-12 mx-auto mb-4 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16h16M4 20h16"></path></svg>
            <p class="text-lg font-medium text-gray-500">No Footer Configured</p>
            <p class="text-sm mt-1">Click "Add Footer" to create your storefront footer.</p>
        </div>
    <?php else: ?>
        <?php foreach ($footers as $footer): 
            $c = $footer['content'];
            $title = $c['title'] ?? 'Footer';
            $bg = $c['theme']['value'] ?? '#1f2937';
            $columns = count($c['columns'] ?? []);
            $email = !empty($c['contact']['email']) ? $c['contact']['email'] : 'No email';
            $isActive = $footer['is_active'];
        ?>
        <div class="bg-white rounded-[1.5rem] overflow-hidden shadow-custom border border-gray-100 flex flex-col transition-transform hover:-translate-y-1 duration-300">
            <!-- Color Area -->
            <div class="h-32 relative overflow-hidden" style="background:<?= htmlspecialchars($bg) ?>;">
            </div>
            
            <!-- Details Area -->
            <div class="p-5 flex-1 flex flex-col">
                <div class="flex justify-between items-start mb-2">
                    <div>
                        <h3 class="font-serif font-bold text-gray-900 text-lg line-clamp-1"><?= htmlspecialchars($title) ?></h3>
                        <p class="text-sm text-gray-500 line-clamp-1"><?= $columns ?> columns &middot; <?= htmlspecialchars($email) ?></p>
                    </div>
                    <?php if($isActive): ?>
                        <span class="bg-green-100 text-green-700 text-[10px] font-bold px-2.5 py-1 rounded-full uppercase tracking-wider">Active</span>
                    <?php else: ?>
                        <span class="bg-gray-100 text-gray-500 text-[10px] font-bold px-2.5 py-1 rounded-full uppercase tracking-wider">Inactive</span>
                    <?php endif; ?>
                </div>
                
                <div class="mt-5 pt-4 border-t border-gray-100 flex items-center justify-between">
                    <!-- Actions -->
                    <div class="flex items-center gap-2">
                        <?php if ($this->hasPermission('cms_footers', 'edit')): ?>
                        <a href="<?= BASE_URL ?>/admin/cms/footers/edit?id=<?= $footer['id'] ?>" class="p-1 text-blue-600 hover:text-blue-800 transition-colors" title="Edit">
                            <i class="bi bi-pencil-square text-lg"></i>
                        </a>
                        <?php endif; ?>
                        <?php if ($this->hasPermission('cms_footers', 'delete')): ?>
                        <form action="<?= BASE_URL ?>/admin/cms/footers/delete" method="POST" class="inline delete-form">
                            <input type="hidden" name="id" value="<?= $footer['id'] ?>">
                            <button type="submit" class="p-1 text-red-600 hover:text-red-800 transition-colors" title="Delete">
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
