<?php
/**
 * @var string $title
 * @var array $orders
 * @var bool $isPendingView
 */
?>

<div class="w-full">
    <!-- Header Section -->
    <div class="mb-8 relative z-10">
        <!-- Breadcrumbs -->
        <div class="text-sm font-medium text-gray-400 mb-2 font-serif tracking-wide">
            Dashboard <span class="mx-1 text-gray-300">›</span> Order Management <span class="mx-1 text-gray-300">›</span> <span class="text-brand-700">Orders</span>
        </div>
        
        <div class="flex justify-between items-center mb-8 w-full">
            <h2 class="text-5xl font-display font-extrabold text-gray-900 tracking-tight">Orders Management</h2>
            
            <div class="flex items-center space-x-3 ml-auto">
                <button onclick="location.reload()" class="bg-white border border-slate-200 hover:bg-slate-50 text-slate-700 text-xs font-bold uppercase tracking-wider px-5 py-2.5 rounded-full flex items-center gap-2 shadow-xs transition active:scale-95 group">
                    <svg class="w-3.5 h-3.5 text-slate-500 group-hover:rotate-180 transition-transform duration-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path></svg>
                    <span>REFRESH</span>
                </button>
                <form action="<?= BASE_URL ?>/admin/orders/export" method="GET" class="bg-white border border-slate-200 rounded-full flex items-center p-1 pl-4 shadow-xs gap-3">
                    <div class="flex items-center gap-2 text-xs font-semibold text-slate-700">
                        <input type="date" name="start_date" value="<?= date('Y-m-01') ?>" required title="Start Date" class="border-none bg-transparent text-xs text-gray-700 focus:ring-0 p-0 cursor-pointer font-mono font-medium outline-none">
                        <span class="text-slate-300 font-normal">-</span>
                        <input type="date" name="end_date" value="<?= date('Y-m-t') ?>" required title="End Date" class="border-none bg-transparent text-xs text-gray-700 focus:ring-0 p-0 cursor-pointer font-mono font-medium outline-none">
                    </div>
                    <button type="submit" class="bg-[#F25996] hover:bg-[#d94480] text-white text-xs font-bold uppercase tracking-wider px-5 py-2 rounded-full flex items-center gap-1.5 shadow-xs transition active:scale-95 cursor-pointer">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path></svg>
                        <span>EXPORT</span>
                    </button>
                </form>
            </div>
        </div>
    </div>
    
<?php if (!$isPendingView): ?>
<!-- Charts Section (Diagrams) -->
<div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
    <!-- Top Selling Products Chart -->
    <div class="bg-white p-6 rounded-2xl shadow-sm border border-gray-100">
        <h3 class="text-xl font-bold text-gray-900">Top Selling Products</h3>
        <p class="text-sm text-gray-500 mb-6">Based on successful orders</p>
        <div class="relative h-64 w-full">
            <canvas id="topProductsChart"></canvas>
        </div>
    </div>

    <!-- Orders by Status Chart -->
    <div class="bg-white p-6 rounded-2xl shadow-sm border border-gray-100">
        <div class="flex justify-between items-start mb-6">
            <div>
                <h3 class="text-xl font-bold text-gray-900">Orders by Status</h3>
                <p class="text-sm text-gray-500">Distribution of order statuses</p>
            </div>
        </div>
        <div class="relative h-64 w-full flex justify-center">
            <canvas id="statusChart"></canvas>
        </div>
    </div>
</div>
<?php endif; ?>

<!-- Single Row: Search Bar (Left) & Packing Slip Buttons (Right) Below Diagram -->
<div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-6">
    <!-- Search Bar (Matching Image 1) -->
    <div class="flex-1 min-w-[260px] h-11 bg-white rounded-full shadow-xs border border-gray-200 px-4 flex items-center transition focus-within:border-pink-300 focus-within:ring-2 focus-within:ring-pink-100">
        <svg class="w-4 h-4 text-gray-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
        </svg>
        <input type="text" id="searchInput" oninput="filterOrders()" placeholder="Search by Order ID, Name..." class="w-full pl-3 pr-2 py-2 bg-transparent border-none focus:ring-0 text-gray-700 placeholder-gray-400 outline-none font-medium text-sm">
    </div>

    <!-- Packing Slip Action Buttons (Matching Image 2) -->
    <div class="flex items-center space-x-2.5 shrink-0 self-end md:self-center">
        <span id="selectedOrdersCountBadge" class="hidden text-xs bg-pink-100 text-[#F25996] font-bold px-3 py-1.5 rounded-full shadow-2xs">0 selected</span>
        
        <!-- Packing Slip Pill Button (White/Light Pink with Pink Border & Text) -->
        <button type="button" onclick="openBulkPackingSlips(false)" class="h-11 px-5 rounded-full font-bold text-sm bg-white hover:bg-pink-50 text-[#F25996] border border-[#ffadd2] transition-all flex items-center gap-2 cursor-pointer shadow-xs active:scale-95" title="Preview / Open 100mm x 100mm Packing Slips">
            <svg class="w-4 h-4 text-[#F25996]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
            </svg>
            <span>Packing Slip</span>
        </button>

        <!-- Print Pill Button (Hot Pink Solid with White Text) -->
        <button type="button" onclick="openBulkPackingSlips(true)" class="h-11 px-5 rounded-full font-bold text-sm bg-[#F25996] hover:bg-[#d94480] text-white shadow-xs transition-all flex items-center gap-2 cursor-pointer active:scale-95" title="Direct Instant Print 100mm Slips">
            <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path>
            </svg>
            <span>Print</span>
        </button>

        <!-- Configuration Settings Circular Button (White with Border and Cog Icon) -->
        <a href="<?= BASE_URL ?>/admin/orders/packing-slip-config" class="w-11 h-11 rounded-full bg-white border border-gray-200 hover:bg-gray-50 text-gray-600 hover:text-gray-900 flex items-center justify-center transition-colors shadow-xs shrink-0" title="Packing Slip Configuration & Section Ordering">
            <svg class="w-5 h-5 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path>
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
            </svg>
        </a>
    </div>
</div>

<!-- Status Filter Tabs -->
<?php
    $curStatus = $currentStatus ?? ($_GET['status'] ?? 'all');
    $tabs = [
        'all' => ['label' => 'All Orders', 'icon' => '🌐', 'count' => $statusCounts['all'] ?? count($orders)],
        'placed' => ['label' => 'Placed / New', 'icon' => '⏳', 'count' => $statusCounts['placed'] ?? 0],
        'packed' => ['label' => 'Packed', 'icon' => '📦', 'count' => $statusCounts['packed'] ?? 0],
        'shipped' => ['label' => 'Shipped', 'icon' => '🚚', 'count' => $statusCounts['shipped'] ?? 0],
        'delivered' => ['label' => 'Delivered', 'icon' => '✅', 'count' => $statusCounts['delivered'] ?? 0],
        'pending' => ['label' => 'Pending Payment', 'icon' => '💳', 'count' => $statusCounts['pending'] ?? 0],
        'cancelled' => ['label' => 'Cancelled', 'icon' => '❌', 'count' => $statusCounts['cancelled'] ?? 0],
    ];
?>
<div class="flex items-center gap-2 overflow-x-auto scrollbar-thin pb-1 mb-6">
    <?php foreach ($tabs as $key => $tab): 
        $isActive = ($curStatus === $key);
    ?>
        <a href="<?= BASE_URL ?>/admin/orders?status=<?= $key ?>" 
           class="inline-flex items-center gap-2 px-5 py-2.5 rounded-full text-xs font-bold transition-all whitespace-nowrap border <?= $isActive ? 'bg-[#F25996] text-white border-[#F25996] shadow-sm' : 'bg-white text-gray-600 hover:bg-gray-50 border-gray-200 shadow-xs' ?>">
            <span><?= $tab['icon'] ?></span>
            <span><?= $tab['label'] ?></span>
            <span class="px-2 py-0.5 rounded-full text-[10.5px] font-black <?= $isActive ? 'bg-white/20 text-white' : 'bg-gray-100 text-gray-700' ?>">
                <?= (int)$tab['count'] ?>
            </span>
        </a>
    <?php endforeach; ?>
</div>

<style>
/* Remove inside right-side vertical scrollbar on Orders Table */
.orders-table-container {
    overflow-x: auto !important;
    overflow-y: hidden !important;
    scrollbar-width: thin;
}
.orders-table-container::-webkit-scrollbar:vertical {
    display: none !important;
    width: 0px !important;
    height: 0px !important;
    background: transparent !important;
}
</style>

<!-- Table Container with Clean List Style (No inside vertical scrollbar) -->
<div class="bg-white rounded-2xl shadow-sm border border-gray-200/80 mb-6 overflow-hidden">
    <div class="orders-table-container overflow-x-auto relative rounded-2xl" style="overflow-y: hidden !important;">
        <table class="w-full text-left border-collapse min-w-[1100px]">
            <thead class="sticky top-0 z-20 bg-gray-50 shadow-2xs">
                <tr class="bg-gray-50 text-gray-500 text-xs uppercase tracking-wider font-semibold">
                    <th class="w-10 px-3 py-4 border-b border-gray-200 text-center">
                        <input type="checkbox" id="selectAllOrdersCb" onchange="toggleSelectAllOrders(this)" class="rounded text-[#F25996] focus:ring-pink-300 w-4 h-4 cursor-pointer" title="Select All Orders">
                    </th>
                    <th class="px-5 py-4 border-b border-gray-200">Order ID</th>
                    <th class="px-5 py-4 border-b border-gray-200">Customer</th>
                    <th class="px-5 py-4 border-b border-gray-200">Date</th>
                    <th class="px-5 py-4 border-b border-gray-200"><?= !empty($isVendor) ? 'Your Subtotal' : 'Total' ?></th>
                    <th class="px-5 py-4 border-b border-gray-200">Payment</th>
                    <th class="px-5 py-4 border-b border-gray-200">Status</th>
                    <th class="px-5 py-4 border-b border-gray-200 text-right min-w-[140px]">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                <?php if(empty($orders)): ?>
                    <tr>
                        <td colspan="8" class="px-6 py-8 text-center text-gray-500">
                            No orders found.
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach($orders as $order): 
                        // Customer Initial
                        $initial = strtoupper(substr($order['user_name'], 0, 1));
                        
                        // Pick pink palette colors to match brand theme
                        $colors = [
                            'bg-[#fdf2f7] text-[#F25996] border border-[#fbaed2]',
                            'bg-pink-100 text-[#e04481] border border-pink-200',
                            'bg-rose-100 text-[#F25996] border border-rose-200',
                            'bg-pink-50 text-[#c72e6b] border border-pink-200',
                            'bg-fuchsia-50 text-[#F25996] border border-fuchsia-200'
                        ];
                        $colorIndex = ord($initial) % count($colors);
                        $avatarClass = $colors[$colorIndex];
                    ?>
                    <tr class="hover:bg-gray-50 transition-colors">
                        <td class="px-3 py-4 text-center">
                            <input type="checkbox" class="order-row-cb rounded text-[#F25996] focus:ring-pink-300 w-4 h-4 cursor-pointer" value="<?= $order['id'] ?>" onchange="updateSelectedOrdersCount()">
                        </td>
                        <td class="px-6 py-4">
                            <span class="font-bold text-gray-900 block"><?= htmlspecialchars($order['order_number']) ?></span>
                            <?php if (!empty($order['has_modification'])): ?>
                                <span class="inline-flex items-center gap-1 mt-1 px-1.5 py-0.5 rounded text-[10px] font-bold bg-amber-50 text-amber-700 border border-amber-200" title="This order has been modified by an admin.">
                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/></svg>
                                    Modified
                                </span>
                                <form method="POST" action="<?= BASE_URL ?>/admin/orders/delete-modification" class="inline" onsubmit="return confirm('Clear the Modified badge for <?= htmlspecialchars($order['order_number']) ?>? This only removes the badge — it does not undo any actual order changes.')">
                                    <input type="hidden" name="order_number" value="<?= htmlspecialchars($order['order_number']) ?>">
                                    <button type="submit" class="inline-flex items-center gap-0.5 mt-1 px-1.5 py-0.5 rounded text-[10px] font-bold bg-gray-100 text-gray-500 border border-gray-200 hover:bg-red-50 hover:text-red-600 hover:border-red-200 transition-colors" title="Clear this Modified badge">
                                        <svg class="w-2.5 h-2.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"/></svg>
                                        Clear
                                    </button>
                                </form>
                            <?php endif; ?>
                        </td>
                        <td class="px-6 py-4">
                            <div class="flex items-center space-x-3">
                                <div class="relative">
                                    <div class="w-10 h-10 rounded-full flex items-center justify-center font-bold <?= $avatarClass ?>">
                                        <?= $initial ?>
                                    </div>
                                    <?php if($order['repeat_count'] > 0): ?>
                                    <div class="absolute -top-1 -right-1 bg-yellow-400 text-yellow-900 text-[10px] font-bold px-1.5 py-0.5 rounded-full border border-white">
                                        <?= $order['repeat_count'] ?>
                                    </div>
                                    <?php endif; ?>
                                </div>
                                <div>
                                    <div class="font-semibold text-gray-800 flex items-center space-x-2">
                                        <span><?= htmlspecialchars($order['user_name']) ?></span>
                                        <?php if($order['repeat_count'] > 0): ?>
                                            <span class="bg-yellow-100 text-yellow-700 text-[10px] font-bold px-1.5 py-0.5 rounded shadow-sm leading-none">Repeat</span>
                                        <?php endif; ?>
                                    </div>
                                    <div class="text-xs text-gray-400"><?= htmlspecialchars($order['user_email']) ?></div>
                                </div>
                            </div>
                        </td>
                        <td class="px-6 py-4 text-sm text-gray-600 font-medium">
                            <?= date('n/j/Y', strtotime($order['created_at'])) ?>
                        </td>
                        <td class="px-6 py-4">
                            <?php 
                            if (!empty($isVendor) && isset($order['vendor_subtotal'])) {
                                $displayTotal = (float)$order['vendor_subtotal'];
                            } else {
                                $displayTotal = ($order['grand_total'] ?? 0) > 0 ? $order['grand_total'] : $order['total_amount'];
                            }
                            ?>
                            <span class="font-bold text-gray-900">₹<?= number_format($displayTotal, 2) ?></span>
                            <?php if (!empty($isVendor) && isset($order['vendor_item_qty'])): ?>
                                <span class="block text-[11px] text-gray-400"><?= (int)$order['vendor_item_qty'] ?> pcs (Your items)</span>
                            <?php elseif (!empty($order['vendor_names'])): ?>
                                <span class="inline-flex items-center gap-1 text-[10px] font-semibold bg-indigo-50 text-indigo-700 px-1.5 py-0.2 rounded border border-indigo-100 mt-0.5 block truncate max-w-[150px]" title="Vendor: <?= htmlspecialchars($order['vendor_names']) ?>">
                                    <svg class="w-2.5 h-2.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
                                    <?= htmlspecialchars($order['vendor_names']) ?>
                                </span>
                            <?php endif; ?>
                        </td>
                        <td class="px-6 py-4">
                            <div class="flex items-center space-x-2">
                                <!-- <span class="font-semibold text-gray-800">Cashfree</span> -->
                                <span class="font-semibold text-gray-800">Razorpay</span>
                                <?php if($order['payment_status'] === 'paid'): ?>
                                    <span class="bg-green-100 text-green-700 text-[10px] font-bold px-1.5 py-0.5 rounded uppercase">Paid</span>
                                <?php else: ?>
                                    <span class="bg-orange-100 text-orange-700 text-[10px] font-bold px-1.5 py-0.5 rounded uppercase">Pending</span>
                                <?php endif; ?>
                            </div>
                        </td>
                        <td class="px-6 py-4">
                            <?php if ($isPendingView): ?>
                                <span class="bg-orange-50 text-orange-700 text-xs font-semibold px-3 py-1.5 rounded-lg border border-orange-200">
                                    Pending
                                </span>
                            <?php else: ?>
                                <?php
                                    $refundStatuses = ['refund_pending', 'refund_processing', 'refund_completed', 'refund_failed'];
                                    $isRefundStatus = in_array($order['status'], $refundStatuses);
                                    
                                    $nextTransitions = [
                                        'placed'           => ['packed' => 'Packed', 'cancelled' => 'Cancel Order'],
                                        'packed'           => ['shipped' => 'Shipped', 'cancelled' => 'Cancel Order'],
                                        'shipped'          => ['out_for_delivery' => 'Out for Delivery'],
                                        'out_for_delivery' => ['delivered' => 'Delivered']
                                    ];
                                    $currentStatus = $order['status'];
                                    $allowedNext = $nextTransitions[$currentStatus] ?? [];

                                    $statusPillStyles = [
                                        'placed'           => 'bg-amber-50 hover:bg-amber-100/80 text-amber-900 border-amber-300',
                                        'packed'           => 'bg-purple-50 hover:bg-purple-100/80 text-purple-900 border-purple-300',
                                        'shipped'          => 'bg-blue-50 hover:bg-blue-100/80 text-blue-900 border-blue-300',
                                        'out_for_delivery' => 'bg-indigo-50 hover:bg-indigo-100/80 text-indigo-900 border-indigo-300',
                                        'delivered'        => 'bg-emerald-50 text-emerald-900 border-emerald-300',
                                        'cancelled'        => 'bg-rose-50 text-rose-900 border-rose-200'
                                    ];
                                    $btnStyle = $statusPillStyles[$currentStatus] ?? 'bg-gray-100 text-gray-800 border-gray-200';
                                    $label = ucwords(str_replace('_', ' ', $currentStatus));
                                ?>
                                <?php if ($this->hasPermission('all_orders', 'edit') && !empty($allowedNext) && !$isRefundStatus): ?>
                                <div class="flex flex-col items-start space-y-1.5">
                                    <button type="button" 
                                            onclick="openOrderStatusMenu(event, '<?= $order['order_number'] ?>', '<?= $currentStatus ?>')" 
                                            class="inline-flex items-center gap-2 px-3.5 py-1.5 rounded-full text-xs font-bold border shadow-2xs cursor-pointer <?= $btnStyle ?>"
                                            id="status-btn-<?= $order['order_number'] ?>">
                                        <span><?= $label ?></span>
                                        <svg class="w-3.5 h-3.5 opacity-70" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 9l-7 7-7-7"/></svg>
                                    </button>
                                    <?php if (!empty($order['has_modification'])): ?>
                                        <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-md text-[10px] font-bold bg-amber-50 text-amber-700 border border-amber-200">
                                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                            Bill Modified
                                        </span>
                                    <?php endif; ?>
                                    <?php if (!empty($order['courier_name'])): ?>
                                        <span class="bg-gray-100 text-gray-600 text-[10px] font-bold px-2 py-1 rounded uppercase tracking-wider">
                                            <?= htmlspecialchars($order['courier_name']) ?>
                                        </span>
                                    <?php endif; ?>
                                </div>
                                <?php else: ?>
                                <div class="flex flex-col gap-1">
                                    <div class="flex items-center gap-2">
                                        <span class="inline-flex items-center gap-1.5 px-3.5 py-1.5 rounded-full text-xs font-bold border shadow-2xs <?= $btnStyle ?>">
                                            <?php if ($order['status'] === 'delivered'): ?>
                                                <svg class="w-3.5 h-3.5 text-emerald-600" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg>
                                            <?php elseif ($order['status'] === 'cancelled'): ?>
                                                <svg class="w-3.5 h-3.5 text-rose-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"/></svg>
                                            <?php endif; ?>
                                            <?= $label ?>
                                        </span>
                                    </div>
                                    <?php if ($order['status'] === 'cancelled' && !empty($order['cancelled_by_role'])): ?>
                                        <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded text-[10px] font-bold <?= $order['cancelled_by_role'] === 'buyer' ? 'bg-orange-50 text-orange-700 border border-orange-200' : 'bg-red-50 text-red-700 border border-red-200' ?>">
                                            <?= $order['cancelled_by_role'] === 'buyer' ? '🙍 Buyer Cancelled' : '🛡 Admin Cancelled' ?>
                                        </span>
                                    <?php endif; ?>
                                </div>
                                <?php endif; ?>
                            <?php endif; ?>
                        </td>
                        <td class="px-6 py-4 text-right">
                            <div class="flex items-center justify-end space-x-2">
                                <a href="<?= BASE_URL ?>/admin/orders/invoice?order=<?= $order['order_number'] ?>&t=<?= time() ?>" target="_blank" class="inline-flex items-center justify-center p-1 text-[#F25996] hover:text-[#e04481] transition-colors" title="Download Invoice PDF">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                                </a>
                                <?php if ($this->hasPermission('all_orders', 'edit') && in_array($order['status'], ['placed', 'packed'])): ?>
                                <button type="button" onclick="openPackingModal('<?= $order['order_number'] ?>', document.getElementById('status-btn-<?= $order['order_number'] ?>'))" class="inline-flex items-center justify-center p-1 text-purple-600 hover:text-purple-800 transition-colors" title="<?= $order['status'] === 'packed' ? 'Update Packing Videos & Email Invoice' : 'Pack Order & Upload Videos' ?>">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z"/></svg>
                                </button>
                                <?php endif; ?>
                                <?php if ($this->hasPermission('all_orders', 'edit') && empty($isVendor)): ?>
                                <a href="<?= BASE_URL ?>/admin/order-modifications/create?order=<?= $order['order_number'] ?>" class="inline-flex items-center justify-center p-1 text-blue-600 hover:text-blue-800 transition-colors" title="Edit / Modify Order">
                                    <i class="bi bi-pencil-square text-lg"></i>
                                </a>
                                <?php endif; ?>
                                <a href="<?= BASE_URL ?>/admin/orders/view?id=<?= $order['order_number'] ?>" class="inline-flex items-center justify-center p-1 text-emerald-600 hover:text-emerald-800 transition-colors" title="View Details">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path></svg>
                                </a>
                                <button type="button" onclick="openTrackingModal('<?= $order['order_number'] ?>', this, '<?= htmlspecialchars(addslashes($order['courier_name'] ?? '')) ?>', '<?= htmlspecialchars(addslashes($order['tracking_number'] ?? '')) ?>', '<?= htmlspecialchars(addslashes($order['tracking_url'] ?? '')) ?>')" class="inline-flex items-center justify-center p-1 text-purple-600 hover:text-purple-800 transition-colors" title="Enter / Edit Tracking Details">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16V6a1 1 0 00-1-1H4a1 1 0 00-1 1v10a1 1 0 001 1h1m8-1a1 1 0 01-1 1H9m4-1V8a1 1 0 011-1h2.586a1 1 0 01.707.293l3.414 3.414a1 1 0 01.293.707V16a1 1 0 01-1 1h-1m-6-1a1 1 0 001 1h1M5 17a2 2 0 104 0m-4 0a2 2 0 114 0m6 0a2 2 0 104 0m-4 0a2 2 0 114 0"/></svg>
                                </button>
                                <a href="<?= BASE_URL ?>/admin/orders/packing-slip?order_id=<?= $order['id'] ?>" target="_blank" class="inline-flex items-center justify-center p-1 text-pink-600 hover:text-pink-800 transition-colors" title="Packing Slip (100mm × 100mm)">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                                </a>
                                <a href="<?= BASE_URL ?>/admin/orders/packing-slip?order_id=<?= $order['id'] ?>&auto_print=1" target="_blank" class="inline-flex items-center justify-center p-1 text-gray-700 hover:text-black transition-colors" title="Print Slip">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path></svg>
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

<!-- Packing Videos Modal -->
<div id="packingModal" class="fixed inset-0 z-50 hidden flex items-center justify-center p-4">
    <!-- Backdrop -->
    <div class="fixed inset-0 bg-gray-900 bg-opacity-50 transition-opacity backdrop-blur-sm" onclick="closePackingModal()"></div>
    
    <!-- Modal Content -->
    <div class="relative bg-white rounded-2xl shadow-xl w-full max-w-lg p-6 transform transition-all max-h-[92vh] overflow-y-auto">
        <div class="flex items-center justify-between mb-4">
            <div class="flex items-center space-x-3">
                <div class="w-10 h-10 rounded-full bg-purple-100 text-purple-600 flex items-center justify-center font-bold">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
                </div>
                <div>
                    <h3 class="text-lg font-bold text-gray-900">Upload Order Packing Videos</h3>
                    <p class="text-xs text-gray-500">Order: <span id="packOrderNumberBadge" class="font-mono font-bold text-purple-700"></span></p>
                </div>
            </div>
            <button type="button" onclick="closePackingModal()" class="text-gray-400 hover:text-gray-600">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
        </div>

        <div class="bg-purple-50 border border-purple-200 rounded-xl p-3.5 mb-4 text-xs text-purple-900 flex items-start gap-2.5">
            <svg class="w-4 h-4 text-purple-600 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            <div>
                <strong>Automated Invoice & Video Workflow:</strong>
                <p class="text-[11.5px] text-purple-800 mt-0.5">Uploading packing videos marks this order as <strong>Packed</strong>, attaches the generated <strong>Tax Invoice PDF</strong>, and emails it directly to the customer. Packing videos remain available to the customer for <strong>30 days after delivery</strong>.</p>
            </div>
        </div>
        
        <form id="packingForm" onsubmit="submitPackingDetails(event)" enctype="multipart/form-data">
            <input type="hidden" id="packOrderId" name="order_id">
            
            <!-- Video 1 -->
            <div class="mb-4">
                <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1">
                    Packing Video 1 <span class="normal-case font-normal text-gray-500">(Item Verification &amp; Box Packing - Max 3MB)</span>
                </label>
                <div class="border-2 border-dashed border-gray-300 rounded-xl p-3 hover:border-purple-500 transition-colors bg-gray-50">
                    <input type="file" id="packingVideo1" name="packing_video_1" accept="video/*,.mp4,.webm,.mov" onchange="validateVideoSize(this, 'Packing Video 1')" class="w-full text-xs text-gray-600 file:mr-3 file:py-1.5 file:px-3 file:rounded-lg file:border-0 file:text-xs file:font-semibold file:bg-purple-100 file:text-purple-700 hover:file:bg-purple-200 cursor-pointer">
                    <p class="text-[11px] text-gray-400 mt-1">MP4, WebM, MOV (Max 3MB limit)</p>
                </div>
            </div>
            
            <!-- Video 2 -->
            <div class="mb-5">
                <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1">
                    Packing Video 2 <span class="normal-case font-normal text-gray-500">(Box Sealing &amp; Label Verification - Max 3MB)</span>
                </label>
                <div class="border-2 border-dashed border-gray-300 rounded-xl p-3 hover:border-purple-500 transition-colors bg-gray-50">
                    <input type="file" id="packingVideo2" name="packing_video_2" accept="video/*,.mp4,.webm,.mov" onchange="validateVideoSize(this, 'Packing Video 2')" class="w-full text-xs text-gray-600 file:mr-3 file:py-1.5 file:px-3 file:rounded-lg file:border-0 file:text-xs file:font-semibold file:bg-purple-100 file:text-purple-700 hover:file:bg-purple-200 cursor-pointer">
                    <p class="text-[11px] text-gray-400 mt-1">MP4, WebM, MOV (Max 3MB limit)</p>
                </div>
            </div>
            
            <div id="packingUploadStatus" class="hidden mb-4 p-3 bg-purple-100 border border-purple-300 rounded-xl text-center text-xs font-semibold text-purple-900">
                <div class="inline-block animate-spin rounded-full h-4 w-4 border-2 border-purple-700 border-t-transparent mr-2 align-middle"></div>
                Uploading packing videos &amp; emailing dynamic Tax Invoice PDF...
            </div>

            <div class="flex space-x-4" style="display: flex; gap: 1rem; width: 100%;">
                <button type="button" onclick="closePackingModal()" id="packingCancelBtn" class="font-bold transition-colors" style="flex: 1; padding: 0.625rem 0; background-color: #f3f4f6; color: #111827; border-radius: 9999px; text-align: center; border: 1px solid #e5e7eb;">Cancel</button>
                <button type="submit" id="packingSubmitBtn" class="font-bold transition-colors shadow-sm" style="flex: 1; padding: 0.625rem 0; background-color: #F25996; color: #ffffff; border-radius: 9999px; text-align: center; border: 1px solid #F25996;">Confirm &amp; Send Invoice</button>
            </div>
        </form>
    </div>
</div>

<!-- Tracking Details Modal -->
<div id="trackingModal" class="fixed inset-0 items-center justify-center p-4 transition-all" style="display: none; z-index: 99990;">
    <!-- Backdrop -->
    <div class="fixed inset-0 bg-gray-900/60 backdrop-blur-sm transition-opacity" onclick="closeTrackingModal()" style="z-index: 99989;"></div>
    
    <!-- Modal Content -->
    <div class="relative bg-white rounded-3xl shadow-2xl w-full max-w-md p-6 transform transition-all border border-gray-100" style="z-index: 99990;">
        <div class="flex items-center space-x-2.5 mb-6">
            <div class="w-9 h-9 rounded-xl bg-blue-50 flex items-center justify-center text-lg">🚚</div>
            <div>
                <h3 class="text-xl font-bold text-gray-900">Enter Tracking Details</h3>
                <p class="text-xs text-gray-500 font-medium">Add courier and tracking information</p>
            </div>
        </div>
        
        <form id="trackingForm" onsubmit="submitTrackingDetails(event)">
            <input type="hidden" id="trackOrderId">
            <input type="hidden" id="trackUpdateStatus" value="0">
            
            <div class="mb-4">
                <div class="flex justify-between items-end mb-1.5">
                    <label class="block text-sm font-bold text-gray-700">Courier Service</label>
                    <button type="button" onclick="openManageCouriersModal()" class="text-xs font-bold text-[#F25996] hover:underline focus:outline-none cursor-pointer flex items-center gap-1">
                        <span>+ Add / Manage</span>
                    </button>
                </div>
                <select id="trackCourier" required class="w-full px-3.5 py-2.5 border border-gray-200 rounded-xl text-sm text-gray-900 font-medium focus:outline-none focus:ring-2 focus:ring-[#F25996] bg-white shadow-2xs">
                    <option value="" disabled selected>Select Courier Service</option>
                </select>
            </div>
            
            <div class="mb-4">
                <label class="block text-sm font-bold text-gray-700 mb-1">Tracking ID</label>
                <input type="text" id="trackId" placeholder="e.g. TRK123456789" class="w-full px-3.5 py-2.5 border border-gray-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-[#F25996] text-gray-900 font-medium shadow-2xs">
            </div>
            
            <div class="mb-6">
                <label class="block text-sm font-bold text-gray-700 mb-1">Tracking URL</label>
                <input type="url" id="trackUrl" placeholder="https://tracker.com/TRK123" class="w-full px-3.5 py-2.5 border border-gray-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-[#F25996] text-gray-900 font-medium shadow-2xs">
            </div>
            
            <div class="flex space-x-3" style="display: flex; gap: 0.75rem; width: 100%;">
                <button type="button" onclick="closeTrackingModal()" class="font-bold transition-all cursor-pointer hover:bg-gray-200" style="flex: 1; padding: 0.65rem 0; background-color: #f3f4f6; color: #111827; border-radius: 9999px; text-align: center; border: 1px solid #e5e7eb;">Cancel</button>
                <button type="submit" class="font-bold transition-all shadow-sm cursor-pointer hover:opacity-95" style="flex: 1; padding: 0.65rem 0; background-color: #F25996; color: #ffffff; border-radius: 9999px; text-align: center; border: 1px solid #F25996;">Save</button>
            </div>
        </form>
    </div>
</div>

<!-- Manage Couriers Modal -->
<div id="manageCouriersModal" class="fixed inset-0 items-center justify-center p-4 transition-all" style="display: none; z-index: 99999;">
    <!-- Backdrop -->
    <div class="fixed inset-0 bg-gray-900/60 backdrop-blur-sm transition-opacity" onclick="closeManageCouriersModal()" style="z-index: 99998;"></div>
    
    <!-- Modal Content -->
    <div class="relative bg-white rounded-3xl shadow-2xl w-full max-w-md p-6 transform transition-all border border-gray-100" style="z-index: 99999;">
        <div class="flex items-center justify-between pb-3.5 border-b border-gray-100 mb-4">
            <div class="flex items-center gap-2.5">
                <div class="w-9 h-9 rounded-xl bg-pink-50 flex items-center justify-center text-lg">🚚</div>
                <div>
                    <h3 class="text-base font-bold text-gray-900 leading-tight">Manage Courier Services</h3>
                    <p class="text-[11px] text-gray-500 font-medium">Add new or delete dropdown couriers</p>
                </div>
            </div>
            <button type="button" onclick="closeManageCouriersModal()" class="text-gray-400 hover:text-gray-600 p-2 rounded-xl hover:bg-gray-100 transition-colors cursor-pointer">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
        </div>

        <!-- Add New Courier Form -->
        <div class="mb-5 bg-gradient-to-br from-pink-50/50 to-purple-50/30 p-4 rounded-2xl border border-pink-100/70">
            <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-2">Add New Courier</label>
            <div class="flex gap-2">
                <input type="text" id="newCourierName" placeholder="e.g. ST Courier, Delhivery, DTDC..." 
                       onkeydown="if(event.key==='Enter'){event.preventDefault();addNewCourierService();}"
                       class="flex-1 px-3.5 py-2.5 bg-white border border-gray-200 rounded-xl text-sm font-medium focus:outline-none focus:ring-2 focus:ring-[#F25996] shadow-2xs">
                <button type="button" onclick="addNewCourierService()" 
                        class="px-4 py-2.5 bg-[#F25996] hover:bg-[#e04481] active:scale-95 text-white text-xs font-bold rounded-xl shadow-xs transition-all shrink-0 cursor-pointer flex items-center gap-1">
                    <span>+ Add</span>
                </button>
            </div>
        </div>

        <!-- List of Couriers -->
        <div class="flex items-center justify-between mb-2.5 px-0.5">
            <label class="text-xs font-bold text-gray-600 uppercase tracking-wider">Available Dropdown Couriers</label>
            <span id="couriersCountBadge" class="text-[11px] font-bold text-pink-600 bg-pink-50 px-2.5 py-0.5 rounded-full border border-pink-100">0 couriers</span>
        </div>
        
        <div id="couriersListContainer" class="max-h-60 overflow-y-auto space-y-1.5 pr-1 divide-y divide-gray-100">
            <!-- Dynamically populated rows with delete buttons -->
        </div>

        <div class="mt-5 pt-3 border-t border-gray-100 flex items-center justify-between">
            <span class="text-[11px] text-gray-400 italic">Deleted couriers are removed from dropdown</span>
            <button type="button" onclick="closeManageCouriersModal()" class="px-5 py-2 bg-gray-900 hover:bg-gray-800 text-white text-xs font-bold rounded-xl transition-all shadow-xs cursor-pointer">
                Done
            </button>
        </div>
    </div>
</div>

<style>
.swal2-container {
    z-index: 100001 !important;
}
</style>

<script>
// Standalone Global Courier & Tracking Services Script
let currentTrackingSelect = null;
let currentTrackingOrderId = null;
let globalCouriersList = [];

function escapeHtmlLocal(str) {
    if (!str) return '';
    return String(str).replace(/[&<>"']/g, function(m) {
        return { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#039;' }[m];
    });
}

function loadCourierServices(selectedCourier = '') {
    return fetch('<?= BASE_URL ?>/admin/orders/courier-services', {
        headers: {
            'Accept': 'application/json',
            'X-Requested-With': 'XMLHttpRequest'
        }
    })
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

    if (currentVal && !foundCurrent && selectedCourier) {
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

    if (badge) badge.textContent = `${globalCouriersList.length} couriers`;

    if (globalCouriersList.length === 0) {
        container.innerHTML = '<p class="text-xs text-gray-400 py-3 text-center italic">No courier services configured yet.</p>';
        return;
    }

    container.innerHTML = globalCouriersList.map(c => `
        <div class="flex items-center justify-between py-2 px-2.5 hover:bg-gray-50 rounded-xl transition-colors group">
            <div class="flex items-center gap-2.5 min-w-0">
                <span class="w-7 h-7 rounded-lg bg-gray-100 flex items-center justify-center text-sm shrink-0">🚚</span>
                <span class="text-sm font-bold text-gray-800 truncate">${escapeHtmlLocal(c.name)}</span>
            </div>
            <button type="button" onclick="deleteCourierServiceItem(${c.id}, '${escapeHtmlLocal(c.name).replace(/'/g, "\\'")}')" 
                    class="px-2.5 py-1 text-xs font-bold text-rose-600 bg-rose-50 hover:bg-rose-100 hover:text-rose-700 border border-rose-200/80 rounded-lg transition-all cursor-pointer flex items-center gap-1 shrink-0" 
                    title="Delete ${escapeHtmlLocal(c.name)}">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                <span>Delete</span>
            </button>
        </div>
    `).join('');
}

window.openManageCouriersModal = function() {
    const modal = document.getElementById('manageCouriersModal');
    if (modal) {
        modal.style.display = 'flex';
        modal.classList.remove('hidden');
        loadCourierServices();
        setTimeout(() => {
            const input = document.getElementById('newCourierName');
            if (input) input.focus();
        }, 100);
    }
};

window.closeManageCouriersModal = function() {
    const modal = document.getElementById('manageCouriersModal');
    if (modal) {
        modal.style.display = 'none';
        modal.classList.add('hidden');
    }
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
        headers: { 
            'Content-Type': 'application/json',
            'Accept': 'application/json',
            'X-Requested-With': 'XMLHttpRequest'
        },
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
                headers: { 
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                },
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
                        title: `'${name}' deleted from dropdown`,
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

window.openTrackingModal = function(orderId, selectEl, courier = '', tracking = '', url = '', updateStatusToShipped = false) {
    currentTrackingOrderId = orderId;
    currentTrackingSelect = selectEl;
    
    document.getElementById('trackOrderId').value = orderId;
    document.getElementById('trackId').value = tracking || '';
    document.getElementById('trackUrl').value = url || '';
    // Flag: if opened from status portal (advance to Shipped), also update status after save
    const updateStatusEl = document.getElementById('trackUpdateStatus');
    if (updateStatusEl) updateStatusEl.value = updateStatusToShipped ? '1' : '0';
    
    loadCourierServices(courier).then(() => {
        const courierEl = document.getElementById('trackCourier');
        if (courierEl) courierEl.value = courier || '';
    });
    
    const modal = document.getElementById('trackingModal');
    if (modal) {
        modal.style.display = 'flex';
        modal.classList.remove('hidden');
    }
};

window.closeTrackingModal = function() {
    const modal = document.getElementById('trackingModal');
    if (modal) {
        modal.style.display = 'none';
        modal.classList.add('hidden');
    }
    if (currentTrackingSelect && currentTrackingSelect.getAttribute && currentTrackingSelect.getAttribute('data-original')) {
        currentTrackingSelect.value = currentTrackingSelect.getAttribute('data-original');
        if (typeof updateDropdownStyle === 'function') {
            updateDropdownStyle(currentTrackingSelect);
        }
    }
    currentTrackingSelect = null;
    currentTrackingOrderId = null;
};

window.submitTrackingDetails = function(e) {
    e.preventDefault();
    
    const orderId = currentTrackingOrderId;
    const selectEl = currentTrackingSelect;
    const courier = document.getElementById('trackCourier').value;
    const trackingNum = document.getElementById('trackId').value;
    const trackingUrl = document.getElementById('trackUrl') ? document.getElementById('trackUrl').value : '';
    const shouldUpdateStatus = document.getElementById('trackUpdateStatus') && document.getElementById('trackUpdateStatus').value === '1';
    
    if (!courier) {
        Swal.fire({
            icon: 'warning',
            title: 'Courier Required',
            text: 'Please select a Courier Service before saving tracking details.',
            confirmButtonColor: '#F25996'
        });
        return;
    }

    fetch('<?= BASE_URL ?>/admin/orders/update-courier', {
        method: 'POST',
        headers: { 
            'Content-Type': 'application/json',
            'Accept': 'application/json',
            'X-Requested-With': 'XMLHttpRequest'
        },
        body: JSON.stringify({ 
            order_id: orderId, 
            courier_name: courier, 
            tracking_number: trackingNum, 
            tracking_url: trackingUrl 
        })
    }).then(r => r.json()).then(res => {
        if (res.success) {
            if (shouldUpdateStatus) {
                // Also advance status to Shipped now that tracking is saved
                return fetch('<?= BASE_URL ?>/admin/orders/update-status', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ order_id: orderId, status: 'shipped' })
                })
                .then(r2 => r2.json())
                .then(res2 => {
                    closeTrackingModal();
                    if (res2.success) {
                        Swal.fire({
                            title: '🚚 Shipped!',
                            text: 'Tracking details saved and order status advanced to Shipped.',
                            icon: 'success',
                            confirmButtonColor: '#F25996'
                        }).then(() => location.reload());
                    } else {
                        Swal.fire({
                            icon: 'warning',
                            title: 'Tracking Saved, Status Not Updated',
                            text: res2.message || 'Tracking details were saved but the status could not be updated to Shipped.',
                            confirmButtonColor: '#F25996'
                        }).then(() => location.reload());
                    }
                });
            } else {
                closeTrackingModal();
                Swal.fire({
                    title: 'Tracking Details Saved!',
                    text: 'Courier tracking details have been saved successfully.',
                    icon: 'success',
                    confirmButtonColor: '#F25996'
                }).then(() => location.reload());
            }
        } else {
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: res.message || 'Failed to save tracking details.',
                confirmButtonColor: '#F25996'
            });
            closeTrackingModal();
        }
    }).catch(err => {
        console.error(err);
        Swal.fire({
            icon: 'error',
            title: 'Error',
            text: 'Failed to save tracking details.',
            confirmButtonColor: '#F25996'
        });
        closeTrackingModal();
    });
};

// Initial load of courier services
loadCourierServices();
</script>

<!-- Global Status Dropdown Portal Menu (Static positioning, closes on scroll) -->
<div id="orderStatusPortal" class="fixed inset-0 z-[9999] hidden">
    <!-- Backdrop to close on click outside -->
    <div class="fixed inset-0 bg-black/5" onclick="closeOrderStatusPortal()"></div>
    
    <!-- Static Positioned Card Container -->
    <div id="orderStatusFloatingCard" class="fixed bg-white rounded-2xl shadow-2xl border border-gray-100 p-2 min-w-[240px] max-w-[280px] z-[10000] ring-1 ring-black/5 flex flex-col gap-1" style="top: 0px; left: 0px;">
        <div class="px-3 py-2 border-b border-gray-100 flex items-center justify-between mb-1 bg-gray-50/80 -mx-2 -mt-2 rounded-t-2xl">
            <span class="text-[10px] font-bold text-gray-500 uppercase tracking-wider">Change Order Status</span>
            <span id="portalOrderNumber" class="text-[11px] font-mono font-bold text-[#F25996]"></span>
        </div>
        <div id="portalStatusOptions" class="flex flex-col gap-1">
            <!-- Dynamic Options Injected by JS -->
        </div>
    </div>
</div>

<?php if (!$isPendingView): ?>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    let currentActiveBtn = null;
    let currentActiveOrderId = null;

    const STATUS_CONFIG = {
        'placed': { label: 'Placed', icon: '⏳', desc: 'Order received', badge: 'bg-amber-100 text-amber-800' },
        'packed': { label: 'Packed', icon: '📦', desc: 'Videos & Invoice dispatched', badge: 'bg-purple-100 text-purple-800' },
        'shipped': { label: 'Shipped', icon: '🚚', desc: 'Courier & Tracking added', badge: 'bg-blue-100 text-blue-800' },
        'out_for_delivery': { label: 'Out for Delivery', icon: '🛵', desc: 'Out for customer delivery', badge: 'bg-indigo-100 text-indigo-800' },
        'delivered': { label: 'Delivered', icon: '✅', desc: 'Successfully delivered', badge: 'bg-emerald-100 text-emerald-800' },
        'cancelled': { label: 'Cancelled', icon: '❌', desc: 'Order cancelled & stock restored', badge: 'bg-rose-100 text-rose-800' }
    };

    const ALLOWED_TRANSITIONS = {
        'placed': ['packed', 'cancelled'],
        'packed': ['shipped', 'cancelled'],
        'shipped': ['out_for_delivery'],
        'out_for_delivery': ['delivered'],
        'delivered': [],
        'cancelled': []
    };

    window.closeOrderStatusPortal = function() {
        const portal = document.getElementById('orderStatusPortal');
        if (portal) portal.classList.add('hidden');
    };

    // Auto close portal on any scroll or resize so it never floats/moves in mid-air
    window.addEventListener('scroll', closeOrderStatusPortal, true);
    window.addEventListener('resize', closeOrderStatusPortal);

    window.openOrderStatusMenu = function(event, orderId, currentStatus) {
        if (event) {
            event.preventDefault();
            event.stopPropagation();
        }
        currentActiveOrderId = orderId;
        currentActiveBtn = event ? event.currentTarget : document.getElementById('status-btn-' + orderId);

        const portal = document.getElementById('orderStatusPortal');
        const card = document.getElementById('orderStatusFloatingCard');
        const badge = document.getElementById('portalOrderNumber');
        const optionsContainer = document.getElementById('portalStatusOptions');

        if (!portal || !card || !optionsContainer) return;

        badge.textContent = '#' + orderId;

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
                const isCancel = st === 'cancelled';
                const btnStyle = isCancel
                    ? 'hover:bg-red-50 text-red-700 border-red-100 hover:border-red-200'
                    : 'hover:bg-pink-50 text-gray-800 hover:text-pink-700 border-gray-100 hover:border-pink-200';
                const actionLabel = isCancel ? 'Cancel Order' : `Advance to ${conf.label}`;

                html += `
                    <button type="button" 
                            onclick="selectOrderStatusOption('${orderId}', '${currentStatus}', '${st}')"
                            class="w-full text-left px-3 py-2.5 rounded-xl text-xs font-bold ${btnStyle} flex items-center justify-between transition-colors group border shadow-2xs cursor-pointer">
                        <span class="flex items-center gap-2.5">
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

        // Position statically right under the clicked button
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
    };

    window.selectOrderStatusOption = function(orderId, currentStatus, newStatus) {
        closeOrderStatusPortal();

        if (newStatus === 'packed') {
            openPackingModal(orderId, currentActiveBtn);
            return;
        }

        // Shipped → require tracking details first; status is updated after tracking is saved
        if (newStatus === 'shipped') {
            openTrackingModal(orderId, currentActiveBtn, '', '', '', true);
            return;
        }

        const isCancel = newStatus === 'cancelled';
        const formatCurrent = currentStatus.replace('_', ' ').replace(/\b\w/g, l => l.toUpperCase());
        const formatTarget = newStatus.replace('_', ' ').replace(/\b\w/g, l => l.toUpperCase());

        Swal.fire({
            title: isCancel ? 'Cancel this Order?' : 'Update Order Status?',
            html: isCancel 
                ? `Are you sure you want to <span class="text-rose-600 font-bold">cancel</span> Order <b>#${orderId}</b>? This will restore inventory stock.`
                : `Are you sure you want to transition Order <b>#${orderId}</b> from <span class="text-gray-600 font-semibold">${formatCurrent}</span> to <span class="text-[#F25996] font-bold">${formatTarget}</span>?`,
            icon: isCancel ? 'warning' : 'question',
            showCancelButton: true,
            confirmButtonColor: isCancel ? '#dc2626' : '#F25996',
            cancelButtonColor: '#f3f4f6',
            confirmButtonText: isCancel ? 'Yes, Cancel Order' : 'Yes, Update Status',
            cancelButtonText: '<span style="color: #6b7280; font-weight: bold;">Keep as ' + formatCurrent + '</span>',
            reverseButtons: true,
            customClass: {
                popup: 'rounded-2xl shadow-xl border border-gray-100',
                confirmButton: 'font-bold rounded-full px-6 py-2.5 text-xs tracking-wider uppercase shadow-xs',
                cancelButton: 'font-bold rounded-full px-6 py-2.5 text-xs tracking-wider uppercase'
            }
        }).then((result) => {
            if (result.isConfirmed) {
                updateStatusAjax(orderId, newStatus, currentActiveBtn);
            }
        });
    };



    // --- Packing Videos Modal Handlers ---
    let currentPackingSelect = null;
    let currentPackingOrderId = null;

    window.openPackingModal = function(orderId, selectEl) {
        currentPackingOrderId = orderId;
        currentPackingSelect = selectEl;

        document.getElementById('packOrderId').value = orderId;
        document.getElementById('packOrderNumberBadge').textContent = '#' + orderId;
        document.getElementById('packingVideo1').value = '';
        document.getElementById('packingVideo2').value = '';
        document.getElementById('packingUploadStatus').classList.add('hidden');
        document.getElementById('packingSubmitBtn').disabled = false;
        document.getElementById('packingCancelBtn').disabled = false;

        const modal = document.getElementById('packingModal');
        modal.classList.remove('hidden');
    };

    window.closePackingModal = function() {
        const modal = document.getElementById('packingModal');
        modal.classList.add('hidden');
        if (currentPackingSelect) {
            currentPackingSelect.value = currentPackingSelect.getAttribute('data-original');
            updateDropdownStyle(currentPackingSelect);
        }
        currentPackingSelect = null;
        currentPackingOrderId = null;
    };

    window.validateVideoSize = function(input, labelName) {
        const maxBytes = 3 * 1024 * 1024;
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
    };

    window.submitPackingDetails = function(e) {
        e.preventDefault();

        const maxBytes = 3 * 1024 * 1024;
        const v1Input = document.getElementById('packingVideo1');
        const v2Input = document.getElementById('packingVideo2');

        if (v1Input && v1Input.files && v1Input.files[0] && v1Input.files[0].size > maxBytes) {
            const mb = (v1Input.files[0].size / (1024 * 1024)).toFixed(2);
            Swal.fire({
                title: 'File Too Large (Max 3MB)',
                text: `Packing Video 1 is ${mb}MB. Maximum allowed file size is 3MB. Please select a smaller video file.`,
                icon: 'warning',
                confirmButtonColor: '#7c3aed'
            });
            return false;
        }

        if (v2Input && v2Input.files && v2Input.files[0] && v2Input.files[0].size > maxBytes) {
            const mb = (v2Input.files[0].size / (1024 * 1024)).toFixed(2);
            Swal.fire({
                title: 'File Too Large (Max 3MB)',
                text: `Packing Video 2 is ${mb}MB. Maximum allowed file size is 3MB. Please select a smaller video file.`,
                icon: 'warning',
                confirmButtonColor: '#7c3aed'
            });
            return false;
        }

        const form = document.getElementById('packingForm');
        const formData = new FormData(form);
        const orderId = currentPackingOrderId;
        const selectEl = currentPackingSelect;

        document.getElementById('packingUploadStatus').classList.remove('hidden');
        document.getElementById('packingSubmitBtn').disabled = true;
        document.getElementById('packingCancelBtn').disabled = true;

        fetch('<?= BASE_URL ?>/admin/orders/update-packed', {
            method: 'POST',
            body: formData
        })
        .then(async response => {
            const rawText = await response.text();
            let res;
            try {
                res = JSON.parse(rawText);
            } catch (e) {
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
                document.getElementById('packingModal').classList.add('hidden');
                if (selectEl) {
                    selectEl.setAttribute('data-original', 'packed');
                    selectEl.value = 'packed';
                    if (typeof updateDropdownStyle === 'function') updateDropdownStyle(selectEl);
                    if (typeof updateDropdownOptions === 'function') updateDropdownOptions(selectEl, 'packed');
                }
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
                title: 'Upload Failed',
                text: (err.message && err.message.length < 150) ? err.message : 'Failed to upload packing videos or update status. Please try again.',
                icon: 'error',
                confirmButtonColor: '#F25996'
            });
        });
    };

    function updateStatusAjax(orderId, status, selectEl) {
        fetch('<?= BASE_URL ?>/admin/orders/update-status', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ order_id: orderId, status: status })
        })
        .then(response => response.json())
        .then(data => {
            if(data.success) {
                location.reload();
            } else {
                alert('Error: ' + data.message);
                if (selectEl) {
                    selectEl.value = selectEl.getAttribute('data-original');
                }
            }
        })
        .catch(err => {
            console.error(err);
            alert('Failed to update status.');
            if (selectEl) {
                selectEl.value = selectEl.getAttribute('data-original');
            }
        });
    }
});

function filterOrders() {
    let input = document.getElementById('searchInput');
    let filter = input.value.toLowerCase();
    let table = document.querySelector("table tbody");
    if(!table) return;
    let tr = table.getElementsByTagName('tr');

    for (let i = 0; i < tr.length; i++) {
        let textContent = tr[i].textContent || tr[i].innerText;
        if (textContent.toLowerCase().indexOf(filter) > -1) {
            tr[i].style.display = "";
        } else {
            tr[i].style.display = "none";
        }
    }
}
</script>

<!-- Chart.js for rendering the charts -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const topProductsData = <?= $topProducts ?? '[]' ?>;
    const statusDistributionData = <?= $statusDistribution ?? '[]' ?>;

    if (topProductsData && topProductsData.length > 0) {
        const productLabels = topProductsData.map(item => item.name);
        const productValues = topProductsData.map(item => item.total_sold);

        const ctxBar = document.getElementById('topProductsChart').getContext('2d');
        new Chart(ctxBar, {
            type: 'bar',
            data: {
                labels: productLabels,
                datasets: [{
                    label: 'Units Sold',
                    data: productValues,
                    backgroundColor: '#10b981', // green-500
                    borderRadius: 4,
                    barThickness: 40
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: { stepSize: 0.5 },
                        grid: { borderDash: [4, 4], display: true, color: '#f3f4f6' }
                    },
                    x: {
                        grid: { display: false }
                    }
                }
            }
        });
    }

    if (statusDistributionData && statusDistributionData.length > 0) {
        const statusColors = {
            'placed': '#f59e0b', // amber-500
            'packed': '#10b981', // green-500
            'shipped': '#F25996', // pink-500
            'out_for_delivery': '#db2777', // pink-600
            'delivered': '#14b8a6', // teal-500
            'cancelled': '#ef4444' // red-500
        };

        const statusLabels = statusDistributionData.map(item => item.status.charAt(0).toUpperCase() + item.status.slice(1));
        const statusValues = statusDistributionData.map(item => item.count);
        const bgColors = statusDistributionData.map(item => statusColors[item.status] || '#9ca3af');

        const ctxPie = document.getElementById('statusChart').getContext('2d');
        new Chart(ctxPie, {
            type: 'pie',
            data: {
                labels: statusLabels,
                datasets: [{
                    data: statusValues,
                    backgroundColor: bgColors,
                    borderWidth: 2,
                    borderColor: '#ffffff'
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'bottom',
                        labels: {
                            usePointStyle: true,
                            padding: 20
                        }
                    }
                }
            }
        });
    }
});
</script>
<?php endif; ?>

<script>
// Packing Slip Selection & Bulk Print Handlers
function toggleSelectAllOrders(master) {
    const cbs = document.querySelectorAll('.order-row-cb');
    cbs.forEach(cb => cb.checked = master.checked);
    updateSelectedOrdersCount();
}

function updateSelectedOrdersCount() {
    const selected = document.querySelectorAll('.order-row-cb:checked');
    const badge = document.getElementById('selectedOrdersCountBadge');
    if (!badge) return;
    if (selected.length > 0) {
        badge.textContent = `${selected.length} selected`;
        badge.classList.remove('hidden');
    } else {
        badge.classList.add('hidden');
    }
}

function openBulkPackingSlips(autoPrint = false) {
    const selected = Array.from(document.querySelectorAll('.order-row-cb:checked')).map(cb => cb.value);
    let url = '<?= BASE_URL ?>/admin/orders/packing-slips/bulk';
    if (selected.length > 0) {
        url += '?order_ids=' + encodeURIComponent(selected.join(','));
    } else {
        const allOnPage = Array.from(document.querySelectorAll('.order-row-cb')).map(cb => cb.value);
        if (allOnPage.length === 0) {
            alert('No orders available on this page to print packing slips.');
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
</div>
