<?php
$totalMediaCount = count($mediaFiles);
$inUseCount = 0;
$unusedCount = 0;
foreach ($mediaFiles as $m) {
    if (!empty($m['is_used'])) {
        $inUseCount++;
    } else {
        $unusedCount++;
    }
}
?>

<div class="mb-8 relative z-10">
    <!-- Breadcrumbs -->
    <div class="text-sm font-medium text-gray-400 mb-2 font-serif tracking-wide">
        Dashboard <span class="mx-1 text-gray-300">›</span> <?= $isVendor ? 'Vendor Portal' : 'Media Management' ?> <span class="mx-1 text-gray-300">›</span> <span class="text-brand-700">Media Assets</span>
    </div>
    
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h2 class="text-5xl font-display font-extrabold text-gray-900 tracking-tight">
                <?= $isVendor ? 'My Store Media Library' : 'Platform Media Manager' ?>
            </h2>
            <p class="text-sm text-gray-500 mt-1">
                <?= $isVendor ? 'Upload and manage product photos, catalogs, and media assets for your store.' : 'Central media library with Cloudinary asset management, automatic deduplication, and vendor tracking.' ?>
            </p>
        </div>
        
        <div class="flex items-center space-x-3">
            <?php if ($this->hasPermission('media_manager', 'create')): ?>
            <button onclick="document.getElementById('upload-modal').classList.remove('hidden')" class="bg-[#F25996] hover:bg-[#e04481] text-white px-6 py-3 rounded-full font-bold text-xs shadow-sm transition-all flex items-center gap-2 uppercase tracking-wider cursor-pointer">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                Upload Media
            </button>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Filter Bar -->
<div class="bg-white p-3 rounded-2xl border border-gray-100 shadow-sm mb-6 flex flex-wrap items-center justify-between gap-3">
    <div class="flex items-center space-x-2 flex-wrap gap-y-2">
        <?php if (!$isVendor): ?>
            <a href="<?= BASE_URL ?>/admin/media?scope=admin" class="px-3.5 py-1.5 rounded-xl text-xs font-bold transition <?= empty($currentVendorId) && ($currentScope === 'admin' || empty($currentScope)) ? 'bg-gray-900 text-white shadow-sm' : 'bg-gray-100 text-gray-600 hover:bg-gray-200' ?>">
                🏢 In-House / Admin Direct (<?= ($currentScope === 'admin' || empty($currentScope)) && empty($currentVendorId) ? $totalMediaCount : '' ?>)
            </a>
            <?php if (is_vendor_module_enabled()): ?>
            <a href="<?= BASE_URL ?>/admin/media?scope=vendors" class="px-3.5 py-1.5 rounded-xl text-xs font-bold transition <?= $currentScope === 'vendors' ? 'bg-[#F25996] text-white shadow-sm' : 'bg-pink-50 text-[#F25996] hover:bg-pink-100' ?>">
                🏬 Vendor Media
            </a>
            <a href="<?= BASE_URL ?>/admin/media?scope=all" class="px-3.5 py-1.5 rounded-xl text-xs font-bold transition <?= $currentScope === 'all' ? 'bg-gray-900 text-white shadow-sm' : 'bg-gray-100 text-gray-600 hover:bg-gray-200' ?>">
                🌐 All Media
            </a>
            <?php endif; ?>
            <span class="h-4 w-px bg-gray-200 mx-1 hidden sm:inline-block"></span>
        <?php endif; ?>

        <!-- Quick Usage Filter Tabs -->
        <div class="inline-flex items-center p-1 bg-gray-50 border border-gray-200/80 rounded-xl space-x-1">
            <button type="button" onclick="filterByUsage('all')" data-type="all" class="usage-filter-btn px-3 py-1 rounded-lg text-xs font-bold transition-all bg-gray-900 text-white shadow-xs cursor-pointer">
                All (<?= $totalMediaCount ?>)
            </button>
            <button type="button" onclick="filterByUsage('used')" data-type="used" class="usage-filter-btn px-3 py-1 rounded-lg text-xs font-bold transition-all bg-transparent text-amber-700 hover:bg-amber-50 cursor-pointer flex items-center gap-1">
                <svg class="w-3 h-3 text-amber-500" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M5 9V7a5 5 0 0110 0v2a2 2 0 012 2v5a2 2 0 01-2 2H5a2 2 0 01-2-2v-5a2 2 0 012-2zm8-2v2H7V7a3 3 0 016 0z" clip-rule="evenodd"/></svg>
                In Use (<?= $inUseCount ?>)
            </button>
            <button type="button" onclick="filterByUsage('unused')" data-type="unused" class="usage-filter-btn px-3 py-1 rounded-lg text-xs font-bold transition-all bg-transparent text-emerald-700 hover:bg-emerald-50 cursor-pointer flex items-center gap-1">
                <svg class="w-3 h-3 text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                Unused / Deletable (<?= $unusedCount ?>)
            </button>
        </div>
    </div>

    <?php if (!$isVendor && is_vendor_module_enabled() && !empty($vendors)): ?>
    <div class="flex items-center space-x-2">
        <label for="vendor_filter" class="text-xs font-semibold text-gray-500">Filter by Vendor:</label>
        <select id="vendor_filter" onchange="if(this.value){ window.location.href='<?= BASE_URL ?>/admin/media?vendor_id=' + this.value; } else { window.location.href='<?= BASE_URL ?>/admin/media?scope=admin'; }" class="px-3 py-1.5 text-xs font-semibold border border-gray-200 rounded-xl bg-gray-50 focus:ring-brand-500 focus:border-brand-500">
            <option value="">Select Vendor...</option>
            <?php foreach ($vendors as $v): ?>
                <option value="<?= $v['id'] ?>" <?= (!empty($currentVendorId) && (int)$currentVendorId === (int)$v['id']) ? 'selected' : '' ?>>
                    <?= htmlspecialchars($v['store_name'] ?? $v['name']) ?> (ID: <?= htmlspecialchars($v['unique_vendor_id'] ?? 'VN'.$v['id']) ?>)
                </option>
            <?php endforeach; ?>
        </select>
    </div>
    <?php endif; ?>
</div>

<!-- Upload Modal -->
<div id="upload-modal" class="fixed inset-0 bg-gray-900/50 backdrop-blur-sm z-50 hidden flex items-center justify-center p-4">
    <div class="bg-white rounded-2xl shadow-2xl w-full max-w-md overflow-hidden border border-gray-100">
        <div class="px-6 py-4 border-b border-gray-100 flex justify-between items-center bg-gray-50">
            <h3 class="font-bold text-gray-900 text-base">Upload Media</h3>
            <button onclick="document.getElementById('upload-modal').classList.add('hidden')" class="text-gray-400 hover:text-gray-600">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
            </button>
        </div>
        <form action="<?= BASE_URL ?>/admin/media/upload" method="POST" enctype="multipart/form-data" class="p-6 space-y-4">
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-1.5">Select Image(s) / Video</label>
                <input type="file" name="images[]" multiple accept="image/*,video/*" required class="w-full text-sm text-gray-500 file:mr-4 file:py-2.5 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-bold file:bg-pink-50 file:text-[#F25996] hover:file:bg-pink-100 border border-gray-200 rounded-xl p-2 bg-gray-50">
                <p class="text-[11px] text-gray-400 mt-1">Files are saved and organized automatically.</p>
            </div>
            <div class="pt-4 flex justify-end space-x-3 border-t border-gray-100">
                <button type="button" onclick="document.getElementById('upload-modal').classList.add('hidden')" class="px-4 py-2 border border-gray-200 rounded-xl text-xs font-bold text-gray-600 hover:bg-gray-50 transition">Cancel</button>
                <button type="submit" class="px-5 py-2 bg-[#F25996] text-white rounded-xl text-xs font-bold hover:bg-[#e04481] transition shadow">Upload</button>
            </div>
        </form>
    </div>
</div>

<div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
    <?php if (empty($mediaFiles)): ?>
        <div class="p-12 text-center text-gray-400">
            <svg class="w-12 h-12 mx-auto mb-3 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
            <p class="font-semibold text-gray-600">No media files found.</p>
            <p class="text-xs text-gray-400 mt-1"><?= $isVendor ? 'Upload product photos and shop images using the button above.' : 'No in-house media files uploaded yet.' ?></p>
        </div>
    <?php else: ?>
        <div id="media-grid-container" class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 xl:grid-cols-5 gap-4 p-6">
            <?php foreach ($mediaFiles as $media): 
                $rawUrl = $media['cloudinary_url'] ?? '';
                $mediaUrl = (strpos($rawUrl, 'http://') === 0 || strpos($rawUrl, 'https://') === 0) ? $rawUrl : (BASE_URL . '/' . ltrim($rawUrl, '/'));
                $isVideo = preg_match('/\.(mp4|webm|ogg)$/i', $media['original_filename']) || strpos($rawUrl, '/video/upload/') !== false;
                $isVendorUpload = !empty($media['store_name']) || ($media['uploader_role'] === 'vendor');
                $isUsed = !empty($media['is_used']);
                $usagesList = !empty($media['usages']) ? $media['usages'] : [];
                $usageSummary = $media['usage_summary'] ?? '';
                $usageCount = $media['usage_count'] ?? 0;
            ?>
                <div class="media-card border border-gray-200 rounded-2xl overflow-hidden bg-gray-50 relative group transition-all duration-200 hover:shadow-md hover:border-pink-300 flex flex-col justify-between"
                     data-in-use="<?= $isUsed ? '1' : '0' ?>"
                     data-filename="<?= htmlspecialchars(strtolower($media['original_filename'])) ?>">
                    
                    <div class="relative bg-white w-full h-40 overflow-hidden flex items-center justify-center">
                        <?php if($isVideo): ?>
                            <video src="<?= htmlspecialchars($mediaUrl) ?>" class="w-full h-40 object-cover bg-black" autoplay muted loop playsinline></video>
                        <?php else: ?>
                            <img src="<?= htmlspecialchars($mediaUrl) ?>" alt="<?= htmlspecialchars($media['original_filename']) ?>" class="w-full h-40 object-cover transition-transform duration-300 group-hover:scale-105" onerror="this.onerror=null; this.src='data:image/svg+xml;utf8,<svg xmlns=\'http://www.w3.org/2000/svg\' width=\'100\' height=\'100\' fill=\'%23cbd5e1\' viewBox=\'0 0 24 24\'><path d=\'M21 19V5c0-1.1-.9-2-2-2H5c-1.1 0-2 .9-2 2v14c0 1.1.9 2 2 2h14c1.1 0 2-.9 2-2zM8.5 13.5l2.5 3.01L14.5 12l4.5 6H5l3.5-4.5z\'/></svg>';">
                        <?php endif; ?>

                        <!-- Top-Left Usage Status Badge -->
                        <div class="absolute top-2 left-2 z-10">
                            <?php if ($isUsed): ?>
                                <button type="button" 
                                        onclick="showInUseAlert(<?= htmlspecialchars(json_encode($media['original_filename'])) ?>, <?= htmlspecialchars(json_encode($usagesList)) ?>)"
                                        class="inline-flex items-center gap-1 text-[9px] font-bold bg-amber-500/95 hover:bg-amber-600 text-white px-2 py-0.5 rounded-md backdrop-blur shadow-xs transition-all cursor-pointer"
                                        title="In active use by <?= $usageCount ?> item(s). Click to view details.">
                                    <svg class="w-2.5 h-2.5 shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M5 9V7a5 5 0 0110 0v2a2 2 0 012 2v5a2 2 0 01-2 2H5a2 2 0 01-2-2v-5a2 2 0 012-2zm8-2v2H7V7a3 3 0 016 0z" clip-rule="evenodd"/></svg>
                                    <span>In Use (<?= $usageCount ?>)</span>
                                </button>
                            <?php else: ?>
                                <span class="inline-flex items-center gap-1 text-[9px] font-bold bg-emerald-600/90 text-white px-2 py-0.5 rounded-md backdrop-blur shadow-xs" title="Unused: Safe to delete">
                                    <svg class="w-2.5 h-2.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/></svg>
                                    <span>Unused</span>
                                </span>
                            <?php endif; ?>
                        </div>

                        <!-- Top-Right Action Button -->
                        <?php if ($this->hasPermission('media_manager', 'delete')): ?>
                            <?php if ($isUsed): ?>
                                <!-- Locked: In Use -->
                                <button type="button" 
                                        onclick="showInUseAlert(<?= htmlspecialchars(json_encode($media['original_filename'])) ?>, <?= htmlspecialchars(json_encode($usagesList)) ?>)"
                                        class="absolute top-2 right-2 opacity-0 group-hover:opacity-100 transition-opacity bg-amber-500 hover:bg-amber-600 text-white rounded-full p-1.5 shadow-md backdrop-blur transition-transform hover:scale-110 cursor-pointer z-10" 
                                        title="Asset is currently in use and cannot be deleted. Click to view references.">
                                    <svg class="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M5 9V7a5 5 0 0110 0v2a2 2 0 012 2v5a2 2 0 01-2 2H5a2 2 0 01-2-2v-5a2 2 0 012-2zm8-2v2H7V7a3 3 0 016 0z" clip-rule="evenodd"/></svg>
                                </button>
                            <?php else: ?>
                                <!-- Deletable: Unused -->
                                <form action="<?= BASE_URL ?>/admin/media/delete" method="POST" class="absolute top-2 right-2 opacity-0 group-hover:opacity-100 transition-opacity z-10">
                                    <input type="hidden" name="id" value="<?= $media['id'] ?>">
                                    <button type="button" 
                                            onclick="confirmDeleteMedia(this, <?= htmlspecialchars(json_encode($media['original_filename'])) ?>)" 
                                            class="bg-red-500/90 hover:bg-red-600 text-white rounded-full p-1.5 shadow-md backdrop-blur transition-transform hover:scale-110 cursor-pointer" 
                                            title="Delete unused media">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                    </button>
                                </form>
                            <?php endif; ?>
                        <?php endif; ?>

                        <!-- Uploader Badge for Admin -->
                        <?php if (!$isVendor): ?>
                            <div class="absolute bottom-2 left-2">
                                <?php if ($isVendorUpload): ?>
                                    <span class="inline-flex items-center gap-1 text-[9px] font-bold bg-indigo-600/90 text-white px-2 py-0.5 rounded-md backdrop-blur shadow-xs">
                                        🏪 <?= htmlspecialchars($media['store_name'] ?? $media['uploader_name'] ?? 'Vendor') ?>
                                    </span>
                                <?php else: ?>
                                    <span class="inline-flex items-center gap-1 text-[9px] font-bold bg-gray-900/80 text-white px-2 py-0.5 rounded-md backdrop-blur shadow-xs">
                                        🏢 In-House
                                    </span>
                                <?php endif; ?>
                            </div>
                        <?php endif; ?>
                    </div>

                    <div class="p-3 bg-white border-t border-gray-100 flex-grow flex flex-col justify-between">
                        <div>
                            <p class="text-xs font-bold text-gray-900 truncate" title="<?= htmlspecialchars($media['original_filename']) ?>">
                                <?= htmlspecialchars($media['original_filename']) ?>
                            </p>
                            <p class="text-[10px] text-gray-400 mt-0.5 font-mono truncate" title="<?= htmlspecialchars($media['file_hash'] ?? '') ?>">
                                <?= !empty($media['file_size']) ? number_format($media['file_size'] / 1024, 1) . ' KB' : 'Media Asset' ?>
                            </p>
                        </div>

                        <!-- Usage Location Indicator -->
                        <div class="mt-2 pt-2 border-t border-gray-100">
                            <?php if ($isUsed): ?>
                                <button type="button" 
                                        onclick="showInUseAlert(<?= htmlspecialchars(json_encode($media['original_filename'])) ?>, <?= htmlspecialchars(json_encode($usagesList)) ?>)"
                                        class="w-full text-left flex items-center justify-between text-[10px] text-amber-800 bg-amber-50 hover:bg-amber-100/90 px-2 py-1 rounded-lg transition-colors cursor-pointer group/usg" 
                                        title="Used in: <?= htmlspecialchars($usageSummary) ?>. Click for full list.">
                                    <span class="truncate font-semibold">🔒 <?= htmlspecialchars($usageSummary) ?></span>
                                    <span class="text-[9px] font-black text-amber-600 group-hover/usg:translate-x-0.5 transition-transform shrink-0 ml-1">›</span>
                                </button>
                            <?php else: ?>
                                <div class="flex items-center justify-between text-[10px] text-emerald-700 bg-emerald-50/70 px-2 py-1 rounded-lg">
                                    <span class="font-semibold flex items-center gap-1">
                                        <svg class="w-3 h-3 text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                                        Ready to delete
                                    </span>
                                    <span class="text-[9px] text-emerald-600 font-bold uppercase tracking-wider">Unused</span>
                                </div>
                            <?php endif; ?>
                        </div>

                        <div class="flex items-center justify-between mt-2 pt-1 text-[10px] text-gray-400">
                            <span><?= date('M d, Y', strtotime($media['created_at'])) ?></span>
                            <a href="<?= htmlspecialchars($mediaUrl) ?>" target="_blank" class="text-pink-600 hover:text-pink-700 font-bold" title="Open Full View">View &nearr;</a>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>

        <div id="no-filter-matches" class="p-12 text-center text-gray-400 hidden">
            <svg class="w-10 h-10 mx-auto mb-2 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            <p class="font-semibold text-gray-600 text-sm">No media files match this filter.</p>
            <button type="button" onclick="filterByUsage('all')" class="mt-2 text-xs font-bold text-pink-600 hover:underline">Show All Media</button>
        </div>
    <?php endif; ?>
</div>

<script>
function showInUseAlert(filename, usages) {
    usages = usages || [];
    const listHtml = usages.length > 0 
        ? usages.map(u => `
            <li class="flex items-start gap-2 py-1.5 border-b border-gray-100 last:border-0 text-left">
                <span class="text-amber-500 font-bold shrink-0 mt-0.5 text-xs">●</span>
                <span class="text-gray-800 font-semibold text-xs leading-relaxed break-words">${u}</span>
            </li>`).join('')
        : '<li class="text-xs text-gray-500 text-left">Active platform dependency detected.</li>';

    Swal.fire({
        title: 'Asset Cannot Be Deleted',
        html: `
            <div class="text-center mb-3">
                <div class="w-12 h-12 bg-amber-100 text-amber-600 rounded-full flex items-center justify-center mx-auto mb-2 shadow-xs">
                    <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M5 9V7a5 5 0 0110 0v2a2 2 0 012 2v5a2 2 0 01-2 2H5a2 2 0 01-2-2v-5a2 2 0 012-2zm8-2v2H7V7a3 3 0 016 0z" clip-rule="evenodd"/></svg>
                </div>
                <p class="text-sm font-bold text-gray-900 mb-1">
                    "${filename}"
                </p>
                <p class="text-xs text-gray-500">
                    This media asset is currently linked to active store catalog and CMS items.
                </p>
            </div>
            <div class="bg-amber-50/50 border border-amber-200/80 rounded-2xl p-3 text-xs max-h-48 overflow-y-auto mt-3">
                <p class="font-extrabold text-amber-900 uppercase tracking-wider text-[10px] mb-2 text-left">Active References (${usages.length}):</p>
                <ul class="space-y-0.5">${listHtml}</ul>
            </div>
            <div class="mt-4 p-2.5 bg-gray-50 rounded-xl border border-gray-100 text-[11px] text-gray-500 text-left flex items-start gap-2">
                <svg class="w-4 h-4 text-blue-500 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                <span>To delete this asset, first remove or replace it in the items listed above. Once detached, it will become deletable.</span>
            </div>
        `,
        icon: null,
        confirmButtonText: 'Understood',
        confirmButtonColor: '#F25996',
        customClass: {
            popup: 'rounded-2xl shadow-2xl border border-gray-100 p-6',
            confirmButton: 'font-bold rounded-full px-6 py-2.5 text-xs tracking-wider uppercase shadow-xs cursor-pointer'
        }
    });
}

function confirmDeleteMedia(btn, filename) {
    Swal.fire({
        title: 'Delete Unused Asset?',
        html: `Are you sure you want to permanently delete <b>"${filename}"</b>?<br><br><span class="text-xs text-gray-500">This asset is not in use anywhere and will be permanently removed from storage.</span>`,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#dc2626',
        cancelButtonColor: '#f3f4f6',
        confirmButtonText: 'Yes, Delete Asset',
        cancelButtonText: '<span style="color: #6b7280; font-weight: bold;">Cancel</span>',
        reverseButtons: true,
        customClass: {
            popup: 'rounded-2xl shadow-xl border border-gray-100',
            confirmButton: 'font-bold rounded-full px-6 py-2.5 text-xs tracking-wider uppercase shadow-xs cursor-pointer',
            cancelButton: 'font-bold rounded-full px-6 py-2.5 text-xs tracking-wider uppercase cursor-pointer'
        }
    }).then((result) => {
        if (result.isConfirmed) {
            btn.closest('form').submit();
        }
    });
}

function filterByUsage(type) {
    const cards = document.querySelectorAll('.media-card');
    const tabs = document.querySelectorAll('.usage-filter-btn');
    tabs.forEach(t => {
        if (t.dataset.type === type) {
            t.className = 'usage-filter-btn px-3 py-1 rounded-lg text-xs font-bold transition-all bg-gray-900 text-white shadow-xs cursor-pointer flex items-center gap-1';
        } else {
            const baseColor = t.dataset.type === 'used' ? 'text-amber-700 hover:bg-amber-50' : (t.dataset.type === 'unused' ? 'text-emerald-700 hover:bg-emerald-50' : 'text-gray-600 hover:bg-gray-200');
            t.className = `usage-filter-btn px-3 py-1 rounded-lg text-xs font-bold transition-all bg-transparent ${baseColor} cursor-pointer flex items-center gap-1`;
        }
    });

    let visibleCount = 0;
    cards.forEach(c => {
        const isUsed = c.dataset.inUse === '1';
        let show = true;
        if (type === 'used' && !isUsed) show = false;
        if (type === 'unused' && isUsed) show = false;
        c.style.display = show ? '' : 'none';
        if (show) visibleCount++;
    });

    const noMatchesEl = document.getElementById('no-filter-matches');
    if (noMatchesEl) {
        noMatchesEl.style.display = (visibleCount === 0 && cards.length > 0) ? 'block' : 'none';
    }
}
</script>
