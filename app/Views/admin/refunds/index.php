<?php
/**
 * Admin — Refunds sub-module (Step 2: Cashfree payment processing)
 * Only shows requests with status = 'processing' (approved) or 'completed'
 *
 * @var array  $requests
 * @var int    $total, $pages, $current
 * @var string $tab   'pending' | 'completed' | 'all'
 * @var array  $counts
 */
?>

<div class="w-full">
    <!-- Header Section -->
    <div class="mb-8 relative z-10">
        <!-- Breadcrumbs -->
        <div class="text-sm font-medium text-gray-400 mb-2 font-serif tracking-wide">
            Dashboard <span class="mx-1 text-gray-300">›</span> <a href="<?= BASE_URL ?>/admin/cancellations" class="hover:text-brand-600">Cancellation Management</a> <span class="mx-1 text-gray-300">›</span> <span class="text-brand-700">Refunds</span>
        </div>
        
        <div class="flex flex-col md:flex-row md:justify-between md:items-center mb-6 gap-4 w-full">
            <h2 class="text-5xl font-display font-extrabold text-gray-900 tracking-tight">Refund Processing</h2>
            
            <div class="flex items-center space-x-3 ml-auto">
                <button onclick="window.location.reload()" class="bg-white hover:bg-gray-50 text-gray-700 px-6 py-3 rounded-full font-bold text-sm shadow-sm border border-gray-200 transition-all flex items-center uppercase tracking-widest">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path></svg>
                    Refresh
                </button>
                <button onclick="exportRefundsCSV()" class="bg-[#F25996] hover:bg-[#e04481] text-white px-6 py-3 rounded-full font-bold text-sm shadow-sm transition-all flex items-center uppercase tracking-widest">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path></svg>
                    Export Excel
                </button>
            </div>
        </div>
    </div>

<!-- Stat cards (compact) -->
<div class="grid grid-cols-3 gap-4 mb-6">
    <?php
    $cards = [
        ['tab' => 'pending',   'label' => 'Awaiting Payment', 'count' => $counts['Processing'],  'amount' => $amounts['Processing'], 'bg' => 'bg-sky-50',     'text' => 'text-sky-700',     'border' => 'border-sky-200'],
        ['tab' => 'completed', 'label' => 'Refunded',          'count' => $counts['Completed'],   'amount' => $amounts['Completed'], 'bg' => 'bg-emerald-50', 'text' => 'text-emerald-700', 'border' => 'border-emerald-200'],
        ['tab' => 'all',       'label' => 'Total Processed',   'count' => $counts['Processing'] + $counts['Completed'], 'amount' => $amounts['Processing'] + $amounts['Completed'], 'bg' => 'bg-gray-50', 'text' => 'text-gray-700', 'border' => 'border-gray-200'],
    ];
    foreach ($cards as $card):
        $active = $tab === $card['tab'];
        $ring   = $active ? 'ring-2 ring-[#1e1b4b] ring-offset-1' : '';
    ?>
    <a href="?tab=<?= $card['tab'] ?>" class="block bg-white border <?= $card['border'] ?> <?= $ring ?> rounded-2xl p-4 shadow-sm hover:shadow-md transition-all <?= $card['bg'] ?> <?= $card['text'] ?>">
        <div class="flex justify-between items-end mb-1">
            <p class="text-2xl font-black"><?= $card['count'] ?></p>
            <p class="text-lg font-bold text-gray-900">₹<?= number_format($card['amount'], 2) ?></p>
        </div>
        <p class="text-xs font-bold uppercase tracking-wider"><?= $card['label'] ?></p>
    </a>
    <?php endforeach; ?>
</div>

<!-- Tabs -->
<div class="flex gap-1 mb-0 border-b border-gray-200">
    <?php
    $tabs = [
        ['key' => 'pending',   'label' => 'Awaiting Refund',  'count' => $counts['Processing']],
        ['key' => 'completed', 'label' => 'Completed',         'count' => $counts['Completed']],
        ['key' => 'all',       'label' => 'All',               'count' => $counts['Processing'] + $counts['Completed']],
    ];
    foreach ($tabs as $t):
        $isActive = $tab === $t['key'];
    ?>
    <a href="?tab=<?= $t['key'] ?>"
       class="px-4 py-2.5 text-sm font-semibold border-b-2 -mb-px transition-colors flex items-center gap-2
              <?= $isActive ? 'border-[#1e1b4b] text-[#1e1b4b]' : 'border-transparent text-gray-500 hover:text-gray-800 hover:border-gray-300' ?>">
        <?= $t['label'] ?>
        <?php if ($t['count'] > 0): ?>
            <span class="px-1.5 py-0.5 text-xs rounded-full font-bold <?= $isActive ? 'bg-[#1e1b4b] text-white' : 'bg-gray-100 text-gray-600' ?>">
                <?= $t['count'] ?>
            </span>
        <?php endif; ?>
    </a>
    <?php endforeach; ?>
</div>

<!-- Table -->
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
                    <th class="px-5 py-4 border-b border-gray-100">Reference</th>
                    <th class="px-5 py-4 border-b border-gray-100">Status</th>
                    <th class="px-5 py-4 border-b border-gray-100">Approved On</th>
                    <th class="px-5 py-4 border-b border-gray-100 text-right">Action</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                <?php if (empty($requests)): ?>
                    <tr>
                        <td colspan="8" class="px-5 py-16 text-center text-gray-400">
                            <svg class="w-10 h-10 mx-auto text-gray-200 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h10a8 8 0 018 8v2M3 10l6 6m-6-6l6-6"/>
                            </svg>
                            <p class="font-medium">No refunds awaiting processing.</p>
                            <?php if ($tab === 'pending'): ?>
                                <p class="text-sm mt-1">Approve cancellations first from the <a href="<?= BASE_URL ?>/admin/cancellations" class="text-[#1e1b4b] font-bold underline">Cancellations</a> page.</p>
                            <?php endif; ?>
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
                    ?>
                    <tr class="hover:bg-gray-50 transition-colors">
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
                                    <p class="text-xs text-gray-400"><?= htmlspecialchars($req['user_email']) ?></p>
                                </div>
                            </div>
                        </td>

                        <td class="px-5 py-4">
                            <span class="text-xl font-black text-gray-900">₹<?= number_format($req['amount'], 2) ?></span>
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
                            <?php $refundRef = $req['razorpay_refund_id'] ?? $req['cashfree_refund_id'] ?? null; ?>
                            <?php if (!empty($refundRef)): ?>
                                <span class="font-mono text-xs bg-gray-100 text-gray-700 px-2 py-1 rounded border border-gray-200 select-all">
                                    <?= htmlspecialchars($refundRef) ?>
                                </span>
                            <?php else: ?>
                                <span class="text-gray-400 text-xs italic">Not yet processed</span>
                            <?php endif; ?>
                        </td>

                        <td class="px-5 py-4">
                            <?php $normStatus = strtolower(trim($req['status'])); ?>
                            <?php if ($normStatus === 'processing'): ?>
                                <span class="px-3 py-1 text-xs font-bold rounded-full bg-sky-50 text-sky-700 border border-sky-200">Awaiting Refund</span>
                            <?php else: ?>
                                <span class="px-3 py-1 text-xs font-bold rounded-full bg-emerald-50 text-emerald-700 border border-emerald-200">✓ Refunded</span>
                            <?php endif; ?>
                        </td>

                        <td class="px-5 py-4 text-xs text-gray-500 whitespace-nowrap">
                            <?= date('d M Y', strtotime($req['updated_at'])) ?><br>
                            <span class="text-gray-400"><?= date('h:i A', strtotime($req['updated_at'])) ?></span>
                        </td>

                        <td class="px-5 py-4 text-right">
                            <?php if ($normStatus === 'processing'): ?>
                                <?php if ($this->hasPermission('refunds', 'edit')): ?>
                                <button
                                    onclick="openProcessModal(<?= $req['id'] ?>, '<?= htmlspecialchars(addslashes($req['order_number'])) ?>', <?= $req['amount'] ?>, '<?= htmlspecialchars(addslashes($req['user_name'])) ?>', '<?= htmlspecialchars(addslashes($req['refund_method'] ?? '')) ?>', '<?= htmlspecialchars(addslashes($req['refund_details'] ?? '')) ?>')"
                                    class="px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold rounded-lg transition-colors shadow-sm flex items-center gap-1.5 ml-auto cursor-pointer">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h10a8 8 0 018 8v2M3 10l6 6m-6-6l6-6"/></svg>
                                    Refund Confirm
                                </button>
                                <?php endif; ?>
                            <?php else: ?>
                                <span class="px-3 py-2 bg-gray-50 text-gray-400 border border-gray-200 text-xs font-bold rounded-lg cursor-default">Completed</span>
                            <?php endif; ?>
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
                <a href="?tab=<?= $tab ?>&page=<?= $current - 1 ?>" class="px-3 py-1.5 text-sm border border-gray-200 rounded-lg hover:bg-gray-100 text-gray-600 font-medium">← Prev</a>
            <?php endif; ?>
            <?php for ($p = max(1, $current - 2); $p <= min($pages, $current + 2); $p++): ?>
                <a href="?tab=<?= $tab ?>&page=<?= $p ?>" class="px-3 py-1.5 text-sm border rounded-lg font-medium transition-colors <?= $p === $current ? 'bg-[#1e1b4b] text-white border-[#1e1b4b]' : 'border-gray-200 hover:bg-gray-100 text-gray-600' ?>">
                    <?= $p ?>
                </a>
            <?php endfor; ?>
            <?php if ($current < $pages): ?>
                <a href="?tab=<?= $tab ?>&page=<?= $current + 1 ?>" class="px-3 py-1.5 text-sm border border-gray-200 rounded-lg hover:bg-gray-100 text-gray-600 font-medium">Next →</a>
            <?php endif; ?>
        </div>
    </div>
    <?php endif; ?>
</div>

<!-- =========================================================================
     Process Refund Confirmation Modal
========================================================================= -->
<div id="process-modal" class="fixed inset-0 hidden flex items-center justify-center p-4" style="z-index: 9999;" role="dialog" aria-modal="true">
    <div class="fixed inset-0 bg-gray-900/60 backdrop-blur-sm" onclick="closeProcessModal()"></div>
    <div class="relative w-full max-w-md bg-white rounded-3xl shadow-2xl border border-gray-100 overflow-hidden">

        <div class="p-6 border-b border-gray-100 bg-gradient-to-r from-emerald-50 to-green-50">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 bg-emerald-100 rounded-full flex items-center justify-center">
                    <svg class="w-5 h-5 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h10a8 8 0 018 8v2M3 10l6 6m-6-6l6-6"/></svg>
                </div>
                <div>
                    <h3 class="font-black text-gray-900 text-lg">Process Refund</h3>
                    <p class="text-sm text-gray-500">This will process the refund</p>
                </div>
            </div>
        </div>

        <div class="p-6 space-y-4">
            <div class="bg-gray-50 border border-gray-200 rounded-2xl p-4 space-y-2.5">
                <div class="flex justify-between text-sm"><span class="text-gray-500">Order</span><span id="pm-order" class="font-bold text-gray-900"></span></div>
                <div class="flex justify-between text-sm"><span class="text-gray-500">Buyer</span><span id="pm-buyer" class="font-bold text-gray-900"></span></div>
                <div class="flex justify-between items-center pt-1 border-t border-gray-200 mt-1">
                    <span class="text-gray-500 text-sm font-bold">Amount to Refund</span>
                    <span id="pm-amount" class="font-black text-2xl text-emerald-700"></span>
                </div>
            </div>

            <div class="bg-gray-50 border border-gray-200 rounded-2xl p-4 space-y-2.5">
                <div class="flex justify-between text-sm"><span class="text-gray-500 font-bold">Send to:</span><span id="pm-refund-method" class="font-bold text-gray-900"></span></div>
                <div class="flex justify-between text-sm"><span class="text-gray-500 font-bold">Details:</span><span id="pm-refund-details" class="font-mono text-gray-900 select-all bg-white px-2 py-1 border border-gray-200 rounded"></span></div>
            </div>

            <div>
                <label class="block text-sm font-bold text-gray-700 mb-1.5">Admin Note (optional)</label>
                <textarea id="pm-notes" rows="2" maxlength="300" placeholder="Internal processing remarks…" class="w-full border border-gray-300 rounded-xl px-4 py-2.5 text-sm resize-none focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:border-transparent transition"></textarea>
            </div>

            <div class="flex items-start gap-2 text-xs text-blue-900 bg-blue-50 border border-blue-200 rounded-xl p-3">
                <svg class="w-4 h-4 text-blue-500 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12A9 9 0 113 12a9 9 0 0118 0z"/></svg>
                <span>The refund will be processed immediately. The buyer will be emailed when the refund is confirmed.</span>
            </div>
        </div>

        <div class="px-6 pb-6 flex gap-3 justify-end">
            <button onclick="closeProcessModal()" class="px-5 py-2.5 border border-gray-300 text-gray-700 font-bold rounded-xl hover:bg-gray-50 transition text-sm">Cancel</button>
            <button id="pm-submit" onclick="submitProcess()" class="px-6 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white font-bold rounded-xl shadow-sm transition text-sm flex items-center gap-2 disabled:opacity-60">
                <span id="pm-label">Confirm Refund</span>
                <svg id="pm-spinner" class="w-4 h-4 animate-spin hidden" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8z"></path></svg>
            </button>
        </div>
    </div>
</div>

<!-- Toast -->
<div id="toast-box" class="fixed bottom-6 right-6 flex flex-col gap-2 pointer-events-none" style="z-index: 10000;"></div>

<script>
let _processId = null;

document.addEventListener('DOMContentLoaded', () => {
    document.body.appendChild(document.getElementById('process-modal'));
    document.body.appendChild(document.getElementById('toast-box'));
});

function openProcessModal(id, order, amount, buyer, method, details) {
    _processId = id;
    document.getElementById('pm-order').textContent  = '#' + order;
    document.getElementById('pm-buyer').textContent  = buyer;
    document.getElementById('pm-amount').textContent = '₹' + parseFloat(amount).toLocaleString('en-IN', {minimumFractionDigits:2});
    
    document.getElementById('pm-refund-method').textContent = method || 'N/A';
    document.getElementById('pm-refund-details').textContent = details || 'N/A';
    
    document.getElementById('pm-notes').value        = '';
    document.getElementById('process-modal').classList.remove('hidden');
    document.body.style.overflow = 'hidden';
}
function closeProcessModal() {
    document.getElementById('process-modal').classList.add('hidden');
    document.body.style.overflow = '';
}

async function submitProcess() {
    const notes  = document.getElementById('pm-notes').value.trim();
    const btn    = document.getElementById('pm-submit');
    const label  = document.getElementById('pm-label');
    const spin   = document.getElementById('pm-spinner');
    btn.disabled = true; label.textContent = 'Processing…'; spin.classList.remove('hidden');

    try {
        const res  = await fetch('<?= BASE_URL ?>/admin/cancellations/refunds/process', {
            method: 'POST',
            headers: {'Content-Type':'application/json','X-Requested-With':'XMLHttpRequest'},
            body: JSON.stringify({refund_request_id: _processId, admin_notes: notes}),
        });
        const data = await res.json();
        if (data.success) {
            toast(data.message + (data.cf_status ? ' [' + data.cf_status + ']' : ''), 'success');
            closeProcessModal();
            setTimeout(() => location.reload(), 2000);
        } else {
            toast(data.message || 'Error.', 'error');
            btn.disabled = false; label.textContent = 'Confirm Refund'; spin.classList.add('hidden');
        }
    } catch(e) {
        toast('Network error.', 'error');
        btn.disabled = false; label.textContent = 'Confirm Refund'; spin.classList.add('hidden');
    }
}

function toast(msg, type='success') {
    const c  = document.getElementById('toast-box');
    const bg = type === 'success' ? 'bg-emerald-600' : 'bg-red-600';
    const ic = type === 'success'
        ? '<svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>'
        : '<svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01M21 12A9 9 0 113 12a9 9 0 0118 0z"/></svg>';
    const el = document.createElement('div');
    el.className = `pointer-events-auto px-5 py-3 rounded-xl shadow-lg text-sm font-semibold flex items-center gap-2 text-white ${bg} transform translate-y-4 opacity-0 transition-all duration-300`;
    el.innerHTML = ic + `<span>${msg}</span>`;
    c.appendChild(el);
    requestAnimationFrame(() => el.classList.remove('translate-y-4','opacity-0'));
    setTimeout(() => { el.classList.add('opacity-0','translate-y-4'); setTimeout(() => el.remove(), 300); }, 4500);
}

function exportRefundsCSV() {
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
    link.setAttribute("download", "refunds_export_<?= date('Y-m-d') ?>.csv");
    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);
}
</script>
</div>
