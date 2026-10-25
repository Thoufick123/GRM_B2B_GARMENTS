<div class="w-full">
    <!-- Header Section -->
    <div class="mb-8 relative z-10">
        <!-- Breadcrumbs -->
        <div class="text-sm font-medium text-gray-400 mb-2 font-serif tracking-wide">
            Dashboard <span class="mx-1 text-gray-300">›</span> Buyer Management <span class="mx-1 text-gray-300">›</span> <span class="text-brand-700">Pending Approvals</span>
        </div>
        
        <div class="flex justify-between items-center mb-6 w-full">
            <div class="flex items-center gap-3">
                <h2 class="text-5xl font-display font-extrabold text-gray-900 tracking-tight">Pending Approvals</h2>
                <span class="bg-brand-100 text-brand-700 text-sm font-bold px-3.5 py-1 rounded-full">
                    <?= count($buyers) ?> Requests
                </span>
            </div>
            
            <div class="flex items-center space-x-3 ml-auto">
                <button onclick="window.location.reload()" class="bg-white hover:bg-gray-50 text-gray-700 px-6 py-3 rounded-full font-bold text-sm shadow-sm border border-gray-200 transition-all flex items-center uppercase tracking-widest">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path></svg>
                    Refresh
                </button>
                <button onclick="exportPendingBuyersCSV()" class="bg-[#F25996] hover:bg-[#e04481] text-white px-6 py-3 rounded-full font-bold text-sm shadow-sm transition-all flex items-center uppercase tracking-widest">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path></svg>
                    Export Excel
                </button>
            </div>
        </div>
    </div>

    <div class="mb-6 flex space-x-6 border-b border-gray-200">
        <button onclick="switchTab('pending')" id="tab-btn-pending" class="pb-3 text-sm font-bold text-brand-700 border-b-2 border-brand-700 transition-colors uppercase tracking-wider">
            Pending Requests (<?= count($buyers) ?>)
        </button>
        <button onclick="switchTab('rejected')" id="tab-btn-rejected" class="pb-3 text-sm font-bold text-gray-500 border-b-2 border-transparent hover:text-gray-700 hover:border-gray-300 transition-colors uppercase tracking-wider">
            Rejected Requests (<?= count($rejectedBuyers ?? []) ?>)
        </button>
    </div>

    <!-- Pending Tab -->
    <div id="tab-pending" class="bg-white border border-gray-200 rounded-xl shadow-sm overflow-hidden">
        <?php if (empty($buyers)): ?>
            <div class="p-12 text-center text-gray-500">
                <svg class="w-12 h-12 mx-auto text-gray-300 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"></path></svg>
                <p>No pending registration requests at the moment.</p>
            </div>
        <?php else: ?>
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-white border-b border-gray-200 text-xs uppercase text-gray-500 font-semibold">
                            <th class="px-6 py-4">Business Details</th>
                            <th class="px-6 py-4">Verification Type</th>
                            <th class="px-6 py-4">Contact</th>
                            <th class="px-6 py-4">Date</th>
                            <th class="px-6 py-4 text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200">
                        <?php foreach ($buyers as $buyer): ?>
                            <tr class="hover:bg-gray-50 transition-colors">
                                <td class="px-6 py-4">
                                    <div class="font-medium text-gray-900"><?= htmlspecialchars($buyer['business_name']) ?></div>
                                    <div class="text-[10px] font-bold text-gray-400 mt-0.5 tracking-wider mb-1">ID: <?= htmlspecialchars($buyer['unique_buyer_id'] ?? 'N/A') ?></div>
                                    <div class="text-xs text-gray-500 mt-1">
                                        <?php if ($buyer['gst_flag']): ?>
                                            <span class="inline-block px-2 py-0.5 bg-blue-50 text-blue-700 rounded mr-2">GST</span>
                                            <?= htmlspecialchars($buyer['gst_number']) ?>
                                        <?php else: ?>
                                            <span class="inline-block px-2 py-0.5 bg-yellow-50 text-yellow-700 rounded mr-2">PAN</span>
                                            <?= htmlspecialchars($buyer['pan_number'] ?? 'N/A') ?>
                                        <?php endif; ?>
                                    </div>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="flex items-center space-x-2">
                                        <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                                        <span class="text-sm text-gray-600">Shop Photos Attached</span>
                                    </div>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="text-sm text-gray-900"><?= htmlspecialchars($buyer['email']) ?></div>
                                    <div class="text-sm text-gray-500"><?= htmlspecialchars($buyer['phone']) ?></div>
                                </td>
                                <td class="px-6 py-4 text-sm text-gray-500">
                                    <?= date('M j, Y', strtotime($buyer['created_at'])) ?>
                                </td>
                                <td class="px-6 py-4 text-right space-x-2">
                                    <a href="<?= BASE_URL ?>/admin/buyers/view?id=<?= $buyer['id'] ?>&from=pending" class="text-emerald-600 hover:text-emerald-800 transition-colors inline-block align-middle" title="View Details">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                    </a>
                                    <?php if ($this->hasPermission('pending_buyers', 'edit')): ?>
                                    <form action="<?= BASE_URL ?>/admin/buyers/approve" method="POST" class="inline">
                                        <input type="hidden" name="user_id" value="<?= $buyer['id'] ?>">
                                        <button type="submit" class="inline-flex items-center px-3 py-1.5 bg-green-600 text-white text-xs font-medium rounded hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500">
                                            Approve
                                        </button>
                                    </form>
                                    <button type="button"
                                        onclick="promptReject(<?= $buyer['id'] ?>, '<?= addslashes(htmlspecialchars($buyer['business_name'], ENT_QUOTES)) ?>')"
                                        class="text-red-600 hover:text-red-800 text-sm font-medium ml-1">
                                        Reject
                                    </button>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>

<?php
$currentPage   = $currentPage  ?? 1;
$totalPages    = $totalPages   ?? 1;
$totalPending  = $totalPending ?? count($buyers);
$perPage       = $perPage      ?? 20;
$paginationFrom = min($totalPending, ($currentPage - 1) * $perPage + 1);
$paginationTo   = min($totalPending, $currentPage * $perPage);
function pendingPageUrl(int $page): string {
    $base = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
    $qs   = $_GET;
    $qs['page'] = $page;
    unset($qs['tab']);
    return $base . '?' . http_build_query($qs);
}
?>
<?php if ($totalPages > 1): ?>
<div class="flex items-center justify-between mt-4 mb-4 px-1">
    <p class="text-sm text-gray-500 font-medium">
        Pending: <span class="font-bold text-gray-800"><?= $paginationFrom ?>–<?= $paginationTo ?></span>
        of <span class="font-bold text-gray-800"><?= number_format($totalPending) ?></span>
    </p>
    <div class="flex items-center gap-1.5">
        <?php if ($currentPage > 1): ?>
            <a href="<?= pendingPageUrl($currentPage - 1) ?>" class="w-9 h-9 flex items-center justify-center rounded-full border border-gray-200 bg-white text-gray-600 hover:bg-gray-50 transition text-sm">‹</a>
        <?php endif; ?>
        <?php for ($p = max(1,$currentPage-2); $p <= min($totalPages,$currentPage+2); $p++): ?>
            <a href="<?= pendingPageUrl($p) ?>"
               class="w-9 h-9 flex items-center justify-center rounded-full text-xs font-bold transition
                      <?= $p === $currentPage ? 'bg-[#F25996] text-white border border-[#F25996]' : 'border border-gray-200 bg-white text-gray-700 hover:bg-gray-50' ?>">
                <?= $p ?>
            </a>
        <?php endfor; ?>
        <?php if ($currentPage < $totalPages): ?>
            <a href="<?= pendingPageUrl($currentPage + 1) ?>" class="w-9 h-9 flex items-center justify-center rounded-full border border-gray-200 bg-white text-gray-600 hover:bg-gray-50 transition text-sm">›</a>
        <?php endif; ?>
    </div>
</div>
<?php endif; ?>

<!-- Rejected Tab -->
    <div id="tab-rejected" class="bg-white border border-gray-200 rounded-xl shadow-sm overflow-hidden hidden">
        <?php if (empty($rejectedBuyers)): ?>
            <div class="p-12 text-center text-gray-500">
                <svg class="w-12 h-12 mx-auto text-gray-300 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                <p>No rejected registration requests.</p>
            </div>
        <?php else: ?>
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-white border-b border-gray-200 text-xs uppercase text-gray-500 font-semibold">
                            <th class="px-6 py-4">Business Details</th>
                            <th class="px-6 py-4">Contact</th>
                            <th class="px-6 py-4">Rejection Reason</th>
                            <th class="px-6 py-4">Date</th>
                            <th class="px-6 py-4 text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200">
                        <?php foreach ($rejectedBuyers as $buyer): ?>
                            <tr class="hover:bg-gray-50 transition-colors">
                                <td class="px-6 py-4">
                                    <div class="font-medium text-gray-900"><?= htmlspecialchars($buyer['business_name']) ?></div>
                                    <div class="text-[10px] font-bold text-gray-400 mt-0.5 tracking-wider mb-1">ID: <?= htmlspecialchars($buyer['unique_buyer_id'] ?? 'N/A') ?></div>
                                    <div class="text-xs text-gray-500 mt-1">
                                        <?php if ($buyer['gst_flag']): ?>
                                            <span class="inline-block px-2 py-0.5 bg-blue-50 text-blue-700 rounded mr-2">GST</span>
                                            <?= htmlspecialchars($buyer['gst_number']) ?>
                                        <?php else: ?>
                                            <span class="inline-block px-2 py-0.5 bg-yellow-50 text-yellow-700 rounded mr-2">PAN</span>
                                            <?= htmlspecialchars($buyer['pan_number'] ?? 'N/A') ?>
                                        <?php endif; ?>
                                    </div>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="text-sm text-gray-900"><?= htmlspecialchars($buyer['email']) ?></div>
                                    <div class="text-sm text-gray-500"><?= htmlspecialchars($buyer['phone']) ?></div>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="text-sm text-red-600 font-medium line-clamp-2 max-w-xs" title="<?= htmlspecialchars($buyer['rejection_reason'] ?? 'Not specified') ?>">
                                        <?= htmlspecialchars($buyer['rejection_reason'] ?? 'Not specified') ?>
                                    </div>
                                </td>
                                <td class="px-6 py-4 text-sm text-gray-500">
                                    <?= date('M j, Y', strtotime($buyer['created_at'])) ?>
                                </td>
                                <td class="px-6 py-4 text-right space-x-2">
                                    <a href="<?= BASE_URL ?>/admin/buyers/view?id=<?= $buyer['id'] ?>&from=rejected" class="p-1.5 text-emerald-600 hover:text-emerald-800 transition-colors inline-block align-middle" title="View Details">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>

<!-- Rejection Form (Hidden) -->
<form id="reject-form" action="<?= BASE_URL ?>/admin/buyers/reject" method="POST" style="display: none;">
    <input type="hidden" name="user_id" id="reject-user-id">
    <input type="hidden" name="rejection_reason" id="reject-reason-input">
</form>

<script>
function promptReject(userId, businessName) {
    Swal.fire({
        html: `
            <div class="text-center">
                <div class="w-14 h-14 mx-auto rounded-2xl bg-red-50 border border-red-200 text-red-600 flex items-center justify-center mb-3.5 shadow-sm">
                    <svg class="w-7 h-7 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                    </svg>
                </div>
                <h3 class="text-xl font-bold text-gray-900 font-display mb-1">Reject Registration</h3>
                <p class="text-xs text-gray-500 mb-4">
                    Rejecting application for <span class="font-bold text-gray-800 bg-gray-100 px-2 py-0.5 rounded border border-gray-200">${businessName}</span>
                </p>

                <div class="text-left mb-3">
                    <label class="block text-[11px] font-bold text-gray-500 uppercase tracking-wider mb-2">Quick Reasons</label>
                    <div class="flex flex-wrap gap-1.5 mb-3">
                        <button type="button" onclick="setRejectReason('Unclear or invalid shop photos provided.')" class="text-[11px] font-medium px-2.5 py-1 bg-gray-50 hover:bg-red-50 hover:text-red-700 hover:border-red-200 border border-gray-200 rounded-full text-gray-600 transition-all cursor-pointer">📸 Unclear Shop Photos</button>
                        <button type="button" onclick="setRejectReason('Invalid or expired GST/PAN document.')" class="text-[11px] font-medium px-2.5 py-1 bg-gray-50 hover:bg-red-50 hover:text-red-700 hover:border-red-200 border border-gray-200 rounded-full text-gray-600 transition-all cursor-pointer">📄 Invalid GST/PAN</button>
                        <button type="button" onclick="setRejectReason('Business details and tax registration do not match.')" class="text-[11px] font-medium px-2.5 py-1 bg-gray-50 hover:bg-red-50 hover:text-red-700 hover:border-red-200 border border-gray-200 rounded-full text-gray-600 transition-all cursor-pointer">🏢 Business Mismatch</button>
                        <button type="button" onclick="setRejectReason('Incomplete shop address or contact verification failed.')" class="text-[11px] font-medium px-2.5 py-1 bg-gray-50 hover:bg-red-50 hover:text-red-700 hover:border-red-200 border border-gray-200 rounded-full text-gray-600 transition-all cursor-pointer">📍 Incomplete Address</button>
                    </div>
                </div>

                <div class="text-left">
                    <label class="block text-[11px] font-bold text-gray-500 uppercase tracking-wider mb-1.5">Reason for Rejection <span class="text-red-500">*</span></label>
                    <textarea id="swal-rejection-reason" rows="3" placeholder="Please enter the reason for rejection..." class="w-full text-xs text-gray-800 bg-gray-50 border border-gray-200 rounded-xl p-3 focus:outline-none focus:bg-white focus:border-red-500 focus:ring-4 focus:ring-red-100 transition-all resize-none"></textarea>
                    <p class="text-[11px] text-gray-400 mt-1">This reason will be recorded and emailed to the applicant.</p>
                </div>
            </div>
        `,
        showCancelButton: true,
        confirmButtonText: 'Confirm Rejection',
        cancelButtonText: 'Cancel',
        buttonsStyling: false,
        customClass: {
            popup: 'rounded-2xl p-6 sm:p-7 shadow-2xl border border-gray-100 bg-white max-w-md w-full',
            actions: 'flex items-center justify-end gap-2.5 mt-5 pt-3 border-t border-gray-100 w-full',
            confirmButton: 'inline-flex items-center justify-center px-5 py-2.5 font-bold text-xs text-white bg-red-600 hover:bg-red-700 active:scale-95 rounded-xl shadow-md transition-all cursor-pointer',
            cancelButton: 'inline-flex items-center justify-center px-4 py-2.5 font-semibold text-xs text-gray-600 bg-gray-100 hover:bg-gray-200 active:scale-95 rounded-xl transition-all cursor-pointer'
        },
        preConfirm: () => {
            const reason = document.getElementById('swal-rejection-reason')?.value.trim();
            if (!reason) {
                Swal.showValidationMessage('Please provide a reason for rejection.');
                return false;
            }
            return reason;
        }
    }).then((result) => {
        if (result.isConfirmed && result.value) {
            document.getElementById('reject-user-id').value = userId;
            document.getElementById('reject-reason-input').value = result.value;
            document.getElementById('reject-form').submit();
        }
    });
}

function setRejectReason(text) {
    const el = document.getElementById('swal-rejection-reason');
    if (el) {
        el.value = text;
        el.focus();
    }
}

function exportPendingBuyersCSV() {
    let table = document.querySelector("table");
    if (!table) return;
    
    let rows = Array.from(table.querySelectorAll("tr"));
    if (rows.length === 0) {
        alert("No pending buyers to export.");
        return;
    }
    
    let csvRows = [];
    rows.forEach(row => {
        let cols = Array.from(row.querySelectorAll("th, td"));
        if (cols.length === 0) return;
        if (cols.length === 1 && cols[0].getAttribute("colspan")) return;
        
        let exportCols = cols.length > 1 ? cols.slice(0, -1) : cols;
        let rowData = exportCols.map(col => {
            let text = col.innerText.replace(/\r?\n|\r/g, " ").replace(/"/g, '""').trim();
            return '"' + text + '"';
        });
        csvRows.push(rowData.join(","));
    });
    
    if (csvRows.length <= 1) {
        alert("No pending buyer data available to export.");
        return;
    }
    
    let csvString = "\uFEFF" + csvRows.join("\r\n");
    let blob = new Blob([csvString], { type: "text/csv;charset=utf-8;" });
    let url = URL.createObjectURL(blob);
    let link = document.createElement("a");
    link.setAttribute("href", url);
    link.setAttribute("download", "pending_buyers_export_<?= date('Y-m-d') ?>.csv");
    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);
    URL.revokeObjectURL(url);
}
function switchTab(tabId) {
    if (tabId === 'pending') {
        document.getElementById('tab-pending').classList.remove('hidden');
        document.getElementById('tab-rejected').classList.add('hidden');
        document.getElementById('tab-btn-pending').className = 'pb-3 text-sm font-bold text-brand-700 border-b-2 border-brand-700 transition-colors uppercase tracking-wider';
        document.getElementById('tab-btn-rejected').className = 'pb-3 text-sm font-bold text-gray-500 border-b-2 border-transparent hover:text-gray-700 hover:border-gray-300 transition-colors uppercase tracking-wider';
    } else {
        document.getElementById('tab-pending').classList.add('hidden');
        document.getElementById('tab-rejected').classList.remove('hidden');
        document.getElementById('tab-btn-rejected').className = 'pb-3 text-sm font-bold text-brand-700 border-b-2 border-brand-700 transition-colors uppercase tracking-wider';
        document.getElementById('tab-btn-pending').className = 'pb-3 text-sm font-bold text-gray-500 border-b-2 border-transparent hover:text-gray-700 hover:border-gray-300 transition-colors uppercase tracking-wider';
    }
}

// Automatically activate tab if query param (?tab=rejected) or hash (#rejected) is present
(function() {
    const params = new URLSearchParams(window.location.search);
    if (params.get('tab') === 'rejected' || window.location.hash === '#rejected') {
        switchTab('rejected');
    }
})();
</script>
</div>
