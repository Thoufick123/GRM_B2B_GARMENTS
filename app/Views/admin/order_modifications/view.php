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
                <p class="text-xs text-gray-400">Original → Revised</p>
                <p class="text-lg font-bold text-gray-900">
                    <span class="line-through text-gray-400">₹<?= number_format($mod['original_total'], 2) ?></span>
                    → ₹<?= number_format($mod['revised_total'], 2) ?>
                </p>
                <p class="text-sm font-semibold <?= $diffClass ?>"><?= $diffText ?></p>
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
        <div class="px-6 py-4 border-b border-gray-100 bg-gray-50">
            <h3 class="font-semibold text-gray-800">Item Changes</h3>
        </div>
        <div class="divide-y divide-gray-100">
            <?php foreach ($mod['items'] as $item): 
                $ct = $changeTypeLabels[$item['change_type']] ?? ['label' => $item['change_type'], 'class' => 'bg-gray-100 text-gray-600'];
                $isRemoved = $item['change_type'] === 'removed';
            ?>
            <div class="flex items-center gap-5 px-6 py-5 <?= $isRemoved ? 'opacity-50' : '' ?>">
                <!-- Image -->
                <div class="w-14 h-14 rounded-lg overflow-hidden bg-gray-100 flex-shrink-0">
                    <?php if ($item['product_image']): ?>
                        <img src="<?= htmlspecialchars($item['product_image']) ?>" class="w-full h-full object-cover <?= $isRemoved ? 'grayscale' : '' ?>">
                    <?php else: ?>
                        <div class="w-full h-full flex items-center justify-center text-gray-300">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                        </div>
                    <?php endif; ?>
                </div>
                <div class="flex-1 min-w-0">
                    <p class="font-semibold text-gray-900 <?= $isRemoved ? 'line-through' : '' ?>"><?= htmlspecialchars($item['product_name']) ?></p>
                    <?php if ($item['variant_name']): ?>
                        <p class="text-xs text-gray-400"><?= htmlspecialchars($item['variant_name']) ?></p>
                    <?php endif; ?>
                    <?php if ($item['change_type'] === 'replaced' && !empty($item['replacement_product_name'])): ?>
                        <p class="text-xs text-blue-700 mt-0.5">Replaced with: <?= htmlspecialchars($item['replacement_product_name']) ?><?= !empty($item['replacement_product_variant_name']) ? ' (' . htmlspecialchars($item['replacement_product_variant_name']) . ')' : '' ?></p>
                    <?php endif; ?>
                    <?php if ($item['note']): ?>
                        <p class="text-xs text-gray-500 mt-0.5 italic"><?= htmlspecialchars($item['note']) ?></p>
                    <?php endif; ?>
                </div>
                <div class="text-sm text-gray-500 flex-shrink-0">
                    <?php if ($item['change_type'] === 'added'): ?>
                        <span class="text-green-500 font-semibold italic">New</span>
                    <?php else: ?>
                        <span class="line-through"><?= $item['old_qty'] ?> × ₹<?= number_format($item['old_price'], 2) ?></span>
                    <?php endif; ?>
                </div>
                <?php if (!$isRemoved): ?>
                <svg class="w-4 h-4 text-gray-300 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                <div class="text-sm font-semibold text-gray-900 flex-shrink-0">
                    <?= $item['new_qty'] ?> × ₹<?= number_format($item['new_price'], 2) ?>
                    <span class="block text-xs text-brand-600">= ₹<?= number_format($item['new_qty'] * $item['new_price'], 2) ?></span>
                </div>
                <?php endif; ?>
                <span class="px-2.5 py-1 rounded-full text-xs font-semibold <?= $ct['class'] ?> flex-shrink-0">
                    <?= $ct['label'] ?>
                </span>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</div>
