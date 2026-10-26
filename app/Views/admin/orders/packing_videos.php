<?php
// Formatting helper for storage bytes
function formatStorageSize($bytes) {
    if ($bytes >= 1073741824) {
        return number_format($bytes / 1073741824, 2) . ' GB';
    } elseif ($bytes >= 1048576) {
        return number_format($bytes / 1048576, 2) . ' MB';
    } elseif ($bytes >= 1024) {
        return number_format($bytes / 1024, 2) . ' KB';
    } else {
        return $bytes . ' B';
    }
}
?>

<div class="w-full space-y-6">

    <!-- Header Section -->
    <div class="mb-8 relative z-10">
        <!-- Breadcrumbs -->
        <div class="text-sm font-medium text-gray-400 mb-2 font-serif tracking-wide">
            Dashboard <span class="mx-1 text-gray-300">›</span> Order Management <span class="mx-1 text-gray-300">›</span> <span class="text-brand-700">Packing Videos</span>
        </div>
        
        <div class="flex flex-col md:flex-row md:justify-between md:items-center mb-6 gap-4">
            <div>
                <h2 class="text-5xl font-display font-extrabold text-gray-900 tracking-tight">Packing Videos</h2>
                <p class="text-sm text-gray-500 mt-1">Warehouse quality verification videos and 30-day retention backup lifecycle</p>
            </div>
            
            <div class="flex items-center space-x-3 ml-auto">
                <a href="<?= BASE_URL ?>/admin/orders" class="h-10 bg-white border border-slate-200 hover:bg-slate-50 text-slate-700 text-xs font-bold uppercase tracking-wider px-5 rounded-full inline-flex items-center gap-2 shadow-xs transition active:scale-95">
                    <svg class="w-3.5 h-3.5 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                    <span>Back to Orders</span>
                </a>
                <?php if ($this->hasPermission('packing_videos', 'delete')): ?>
                <button type="button" onclick="triggerCleanup()" class="h-10 bg-[#F25996] border border-[#F25996] hover:bg-[#d94480] hover:border-[#d94480] text-white text-xs font-bold uppercase tracking-wider px-5 rounded-full inline-flex items-center gap-2 shadow-xs transition active:scale-95 cursor-pointer">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                    <span>Run 30-Day Retention Cleanup</span>
                </button>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Stat Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <div class="bg-white p-5 rounded-2xl border border-gray-100 shadow-sm flex items-center justify-between">
            <div>
                <p class="text-xs font-bold text-gray-400 uppercase tracking-wider">Orders with Videos</p>
                <p class="text-2xl font-black text-gray-900 mt-1"><?= $totalWithVideos ?></p>
            </div>
            <div class="w-12 h-12 rounded-xl bg-pink-50 text-[#F25996] flex items-center justify-center font-bold text-xl">
                🎬
            </div>
        </div>

        <div class="bg-white p-5 rounded-2xl border border-gray-100 shadow-sm flex items-center justify-between">
            <div>
                <p class="text-xs font-bold text-gray-400 uppercase tracking-wider">Active Customer Videos</p>
                <p class="text-2xl font-black text-emerald-600 mt-1"><?= $activeVideos ?></p>
            </div>
            <div class="w-12 h-12 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center font-bold text-xl">
                ⏱️
            </div>
        </div>

        <div class="bg-white p-5 rounded-2xl border border-gray-100 shadow-sm flex items-center justify-between">
            <div>
                <p class="text-xs font-bold text-gray-400 uppercase tracking-wider">Expired Retention (>30d)</p>
                <p class="text-2xl font-black text-amber-600 mt-1"><?= $expiredVideos ?></p>
            </div>
            <div class="w-12 h-12 rounded-xl bg-amber-50 text-amber-600 flex items-center justify-center font-bold text-xl">
                ⏳
            </div>
        </div>

        <div class="bg-white p-5 rounded-2xl border border-gray-100 shadow-sm flex items-center justify-between">
            <div>
                <p class="text-xs font-bold text-gray-400 uppercase tracking-wider">Storage Footprint</p>
                <p class="text-2xl font-black text-brand-600 mt-1"><?= formatStorageSize($totalStorageBytes) ?></p>
            </div>
            <div class="w-12 h-12 rounded-xl bg-brand-50 text-brand-600 flex items-center justify-center font-bold text-xl">
                💾
            </div>
        </div>
    </div>

    <!-- Info Policy Banner -->
    <div class="bg-gradient-to-r from-pink-50 via-white to-pink-50/40 p-4 rounded-2xl border border-pink-200/80 flex items-start gap-3">
        <div class="text-[#F25996] text-lg flex-shrink-0 mt-0.5">ℹ️</div>
        <div class="text-xs text-pink-950 leading-relaxed">
            <strong>30-Day Video Retention Policy:</strong> When an order status transitions to <strong>Delivered</strong>, a 30-day access countdown begins. Buyers can view and download quality verification videos directly from their Order History page. After 30 days post-delivery, videos expire automatically to optimize disk storage while order records and Tax Invoices remain permanently archived.
        </div>
    </div>

    <!-- Search & Filter Bar -->
    <div class="bg-white p-4 rounded-2xl border border-gray-100 shadow-sm flex flex-col sm:flex-row gap-3 items-center justify-between">
        <div class="relative w-full sm:w-80">
            <input type="text" id="videoSearchInput" onkeyup="filterVideoTable()" placeholder="Search order #, customer, email..." class="w-full pl-10 pr-4 py-2 bg-gray-50 border border-gray-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-[#F25996] font-medium">
            <svg class="w-4 h-4 text-gray-400 absolute left-3.5 top-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
        </div>

        <div class="flex items-center gap-2 w-full sm:w-auto">
            <select id="videoFilterStatus" onchange="filterVideoTable()" class="px-3 py-2 bg-gray-50 border border-gray-200 rounded-xl text-xs font-bold text-gray-700 focus:outline-none focus:ring-2 focus:ring-[#F25996]">
                <option value="all">All Orders</option>
                <option value="active">Active Retention</option>
                <option value="expired">Expired (>30 Days)</option>
                <option value="missing">Missing Videos</option>
            </select>
        </div>
    </div>

    <!-- Main Table -->
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm" id="videosTable">
                <thead class="bg-gray-50/60 text-xs uppercase text-gray-400 font-bold border-b border-gray-100">
                    <tr>
                        <th class="px-6 py-4">Order</th>
                        <th class="px-6 py-4">Customer</th>
                        <th class="px-6 py-4">Status &amp; Packed At</th>
                        <th class="px-6 py-4">30-Day Retention Lifecycle</th>
                        <th class="px-6 py-4">Packing Videos</th>
                        <th class="px-6 py-4 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 text-gray-600">
                    <?php if (empty($orders)): ?>
                        <tr>
                            <td colspan="6" class="px-6 py-12 text-center text-gray-400">
                                <div class="flex flex-col items-center">
                                    <svg class="w-12 h-12 text-gray-300 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z"/></svg>
                                    <p class="font-bold text-gray-600">No orders found</p>
                                    <p class="text-xs text-gray-400 mt-1">Orders with packing videos will appear here.</p>
                                </div>
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($orders as $ord): 
                            $hasV1 = !empty($ord['packing_video_1']);
                            $hasV2 = !empty($ord['packing_video_2']);
                            $hasAny = $hasV1 || $hasV2;
                            $filterType = 'active';
                            if (!$hasAny) {
                                $filterType = 'missing';
                            } elseif ($ord['is_expired']) {
                                $filterType = 'expired';
                            }
                        ?>
                        <tr class="hover:bg-gray-50/50 transition-colors video-row" 
                            data-search="<?= strtolower($ord['order_number'] . ' ' . $ord['customer_name'] . ' ' . $ord['email'] . ' ' . ($ord['customer_phone'] ?? '')) ?>"
                            data-filter="<?= $filterType ?>">
                            
                            <!-- Order Column -->
                            <td class="px-6 py-4 whitespace-nowrap">
                                <a href="<?= BASE_URL ?>/admin/orders/view?id=<?= htmlspecialchars($ord['order_number']) ?>" class="font-bold text-brand-700 hover:text-brand-900 flex items-center gap-1.5">
                                    #<?= htmlspecialchars($ord['order_number']) ?>
                                    <svg class="w-3.5 h-3.5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
                                </a>
                                <span class="text-[11px] text-gray-400 block"><?= date('d M Y, h:i A', strtotime($ord['created_at'])) ?></span>
                            </td>

                            <!-- Customer Column -->
                            <td class="px-6 py-4">
                                <div class="font-bold text-gray-900 text-sm"><?= htmlspecialchars($ord['customer_name'] ?: 'Guest') ?></div>
                                <?php if (!empty($ord['customer_phone'])): ?>
                                    <span class="text-xs text-gray-500 font-medium block">📞 <?= htmlspecialchars($ord['customer_phone']) ?></span>
                                <?php endif; ?>
                                <span class="text-[11px] text-gray-400 block"><?= htmlspecialchars($ord['email']) ?></span>
                            </td>

                            <!-- Status & Packed Column -->
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="px-2.5 py-0.5 rounded-full text-xs font-bold capitalize <?= 
                                    $ord['status'] === 'delivered' ? 'bg-green-100 text-green-800' :
                                    ($ord['status'] === 'packed' ? 'bg-pink-100 text-[#d94480]' :
                                    ($ord['status'] === 'shipped' ? 'bg-blue-100 text-blue-800' : 'bg-gray-100 text-gray-700'))
                                ?>">
                                    <?= str_replace('_', ' ', $ord['status']) ?>
                                </span>
                                <?php if (!empty($ord['packed_at'])): ?>
                                    <span class="text-[11px] text-gray-500 block mt-1">Packed: <?= date('d M Y, h:i A', strtotime($ord['packed_at'])) ?></span>
                                <?php else: ?>
                                    <span class="text-[11px] text-gray-400 italic block mt-1">Not packed yet</span>
                                <?php endif; ?>
                            </td>

                            <!-- Retention Lifecycle Column -->
                            <td class="px-6 py-4 whitespace-nowrap">
                                <?php if ($ord['is_delivered']): ?>
                                    <?php if ($ord['is_expired']): ?>
                                        <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold bg-gray-100 text-gray-500 border border-gray-200">
                                            ❌ Expired (>30 Days)
                                        </span>
                                        <span class="text-[11px] text-gray-400 block mt-1">Delivered on: <?= !empty($ord['delivered_at']) ? date('d M Y', strtotime($ord['delivered_at'])) : 'N/A' ?></span>
                                    <?php else: ?>
                                        <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold bg-emerald-100 text-emerald-800 border border-emerald-200">
                                            ⏱️ <?= $ord['days_left'] ?? 30 ?> days left
                                        </span>
                                        <span class="text-[11px] text-gray-500 block mt-1">Expires on: <?= !empty($ord['expiry_timestamp']) ? date('d M Y', $ord['expiry_timestamp']) : 'N/A' ?></span>
                                    <?php endif; ?>
                                <?php else: ?>
                                    <span class="inline-flex items-center gap-1 px-3 py-1 rounded-full text-xs font-bold bg-pink-50 text-[#F25996] border border-pink-200">
                                        🔒 Active (Timer starts upon Delivery)
                                    </span>
                                <?php endif; ?>
                            </td>

                            <!-- Packing Videos Column -->
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="flex items-center gap-2">
                                    <?php if ($hasV1): 
                                        $v1Url = (strpos($ord['packing_video_1'], 'http') === 0) ? $ord['packing_video_1'] : (BASE_URL . '/' . ltrim($ord['packing_video_1'], '/'));
                                    ?>
                                        <button type="button" onclick="previewPackingVideo('<?= htmlspecialchars($v1Url) ?>', 'Video 1 (Item & Box Packing) - #<?= htmlspecialchars($ord['order_number']) ?>')" class="px-2.5 py-1 rounded-lg bg-pink-50 hover:bg-pink-100 text-[#F25996] border border-pink-200 font-bold text-xs flex items-center gap-1 transition-colors cursor-pointer" title="Watch Video 1">
                                            📹 Video 1
                                        </button>
                                    <?php endif; ?>

                                    <?php if ($hasV2): 
                                        $v2Url = (strpos($ord['packing_video_2'], 'http') === 0) ? $ord['packing_video_2'] : (BASE_URL . '/' . ltrim($ord['packing_video_2'], '/'));
                                    ?>
                                        <button type="button" onclick="previewPackingVideo('<?= htmlspecialchars($v2Url) ?>', 'Video 2 (Sealing & Label) - #<?= htmlspecialchars($ord['order_number']) ?>')" class="px-2.5 py-1 rounded-lg bg-pink-50 hover:bg-pink-100 text-[#F25996] border border-pink-200 font-bold text-xs flex items-center gap-1 transition-colors cursor-pointer" title="Watch Video 2">
                                            📹 Video 2
                                        </button>
                                    <?php endif; ?>

                                    <?php if (!$hasAny): ?>
                                        <span class="text-xs text-gray-400 italic">No videos</span>
                                    <?php endif; ?>
                                </div>
                            </td>

                            <!-- Actions Column -->
                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                <div class="flex items-center justify-end gap-2">
                                    <a href="<?= BASE_URL ?>/admin/orders/invoice?order=<?= htmlspecialchars($ord['order_number']) ?>" target="_blank" class="inline-flex items-center justify-center p-1 text-[#F25996] hover:text-[#e04481] transition-colors" title="Download Tax Invoice PDF">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                                    </a>

                                    <?php 
                                        $hasAny = !empty($ord['packing_video_1']) || !empty($ord['packing_video_2']);
                                        $canUpload = $hasAny ? ($this->hasPermission('packing_videos', 'edit') || $this->hasPermission('all_orders', 'edit'))
                                                             : ($this->hasPermission('packing_videos', 'create') || $this->hasPermission('packing_videos', 'edit') || $this->hasPermission('all_orders', 'edit'));
                                    ?>
                                    <?php if ($canUpload && in_array($ord['status'], ['placed', 'packed'])): ?>
                                    <button type="button" onclick="openPackingModal('<?= htmlspecialchars($ord['order_number']) ?>')" class="inline-flex items-center justify-center p-1 text-purple-600 hover:text-purple-800 transition-colors" title="<?= $hasAny ? 'Update Packing Videos' : 'Upload Packing Videos' ?>">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z"/></svg>
                                    </button>
                                    <?php endif; ?>

                                    <?php if ($hasAny && ($this->hasPermission('packing_videos', 'delete') || $this->hasPermission('all_orders', 'delete'))): ?>
                                    <button type="button" onclick="deleteOrderPackingVideos('<?= htmlspecialchars($ord['order_number']) ?>')" class="inline-flex items-center justify-center p-1 text-red-500 hover:text-red-700 transition-colors" title="Delete Packing Videos">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                    </button>
                                    <?php endif; ?>

                                    <a href="<?= BASE_URL ?>/admin/orders/view?id=<?= htmlspecialchars($ord['order_number']) ?>" class="inline-flex items-center justify-center p-1 text-emerald-600 hover:text-emerald-800 transition-colors" title="View Details">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                    </a>
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

<!-- Packing Videos Upload Modal -->
<div id="packingModal" class="fixed inset-0 z-50 hidden flex items-center justify-center p-4">
    <div class="fixed inset-0 bg-gray-900 bg-opacity-50 transition-opacity backdrop-blur-sm" onclick="closePackingModal()"></div>
    
    <div class="relative bg-white rounded-2xl shadow-xl w-full max-w-lg p-6 transform transition-all max-h-[92vh] overflow-y-auto">
        <div class="flex items-center justify-between mb-4">
            <div class="flex items-center space-x-3">
                <div class="w-10 h-10 rounded-full bg-pink-100 text-[#F25996] flex items-center justify-center font-bold">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z"/></svg>
                </div>
                <div>
                    <h3 class="text-lg font-bold text-gray-900">Upload Packing Videos</h3>
                    <p class="text-xs text-gray-500">Order <span id="packOrderNumberBadge" class="font-bold text-[#F25996]"></span></p>
                </div>
            </div>
            <button type="button" onclick="closePackingModal()" class="text-gray-400 hover:text-gray-600 p-1.5 rounded-lg hover:bg-gray-100 transition-colors">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
        </div>

        <form id="packingForm" onsubmit="submitPackingDetails(event)" enctype="multipart/form-data">
            <input type="hidden" name="order_id" id="packOrderId">

            <div class="space-y-4 text-sm text-gray-700">
                <div class="bg-pink-50 p-3 rounded-xl text-xs text-[#d94480] border border-pink-100 font-medium">
                    💡 Uploading videos will update status to <strong>Packed</strong>, record timestamps, generate the dynamic Tax Invoice PDF, and automatically email it to the buyer.
                </div>

                <!-- Video 1 -->
                <div>
                    <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1.5">Packing Video 1 (Item &amp; Box - Max 3MB)</label>
                    <input type="file" name="packing_video_1" id="packingVideo1" accept="video/*" class="w-full text-xs text-gray-600 file:mr-3 file:py-2 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-bold file:bg-pink-100 file:text-[#F25996] hover:file:bg-pink-200 border border-gray-200 rounded-xl p-1 bg-gray-50">
                    <p class="text-[11px] text-gray-400 mt-1">Accepts MP4, MOV, WebM video files (Max size: 3MB).</p>
                </div>

                <!-- Video 2 -->
                <div>
                    <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1.5">Packing Video 2 (Sealing &amp; Label - Max 3MB)</label>
                    <input type="file" name="packing_video_2" id="packingVideo2" accept="video/*" class="w-full text-xs text-gray-600 file:mr-3 file:py-2 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-bold file:bg-pink-100 file:text-[#F25996] hover:file:bg-pink-200 border border-gray-200 rounded-xl p-1 bg-gray-50">
                    <p class="text-[11px] text-gray-400 mt-1">Optional secondary camera clip (Max size: 3MB).</p>
                </div>

                <!-- Upload status indicator -->
                <div id="packingUploadStatus" class="hidden p-3 rounded-xl bg-amber-50 border border-amber-200 text-amber-800 text-xs flex items-center gap-2">
                    <svg class="animate-spin h-4 w-4 text-amber-600" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                    <span>Uploading packing videos and generating invoice email...</span>
                </div>
            </div>

            <div class="mt-6 flex justify-end space-x-3">
                <button type="button" onclick="closePackingModal()" id="packingCancelBtn" class="px-4 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 font-bold rounded-xl text-xs transition-colors">
                    Cancel
                </button>
                <button type="submit" id="packingSubmitBtn" class="px-5 py-2 bg-[#F25996] hover:bg-[#d94480] text-white font-bold rounded-xl text-xs transition-colors flex items-center gap-1.5 shadow-sm">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                    Save &amp; Email Invoice
                </button>
            </div>
        </form>
    </div>
</div>

<script>
function filterVideoTable() {
    const searchVal = document.getElementById('videoSearchInput').value.toLowerCase().trim();
    const filterStatus = document.getElementById('videoFilterStatus').value;
    const rows = document.querySelectorAll('.video-row');

    rows.forEach(row => {
        const searchData = row.getAttribute('data-search') || '';
        const filterData = row.getAttribute('data-filter') || '';

        const matchesSearch = !searchVal || searchData.includes(searchVal);
        let matchesFilter = true;
        if (filterStatus !== 'all') {
            matchesFilter = (filterData === filterStatus);
        }

        if (matchesSearch && matchesFilter) {
            row.style.display = '';
        } else {
            row.style.display = 'none';
        }
    });
}

function triggerCleanup() {
    Swal.fire({
        title: 'Run 30-Day Retention Cleanup?',
        text: 'This will purge packing video files from server storage for all orders that have been marked Delivered for more than 30 days. Order details and Tax Invoices will be preserved.',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#F25996',
        cancelButtonColor: '#6b7280',
        confirmButtonText: 'Yes, Clean Up Expired Videos',
        cancelButtonText: 'Cancel'
    }).then((result) => {
        if (result.isConfirmed) {
            Swal.fire({
                title: 'Cleaning Up...',
                text: 'Processing expired packing video files.',
                allowOutsideClick: false,
                didOpen: () => {
                    Swal.showLoading();
                }
            });

            fetch('<?= BASE_URL ?>/admin/orders/cleanup-packing-videos', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' }
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    Swal.fire({
                        title: 'Cleanup Complete!',
                        text: data.message,
                        icon: 'success',
                        confirmButtonColor: '#F25996'
                    }).then(() => {
                        location.reload();
                    });
                } else {
                    Swal.fire('Error', data.message || 'Cleanup failed', 'error');
                }
            })
            .catch(err => {
                console.error(err);
                Swal.fire('Error', 'Failed to communicate with cleanup service.', 'error');
            });
        }
    });
}

// Modal handlers
function openPackingModal(orderId) {
    document.getElementById('packOrderId').value = orderId;
    document.getElementById('packOrderNumberBadge').textContent = '#' + orderId;
    document.getElementById('packingVideo1').value = '';
    document.getElementById('packingVideo2').value = '';
    document.getElementById('packingUploadStatus').classList.add('hidden');
    document.getElementById('packingSubmitBtn').disabled = false;
    document.getElementById('packingCancelBtn').disabled = false;

    document.getElementById('packingModal').classList.remove('hidden');
}

function closePackingModal() {
    document.getElementById('packingModal').classList.add('hidden');
}

function submitPackingDetails(e) {
    e.preventDefault();

    const maxBytes = 3 * 1024 * 1024;
    const v1Input = document.getElementById('packingVideo1');
    const v2Input = document.getElementById('packingVideo2');

    if (v1Input && v1Input.files && v1Input.files[0] && v1Input.files[0].size > maxBytes) {
        const mb = (v1Input.files[0].size / (1024 * 1024)).toFixed(2);
        Swal.fire('File Too Large', `Packing Video 1 file size (${mb}MB) exceeds the 3MB limit. Please upload a video file under 3MB.`, 'warning');
        return false;
    }
    if (v2Input && v2Input.files && v2Input.files[0] && v2Input.files[0].size > maxBytes) {
        const mb = (v2Input.files[0].size / (1024 * 1024)).toFixed(2);
        Swal.fire('File Too Large', `Packing Video 2 file size (${mb}MB) exceeds the 3MB limit. Please upload a video file under 3MB.`, 'warning');
        return false;
    }

    const form = document.getElementById('packingForm');
    const formData = new FormData(form);

    document.getElementById('packingUploadStatus').classList.remove('hidden');
    document.getElementById('packingSubmitBtn').disabled = true;
    document.getElementById('packingCancelBtn').disabled = true;

    fetch('<?= BASE_URL ?>/admin/orders/update-packed', {
        method: 'POST',
        body: formData
    })
    .then(r => r.json())
    .then(res => {
        document.getElementById('packingUploadStatus').classList.add('hidden');
        document.getElementById('packingSubmitBtn').disabled = false;
        document.getElementById('packingCancelBtn').disabled = false;

        if (res.success) {
            closePackingModal();
            Swal.fire({
                title: 'Saved & Invoice Dispatched!',
                text: res.message,
                icon: 'success',
                confirmButtonColor: '#F25996'
            }).then(() => {
                location.reload();
            });
        } else {
            Swal.fire('Error', res.message, 'error');
        }
    })
    .catch(err => {
        console.error(err);
        document.getElementById('packingUploadStatus').classList.add('hidden');
        document.getElementById('packingSubmitBtn').disabled = false;
        document.getElementById('packingCancelBtn').disabled = false;
        Swal.fire('Error', 'Failed to upload packing videos or update status.', 'error');
    });
}

function deleteOrderPackingVideos(orderNumber) {
    Swal.fire({
        title: 'Delete Packing Videos?',
        text: 'Are you sure you want to permanently delete the packing videos for order #' + orderNumber + '? This action cannot be undone.',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#ef4444',
        cancelButtonColor: '#64748b',
        confirmButtonText: 'Yes, Delete Videos'
    }).then((result) => {
        if (result.isConfirmed) {
            Swal.fire({
                title: 'Deleting...',
                allowOutsideClick: false,
                didOpen: () => { Swal.showLoading(); }
            });

            const fd = new FormData();
            fd.append('order_number', orderNumber);

            fetch('<?= BASE_URL ?>/admin/orders/delete-packing-video', {
                method: 'POST',
                body: fd
            })
            .then(r => r.json())
            .then(res => {
                if (res.success) {
                    Swal.fire({
                        title: 'Deleted!',
                        text: res.message,
                        icon: 'success',
                        confirmButtonColor: '#F25996'
                    }).then(() => {
                        location.reload();
                    });
                } else {
                    Swal.fire('Error', res.message || 'Failed to delete packing video.', 'error');
                }
            })
            .catch(err => {
                console.error(err);
                Swal.fire('Error', 'Network or server error while deleting.', 'error');
            });
        }
    });
}

function previewPackingVideo(videoUrl, title) {
    const modal = document.getElementById('videoPreviewModal');
    const player = document.getElementById('previewVideoPlayer');
    const source = document.getElementById('previewVideoSource');
    const titleEl = document.getElementById('videoPreviewTitle');
    const downloadLink = document.getElementById('videoDownloadLink');
    const newTabLink = document.getElementById('videoNewTabLink');

    titleEl.textContent = title || 'Packing Video';
    source.src = videoUrl;
    player.load();
    downloadLink.href = videoUrl;
    newTabLink.href = videoUrl;

    modal.classList.remove('hidden');
    player.play().catch(e => console.log('Autoplay deferred:', e));
}

function closeVideoPreviewModal() {
    const modal = document.getElementById('videoPreviewModal');
    const player = document.getElementById('previewVideoPlayer');
    player.pause();
    player.currentTime = 0;
    modal.classList.add('hidden');
}
</script>

<!-- Video Player Modal -->
<div id="videoPreviewModal" class="fixed inset-0 z-50 hidden flex items-center justify-center p-4">
    <div class="fixed inset-0 bg-gray-900 bg-opacity-70 backdrop-blur-sm transition-opacity" onclick="closeVideoPreviewModal()"></div>
    <div class="relative bg-white rounded-2xl shadow-2xl w-full max-w-2xl overflow-hidden z-10 border border-gray-100">
        <div class="px-5 py-4 border-b border-gray-100 flex items-center justify-between bg-gradient-to-r from-pink-50 to-purple-50">
            <div class="flex items-center gap-2">
                <span class="w-8 h-8 rounded-lg bg-[#F25996] text-white flex items-center justify-center text-sm">📹</span>
                <div>
                    <h3 id="videoPreviewTitle" class="text-sm font-bold text-gray-900">Packing Video</h3>
                    <p class="text-[11px] text-gray-500">Warehouse Quality Verification</p>
                </div>
            </div>
            <div class="flex items-center gap-2">
                <a id="videoDownloadLink" href="#" download target="_blank" class="px-3 py-1.5 rounded-lg bg-white border border-gray-200 text-gray-700 text-xs font-bold hover:bg-gray-50 transition-colors flex items-center gap-1 shadow-2xs">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                    Download
                </a>
                <a id="videoNewTabLink" href="#" target="_blank" class="px-3 py-1.5 rounded-lg bg-white border border-gray-200 text-gray-700 text-xs font-bold hover:bg-gray-50 transition-colors flex items-center gap-1 shadow-2xs">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
                    Open Tab
                </a>
                <button type="button" onclick="closeVideoPreviewModal()" class="w-8 h-8 rounded-lg bg-white border border-gray-200 text-gray-500 hover:text-gray-800 hover:bg-gray-100 flex items-center justify-center transition-colors">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>
        </div>
        <div class="p-4 bg-black flex items-center justify-center">
            <video id="previewVideoPlayer" controls class="w-full max-h-[60vh] rounded-lg shadow-inner bg-black">
                <source id="previewVideoSource" src="" type="video/mp4">
                Your browser does not support the video tag.
            </video>
        </div>
    </div>
</div>
