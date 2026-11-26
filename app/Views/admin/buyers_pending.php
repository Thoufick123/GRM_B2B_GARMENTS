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
                            <th class="px-6 py-4">Shop Photos</th>
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
                                        <?php elseif (!empty($buyer['instagram_link'])): ?>
                                            <a href="<?= htmlspecialchars($buyer['instagram_link']) ?>" target="_blank" class="inline-flex items-center px-2 py-0.5 bg-pink-50 text-[#F25996] hover:bg-pink-100 rounded text-xs font-semibold mr-2 transition-colors">
                                                <svg class="w-3 h-3 mr-1" viewBox="0 0 24 24" fill="currentColor"><path d="M12 2.163c3.204 0 3.584.012 4.85.07 3.252.148 4.771 1.691 4.919 4.919.058 1.265.069 1.645.069 4.849 0 3.205-.012 3.584-.069 4.849-.149 3.225-1.664 4.771-4.919 4.919-1.266.058-1.644.07-4.85.07-3.204 0-3.584-.012-4.849-.07-3.26-.149-4.771-1.699-4.919-4.92-.058-1.265-.07-1.644-.07-4.849 0-3.204.013-3.583.07-4.849.149-3.227 1.664-4.771 4.919-4.919 1.266-.057 1.645-.069 4.849-.069zM12 0C8.741 0 8.333.014 7.053.072 2.695.272.273 2.69.073 7.052.014 8.333 0 8.741 0 12c0 3.259.014 3.668.072 4.948.2 4.358 2.618 6.78 6.98 6.98C8.333 23.986 8.741 24 12 24c3.259 0 3.668-.014 4.948-.072 4.354-.2 6.782-2.618 6.979-6.98.059-1.28.073-1.689.073-4.948 0-3.259-.014-3.667-.072-4.947-.196-4.354-2.617-6.78-6.979-6.98C15.668.014 15.259 0 12 0zm0 5.838a6.162 6.162 0 100 12.324 6.162 6.162 0 000-12.324zM12 16a4 4 0 110-8 4 4 0 010 8zm6.406-11.845a1.44 1.44 0 100 2.881 1.44 1.44 0 000-2.881z"/></svg>
                                                Instagram Profile
                                            </a>
                                        <?php else: ?>
                                            <span class="inline-block px-2 py-0.5 bg-yellow-50 text-yellow-700 rounded mr-2">PAN</span>
                                            <?= htmlspecialchars($buyer['pan_number'] ?? 'N/A') ?>
                                        <?php endif; ?>
                                    </div>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="flex items-center space-x-2">
                                        <svg class="w-5 h-5 <?= !empty($buyer['photo_count']) ? 'text-[#F25996]' : 'text-gray-400' ?>" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                                        <span class="text-sm <?= !empty($buyer['photo_count']) ? 'font-medium text-gray-800' : 'text-gray-500 italic' ?>">
                                            <?= !empty($buyer['photo_count']) ? htmlspecialchars($buyer['photo_count']) . ' Photo(s)' : 'No Photos (Optional)' ?>
                                        </span>
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
                                    <?php if (\Core\Session::get('user_role') === 'super_admin' || $this->hasPermission('pending_buyers', 'delete')): ?>
                                    <button type="button"
                                        onclick="promptDeleteBuyer(<?= $buyer['id'] ?>, '<?= addslashes(htmlspecialchars($buyer['business_name'], ENT_QUOTES)) ?>', 'pending')"
                                        class="text-red-500 hover:text-red-700 p-1.5 transition-colors inline-block align-middle cursor-pointer" title="Delete Buyer Request">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
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
                                        <?php elseif (!empty($buyer['instagram_link'])): ?>
                                            <a href="<?= htmlspecialchars($buyer['instagram_link']) ?>" target="_blank" class="inline-flex items-center px-2 py-0.5 bg-pink-50 text-[#F25996] hover:bg-pink-100 rounded text-xs font-semibold mr-2 transition-colors">
                                                <svg class="w-3 h-3 mr-1" viewBox="0 0 24 24" fill="currentColor"><path d="M12 2.163c3.204 0 3.584.012 4.85.07 3.252.148 4.771 1.691 4.919 4.919.058 1.265.069 1.645.069 4.849 0 3.205-.012 3.584-.069 4.849-.149 3.225-1.664 4.771-4.919 4.919-1.266.058-1.644.07-4.85.07-3.204 0-3.584-.012-4.849-.07-3.26-.149-4.771-1.699-4.919-4.92-.058-1.265-.07-1.644-.07-4.849 0-3.204.013-3.583.07-4.849.149-3.227 1.664-4.771 4.919-4.919 1.266-.057 1.645-.069 4.849-.069zM12 0C8.741 0 8.333.014 7.053.072 2.695.272.273 2.69.073 7.052.014 8.333 0 8.741 0 12c0 3.259.014 3.668.072 4.948.2 4.358 2.618 6.78 6.98 6.98C8.333 23.986 8.741 24 12 24c3.259 0 3.668-.014 4.948-.072 4.354-.2 6.782-2.618 6.979-6.98.059-1.28.073-1.689.073-4.948 0-3.259-.014-3.667-.072-4.947-.196-4.354-2.617-6.78-6.979-6.98C15.668.014 15.259 0 12 0zm0 5.838a6.162 6.162 0 100 12.324 6.162 6.162 0 000-12.324zM12 16a4 4 0 110-8 4 4 0 010 8zm6.406-11.845a1.44 1.44 0 100 2.881 1.44 1.44 0 000-2.881z"/></svg>
                                                Instagram Profile
                                            </a>
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
                                    <?php if (\Core\Session::get('user_role') === 'super_admin' || $this->hasPermission('pending_buyers', 'delete') || $this->hasPermission('all_buyers', 'delete')): ?>
                                    <button type="button"
                                        onclick="promptDeleteBuyer(<?= $buyer['id'] ?>, '<?= addslashes(htmlspecialchars($buyer['business_name'], ENT_QUOTES)) ?>', 'rejected')"
                                        class="p-1.5 text-red-500 hover:text-red-700 transition-colors inline-block align-middle cursor-pointer" title="Delete Rejected Registration">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
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

function promptDeleteBuyer(userId, businessName, tab) {
    Swal.fire({
        title: 'Delete Buyer Registration?',
        html: `Are you sure you want to permanently delete registration for <strong class="text-gray-900">${businessName}</strong>?<br><span class="text-xs text-red-500 font-medium mt-1 block">This action cannot be undone. All related requests, profiles and documents will be permanently removed.</span>`,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#dc2626',
        cancelButtonColor: '#6b7280',
        confirmButtonText: 'Yes, permanently delete',
        cancelButtonText: 'Cancel',
        customClass: { popup: 'rounded-2xl shadow-xl border border-gray-100' }
    }).then((result) => {
        if (result.isConfirmed) {
            Swal.fire({
                title: 'Deleting...',
                text: 'Please wait while the buyer record is removed.',
                allowOutsideClick: false,
                didOpen: () => { Swal.showLoading(); }
            });

            fetch('<?= BASE_URL ?>/admin/buyers/delete', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json'
                },
                body: JSON.stringify({ user_id: userId, redirect_to: '<?= BASE_URL ?>/admin/buyers/pending?tab=' + tab })
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Deleted!',
                        text: data.message || 'Buyer deleted successfully.',
                        confirmButtonColor: '#F25996'
                    }).then(() => {
                        window.location.href = '<?= BASE_URL ?>/admin/buyers/pending' + (tab === 'rejected' ? '?tab=rejected' : '');
                    });
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Failed',
                        text: data.message || 'Failed to delete buyer.',
                        confirmButtonColor: '#F25996'
                    });
                }
            })
            .catch(err => {
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'An unexpected network error occurred.',
                    confirmButtonColor: '#F25996'
                });
            });
        }
    });
}
</script>
</div>
