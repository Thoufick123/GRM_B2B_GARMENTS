<?php
/**
 * Admin — Cancellations Module
 * Step 1: Review user cancellation requests → Approve or Reject
 *
 * @var array  $requests
 * @var int    $total, $pages, $current
 * @var string $filter   'pending' | 'all' | 'rejected'
 * @var array  $counts
 */
?>

<div class="w-full">
    <!-- Header Section -->
    <div class="mb-8 relative z-10">
        <!-- Breadcrumbs -->
        <div class="text-sm font-medium text-gray-400 mb-2 font-serif tracking-wide">
            Dashboard <span class="mx-1 text-gray-300">›</span> Cancellation Management <span class="mx-1 text-gray-300">›</span> <span class="text-brand-700">Cancellations</span>
        </div>
        
        <div class="flex flex-col md:flex-row md:justify-between md:items-center mb-8 gap-4">
            <h2 class="text-5xl font-display font-extrabold text-gray-900 tracking-tight">Cancellation Management</h2>
            
            <div class="flex items-center space-x-3 mt-2 md:mt-0 ml-auto md:ml-0">
                <button onclick="location.reload()" class="bg-white hover:bg-gray-50 text-gray-700 px-6 py-3 rounded-full font-bold text-sm shadow-sm border border-gray-200 transition-all flex items-center uppercase tracking-widest">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path></svg>
                    Refresh
                </button>
                <button onclick="exportCancellationsCSV()" class="bg-[#F25996] hover:bg-[#e04481] text-white px-6 py-3 rounded-full font-bold text-sm shadow-sm transition-all flex items-center uppercase tracking-widest">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path></svg>
                    Export Excel
                </button>
            </div>
        </div>
    </div>

<!-- Stat cards -->
<div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
    <?php
    $cards = [
        ['filter' => 'pending',  'label' => 'Awaiting Review', 'count' => $counts['pending_admin_approval'] ?? $counts['Pending_Admin_Approval'] ?? 0, 'icon' => 'M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z',  'bg' => 'bg-amber-50',   'text' => 'text-amber-700',   'border' => 'border-amber-200'],
        ['filter' => 'all',      'label' => 'Total Requests',  'count' => $counts['total'] ?? 0,                                                 'icon' => 'M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2', 'bg' => 'bg-gray-50', 'text' => 'text-gray-700', 'border' => 'border-gray-200'],
        ['filter' => 'all',      'label' => 'Approved',        'count' => $counts['processing'] ?? $counts['Processing'] ?? 0,                   'icon' => 'M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z', 'bg' => 'bg-sky-50',    'text' => 'text-sky-700',    'border' => 'border-sky-200'],
        ['filter' => 'rejected', 'label' => 'Rejected',        'count' => $counts['rejected'] ?? $counts['Rejected'] ?? 0,                       'icon' => 'M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z', 'bg' => 'bg-rose-50', 'text' => 'text-rose-700', 'border' => 'border-rose-200'],
    ];
    foreach ($cards as $card):
        $active = ($filter === $card['filter'] && $card['filter'] !== 'all') || ($filter === 'all' && $card['label'] === 'Total Requests');
        $ring = $active ? 'ring-2 ring-[#1e1b4b] ring-offset-1' : '';
    ?>
    <a href="?filter=<?= $card['filter'] ?>" class="block bg-white border <?= $card['border'] ?> <?= $ring ?> rounded-2xl p-4 shadow-sm hover:shadow-md transition-all <?= $card['bg'] ?> <?= $card['text'] ?>">
        <div class="flex items-center justify-between mb-2">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="<?= $card['icon'] ?>"/></svg>
            <span class="text-2xl font-black"><?= $card['count'] ?></span>
        </div>
        <p class="text-xs font-bold uppercase tracking-wider"><?= $card['label'] ?></p>
    </a>
    <?php endforeach; ?>
</div>

<!-- Quick-action button to Refunds -->
<?php $approvedCount = $counts['processing'] ?? $counts['Processing'] ?? 0; ?>
<?php if ($approvedCount > 0): ?>
<div class="mb-5 flex items-center gap-3 bg-sky-50 border border-sky-200 rounded-xl px-4 py-3">
    <svg class="w-5 h-5 text-sky-600 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
    <p class="text-sm text-sky-800 font-medium flex-1">
        <strong><?= $approvedCount ?> approved request<?= $approvedCount !== 1 ? 's' : '' ?></strong> <?= $approvedCount !== 1 ? 'are' : 'is' ?> ready for refund processing.
    </p>
    <?php if ($this->hasPermission('all_cancellations', 'edit') || $this->hasPermission('cancellations', 'edit')): ?>
    <a href="<?= BASE_URL ?>/admin/cancellations/refunds" class="px-4 py-1.5 bg-sky-600 hover:bg-sky-700 text-white text-xs font-bold rounded-lg transition-colors flex-shrink-0">
        Process Refunds →
    </a>
    <?php endif; ?>
</div>
<?php endif; ?>

<!-- =========================================================================
     Filter tabs
========================================================================= -->
<div class="flex gap-1 mb-0 border-b border-gray-200">
    <?php
    $tabs = [
        ['key' => 'pending',  'label' => 'Pending Review',      'badge' => $counts['pending_admin_approval'] ?? $counts['Pending_Admin_Approval'] ?? 0],
        ['key' => 'all',      'label' => 'All Requests',        'badge' => $counts['total'] ?? 0],
        ['key' => 'rejected', 'label' => 'Rejected',            'badge' => $counts['rejected'] ?? $counts['Rejected'] ?? 0],
    ];
    foreach ($tabs as $t):
        $isActive = $filter === $t['key'];
    ?>
    <a href="?filter=<?= $t['key'] ?>"
       class="px-4 py-2.5 text-sm font-semibold border-b-2 transition-colors whitespace-nowrap -mb-px flex items-center gap-2
              <?= $isActive ? 'border-[#1e1b4b] text-[#1e1b4b]' : 'border-transparent text-gray-500 hover:text-gray-800 hover:border-gray-300' ?>">
        <?= $t['label'] ?>
        <?php if ($t['badge'] > 0): ?>
            <span class="px-1.5 py-0.5 text-xs rounded-full font-bold <?= $isActive ? 'bg-[#1e1b4b] text-white' : 'bg-gray-100 text-gray-600' ?>">
                <?= $t['badge'] ?>
            </span>
        <?php endif; ?>
    </a>
    <?php endforeach; ?>
</div>

<!-- =========================================================================
     Table
========================================================================= -->
<div class="bg-white rounded-b-2xl rounded-tr-2xl shadow-sm border border-gray-100 border-t-0 overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full text-left border-collapse">
            <thead>
                <tr class="bg-gray-50 text-gray-500 text-xs uppercase tracking-wider font-semibold">
                    <th class="px-5 py-4 border-b border-gray-100">#</th>
                    <th class="px-5 py-4 border-b border-gray-100">Order</th>
                    <th class="px-5 py-4 border-b border-gray-100">Buyer</th>
                    <th class="px-5 py-4 border-b border-gray-100">Refund Amt.</th>
                    <th class="px-5 py-4 border-b border-gray-100">Refund Details</th>
                    <th class="px-5 py-4 border-b border-gray-100">Type</th>
                    <th class="px-5 py-4 border-b border-gray-100">Reason</th>
                    <th class="px-5 py-4 border-b border-gray-100">Status</th>
                    <th class="px-5 py-4 border-b border-gray-100">Requested</th>
                    <th class="px-5 py-4 border-b border-gray-100 text-right">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                <?php if (empty($requests)): ?>
                    <tr>
                        <td colspan="10" class="px-5 py-16 text-center text-gray-400">
                            <svg class="w-10 h-10 mx-auto text-gray-200 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                            </svg>
                            <p class="font-medium">No cancellation requests <?= $filter === 'pending' ? 'awaiting review' : '' ?>.</p>
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($requests as $req):
                        $initials  = strtoupper(substr($req['user_name'], 0, 2));
                        $colors    = [
                            'bg-[#fdf2f7] text-[#F25996] border border-[#fbaed2]',
                            'bg-pink-100 text-[#e04481] border border-pink-200',
                            'bg-rose-100 text-[#F25996] border border-rose-200',
                            'bg-pink-50 text-[#c72e6b] border border-pink-200',
                            'bg-fuchsia-50 text-[#F25996] border border-fuchsia-200'
                        ];
                        $avatarClr = $colors[ord($initials[0]) % count($colors)];

                        $normStatus = strtolower(trim($req['status']));
                        $statusColors = [
                            'pending_admin_approval' => 'bg-amber-50 text-amber-700 border-amber-200',
                            'processing'             => 'bg-sky-50 text-sky-700 border-sky-200',
                            'completed'              => 'bg-emerald-50 text-emerald-700 border-emerald-200',
                            'rejected'               => 'bg-rose-50 text-rose-700 border-rose-200',
                        ];
                        $statusBadge = $statusColors[$normStatus] ?? 'bg-gray-50 text-gray-600 border-gray-200';

                        $statusLabels = [
                            'pending_admin_approval' => 'Pending Review',
                            'processing'             => 'Approved (Processing Refund)',
                            'completed'              => 'Refunded',
                            'rejected'               => 'Rejected',
                        ];
                        $statusLabel = $statusLabels[$normStatus] ?? ucwords(str_replace('_', ' ', $normStatus));
                    ?>
                    <tr class="hover:bg-gray-50 transition-colors group">
                        <td class="px-5 py-4 text-xs text-gray-400 font-mono"><?= $req['id'] ?></td>

                        <td class="px-5 py-4">
                            <a href="<?= BASE_URL ?>/admin/orders/view?id=<?= htmlspecialchars($req['order_number']) ?>" class="font-bold text-[#1e1b4b] hover:underline text-sm">
                                #<?= htmlspecialchars($req['order_number']) ?>
                            </a>
                            <p class="text-xs text-gray-400 mt-0.5">Grand: ₹<?= number_format($req['grand_total']) ?></p>
                        </td>

                        <td class="px-5 py-4">
                            <div class="flex items-center gap-2.5">
                                <span class="w-9 h-9 rounded-full <?= $avatarClr ?> flex items-center justify-center text-xs font-bold flex-shrink-0"><?= $initials ?></span>
                                <div class="min-w-0">
                                    <p class="text-sm font-bold text-gray-900 truncate"><?= htmlspecialchars($req['user_name']) ?></p>
                                    <p class="text-xs text-gray-400 truncate"><?= htmlspecialchars($req['user_email']) ?></p>
                                </div>
                            </div>
                        </td>

                        <td class="px-5 py-4">
                            <span class="text-lg font-black text-gray-900">₹<?= number_format($req['amount'], 2) ?></span>
                        </td>

                        <td class="px-5 py-4">
                            <?php if (!empty($req['refund_method'])): ?>
                                <p class="text-xs font-bold text-gray-900"><?= htmlspecialchars($req['refund_method']) ?></p>
                                <p class="text-sm font-mono text-gray-600 select-all"><?= htmlspecialchars($req['refund_details']) ?></p>
                            <?php else: ?>
                                <span class="text-gray-400 text-xs italic">N/A</span>
                            <?php endif; ?>
                        </td>

                        <td class="px-5 py-4">
                            <span class="px-2.5 py-0.5 text-xs font-bold rounded-full uppercase border
                                <?= $req['cancellation_type'] === 'full' ? 'bg-gray-100 text-gray-700 border-gray-200' : 'bg-blue-50 text-blue-700 border-blue-200' ?>">
                                <?= ucfirst($req['cancellation_type']) ?>
                            </span>
                        </td>

                        <td class="px-5 py-4 max-w-[180px]">
                            <p class="text-sm text-gray-700 truncate" title="<?= htmlspecialchars($req['reason']) ?>">
                                <?= htmlspecialchars($req['reason']) ?>
                            </p>
                        </td>

                        <td class="px-5 py-4">
                            <span class="px-3 py-1 text-xs font-bold rounded-full border <?= $statusBadge ?>">
                                <?= $statusLabel ?>
                            </span>
                            <?php if (!empty($req['admin_notes'])): ?>
                                <p class="text-xs text-gray-400 mt-1 italic truncate max-w-[160px]" title="<?= htmlspecialchars($req['admin_notes']) ?>">
                                    Note: <?= htmlspecialchars($req['admin_notes']) ?>
                                </p>
                            <?php endif; ?>
                        </td>

                        <td class="px-5 py-4 whitespace-nowrap text-xs text-gray-500">
                            <?= date('d M Y', strtotime($req['created_at'])) ?><br>
                            <span class="text-gray-400"><?= date('h:i A', strtotime($req['created_at'])) ?></span>
                        </td>

                        <td class="px-5 py-4 text-right">
                            <div class="flex justify-end gap-2 items-center">
                                <?php if ($normStatus === 'pending_admin_approval'): ?>
                                    <?php if ($this->hasPermission('all_cancellations', 'edit')): ?>
                                    <button
                                        onclick="openApproveModal(<?= $req['id'] ?>, '<?= htmlspecialchars(addslashes($req['order_number'])) ?>', <?= $req['amount'] ?>, '<?= htmlspecialchars(addslashes($req['user_name'])) ?>')"
                                        class="px-3 py-1.5 bg-[#1e1b4b] hover:bg-indigo-900 text-white text-xs font-bold rounded-lg transition-colors shadow-sm flex items-center gap-1 cursor-pointer">
                                        <svg class="w-3.5 h-3.5 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                                        Approve
                                    </button>
                                    <button
                                        onclick="openRejectModal(<?= $req['id'] ?>, '<?= htmlspecialchars(addslashes($req['order_number'])) ?>')"
                                        class="px-3 py-1.5 bg-rose-50 hover:bg-rose-100 text-rose-700 border border-rose-200 text-xs font-bold rounded-lg transition-colors flex items-center gap-1 cursor-pointer">
                                        <svg class="w-3.5 h-3.5 text-rose-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                                        Reject
                                    </button>
                                    <?php endif; ?>
                                <?php elseif ($normStatus === 'processing'): ?>
                                    <?php if ($this->hasPermission('cancellations', 'edit') || $this->hasPermission('all_cancellations', 'edit')): ?>
                                    <a href="<?= BASE_URL ?>/admin/cancellations/refunds"
                                       class="px-3 py-1.5 bg-sky-50 hover:bg-sky-100 text-sky-700 border border-sky-200 text-xs font-bold rounded-lg transition-colors">
                                        → Process Refund
                                    </a>
                                    <?php endif; ?>
                                <?php elseif ($normStatus === 'completed'): ?>
                                    <span class="px-3 py-1.5 bg-emerald-50 text-emerald-700 border border-emerald-200 text-xs font-bold rounded-lg cursor-default">✓ Refunded</span>
                                <?php elseif ($normStatus === 'rejected'): ?>
                                    <span class="px-3 py-1.5 bg-rose-50 text-rose-700 border border-rose-200 text-xs font-bold rounded-lg cursor-default">Rejected</span>
                                <?php else: ?>
                                    <span class="px-3 py-1.5 bg-gray-50 text-gray-400 border border-gray-200 text-xs rounded-lg cursor-default"><?= htmlspecialchars($statusLabel) ?></span>
                                <?php endif; ?>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <!-- Pagination -->
    <?php if ($pages > 1): ?>
    <div class="px-5 py-4 border-t border-gray-100 flex items-center justify-between bg-gray-50/50">
        <p class="text-sm text-gray-500">Page <?= $current ?> of <?= $pages ?> &mdash; <?= $total ?> total</p>
        <div class="flex gap-1">
            <?php if ($current > 1): ?>
                <a href="?filter=<?= $filter ?>&page=<?= $current - 1 ?>" class="px-3 py-1.5 text-sm border border-gray-200 rounded-lg hover:bg-gray-100 transition-colors text-gray-600 font-medium">← Prev</a>
            <?php endif; ?>
            <?php for ($p = max(1, $current - 2); $p <= min($pages, $current + 2); $p++): ?>
                <a href="?filter=<?= $filter ?>&page=<?= $p ?>" class="px-3 py-1.5 text-sm border rounded-lg font-medium transition-colors <?= $p === $current ? 'bg-[#1e1b4b] text-white border-[#1e1b4b]' : 'border-gray-200 hover:bg-gray-100 text-gray-600' ?>">
                    <?= $p ?>
                </a>
            <?php endfor; ?>
            <?php if ($current < $pages): ?>
                <a href="?filter=<?= $filter ?>&page=<?= $current + 1 ?>" class="px-3 py-1.5 text-sm border border-gray-200 rounded-lg hover:bg-gray-100 transition-colors text-gray-600 font-medium">Next →</a>
            <?php endif; ?>
        </div>
    </div>
    <?php endif; ?>
</div>

<!-- =========================================================================
     Approve Modal
========================================================================= -->
<div id="approve-modal" class="fixed inset-0 hidden flex items-center justify-center p-4" style="z-index: 9999;" role="dialog" aria-modal="true">
    <div class="fixed inset-0 bg-gray-900/60 backdrop-blur-sm" onclick="closeApproveModal()"></div>
    <div class="relative w-full max-w-md bg-white rounded-3xl shadow-2xl border border-gray-100 overflow-hidden">

        <div class="p-6 border-b border-gray-100 bg-gradient-to-br from-[#1e1b4b] to-indigo-700">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 bg-white/20 rounded-full flex items-center justify-center">
                    <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                </div>
                <div>
                    <h3 class="font-black text-white text-lg">Approve Cancellation</h3>
                    <!-- <p class="text-indigo-200 text-sm">This will queue the refund for Cashfree processing</p> -->
                    <p class="text-indigo-200 text-sm">This will queue the refund for Razorpay / Manual processing</p>
                </div>
            </div>
        </div>

        <div class="p-6 space-y-4">
            <div class="bg-gray-50 border border-gray-200 rounded-2xl p-4 space-y-2.5">
                <div class="flex justify-between text-sm"><span class="text-gray-500">Order</span><span id="am-order" class="font-bold text-gray-900"></span></div>
                <div class="flex justify-between text-sm"><span class="text-gray-500">Buyer</span><span id="am-buyer" class="font-bold text-gray-900"></span></div>
                <div class="flex justify-between items-center">
                    <span class="text-gray-500 text-sm">Refund Amount</span>
                    <span id="am-amount" class="font-black text-xl text-[#1e1b4b]"></span>
                </div>
            </div>

            <div class="flex items-start gap-2.5 text-xs text-indigo-900 bg-indigo-50 border border-indigo-200 rounded-xl p-3">
                <svg class="w-4 h-4 flex-shrink-0 mt-0.5 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12A9 9 0 113 12a9 9 0 0118 0z"/></svg>
                <!-- <span>Approving will move this request to the <strong>Refunds</strong> page where you can trigger the Cashfree payment. The buyer is not notified at this stage.</span> -->
                <span>Approving will move this request to the <strong>Refunds</strong> page where you can trigger the refund payment. The buyer is not notified at this stage.</span>
            </div>

            <div>
                <label class="block text-sm font-bold text-gray-700 mb-1.5">Internal Note (optional)</label>
                <textarea id="am-notes" rows="2" maxlength="300" placeholder="E.g. Verified — order not dispatched yet." class="w-full border border-gray-300 rounded-xl px-4 py-2.5 text-sm resize-none focus:outline-none focus:ring-2 focus:ring-[#1e1b4b] focus:border-transparent transition"></textarea>
            </div>
        </div>

        <div class="px-6 pb-6 flex gap-3 justify-end">
            <button onclick="closeApproveModal()" class="px-5 py-2.5 border border-gray-300 text-gray-700 font-bold rounded-xl hover:bg-gray-50 transition text-sm">Cancel</button>
            <button id="am-submit" onclick="submitApprove()" class="px-6 py-2.5 bg-[#1e1b4b] hover:bg-indigo-900 text-white font-bold rounded-xl shadow-sm transition text-sm flex items-center gap-2 disabled:opacity-60">
                <span id="am-label">Approve Cancellation</span>
                <svg id="am-spinner" class="w-4 h-4 animate-spin hidden" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8z"></path></svg>
            </button>
        </div>
    </div>
</div>

<!-- =========================================================================
     Reject Modal
========================================================================= -->
<div id="reject-modal" class="fixed inset-0 hidden flex items-center justify-center p-4" style="z-index: 9999;" role="dialog" aria-modal="true">
    <div class="fixed inset-0 bg-gray-900/60 backdrop-blur-sm" onclick="closeRejectModal()"></div>
    <div class="relative w-full max-w-md bg-white rounded-3xl shadow-2xl border border-gray-100 overflow-hidden">

        <div class="p-6 border-b border-gray-100 bg-gradient-to-r from-rose-50 to-red-50">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 bg-rose-100 rounded-full flex items-center justify-center">
                    <svg class="w-5 h-5 text-rose-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </div>
                <div>
                    <h3 class="font-black text-gray-900 text-lg">Reject Cancellation</h3>
                    <p id="rm-subtitle" class="text-sm text-gray-500"></p>
                </div>
            </div>
        </div>

        <div class="p-6 space-y-4">
            <div>
                <label class="block text-sm font-bold text-gray-700 mb-1.5">
                    Rejection Reason <span class="text-red-500">*</span>
                </label>
                <textarea id="rm-notes" rows="3" maxlength="500" placeholder="Explain why the cancellation is being rejected. This will be emailed to the buyer…" class="w-full border border-gray-300 rounded-xl px-4 py-2.5 text-sm resize-none focus:outline-none focus:ring-2 focus:ring-rose-500 focus:border-transparent transition"></textarea>
                <p class="text-xs text-gray-400 mt-1 text-right"><span id="rm-count">0</span>/500</p>
            </div>
            <div class="flex items-start gap-2 text-xs text-amber-800 bg-amber-50 border border-amber-200 rounded-xl p-3">
                <svg class="w-4 h-4 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01M21 12A9 9 0 113 12a9 9 0 0118 0z"/></svg>
                <span>The order will be set to <strong>Cancelled</strong> status and the buyer will receive an email with this reason.</span>
            </div>
        </div>

        <div class="px-6 pb-6 flex gap-3 justify-end">
            <button onclick="closeRejectModal()" class="px-5 py-2.5 border border-gray-300 text-gray-700 font-bold rounded-xl hover:bg-gray-50 transition text-sm">Go Back</button>
            <button id="rm-submit" onclick="submitReject()" class="px-6 py-2.5 bg-rose-600 hover:bg-rose-700 text-white font-bold rounded-xl shadow-sm transition text-sm flex items-center gap-2 disabled:opacity-60">
                <span id="rm-label">Confirm Rejection</span>
                <svg id="rm-spinner" class="w-4 h-4 animate-spin hidden" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8z"></path></svg>
            </button>
        </div>
    </div>
</div>

<!-- Toast -->
<div id="toast-box" class="fixed bottom-6 right-6 flex flex-col gap-2 pointer-events-none" style="z-index: 10000;"></div>

<script>
let _approveId = null;
let _rejectId  = null;

document.addEventListener('DOMContentLoaded', () => {
    document.body.appendChild(document.getElementById('approve-modal'));
    document.body.appendChild(document.getElementById('reject-modal'));
    document.body.appendChild(document.getElementById('toast-box'));
});

/* ── Approve ─────────────────────────────────────────────────────────────── */
function openApproveModal(id, order, amount, buyer) {
    _approveId = id;
    document.getElementById('am-order').textContent  = '#' + order;
    document.getElementById('am-buyer').textContent  = buyer;
    document.getElementById('am-amount').textContent = '₹' + parseFloat(amount).toLocaleString('en-IN', {minimumFractionDigits:2});
    document.getElementById('am-notes').value        = '';
    document.getElementById('approve-modal').classList.remove('hidden');
    document.body.style.overflow = 'hidden';
}
function closeApproveModal() {
    document.getElementById('approve-modal').classList.add('hidden');
    document.body.style.overflow = '';
}

async function submitApprove() {
    const notes  = document.getElementById('am-notes').value.trim();
    const btn    = document.getElementById('am-submit');
    const label  = document.getElementById('am-label');
    const spin   = document.getElementById('am-spinner');
    btn.disabled = true; label.textContent = 'Approving…'; spin.classList.remove('hidden');

    try {
        const res  = await fetch('<?= BASE_URL ?>/admin/cancellations/approve', {
            method: 'POST',
            headers: {'Content-Type':'application/json','X-Requested-With':'XMLHttpRequest'},
            body: JSON.stringify({refund_request_id: _approveId, admin_notes: notes}),
        });
        const data = await res.json();
        if (data.success) {
            toast(data.message, 'success');
            closeApproveModal();
            setTimeout(() => location.reload(), 1800);
        } else {
            toast(data.message || 'Error occurred.', 'error');
            btn.disabled = false; label.textContent = 'Approve Cancellation'; spin.classList.add('hidden');
        }
    } catch(e) {
        toast('Network error.', 'error');
        btn.disabled = false; label.textContent = 'Approve Cancellation'; spin.classList.add('hidden');
    }
}

/* ── Reject ──────────────────────────────────────────────────────────────── */
function openRejectModal(id, order) {
    _rejectId = id;
    document.getElementById('rm-subtitle').textContent = 'Order #' + order;
    document.getElementById('rm-notes').value          = '';
    document.getElementById('rm-count').textContent    = '0';
    document.getElementById('reject-modal').classList.remove('hidden');
    document.body.style.overflow = 'hidden';
}
function closeRejectModal() {
    document.getElementById('reject-modal').classList.add('hidden');
    document.body.style.overflow = '';
}

document.getElementById('rm-notes').addEventListener('input', function() {
    document.getElementById('rm-count').textContent = this.value.length;
});

async function submitReject() {
    const notes = document.getElementById('rm-notes').value.trim();
    if (!notes || notes.length < 5) { toast('Please enter a reason (min 5 chars).', 'error'); return; }

    const btn   = document.getElementById('rm-submit');
    const label = document.getElementById('rm-label');
    const spin  = document.getElementById('rm-spinner');
    btn.disabled = true; label.textContent = 'Rejecting…'; spin.classList.remove('hidden');

    try {
        const res  = await fetch('<?= BASE_URL ?>/admin/cancellations/reject', {
            method: 'POST',
            headers: {'Content-Type':'application/json','X-Requested-With':'XMLHttpRequest'},
            body: JSON.stringify({refund_request_id: _rejectId, admin_notes: notes}),
        });
        const data = await res.json();
        if (data.success) {
            toast(data.message, 'success');
            closeRejectModal();
            setTimeout(() => location.reload(), 1800);
        } else {
            toast(data.message || 'Error occurred.', 'error');
            btn.disabled = false; label.textContent = 'Confirm Rejection'; spin.classList.add('hidden');
        }
    } catch(e) {
        toast('Network error.', 'error');
        btn.disabled = false; label.textContent = 'Confirm Rejection'; spin.classList.add('hidden');
    }
}

/* ── Toast ───────────────────────────────────────────────────────────────── */
function toast(msg, type='success') {
    const c  = document.getElementById('toast-box');
    const bg = type === 'success' ? 'bg-emerald-600' : 'bg-red-600';
    const ic = type === 'success'
        ? '<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>'
        : '<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01M21 12A9 9 0 113 12a9 9 0 0118 0z"/></svg>';
    const el = document.createElement('div');
    el.className = `pointer-events-auto px-5 py-3 rounded-xl shadow-lg text-sm font-semibold flex items-center gap-2 text-white ${bg} transform translate-y-4 opacity-0 transition-all duration-300`;
    el.innerHTML = ic + `<span>${msg}</span>`;
    c.appendChild(el);
    requestAnimationFrame(() => el.classList.remove('translate-y-4','opacity-0'));
    setTimeout(() => { el.classList.add('opacity-0','translate-y-4'); setTimeout(() => el.remove(), 300); }, 4500);
}

function exportCancellationsCSV() {
    let table = document.querySelector("table");
    if(!table) return;
    
    let rows = Array.from(table.querySelectorAll("tr"));
    let csvContent = "data:text/csv;charset=utf-8,";
    
    rows.forEach(row => {
        let cols = Array.from(row.querySelectorAll("th, td"));
        let rowData = cols.map(col => '"' + col.innerText.replace(/"/g, '""').trim() + '"');
        csvContent += rowData.join(",") + "\r\n";
    });
    
    let encodedUri = encodeURI(csvContent);
    let link = document.createElement("a");
    link.setAttribute("href", encodedUri);
    link.setAttribute("download", "cancellations_export_<?= date('Y-m-d') ?>.csv");
    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);
}
</script>
</div>
