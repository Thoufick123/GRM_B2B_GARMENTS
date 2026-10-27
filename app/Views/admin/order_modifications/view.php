<?php
$statusColors = [
    'pending_buyer' => 'bg-amber-100 text-amber-700',
    'accepted'      => 'bg-green-100 text-green-700',
    'rejected'      => 'bg-red-100 text-red-700',
    'cancelled'     => 'bg-gray-100 text-gray-600',
];
$statusLabels = [
    'pending_buyer' => '⏳ Awaiting Buyer',
    'accepted'      => '✅ Accepted',
    'rejected'      => '❌ Rejected',
    'cancelled'     => 'Cancelled',
];
$diff     = $mod['revised_total'] - $mod['original_total'];
$diffText = ($diff < 0 ? '-' : '+') . '₹' . number_format(abs($diff), 2);
$diffClass = $diff < 0 ? 'text-green-600' : ($diff > 0 ? 'text-red-600' : 'text-gray-500');

$changeTypeLabels = [
    'removed'       => ['label' => 'Removed',       'class' => 'bg-red-100 text-red-700'],
    'qty_changed'   => ['label' => 'Qty Changed',   'class' => 'bg-amber-100 text-amber-700'],
    'price_changed' => ['label' => 'Price Changed', 'class' => 'bg-purple-100 text-purple-700'],
    'replaced'      => ['label' => 'Replaced',      'class' => 'bg-blue-100 text-blue-700'],
    'added'         => ['label' => 'New Added',     'class' => 'bg-green-100 text-green-700'],
    'unchanged'     => ['label' => 'Unchanged',     'class' => 'bg-gray-100 text-gray-500'],
];
?>

<div class="max-w-4xl mx-auto space-y-6">
    <!-- Back -->
    <a href="<?= BASE_URL ?>/admin/order-modifications" class="inline-flex items-center gap-2 text-sm text-gray-500 hover:text-gray-800">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
        Back
    </a>

    <!-- Header Card -->
    <div class="bg-white rounded-2xl border border-gray-200 shadow-sm p-6">
        <div class="flex flex-wrap items-start justify-between gap-4">
            <div>
                <div class="flex items-center gap-3 mb-1">
                    <h2 class="text-xl font-bold text-gray-900">Modification #<?= $mod['id'] ?></h2>
                    <span class="px-3 py-1 rounded-full text-xs font-semibold <?= $statusColors[$mod['status']] ?? 'bg-gray-100 text-gray-600' ?>">
                        <?= $statusLabels[$mod['status']] ?? ucfirst($mod['status']) ?>
                    </span>
                </div>
                <p class="text-sm text-gray-500">
                    Order <a href="<?= BASE_URL ?>/admin/orders/view?id=<?= htmlspecialchars($mod['order_number']) ?>" class="font-mono text-brand-700 hover:underline">#<?= htmlspecialchars($mod['order_number']) ?></a>
                    · Buyer: <span class="font-medium text-gray-700"><?= htmlspecialchars($mod['buyer_name']) ?></span>
                </p>
                <p class="text-sm text-gray-400 mt-0.5">Modified by <?= htmlspecialchars($mod['admin_name']) ?> on <?= date('M j, Y H:i', strtotime($mod['created_at'])) ?></p>
            </div>
            <div class="text-right">
                <p class="text-xs text-gray-400 font-semibold uppercase tracking-wider">Original (Paid) → Revised Total</p>
                <p class="text-lg font-bold text-gray-900">
                    <span class="line-through text-gray-400">₹<?= number_format($mod['original_total'], 2) ?></span>
                    → <span class="text-indigo-950 font-black">₹<?= number_format($mod['revised_total'], 2) ?></span>
                </p>
                <p class="text-sm font-bold <?= $diffClass ?>">
                    <?= $diff > 0 ? '+₹' . number_format($diff, 2) . ' (Balance to Pay)' : ($diff < 0 ? '-₹' . number_format(abs($diff), 2) . ' (Refund Due)' : '₹0.00') ?>
                </p>
                <div class="mt-2 text-xs bg-gray-50 px-2.5 py-1 rounded-lg border border-gray-200 inline-block text-left">
                    <div class="text-gray-500">Paid by Buyer: <strong class="text-gray-800">₹<?= number_format($mod['original_total'], 2) ?></strong></div>
                    <?php if ($diff > 0): ?>
                        <div class="text-red-600 font-bold">Due for Replaced Items: +₹<?= number_format($diff, 2) ?></div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        <?php if ($mod['reason']): ?>
        <div class="mt-4 pt-4 border-t border-gray-100">
            <p class="text-xs text-gray-400 uppercase tracking-wider font-semibold mb-1">Reason</p>
            <p class="text-sm text-gray-700"><?= nl2br(htmlspecialchars($mod['reason'])) ?></p>
        </div>
        <?php endif; ?>

        <?php if (!empty($mod['payment_screenshot'])): ?>
        <div class="mt-4 pt-4 border-t border-gray-100">
            <p class="text-xs text-blue-500 uppercase tracking-wider font-semibold mb-2">📎 Customer Payment Screenshot</p>
            <?php
                $fileUrl = get_image_url($mod['payment_screenshot']);
                $parsedPath = parse_url($mod['payment_screenshot'], PHP_URL_PATH);
                $ext = strtolower(pathinfo($parsedPath ?: $mod['payment_screenshot'], PATHINFO_EXTENSION));
                $isPdf = ($ext === 'pdf');
            ?>
            <?php if ($isPdf): ?>
                <a href="<?= $fileUrl ?>" target="_blank"
                   class="inline-flex items-center gap-2 px-4 py-2 bg-blue-50 text-blue-700 text-sm font-semibold rounded-lg hover:bg-blue-100 transition-colors">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                    View Payment PDF
                </a>
            <?php else: ?>
                <a href="<?= $fileUrl ?>" target="_blank">
                    <img src="<?= $fileUrl ?>" alt="Payment Screenshot"
                         class="max-h-64 rounded-xl border border-blue-100 shadow-sm hover:shadow-md transition-shadow object-contain cursor-zoom-in">
                </a>
                <p class="text-xs text-gray-400 mt-1">Click image to open full size</p>
            <?php endif; ?>
        </div>
        <?php endif; ?>
        <?php if ($mod['status'] === 'pending_buyer' && $mod['expires_at']): ?>
        <div class="mt-3 bg-amber-50 border border-amber-100 rounded-lg px-4 py-2 text-sm text-amber-700 flex items-center gap-2">
            <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            Expires: <?= date('M j, Y H:i', strtotime($mod['expires_at'])) ?>
        </div>
        <?php endif; ?>
        <?php if ($mod['buyer_responded_at']): ?>
        <div class="mt-3 text-xs text-gray-400">Buyer responded: <?= date('M j, Y H:i', strtotime($mod['buyer_responded_at'])) ?></div>
        <?php endif; ?>
    </div>

    <!-- Items -->
    <div class="bg-white rounded-2xl border border-gray-200 shadow-sm overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-100 bg-gray-50 flex items-center justify-between">
            <h3 class="font-semibold text-gray-800">Item Changes</h3>
            <span class="text-xs text-gray-500 font-medium">Original vs Revised Order Products</span>
        </div>
        <div class="divide-y divide-gray-100">
            <?php foreach ($mod['items'] as $item): 
                $ct = $changeTypeLabels[$item['change_type']] ?? ['label' => $item['change_type'], 'class' => 'bg-gray-100 text-gray-600'];
                $isRemoved = $item['change_type'] === 'removed';
                $isReplaced = $item['change_type'] === 'replaced';
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
                <div class="flex items-center justify-between gap-3 mb-4 flex-wrap">
                    <div class="flex items-center gap-2">
                        <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold bg-blue-100 text-blue-800 border border-blue-200">
                            🔄 Replaced Product (Bill Modification)
                        </span>
                        <?php if (!empty($item['note'])): ?>
                            <span class="text-xs text-gray-500 italic">Note: <?= htmlspecialchars($item['note']) ?></span>
                        <?php endif; ?>
                    </div>
                    <div class="text-xs font-bold px-3 py-1.5 rounded-lg border <?= $itemDiff >= 0 ? 'bg-red-50 text-red-700 border-red-200' : 'bg-green-50 text-green-700 border-green-200' ?>">
                        <?= $itemDiff >= 0 ? 'Item Difference: +₹' . number_format($itemDiff, 2) . ' (To pay for replacement)' : 'Item Difference: -₹' . number_format(abs($itemDiff), 2) . ' (Refund / Adjustment)' ?>
                    </div>
                </div>

                <!-- Dual Cards: First Order Product vs Replaced Product -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 items-stretch">
                    <!-- 1. Customer First Order Product -->
                    <div class="bg-white rounded-xl border border-red-200/90 p-4 shadow-sm flex items-center gap-4 relative">
                        <span class="absolute -top-2.5 left-3 px-2 py-0.5 rounded text-[10px] font-extrabold uppercase tracking-wider bg-red-100 text-red-700 border border-red-200">
                            ⏮️ Customer First Order Product
                        </span>
                        <div class="w-16 h-16 rounded-lg bg-gray-100 border border-gray-200 overflow-hidden flex-shrink-0 flex items-center justify-center">
                            <?php if (!empty($origImg)): ?>
                                <img src="<?= htmlspecialchars(get_image_url($origImg)) ?>" class="w-full h-full object-cover">
                            <?php else: ?>
                                <svg class="w-6 h-6 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                            <?php endif; ?>
                        </div>
                        <div class="flex-1 min-w-0">
                            <p class="font-bold text-gray-900 text-sm truncate" title="<?= htmlspecialchars($origPName) ?>"><?= htmlspecialchars($origPName) ?></p>
                            <?php if (!empty($origVName)): ?>
                                <p class="text-xs text-gray-500 font-medium mt-0.5">Variant: <?= htmlspecialchars($origVName) ?></p>
                            <?php endif; ?>
                            <div class="mt-2 text-xs text-gray-600 font-medium">
                                <span><?= $item['old_qty'] ?> × ₹<?= number_format($item['old_price'], 2) ?></span>
                                <span class="font-bold text-gray-900 ml-1.5">= ₹<?= number_format($item['old_qty'] * $item['old_price'], 2) ?></span>
                            </div>
                        </div>
                    </div>

                    <!-- 2. Replaced Product -->
                    <div class="bg-white rounded-xl border border-blue-200 p-4 shadow-sm flex items-center gap-4 relative">
                        <span class="absolute -top-2.5 left-3 px-2 py-0.5 rounded text-[10px] font-extrabold uppercase tracking-wider bg-blue-100 text-blue-700 border border-blue-200">
                            🔄 Replaced Product
                        </span>
                        <div class="w-16 h-16 rounded-lg bg-gray-100 border border-blue-200 overflow-hidden flex-shrink-0 flex items-center justify-center">
                            <?php if (!empty($repImg)): ?>
                                <img src="<?= htmlspecialchars(get_image_url($repImg)) ?>" class="w-full h-full object-cover">
                            <?php else: ?>
                                <svg class="w-6 h-6 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                            <?php endif; ?>
                        </div>
                        <div class="flex-1 min-w-0">
                            <p class="font-bold text-gray-900 text-sm truncate" title="<?= htmlspecialchars($repPName) ?>"><?= htmlspecialchars($repPName) ?></p>
                            <?php if (!empty($repVName)): ?>
                                <p class="text-xs text-blue-600 font-medium mt-0.5">Variant: <?= htmlspecialchars($repVName) ?></p>
                            <?php endif; ?>
                            <div class="mt-2 text-xs text-gray-600 font-medium">
                                <span><?= $item['new_qty'] ?> × ₹<?= number_format($item['new_price'], 2) ?></span>
                                <span class="font-bold text-brand-700 ml-1.5">= ₹<?= number_format($item['new_qty'] * $item['new_price'], 2) ?></span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <?php else: ?>
            <div class="flex items-start gap-5 px-6 py-5 <?= $isRemoved ? 'opacity-50 bg-red-50/20' : '' ?>">
                <!-- Image -->
                <div class="w-14 h-14 rounded-lg overflow-hidden bg-gray-100 flex-shrink-0 mt-0.5">
                    <?php if (!empty($item['product_image'])): ?>
                        <img src="<?= htmlspecialchars(get_image_url($item['product_image'])) ?>" class="w-full h-full object-cover <?= $isRemoved ? 'grayscale' : '' ?>">
                    <?php else: ?>
                        <div class="w-full h-full flex items-center justify-center text-gray-300">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                        </div>
                    <?php endif; ?>
                </div>
                <div class="flex-1 min-w-0">
                    <div class="flex items-center gap-2 flex-wrap">
                        <p class="font-semibold text-gray-900 <?= $isRemoved ? 'line-through text-gray-400' : '' ?>"><?= htmlspecialchars($item['product_name']) ?></p>
                        <?php if ($item['variant_name']): ?>
                            <span class="text-xs text-gray-500 font-medium">(<?= htmlspecialchars($item['variant_name']) ?>)</span>
                        <?php endif; ?>
                    </div>

                    <?php if ($item['note']): ?>
                        <p class="text-xs text-gray-500 mt-1 italic"><?= htmlspecialchars($item['note']) ?></p>
                    <?php endif; ?>
                </div>
                <div class="text-sm text-gray-500 flex-shrink-0 pt-0.5">
                    <?php if ($item['change_type'] === 'added'): ?>
                        <span class="text-green-500 font-semibold italic">New</span>
                    <?php else: ?>
                        <span class="line-through"><?= $item['old_qty'] ?> × ₹<?= number_format($item['old_price'], 2) ?></span>
                    <?php endif; ?>
                </div>
                <?php if (!$isRemoved): ?>
                <svg class="w-4 h-4 text-gray-300 flex-shrink-0 mt-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                <div class="text-sm font-semibold text-gray-900 flex-shrink-0 pt-0.5">
                    <?= $item['new_qty'] ?> × ₹<?= number_format($item['new_price'], 2) ?>
                    <span class="block text-xs text-brand-600">= ₹<?= number_format($item['new_qty'] * $item['new_price'], 2) ?></span>
                </div>
                <?php endif; ?>
                <span class="px-2.5 py-1 rounded-full text-xs font-semibold <?= $ct['class'] ?> flex-shrink-0">
                    <?= $ct['label'] ?>
                </span>
            </div>
            <?php endif; ?>
            <?php endforeach; ?>
        </div>
    </div>
</div>
