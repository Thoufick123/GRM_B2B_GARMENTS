<?php
/**
 * @var array  $orders
 * @var array  $orderItems
 * @var array  $refundRequests   Keyed by order_id
 * @var string $activeTab
 */

// Status → badge colour mapping (extended for refund states)
function orderStatusBadge(string $status): string
{
    $map = [
        'pending'            => 'bg-yellow-50 text-yellow-700 border border-yellow-200',
        'placed'             => 'bg-pink-50 text-[#F25996] border border-pink-200',
        'packed'             => 'bg-purple-50 text-purple-700 border border-purple-200',
        'shipped'            => 'bg-indigo-50 text-indigo-700 border border-indigo-200',
        'out_for_delivery'   => 'bg-orange-50 text-orange-700 border border-orange-200',
        'delivered'          => 'bg-green-50 text-green-700 border border-green-200',
        'cancelled'          => 'bg-red-50 text-red-700 border border-red-200',
        'stitching'          => 'bg-blue-50 text-blue-700 border border-blue-200',
        'in_production'      => 'bg-cyan-50 text-cyan-700 border border-cyan-200',
        'quality_check'      => 'bg-teal-50 text-teal-700 border border-teal-200',
        'refund_pending'     => 'bg-amber-50 text-amber-700 border border-amber-200',
        'refund_processing'  => 'bg-sky-50 text-sky-700 border border-sky-200',
        'refund_completed'   => 'bg-emerald-50 text-emerald-700 border border-emerald-200',
        'refund_failed'      => 'bg-rose-50 text-rose-700 border border-rose-200',
    ];
    return $map[$status] ?? 'bg-gray-50 text-gray-700 border border-gray-200';
}

function orderStatusLabel(string $status): string
{
    $labels = [
        'placed'            => 'Placed',
        'packed'            => 'Packed',
        'shipped'           => 'Shipped',
        'out_for_delivery'  => 'Out for Delivery',
        'delivered'         => 'Delivered',
        'cancelled'         => 'Cancelled',
        'stitching'         => 'Stitching',
        'in_production'     => 'In Production',
        'quality_check'     => 'Quality Check',
        'refund_pending'    => 'Refund Pending',
        'refund_processing' => 'Refund Processing',
        'refund_completed'  => 'Refunded',
        'refund_failed'     => 'Refund Failed',
    ];
    return $labels[$status] ?? ucwords(str_replace('_', ' ', $status));
}
?>

<div class="bg-[#fafafa] min-h-screen flex flex-col md:flex-row relative">
    
    <?php include __DIR__ . '/_sidebar.php'; ?>
    
    <div class="flex-1 p-3 xs:p-4 sm:p-5 md:p-6 lg:p-12 relative w-full min-w-0 overflow-y-auto">
        <div class="max-w-5xl mx-auto w-full min-w-0">
            
            <div class="mb-5 sm:mb-8 border-b border-gray-100 pb-3 sm:pb-5 flex justify-between items-center">
                <h1 class="text-xl sm:text-2xl md:text-3xl font-black text-gray-900 font-display tracking-tight">Order History</h1>
            </div>

            <!-- Flash Messages -->
            <?php if ($flash = \Core\Session::getFlash('success')): ?>
                <div class="mb-4 px-4 py-3 bg-green-50 border border-green-200 text-green-800 rounded-xl text-xs sm:text-sm font-medium flex items-center gap-2">
                    <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                    <?= htmlspecialchars($flash) ?>
                </div>
            <?php endif; ?>
            <?php if ($flash = \Core\Session::getFlash('error')): ?>
                <div class="mb-4 px-4 py-3 bg-red-50 border border-red-200 text-red-800 rounded-xl text-xs sm:text-sm font-medium flex items-center gap-2">
                    <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01M21 12A9 9 0 113 12a9 9 0 0118 0z"/></svg>
                    <?= htmlspecialchars($flash) ?>
                </div>
            <?php endif; ?>

            <?php if (empty($orders)): ?>
                <div class="py-12 md:py-16 text-center text-gray-500 bg-white rounded-2xl border border-gray-100 shadow-sm">
                    <svg class="w-12 h-12 mx-auto text-gray-300 mb-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z" />
                    </svg>
                    <p class="text-base sm:text-lg font-medium">You haven't placed any orders yet.</p>
                    <a href="<?= BASE_URL ?>/catalog" class="text-[#F25996] hover:text-[#d8407d] font-bold hover:underline mt-2 inline-block text-sm">Browse Catalog</a>
                </div>
            <?php else: ?>
                <!-- Mobile View (Card List) -->
                <div class="lg:hidden space-y-3 sm:space-y-4">
                    <?php foreach ($orders as $order): 
                        $refundReq = $refundRequests[$order['id']] ?? null;
                        $cNum = strtoupper(trim(ltrim($order['order_number'] ?? '', '#')));
                        $isCustomOrder = (str_starts_with($cNum, 'GBC') || str_starts_with($cNum, 'GRM-CUST-') || str_contains($cNum, 'CUST') || !empty($order['customization_id']) || !empty($order['is_custom']) || (!empty($order['notes']) && (stripos($order['notes'], 'Custom Order') !== false || stripos($order['notes'], 'customization') !== false)));
                    ?>
                        <div class="bg-white border border-gray-100 rounded-2xl p-3.5 sm:p-4 shadow-sm relative">
                            <!-- Header: Order ID & Status Badge -->
                            <div class="flex justify-between items-start gap-2 mb-2.5">
                                <div class="min-w-0">
                                    <div class="text-sm font-bold text-gray-900 truncate">#<?= htmlspecialchars($order['order_number']) ?></div>
                                    <div class="text-xs text-gray-500 mt-0.5"><?= date('M d, Y', strtotime($order['created_at'])) ?></div>
                                </div>
                                <span class="px-2.5 py-1 text-[10px] font-bold uppercase tracking-wider rounded-full shrink-0 <?= orderStatusBadge($order['status']) ?>">
                                    <?= orderStatusLabel($order['status']) ?>
                                </span>
                            </div>

                            <!-- Total Amount Banner -->
                            <div class="flex items-center justify-between py-2 px-3 bg-gray-50/90 rounded-xl mb-3 border border-gray-100/80">
                                <span class="text-xs font-semibold text-gray-500">Total Amount</span>
                                <span class="text-sm sm:text-base font-extrabold text-gray-900">₹<?= number_format($order['grand_total'] ?? $order['total_amount'], 2) ?></span>
                            </div>

                            <!-- Action Buttons -->
                            <div class="grid grid-cols-2 sm:grid-cols-3 gap-2">
                                <!-- Invoice Download -->
                                <a href="<?= BASE_URL ?>/dashboard/orders/invoice?order=<?= htmlspecialchars($order['order_number']) ?>&t=<?= time() ?>" target="_blank"
                                   class="flex items-center justify-center gap-1.5 px-3 py-2 bg-pink-50/70 hover:bg-pink-100 border border-pink-200 text-[#db2777] rounded-xl text-xs font-bold transition-colors shadow-sm"
                                   title="Download Invoice">
                                    <svg class="w-3.5 h-3.5 text-[#db2777] shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                                    <span>Invoice</span>
                                </a>
                                <!-- View Details -->
                                <button onclick="openOrderModal('<?= $order['id'] ?>')" class="flex items-center justify-center gap-1.5 px-3 py-2 bg-[#F25996] hover:bg-[#d8407d] text-white rounded-xl text-xs font-bold transition-colors shadow-sm whitespace-nowrap">
                                    <svg class="w-3.5 h-3.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                    <span>View Details</span>
                                </button>
                                <?php if (!$isCustomOrder && !empty($orderRules[$order['id']]['eligible']) && !$refundReq && $cancellationsEnabled): ?>
                                    <?php $listRuleData = $orderRules[$order['id']]; ?>
                                    <button type="button" 
                                        onclick="openCancelModal(<?= $order['id'] ?>, '<?= htmlspecialchars($order['order_number']) ?>', <?= number_format($order['grand_total'] ?? $order['total_amount'], 2, '.', '') ?>, <?= number_format($listRuleData['refund_amount'], 2, '.', '') ?>, <?= number_format($listRuleData['fee_amount'], 2, '.', '') ?>, <?= $listRuleData['fee_percent'] ?>, '<?= number_format($order['weight_fee'] ?? 0, 2, '.', '') ?>', '<?= number_format($order['packaging_fee'] ?? 0, 2, '.', '') ?>', '<?= number_format($order['shipping_fee'] ?? 0, 2, '.', '') ?>', '<?= number_format($order['platform_fee'] ?? 0, 2, '.', '') ?>', <?= (int)($listRuleData['sla_days'] ?? 0) ?>)"
                                        class="col-span-2 sm:col-span-1 flex items-center justify-center gap-1.5 px-3 py-2 bg-red-50 hover:bg-red-100 border border-red-200 text-red-600 rounded-xl text-xs font-bold transition-colors shadow-sm whitespace-nowrap">
                                        Cancel Order
                                    </button>
                                <?php endif; ?>
                            </div>

                            <?php if ($refundReq): ?>
                                <?php
                                $rBadge = [
                                    'pending_admin_approval' => 'bg-amber-50 text-amber-700 border border-amber-200',
                                    'processing'             => 'bg-sky-50 text-sky-700 border border-sky-200',
                                    'completed'              => 'bg-emerald-50 text-emerald-700 border border-emerald-200',
                                    'rejected'               => 'bg-rose-50 text-rose-700 border border-rose-200',
                                ][$refundReq['status']] ?? 'bg-gray-50 text-gray-600 border border-gray-200';
                                $rAmt = (float)($refundReq['amount'] ?? $refundReq['refund_amount'] ?? 0);
                                $rLabel = [
                                    'pending_admin_approval' => '⏳ Refund Under Review' . ($rAmt > 0 ? ' (₹' . number_format($rAmt, 2) . ')' : ''),
                                    'processing'             => '🔄 Refund Processing' . ($rAmt > 0 ? ' (₹' . number_format($rAmt, 2) . ')' : ''),
                                    'completed'              => '✅ Refunded ₹' . number_format($rAmt, 2),
                                    'rejected'               => '❌ Refund Rejected',
                                ][$refundReq['status']] ?? ($refundReq['status'] ?? 'Refund processing');
                                ?>
                                <div class="mt-3 pt-3 border-t border-gray-100 flex items-center justify-between">
                                    <span class="px-2.5 py-1 inline-flex text-xs font-bold rounded-full <?= $rBadge ?>">
                                        <?= $rLabel ?>
                                    </span>
                                </div>
                            <?php endif; ?>
                        </div>
                    <?php endforeach; ?>
                </div>

                <!-- Desktop View (Table) -->
                <div class="hidden lg:block bg-white border border-gray-100 rounded-2xl overflow-hidden shadow-sm">
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-100">
                            <thead>
                                <tr>
                                    <th class="px-6 py-4 bg-[#fafafa] text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Order ID</th>
                                    <th class="px-6 py-4 bg-[#fafafa] text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Date</th>
                                    <th class="px-6 py-4 bg-[#fafafa] text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Status</th>
                                    <th class="px-6 py-4 bg-[#fafafa] text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Total</th>
                                    <th class="px-6 py-4 bg-[#fafafa] text-right text-xs font-bold text-gray-500 uppercase tracking-wider">Action</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-100">
                                <?php foreach ($orders as $order):
                                    $refundReq = $refundRequests[$order['id']] ?? null;
                                    $cNum = strtoupper(trim(ltrim($order['order_number'] ?? '', '#')));
                                    $isCustomOrder = (str_starts_with($cNum, 'GBC') || str_starts_with($cNum, 'GRM-CUST-') || str_contains($cNum, 'CUST') || !empty($order['customization_id']) || !empty($order['is_custom']) || (!empty($order['notes']) && (stripos($order['notes'], 'Custom Order') !== false || stripos($order['notes'], 'customization') !== false)));
                                ?>
                                    <tr class="hover:bg-gray-50 transition-colors">
                                        <td class="px-6 py-5 whitespace-nowrap text-sm font-bold text-gray-900">
                                            #<?= htmlspecialchars($order['order_number']) ?>
                                        </td>
                                        <td class="px-6 py-5 whitespace-nowrap text-sm text-gray-600">
                                            <?= date('M d, Y', strtotime($order['created_at'])) ?>
                                        </td>
                                        <td class="px-6 py-5 whitespace-nowrap">
                                            <div class="flex flex-col gap-1">
                                                <span class="px-3 py-1 inline-flex text-xs leading-5 font-bold rounded-full <?= orderStatusBadge($order['status']) ?> uppercase tracking-wider">
                                                    <?= orderStatusLabel($order['status']) ?>
                                                </span>
                                                <?php if ($refundReq): ?>
                                                    <?php
                                                    $rBadge = [
                                                        'pending_admin_approval' => 'bg-amber-50 text-amber-700 border border-amber-200',
                                                        'processing'             => 'bg-sky-50 text-sky-700 border border-sky-200',
                                                        'completed'              => 'bg-emerald-50 text-emerald-700 border border-emerald-200',
                                                        'rejected'               => 'bg-rose-50 text-rose-700 border border-rose-200',
                                                    ][$refundReq['status']] ?? 'bg-gray-50 text-gray-600 border border-gray-200';
                                                    $rLabel = [
                                                        'pending_admin_approval' => '⏳ Refund Under Review',
                                                        'processing'             => '🔄 Refund Processing',
                                                        'completed'              => '✅ Refunded ₹' . number_format($refundReq['amount']),
                                                        'rejected'               => '❌ Refund Rejected',
                                                    ][$refundReq['status']] ?? $refundReq['status'];
                                                    ?>
                                                    <span class="px-2.5 py-0.5 inline-flex text-xs font-bold rounded-full <?= $rBadge ?>">
                                                        <?= $rLabel ?>
                                                    </span>
                                                <?php endif; ?>
                                            </div>
                                        </td>
                                        <td class="px-6 py-5 whitespace-nowrap text-sm text-gray-900 font-black">
                                            ₹<?= number_format($order['grand_total'] ?? $order['total_amount']) ?>
                                        </td>
                                        <td class="px-6 py-5 whitespace-nowrap text-right text-sm font-medium">
                                            <div class="flex justify-end gap-2 items-center">
                                                <button onclick="openOrderModal('<?= $order['id'] ?>')" class="text-white bg-[#F25996] hover:bg-[#d8407d] px-4 py-2 rounded-lg font-bold transition-colors shadow-sm text-xs">
                                                    View Details
                                                </button>
                                                <a href="<?= BASE_URL ?>/dashboard/orders/invoice?order=<?= htmlspecialchars($order['order_number']) ?>&t=<?= time() ?>" target="_blank" class="text-[#db2777] bg-pink-50 hover:bg-pink-100 border border-pink-200 px-3 py-2 rounded-lg font-bold transition-colors shadow-sm text-xs flex items-center gap-1" title="Download Invoice">
                                                    <svg class="w-3.5 h-3.5 text-[#db2777]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                                                    Invoice
                                                </a>
                                                <?php if ($order['status'] === 'pending' && $order['payment_status'] === 'pending'): ?>
                                                    <a href="<?= BASE_URL ?>/checkout/retry?order_id=<?= $order['order_number'] ?>" class="text-white bg-brand-600 hover:bg-brand-700 px-4 py-2 rounded-lg font-bold transition-colors shadow-sm text-xs">
                                                        Pay Now
                                                    </a>
                                                <?php endif; ?>
                                                <?php if (!$isCustomOrder && !empty($orderRules[$order['id']]['eligible']) && !$refundReq && $cancellationsEnabled): ?>
                                                    <?php $listRuleData = $orderRules[$order['id']]; ?>
                                                    <button type="button" 
                                                        onclick="openCancelModal(<?= $order['id'] ?>, '<?= htmlspecialchars($order['order_number']) ?>', <?= number_format($order['grand_total'] ?? $order['total_amount'], 2, '.', '') ?>, <?= number_format($listRuleData['refund_amount'], 2, '.', '') ?>, <?= number_format($listRuleData['fee_amount'], 2, '.', '') ?>, <?= $listRuleData['fee_percent'] ?>, '<?= number_format($order['weight_fee'] ?? 0, 2, '.', '') ?>', '<?= number_format($order['packaging_fee'] ?? 0, 2, '.', '') ?>', '<?= number_format($order['shipping_fee'] ?? 0, 2, '.', '') ?>', '<?= number_format($order['platform_fee'] ?? 0, 2, '.', '') ?>', <?= (int)($listRuleData['sla_days'] ?? 0) ?>)"
                                                        class="text-red-600 font-bold bg-red-50 hover:bg-red-100 border border-red-200 px-3 py-2 rounded-lg transition-colors shadow-sm text-xs">
                                                        Cancel Order
                                                    </button>
                                                <?php elseif ($refundReq && $refundReq['status'] === 'pending_admin_approval'): ?>
                                                    <span class="text-amber-700 bg-amber-50 border border-amber-200 px-4 py-2 rounded-lg font-bold text-xs cursor-default">
                                                        Request Submitted
                                                    </span>
                                                <?php endif; ?>
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
    </div>
    
    <!-- ===================================================================== -->
    <!-- Order Detail Modals                                                    -->
    <!-- ===================================================================== -->
    <?php if (!empty($orders)): ?>
        <?php foreach ($orders as $order):
            $items     = $orderItems[$order['id']] ?? [];
            $refundReq = $refundRequests[$order['id']] ?? null;
            $cNum = strtoupper(trim(ltrim($order['order_number'] ?? '', '#')));
            $isCustomOrder = (str_starts_with($cNum, 'GBC') || str_starts_with($cNum, 'GRM-CUST-') || str_contains($cNum, 'CUST') || !empty($order['customization_id']) || !empty($order['is_custom']) || (!empty($order['notes']) && (stripos($order['notes'], 'Custom Order') !== false || stripos($order['notes'], 'customization') !== false)));
            if (!$isCustomOrder && !empty($items)) {
                foreach ($items as $item) {
                    if (!empty($item['customization_id']) || !empty($item['customization_data'])) {
                        $isCustomOrder = true;
                        break;
                    }
                }
            }
        ?>
            <div id="order-modal-<?= $order['id'] ?>" class="fixed inset-0 z-[100] hidden flex items-center justify-center p-2.5 sm:p-4 md:p-6" role="dialog" aria-modal="true">
                <div class="fixed inset-0 bg-gray-900/60 backdrop-blur-sm transition-opacity" onclick="closeOrderModal('<?= $order['id'] ?>')"></div>

                <div class="relative w-full max-w-3xl bg-white rounded-2xl sm:rounded-3xl shadow-2xl border border-gray-100 overflow-hidden transform transition-all flex flex-col max-h-[92vh] sm:max-h-[90vh]">
                    
                    <!-- Header -->
                    <div class="p-3.5 sm:p-5 md:p-6 border-b border-gray-100 bg-[#fafafa]">
                        <div class="flex items-start justify-between gap-2.5">
                            <div class="flex items-center gap-2.5 sm:gap-3.5 min-w-0">
                                <div class="w-9 h-9 sm:w-11 sm:h-11 bg-white rounded-full flex items-center justify-center shadow-sm border border-gray-200 shrink-0">
                                    <svg class="w-4 h-4 sm:w-5 sm:h-5 text-gray-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"></path>
                                    </svg>
                                </div>
                                <div class="min-w-0">
                                    <div class="flex flex-wrap items-center gap-1.5 sm:gap-2">
                                        <h2 class="text-base sm:text-lg md:text-xl font-black text-gray-900 tracking-tight leading-tight">Order Details</h2>
                                        <span class="px-2 sm:px-2.5 py-0.5 inline-flex text-[10px] sm:text-xs font-bold rounded-full <?= orderStatusBadge($order['status']) ?> uppercase tracking-wider shrink-0">
                                            <?= orderStatusLabel($order['status']) ?>
                                        </span>
                                    </div>
                                    <p class="text-gray-500 font-medium text-xs sm:text-sm mt-0.5">Placed on <?= date('M d, Y', strtotime($order['created_at'])) ?></p>
                                </div>
                            </div>
                            <button onclick="closeOrderModal('<?= $order['id'] ?>')" class="text-gray-400 hover:text-gray-600 bg-white hover:bg-gray-100 rounded-full p-1.5 sm:p-2 transition-colors border border-gray-200 shadow-sm shrink-0 ml-1">
                                <svg class="w-4 h-4 sm:w-5 sm:h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                            </button>
                        </div>
                    </div>

                    <!-- Body (scrollable) -->
                    <div class="p-3.5 sm:p-5 md:p-6 overflow-y-auto flex-1 bg-white space-y-4 sm:space-y-6">

                        <!-- Refund status banner -->
                        <?php if ($refundReq): ?>
                            <?php
                            $bannerColors = [
                                'pending_admin_approval' => 'bg-amber-50 border-amber-200 text-amber-800',
                                'processing'             => 'bg-sky-50 border-sky-200 text-sky-800',
                                'completed'              => 'bg-emerald-50 border-emerald-200 text-emerald-800',
                                'rejected'               => 'bg-rose-50 border-rose-200 text-rose-800',
                            ][$refundReq['status']] ?? 'bg-gray-50 border-gray-200 text-gray-700';
                            ?>
                            <div class="px-3.5 py-2.5 sm:px-4 sm:py-3 rounded-xl border <?= $bannerColors ?> text-xs sm:text-sm font-medium">
                                <?php if ($refundReq['status'] === 'pending_admin_approval'): ?>
                                    ⏳ <strong>Cancellation request submitted.</strong> Under review by our team.
                                <?php elseif ($refundReq['status'] === 'processing'): ?>
                                    🔄 <strong>Refund is being processed</strong> via Razorpay. Expected in 5–7 business days.
                                <?php elseif ($refundReq['status'] === 'completed'): ?>
                                    ✅ <strong>Refund of ₹<?= number_format($refundReq['amount']) ?> has been completed.</strong> Please allow 5–7 business days.
                                <?php elseif ($refundReq['status'] === 'rejected'): ?>
                                    ❌ <strong>Refund request was rejected.</strong> Check your email for details.
                                <?php endif; ?>
                            </div>
                        <?php endif; ?>
                        
                        <!-- Order Modified banner -->
                        <?php if (isset($orderModifications[$order['id']])): ?>
                            <?php 
                            $mod = $orderModifications[$order['id']];
                            $diff = (float)$mod['revised_total'] - (float)$mod['original_total'];
                            $isPending = ($mod['extra_payment_status'] ?? 'pending') === 'pending';
                            ?>
                            <div class="px-3.5 py-2.5 sm:px-4 sm:py-3.5 rounded-xl border bg-blue-50 border-blue-200 text-blue-800 text-xs sm:text-sm font-medium space-y-2">
                                <div class="flex items-center justify-between gap-2 flex-wrap">
                                    <div class="flex items-center gap-1.5">
                                        <span class="text-base">⚡</span>
                                        <strong class="text-blue-900">Order Modified by Admin.</strong>
                                    </div>
                                    <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-blue-100 text-blue-800 uppercase tracking-wide">
                                        <?= htmlspecialchars(ucfirst($mod['status'])) ?>
                                    </span>
                                </div>
                                
                                <div class="grid grid-cols-1 sm:grid-cols-3 gap-2 py-1 text-xs">
                                    <div class="bg-white/90 p-2 rounded-lg border border-blue-100">
                                        <span class="text-gray-500 block text-[10px] font-bold uppercase">Original Total (Paid)</span>
                                        <span class="font-bold text-gray-800">₹<?= number_format($mod['original_total'], 2) ?></span>
                                    </div>
                                    <div class="bg-white/90 p-2 rounded-lg border border-blue-100">
                                        <span class="text-gray-500 block text-[10px] font-bold uppercase">Revised Total</span>
                                        <span class="font-black text-indigo-950">₹<?= number_format($mod['revised_total'], 2) ?></span>
                                    </div>
                                    <div class="bg-white/90 p-2 rounded-lg border border-blue-100">
                                        <span class="text-gray-500 block text-[10px] font-bold uppercase"><?= $diff >= 0 ? 'Balance to Pay' : 'Refund' ?></span>
                                        <span class="font-bold <?= $diff > 0 ? ($isPending ? 'text-red-600' : 'text-green-600') : 'text-green-600' ?>">
                                            <?= $diff > 0 ? '+₹' . number_format($diff, 2) . ' (' . ($isPending ? 'Pending' : 'Paid') . ')' : ($diff < 0 ? '-₹' . number_format(abs($diff), 2) : '₹0.00') ?>
                                        </span>
                                    </div>
                                </div>

                                <?php if (!empty($mod['reason'])): ?>
                                    <p class="text-xs text-blue-700 font-normal"><strong>Reason:</strong> <?= htmlspecialchars($mod['reason']) ?></p>
                                <?php endif; ?>
                                
                                <?php if ($diff > 0 && $isPending): ?>
                                    <div class="flex items-center justify-between gap-2 pt-1 border-t border-blue-200/60 flex-wrap">
                                        <p class="text-xs font-bold text-red-600">Balance Due to Pay for Replaced Items: +₹<?= number_format($diff, 2) ?></p>
                                        <a href="<?= BASE_URL ?>/order-modifications/review/<?= $mod['id'] ?>" class="px-3 py-1 bg-red-600 hover:bg-red-700 text-white rounded-lg text-xs font-bold shadow-xs transition inline-flex items-center gap-1">
                                            💳 Pay / Review Difference (+₹<?= number_format($diff, 2) ?>)
                                        </a>
                                    </div>
                                    <?php if (!empty($mod['extra_payment_note'])): ?>
                                        <p class="mt-1 text-xs text-red-500 font-medium bg-red-50 p-2 rounded-lg border border-red-100">Note: <?= htmlspecialchars($mod['extra_payment_note']) ?></p>
                                    <?php endif; ?>
                                <?php elseif ($diff > 0 && !$isPending): ?>
                                    <p class="text-xs font-bold text-green-600">✅ Extra payment complete (+₹<?= number_format($diff, 2) ?>)</p>
                                <?php elseif ($diff < 0): ?>
                                    <p class="text-xs font-bold text-green-600">Refund Amount: -₹<?= number_format(abs($diff), 2) ?></p>
                                <?php endif; ?>
                            </div>
                        <?php endif; ?>

                        <div class="grid grid-cols-2 gap-2.5 sm:gap-4 pb-3 sm:pb-5 border-b border-gray-100">
                            <div class="bg-gray-50 p-2.5 sm:p-4 rounded-xl sm:rounded-2xl border border-gray-100 min-w-0">
                                <p class="text-[10px] sm:text-xs text-gray-500 uppercase tracking-wider font-bold mb-0.5 sm:mb-1">Order Number</p>
                                <p class="text-sm sm:text-lg font-black text-gray-900 truncate">#<?= htmlspecialchars($order['order_number']) ?></p>
                            </div>
                            <div class="bg-gray-50 p-2.5 sm:p-4 rounded-xl sm:rounded-2xl border border-gray-100 text-right min-w-0">
                                <?php if (isset($orderModifications[$order['id']])): ?>
                                    <?php 
                                    $topMod = $orderModifications[$order['id']];
                                    $topDiff = (float)$topMod['revised_total'] - (float)$topMod['original_total'];
                                    $topPending = ($topMod['extra_payment_status'] ?? 'pending') === 'pending';
                                    ?>
                                    <?php if ($topDiff > 0 && $topPending): ?>
                                        <p class="text-[10px] sm:text-xs text-gray-500 uppercase tracking-wider font-bold mb-0.5 sm:mb-1">Amount Paid (Original)</p>
                                        <p class="text-sm sm:text-xl font-black text-emerald-700">₹<?= number_format($topMod['original_total'], 2) ?></p>
                                        <p class="text-[10px] sm:text-xs font-bold text-red-600 mt-0.5">Due: +₹<?= number_format($topDiff, 2) ?></p>
                                    <?php else: ?>
                                        <p class="text-[10px] sm:text-xs text-gray-500 uppercase tracking-wider font-bold mb-0.5 sm:mb-1">Total Paid</p>
                                        <p class="text-sm sm:text-xl font-black text-[#1e1b4b]">₹<?= number_format($order['grand_total'] ?? $order['total_amount'], 2) ?></p>
                                    <?php endif; ?>
                                <?php else: ?>
                                    <p class="text-[10px] sm:text-xs text-gray-500 uppercase tracking-wider font-bold mb-0.5 sm:mb-1">Total Paid</p>
                                    <p class="text-sm sm:text-xl font-black text-[#1e1b4b]">₹<?= number_format($order['grand_total'] ?? $order['total_amount']) ?></p>
                                <?php endif; ?>
                            </div>
                        </div>

                        <!-- Dynamic Order Status Tracker -->
                        <?php
                        $timelineSteps = [
                            'placed' => 'Placed',
                            'packed' => 'Packed',
                            'shipped' => 'Shipped',
                            'out_for_delivery' => 'Out for Delivery',
                            'delivered' => 'Delivered'
                        ];
                        // fallback to 0 if not found so at least it shows "placed"
                        $currentStepIndex = array_search($order['status'], array_keys($timelineSteps));
                        if ($currentStepIndex === false) {
                            $currentStepIndex = ($order['status'] === 'cancelled' || str_contains($order['status'], 'refund')) ? -1 : 0;
                        }
                        ?>
                        <div class="bg-white p-3.5 sm:p-6 rounded-2xl sm:rounded-3xl border border-gray-100 shadow-sm overflow-x-auto">
                            <div class="flex items-center gap-3 sm:gap-4 mb-6 sm:mb-8 min-w-[280px]">
                                <div class="w-10 h-10 sm:w-12 sm:h-12 bg-green-50 text-green-600 rounded-xl flex items-center justify-center shrink-0">
                                    <svg class="w-5 h-5 sm:w-6 sm:h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path></svg>
                                </div>
                                <div>
                                    <h3 class="text-base sm:text-xl font-bold text-gray-900 font-display">Order <?= orderStatusLabel($order['status']) ?></h3>
                                </div>
                            </div>
                            
                            <div class="relative mt-10 sm:mt-12 mb-3 sm:mb-4 px-2 min-w-[280px]">
                                <!-- Background Line -->
                                <div class="absolute left-0 top-4 w-full h-1 bg-gray-200 rounded-full z-0"></div>
                                
                                <?php if ($currentStepIndex >= 0): ?>
                                    <!-- Active Progress Bar -->
                                    <div class="absolute left-0 top-4 h-1 bg-green-500 rounded-full transition-all duration-500 z-0" style="width: <?= ($currentStepIndex / (count($timelineSteps) - 1)) * 100 ?>%"></div>
                                <?php endif; ?>
                                
                                <div class="relative flex justify-between w-full z-10">
                                    <?php 
                                    $i = 0;
                                    foreach ($timelineSteps as $stepKey => $stepLabel): 
                                        $isCompleted = $i <= $currentStepIndex;
                                        $isActive = $i === $currentStepIndex;
                                    ?>
                                        <div class="flex flex-col items-center relative group w-14 sm:w-16 -ml-3 sm:-ml-4">
                                            <?php if ($isActive): ?>
                                                <div class="absolute -top-9 sm:-top-10 left-1/2 transform -translate-x-1/2 bg-gray-800 text-white text-[9px] sm:text-[10px] font-bold px-1.5 sm:px-2 py-0.5 sm:py-1 rounded shadow-lg whitespace-nowrap z-20">
                                                    <span class="w-1.5 h-1.5 inline-block bg-green-400 rounded-full mr-1"></span>In Progress!
                                                    <div class="absolute -bottom-1 left-1/2 transform -translate-x-1/2 w-2 h-2 bg-gray-800 rotate-45"></div>
                                                </div>
                                            <?php endif; ?>
                                            
                                            <div class="w-7 h-7 sm:w-8 sm:h-8 rounded-full flex items-center justify-center bg-white <?= $isCompleted ? 'border-none' : 'border border-gray-200' ?> z-10 shadow-sm relative">
                                                <?php if ($isCompleted && !$isActive): ?>
                                                    <div class="w-full h-full rounded-full bg-green-500 flex items-center justify-center text-white">
                                                        <svg class="w-3.5 h-3.5 sm:w-4 sm:h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"></path></svg>
                                                    </div>
                                                <?php elseif ($isActive): ?>
                                                    <div class="w-full h-full rounded-full border-2 border-orange-200 flex items-center justify-center text-[#8c5a38] bg-orange-50">
                                                        <svg class="w-3.5 h-3.5 sm:w-4 sm:h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17a2 2 0 11-4 0 2 2 0 014 0zM19 17a2 2 0 11-4 0 2 2 0 014 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16V6a1 1 0 00-1-1H4a1 1 0 00-1 1v10a1 1 0 001 1h1m8-1a1 1 0 01-1 1H9m4-1V8a1 1 0 011-1h2.586a1 1 0 01.707.293l3.414 3.414a1 1 0 01.293.707V16a1 1 0 01-1 1h-1m-6-1a1 1 0 001 1h1M5 17a2 2 0 104 0m-4 0a2 2 0 114 0m6 0a2 2 0 104 0m-4 0a2 2 0 114 0"></path></svg>
                                                    </div>
                                                <?php else: ?>
                                                    <div class="w-2 h-2 rounded-full bg-gray-200"></div>
                                                <?php endif; ?>
                                            </div>
                                            
                                            <div class="text-center mt-1.5 sm:mt-2 w-16 sm:w-20">
                                                <p class="text-[11px] sm:text-xs font-bold <?= $isCompleted ? 'text-gray-900' : 'text-gray-400' ?> leading-tight"><?= $stepLabel ?></p>
                                                <?php if ($stepKey === 'placed'): ?>
                                                    <p class="text-[9px] text-gray-500 leading-tight mt-0.5"><?= date('d M Y', strtotime($order['created_at'])) ?></p>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                    <?php 
                                        $i++;
                                    endforeach; 
                                    ?>
                                </div>
                            </div>
                        </div>

                        <!-- Packing Verification Videos -->
                        <?php if (!empty($order['packing_video_1']) || !empty($order['packing_video_2'])): ?>
                            <?php
                                $isDelivered = ($order['status'] === 'delivered');
                                $expiryTimestamp = null;
                                $isExpired = false;
                                $daysLeft = 30;

                                if ($isDelivered || !empty($order['delivered_at'])) {
                                    $delTime = !empty($order['delivered_at']) ? strtotime($order['delivered_at']) : strtotime($order['updated_at'] ?? $order['created_at']);
                                    $expiryTimestamp = !empty($order['packing_video_expires_at']) ? strtotime($order['packing_video_expires_at']) : strtotime('+30 days', $delTime);
                                    if (time() > $expiryTimestamp) {
                                        $isExpired = true;
                                    } else {
                                        $daysLeft = max(0, ceil(($expiryTimestamp - time()) / 86400));
                                    }
                                }
                            ?>
                            <div class="bg-gradient-to-br from-purple-50 via-white to-purple-50/30 p-3.5 sm:p-5 rounded-2xl sm:rounded-3xl border border-purple-200/80 shadow-sm">
                                <div class="flex items-center justify-between gap-2 mb-3">
                                    <div class="flex items-center gap-2 sm:gap-2.5 min-w-0">
                                        <div class="w-8 h-8 rounded-lg bg-purple-600 text-white flex items-center justify-center font-bold text-sm shadow-sm shrink-0">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z"/></svg>
                                        </div>
                                        <div class="min-w-0">
                                            <h4 class="font-bold text-gray-900 text-xs sm:text-sm truncate">Packing Verification Videos</h4>
                                            <p class="text-[10px] sm:text-[11px] text-gray-500 truncate">Quality check recorded by warehouse</p>
                                        </div>
                                    </div>
                                    <?php if ($isDelivered): ?>
                                        <?php if ($isExpired): ?>
                                            <span class="px-2 py-0.5 rounded-full text-[9px] sm:text-[10px] font-bold bg-gray-100 text-gray-500 border border-gray-200 shrink-0">
                                                Expired
                                            </span>
                                        <?php else: ?>
                                            <span class="px-2 py-0.5 rounded-full text-[9px] sm:text-[10px] font-bold bg-green-100 text-green-800 border border-green-200 shrink-0">
                                                ⏱️ <?= $daysLeft ?>d left
                                            </span>
                                        <?php endif; ?>
                                    <?php else: ?>
                                        <span class="px-2 py-0.5 rounded-full text-[9px] sm:text-[10px] font-bold bg-purple-100 text-purple-800 border border-purple-200 shrink-0 hidden xs:inline-block">
                                            30 days limit
                                        </span>
                                    <?php endif; ?>
                                </div>

                                <?php if (!$isExpired): ?>
                                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-2.5 sm:gap-3 mt-3">
                                        <?php if (!empty($order['packing_video_1'])): 
                                            $v1Url = (strpos($order['packing_video_1'], 'http') === 0) ? $order['packing_video_1'] : (BASE_URL . '/' . ltrim($order['packing_video_1'], '/'));
                                        ?>
                                        <a href="<?= htmlspecialchars($v1Url) ?>" target="_blank" class="flex items-center justify-between p-2.5 sm:p-3 rounded-xl sm:rounded-2xl bg-white border border-purple-200 hover:border-purple-400 hover:shadow-md transition-all group">
                                            <div class="flex items-center gap-2 sm:gap-2.5 min-w-0">
                                                <div class="w-7 h-7 sm:w-8 sm:h-8 rounded-full bg-purple-100 text-purple-700 flex items-center justify-center font-bold text-xs shrink-0">1</div>
                                                <div class="min-w-0">
                                                    <span class="text-xs font-bold text-gray-900 group-hover:text-purple-700 transition-colors block truncate">Item &amp; Box Packing</span>
                                                    <span class="text-[10px] text-gray-400">Video 1</span>
                                                </div>
                                            </div>
                                            <span class="px-2.5 sm:px-3 py-1 bg-purple-600 group-hover:bg-purple-700 text-white rounded-lg text-xs font-bold transition-colors flex items-center gap-1 shadow-sm shrink-0 ml-2">
                                                Watch
                                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
                                            </span>
                                        </a>
                                        <?php endif; ?>

                                        <?php if (!empty($order['packing_video_2'])): 
                                            $v2Url = (strpos($order['packing_video_2'], 'http') === 0) ? $order['packing_video_2'] : (BASE_URL . '/' . ltrim($order['packing_video_2'], '/'));
                                        ?>
                                        <a href="<?= htmlspecialchars($v2Url) ?>" target="_blank" class="flex items-center justify-between p-2.5 sm:p-3 rounded-xl sm:rounded-2xl bg-white border border-purple-200 hover:border-purple-400 hover:shadow-md transition-all group">
                                            <div class="flex items-center gap-2 sm:gap-2.5 min-w-0">
                                                <div class="w-7 h-7 sm:w-8 sm:h-8 rounded-full bg-purple-100 text-purple-700 flex items-center justify-center font-bold text-xs shrink-0">2</div>
                                                <div class="min-w-0">
                                                    <span class="text-xs font-bold text-gray-900 group-hover:text-purple-700 transition-colors block truncate">Sealing &amp; Label</span>
                                                    <span class="text-[10px] text-gray-400">Video 2</span>
                                                </div>
                                            </div>
                                            <span class="px-2.5 sm:px-3 py-1 bg-purple-600 group-hover:bg-purple-700 text-white rounded-lg text-xs font-bold transition-colors flex items-center gap-1 shadow-sm shrink-0 ml-2">
                                                Watch
                                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
                                            </span>
                                        </a>
                                        <?php endif; ?>
                                    </div>
                                    <p class="text-[10px] sm:text-[10.5px] text-purple-700/80 mt-2.5">
                                        ℹ️ Videos can be viewed or downloaded directly. Available for 30 days following order delivery.
                                    </p>
                                <?php else: ?>
                                    <p class="text-xs text-gray-500 mt-2">
                                        The 30-day retention window for these packing videos has expired. If you need assistance regarding this order, please contact support.
                                    </p>
                                <?php endif; ?>
                            </div>
                        <?php endif; ?>

                        <!-- Products -->
                        <div>
                            <h3 class="text-base sm:text-lg font-black text-gray-900 mb-3 sm:mb-4 flex items-center gap-2">
                                <svg class="w-4 h-4 sm:w-5 sm:h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 10h16M4 14h16M4 18h16"></path></svg>
                                Order Items
                            </h3>
                            <?php
                            $mod = $orderModifications[$order['id']] ?? null;
                            $thisModItems = ($mod && !empty($orderModItems[$mod['id']])) ? $orderModItems[$mod['id']] : [];
                            $modItemByOI = [];
                            $removedModItems = [];
                            if (!empty($thisModItems)) {
                                foreach ($thisModItems as $mi) {
                                    if ($mi['change_type'] === 'removed') {
                                        $removedModItems[] = $mi;
                                    } elseif (!empty($mi['order_item_id'])) {
                                        $modItemByOI[$mi['order_item_id']] = $mi;
                                    } elseif (!empty($mi['product_id'])) {
                                        $modItemByOI['p_' . $mi['product_id']] = $mi;
                                    }
                                }
                            }
                            ?>
                            <div class="space-y-3 sm:space-y-4">
                                <?php foreach($items as $item): 
                                    $modItem = $modItemByOI[$item['id']] ?? $modItemByOI['p_' . ($item['product_id'] ?? 0)] ?? null;
                                    $isReplaced = ($modItem && $modItem['change_type'] === 'replaced');
                                    $isQtyChanged = ($modItem && $modItem['change_type'] === 'qty_changed');
                                    $isPriceChanged = ($modItem && $modItem['change_type'] === 'price_changed');
                                    $isAdded = ($modItem && $modItem['change_type'] === 'added');
                                ?>
                                    <div class="bg-white p-3 sm:p-4 rounded-2xl border <?= $isReplaced ? 'border-blue-200 bg-blue-50/10' : 'border-gray-200' ?> hover:border-gray-300 transition-colors shadow-sm">
                                        <div class="flex gap-3 sm:gap-4 items-start">
                                            <!-- Thumbnail -->
                                            <div class="w-16 h-16 sm:w-20 sm:h-20 bg-gray-50 rounded-xl border border-gray-100 shrink-0 flex items-center justify-center overflow-hidden">
                                                <?php if (!empty($item['image'])): ?>
                                                    <?php
                                                    $imgSrc = $item['image'];
                                                    if ($imgSrc && !str_starts_with($imgSrc, 'http') && !str_starts_with($imgSrc, '/')) {
                                                        $imgSrc = '/' . $imgSrc;
                                                    }
                                                    ?>
                                                    <img src="<?= htmlspecialchars($imgSrc) ?>" alt="<?= htmlspecialchars($item['name']) ?>" class="w-full h-full object-contain p-1 sm:p-1.5" onerror="this.style.display='none';this.nextElementSibling.style.display='flex'">
                                                    <svg class="w-6 h-6 text-gray-300 hidden" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                                                <?php else: ?>
                                                    <svg class="w-6 h-6 sm:w-8 sm:h-8 text-gray-300" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                                                <?php endif; ?>
                                            </div>

                                            <!-- Details -->
                                            <div class="flex-1 min-w-0">
                                                <div class="flex items-start justify-between gap-2">
                                                    <div>
                                                        <h4 class="font-bold text-gray-900 text-xs sm:text-base leading-snug line-clamp-2" title="<?= htmlspecialchars($item['name']) ?>">
                                                            <?= htmlspecialchars($item['name']) ?>
                                                        </h4>
                                                        <?php if ($isReplaced): ?>
                                                            <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold bg-blue-100 text-blue-700 border border-blue-200 mt-1">
                                                                🔄 Replaced Product (Bill Modified)
                                                            </span>
                                                        <?php elseif ($isAdded): ?>
                                                            <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold bg-green-100 text-green-700 border border-green-200 mt-1">
                                                                ✨ Added to Bill
                                                            </span>
                                                        <?php endif; ?>
                                                    </div>
                                                    <div class="text-right shrink-0">
                                                        <span class="text-[9px] text-gray-400 font-bold uppercase tracking-wider block sm:hidden">Total</span>
                                                        <span class="font-black text-gray-900 text-sm sm:text-lg whitespace-nowrap">₹<?= number_format($item['total_price']) ?></span>
                                                    </div>
                                                </div>

                                                <!-- Meta Badges -->
                                                <div class="flex flex-wrap gap-1 sm:gap-1.5 text-[10px] sm:text-xs font-semibold mt-1.5 sm:mt-2">
                                                    <?php if (!empty($item['garment_type']) || !empty($item['customization_id'])): ?>
                                                        <span class="bg-pink-50 text-[#db2777] px-2 py-0.5 rounded-md border border-pink-200">✂️ Custom <?= htmlspecialchars($item['garment_type'] ?? 'Garment') ?><?= !empty($item['program_name']) ? ' — ' . htmlspecialchars($item['program_name']) : '' ?></span>
                                                    <?php endif; ?>
                                                    <?php if (!empty($item['variant_name'])): ?>
                                                        <span class="bg-[#eef8e5] text-gray-800 px-2 py-0.5 rounded-md border border-[#d5edc4]">Variant: <?= htmlspecialchars($item['variant_name']) ?></span>
                                                    <?php endif; ?>
                                                    <?php if (!empty($item['category_hsn_code'])): ?>
                                                        <span class="bg-pink-50 text-[#db2777] px-2 py-0.5 rounded-md border border-pink-200">HSN: <?= htmlspecialchars($item['category_hsn_code']) ?></span>
                                                    <?php endif; ?>
                                                    <span class="bg-gray-100 text-gray-700 px-2 py-0.5 rounded-md border border-gray-200">Qty: <?= $item['quantity'] ?></span>
                                                    <?php if (!empty($item['weight'])): ?>
                                                        <span class="bg-gray-100 text-gray-700 px-2 py-0.5 rounded-md border border-gray-200"><?= floatval($item['weight']) * intval($item['quantity']) ?> kg</span>
                                                    <?php endif; ?>
                                                    <span class="bg-gray-100 text-gray-700 px-2 py-0.5 rounded-md border border-gray-200">₹<?= number_format($item['unit_price'] ?? ($item['total_price'] / $item['quantity'])) ?> / unit</span>
                                                </div>

                                                <?php if ($isReplaced): 
                                                    $origPName = !empty($modItem['original_product_name']) ? $modItem['original_product_name'] : $item['name'];
                                                    $origVName = !empty($modItem['original_variant_name']) ? $modItem['original_variant_name'] : ($item['variant_name'] ?? '');
                                                    $origImg   = !empty($modItem['original_image']) ? $modItem['original_image'] : ($modItem['product_image'] ?? $item['image']);
                                                    $repPName  = !empty($modItem['replacement_product_name']) ? $modItem['replacement_product_name'] : $item['name'];
                                                    $repVName  = !empty($modItem['replacement_variant_name']) ? $modItem['replacement_variant_name'] : ($item['variant_name'] ?? '');
                                                    $repImg    = !empty($modItem['replacement_image']) ? $modItem['replacement_image'] : $item['image'];
                                                    $itemDiff  = ($modItem['new_qty'] * $modItem['new_price']) - ($modItem['old_qty'] * $modItem['old_price']);
                                                ?>
                                                    <div class="mt-2.5 p-2.5 sm:p-3 bg-blue-50/80 border border-blue-200 rounded-xl text-xs space-y-2.5">
                                                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-2.5">
                                                            <!-- Customer First Order Product -->
                                                            <div class="bg-red-50/50 border border-red-200/80 rounded-lg p-2.5 flex items-center gap-2.5">
                                                                <div class="w-12 h-12 bg-white rounded-md border border-red-200 shrink-0 overflow-hidden flex items-center justify-center">
                                                                    <?php if (!empty($origImg)): ?>
                                                                        <img src="<?= htmlspecialchars(get_image_url($origImg)) ?>" class="w-full h-full object-contain p-0.5">
                                                                    <?php else: ?>
                                                                        <svg class="w-5 h-5 text-gray-300" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                                                                    <?php endif; ?>
                                                                </div>
                                                                <div class="flex-1 min-w-0">
                                                                    <span class="text-[9px] font-extrabold uppercase tracking-wider text-red-700 block">⏮️ First Order Product</span>
                                                                    <p class="font-bold text-gray-900 text-xs truncate" title="<?= htmlspecialchars($origPName) ?>"><?= htmlspecialchars($origPName) ?></p>
                                                                    <?php if (!empty($origVName)): ?>
                                                                        <p class="text-[10px] text-gray-500">Variant: <?= htmlspecialchars($origVName) ?></p>
                                                                    <?php endif; ?>
                                                                    <p class="text-[10px] text-gray-600 font-medium"><?= $modItem['old_qty'] ?> × ₹<?= number_format($modItem['old_price'], 2) ?> = <strong class="text-gray-900">₹<?= number_format($modItem['old_qty'] * $modItem['old_price'], 2) ?></strong></p>
                                                                </div>
                                                            </div>

                                                            <!-- Replaced Product -->
                                                            <div class="bg-blue-50/50 border border-blue-200 rounded-lg p-2.5 flex items-center gap-2.5">
                                                                <div class="w-12 h-12 bg-white rounded-md border border-blue-200 shrink-0 overflow-hidden flex items-center justify-center">
                                                                    <?php if (!empty($repImg)): ?>
                                                                        <img src="<?= htmlspecialchars(get_image_url($repImg)) ?>" class="w-full h-full object-contain p-0.5">
                                                                    <?php else: ?>
                                                                        <svg class="w-5 h-5 text-gray-300" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                                                                    <?php endif; ?>
                                                                </div>
                                                                <div class="flex-1 min-w-0">
                                                                    <span class="text-[9px] font-extrabold uppercase tracking-wider text-blue-700 block">🔄 Replaced Product</span>
                                                                    <p class="font-bold text-gray-900 text-xs truncate" title="<?= htmlspecialchars($repPName) ?>"><?= htmlspecialchars($repPName) ?></p>
                                                                    <?php if (!empty($repVName)): ?>
                                                                        <p class="text-[10px] text-blue-600">Variant: <?= htmlspecialchars($repVName) ?></p>
                                                                    <?php endif; ?>
                                                                    <p class="text-[10px] text-gray-600 font-medium"><?= $modItem['new_qty'] ?> × ₹<?= number_format($modItem['new_price'], 2) ?> = <strong class="text-brand-700">₹<?= number_format($modItem['new_qty'] * $modItem['new_price'], 2) ?></strong></p>
                                                                </div>
                                                            </div>
                                                        </div>

                                                        <div class="pt-1.5 border-t border-blue-200/60 flex items-center justify-between text-[11px]">
                                                            <span class="text-gray-600 font-medium">Difference for this item:</span>
                                                            <span class="font-bold <?= $itemDiff >= 0 ? 'text-red-600' : 'text-green-700' ?>">
                                                                <?= $itemDiff >= 0 ? '+₹' . number_format($itemDiff, 2) . ' (To pay for replacement)' : '-₹' . number_format(abs($itemDiff), 2) . ' (Refund / Adjustment)' ?>
                                                            </span>
                                                        </div>
                                                    </div>
                                                <?php endif; ?>

                                                <?php if ($order['status'] === 'delivered'): 
                                                    $rCid = !empty($item['customization_id']) ? $item['customization_id'] : '0';
                                                    $rPid = $item['resolved_product_id'] ?? $item['product_id'];
                                                    $reviewKey = $order['id'] . '_' . $rPid . '_' . $rCid;
                                                    $reviewKeyAlt = $order['id'] . '_' . $rPid;
                                                    $existingRev = $userReviewedMap[$reviewKey] ?? $userReviewedMap[$reviewKeyAlt] ?? null;
                                                    $isCustomItem = !empty($item['customization_id']);
                                                ?>
                                                    <div class="mt-2.5 flex items-center gap-2 flex-wrap" id="review-action-container-<?= $order['id'] ?>-<?= $rPid ?>-<?= $rCid ?>">
                                                        <?php if ($existingRev): ?>
                                                            <span class="px-2.5 py-1 bg-green-50 text-green-700 border border-green-200 rounded-lg text-xs font-bold inline-flex items-center gap-1 shadow-2xs">
                                                                <svg class="w-3.5 h-3.5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                                                                <span>Reviewed</span>
                                                                <span class="text-amber-500 font-bold ml-0.5"><?= str_repeat('★', (int)$existingRev['rating']) ?></span>
                                                            </span>
                                                            <?php if (empty($existingRev['is_approved'])): ?>
                                                                <span class="text-[10px] text-amber-600 bg-amber-50 px-2 py-0.5 rounded-md border border-amber-200">Pending Verification</span>
                                                            <?php else: ?>
                                                                <span class="text-[10px] text-green-700 bg-green-100/60 px-2 py-0.5 rounded-md font-medium">Approved &amp; Live</span>
                                                            <?php endif; ?>
                                                        <?php else: ?>
                                                            <button type="button" 
                                                                    onclick="activateOrderReviewModal({
                                                                        orderId: <?= (int)$order['id'] ?>,
                                                                        orderNumber: '<?= htmlspecialchars($order['order_number'], ENT_QUOTES) ?>',
                                                                        productId: <?= (int)$rPid ?>,
                                                                        customizationId: <?= !empty($item['customization_id']) ? (int)$item['customization_id'] : 'null' ?>,
                                                                        productName: '<?= htmlspecialchars(addslashes($item['name']), ENT_QUOTES) ?>',
                                                                        isCustom: <?= $isCustomItem ? 'true' : 'false' ?>,
                                                                        garmentType: '<?= htmlspecialchars(addslashes($item['garment_type'] ?? ''), ENT_QUOTES) ?>',
                                                                        image: '<?= htmlspecialchars($imgSrc ?? '', ENT_QUOTES) ?>'
                                                                    })" 
                                                                    class="px-3 py-1.5 <?= $isCustomItem ? 'bg-gradient-to-r from-[#F25996] to-pink-600 text-white hover:from-pink-600 hover:to-pink-700 shadow-sm' : 'bg-pink-50 text-[#F25996] hover:bg-pink-100 border border-pink-200' ?> rounded-xl text-xs font-bold transition-all inline-flex items-center gap-1.5 transform hover:scale-[1.02] active:scale-[0.98]">
                                                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z"/></svg>
                                                                <span><?= $isCustomItem ? '✂️ Review Custom Product' : '⭐ Write Review' ?></span>
                                                            </button>
                                                        <?php endif; ?>
                                                    </div>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>

                                <?php if (!empty($removedModItems)): ?>
                                    <?php foreach ($removedModItems as $remItem): ?>
                                        <div class="bg-red-50/50 p-3 sm:p-4 rounded-2xl border border-red-200 opacity-80">
                                            <div class="flex items-center justify-between gap-3">
                                                <div>
                                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold bg-red-100 text-red-700 border border-red-200 mb-1">
                                                        ❌ Removed from Bill
                                                    </span>
                                                    <h4 class="font-bold text-gray-700 text-xs sm:text-sm line-through">
                                                        <?= htmlspecialchars($remItem['original_product_name'] ?? 'Removed Product') ?><?= !empty($remItem['original_variant_name']) ? ' (' . htmlspecialchars($remItem['original_variant_name']) . ')' : '' ?>
                                                    </h4>
                                                    <p class="text-[11px] text-gray-500 mt-0.5">
                                                        Old Bill: <?= $remItem['old_qty'] ?> × ₹<?= number_format($remItem['old_price'], 2) ?> = ₹<?= number_format($remItem['old_qty'] * $remItem['old_price'], 2) ?>
                                                    </p>
                                                </div>
                                                <div class="text-right">
                                                    <span class="text-xs font-bold text-green-700">Deducted from Bill</span>
                                                </div>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </div>
                            
                            <!-- Fees Summary -->
                            <div class="mt-4 bg-gray-50 p-3 sm:p-4 rounded-xl sm:rounded-2xl border border-gray-100 flex flex-col items-end gap-1.5 text-xs sm:text-sm">
                                <?php 
                                    $itemsSubtotal = 0;
                                    foreach($items as $i) { $itemsSubtotal += $i['total_price']; }
                                    $mod = $orderModifications[$order['id']] ?? null;
                                    $revisedTotal = (float)($order['grand_total'] ?? $order['total_amount']);
                                    $otherFeeAmount = max(0, $revisedTotal - $itemsSubtotal);
                                ?>
                                <div class="flex justify-between w-full max-w-sm text-gray-600">
                                    <span><?= $mod ? 'Revised Items Subtotal:' : 'Items Subtotal:' ?></span>
                                    <span class="font-bold">₹<?= number_format($itemsSubtotal, 2) ?></span>
                                </div>

                                <?php if ($mod): ?>
                                    <?php if ($otherFeeAmount > 0): ?>
                                    <div class="flex justify-between w-full max-w-sm text-gray-600">
                                        <span>Other Fees (Shipping, Platform, etc.):</span>
                                        <span class="font-bold text-gray-800">+₹<?= number_format($otherFeeAmount, 2) ?></span>
                                    </div>
                                    <?php endif; ?>
                                <?php else: ?>
                                    <?php if (floatval($order['weight_fee'] ?? 0) > 0): ?>
                                    <div class="flex justify-between w-full max-w-sm text-gray-600">
                                        <span>Weight Fee:</span>
                                        <span class="font-bold text-red-600">+₹<?= number_format($order['weight_fee'], 2) ?></span>
                                    </div>
                                    <?php endif; ?>
                                    <?php if (floatval($order['packaging_fee'] ?? 0) > 0): ?>
                                    <div class="flex justify-between w-full max-w-sm text-gray-600">
                                        <span>Packaging Fee:</span>
                                        <span class="font-bold text-red-600">+₹<?= number_format($order['packaging_fee'], 2) ?></span>
                                    </div>
                                    <?php endif; ?>
                                    <?php if (floatval($order['shipping_fee'] ?? 0) > 0): ?>
                                    <div class="flex justify-between w-full max-w-sm text-gray-600">
                                        <span>Shipping Fee:</span>
                                        <span class="font-bold text-red-600">+₹<?= number_format($order['shipping_fee'], 2) ?></span>
                                    </div>
                                    <?php endif; ?>
                                    <?php if (floatval($order['platform_fee'] ?? 0) > 0): ?>
                                    <div class="flex justify-between w-full max-w-sm text-gray-600">
                                        <span>Platform Fee:</span>
                                        <span class="font-bold text-red-600">+₹<?= number_format($order['platform_fee'], 2) ?></span>
                                    </div>
                                    <?php endif; ?>
                                <?php endif; ?>

                                <div class="flex justify-between w-full max-w-sm text-[#1e1b4b] mt-2 pt-2 border-t border-gray-200">
                                    <span class="font-bold text-sm sm:text-base"><?= $mod ? 'Revised Grand Total:' : 'Total Paid:' ?></span>
                                    <span class="font-black text-base sm:text-lg">₹<?= number_format($revisedTotal, 2) ?></span>
                                </div>

                                <?php if ($mod): 
                                    $origPaid = (float)$mod['original_total'];
                                    $diff = $revisedTotal - $origPaid;
                                ?>
                                <div class="w-full max-w-sm bg-indigo-50/80 p-3 rounded-xl border border-indigo-100 mt-2 space-y-1.5 text-xs text-left">
                                    <div class="flex justify-between text-gray-700">
                                        <span>Amount Already Paid (Old Bill):</span>
                                        <span class="font-semibold text-gray-900">₹<?= number_format($origPaid, 2) ?></span>
                                    </div>
                                    <div class="flex justify-between text-gray-700">
                                        <span>Revised Bill Total:</span>
                                        <span class="font-bold text-[#1e1b4b]">₹<?= number_format($revisedTotal, 2) ?></span>
                                    </div>
                                    <?php if ($diff > 0): ?>
                                        <div class="flex justify-between font-bold text-red-600 pt-1 border-t border-indigo-100 text-xs sm:text-sm">
                                            <span>Balance Due (To Pay for Replaced Items):</span>
                                            <span>+₹<?= number_format($diff, 2) ?></span>
                                        </div>
                                    <?php elseif ($diff < 0): ?>
                                        <div class="flex justify-between font-bold text-green-700 pt-1 border-t border-indigo-100 text-xs sm:text-sm">
                                            <span>Refund Amount Due:</span>
                                            <span>-₹<?= number_format(abs($diff), 2) ?></span>
                                        </div>
                                    <?php else: ?>
                                        <div class="flex justify-between font-bold text-gray-600 pt-1 border-t border-indigo-100 text-xs sm:text-sm">
                                            <span>Balance Amount:</span>
                                            <span>₹0.00</span>
                                        </div>
                                    <?php endif; ?>
                                </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Footer -->
                    <div class="p-3 sm:p-4 border-t border-gray-100 bg-gray-50 flex flex-col sm:flex-row sm:justify-between sm:items-center gap-2.5 sm:gap-4 shrink-0 rounded-b-2xl sm:rounded-b-3xl">
                        <div class="flex items-center justify-center sm:justify-start">
                            <?php if (!$isCustomOrder && !empty($orderRules[$order['id']]['eligible']) && !$refundReq && $cancellationsEnabled): ?>
                                <?php $ruleData = $orderRules[$order['id']]; ?>
                                <button
                                    onclick="closeOrderModal('<?= $order['id'] ?>'); openCancelModal(<?= $order['id'] ?>, '<?= htmlspecialchars($order['order_number']) ?>', <?= number_format($order['grand_total'] ?? $order['total_amount'], 2, '.', '') ?>, <?= number_format($ruleData['refund_amount'], 2, '.', '') ?>, <?= number_format($ruleData['fee_amount'], 2, '.', '') ?>, <?= $ruleData['fee_percent'] ?>, '<?= number_format($order['weight_fee'] ?? 0, 2, '.', '') ?>', '<?= number_format($order['packaging_fee'] ?? 0, 2, '.', '') ?>', '<?= number_format($order['shipping_fee'] ?? 0, 2, '.', '') ?>', '<?= number_format($order['platform_fee'] ?? 0, 2, '.', '') ?>', <?= (int)($ruleData['sla_days'] ?? 0) ?>)"
                                    class="text-red-600 hover:text-red-800 transition-colors text-xs sm:text-sm font-bold underline decoration-red-200 underline-offset-4 hover:decoration-red-600 py-1">
                                    Cancel Order
                                </button>
                            <?php elseif ($refundReq): ?>
                                <span class="text-xs text-gray-500 font-medium italic text-center sm:text-left">Cancellation request is <?= str_replace('_', ' ', $refundReq['status']) ?>.</span>
                            <?php else: ?>
                                <div></div>
                            <?php endif; ?>
                        </div>
                        <div class="grid grid-cols-2 sm:flex sm:items-center gap-2 w-full sm:w-auto">
                            <a href="<?= BASE_URL ?>/dashboard/orders/invoice?order=<?= htmlspecialchars($order['order_number']) ?>&t=<?= time() ?>" target="_blank" class="px-3 sm:px-4 py-2 sm:py-2.5 bg-[#F25996] hover:bg-[#d8407d] text-white font-bold rounded-xl shadow-sm transition-all text-xs sm:text-sm flex items-center justify-center gap-1.5 whitespace-nowrap">
                                <svg class="w-3.5 h-3.5 sm:w-4 sm:h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                                <span>Invoice</span>
                            </a>
                            <button onclick="closeOrderModal('<?= $order['id'] ?>')" class="px-4 sm:px-5 py-2 sm:py-2.5 bg-white border border-gray-300 hover:bg-gray-100 text-gray-700 font-bold rounded-xl shadow-sm transition-all focus:outline-none focus:ring-2 focus:ring-[#1e1b4b] focus:ring-offset-2 text-xs sm:text-sm whitespace-nowrap">
                                Close
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>

    <!-- ===================================================================== -->
    <!-- Cancel / Refund Request Modal                                         -->
    <!-- ===================================================================== -->
    <div id="cancel-refund-modal" class="fixed inset-0 z-[200] hidden overflow-y-auto" role="dialog" aria-modal="true" aria-labelledby="cancel-modal-title">
        <div class="fixed inset-0 bg-gray-900/60 backdrop-blur-sm" onclick="closeCancelModal()"></div>
        <div class="flex items-center justify-center min-h-full p-2.5 sm:p-4 py-6">
        <div class="relative w-full max-w-lg bg-white rounded-2xl sm:rounded-3xl shadow-2xl border border-gray-100 overflow-visible">
            
            <!-- Header -->
            <div class="p-4 sm:p-6 border-b border-pink-100 bg-gradient-to-r from-pink-50 via-rose-50 to-pink-50 rounded-t-2xl sm:rounded-t-3xl">
                <div class="flex items-center justify-between gap-3">
                    <div class="flex items-center gap-3 min-w-0">
                        <div class="w-9 h-9 sm:w-10 sm:h-10 bg-pink-100 rounded-full flex items-center justify-center shrink-0">
                            <svg class="w-4 h-4 sm:w-5 sm:h-5 text-[#F25996]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                        </div>
                        <div class="min-w-0">
                            <h2 id="cancel-modal-title" class="text-base sm:text-lg font-black text-gray-900">Cancel Order</h2>
                            <p id="cancel-modal-subtitle" class="text-xs sm:text-sm text-gray-500 truncate"></p>
                        </div>
                    </div>
                    <button type="button" onclick="closeCancelModal()" class="text-gray-400 hover:text-gray-600 bg-white hover:bg-gray-100 rounded-full p-1.5 transition-colors border border-gray-200 shadow-sm shrink-0 cursor-pointer">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                    </button>
                </div>
            </div>

            <!-- Form -->
            <form id="cancel-refund-form" onsubmit="submitCancellationRequest(event)">
                <input type="hidden" id="cancel-order-id" name="order_id">
                <div class="p-4 sm:p-6 space-y-3.5 sm:space-y-4">
                    
                    <!-- Calculation Breakdown -->
                    <div class="bg-gray-50 border border-gray-200 rounded-xl sm:rounded-2xl p-3.5 sm:p-4 text-xs sm:text-sm text-gray-700 font-medium space-y-3">
                        
                        <!-- Order Time Calculation (How Total Paid was reached) -->
                        <div id="cancel-collected-fees-section" class="pb-3 border-b border-gray-200 flex flex-col items-end gap-1.5 text-xs sm:text-sm">
                            <div class="flex justify-between w-full text-gray-600">
                                <span>Items Subtotal:</span>
                                <span class="font-bold" id="cancel-items-subtotal"></span>
                            </div>
                            <div id="cancel-collected-fees-list" class="w-full flex flex-col items-end gap-1.5 hidden">
                                <!-- Injected via JS -->
                            </div>
                            <div class="flex justify-between w-full text-[#1e1b4b] mt-2 pt-2 border-t border-gray-200">
                                <span class="font-bold text-sm sm:text-base">Total Paid:</span>
                                <span class="font-black text-base sm:text-lg" id="cancel-total-paid"></span>
                            </div>
                        </div>

                        <!-- Deductions -->
                        <div class="space-y-1 text-red-600">
                            <div id="cancel-deduction-row" class="flex justify-between items-center hidden">
                                <span>Less Cancellation Charges:</span>
                                <span id="cancel-deduction-amount"></span>
                            </div>
                        </div>
                        
                        <div class="pt-3 border-t border-gray-200 flex justify-between items-center text-[#1e1b4b] font-black text-sm sm:text-lg">
                            <span>Final Refund Amount:</span>
                            <span id="cancel-modal-amount"></span>
                        </div>
                    </div>

                    <!-- Reason dropdown -->
                    <div>
                        <label class="block text-xs sm:text-sm font-bold text-gray-700 mb-1.5" for="cancel-reason-select">Reason for Cancellation <span class="text-red-500">*</span></label>
                        <select id="cancel-reason-select" onchange="handleReasonSelect(this)" class="custom-select-pink w-full">
                            <option value="">— Select a reason —</option>
                            <option value="Changed my mind">Changed my mind</option>
                            <option value="Found a better price elsewhere">Found a better price elsewhere</option>
                            <option value="Ordered by mistake">Ordered by mistake</option>
                            <option value="Product is no longer needed">Product is no longer needed</option>
                            <option value="Delivery time is too long">Delivery time is too long</option>
                            <option value="other">Other (please specify)</option>
                        </select>
                    </div>

                    <!-- Custom reason textarea (shown only for "Other") -->
                    <div id="cancel-reason-other-wrap" class="hidden">
                        <label class="block text-xs sm:text-sm font-bold text-gray-700 mb-1" for="cancel-reason-text">Please describe your reason <span class="text-red-500">*</span></label>
                        <textarea id="cancel-reason-text" name="reason_text" rows="3" maxlength="500" placeholder="Tell us more..." class="w-full border border-gray-300 rounded-xl px-3.5 sm:px-4 py-2 sm:py-2.5 text-xs sm:text-sm resize-none focus:outline-none focus:ring-2 focus:ring-[#F25996] focus:border-[#F25996] transition"></textarea>
                        <p class="text-[10px] sm:text-xs text-gray-400 mt-1 text-right"><span id="reason-char-count">0</span>/500</p>
                    </div>

                    <!-- Refund Method Dropdown -->
                    <div>
                        <label class="block text-xs sm:text-sm font-bold text-gray-700 mb-1.5" for="refund-method-select">Refund Method <span class="text-red-500">*</span></label>
                        <select id="refund-method-select" name="refund_method" onchange="handleRefundMethodSelect(this)" required class="custom-select-pink w-full">
                            <option value="">— Select where to send refund —</option>
                            <option value="UPI ID">UPI ID</option>
                            <option value="Phone Number">Phone Number (GPay/PhonePe etc.)</option>
                        </select>
                    </div>

                    <!-- Refund Details Input -->
                    <div id="refund-details-wrap" class="hidden">
                        <label class="block text-xs sm:text-sm font-bold text-gray-700 mb-1" id="refund-details-label" for="refund-details-input">Refund Details <span class="text-red-500">*</span></label>
                        <input type="text" id="refund-details-input" name="refund_details" placeholder="Enter details..." class="w-full border border-gray-300 rounded-xl px-3.5 sm:px-4 py-2 sm:py-2.5 text-xs sm:text-sm focus:outline-none focus:ring-2 focus:ring-[#1e1b4b] focus:border-transparent transition">
                        <p id="refund-details-hint" class="mt-1.5 text-[11px] sm:text-xs text-blue-700 bg-blue-50 border border-blue-100 rounded-lg px-3 py-2 flex items-center gap-1.5">
                            <svg class="w-3.5 h-3.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            <span id="refund-details-hint-text">The refund amount will be sent to this UPI ID.</span>
                        </p>
                    </div>

                    <!-- Dynamic Refund SLA Notice -->
                    <div id="cancel-sla-notice" class="p-3 sm:p-3.5 bg-gradient-to-r from-pink-50/80 via-rose-50/60 to-pink-50/80 border border-pink-200/90 rounded-xl text-xs flex items-center gap-2.5 text-gray-700 shadow-2xs">
                        <div class="w-8 h-8 rounded-full bg-pink-100/80 border border-pink-200 flex items-center justify-center text-[#F25996] flex-shrink-0 shadow-2xs">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        </div>
                        <div class="flex-1 min-w-0">
                            <span class="font-bold text-gray-900 text-xs sm:text-[13px] block">Estimated Refund Timeline</span>
                            <span class="text-gray-600 text-[11px] sm:text-xs leading-snug" id="cancel-sla-text">Refund will be processed and credited within <strong>3 business days</strong>.</span>
                        </div>
                    </div>
                </div>

                <!-- Footer -->
                <div class="p-4 sm:p-6 pt-0 grid grid-cols-2 sm:flex sm:justify-end gap-2.5 sm:gap-3">
                    <button type="button" onclick="closeCancelModal()" class="px-5 py-2.5 bg-gray-100 hover:bg-gray-200 border border-gray-200 text-gray-700 font-bold rounded-full transition text-xs sm:text-sm cursor-pointer">
                        Keep Order
                    </button>
                    <button id="cancel-submit-btn" type="submit" class="px-6 py-2.5 bg-gradient-to-r from-[#F25996] to-[#ea3c85] hover:opacity-95 text-white font-bold rounded-full shadow-sm transition text-xs sm:text-sm flex items-center justify-center gap-1.5 disabled:opacity-60 disabled:cursor-not-allowed whitespace-nowrap cursor-pointer">
                        <span id="cancel-submit-label">Submit Cancellation</span>
                        <svg id="cancel-submit-spinner" class="w-3.5 h-3.5 sm:w-4 sm:h-4 animate-spin hidden" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8z"></path></svg>
                    </button>
                </div>
            </form>
        </div>
        </div>
    </div>

    <!-- Toast notification -->
    <div id="toast-container" class="fixed bottom-6 right-6 z-[300] flex flex-col gap-2 pointer-events-none"></div>
</div>

<script>
// ── Order detail modals ───────────────────────────────────────────────────────
function openOrderModal(id) {
    const m = document.getElementById('order-modal-' + id);
    if (m) { m.classList.remove('hidden'); document.body.style.overflow = 'hidden'; }
}
function closeOrderModal(id) {
    const m = document.getElementById('order-modal-' + id);
    if (m) { m.classList.add('hidden'); document.body.style.overflow = ''; }
}

// ── Cancel / Refund modal ─────────────────────────────────────────────────────
let _cancelOrderId = null;

function openCancelModal(orderId, orderNumber, totalPaid, refundAmount, feeAmount, feePercent, weightFee, packagingFee, shippingFee, platformFee, slaDays) {
    _cancelOrderId = orderId;
    document.getElementById('cancel-order-id').value   = orderId;
    document.getElementById('cancel-modal-subtitle').textContent = 'Order #' + orderNumber;
    
    console.log('openCancelModal called with:', {totalPaid, refundAmount, feeAmount, weightFee, packagingFee, shippingFee, platformFee, slaDays});

    // Display Amounts
    document.getElementById('cancel-total-paid').textContent = '₹' + parseFloat(totalPaid).toLocaleString('en-IN', {minimumFractionDigits: 2});
    document.getElementById('cancel-modal-amount').textContent = '₹' + parseFloat(refundAmount).toLocaleString('en-IN', {minimumFractionDigits: 2});

    // Dynamic SLA Days display
    const slaTextEl = document.getElementById('cancel-sla-text');
    const slaDaysInt = parseInt(slaDays, 10);
    if (!isNaN(slaDaysInt) && slaDaysInt > 0) {
        slaTextEl.innerHTML = `Your refund will be processed and credited within <strong class="text-[#F25996] font-bold">${slaDaysInt} ${slaDaysInt === 1 ? 'business day' : 'business days'}</strong> to your selected refund method.`;
    } else {
        slaTextEl.innerHTML = `Your refund will be processed and credited within <strong class="text-[#F25996] font-bold">1-2 business days</strong> to your selected refund method.`;
    }
    
    // Display Collected Fees
    const feesSection = document.getElementById('cancel-collected-fees-section');
    const feesList = document.getElementById('cancel-collected-fees-list');
    feesList.innerHTML = '';
    
    let orderFeesTotal = 0;
    let hasOrderFees = false;
    
    if (parseFloat(weightFee) > 0) {
        feesList.innerHTML += `<div class="flex justify-between w-full text-gray-600"><span>Weight Fee:</span><span class="font-bold text-red-600">+₹${parseFloat(weightFee).toFixed(2)}</span></div>`;
        orderFeesTotal += parseFloat(weightFee);
        hasOrderFees = true;
    }
    if (parseFloat(packagingFee) > 0) {
        feesList.innerHTML += `<div class="flex justify-between w-full text-gray-600"><span>Packaging Fee:</span><span class="font-bold text-red-600">+₹${parseFloat(packagingFee).toFixed(2)}</span></div>`;
        orderFeesTotal += parseFloat(packagingFee);
        hasOrderFees = true;
    }
    if (parseFloat(shippingFee) > 0) {
        feesList.innerHTML += `<div class="flex justify-between w-full text-gray-600"><span>Shipping Fee:</span><span class="font-bold text-red-600">+₹${parseFloat(shippingFee).toFixed(2)}</span></div>`;
        orderFeesTotal += parseFloat(shippingFee);
        hasOrderFees = true;
    }
    if (parseFloat(platformFee) > 0) {
        feesList.innerHTML += `<div class="flex justify-between w-full text-gray-600"><span>Platform Fee:</span><span class="font-bold text-red-600">+₹${parseFloat(platformFee).toFixed(2)}</span></div>`;
        orderFeesTotal += parseFloat(platformFee);
        hasOrderFees = true;
    }
    
    if (hasOrderFees) {
        feesList.classList.remove('hidden');
    } else {
        feesList.classList.add('hidden');
    }
    
    let itemsSubtotal = parseFloat(totalPaid) - orderFeesTotal;
    document.getElementById('cancel-items-subtotal').textContent = '₹' + itemsSubtotal.toLocaleString('en-IN', {minimumFractionDigits: 2});
    
    // Deductions Section
    const deductionRow = document.getElementById('cancel-deduction-row');
    let totalDeduction = parseFloat(totalPaid) - parseFloat(refundAmount);
    if (totalDeduction > 0) {
        deductionRow.classList.remove('hidden');
        document.getElementById('cancel-deduction-amount').textContent = '-₹' + totalDeduction.toLocaleString('en-IN', {minimumFractionDigits: 2});
    } else {
        deductionRow.classList.add('hidden');
    }

    const rSelect = document.getElementById('cancel-reason-select');
    rSelect.value = '';
    if (typeof rSelect.resetCustomSelect === 'function') rSelect.resetCustomSelect();
    document.getElementById('cancel-reason-other-wrap').classList.add('hidden');
    document.getElementById('cancel-reason-text').value          = '';
    
    const mSelect = document.getElementById('refund-method-select');
    mSelect.value = '';
    if (typeof mSelect.resetCustomSelect === 'function') mSelect.resetCustomSelect();
    document.getElementById('refund-details-wrap').classList.add('hidden');
    document.getElementById('refund-details-input').value        = '';
    
    document.getElementById('cancel-refund-modal').classList.remove('hidden');
    document.body.style.overflow = 'hidden';
}
function closeCancelModal() {
    document.getElementById('cancel-refund-modal').classList.add('hidden');
    document.body.style.overflow = '';
}

function handleReasonSelect(sel) {
    const wrap = document.getElementById('cancel-reason-other-wrap');
    wrap.classList.toggle('hidden', sel.value !== 'other');
}

function handleRefundMethodSelect(sel) {
    const wrap = document.getElementById('refund-details-wrap');
    const input = document.getElementById('refund-details-input');
    const label = document.getElementById('refund-details-label');
    const hintText = document.getElementById('refund-details-hint-text');
    
    if (sel.value) {
        wrap.classList.remove('hidden');
        input.required = true;
        if (sel.value === 'Phone Number') {
            label.innerHTML = 'Phone Number <span class="text-red-500">*</span>';
            input.placeholder = 'Enter 10 digit phone number';
            input.pattern = '\\d{10}';
            input.type = 'tel';
            input.oninput = function() { this.value = this.value.replace(/[^0-9]/g, '').slice(0, 10); };
            if (hintText) hintText.textContent = 'The refund amount will be sent to this phone number (GPay / PhonePe / Paytm). Please make sure it is linked to a UPI account.';
        } else {
            label.innerHTML = 'UPI ID <span class="text-red-500">*</span>';
            input.placeholder = 'e.g. username@upi';
            input.removeAttribute('pattern');
            input.type = 'text';
            input.oninput = null;
            if (hintText) hintText.textContent = 'The refund amount will be sent directly to this UPI ID. Please double-check it is correct.';
        }
    } else {
        wrap.classList.add('hidden');
        input.required = false;
        input.value = '';
    }
}

document.getElementById('cancel-reason-text')?.addEventListener('input', function() {
    document.getElementById('reason-char-count').textContent = this.value.length;
});

async function submitCancellationRequest(e) {
    e.preventDefault();

    const selectEl = document.getElementById('cancel-reason-select');
    const textEl   = document.getElementById('cancel-reason-text');
    let reason     = selectEl.value === 'other' ? textEl.value.trim() : selectEl.value;

    const refundMethodSelect = document.getElementById('refund-method-select');
    const refundDetailsInput = document.getElementById('refund-details-input');
    let refundMethod = refundMethodSelect.value;
    let refundDetails = refundDetailsInput.value.trim();

    if (!reason || reason.length < 5) {
        showToast('Please select or enter a reason (min 5 characters).', 'error');
        return;
    }
    
    if (!refundMethod) {
        showToast('Please select a refund method.', 'error');
        return;
    }
    
    if (!refundDetails) {
        showToast('Please enter your refund details.', 'error');
        return;
    }
    
    if (refundMethod === 'Phone Number' && !/^\d{10}$/.test(refundDetails)) {
        showToast('Please enter a valid 10-digit phone number.', 'error');
        return;
    }

    const btn     = document.getElementById('cancel-submit-btn');
    const label   = document.getElementById('cancel-submit-label');
    const spinner = document.getElementById('cancel-submit-spinner');

    btn.disabled    = true;
    label.textContent = 'Submitting…';
    spinner.classList.remove('hidden');

    try {
        const res = await fetch('<?= BASE_URL ?>/dashboard/orders/request-refund', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
            body: JSON.stringify({
                order_id:          _cancelOrderId,
                reason:            reason,
                cancellation_type: 'full',
                refund_method:     refundMethod,
                refund_details:    refundDetails
            }),
        });

        const data = await res.json();

        if (data.success) {
            showToast(data.message, 'success');
            closeCancelModal();
            setTimeout(() => location.reload(), 1800);
        } else {
            showToast(data.message || 'Something went wrong.', 'error');
            btn.disabled = false;
            label.textContent = 'Submit Cancellation';
            spinner.classList.add('hidden');
        }
    } catch (err) {
        showToast('Network error. Please try again.', 'error');
        btn.disabled = false;
        label.textContent = 'Submit Cancellation';
        spinner.classList.add('hidden');
    }
}

// ── Toast ─────────────────────────────────────────────────────────────────────
function showToast(message, type = 'success') {
    const container = document.getElementById('toast-container');
    const colors    = type === 'success'
        ? 'bg-emerald-600 text-white'
        : 'bg-red-600 text-white';

    const toast = document.createElement('div');
    toast.className = `pointer-events-auto px-5 py-3 rounded-xl shadow-lg text-sm font-semibold flex items-center gap-2 ${colors} transform translate-y-4 opacity-0 transition-all duration-300`;
    toast.innerHTML = (type === 'success'
        ? '<svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>'
        : '<svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01M21 12A9 9 0 113 12a9 9 0 0118 0z"/></svg>'
    ) + `<span>${message}</span>`;

    container.appendChild(toast);
    requestAnimationFrame(() => {
        toast.classList.remove('translate-y-4', 'opacity-0');
    });

    setTimeout(() => {
        toast.classList.add('opacity-0', 'translate-y-4');
        setTimeout(() => toast.remove(), 300);
    }, 4000);
}

// ── Custom Pink Dropdown Initializer ──────────────────────────────────────────
function initOrdersCustomPinkSelects() {
    const selects = document.querySelectorAll('select.custom-select-pink');
    
    selects.forEach(function (sel) {
        if (sel.dataset.customInit) return;
        sel.dataset.customInit = '1';

        // Hide native select
        sel.style.display = 'none';

        // Wrapper
        const wrapper = document.createElement('div');
        wrapper.className = 'custom-select-pink-wrapper';
        sel.parentNode.insertBefore(wrapper, sel);
        wrapper.appendChild(sel);

        // Trigger Button
        const trigger = document.createElement('button');
        trigger.type = 'button';
        trigger.className = 'custom-select-pink-trigger';

        const label = document.createElement('span');
        label.className = 'custom-select-pink-label';
        const selectedOption = sel.options[sel.selectedIndex] || sel.options[0];
        label.textContent = selectedOption ? selectedOption.text : 'Select Option';
        trigger.appendChild(label);

        // Chevron SVG Arrow
        const arrow = document.createElementNS('http://www.w3.org/2000/svg', 'svg');
        arrow.setAttribute('class', 'custom-select-pink-arrow');
        arrow.setAttribute('viewBox', '0 0 20 20');
        arrow.setAttribute('fill', 'none');
        arrow.setAttribute('stroke', 'currentColor');
        arrow.setAttribute('stroke-width', '2.2');
        arrow.setAttribute('stroke-linecap', 'round');
        arrow.setAttribute('stroke-linejoin', 'round');
        const path = document.createElementNS('http://www.w3.org/2000/svg', 'path');
        path.setAttribute('d', 'M5 7.5l5 5 5-5');
        arrow.appendChild(path);
        trigger.appendChild(arrow);

        wrapper.appendChild(trigger);

        // Menu
        const menu = document.createElement('div');
        menu.className = 'custom-select-pink-menu';

        Array.from(sel.options).forEach(function (opt) {
            const item = document.createElement('div');
            item.className = 'custom-select-pink-item' + (opt.selected ? ' selected' : '');
            item.textContent = opt.text;

            item.addEventListener('click', function (e) {
                e.stopPropagation();
                sel.value = opt.value;
                label.textContent = opt.text;

                menu.querySelectorAll('.custom-select-pink-item').forEach(function (el) {
                    el.classList.remove('selected');
                });
                item.classList.add('selected');

                trigger.classList.remove('open');
                menu.classList.remove('open');

                // Trigger change event and direct onchange attribute
                sel.dispatchEvent(new Event('change'));
                if (sel.id === 'cancel-reason-select' && typeof handleReasonSelect === 'function') {
                    handleReasonSelect(sel);
                } else if (sel.id === 'refund-method-select' && typeof handleRefundMethodSelect === 'function') {
                    handleRefundMethodSelect(sel);
                }
            });

            menu.appendChild(item);
        });

        wrapper.appendChild(menu);

        // Reset Method attached to DOM element
        sel.resetCustomSelect = function() {
            const defaultOpt = sel.options[0];
            label.textContent = defaultOpt ? defaultOpt.text : 'Select Option';
            menu.querySelectorAll('.custom-select-pink-item').forEach(function (el, idx) {
                if (idx === 0) {
                    el.classList.add('selected');
                } else {
                    el.classList.remove('selected');
                }
            });
            trigger.classList.remove('open');
            menu.classList.remove('open');
        };

        // Toggle click
        trigger.addEventListener('click', function (e) {
            e.preventDefault();
            e.stopPropagation();

            const isOpen = trigger.classList.contains('open');

            document.querySelectorAll('.custom-select-pink-trigger.open').forEach(function (t) {
                t.classList.remove('open');
                const m = t.parentElement ? t.parentElement.querySelector('.custom-select-pink-menu') : null;
                if (m) m.classList.remove('open');
            });

            if (!isOpen) {
                trigger.classList.add('open');
                menu.classList.add('open');
            }
        });
    });

    document.addEventListener('click', function () {
        document.querySelectorAll('.custom-select-pink-trigger.open').forEach(function (t) {
            t.classList.remove('open');
            const m = t.parentElement ? t.parentElement.querySelector('.custom-select-pink-menu') : null;
            if (m) m.classList.remove('open');
        });
    });

    document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape') {
            document.querySelectorAll('.custom-select-pink-trigger.open').forEach(function (t) {
                t.classList.remove('open');
                const m = t.parentElement ? t.parentElement.querySelector('.custom-select-pink-menu') : null;
                if (m) m.classList.remove('open');
            });
        }
    });
}

document.addEventListener('DOMContentLoaded', function() {
    initOrdersCustomPinkSelects();
});
</script>

<!-- Custom Pink Select Styles (Matching Reference Image) -->
<style>
    .custom-select-pink-wrapper {
        position: relative;
        width: 100%;
    }
    .custom-select-pink-trigger {
        width: 100%;
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 0.75rem;
        padding: 0.625rem 1rem;
        background-color: #ffffff;
        border: 1.5px solid #FDBFDD;
        border-radius: 0.875rem;
        font-size: 0.875rem;
        font-weight: 500;
        color: #374151;
        cursor: pointer;
        outline: none;
        user-select: none;
        transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
        box-shadow: 0 1px 2px rgba(0, 0, 0, 0.03);
    }
    .custom-select-pink-trigger:hover {
        border-color: #F25996;
    }
    .custom-select-pink-trigger:focus,
    .custom-select-pink-trigger.open {
        border-color: #F25996 !important;
        box-shadow: 0 0 0 3px rgba(242, 89, 150, 0.2) !important;
    }
    .custom-select-pink-label {
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
        text-align: left;
        flex: 1;
    }
    .custom-select-pink-arrow {
        width: 1.05rem;
        height: 1.05rem;
        color: #F25996;
        transition: transform 0.25s cubic-bezier(0.4, 0, 0.2, 1);
        flex-shrink: 0;
        pointer-events: none;
    }
    .custom-select-pink-trigger.open .custom-select-pink-arrow {
        transform: rotate(180deg);
    }
    .custom-select-pink-menu {
        position: absolute;
        top: calc(100% + 6px);
        left: 0;
        right: 0;
        min-width: 100%;
        background: #ffffff;
        border: 1.5px solid #FDBFDD;
        border-radius: 1rem;
        box-shadow: 0 12px 30px rgba(242, 89, 150, 0.15), 0 4px 10px rgba(0,0,0,0.05);
        padding: 6px;
        z-index: 100;
        max-height: 220px;
        overflow-y: auto;
        display: none;
        animation: cfFadeInOrders 0.15s ease-out;
    }
    .custom-select-pink-menu.open {
        display: block;
    }
    .custom-select-pink-menu::-webkit-scrollbar {
        width: 4px;
    }
    .custom-select-pink-menu::-webkit-scrollbar-track {
        background: transparent;
    }
    .custom-select-pink-menu::-webkit-scrollbar-thumb {
        background: #F25996;
        border-radius: 4px;
    }
    .custom-select-pink-item {
        padding: 0.625rem 0.875rem;
        font-size: 0.875rem;
        border-radius: 0.625rem;
        cursor: pointer;
        color: #374151;
        font-weight: 500;
        transition: all 0.15s ease;
        margin-bottom: 2px;
        line-height: 1.4;
    }
    .custom-select-pink-item:last-child {
        margin-bottom: 0;
    }
    .custom-select-pink-item:hover {
        background-color: #fdf2f7;
        color: #F25996;
        font-weight: 600;
    }
    .custom-select-pink-item.selected {
        background-color: #F25996 !important;
        color: #ffffff !important;
        font-weight: 600;
    }
    @keyframes cfFadeInOrders {
        from { opacity: 0; transform: translateY(-4px); }
        to { opacity: 1; transform: translateY(0); }
    }
</style>

<!-- ========================================================================= -->
<!-- Global Order Review Activation Modal for Orders View                     -->
<!-- ========================================================================= -->
<div id="orderReviewModal" class="fixed inset-0 bg-gray-900/60 backdrop-blur-sm z-[200] hidden opacity-0 transition-opacity duration-300 flex items-center justify-center p-3 sm:p-4">
    <div class="bg-white rounded-2xl sm:rounded-3xl shadow-2xl w-full max-w-lg transform scale-95 transition-transform duration-300 max-h-[92vh] overflow-hidden flex flex-col border border-gray-100">
        <!-- Modal Header -->
        <div class="flex justify-between items-center p-4 sm:p-5 border-b border-gray-100 bg-[#fafafa]">
            <div class="flex items-center gap-2.5">
                <span class="w-8 h-8 rounded-full bg-pink-50 text-[#F25996] flex items-center justify-center font-bold text-sm border border-pink-100">⭐</span>
                <div>
                    <h2 class="text-base sm:text-lg font-black text-gray-900 leading-tight" id="ormTitle">Write a Review</h2>
                    <p class="text-[11px] text-gray-500 font-medium" id="ormSubtitle">Share your experience</p>
                </div>
            </div>
            <button onclick="closeOrderReviewModal()" class="text-gray-400 hover:text-gray-600 bg-white hover:bg-gray-100 rounded-full p-1.5 transition-colors border border-gray-200 shadow-sm">
                <svg class="w-4 h-4 sm:w-5 sm:h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
            </button>
        </div>

        <!-- Product Preview Card -->
        <div class="px-4 py-3 sm:px-6 bg-pink-50/40 border-b border-pink-100/60 flex items-center gap-3">
            <div class="w-12 h-12 rounded-xl bg-white border border-gray-200 overflow-hidden shrink-0 flex items-center justify-center">
                <img id="ormItemImg" src="" alt="" class="w-full h-full object-cover hidden">
                <span id="ormItemFallback" class="text-xl">✂️</span>
            </div>
            <div class="min-w-0 flex-1">
                <div class="flex items-center gap-1.5">
                    <span id="ormItemBadge" class="hidden text-[10px] font-bold text-[#F25996] bg-white border border-pink-200 px-2 py-0.5 rounded-full">Custom Garment</span>
                </div>
                <h4 id="ormItemName" class="text-xs sm:text-sm font-bold text-gray-900 truncate mt-0.5">Product Name</h4>
            </div>
        </div>

        <!-- Form Body -->
        <form id="orderReviewForm" onsubmit="handleOrderReviewSubmit(event)" class="p-4 sm:p-6 overflow-y-auto space-y-4">
            <input type="hidden" name="product_id" id="ormProductId">
            <input type="hidden" name="customization_id" id="ormCustomizationId">
            <input type="hidden" name="order_id" id="ormOrderId">
            
            <!-- Star Rating -->
            <div class="flex flex-col items-center py-3 bg-gray-50/90 rounded-2xl border border-gray-100">
                <p class="text-xs sm:text-sm font-bold text-gray-800 mb-1.5" id="ormRatingPrompt">How would you rate this product?</p>
                <div class="flex items-center gap-1.5 sm:gap-2" id="ormStarRating">
                    <?php for($i=1; $i<=5; $i++): ?>
                        <button type="button" data-rating="<?= $i ?>" onclick="setOrmRating(<?= $i ?>)" onmouseover="hoverOrmRating(<?= $i ?>)" onmouseout="resetOrmRatingHover()" class="orm-star-btn text-gray-300 hover:text-yellow-400 focus:outline-none transition-colors p-1">
                            <svg class="w-8 h-8 fill-current" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/></svg>
                        </button>
                    <?php endfor; ?>
                </div>
                <input type="hidden" name="rating" id="ormRatingInput" value="0">
                <span id="ormRatingText" class="text-xs font-semibold text-gray-500 mt-1">Tap a star to rate</span>
            </div>

            <!-- Review Text -->
            <div>
                <label class="block text-xs sm:text-sm font-bold text-gray-700 mb-1.5">Your Feedback <span class="text-red-500">*</span></label>
                <textarea name="review_text" id="ormReviewText" rows="3" required class="w-full px-3.5 py-2.5 bg-gray-50 border border-gray-200 rounded-xl focus:ring-2 focus:ring-[#F25996] focus:border-transparent text-xs sm:text-sm resize-none" placeholder="Share your experience regarding the quality, fabric texture, stitching finish, and fit..."></textarea>
            </div>

            <!-- Photos -->
            <div>
                <label class="block text-xs sm:text-sm font-bold text-gray-700 mb-1.5">Customer Photos (Optional)</label>
                <input type="file" id="ormPhotosInput" name="photos[]" multiple accept="image/*" onchange="previewOrmPhotos(this)" class="block w-full text-xs sm:text-sm text-gray-500 file:mr-3 file:py-1.5 file:px-3 file:rounded-xl file:border-0 file:text-xs file:font-bold file:bg-[#F25996]/10 file:text-[#F25996] hover:file:bg-[#F25996]/20 transition cursor-pointer">
                <div id="ormPhotosPreview" class="mt-2.5 flex flex-wrap gap-2 hidden"></div>
            </div>

            <button type="submit" id="ormSubmitBtn" class="w-full py-3 bg-[#F25996] hover:bg-[#d8407d] text-white font-bold rounded-xl transition-all shadow-md text-xs sm:text-sm flex items-center justify-center gap-2">
                <span>Submit Review</span>
            </button>
        </form>
    </div>
</div>

<script>
let ormCurrentRating = 0;
let ormActiveItemData = null;

const ratingLabels = {
    1: 'Poor — Unsatisfied',
    2: 'Fair — Below expectations',
    3: 'Good — Average quality',
    4: 'Very Good — Satisfied',
    5: 'Excellent — Highly recommended!'
};

function activateOrderReviewModal(data) {
    ormActiveItemData = data;
    document.getElementById('ormProductId').value = data.productId;
    document.getElementById('ormCustomizationId').value = data.customizationId || '';
    document.getElementById('ormOrderId').value = data.orderId;
    
    // Set titles
    const titleEl = document.getElementById('ormTitle');
    const subtitleEl = document.getElementById('ormSubtitle');
    const badgeEl = document.getElementById('ormItemBadge');
    const nameEl = document.getElementById('ormItemName');
    const promptEl = document.getElementById('ormRatingPrompt');
    const imgEl = document.getElementById('ormItemImg');
    const fallbackEl = document.getElementById('ormItemFallback');
    
    subtitleEl.innerText = 'Order #' + data.orderNumber;
    
    if (data.isCustom) {
        titleEl.innerText = 'Review Customized Product';
        badgeEl.classList.remove('hidden');
        badgeEl.innerText = '✂️ Custom ' + (data.garmentType || 'Garment');
        promptEl.innerText = 'How was the custom stitching & fabric quality?';
    } else {
        titleEl.innerText = 'Write a Review';
        badgeEl.classList.add('hidden');
        promptEl.innerText = 'How would you rate this product?';
    }
    
    nameEl.innerText = data.productName;
    if (data.image && data.image.length > 5) {
        imgEl.src = data.image;
        imgEl.classList.remove('hidden');
        fallbackEl.classList.add('hidden');
    } else {
        imgEl.classList.add('hidden');
        fallbackEl.classList.remove('hidden');
    }

    // Reset Form
    document.getElementById('orderReviewForm').reset();
    const preview = document.getElementById('ormPhotosPreview');
    if (preview) {
        preview.innerHTML = '';
        preview.classList.add('hidden');
    }
    setOrmRating(0);
    
    const modal = document.getElementById('orderReviewModal');
    modal.classList.remove('hidden');
    setTimeout(() => {
        modal.classList.remove('opacity-0');
        modal.firstElementChild.classList.remove('scale-95');
    }, 10);
}

function closeOrderReviewModal() {
    const modal = document.getElementById('orderReviewModal');
    modal.classList.add('opacity-0');
    modal.firstElementChild.classList.add('scale-95');
    setTimeout(() => {
        modal.classList.add('hidden');
    }, 300);
}

function setOrmRating(rating) {
    ormCurrentRating = rating;
    document.getElementById('ormRatingInput').value = rating;
    updateOrmStars(rating);
    const txt = document.getElementById('ormRatingText');
    if (txt) {
        txt.innerText = ratingLabels[rating] || 'Tap a star to rate';
        txt.className = rating > 0 ? 'text-xs font-bold text-[#F25996] mt-1' : 'text-xs font-semibold text-gray-500 mt-1';
    }
}

function hoverOrmRating(rating) {
    updateOrmStars(rating);
    const txt = document.getElementById('ormRatingText');
    if (txt && rating > 0) {
        txt.innerText = ratingLabels[rating] || '';
    }
}

function resetOrmRatingHover() {
    updateOrmStars(ormCurrentRating);
    const txt = document.getElementById('ormRatingText');
    if (txt) {
        txt.innerText = ratingLabels[ormCurrentRating] || 'Tap a star to rate';
        txt.className = ormCurrentRating > 0 ? 'text-xs font-bold text-[#F25996] mt-1' : 'text-xs font-semibold text-gray-500 mt-1';
    }
}

function updateOrmStars(rating) {
    const stars = document.querySelectorAll('.orm-star-btn');
    stars.forEach(star => {
        const val = parseInt(star.getAttribute('data-rating'));
        if (val <= rating) {
            star.classList.remove('text-gray-300');
            star.classList.add('text-yellow-400');
        } else {
            star.classList.remove('text-yellow-400');
            star.classList.add('text-gray-300');
        }
    });
}

function previewOrmPhotos(input) {
    const previewContainer = document.getElementById('ormPhotosPreview');
    previewContainer.innerHTML = '';
    if (!input.files || input.files.length === 0) {
        previewContainer.classList.add('hidden');
        return;
    }
    
    previewContainer.classList.remove('hidden');
    Array.from(input.files).forEach(file => {
        if (!file.type.startsWith('image/')) return;
        const reader = new FileReader();
        reader.onload = function(e) {
            const wrap = document.createElement('div');
            wrap.className = 'w-14 h-14 rounded-xl overflow-hidden border border-gray-200 shadow-sm relative';
            const img = document.createElement('img');
            img.src = e.target.result;
            img.className = 'w-full h-full object-cover';
            wrap.appendChild(img);
            previewContainer.appendChild(wrap);
        }
        reader.readAsDataURL(file);
    });
}

function handleOrderReviewSubmit(e) {
    e.preventDefault();
    if (ormCurrentRating === 0) {
        alert('Please choose a star rating (1 to 5 stars).');
        return;
    }
    
    const form = e.target;
    const formData = new FormData(form);
    const btn = document.getElementById('ormSubmitBtn');
    
    btn.disabled = true;
    btn.innerHTML = `<svg class="animate-spin w-4 h-4 mr-2" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path></svg> Submitting...`;
    
    fetch('<?= BASE_URL ?>/dashboard/reviews/submit', {
        method: 'POST',
        body: formData
    })
    .then(res => res.json())
    .then(data => {
        if(data.success) {
            // Update container in DOM
            if (ormActiveItemData) {
                const cid = ormActiveItemData.customizationId || '0';
                const contId = 'review-action-container-' + ormActiveItemData.orderId + '-' + ormActiveItemData.productId + '-' + cid;
                const container = document.getElementById(contId);
                if (container) {
                    const stars = '★'.repeat(ormCurrentRating);
                    container.innerHTML = `
                        <span class="px-2.5 py-1 bg-green-50 text-green-700 border border-green-200 rounded-lg text-xs font-bold inline-flex items-center gap-1 shadow-xs">
                            <svg class="w-3.5 h-3.5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                            <span>Reviewed</span>
                            <span class="text-amber-500 font-bold ml-0.5">${stars}</span>
                        </span>
                        <span class="text-[10px] text-amber-600 bg-amber-50 px-2 py-0.5 rounded-md border border-amber-200">Pending Verification</span>
                    `;
                }
            }
            alert(data.message || 'Review submitted successfully!');
            closeOrderReviewModal();
        } else {
            alert(data.message || 'Something went wrong.');
            btn.disabled = false;
            btn.innerHTML = '<span>Submit Review</span>';
        }
    })
    .catch(err => {
        console.error(err);
        alert('An error occurred while submitting your review.');
        btn.disabled = false;
        btn.innerHTML = '<span>Submit Review</span>';
    });
}
</script>

