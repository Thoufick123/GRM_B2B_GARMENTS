<?php
/**
 * @var string $title
 * @var array  $order
 * @var array  $items
 */

$statusColors = [
    'placed'           => 'bg-yellow-100 text-yellow-800 border border-yellow-200',
    'packed'           => 'bg-orange-100 text-orange-800 border border-orange-200',
    'shipped'          => 'bg-blue-100 text-blue-800 border border-blue-200',
    'out_for_delivery' => 'bg-indigo-100 text-indigo-800 border border-indigo-200',
    'delivered'        => 'bg-green-100 text-green-800 border border-green-200',
    'cancelled'        => 'bg-red-100 text-red-800 border border-red-200',
    'stitching'        => 'bg-blue-100 text-blue-800 border border-blue-200',
    'in_production'    => 'bg-cyan-100 text-cyan-800 border border-cyan-200',
    'quality_check'    => 'bg-teal-100 text-teal-800 border border-teal-200',
    'pending'          => 'bg-gray-100 text-gray-700 border border-gray-200',
];
$statusColor  = $statusColors[$order['status']] ?? 'bg-gray-100 text-gray-700 border border-gray-200';
$statusLabels = [
    'placed'           => 'Placed',
    'packed'           => 'Packed',
    'shipped'          => 'Shipped',
    'out_for_delivery' => 'Out for Delivery',
    'delivered'        => 'Delivered',
    'cancelled'        => 'Cancelled',
    'stitching'        => 'Stitching',
    'in_production'    => 'In Production',
    'quality_check'    => 'Quality Check',
    'pending'          => 'Pending',
];
$statusLabel = $statusLabels[$order['status']] ?? ucwords(str_replace('_', ' ', $order['status']));
$grandTotal = ($order['grand_total'] ?? 0) > 0 ? $order['grand_total'] : $order['total_amount'];
?>

<div class="bg-[#fafafa] min-h-screen flex flex-col md:flex-row relative">
    <?php include __DIR__ . '/_sidebar.php'; ?>
    <div class="flex-1 p-4 md:p-10 relative overflow-hidden">
        <div class="max-w-7xl mx-auto w-full">

            <div class="mb-5 flex justify-between items-center">
                <h1 class="text-xl font-bold text-gray-900 tracking-tight">Order Details &mdash; <?= htmlspecialchars($order['order_number']) ?></h1>
                <a href="<?= BASE_URL ?>/dashboard/orders"
                   class="inline-flex items-center text-sm text-gray-600 hover:text-gray-900 font-medium group">
                    <svg class="w-4 h-4 mr-1 transition-transform group-hover:-translate-x-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                    </svg>
                    Back to My Orders
                </a>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-4 gap-6">

                <div class="lg:col-span-3 space-y-6">

                    <?php if (!empty($modification)): 
                        $diff = (float)$modification['revised_total'] - (float)$modification['original_total'];
                        $isPending = ($modification['extra_payment_status'] ?? 'pending') === 'pending';
                    ?>
                    <div class="p-5 rounded-2xl border bg-blue-50 border-blue-200 text-blue-900 shadow-sm space-y-3">
                        <div class="flex items-center justify-between gap-3 flex-wrap">
                            <div class="flex items-center gap-3">
                                <span class="p-2 bg-blue-100 text-blue-700 rounded-xl">⚡</span>
                                <div>
                                    <h3 class="font-bold text-base">Order Bill Modified by Admin</h3>
                                    <p class="text-xs text-blue-700">Modified on <?= date('M j, Y, h:i A', strtotime($modification['created_at'])) ?></p>
                                </div>
                            </div>
                            <span class="px-2.5 py-1 rounded-full text-xs font-bold bg-blue-100 text-blue-800 uppercase tracking-wide">
                                <?= htmlspecialchars(ucfirst($modification['status'])) ?>
                            </span>
                        </div>
                        <?php if (!empty($modification['reason'])): ?>
                            <p class="text-xs text-blue-800 bg-blue-100/70 p-2.5 rounded-lg border border-blue-200 font-medium">
                                <strong>Reason:</strong> <?= htmlspecialchars($modification['reason']) ?>
                            </p>
                        <?php endif; ?>

                        <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 pt-2 border-t border-blue-200/80 text-xs">
                            <div class="bg-white/90 p-2.5 rounded-xl border border-blue-100">
                                <span class="text-gray-500 block text-[10px] font-bold uppercase">Original Total (Paid)</span>
                                <span class="font-bold text-gray-800 text-sm">₹<?= number_format($modification['original_total'], 2) ?></span>
                            </div>
                            <div class="bg-white/90 p-2.5 rounded-xl border border-blue-100">
                                <span class="text-gray-500 block text-[10px] font-bold uppercase">Revised Total</span>
                                <span class="font-black text-indigo-950 text-sm">₹<?= number_format($modification['revised_total'], 2) ?></span>
                            </div>
                            <div class="bg-white/90 p-2.5 rounded-xl border border-blue-100">
                                <span class="text-gray-500 block text-[10px] font-bold uppercase"><?= $diff >= 0 ? 'Balance to Pay' : 'Refund Due' ?></span>
                                <span class="font-bold text-sm <?= $diff > 0 ? ($isPending ? 'text-red-600' : 'text-green-600') : 'text-green-600' ?>">
                                    <?= $diff > 0 ? '+₹' . number_format($diff, 2) . ' (' . ($isPending ? 'Pending' : 'Paid') . ')' : ($diff < 0 ? '-₹' . number_format(abs($diff), 2) : '₹0.00') ?>
                                </span>
                            </div>
                        </div>

                        <?php if ($diff > 0 && $isPending): ?>
                            <div class="flex items-center justify-between gap-3 pt-2 border-t border-blue-200/80 flex-wrap">
                                <p class="text-xs font-bold text-red-600">Balance Due to Pay for Replaced Items: +₹<?= number_format($diff, 2) ?></p>
                                <a href="<?= BASE_URL ?>/order-modifications/review/<?= $modification['id'] ?>" class="px-3.5 py-1.5 bg-red-600 hover:bg-red-700 text-white rounded-lg text-xs font-bold shadow-xs transition inline-flex items-center gap-1.5">
                                    💳 Pay / Review Balance (+₹<?= number_format($diff, 2) ?>)
                                </a>
                            </div>
                        <?php endif; ?>
                    </div>
                    <?php endif; ?>

                    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
                        <div class="px-6 py-5 border-b border-gray-100 flex justify-between items-center">
                            <h2 class="text-lg font-bold text-gray-900">Order Items</h2>
                            <span class="px-3 py-1 bg-<?= $order['payment_status'] === 'paid' ? 'green' : 'amber' ?>-100 text-<?= $order['payment_status'] === 'paid' ? 'green' : 'amber' ?>-800 rounded-full text-xs font-bold uppercase tracking-wide">
                                <?= htmlspecialchars(strtoupper($order['payment_status'])) ?>
                            </span>
                        </div>
                        <div class="overflow-x-auto">
                            <table class="w-full text-sm text-left text-gray-600">
                                <thead class="bg-gray-50 text-xs uppercase text-gray-500 font-semibold border-b border-gray-100">
                                    <tr>
                                        <th class="px-6 py-4">Product</th>
                                        <th class="px-6 py-4">Price</th>
                                        <th class="px-6 py-4 text-center">Qty</th>
                                        <th class="px-6 py-4 text-right">Total</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-50">
                                    <?php 
                                    $calculatedItemsSubtotal = 0;
                                    $modItemByOI = [];
                                    $removedModItems = [];
                                    if (!empty($modItems)) {
                                        foreach ($modItems as $mi) {
                                            if ($mi['change_type'] === 'removed') {
                                                $removedModItems[] = $mi;
                                            } elseif (!empty($mi['order_item_id'])) {
                                                $modItemByOI[$mi['order_item_id']] = $mi;
                                            } elseif (!empty($mi['product_id'])) {
                                                $modItemByOI['p_' . $mi['product_id']] = $mi;
                                            }
                                        }
                                    }
                                    foreach ($items as $item):
                                        $modItem = $modItemByOI[$item['id']] ?? $modItemByOI['p_' . ($item['product_id'] ?? 0)] ?? null;
                                        $isReplaced = ($modItem && $modItem['change_type'] === 'replaced');
                                        $isQtyChanged = ($modItem && $modItem['change_type'] === 'qty_changed');
                                        $isPriceChanged = ($modItem && $modItem['change_type'] === 'price_changed');
                                        $isAdded = ($modItem && $modItem['change_type'] === 'added');
                                        $itemTotal = (float)$item['unit_price'] * (int)$item['quantity'];
                                        $calculatedItemsSubtotal += $itemTotal;
                                        $rawImg = $item['image'] ?? '';
                                        if (empty($rawImg)) { $imgUrl = null; }
                                        elseif (str_starts_with($rawImg, 'http://') || str_starts_with($rawImg, 'https://')) { $imgUrl = $rawImg; }
                                        else { $imgUrl = BASE_URL . '/' . ltrim($rawImg, '/'); }
                                    ?>
                                    <tr class="hover:bg-gray-50/70 transition-colors <?= $isReplaced ? 'bg-blue-50/20' : '' ?>">
                                        <td class="px-6 py-5">
                                            <div class="flex items-start gap-4">
                                                <div class="w-14 h-14 flex-shrink-0 rounded-xl border border-gray-100 overflow-hidden bg-gray-50 mt-0.5">
                                                    <?php if ($imgUrl): ?>
                                                        <img src="<?= htmlspecialchars($imgUrl) ?>" alt="<?= htmlspecialchars($item['name']) ?>" class="w-full h-full object-cover">
                                                    <?php else: ?>
                                                        <div class="w-full h-full flex items-center justify-center text-gray-300">
                                                            <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 24 24"><path d="M21 19V5c0-1.1-.9-2-2-2H5c-1.1 0-2 .9-2 2v14c0 1.1.9 2 2 2h14c1.1 0 2-.9 2-2zM8.5 13.5l2.5 3.01L14.5 12l4.5 6H5l3.5-4.5z"/></svg>
                                                        </div>
                                                    <?php endif; ?>
                                                </div>
                                                <div class="flex-1 min-w-0">
                                                    <div class="flex items-center gap-2 flex-wrap">
                                                        <p class="font-semibold text-gray-900 leading-tight"><?= htmlspecialchars($item['name']) ?></p>
                                                        <?php if ($isReplaced): ?>
                                                            <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold bg-blue-100 text-blue-700 border border-blue-200">
                                                                🔄 Replaced Product (Bill Modified)
                                                            </span>
                                                        <?php elseif ($isAdded): ?>
                                                            <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold bg-green-100 text-green-700 border border-green-200">
                                                                ✨ Added to Bill
                                                            </span>
                                                        <?php endif; ?>
                                                    </div>

                                                    <div class="flex items-center gap-2 mt-1 flex-wrap">
                                                        <?php if (!empty($item['garment_type']) || !empty($item['customization_id'])): ?>
                                                            <span class="inline-block text-xs font-bold text-[#F25996] bg-pink-50 border border-pink-200 px-2 py-0.5 rounded-full">
                                                                ✂️ Custom <?= htmlspecialchars($item['garment_type'] ?? 'Garment') ?><?= !empty($item['program_name']) ? ' — ' . htmlspecialchars($item['program_name']) : '' ?>
                                                            </span>
                                                        <?php endif; ?>
                                                        <?php if (!empty($item['variant_name'])): ?>
                                                            <span class="inline-block text-xs font-medium text-[#F25996] bg-pink-50 border border-pink-200 px-2 py-0.5 rounded-full">
                                                                Variant: <?= htmlspecialchars($item['variant_name']) ?>
                                                            </span>
                                                        <?php endif; ?>
                                                        <?php if (!empty($item['category_hsn_code'])): ?>
                                                            <span class="inline-block text-xs font-mono font-bold text-[#F25996] bg-pink-50 border border-pink-200 px-2 py-0.5 rounded-full">
                                                                HSN: <?= htmlspecialchars($item['category_hsn_code']) ?>
                                                            </span>
                                                        <?php endif; ?>
                                                    </div>
                                                    <?php if (!empty($item['weight'])): ?>
                                                        <p class="text-xs text-gray-400 mt-0.5"><?= floatval($item['weight']) ?> <?= htmlspecialchars($item['weight_unit'] ?? 'kg') ?></p>
                                                    <?php endif; ?>

                                                    <?php if ($isReplaced): 
                                                        $origPName = !empty($modItem['original_product_name']) ? $modItem['original_product_name'] : $item['name'];
                                                        $origVName = !empty($modItem['original_variant_name']) ? $modItem['original_variant_name'] : ($item['variant_name'] ?? '');
                                                        $origImg   = !empty($modItem['original_image']) ? $modItem['original_image'] : ($modItem['product_image'] ?? ($item['image'] ?? ''));
                                                        $repPName  = !empty($modItem['replacement_product_name']) ? $modItem['replacement_product_name'] : $item['name'];
                                                        $repVName  = !empty($modItem['replacement_variant_name']) ? $modItem['replacement_variant_name'] : ($item['variant_name'] ?? '');
                                                        $repImg    = !empty($modItem['replacement_image']) ? $modItem['replacement_image'] : ($item['image'] ?? '');
                                                        $itemDiff  = ($modItem['new_qty'] * $modItem['new_price']) - ($modItem['old_qty'] * $modItem['old_price']);
                                                    ?>
                                                        <div class="mt-2.5 p-3 bg-blue-50/80 border border-blue-200 rounded-xl text-xs space-y-2.5">
                                                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-2.5">
                                                                <!-- Customer First Order Product -->
                                                                <div class="bg-red-50/50 border border-red-200/80 rounded-lg p-2.5 flex items-center gap-2.5">
                                                                    <div class="w-12 h-12 bg-white rounded-md border border-red-200 shrink-0 overflow-hidden flex items-center justify-center">
                                                                        <?php if (!empty($origImg)): ?>
                                                                            <img src="<?= htmlspecialchars(get_image_url($origImg)) ?>" class="w-full h-full object-cover">
                                                                        <?php else: ?>
                                                                            <svg class="w-5 h-5 text-gray-300" fill="currentColor" viewBox="0 0 24 24"><path d="M21 19V5c0-1.1-.9-2-2-2H5c-1.1 0-2 .9-2 2v14c0 1.1.9 2 2 2h14c1.1 0 2-.9 2-2zM8.5 13.5l2.5 3.01L14.5 12l4.5 6H5l3.5-4.5z"/></svg>
                                                                        <?php endif; ?>
                                                                    </div>
                                                                    <div class="flex-1 min-w-0">
                                                                        <span class="text-[9px] font-extrabold uppercase tracking-wider text-red-700 block">⏮️ Customer First Order Product</span>
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
                                                                            <img src="<?= htmlspecialchars(get_image_url($repImg)) ?>" class="w-full h-full object-cover">
                                                                        <?php else: ?>
                                                                            <svg class="w-5 h-5 text-gray-300" fill="currentColor" viewBox="0 0 24 24"><path d="M21 19V5c0-1.1-.9-2-2-2H5c-1.1 0-2 .9-2 2v14c0 1.1.9 2 2 2h14c1.1 0 2-.9 2-2zM8.5 13.5l2.5 3.01L14.5 12l4.5 6H5l3.5-4.5z"/></svg>
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
                                                                <span class="px-2.5 py-1 bg-green-50 text-green-700 border border-green-200 rounded-lg text-xs font-bold inline-flex items-center gap-1 shadow-xs">
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
                                                                            image: '<?= htmlspecialchars($imgUrl ?? '', ENT_QUOTES) ?>'
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
                                        </td>
                                        <td class="px-6 py-5 font-medium text-gray-900 whitespace-nowrap">₹<?= number_format($item['unit_price'], 2) ?></td>
                                        <td class="px-6 py-5 text-center font-semibold text-gray-800"><?= (int)$item['quantity'] ?></td>
                                        <td class="px-6 py-5 text-right font-bold text-gray-900 whitespace-nowrap">₹<?= number_format($itemTotal, 2) ?></td>
                                    </tr>
                                    <?php endforeach; ?>

                                    <?php if (!empty($removedModItems)): ?>
                                        <?php foreach ($removedModItems as $remItem): ?>
                                        <tr class="bg-red-50/40 opacity-80">
                                            <td class="px-6 py-4">
                                                <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold bg-red-100 text-red-700 border border-red-200 mb-1">
                                                    ❌ Removed from Bill
                                                </span>
                                                <p class="font-semibold text-gray-700 line-through text-xs sm:text-sm">
                                                    <?= htmlspecialchars($remItem['original_product_name'] ?? 'Removed Product') ?><?= !empty($remItem['original_variant_name']) ? ' (' . htmlspecialchars($remItem['original_variant_name']) . ')' : '' ?>
                                                </p>
                                            </td>
                                            <td class="px-6 py-4 font-mono text-xs text-gray-500 line-through">₹<?= number_format($remItem['old_price'], 2) ?></td>
                                            <td class="px-6 py-4 text-center font-mono text-xs text-gray-500 line-through"><?= (int)$remItem['old_qty'] ?></td>
                                            <td class="px-6 py-4 text-right font-bold text-green-700 text-xs">Deducted (₹<?= number_format($remItem['old_qty'] * $remItem['old_price'], 2) ?>)</td>
                                        </tr>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>

                        <div class="p-6 bg-gradient-to-br from-gray-50 to-white border-t border-gray-100">
                            <h3 class="text-xs font-bold text-gray-500 uppercase tracking-wider mb-4">Payment Summary</h3>
                            <div class="space-y-2.5 text-sm">
                                <?php
                                $revisedTotal = (float)($order['grand_total'] ?? $order['total_amount']);
                                $otherFees = max(0, $revisedTotal - $calculatedItemsSubtotal);
                                ?>
                                <div class="flex justify-between items-center text-gray-600">
                                    <span><?= !empty($modification) ? 'Revised Items Subtotal' : 'Subtotal' ?></span>
                                    <span class="font-medium text-gray-800">₹<?= number_format($calculatedItemsSubtotal, 2) ?></span>
                                </div>
                                <?php if (!empty($modification)): ?>
                                    <?php if ($otherFees > 0): ?>
                                    <div class="flex justify-between items-center text-gray-600">
                                        <span>Other Fees (Shipping, Platform, Extra Charges, etc.)</span>
                                        <span class="font-medium text-gray-800">+₹<?= number_format($otherFees, 2) ?></span>
                                    </div>
                                    <?php endif; ?>
                                <?php else: ?>
                                    <?php
                                    $feeBreakdown = !empty($order['fee_breakdown']) ? json_decode($order['fee_breakdown'], true) : null;
                                    if (!empty($feeBreakdown)): 
                                        foreach ($feeBreakdown as $fee):
                                    ?>
                                        <div class="flex justify-between items-center text-gray-600">
                                            <span>
                                                <?= htmlspecialchars($fee['name']) ?>
                                                <?php if (!empty($fee['note'])): ?>
                                                    <span class="text-xs text-gray-400 ml-1">(<?= htmlspecialchars($fee['note']) ?>)</span>
                                                <?php endif; ?>
                                            </span>
                                            <span class="font-medium text-gray-800">₹<?= number_format($fee['amount'], 2) ?></span>
                                        </div>
                                    <?php 
                                        endforeach; 
                                    else: 
                                    ?>
                                        <?php if (($order['shipping_fee'] ?? 0) > 0): ?>
                                        <div class="flex justify-between items-center text-gray-600">
                                            <span>Shipping Fee</span>
                                            <span class="font-medium text-gray-800">₹<?= number_format($order['shipping_fee'], 2) ?></span>
                                        </div>
                                        <?php endif; ?>
                                        <?php if (($order['weight_fee'] ?? 0) > 0): ?>
                                        <div class="flex justify-between items-center text-gray-600">
                                            <span>Weight Fee</span>
                                            <span class="font-medium text-gray-800">₹<?= number_format($order['weight_fee'], 2) ?></span>
                                        </div>
                                        <?php endif; ?>
                                        <?php if (($order['platform_fee'] ?? 0) > 0): ?>
                                        <div class="flex justify-between items-center text-gray-600">
                                            <span>Platform Fee</span>
                                            <span class="font-medium text-gray-800">₹<?= number_format($order['platform_fee'], 2) ?></span>
                                        </div>
                                        <?php endif; ?>
                                        <?php if (($order['packaging_fee'] ?? 0) > 0): ?>
                                        <div class="flex justify-between items-center text-gray-600">
                                            <span>Packing Fee</span>
                                            <span class="font-medium text-gray-800">₹<?= number_format($order['packaging_fee'], 2) ?></span>
                                        </div>
                                        <?php endif; ?>
                                    <?php endif; ?>
                                <?php endif; ?>
                                <?php
                                    $unroundedDetailTotal = (float)$calculatedItemsSubtotal + (float)($order['shipping_fee'] ?? 0) + (float)($order['weight_fee'] ?? 0) + (float)($order['platform_fee'] ?? 0) + (float)($order['packaging_fee'] ?? 0);
                                    $finalDisplayTotal = (float)($revisedTotal > 0 ? $revisedTotal : round($unroundedDetailTotal));
                                    $detailRoundOff = round($finalDisplayTotal - $unroundedDetailTotal, 2);
                                ?>
                                <?php if ($detailRoundOff > 0 || abs($detailRoundOff) > 0.001): ?>
                                <div class="flex justify-between items-center text-gray-600">
                                    <span>Round Off</span>
                                    <span class="font-medium text-gray-800"><?= $detailRoundOff > 0 ? '+ ₹' . number_format($detailRoundOff, 2) : '₹0.00' ?></span>
                                </div>
                                <?php endif; ?>

                                <div class="border-t border-gray-200 pt-3 mt-1">
                                    <div class="flex justify-between items-center">
                                        <span class="text-base font-bold text-gray-900"><?= !empty($modification) ? 'Revised Grand Total' : 'Grand Total' ?></span>
                                        <span class="text-xl font-black text-[#1e1b4b]">₹<?= number_format($finalDisplayTotal, 2) ?></span>
                                    </div>
                                    <div class="text-xs text-blue-700 bg-blue-50/60 rounded px-2.5 py-1 mt-1.5 font-medium">
                                        <span class="text-[10px] uppercase font-bold text-gray-400 block">In Words:</span>
                                        <?= htmlspecialchars(\App\Services\InvoiceService::numberToWordsIndian($finalDisplayTotal)) ?>
                                    </div>
                                </div>

                                <?php if (!empty($modification)): 
                                    $origPaid = (float)$modification['original_total'];
                                    $diff = $revisedTotal - $origPaid;
                                ?>
                                <div class="bg-indigo-50/80 p-3.5 rounded-xl border border-indigo-100 mt-3 space-y-2 text-xs">
                                    <div class="flex justify-between text-gray-700">
                                        <span>Amount Already Paid (Old Bill):</span>
                                        <span class="font-semibold text-gray-900">₹<?= number_format($origPaid, 2) ?></span>
                                    </div>
                                    <div class="flex justify-between text-gray-700">
                                        <span>Revised Bill Total:</span>
                                        <span class="font-bold text-[#1e1b4b]">₹<?= number_format($revisedTotal, 2) ?></span>
                                    </div>
                                    <?php if ($diff > 0): ?>
                                        <div class="flex justify-between font-bold text-red-600 pt-1.5 border-t border-indigo-100 text-sm">
                                            <span>Balance Due (To Pay for Replaced Items):</span>
                                            <span>+₹<?= number_format($diff, 2) ?></span>
                                        </div>
                                    <?php elseif ($diff < 0): ?>
                                        <div class="flex justify-between font-bold text-green-700 pt-1.5 border-t border-indigo-100 text-sm">
                                            <span>Refund Amount Due:</span>
                                            <span>-₹<?= number_format(abs($diff), 2) ?></span>
                                        </div>
                                    <?php else: ?>
                                        <div class="flex justify-between font-bold text-gray-600 pt-1.5 border-t border-indigo-100 text-sm">
                                            <span>Balance Amount:</span>
                                            <span>₹0.00</span>
                                        </div>
                                    <?php endif; ?>
                                </div>
                                <?php endif; ?>

                                <?php if (($order['cod_advance_paid'] ?? 0) > 0): ?>
                                <div class="border-t border-gray-100 pt-2 mt-1 space-y-1.5 text-xs text-gray-500">
                                    <div class="flex justify-between"><span>COD Advance Paid</span><span>₹<?= number_format($order['cod_advance_paid'], 2) ?></span></div>
                                    <div class="flex justify-between"><span>Balance (Cash on Delivery)</span><span>₹<?= number_format($order['balance_amount'], 2) ?></span></div>
                                </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="space-y-5">
                    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
                        <h3 class="text-xs font-bold text-gray-400 uppercase tracking-wider mb-4">Order Info</h3>
                        <div class="space-y-3 text-sm">
                            <div>
                                <p class="text-gray-400 text-xs font-semibold uppercase tracking-wide mb-0.5">Order Number</p>
                                <p class="font-bold text-gray-900 font-mono text-sm">#<?= htmlspecialchars($order['order_number']) ?></p>
                            </div>
                            <div>
                                <p class="text-gray-400 text-xs font-semibold uppercase tracking-wide mb-0.5">Placed On</p>
                                <p class="font-medium text-gray-700"><?= date('d M Y, h:i A', strtotime($order['created_at'])) ?></p>
                            </div>
                            <div>
                                <?php if (!empty($modification)): 
                                    $topDiff = (float)$modification['revised_total'] - (float)$modification['original_total'];
                                    $topPending = ($modification['extra_payment_status'] ?? 'pending') === 'pending';
                                ?>
                                    <?php if ($topDiff > 0 && $topPending): ?>
                                        <p class="text-gray-400 text-xs font-semibold uppercase tracking-wide mb-0.5">Amount Paid (Original)</p>
                                        <p class="text-xl font-black text-emerald-700">₹<?= number_format($modification['original_total'], 2) ?></p>
                                        <p class="text-xs font-bold text-red-600 mt-0.5">Due: +₹<?= number_format($topDiff, 2) ?></p>
                                    <?php else: ?>
                                        <p class="text-gray-400 text-xs font-semibold uppercase tracking-wide mb-0.5">Total Paid</p>
                                        <p class="text-xl font-black text-[#F25996]">₹<?= number_format($grandTotal, 2) ?></p>
                                    <?php endif; ?>
                                <?php else: ?>
                                    <p class="text-gray-400 text-xs font-semibold uppercase tracking-wide mb-0.5">Total Paid</p>
                                    <p class="text-xl font-black text-[#F25996]">₹<?= number_format($grandTotal, 2) ?></p>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>

                    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
                        <h3 class="text-xs font-bold text-gray-400 uppercase tracking-wider mb-4">Order Status</h3>
                        <span class="inline-block px-4 py-2 rounded-xl text-sm font-bold uppercase tracking-wide <?= $statusColor ?>">
                            <?= $statusLabel ?>
                        </span>
                        <?php if (in_array($order['status'], ['shipped', 'out_for_delivery', 'delivered'])): ?>
                        <div class="mt-5 pt-4 border-t border-gray-100">
                            <h4 class="text-xs font-semibold text-gray-500 uppercase tracking-wide mb-2">Courier</h4>
                            <p class="text-sm font-bold text-gray-900"><?= htmlspecialchars($order['courier_name'] ?: 'Not Specified') ?></p>
                            <?php if (!empty($order['tracking_number'])): ?>
                                <p class="text-xs text-gray-500 mt-1">Tracking: <span class="font-medium text-gray-700"><?= htmlspecialchars($order['tracking_number']) ?></span></p>
                            <?php endif; ?>
                        </div>
                        <?php endif; ?>
                        <?php
                        $steps = [
                            'placed'           => ['label' => 'Order Placed',     'icon' => 'M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2'],
                            'packed'           => ['label' => 'Packed',           'icon' => 'M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10'],
                            'shipped'          => ['label' => 'Shipped',          'icon' => 'M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8l1 12a2 2 0 002 2h8a2 2 0 002-2L19 8'],
                            'out_for_delivery' => ['label' => 'Out for Delivery', 'icon' => 'M13 16V6a1 1 0 00-1-1H4a1 1 0 00-1 1v10a1 1 0 001 1h1m8-1a1 1 0 01-1 1H9m4-1V8a1 1 0 011-1h2.586a1 1 0 01.707.293l3.414 3.414a1 1 0 01.293.707V16a1 1 0 01-1 1h-1m-6-1a1 1 0 001 1h1M5 17a2 2 0 104 0m-4 0a2 2 0 114 0m6 0a2 2 0 104 0m-4 0a2 2 0 114 0'],
                            'delivered'        => ['label' => 'Delivered',        'icon' => 'M5 13l4 4L19 7'],
                        ];
                        $stepKeys   = array_keys($steps);
                        $currentIdx = array_search($order['status'], $stepKeys);
                        if ($order['status'] !== 'cancelled' && $currentIdx !== false):
                        ?>
                        <div class="mt-5 pt-4 border-t border-gray-100">
                            <h4 class="text-xs font-semibold text-gray-500 uppercase tracking-wide mb-3">Progress</h4>
                            <ol class="space-y-3">
                                <?php foreach ($steps as $key => $step):
                                    $idx    = array_search($key, $stepKeys);
                                    $done   = $idx <= $currentIdx;
                                    $active = ($key === $order['status']);
                                ?>
                                <li class="flex items-center gap-3">
                                    <div class="w-7 h-7 rounded-full flex items-center justify-center flex-shrink-0
                                        <?= $done ? ($active ? 'bg-[#F25996] text-white' : 'bg-green-500 text-white') : 'bg-gray-100 text-gray-400' ?>">
                                        <?php if ($done && !$active): ?>
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                                        <?php else: ?>
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="<?= $step['icon'] ?>"/></svg>
                                        <?php endif; ?>
                                    </div>
                                    <span class="text-sm <?= $active ? 'font-bold text-[#F25996]' : ($done ? 'font-medium text-gray-600' : 'text-gray-400') ?>">
                                        <?= $step['label'] ?>
                                    </span>
                                </li>
                                <?php endforeach; ?>
                            </ol>
                        </div>
                        <?php endif; ?>
                    </div>

                    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
                        <h3 class="text-xs font-bold text-gray-400 uppercase tracking-wider mb-3">Shipping Address</h3>
                        <div class="flex items-start gap-2.5">
                            <svg class="w-4 h-4 text-gray-400 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                            </svg>
                            <div class="text-sm text-gray-600 whitespace-pre-line leading-relaxed">
                                <?= nl2br(htmlspecialchars($order['shipping_address'])) ?>
                            </div>
                        </div>
                    </div>

                    <?php if ($order['payment_status'] === 'pending'): ?>
                    <a href="<?= BASE_URL ?>/checkout/retry?order_id=<?= urlencode($order['order_number']) ?>"
                       class="flex items-center justify-center gap-2 w-full py-3 bg-[#F25996] hover:bg-[#d8407d]
                              text-white font-bold rounded-xl shadow-md transition-all text-sm">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/>
                        </svg>
                        Complete Payment
                    </a>
                    <?php endif; ?>
                </div>
            </div>

        </div>
    </div>
</div>

<!-- ========================================================================= -->
<!-- Order Review Activation Modal                                             -->
<!-- ========================================================================= -->
<div id="orderReviewModal" class="fixed inset-0 bg-gray-900/60 backdrop-blur-sm z-[150] hidden opacity-0 transition-opacity duration-300 flex items-center justify-center p-3 sm:p-4">
    <div class="bg-white rounded-2xl sm:rounded-3xl shadow-2xl w-full max-w-lg transform scale-95 transition-transform duration-300 max-h-[92vh] overflow-hidden flex flex-col border border-gray-100">
        <!-- Modal Header -->
        <div class="flex justify-between items-center p-4 sm:p-5 border-b border-gray-100 bg-[#fafafa]">
            <div class="flex items-center gap-2.5">
                <span class="w-8 h-8 rounded-full bg-pink-50 text-[#F25996] flex items-center justify-center font-bold text-sm border border-pink-100">⭐</span>
                <div>
                    <h2 class="text-base sm:text-lg font-black text-gray-900 leading-tight" id="ormTitle">Write a Review</h2>
                    <p class="text-[11px] text-gray-500 font-medium" id="ormSubtitle">Order #<?= htmlspecialchars($order['order_number']) ?></p>
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
    const badgeEl = document.getElementById('ormItemBadge');
    const nameEl = document.getElementById('ormItemName');
    const promptEl = document.getElementById('ormRatingPrompt');
    const imgEl = document.getElementById('ormItemImg');
    const fallbackEl = document.getElementById('ormItemFallback');
    
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
    document.body.style.overflow = 'hidden';
    setTimeout(() => {
        modal.classList.remove('opacity-0');
        modal.firstElementChild.classList.remove('scale-95');
    }, 10);
}

function closeOrderReviewModal() {
    const modal = document.getElementById('orderReviewModal');
    modal.classList.add('opacity-0');
    modal.firstElementChild.classList.add('scale-95');
    document.body.style.overflow = '';
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
