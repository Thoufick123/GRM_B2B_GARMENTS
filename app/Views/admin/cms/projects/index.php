<?php
// Project List Page
?>
<div class="mb-8 relative z-10">
    <!-- Breadcrumbs -->
    <div class="text-sm font-medium text-gray-400 mb-2 font-serif tracking-wide">
        Dashboard <span class="mx-1 text-gray-300">›</span> CMS Management <span class="mx-1 text-gray-300">›</span> <span class="text-brand-700">Our Project</span>
    </div>
    
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h2 class="text-3xl sm:text-4xl font-display font-extrabold text-gray-900 tracking-tight">Our Projects</h2>
            <p class="text-sm text-gray-500 mt-1">Manage project portfolios, showcase multiple images, videos, and descriptions displayed dynamically on <a href="<?= BASE_URL ?>/project" target="_blank" class="text-brand-600 underline font-medium hover:text-brand-700">/project</a>.</p>
        </div>
        <div class="flex items-center gap-3">
            <a href="<?= BASE_URL ?>/project" target="_blank" class="bg-gray-100 hover:bg-gray-200 text-gray-700 px-5 py-2.5 rounded-full font-bold text-xs shadow-sm transition-all flex items-center gap-2 uppercase tracking-wider">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"></path></svg>
                View Storefront Page
            </a>
            <?php if ($this->hasPermission('cms_projects', 'create')): ?>
            <a href="<?= BASE_URL ?>/admin/cms/projects/create" class="bg-[#F25996] hover:bg-[#e04481] text-white px-6 py-2.5 rounded-full font-bold text-xs shadow-sm transition-all flex items-center gap-2 uppercase tracking-wider">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                Add Project
            </a>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Stats Counter Bar -->
<div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-6">
    <div class="bg-white p-5 rounded-2xl border border-gray-100 shadow-sm flex items-center gap-4">
        <div class="w-12 h-12 rounded-xl bg-pink-50 text-[#F25996] flex items-center justify-center font-bold text-lg">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path></svg>
        </div>
        <div>
            <div class="text-xs uppercase font-bold tracking-wider text-gray-400">Total Projects</div>
            <div class="text-2xl font-extrabold text-gray-900"><?= count($projects ?? []) ?></div>
        </div>
    </div>
    <div class="bg-white p-5 rounded-2xl border border-gray-100 shadow-sm flex items-center gap-4">
        <div class="w-12 h-12 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center font-bold text-lg">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
        </div>
        <div>
            <div class="text-xs uppercase font-bold tracking-wider text-gray-400">Active Live</div>
            <div class="text-2xl font-extrabold text-gray-900">
                <?= count(array_filter($projects ?? [], fn($p) => !empty($p['is_active']))) ?>
            </div>
        </div>
    </div>
    <div class="bg-white p-5 rounded-2xl border border-gray-100 shadow-sm flex items-center gap-4">
        <div class="w-12 h-12 rounded-xl bg-indigo-50 text-indigo-600 flex items-center justify-center font-bold text-lg">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
        </div>
        <div>
            <div class="text-xs uppercase font-bold tracking-wider text-gray-400">Total Media Assets</div>
            <div class="text-2xl font-extrabold text-gray-900">
                <?php 
                    $mediaCount = 0;
                    foreach ($projects ?? [] as $p) {
                        $mediaCount += count($p['images'] ?? []) + count($p['videos'] ?? []);
                    }
                    echo $mediaCount;
                ?>
            </div>
        </div>
    </div>
</div>

<!-- Project List Table / Grid -->
<?php if (empty($projects)): ?>
    <div class="py-20 text-center bg-white rounded-3xl border border-gray-100 shadow-sm">
        <div class="w-20 h-20 mx-auto mb-4 bg-pink-50 text-[#F25996] rounded-3xl flex items-center justify-center">
            <svg class="w-10 h-10" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path></svg>
        </div>
        <h3 class="text-2xl font-bold text-gray-900">No Projects Created Yet</h3>
        <p class="text-sm text-gray-500 mt-2 max-w-md mx-auto">Create your first project showcase with multiple images, videos, and details. It will automatically be rendered dynamically on the storefront `/project` page.</p>
        <?php if ($this->hasPermission('cms_projects', 'create')): ?>
        <a href="<?= BASE_URL ?>/admin/cms/projects/create" class="inline-flex items-center gap-2 mt-6 bg-[#F25996] hover:bg-[#e04481] text-white px-7 py-3 rounded-full font-bold text-xs uppercase tracking-wider shadow-md hover:shadow-lg transition-all">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
            Add Your First Project
        </a>
        <?php endif; ?>
    </div>
<?php else: ?>
    <div class="bg-white rounded-3xl border border-gray-100 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-gray-50/80 border-b border-gray-100 text-[11px] font-bold text-gray-400 uppercase tracking-wider">
                        <th class="py-4 px-6 w-16 text-center">Sort</th>
                        <th class="py-4 px-6">Project Info</th>
                        <th class="py-4 px-6">Images</th>
                        <th class="py-4 px-6">Videos</th>
                        <th class="py-4 px-6 text-center">Status</th>
                        <th class="py-4 px-6 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 text-sm">
                    <?php foreach ($projects as $project): 
                        $imgList = $project['images'] ?? [];
                        $vidList = $project['videos'] ?? [];
                        $isActive = (bool)$project['is_active'];
                    ?>
                    <tr class="hover:bg-gray-50/50 transition-colors">
                        <!-- Sort Order -->
                        <td class="py-4 px-6 text-center font-mono text-xs font-bold text-gray-500">
                            <?= htmlspecialchars($project['sort_order']) ?>
                        </td>

                        <!-- Title & Subtitle -->
                        <td class="py-4 px-6">
                            <div class="font-bold text-gray-900 text-base">
                                <?= htmlspecialchars($project['title']) ?>
                            </div>
                            <?php if (!empty($project['subtitle'])): ?>
                                <div class="text-xs text-gray-500 font-medium mt-0.5">
                                    <?= htmlspecialchars($project['subtitle']) ?>
                                </div>
                            <?php endif; ?>
                            <?php if (!empty($project['description'])): ?>
                                <div class="text-xs text-gray-400 mt-1 line-clamp-1 max-w-sm">
                                    <?= strip_tags($project['description']) ?>
                                </div>
                            <?php endif; ?>
                        </td>

                        <!-- Images Preview Strip -->
                        <td class="py-4 px-6">
                            <div class="flex items-center gap-1.5 flex-wrap max-w-xs">
                                <?php if (!empty($imgList)): ?>
                                    <?php foreach (array_slice($imgList, 0, 4) as $imgUrl): ?>
                                        <div class="w-10 h-10 rounded-lg overflow-hidden border border-gray-200 bg-gray-50 flex-shrink-0">
                                            <img src="<?= htmlspecialchars($imgUrl) ?>" alt="Project preview" class="w-full h-full object-cover">
                                        </div>
                                    <?php endforeach; ?>
                                    <?php if (count($imgList) > 4): ?>
                                        <span class="w-10 h-10 rounded-lg bg-pink-50 text-[#F25996] text-xs font-bold flex items-center justify-center border border-pink-100">
                                            +<?= count($imgList) - 4 ?>
                                        </span>
                                    <?php endif; ?>
                                    <div class="w-full text-[11px] text-gray-400 font-medium mt-0.5">
                                        <?= count($imgList) ?> image<?= count($imgList) > 1 ? 's' : '' ?>
                                    </div>
                                <?php else: ?>
                                    <span class="text-xs text-gray-400 italic">No images</span>
                                <?php endif; ?>
                            </div>
                        </td>

                        <!-- Videos Preview Strip -->
                        <td class="py-4 px-6">
                            <div class="flex items-center gap-2 flex-wrap">
                                <?php if (!empty($vidList)): ?>
                                    <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold bg-indigo-50 text-indigo-700 border border-indigo-100">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                        <?= count($vidList) ?> video<?= count($vidList) > 1 ? 's' : '' ?>
                                    </span>
                                <?php else: ?>
                                    <span class="text-xs text-gray-400 italic">No videos</span>
                                <?php endif; ?>
                            </div>
                        </td>

                        <!-- Status Toggle -->
                        <td class="py-4 px-6 text-center">
                            <?php if ($this->hasPermission('cms_projects', 'edit')): ?>
                            <button onclick="toggleProjectStatus(<?= $project['id'] ?>, this)" 
                                    class="status-badge inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold cursor-pointer transition-all <?= $isActive ? 'bg-emerald-50 text-emerald-700 border border-emerald-200' : 'bg-gray-100 text-gray-500 border border-gray-200' ?>">
                                <span class="w-2 h-2 rounded-full <?= $isActive ? 'bg-emerald-500' : 'bg-gray-400' ?>"></span>
                                <span><?= $isActive ? 'Active' : 'Inactive' ?></span>
                            </button>
                            <?php else: ?>
                            <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold <?= $isActive ? 'bg-emerald-50 text-emerald-700 border border-emerald-200' : 'bg-gray-100 text-gray-500 border border-gray-200' ?>">
                                <span class="w-2 h-2 rounded-full <?= $isActive ? 'bg-emerald-500' : 'bg-gray-400' ?>"></span>
                                <span><?= $isActive ? 'Active' : 'Inactive' ?></span>
                            </span>
                            <?php endif; ?>
                        </td>

                        <!-- Action Buttons -->
                        <td class="py-4 px-6 text-right space-x-2">
                            <?php if ($this->hasPermission('cms_projects', 'edit')): ?>
                            <a href="<?= BASE_URL ?>/admin/cms/projects/edit?id=<?= $project['id'] ?>" class="inline-flex items-center justify-center p-1 text-blue-600 hover:text-blue-800 transition-colors group/btn" title="Edit">
                                <svg class="w-5 h-5 group-hover/btn:scale-110 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>
                            </a>
                            <?php endif; ?>

                            <?php if ($this->hasPermission('cms_projects', 'delete')): ?>
                            <form action="<?= BASE_URL ?>/admin/cms/projects/delete" method="POST" class="inline-block" onsubmit="return confirm('Are you sure you want to delete this project?');">
                                <input type="hidden" name="id" value="<?= $project['id'] ?>">
                                <button type="submit" class="inline-flex items-center justify-center p-1 text-red-600 hover:text-red-800 transition-colors group/btn cursor-pointer" title="Delete">
                                    <svg class="w-5 h-5 group-hover/btn:scale-110 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                </button>
                            </form>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
<?php endif; ?>

<script>
function toggleProjectStatus(id, btn) {
    btn.disabled = true;
    fetch('<?= BASE_URL ?>/admin/cms/projects/toggle-status', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: 'id=' + encodeURIComponent(id)
    })
    .then(r => r.json())
    .then(data => {
        btn.disabled = false;
        if (data.success) {
            const isActive = data.is_active == 1;
            btn.className = 'status-badge inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold cursor-pointer transition-all ' + 
                            (isActive ? 'bg-emerald-50 text-emerald-700 border border-emerald-200' : 'bg-gray-100 text-gray-500 border border-gray-200');
            btn.innerHTML = `<span class="w-2 h-2 rounded-full ${isActive ? 'bg-emerald-500' : 'bg-gray-400'}"></span><span>${isActive ? 'Active' : 'Inactive'}</span>`;
        } else {
            alert(data.message || 'Error updating status');
        }
    })
    .catch(() => {
        btn.disabled = false;
        alert('Server connection error');
    });
}
</script>
