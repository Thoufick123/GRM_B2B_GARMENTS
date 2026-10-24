<?php
/**
 * Admin Customized Garments & Orders View
 * 
 * @var string $title
 * @var array $orders
 * @var string $currentStatus
 * @var array $statusCounts
 */
?>

<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <!-- Breadcrumbs -->
    <div class="text-sm font-medium text-gray-400 font-serif tracking-wide mb-2">
        Dashboard <span class="mx-1 text-gray-300">›</span> Customization Management <span class="mx-1 text-gray-300">›</span> <span class="text-[#F25996]">Customized Garment Orders & Requests</span>
    </div>

    <!-- Header -->
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 mb-6">
        <div>
            <h1 class="text-3xl sm:text-4xl font-display font-extrabold text-gray-900 tracking-tight">Customized Garment Orders & Requests</h1>
            <p class="text-sm text-gray-500 mt-1">Manage wholesale fabric customization configurations, stitching orders, and quote requests.</p>
        </div>
        <div class="flex items-center flex-wrap gap-2.5">
            <span id="selectedCustomOrdersCountBadge" class="hidden text-xs bg-pink-50 text-[#F25996] border border-pink-200 font-bold px-3.5 py-1.5 rounded-full">0 selected</span>
            <button type="button" onclick="openBulkCustomPackingSlips(false)" class="inline-flex items-center gap-2 px-5 py-2.5 bg-white border border-gray-200 text-gray-700 hover:bg-gray-50 text-xs font-bold rounded-full shadow-xs uppercase tracking-wider transition-colors" title="Print 100mm x 100mm Packing Slips">
                <svg class="w-4 h-4 text-[#F25996]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                <span>Packing Slip</span>
            </button>
            <button type="button" onclick="openBulkCustomPackingSlips(true)" class="inline-flex items-center gap-2 px-5 py-2.5 bg-[#F25996] hover:bg-[#d8407d] text-white text-xs font-bold rounded-full shadow-md uppercase tracking-widest transition-all" title="Instant Print 100mm Slips">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path></svg>
                <span>Print</span>
            </button>
            <a href="<?= BASE_URL ?>/admin/orders/packing-slip-config" class="w-10 h-10 rounded-full bg-white border border-gray-200 text-gray-600 hover:text-[#F25996] flex items-center justify-center transition-colors shadow-xs shrink-0" title="Packing Slip Configuration & Section Ordering">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
            </a>
            <a href="<?= BASE_URL ?>/admin/catalog/products?module=customize" class="inline-flex items-center gap-2 px-5 py-2.5 bg-white border border-gray-200 text-gray-700 hover:bg-gray-50 text-xs font-bold rounded-full shadow-xs uppercase tracking-wider transition-colors">
                <svg class="w-4 h-4 text-[#F25996]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/></svg>
                <span>Customizable Fabrics</span>
            </a>
            <a href="<?= BASE_URL ?>/admin/catalog/products/create?module=customize" class="inline-flex items-center gap-2 px-5 py-2.5 bg-[#F25996] hover:bg-[#d8407d] text-white text-xs font-bold rounded-full shadow-md uppercase tracking-widest transition-all">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                <span>+ Add Customizable Fabric</span>
            </a>
        </div>
    </div>

    <!-- Status Filter Tabs (Image 5 style) -->
    <div class="flex items-center space-x-2 overflow-x-auto mb-6 bg-white/70 backdrop-blur p-1.5 rounded-full border border-gray-100 shadow-sm w-fit max-w-full">
        <?php
        $tabs = [
            'all' => ['label' => 'All Custom Orders', 'count' => $statusCounts['all'] ?? 0],
            'request_pending' => ['label' => 'Quote Requests', 'count' => $statusCounts['request_pending'] ?? 0],
            'paid' => ['label' => 'Razorpay Paid', 'count' => $statusCounts['paid'] ?? 0],
            'placed' => ['label' => 'Placed', 'count' => $statusCounts['placed'] ?? 0],
            'packed' => ['label' => 'Packed', 'count' => $statusCounts['packed'] ?? 0],
            'shipped' => ['label' => 'Shipped', 'count' => $statusCounts['shipped'] ?? 0],
            'out_for_delivery' => ['label' => 'Out for Delivery', 'count' => $statusCounts['out_for_delivery'] ?? 0],
            'delivered' => ['label' => 'Delivered', 'count' => $statusCounts['delivered'] ?? 0],
            'cancelled' => ['label' => 'Cancelled', 'count' => $statusCounts['cancelled'] ?? 0],
        ];
        ?>
        <?php foreach ($tabs as $key => $tab): ?>
            <?php $isActive = $currentStatus === $key; ?>
            <a href="<?= BASE_URL ?>/admin/customize/orders?status=<?= $key ?>" class="px-4 py-2 rounded-full text-xs font-bold transition-all flex items-center gap-2 whitespace-nowrap <?= $isActive ? 'bg-[#F25996] text-white shadow-sm shadow-[#F25996]/30' : 'text-gray-600 hover:bg-gray-100' ?>">
                <span><?= htmlspecialchars($tab['label']) ?></span>
                <span class="px-2 py-0.5 rounded-full text-[10px] font-black <?= $isActive ? 'bg-white text-[#F25996]' : 'bg-pink-50 text-[#F25996] border border-pink-100' ?>">
                    <?= $tab['count'] ?>
                </span>
            </a>
        <?php endforeach; ?>
    </div>

    <!-- Orders Feed -->
    <?php if (empty($orders)): ?>
        <div class="bg-white rounded-3xl p-16 text-center border border-gray-100 shadow-sm">
            <div class="w-16 h-16 rounded-2xl bg-pink-50 text-[#F25996] flex items-center justify-center mx-auto mb-4 border border-pink-100 shadow-xs">
                <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.121 14.121L19 19m-7-7l7-7m-7 7l-2.879 2.879a3 3 0 11-4.242-4.242L11.758 4.758a3 3 0 114.242 4.242L12 12z"/></svg>
            </div>
            <h3 class="text-lg font-bold text-gray-900 mb-1">No Custom Orders Found</h3>
            <p class="text-sm text-gray-500 max-w-md mx-auto mb-6">There are no customized garment orders or requests matching the selected filter status.</p>
            <a href="<?= BASE_URL ?>/admin/customize/orders" class="inline-flex items-center gap-2 px-6 py-2.5 bg-[#F25996] hover:bg-[#d8407d] text-white text-xs font-bold rounded-full uppercase tracking-wider transition-colors shadow-sm">
                View All Custom Orders
            </a>
        </div>
    <?php else: ?>

<style>
.custom-orders-table-container {
    overflow-x: auto !important;
    overflow-y: hidden !important;
    scrollbar-width: thin;
}
.custom-orders-table-container::-webkit-scrollbar:vertical {
    display: none !important;
    width: 0px !important;
}
</style>

<div class="bg-white rounded-2xl shadow-sm border border-gray-200/80 mb-6 overflow-hidden">
    <div class="custom-orders-table-container relative rounded-2xl">
        <table class="w-full text-left border-collapse min-w-[1100px]">
            <thead class="sticky top-0 z-20 bg-gray-50 shadow-2xs">
                <tr class="bg-gray-50 text-gray-500 text-xs uppercase tracking-wider font-semibold">
                    <th class="w-10 px-3 py-4 border-b border-gray-200 text-center">
                        <input type="checkbox" id="selectAllCustomOrdersCb" onchange="toggleSelectAllCustomOrders(this)" class="rounded text-[#F25996] focus:ring-pink-300 w-4 h-4 cursor-pointer" title="Select All Custom Orders">
                    </th>
                    <th class="px-5 py-4 border-b border-gray-200">Order ID</th>
                    <th class="px-5 py-4 border-b border-gray-200">Customer</th>
                    <th class="px-5 py-4 border-b border-gray-200">Date</th>
                    <th class="px-5 py-4 border-b border-gray-200">Item / Fabric</th>
                    <th class="px-5 py-4 border-b border-gray-200 text-center">Qty</th>
                    <th class="px-5 py-4 border-b border-gray-200">Payment</th>
                    <th class="px-5 py-4 border-b border-gray-200">Status</th>
                    <th class="px-5 py-4 border-b border-gray-200 text-right min-w-[100px]">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
            <?php foreach ($orders as $order):
                $initial     = strtoupper(substr($order['user_name'] ?? 'B', 0, 1));
                $colors      = [
                    'bg-[#fdf2f7] text-[#F25996] border border-[#fbaed2]',
                    'bg-pink-100 text-[#e04481] border border-pink-200',
                    'bg-rose-100 text-[#F25996] border border-rose-200',
                    'bg-pink-50 text-[#c72e6b] border border-pink-200',
                    'bg-fuchsia-50 text-[#F25996] border border-fuchsia-200'
                ];
                $avatarClass = $colors[ord($initial) % count($colors)];

                $cStatus = $order['status'] ?? 'placed';
                $statusConfigs = [
                    'placed'           => ['label' => 'Placed',           'icon' => '⏳', 'pill' => 'bg-amber-50 text-amber-900 border-amber-300 hover:bg-amber-100'],
                    'packed'           => ['label' => 'Packed',           'icon' => '📦', 'pill' => 'bg-purple-50 text-purple-900 border-purple-300 hover:bg-purple-100'],
                    'shipped'          => ['label' => 'Shipped',          'icon' => '🚚', 'pill' => 'bg-blue-50 text-blue-900 border-blue-300 hover:bg-blue-100'],
                    'out_for_delivery' => ['label' => 'Out for Delivery', 'icon' => '🛵', 'pill' => 'bg-indigo-50 text-indigo-900 border-indigo-300 hover:bg-indigo-100'],
                    'delivered'        => ['label' => 'Delivered',        'icon' => '✅', 'pill' => 'bg-emerald-50 text-emerald-900 border-emerald-300'],
                    'cancelled'        => ['label' => 'Cancelled',        'icon' => '❌', 'pill' => 'bg-rose-50 text-rose-900 border-rose-200'],
                ];
                $cConf     = $statusConfigs[$cStatus] ?? ['label' => ucwords(str_replace('_',' ',$cStatus)), 'icon' => '•', 'pill' => 'bg-gray-100 text-gray-800 border-gray-200'];
                $isTerminal = in_array($cStatus, ['delivered', 'cancelled']);

                // Summarise items
                $firstItem  = $order['items'][0] ?? [];
                $firstCData = $firstItem['customization_parsed'] ?? [];
                $itemCount  = count($order['items']);
                $totalQty   = array_sum(array_column($order['items'], 'quantity'));
                $orderTotal = array_sum(array_column($order['items'], 'total_price'));
                $rawImg     = !empty($firstItem['primary_image']) ? get_image_url($firstItem['primary_image']) : null;
            ?>
            <tr class="hover:bg-gray-50 transition-colors">
                <td class="px-3 py-4 text-center">
                    <input type="checkbox" class="custom-order-row-cb rounded text-[#F25996] focus:ring-pink-300 w-4 h-4 cursor-pointer" value="<?= $order['id'] ?>" onchange="updateSelectedCustomOrdersCount()">
                </td>

                <!-- Order ID -->
                <td class="px-6 py-4">
                    <span class="font-bold text-gray-900 block font-mono"><?= htmlspecialchars($order['order_number']) ?></span>
                    <span class="inline-flex items-center gap-1 mt-1 text-[10px] font-bold text-amber-700 bg-amber-50 border border-amber-200 px-1.5 py-0.5 rounded">
                        ✂️ Custom
                    </span>
                </td>

                <!-- Customer -->
                <td class="px-6 py-4">
                    <div class="flex items-center space-x-3">
                        <div class="w-10 h-10 rounded-full flex items-center justify-center font-bold <?= $avatarClass ?> flex-shrink-0">
                            <?= $initial ?>
                        </div>
                        <div>
                            <div class="font-semibold text-gray-800"><?= htmlspecialchars($order['user_name'] ?? 'Buyer') ?></div>
                            <div class="text-xs text-gray-400"><?= htmlspecialchars($order['user_phone'] ?? $order['user_email'] ?? '') ?></div>
                        </div>
                    </div>
                </td>

                <!-- Date -->
                <td class="px-6 py-4 text-sm text-gray-600 font-medium whitespace-nowrap">
                    <?= date('n/j/Y', strtotime($order['created_at'])) ?>
                    <div class="text-[11px] text-gray-400"><?= date('h:i A', strtotime($order['created_at'])) ?></div>
                </td>

                <!-- Item / Fabric summary -->
                <td class="px-6 py-4">
                    <div class="flex items-center gap-2.5">
                        <?php if ($rawImg): ?>
                            <img src="<?= htmlspecialchars($rawImg) ?>" class="w-9 h-9 rounded-lg object-cover border border-gray-200 flex-shrink-0" alt="">
                        <?php else: ?>
                            <div class="w-9 h-9 rounded-lg bg-amber-100 border border-amber-200 flex items-center justify-center text-sm flex-shrink-0">✂️</div>
                        <?php endif; ?>
                        <div>
                            <div class="font-semibold text-gray-800 text-sm leading-tight max-w-[180px] truncate">
                                <?= htmlspecialchars($firstItem['product_name'] ?? 'Custom Item') ?>
                            </div>
                            <div class="flex items-center gap-1.5 mt-0.5 flex-wrap">
                                <?php if (!empty($firstCData['program_name'])): ?>
                                    <span class="text-[10px] font-bold text-amber-800 bg-amber-100 px-1.5 py-0.5 rounded truncate max-w-[130px]">
                                        <?= htmlspecialchars($firstCData['program_name']) ?>
                                    </span>
                                <?php endif; ?>
                                <?php if ($itemCount > 1): ?>
                                    <span class="text-[10px] font-bold text-gray-500 bg-gray-100 px-1.5 py-0.5 rounded">+<?= $itemCount - 1 ?> more</span>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </td>

                <!-- Qty + Total -->
                <td class="px-6 py-4 text-center">
                    <span class="font-bold text-gray-900"><?= (int)$totalQty ?></span>
                    <span class="text-[11px] text-gray-400 block">pcs</span>
                    <span class="font-bold text-gray-900 text-sm">₹<?= number_format($orderTotal, 2) ?></span>
                </td>

                <!-- Payment -->
                <td class="px-6 py-4">
                    <div class="flex flex-col gap-1">
                        <span class="font-semibold text-gray-800 text-sm">Razorpay</span>
                        <?php if ($order['payment_status'] === 'paid'): ?>
                            <span class="bg-green-100 text-green-700 text-[10px] font-bold px-1.5 py-0.5 rounded uppercase w-fit">Paid</span>
                        <?php elseif ($order['payment_status'] === 'request_pending'): ?>
                            <span class="bg-amber-100 text-amber-700 text-[10px] font-bold px-1.5 py-0.5 rounded uppercase w-fit">Quote</span>
                        <?php else: ?>
                            <span class="bg-orange-100 text-orange-700 text-[10px] font-bold px-1.5 py-0.5 rounded uppercase w-fit">Pending</span>
                        <?php endif; ?>
                    </div>
                </td>

                <!-- Status -->
                <td class="px-6 py-4">
                    <div class="flex flex-col items-start gap-1.5">
                        <?php if ($this->hasPermission('custom_orders', 'edit') && !$isTerminal): ?>
                            <button type="button"
                                    onclick="openOrderStatusMenu(event, '<?= $order['id'] ?>', '<?= htmlspecialchars($order['order_number']) ?>', '<?= $cStatus ?>')"
                                    class="inline-flex items-center gap-2 px-3.5 py-1.5 rounded-full text-xs font-bold border shadow-2xs cursor-pointer <?= $cConf['pill'] ?>"
                                    id="status-btn-<?= $order['id'] ?>"
                                    data-courier="<?= htmlspecialchars($order['courier_name'] ?? '') ?>"
                                    data-tracking="<?= htmlspecialchars($order['tracking_number'] ?? '') ?>"
                                    data-url="<?= htmlspecialchars($order['tracking_url'] ?? '') ?>">
                                <span><?= $cConf['label'] ?></span>
                                <svg class="w-3.5 h-3.5 opacity-70" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 9l-7 7-7-7"/></svg>
                            </button>
                        <?php else: ?>
                            <span class="inline-flex items-center gap-1.5 px-3.5 py-1.5 rounded-full text-xs font-bold border shadow-2xs <?= $cConf['pill'] ?>">
                                <?php if ($cStatus === 'delivered'): ?>
                                    <svg class="w-3.5 h-3.5 text-emerald-600" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg>
                                <?php elseif ($cStatus === 'cancelled'): ?>
                                    <svg class="w-3.5 h-3.5 text-rose-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"/></svg>
                                <?php endif; ?>
                                <?= $cConf['label'] ?>
                            </span>
                        <?php endif; ?>
                        <?php if (!empty($order['courier_name'])): ?>
                            <span class="bg-gray-100 text-gray-600 text-[10px] font-bold px-2 py-0.5 rounded uppercase tracking-wider">
                                <?= htmlspecialchars($order['courier_name']) ?>
                            </span>
                        <?php endif; ?>
                        <?php if (!empty($order['courier_name']) || in_array($cStatus, ['shipped', 'out_for_delivery'])): ?>
                            <button type="button" onclick="openTrackingModal('<?= $order['id'] ?>', '<?= htmlspecialchars($order['order_number']) ?>', '<?= htmlspecialchars(addslashes($order['courier_name'] ?? '')) ?>', '<?= htmlspecialchars(addslashes($order['tracking_number'] ?? '')) ?>', '<?= htmlspecialchars(addslashes($order['tracking_url'] ?? '')) ?>')"
                                    class="inline-flex items-center gap-1 text-[10px] font-bold bg-blue-50 text-blue-700 border border-blue-200 px-2 py-0.5 rounded-lg hover:bg-blue-100 transition-colors">
                                🚚 <?= htmlspecialchars($order['tracking_number'] ?: 'Add Tracking') ?>
                            </button>
                        <?php endif; ?>

                        <!-- Packing Videos & 30-Day Retention Backup Info -->
                        <?php 
                        $hasV1 = !empty($order['packing_video_1']);
                        $hasV2 = !empty($order['packing_video_2']);
                        $v1Url = $hasV1 ? ((strpos($order['packing_video_1'], 'http') === 0) ? $order['packing_video_1'] : (BASE_URL . '/' . ltrim($order['packing_video_1'], '/'))) : '';
                        $v2Url = $hasV2 ? ((strpos($order['packing_video_2'], 'http') === 0) ? $order['packing_video_2'] : (BASE_URL . '/' . ltrim($order['packing_video_2'], '/'))) : '';
                        $isDelivered = ($cStatus === 'delivered');
                        $delTime = !empty($order['delivered_at']) ? strtotime($order['delivered_at']) : null;
                        $expiryTime = !empty($order['packing_video_expires_at']) ? strtotime($order['packing_video_expires_at']) : ($delTime ? strtotime('+30 days', $delTime) : null);
                        $daysLeft = $expiryTime ? max(0, ceil(($expiryTime - time()) / 86400)) : 30;
                        $isExpired = $expiryTime && (time() > $expiryTime);
                        ?>
                        <?php if ($hasV1 || $hasV2): ?>
                            <div class="mt-0.5 flex flex-col gap-1">
                                <div class="flex items-center gap-1 flex-wrap">
                                    <?php if ($hasV1): ?>
                                        <button type="button" onclick="previewPackingVideo('<?= htmlspecialchars($v1Url) ?>', '<?= htmlspecialchars($order['order_number']) ?> (Video 1)')"
                                                class="inline-flex items-center gap-1 text-[10px] font-bold bg-purple-50 text-purple-700 border border-purple-200 px-2 py-0.5 rounded-lg hover:bg-purple-100 transition-colors" title="Watch Packing Video 1">
                                            📹 Video 1
                                        </button>
                                    <?php endif; ?>
                                    <?php if ($hasV2): ?>
                                        <button type="button" onclick="previewPackingVideo('<?= htmlspecialchars($v2Url) ?>', '<?= htmlspecialchars($order['order_number']) ?> (Video 2)')"
                                                class="inline-flex items-center gap-1 text-[10px] font-bold bg-purple-50 text-purple-700 border border-purple-200 px-2 py-0.5 rounded-lg hover:bg-purple-100 transition-colors" title="Watch Packing Video 2">
                                            📹 Video 2
                                        </button>
                                    <?php endif; ?>
                                </div>
                                <?php if ($isDelivered): ?>
                                    <?php if ($isExpired): ?>
                                        <span class="text-[9.5px] font-bold text-gray-400">❌ Backup Expired (>30d)</span>
                                    <?php else: ?>
                                        <span class="text-[9.5px] font-bold text-emerald-700 bg-emerald-50 border border-emerald-200 px-1.5 py-0.5 rounded-md w-fit flex items-center gap-1">
                                            ⏱️ Backup: <?= $daysLeft ?>d left
                                        </span>
                                    <?php endif; ?>
                                <?php else: ?>
                                    <span class="text-[9.5px] font-medium text-purple-700 bg-purple-50/80 px-1.5 py-0.5 rounded w-fit">
                                        🔒 30-day backup starts on delivery
                                    </span>
                                <?php endif; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </td>

                <!-- Actions -->
                <td class="px-6 py-4 text-right">
                    <div class="flex items-center justify-end space-x-1.5">
                        <?php if ($this->hasPermission('custom_orders', 'edit') && in_array($cStatus, ['placed', 'packed'])): ?>
                            <button type="button" onclick="openPackingModal('<?= $order['id'] ?>', '<?= htmlspecialchars($order['order_number']) ?>')"
                                    class="inline-flex items-center justify-center p-1.5 text-purple-600 hover:text-purple-800 hover:bg-purple-50 rounded-lg transition-colors"
                                    title="<?= ($hasV1 || $hasV2) ? 'Update Packing Videos' : 'Upload Packing Video & Mark Packed' ?>">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z"/></svg>
                            </button>
                        <?php endif; ?>

                        <?php if ($this->hasPermission('bill_modifications', 'edit') || $this->hasPermission('custom_orders', 'edit') || $this->hasPermission('orders', 'edit')): ?>
                            <a href="<?= BASE_URL ?>/admin/order-modifications/create?order=<?= urlencode($order['order_number']) ?>"
                               class="inline-flex items-center justify-center p-1.5 text-amber-600 hover:text-amber-800 hover:bg-amber-50 rounded-lg transition-colors"
                               title="Modify Bill (Edit Order)">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                            </a>
                        <?php endif; ?>

                        <a href="<?= BASE_URL ?>/admin/orders/view?id=<?= $order['order_number'] ?>"
                           class="inline-flex items-center justify-center p-1.5 text-emerald-600 hover:text-emerald-800 hover:bg-emerald-50 rounded-lg transition-colors" title="View Full Order">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                        </a>

                        <a href="<?= BASE_URL ?>/admin/orders/packing-slip?order_id=<?= $order['id'] ?>" target="_blank"
                           class="inline-flex items-center justify-center p-1.5 text-pink-600 hover:text-pink-800 hover:bg-pink-50 rounded-lg transition-colors" title="Packing Slip (100mm × 100mm)">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                        </a>

                        <a href="<?= BASE_URL ?>/admin/orders/packing-slip?order_id=<?= $order['id'] ?>&auto_print=1" target="_blank"
                           class="inline-flex items-center justify-center p-1.5 text-gray-700 hover:text-black hover:bg-gray-100 rounded-lg transition-colors" title="Print Slip">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path></svg>
                        </a>
                    </div>
                </td>

            </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

    <?php endif; ?>
</div>


<!-- Tracking Details Modal -->
<div id="trackingModal" class="fixed inset-0 z-50 flex items-center justify-center p-4 hidden">
    <!-- Backdrop -->
    <div class="fixed inset-0 bg-gray-900 bg-opacity-50 transition-opacity backdrop-blur-sm" onclick="closeTrackingModal()"></div>
    
    <!-- Modal Content -->
    <div class="relative bg-white rounded-3xl shadow-2xl w-full max-w-md p-6 sm:p-7 transform transition-all z-10 border border-gray-100">
        <div class="flex items-center space-x-2.5 mb-6">
            <div class="w-8 h-8 rounded-xl bg-pink-50 text-[#F25996] flex items-center justify-center">
                <svg class="w-5 h-5 text-[#F25996]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path></svg>
            </div>
            <h3 class="text-xl font-bold text-gray-900">Enter Tracking Details</h3>
        </div>
        
        <form id="trackingForm" onsubmit="submitTrackingDetails(event)">
            <input type="hidden" id="trackOrderId">
            <input type="hidden" id="trackOrderNumber">
            
            <div class="mb-4">
                <div class="flex justify-between items-end mb-1.5">
                    <label class="block text-sm font-bold text-gray-700">Courier Service</label>
                    <button type="button" onclick="openManageCouriersModal()" class="text-xs font-bold text-[#F25996] hover:underline focus:outline-none cursor-pointer">+ Add / Manage</button>
                </div>
                <select id="trackCourier" required class="w-full px-3.5 py-2.5 border border-gray-200 rounded-xl text-sm text-gray-900 font-medium focus:outline-none focus:ring-2 focus:ring-[#F25996] bg-white">
                    <option value="" disabled selected>Select Courier Service</option>
                </select>
            </div>
            
            <div class="mb-4">
                <label class="block text-sm font-bold text-gray-700 mb-1.5">Tracking ID</label>
                <input type="text" id="trackId" placeholder="e.g. TRK123456789" class="w-full px-3.5 py-2.5 border border-gray-200 rounded-xl text-sm text-gray-900 font-medium focus:outline-none focus:ring-2 focus:ring-[#F25996]">
            </div>
            
            <div class="mb-6">
                <label class="block text-sm font-bold text-gray-700 mb-1.5">Tracking URL</label>
                <input type="url" id="trackUrl" placeholder="https://tracker.com/TRK123" class="w-full px-3.5 py-2.5 border border-gray-200 rounded-xl text-sm text-gray-900 font-medium focus:outline-none focus:ring-2 focus:ring-[#F25996]">
            </div>
            
            <div class="flex items-center gap-4 mt-6">
                <button type="button" onclick="closeTrackingModal()" class="flex-1 py-2.5 px-4 bg-gray-100 hover:bg-gray-200 text-gray-800 font-bold rounded-full text-center transition-colors">Cancel</button>
                <button type="submit" class="flex-1 py-2.5 px-4 bg-[#F25996] hover:bg-[#e04885] text-white font-bold rounded-full text-center transition-colors shadow-sm">Save</button>
            </div>
        </form>
    </div>
</div>

<!-- Manage Couriers Modal -->
<div id="manageCouriersModal" class="fixed inset-0 z-[60] hidden flex items-center justify-center p-4">
    <!-- Backdrop -->
    <div class="fixed inset-0 bg-gray-900 bg-opacity-60 transition-opacity backdrop-blur-xs" onclick="closeManageCouriersModal()"></div>
    
    <!-- Modal Content -->
    <div class="relative bg-white rounded-2xl shadow-2xl w-full max-w-md p-6 transform transition-all z-10">
        <div class="flex items-center justify-between pb-3 border-b border-gray-100 mb-4">
            <div class="flex items-center gap-2">
                <span class="text-xl">🚚</span>
                <h3 class="text-lg font-bold text-gray-900">Manage Courier Services</h3>
            </div>
            <button type="button" onclick="closeManageCouriersModal()" class="text-gray-400 hover:text-gray-600 p-1.5 rounded-xl hover:bg-gray-100 transition-colors">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
        </div>

        <!-- Add New Courier Form -->
        <div class="mb-5 bg-gray-50/80 p-3.5 rounded-2xl border border-gray-200/80">
            <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-2">Add New Courier</label>
            <div class="flex gap-2">
                <input type="text" id="newCourierName" placeholder="e.g. DTDC, Delhivery..." class="flex-1 px-3.5 py-2 bg-white border border-gray-200 rounded-xl text-sm font-medium focus:outline-none focus:ring-2 focus:ring-[#F25996]">
                <button type="button" onclick="addNewCourierService()" class="px-4 py-2 bg-[#F25996] hover:bg-[#e04481] text-white text-xs font-bold rounded-xl shadow-xs transition-all shrink-0">
                    + Add
                </button>
            </div>
        </div>

        <!-- List of Couriers -->
        <div class="flex items-center justify-between mb-2">
            <label class="text-xs font-bold text-gray-500 uppercase tracking-wider">Available Dropdown Couriers</label>
            <span id="couriersCountBadge" class="text-[11px] font-bold text-gray-400"></span>
        </div>
        
        <div id="couriersListContainer" class="max-h-60 overflow-y-auto space-y-1.5 pr-1 divide-y divide-gray-100">
            <!-- Dynamically populated rows with delete buttons -->
        </div>

        <div class="mt-6 pt-3 border-t border-gray-100 flex justify-end">
            <button type="button" onclick="closeManageCouriersModal()" class="px-5 py-2.5 bg-gray-900 hover:bg-gray-800 text-white text-xs font-bold rounded-xl transition-colors shadow-xs">
                Done
            </button>
        </div>
    </div>
</div>

<!-- Packing Videos Modal -->
<div id="packingModal" class="fixed inset-0 z-50 flex items-center justify-center p-4 hidden">
    <!-- Backdrop -->
    <div class="fixed inset-0 bg-gray-900 bg-opacity-50 transition-opacity backdrop-blur-sm" onclick="closePackingModal()"></div>
    
    <!-- Modal Content -->
    <div class="relative bg-white rounded-3xl shadow-2xl w-full max-w-lg p-6 sm:p-7 transform transition-all z-10 border border-gray-100">
        <div class="flex items-center justify-between pb-4 mb-4 border-b border-gray-100">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-2xl bg-purple-50 text-purple-700 flex items-center justify-center shadow-xs">
                    <svg class="w-5 h-5 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z"/></svg>
                </div>
                <div>
                    <h3 class="text-lg font-bold text-gray-900">Upload Order Packing Videos</h3>
                    <p class="text-xs text-gray-500">Custom Order: <span id="packOrderNumberBadge" class="font-mono font-bold text-purple-700"></span></p>
                </div>
            </div>
            <button type="button" onclick="closePackingModal()" class="text-gray-400 hover:text-gray-600 p-1">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
        </div>

        <div class="bg-purple-50 border border-purple-200/80 rounded-2xl p-4 mb-5 text-xs text-purple-900 flex items-start gap-3">
            <svg class="w-5 h-5 text-purple-600 mt-0.5 flex-shrink-0" style="width: 20px; height: 20px; min-width: 20px;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            <div class="space-y-1 flex-1">
                <strong class="font-bold text-purple-950">Mandatory Packing Video &amp; 30-Day Backup:</strong>
                <p class="text-[11.5px] text-purple-900 leading-relaxed">
                    Uploading packing video marks this order as <strong>Packed</strong>, attaches the generated <strong>Tax Invoice PDF</strong>, and emails it to the customer. Videos remain stored &amp; backed up for <strong>30 days after delivery date</strong>, after which expired videos are auto-cleared.
                </p>
            </div>
        </div>
        
        <form id="packingForm" onsubmit="submitPackingDetails(event)" enctype="multipart/form-data">
            <input type="hidden" id="packOrderId" name="order_id">
            
            <!-- Video 1 -->
            <div class="mb-4">
                <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1.5">
                    Packing Video 1 <span class="text-rose-500">*</span> <span class="normal-case font-normal text-gray-500">(Garment Inspection &amp; Folding - Max 3MB)</span>
                </label>
                <div class="border-2 border-dashed border-gray-300 rounded-2xl p-3.5 hover:border-purple-500 transition-colors bg-gray-50/60">
                    <input type="file" id="packingVideo1" name="packing_video_1" accept="video/*,.mp4,.webm,.mov" onchange="validateVideoSize(this, 'Packing Video 1')" class="w-full text-xs text-gray-600 file:mr-3 file:py-1.5 file:px-3 file:rounded-xl file:border-0 file:text-xs file:font-bold file:bg-purple-100 file:text-purple-700 hover:file:bg-purple-200 cursor-pointer">
                    <p class="text-[11px] text-gray-400 mt-1.5">MP4, WebM, MOV, MKV (Maximum 3MB file size)</p>
                </div>
            </div>
            
            <!-- Video 2 -->
            <div class="mb-5">
                <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1.5">
                    Packing Video 2 <span class="normal-case font-normal text-gray-500">(Optional: Parcel Sealing &amp; Shipping Label - Max 3MB)</span>
                </label>
                <div class="border-2 border-dashed border-gray-300 rounded-2xl p-3.5 hover:border-purple-500 transition-colors bg-gray-50/60">
                    <input type="file" id="packingVideo2" name="packing_video_2" accept="video/*,.mp4,.webm,.mov" onchange="validateVideoSize(this, 'Packing Video 2')" class="w-full text-xs text-gray-600 file:mr-3 file:py-1.5 file:px-3 file:rounded-xl file:border-0 file:text-xs file:font-bold file:bg-purple-100 file:text-purple-700 hover:file:bg-purple-200 cursor-pointer">
                    <p class="text-[11px] text-gray-400 mt-1.5">MP4, WebM, MOV (Optional secondary video)</p>
                </div>
            </div>
            
            <div id="packingUploadStatus" class="hidden mb-4 p-3.5 bg-purple-100 border border-purple-300 rounded-2xl text-center text-xs font-bold text-purple-900">
                <div class="inline-block animate-spin rounded-full h-4 w-4 border-2 border-purple-700 border-t-transparent mr-2 align-middle"></div>
                Uploading packing video &amp; generating customer Tax Invoice PDF...
            </div>

            <div class="flex items-center gap-3 mt-6">
                <button type="button" onclick="closePackingModal()" id="packingCancelBtn" class="flex-1 py-2.5 px-4 bg-gray-100 hover:bg-gray-200 text-gray-800 font-bold rounded-full text-center transition-colors">Cancel</button>
                <button type="submit" id="packingSubmitBtn" class="flex-1 py-2.5 px-4 bg-[#F25996] hover:bg-[#e04885] text-white font-bold rounded-full text-center transition-colors shadow-sm flex items-center justify-center gap-2">
                    <span>Confirm Packed &amp; Send</span>
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Video Player Preview Modal -->
<div id="videoPreviewModal" class="fixed inset-0 z-50 flex items-center justify-center p-4 hidden">
    <!-- Backdrop -->
    <div class="fixed inset-0 bg-gray-900 bg-opacity-70 transition-opacity backdrop-blur-sm" onclick="closeVideoPreviewModal()"></div>
    
    <!-- Modal Card -->
    <div class="relative bg-white rounded-3xl shadow-2xl w-full max-w-2xl p-5 z-10 border border-gray-200">
        <div class="flex items-center justify-between pb-3 mb-3 border-b border-gray-100">
            <h3 id="videoPreviewTitle" class="text-sm font-bold text-gray-900 flex items-center gap-2">
                <span>📹</span> <span>Packing Video Preview</span>
            </h3>
            <button type="button" onclick="closeVideoPreviewModal()" class="text-gray-400 hover:text-gray-600 p-1">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
        </div>
        <div class="bg-black rounded-2xl overflow-hidden aspect-video flex items-center justify-center">
            <video id="videoPreviewPlayer" controls class="w-full h-full object-contain"></video>
        </div>
    </div>
</div>

<!-- Global Status Dropdown Portal Menu (Static positioning, closes on scroll) -->
<div id="orderStatusPortal" class="fixed inset-0 z-[9999] hidden">
    <!-- Backdrop to close on click outside -->
    <div class="fixed inset-0 bg-black/5" onclick="closeOrderStatusPortal()"></div>
    
    <!-- Static Positioned Card Container -->
    <div id="orderStatusFloatingCard" class="fixed bg-white rounded-2xl shadow-2xl border border-gray-100 p-2 min-w-[240px] max-w-[280px] z-[10000] ring-1 ring-black/5 flex flex-col gap-1" style="top: 0px; left: 0px;">
        <div class="px-3 py-2 border-b border-gray-100 flex items-center justify-between mb-1 bg-gray-50/80 -mx-2 -mt-2 rounded-t-2xl">
            <span class="text-[10px] font-bold text-gray-500 uppercase tracking-wider">Change Custom Order Status</span>
            <span id="portalOrderNumber" class="text-[11px] font-mono font-bold text-[#F25996]"></span>
        </div>
        <div id="portalStatusOptions" class="flex flex-col gap-1">
            <!-- Dynamic Options Injected by JS -->
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
const STATUS_CONFIG = {
    'placed':           { label: 'Placed', icon: '⏳', badge: 'bg-amber-100 text-amber-800' },
    'packed':           { label: 'Packed', icon: '📦', badge: 'bg-purple-100 text-purple-800' },
    'shipped':          { label: 'Shipped', icon: '🚚', badge: 'bg-blue-100 text-blue-800' },
    'out_for_delivery': { label: 'Out for Delivery', icon: '🛵', badge: 'bg-indigo-100 text-indigo-800' },
    'delivered':        { label: 'Delivered', icon: '✅', badge: 'bg-emerald-100 text-emerald-800' },
    'cancelled':        { label: 'Cancelled', icon: '❌', badge: 'bg-rose-100 text-rose-800' }
};

const ALLOWED_TRANSITIONS = {
    'placed':           ['packed', 'cancelled'],
    'packed':           ['shipped', 'cancelled'],
    'shipped':          ['out_for_delivery'],
    'out_for_delivery': ['delivered'],
    'delivered':        [],
    'cancelled':        []
};

let currentActiveOrderId = null;
let currentActiveOrderNumber = null;
let currentActiveBtn = null;

function closeOrderStatusPortal() {
    const portal = document.getElementById('orderStatusPortal');
    if (portal) portal.classList.add('hidden');
}

window.addEventListener('scroll', closeOrderStatusPortal, true);
window.addEventListener('resize', closeOrderStatusPortal);

function openOrderStatusMenu(event, orderId, orderNumber, currentStatus) {
    if (event) {
        event.preventDefault();
        event.stopPropagation();
    }
    currentActiveOrderId = orderId;
    currentActiveOrderNumber = orderNumber;
    currentActiveBtn = event ? event.currentTarget : document.getElementById('status-btn-' + orderId);

    const portal = document.getElementById('orderStatusPortal');
    const card = document.getElementById('orderStatusFloatingCard');
    const badge = document.getElementById('portalOrderNumber');
    const optionsContainer = document.getElementById('portalStatusOptions');

    if (!portal || !card || !optionsContainer) return;

    badge.textContent = '#' + orderNumber;

    const allowed = ALLOWED_TRANSITIONS[currentStatus] || [];
    const curConf = STATUS_CONFIG[currentStatus] || { label: currentStatus, icon: '•' };

    let html = `
        <div class="px-3 py-2 rounded-xl bg-gray-50 flex items-center justify-between border border-gray-200/80 mb-1">
            <div class="flex items-center gap-2">
                <span class="text-base">${curConf.icon}</span>
                <span class="text-xs font-bold text-gray-800">${curConf.label}</span>
            </div>
            <span class="text-[9px] font-black uppercase tracking-wider bg-gray-200 text-gray-700 px-2 py-0.5 rounded-full">Current</span>
        </div>
    `;

    if (allowed.length === 0) {
        html += `
            <div class="px-3 py-2 text-center text-xs text-gray-400 font-medium">
                No further status transitions available
            </div>
        `;
    } else {
        html += `<div class="text-[10px] font-bold text-gray-400 uppercase tracking-wider px-2 py-0.5">Advance Status To:</div>`;
        allowed.forEach(st => {
            const conf = STATUS_CONFIG[st] || { label: st, icon: '•' };
            const isCancel = (st === 'cancelled');
            const btnStyle = isCancel 
                ? 'hover:bg-red-50 text-red-700 border-red-100 hover:border-red-200' 
                : 'hover:bg-pink-50 text-gray-800 hover:text-pink-700 border-gray-100 hover:border-pink-200';
            const actionLabel = isCancel ? 'Cancel Order' : `Advance to ${conf.label}`;

            html += `
                <button type="button" 
                        onclick="selectOrderStatusOption('${orderId}', '${orderNumber}', '${currentStatus}', '${st}')"
                        class="w-full text-left px-3 py-2 rounded-xl text-xs font-bold ${btnStyle} flex items-center justify-between transition-colors group border shadow-2xs cursor-pointer">
                    <span class="flex items-center gap-2">
                        <span class="text-base">${conf.icon}</span>
                        <span>${actionLabel}</span>
                    </span>
                    <svg class="w-4 h-4 text-gray-400 group-hover:text-pink-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                </button>
            `;
        });
    }

    optionsContainer.innerHTML = html;
    portal.classList.remove('hidden');

    const rect = currentActiveBtn ? currentActiveBtn.getBoundingClientRect() : { top: 100, left: 100, bottom: 130 };
    const cardWidth = 260;
    const cardHeight = card.offsetHeight || 200;

    let top = rect.bottom + 6;
    let left = rect.left;

    if (top + cardHeight > window.innerHeight - 15) {
        top = Math.max(15, rect.top - cardHeight - 6);
    }
    if (left + cardWidth > window.innerWidth - 15) {
        left = Math.max(15, window.innerWidth - cardWidth - 15);
    }

    card.style.top = top + 'px';
    card.style.left = left + 'px';
}

function selectOrderStatusOption(orderId, orderNumber, currentStatus, newStatus) {
    closeOrderStatusPortal();

    // Packing video modal required for moving from placed to packed
    if (newStatus === 'packed') {
        openPackingModal(orderId, orderNumber);
        return;
    }

    if (newStatus === 'shipped') {
        const courier = currentActiveBtn ? (currentActiveBtn.getAttribute('data-courier') || '') : '';
        const tracking = currentActiveBtn ? (currentActiveBtn.getAttribute('data-tracking') || '') : '';
        const url = currentActiveBtn ? (currentActiveBtn.getAttribute('data-url') || '') : '';
        openTrackingModal(orderId, orderNumber, courier, tracking, url);
        return;
    }

    const curLabel = (STATUS_CONFIG[currentStatus] || {}).label || currentStatus;
    const nextLabel = (STATUS_CONFIG[newStatus] || {}).label || newStatus;

    Swal.fire({
        title: newStatus === 'cancelled' ? 'Cancel Custom Order?' : 'Update Order Status?',
        html: `Are you sure you want to transition Order <b>#${orderNumber}</b> from <span class="text-gray-600 font-semibold">${curLabel}</span> to <span class="text-[#F25996] font-bold">${nextLabel}</span>?`,
        icon: newStatus === 'cancelled' ? 'warning' : 'question',
        showCancelButton: true,
        confirmButtonColor: newStatus === 'cancelled' ? '#dc2626' : '#ea580c',
        cancelButtonColor: '#6b7280',
        confirmButtonText: newStatus === 'cancelled' ? 'Yes, cancel order' : `Yes, advance to ${nextLabel}`
    }).then((result) => {
        if (result.isConfirmed) {
            executeStatusUpdate(orderId, newStatus);
        }
    });
}

let globalCouriersList = [];

function escapeHtmlLocal(str) {
    if (!str) return '';
    return String(str).replace(/[&<>"']/g, function(m) {
        return { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#039;' }[m];
    });
}

function loadCourierServices(selectedCourier = '') {
    return fetch('<?= BASE_URL ?>/admin/orders/courier-services')
        .then(r => r.json())
        .then(res => {
            if (res.success && Array.isArray(res.couriers)) {
                globalCouriersList = res.couriers;
                renderCourierDropdown(selectedCourier);
                renderCourierManagerList();
            }
        })
        .catch(err => console.error('Failed to load couriers', err));
}

function renderCourierDropdown(selectedCourier = '') {
    const select = document.getElementById('trackCourier');
    if (!select) return;

    const currentVal = selectedCourier || select.value;
    select.innerHTML = '<option value="" disabled selected>Select Courier Service</option>';

    let foundCurrent = false;
    globalCouriersList.forEach(c => {
        const opt = document.createElement('option');
        opt.value = c.name;
        opt.textContent = c.name;
        if (currentVal && currentVal.toLowerCase() === c.name.toLowerCase()) {
            opt.selected = true;
            foundCurrent = true;
        }
        select.appendChild(opt);
    });

    if (currentVal && !foundCurrent) {
        const customOpt = document.createElement('option');
        customOpt.value = currentVal;
        customOpt.textContent = currentVal;
        customOpt.selected = true;
        select.appendChild(customOpt);
    }
}

function renderCourierManagerList() {
    const container = document.getElementById('couriersListContainer');
    const badge = document.getElementById('couriersCountBadge');
    if (!container) return;

    if (badge) badge.textContent = `${globalCouriersList.length} options`;

    if (globalCouriersList.length === 0) {
        container.innerHTML = '<p class="text-xs text-gray-400 py-3 text-center italic">No courier services configured yet.</p>';
        return;
    }

    container.innerHTML = globalCouriersList.map(c => `
        <div class="flex items-center justify-between py-2 px-2 hover:bg-gray-50 rounded-xl transition-colors group">
            <div class="flex items-center gap-2.5 min-w-0">
                <span class="text-gray-400 text-sm">🚚</span>
                <span class="text-sm font-semibold text-gray-800 truncate">${escapeHtmlLocal(c.name)}</span>
            </div>
            <button type="button" onclick="deleteCourierServiceItem(${c.id}, '${escapeHtmlLocal(c.name).replace(/'/g, "\\'")}')" 
                    class="p-1.5 text-gray-400 hover:text-rose-600 hover:bg-rose-50 rounded-lg transition-colors cursor-pointer" 
                    title="Delete ${escapeHtmlLocal(c.name)}">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
            </button>
        </div>
    `).join('');
}

window.openManageCouriersModal = function() {
    const modal = document.getElementById('manageCouriersModal');
    if (modal) {
        modal.classList.remove('hidden');
        renderCourierManagerList();
        setTimeout(() => {
            const input = document.getElementById('newCourierName');
            if (input) input.focus();
        }, 100);
    }
};

window.closeManageCouriersModal = function() {
    const modal = document.getElementById('manageCouriersModal');
    if (modal) modal.classList.add('hidden');
};

window.addNewCourierService = function() {
    const input = document.getElementById('newCourierName');
    const name = input ? input.value.trim() : '';
    if (!name) {
        Swal.fire({
            icon: 'warning',
            title: 'Courier Name Required',
            text: 'Please enter a name for the courier service.',
            confirmButtonColor: '#F25996'
        });
        return;
    }

    fetch('<?= BASE_URL ?>/admin/orders/courier-services/add', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ name: name })
    })
    .then(r => r.json())
    .then(res => {
        if (res.success) {
            input.value = '';
            globalCouriersList = res.couriers || [];
            renderCourierManagerList();
            renderCourierDropdown(res.added_name || name);
            Swal.fire({
                toast: true,
                position: 'top-end',
                icon: 'success',
                title: `'${name}' added to couriers`,
                showConfirmButton: false,
                timer: 2000
            });
        } else {
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: res.message || 'Failed to add courier.',
                confirmButtonColor: '#F25996'
            });
        }
    })
    .catch(err => {
        console.error(err);
        Swal.fire({
            icon: 'error',
            title: 'Error',
            text: 'Failed to communicate with server.',
            confirmButtonColor: '#F25996'
        });
    });
};

window.deleteCourierServiceItem = function(id, name) {
    Swal.fire({
        title: 'Delete Courier?',
        html: `Are you sure you want to remove <b>${name}</b> from the courier dropdown options?`,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#dc2626',
        cancelButtonColor: '#f3f4f6',
        confirmButtonText: 'Yes, Delete',
        cancelButtonText: '<span style="color: #4b5563; font-weight: bold;">Cancel</span>',
        reverseButtons: true,
        customClass: { popup: 'rounded-2xl' }
    }).then(result => {
        if (result.isConfirmed) {
            fetch('<?= BASE_URL ?>/admin/orders/courier-services/delete', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ id: id, name: name })
            })
            .then(r => r.json())
            .then(res => {
                if (res.success) {
                    globalCouriersList = res.couriers || [];
                    renderCourierManagerList();
                    renderCourierDropdown();
                    Swal.fire({
                        toast: true,
                        position: 'top-end',
                        icon: 'success',
                        title: `'${name}' deleted`,
                        showConfirmButton: false,
                        timer: 2000
                    });
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: res.message || 'Failed to delete courier.',
                        confirmButtonColor: '#F25996'
                    });
                }
            })
            .catch(err => {
                console.error(err);
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'Failed to communicate with server.',
                    confirmButtonColor: '#F25996'
                });
            });
        }
    });
};

// Pre-load couriers on initial load
loadCourierServices();

// --- Tracking Details Modal Handlers ---
function openTrackingModal(orderId, orderNumber, courier = '', tracking = '', url = '') {
    currentActiveOrderId = orderId;
    currentActiveOrderNumber = orderNumber;
    
    document.getElementById('trackOrderId').value = orderId;
    document.getElementById('trackOrderNumber').value = orderNumber || '';
    
    document.getElementById('trackId').value = tracking || '';
    document.getElementById('trackUrl').value = url || '';
    
    loadCourierServices(courier).then(() => {
        const courierEl = document.getElementById('trackCourier');
        if (courierEl) courierEl.value = courier || '';
    });
    
    const modal = document.getElementById('trackingModal');
    modal.classList.remove('hidden');
}

function closeTrackingModal() {
    const modal = document.getElementById('trackingModal');
    modal.classList.add('hidden');
}

function submitTrackingDetails(e) {
    e.preventDefault();
    const orderId = document.getElementById('trackOrderId').value;
    const courier = document.getElementById('trackCourier').value;
    const trackingNum = document.getElementById('trackId').value;
    const trackingUrl = document.getElementById('trackUrl').value;

    if (!courier) {
        Swal.fire({
            icon: 'warning',
            title: 'Courier Required',
            text: 'Please select a Courier Service.'
        });
        return;
    }

    document.getElementById('trackingModal').classList.add('hidden');
    executeStatusUpdate(orderId, 'shipped', {
        courier_name: courier,
        tracking_number: trackingNum,
        tracking_url: trackingUrl
    });
}

// --- Packing Videos Modal Handlers ---
function openPackingModal(orderId, orderNumber) {
    currentActiveOrderId = orderId;
    currentActiveOrderNumber = orderNumber;

    document.getElementById('packOrderId').value = orderId;
    document.getElementById('packOrderNumberBadge').textContent = '#' + (orderNumber || orderId);
    document.getElementById('packingVideo1').value = '';
    document.getElementById('packingVideo2').value = '';
    document.getElementById('packingUploadStatus').classList.add('hidden');
    document.getElementById('packingSubmitBtn').disabled = false;
    document.getElementById('packingCancelBtn').disabled = false;

    const modal = document.getElementById('packingModal');
    modal.classList.remove('hidden');
}

function closePackingModal() {
    const modal = document.getElementById('packingModal');
    modal.classList.add('hidden');
}

function validateVideoSize(input, labelName) {
    const maxBytes = 3 * 1024 * 1024; // 3MB limit
    if (input.files && input.files[0]) {
        if (input.files[0].size > maxBytes) {
            const mb = (input.files[0].size / (1024 * 1024)).toFixed(2);
            Swal.fire({
                title: 'File Too Large (Max 3MB)',
                text: `${labelName} is ${mb}MB. Maximum allowed file size is 3MB. Please select a smaller video file.`,
                icon: 'warning',
                confirmButtonColor: '#7c3aed'
            });
            input.value = '';
            return false;
        }
    }
    return true;
}

function submitPackingDetails(e) {
    e.preventDefault();

    const maxBytes = 3 * 1024 * 1024;
    const v1Input = document.getElementById('packingVideo1');
    const v2Input = document.getElementById('packingVideo2');

    if (!v1Input.files || !v1Input.files[0]) {
        Swal.fire({
            title: 'Packing Video Required',
            text: 'Please choose Packing Video 1 to mark this custom order as Packed.',
            icon: 'warning',
            confirmButtonColor: '#7c3aed'
        });
        return false;
    }

    if (v1Input.files[0].size > maxBytes) {
        const mb = (v1Input.files[0].size / (1024 * 1024)).toFixed(2);
        Swal.fire({
            title: 'File Too Large (Max 3MB)',
            text: `Packing Video 1 is ${mb}MB. Maximum allowed size is 3MB.`,
            icon: 'warning',
            confirmButtonColor: '#7c3aed'
        });
        return false;
    }

    if (v2Input && v2Input.files && v2Input.files[0] && v2Input.files[0].size > maxBytes) {
        const mb = (v2Input.files[0].size / (1024 * 1024)).toFixed(2);
        Swal.fire({
            title: 'File Too Large (Max 3MB)',
            text: `Packing Video 2 is ${mb}MB. Maximum allowed size is 3MB.`,
            icon: 'warning',
            confirmButtonColor: '#7c3aed'
        });
        return false;
    }

    const form = document.getElementById('packingForm');
    const formData = new FormData(form);

    document.getElementById('packingUploadStatus').classList.remove('hidden');
    document.getElementById('packingSubmitBtn').disabled = true;
    document.getElementById('packingCancelBtn').disabled = true;

    fetch('<?= BASE_URL ?>/admin/customize/orders/update-packed', {
        method: 'POST',
        body: formData
    })
    .then(async response => {
        const rawText = await response.text();
        let res;
        try {
            res = JSON.parse(rawText);
        } catch (err) {
            console.error("Server raw response:", rawText);
            throw new Error(rawText ? (rawText.length > 150 ? rawText.substring(0, 150) + '...' : rawText) : 'Invalid response from server');
        }
        return res;
    })
    .then(res => {
        document.getElementById('packingUploadStatus').classList.add('hidden');
        document.getElementById('packingSubmitBtn').disabled = false;
        document.getElementById('packingCancelBtn').disabled = false;

        if (res && res.success) {
            closePackingModal();
            Swal.fire({
                title: 'Packed & Invoice Dispatched!',
                text: res.message,
                icon: 'success',
                confirmButtonColor: '#7c3aed'
            }).then(() => {
                location.reload();
            });
        } else {
            Swal.fire({
                title: 'Failed to Pack Order',
                text: res ? res.message : 'An unknown error occurred.',
                icon: 'error',
                confirmButtonColor: '#F25996'
            });
        }
    })
    .catch(err => {
        console.error(err);
        document.getElementById('packingUploadStatus').classList.add('hidden');
        document.getElementById('packingSubmitBtn').disabled = false;
        document.getElementById('packingCancelBtn').disabled = false;
        Swal.fire({
            title: 'Error',
            text: err.message || 'An error occurred while uploading packing videos.',
            icon: 'error',
            confirmButtonColor: '#F25996'
        });
    });
}

// --- Video Preview Player Handlers ---
function previewPackingVideo(url, title) {
    if (!url) return;
    const modal = document.getElementById('videoPreviewModal');
    const player = document.getElementById('videoPreviewPlayer');
    const titleEl = document.getElementById('videoPreviewTitle');

    if (titleEl) {
        titleEl.innerHTML = `<span>📹</span> <span>${title || 'Packing Video Preview'}</span>`;
    }

    player.src = url;
    modal.classList.remove('hidden');
    player.play().catch(() => {});
}

function closeVideoPreviewModal() {
    const modal = document.getElementById('videoPreviewModal');
    const player = document.getElementById('videoPreviewPlayer');
    if (player) {
        player.pause();
        player.src = '';
    }
    if (modal) {
        modal.classList.add('hidden');
    }
}

function executeStatusUpdate(orderId, newStatus, extraData = {}) {
    const payload = Object.assign({ order_id: orderId, status: newStatus }, extraData);

    fetch('<?= BASE_URL ?>/admin/customize/orders/update-status', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-Requested-With': 'XMLHttpRequest'
        },
        body: JSON.stringify(payload)
    })
    .then(async res => {
        const data = await res.json().catch(() => ({ success: false, message: 'Server returned invalid response.' }));
        if (res.ok && data.success) {
            Swal.fire({
                icon: 'success',
                title: 'Updated!',
                text: data.message,
                timer: 1500,
                showConfirmButton: false
            }).then(() => {
                location.reload();
            });
        } else {
            if (data.require_video) {
                openPackingModal(orderId, currentActiveOrderNumber);
                return;
            }
            Swal.fire({
                icon: 'error',
                title: 'Update Failed',
                text: data.message || 'Could not update status.'
            });
        }
    })
    .catch(err => {
        console.error(err);
        Swal.fire({
            icon: 'error',
            title: 'Error',
            text: err.message || 'An unexpected network error occurred.'
        });
    });
}

// Custom Orders Packing Slip Selection & Bulk Print Handlers
function toggleSelectAllCustomOrders(master) {
    const cbs = document.querySelectorAll('.custom-order-row-cb');
    cbs.forEach(cb => cb.checked = master.checked);
    updateSelectedCustomOrdersCount();
}

function updateSelectedCustomOrdersCount() {
    const selected = document.querySelectorAll('.custom-order-row-cb:checked');
    const badge = document.getElementById('selectedCustomOrdersCountBadge');
    if (!badge) return;
    if (selected.length > 0) {
        badge.textContent = `${selected.length} selected`;
        badge.classList.remove('hidden');
    } else {
        badge.classList.add('hidden');
    }
}

function openBulkCustomPackingSlips(autoPrint = false) {
    const selected = Array.from(document.querySelectorAll('.custom-order-row-cb:checked')).map(cb => cb.value);
    let url = '<?= BASE_URL ?>/admin/orders/packing-slips/bulk';
    if (selected.length > 0) {
        url += '?order_ids=' + encodeURIComponent(selected.join(','));
    } else {
        const allOnPage = Array.from(document.querySelectorAll('.custom-order-row-cb')).map(cb => cb.value);
        if (allOnPage.length === 0) {
            alert('No custom orders available on this page to print packing slips.');
            return;
        }
        url += '?order_ids=' + encodeURIComponent(allOnPage.join(','));
    }
    if (autoPrint) {
        url += '&auto_print=1';
    }
    window.open(url, '_blank');
}
</script>
