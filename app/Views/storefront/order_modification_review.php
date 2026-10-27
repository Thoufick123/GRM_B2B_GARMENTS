<?php
$diff      = $mod['revised_total'] - $mod['original_total'];
$isSaving  = $diff < 0;
$diffText  = ($diff < 0 ? '−' : '+') . '₹' . number_format(abs($diff), 2);
$diffClass = $diff < 0 ? 'text-green-600' : ($diff > 0 ? 'text-red-500' : 'text-gray-500');

$changeTypeLabels = [
    'removed'       => ['label' => 'Removed',     'badge' => 'bg-red-100 text-red-700'],
    'qty_changed'   => ['label' => 'Qty Changed', 'badge' => 'bg-amber-100 text-amber-700'],
    'price_changed' => ['label' => 'Price Change','badge' => 'bg-purple-100 text-purple-700'],
    'replaced'      => ['label' => 'Replaced',    'badge' => 'bg-blue-100 text-blue-700'],
    'added'         => ['label' => 'New Added',   'badge' => 'bg-green-100 text-green-700'],
    'unchanged'     => ['label' => 'Unchanged',   'badge' => 'bg-gray-100 text-gray-500'],
];
?>

<section class="min-h-screen bg-gray-50 py-10 px-4">
<div class="max-w-2xl mx-auto space-y-6">

    <!-- Header -->
    <div class="text-center">
        <div class="inline-flex items-center justify-center w-14 h-14 rounded-full bg-amber-100 mb-4">
            <svg class="w-7 h-7 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
        </div>
        <h1 class="text-2xl font-bold text-gray-900">Your Order Has Been Modified</h1>
        <p class="text-gray-500 mt-2 text-sm">Order <span class="font-mono font-semibold text-gray-800">#<?= htmlspecialchars($mod['order_number']) ?></span></p>
    </div>

    <!-- Reason box -->
    <div class="bg-amber-50 border border-amber-200 rounded-xl p-5">
        <p class="text-sm font-semibold text-amber-800 mb-1">Message from our team:</p>
        <p class="text-sm text-amber-900"><?= nl2br(htmlspecialchars($mod['reason'])) ?></p>
    </div>

    <!-- Expiry warning -->
    <?php if ($mod['expires_at'] && strtotime($mod['expires_at']) > time()): ?>
    <div class="flex items-center gap-2 bg-white border border-gray-200 rounded-xl px-4 py-3 text-sm text-gray-600 shadow-sm">
        <svg class="w-4 h-4 text-gray-400 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
        Please respond by <strong class="text-gray-800"><?= date('M j, Y H:i', strtotime($mod['expires_at'])) ?></strong>. After this, the order will be auto-cancelled.
    </div>
    <?php endif; ?>

    <!-- Items comparison -->
    <div class="bg-white rounded-2xl border border-gray-200 shadow-sm overflow-hidden">
        <div class="px-6 py-4 bg-gray-50 border-b border-gray-100">
            <h2 class="font-semibold text-gray-800">What Changed in Your Order</h2>
        </div>
        <div class="divide-y divide-gray-100">
            <?php foreach ($mod['items'] as $item):
                $ct        = $changeTypeLabels[$item['change_type']] ?? ['label' => $item['change_type'], 'badge' => 'bg-gray-100 text-gray-500'];
                $isRemoved = $item['change_type'] === 'removed';
                $isReplaced = $item['change_type'] === 'replaced';
                $isUnchanged = $item['change_type'] === 'unchanged';
                $itemDiff = ($item['new_qty'] * $item['new_price']) - ($item['old_qty'] * $item['old_price']);
            ?>
            <?php if ($isReplaced): 
                $origPName = !empty($item['original_product_name']) ? $item['original_product_name'] : $item['product_name'];
                $origVName = !empty($item['original_variant_name']) ? $item['original_variant_name'] : ($item['variant_name'] ?? '');
                $origImg   = !empty($item['original_image']) ? $item['original_image'] : $item['product_image'];
                $repPName  = !empty($item['replacement_product_name']) ? $item['replacement_product_name'] : $item['product_name'];
                $repVName  = !empty($item['replacement_product_variant_name']) ? $item['replacement_product_variant_name'] : ($item['replacement_variant_name'] ?? '');
                $repImg    = !empty($item['replacement_image']) ? $item['replacement_image'] : $item['product_image'];
            ?>
            <div class="px-6 py-5 bg-gradient-to-r from-blue-50/40 via-indigo-50/20 to-blue-50/40">
                <div class="flex items-center justify-between gap-3 mb-3 flex-wrap">
                    <div class="flex items-center gap-2">
                        <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-xs font-bold bg-blue-100 text-blue-800 border border-blue-200">
                            🔄 Replaced Product (Modification)
                        </span>
                        <?php if (!empty($item['note'])): ?>
                            <span class="text-xs text-gray-500 italic">Note: <?= htmlspecialchars($item['note']) ?></span>
                        <?php endif; ?>
                    </div>
                    <div class="text-xs font-bold px-2.5 py-1 rounded-lg border <?= $itemDiff >= 0 ? 'bg-red-50 text-red-700 border-red-200' : 'bg-green-50 text-green-700 border-green-200' ?>">
                        <?= $itemDiff >= 0 ? 'Difference: +₹' . number_format($itemDiff, 2) . ' (To pay for replacement)' : 'Difference: -₹' . number_format(abs($itemDiff), 2) . ' (Refund / Adjustment)' ?>
                    </div>
                </div>

                <!-- Dual Cards -->
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3.5 items-stretch">
                    <!-- 1. Customer First Order Product -->
                    <div class="bg-white rounded-xl border border-red-200/90 p-3.5 shadow-sm flex items-center gap-3.5 relative">
                        <span class="absolute -top-2.5 left-3 px-2 py-0.5 rounded text-[9.5px] font-extrabold uppercase tracking-wider bg-red-100 text-red-700 border border-red-200">
                            ⏮️ First Order Product
                        </span>
                        <div class="w-14 h-14 rounded-lg bg-gray-100 border border-gray-200 overflow-hidden flex-shrink-0 flex items-center justify-center">
                            <?php if (!empty($origImg)): ?>
                                <img src="<?= htmlspecialchars(get_image_url($origImg)) ?>" class="w-full h-full object-cover">
                            <?php else: ?>
                                <svg class="w-6 h-6 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                            <?php endif; ?>
                        </div>
                        <div class="flex-1 min-w-0">
                            <p class="font-bold text-gray-900 text-xs sm:text-sm truncate" title="<?= htmlspecialchars($origPName) ?>"><?= htmlspecialchars($origPName) ?></p>
                            <?php if (!empty($origVName)): ?>
                                <p class="text-[11px] text-gray-500 font-medium">Variant: <?= htmlspecialchars($origVName) ?></p>
                            <?php endif; ?>
                            <div class="mt-1 text-xs text-gray-600 font-medium">
                                <span><?= $item['old_qty'] ?> × ₹<?= number_format($item['old_price'], 2) ?></span>
                                <span class="font-bold text-gray-900 ml-1.5">= ₹<?= number_format($item['old_qty'] * $item['old_price'], 2) ?></span>
                            </div>
                        </div>
                    </div>

                    <!-- 2. Replaced Product -->
                    <div class="bg-white rounded-xl border border-blue-200 p-3.5 shadow-sm flex items-center gap-3.5 relative">
                        <span class="absolute -top-2.5 left-3 px-2 py-0.5 rounded text-[9.5px] font-extrabold uppercase tracking-wider bg-blue-100 text-blue-700 border border-blue-200">
                            🔄 Replaced Product
                        </span>
                        <div class="w-14 h-14 rounded-lg bg-gray-100 border border-blue-200 overflow-hidden flex-shrink-0 flex items-center justify-center">
                            <?php if (!empty($repImg)): ?>
                                <img src="<?= htmlspecialchars(get_image_url($repImg)) ?>" class="w-full h-full object-cover">
                            <?php else: ?>
                                <svg class="w-6 h-6 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                            <?php endif; ?>
                        </div>
                        <div class="flex-1 min-w-0">
                            <p class="font-bold text-gray-900 text-xs sm:text-sm truncate" title="<?= htmlspecialchars($repPName) ?>"><?= htmlspecialchars($repPName) ?></p>
                            <?php if (!empty($repVName)): ?>
                                <p class="text-[11px] text-blue-600 font-medium">Variant: <?= htmlspecialchars($repVName) ?></p>
                            <?php endif; ?>
                            <div class="mt-1 text-xs text-gray-600 font-medium">
                                <span><?= $item['new_qty'] ?> × ₹<?= number_format($item['new_price'], 2) ?></span>
                                <span class="font-bold text-brand-700 ml-1.5">= ₹<?= number_format($item['new_qty'] * $item['new_price'], 2) ?></span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <?php else: ?>
            <div class="flex items-start gap-4 px-6 py-5 <?= $isRemoved ? 'bg-red-50/50' : '' ?>">
                <!-- Image -->
                <div class="w-12 h-12 rounded-lg overflow-hidden bg-gray-100 flex-shrink-0 mt-0.5">
                    <?php if (!empty($item['product_image'])): ?>
                        <img src="<?= htmlspecialchars(get_image_url($item['product_image'])) ?>" class="w-full h-full object-cover <?= $isRemoved ? 'grayscale opacity-50' : '' ?>">
                    <?php endif; ?>
                </div>
                <div class="flex-1 min-w-0">
                    <div class="flex items-center gap-2 flex-wrap">
                        <p class="font-semibold text-gray-900 <?= $isRemoved ? 'line-through text-gray-400' : '' ?>">
                            <?= htmlspecialchars($item['product_name']) ?>
                        </p>
                        <?php if ($item['variant_name']): ?>
                            <span class="text-xs text-gray-500">(<?= htmlspecialchars($item['variant_name']) ?>)</span>
                        <?php endif; ?>
                        <?php if (!$isUnchanged): ?>
                        <span class="px-2 py-0.5 rounded-full text-xs font-semibold <?= $ct['badge'] ?>">
                            <?= $ct['label'] ?>
                        </span>
                        <?php endif; ?>
                    </div>

                    <?php if (!empty($item['note'])): ?>
                        <p class="text-xs text-gray-500 italic mt-1"><?= htmlspecialchars($item['note']) ?></p>
                    <?php endif; ?>
                    
                    <div class="mt-2 text-sm flex items-center gap-3 flex-wrap">
                        <?php if ($isRemoved): ?>
                            <span class="text-gray-400 line-through"><?= $item['old_qty'] ?> × ₹<?= number_format($item['old_price'], 2) ?></span>
                            <span class="font-semibold text-red-500">Removed</span>
                        <?php elseif ($item['change_type'] === 'added'): ?>
                            <span class="font-semibold text-green-500 italic">New</span>
                            <svg class="w-4 h-4 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                            <span class="text-gray-900 font-semibold"><?= $item['new_qty'] ?> × ₹<?= number_format($item['new_price'], 2) ?> = <strong class="text-brand-700">₹<?= number_format($item['new_qty'] * $item['new_price'], 2) ?></strong></span>
                        <?php elseif ($isUnchanged): ?>
                            <span class="text-gray-600"><?= $item['new_qty'] ?> × ₹<?= number_format($item['new_price'], 2) ?> = <strong>₹<?= number_format($item['new_qty'] * $item['new_price'], 2) ?></strong></span>
                        <?php else: ?>
                            <span class="text-gray-400 line-through"><?= $item['old_qty'] ?> × ₹<?= number_format($item['old_price'], 2) ?></span>
                            <svg class="w-4 h-4 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                            <span class="text-gray-900 font-semibold"><?= $item['new_qty'] ?> × ₹<?= number_format($item['new_price'], 2) ?> = <strong class="text-brand-700">₹<?= number_format($item['new_qty'] * $item['new_price'], 2) ?></strong></span>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            <?php endif; ?>
            <?php endforeach; ?>
        </div>
    </div>

    <!-- Bill Summary -->
    <div class="bg-white rounded-2xl border border-gray-200 shadow-sm p-6">
        <h3 class="font-semibold text-gray-800 mb-4">Bill Summary</h3>
        <div class="space-y-2.5 text-sm text-gray-600">
            <div class="flex justify-between">
                <span>Amount Already Paid (Old Bill)</span>
                <span class="line-through text-gray-400">₹<?= number_format($mod['original_total'], 2) ?></span>
            </div>
            <div class="flex justify-between font-semibold text-gray-900 pt-2 border-t border-gray-100">
                <span>Revised Grand Total</span>
                <span class="text-xl text-brand-700">₹<?= number_format($mod['revised_total'], 2) ?></span>
            </div>
            <div class="flex justify-between text-sm <?= $diffClass ?> font-bold pt-1 border-t border-gray-100">
                <span><?= $isSaving ? 'Refund Amount' : 'Balance to Pay for Replaced Items' ?></span>
                <span><?= $diffText ?></span>
            </div>
        </div>
    </div>

    <!-- Decision Buttons -->
    <div class="bg-white rounded-2xl border border-gray-200 shadow-sm p-6">
        <p class="text-sm text-gray-600 mb-5 text-center">
            <strong>Accept</strong> to confirm the modified order, or <strong>Reject</strong> to cancel and get a full refund.
        </p>
        <div class="flex gap-4">
            <!-- Reject -->
            <form action="<?= BASE_URL ?>/order-modification/respond" method="POST" class="flex-1" onsubmit="return confirm('Are you sure you want to reject this modification? Your order will be cancelled and a refund will be initiated.')">
                <input type="hidden" name="modification_id" value="<?= $mod['id'] ?>">
                <input type="hidden" name="decision" value="reject">
                <button type="submit" class="w-full py-3.5 border-2 border-red-200 text-red-600 font-semibold rounded-xl hover:bg-red-50 transition-colors text-sm">
                    ✕ Reject & Cancel Order
                </button>
            </form>
            <!-- Accept -->
            <form action="<?= BASE_URL ?>/order-modification/respond" method="POST" class="flex-1" onsubmit="return confirm('Accept this modification? Your order will be updated and processed at the revised total.')">
                <input type="hidden" name="modification_id" value="<?= $mod['id'] ?>">
                <input type="hidden" name="decision" value="accept">
                <button type="submit" class="w-full py-3.5 bg-green-600 text-white font-semibold rounded-xl hover:bg-green-700 transition-colors text-sm">
                    ✓ Accept Modified Order
                </button>
            </form>
        </div>
    </div>

    <a href="<?= BASE_URL ?>/dashboard/orders" class="block text-center text-sm text-gray-400 hover:text-gray-600">← Back to My Orders</a>

</div>
</section>
