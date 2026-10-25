<div class="w-full">
    <!-- Header Section -->
    <div class="mb-8 relative z-10">
        <!-- Breadcrumbs -->
        <div class="text-sm font-medium text-gray-400 mb-2 font-serif tracking-wide">
            Dashboard <span class="mx-1 text-gray-300">›</span> Buyer Management <span class="mx-1 text-gray-300">›</span> <span class="text-brand-700">Registered Buyers</span>
        </div>
        
        <div class="flex justify-between items-center mb-6 w-full">
            <h2 class="text-5xl font-display font-extrabold text-gray-900 tracking-tight"><?= htmlspecialchars($title) ?></h2>
            
            <div class="flex items-center space-x-3 ml-auto">
                <button onclick="const u=new URL(window.location);u.searchParams.set('refresh','1');window.location.href=u.toString();" class="bg-white hover:bg-gray-50 text-gray-700 px-6 py-3 rounded-full font-bold text-sm shadow-sm border border-gray-200 transition-all flex items-center uppercase tracking-widest">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path></svg>
                    Refresh
                </button>
                <button onclick="exportBuyersCSV()" class="bg-[#F25996] hover:bg-[#e04481] text-white px-6 py-3 rounded-full font-bold text-sm shadow-sm transition-all flex items-center uppercase tracking-widest">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path></svg>
                    Export Excel
                </button>
            </div>
        </div>
    </div>

    <!-- Stats Overview -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-5 gap-6 mb-8">
        <!-- Total Customers -->
        <div class="bg-white rounded-2xl p-6 shadow-sm border border-gray-100 flex flex-col justify-between">
            <div>
                <div class="w-12 h-12 bg-indigo-50 rounded-xl flex items-center justify-center text-indigo-600 mb-4">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
                </div>
                <h3 class="text-xs font-bold text-gray-400 uppercase tracking-wider mb-1">Total Buyers</h3>
            </div>
            <p class="text-3xl font-display font-black text-gray-900"><?= number_format($stats['total_buyers']) ?></p>
        </div>

        <!-- Active Customers -->
        <div class="bg-white rounded-2xl p-6 shadow-sm border border-gray-100 flex flex-col justify-between">
            <div>
                <div class="w-12 h-12 bg-emerald-50 rounded-xl flex items-center justify-center text-emerald-600 mb-4">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path></svg>
                </div>
                <h3 class="text-xs font-bold text-gray-400 uppercase tracking-wider mb-1">Active Buyers</h3>
            </div>
            <p class="text-3xl font-display font-black text-gray-900"><?= number_format($stats['active_buyers']) ?></p>
        </div>

        <!-- Total Orders -->
        <div class="bg-white rounded-2xl p-6 shadow-sm border border-gray-100 flex flex-col justify-between">
            <div>
                <div class="w-12 h-12 bg-amber-50 rounded-xl flex items-center justify-center text-amber-600 mb-4">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path></svg>
                </div>
                <h3 class="text-xs font-bold text-gray-400 uppercase tracking-wider mb-1">Total Orders</h3>
            </div>
            <p class="text-3xl font-display font-black text-gray-900"><?= number_format($stats['total_orders']) ?></p>
        </div>

        <!-- Delivered Revenue -->
        <div class="bg-white rounded-2xl p-6 shadow-sm border border-gray-100 flex flex-col justify-between">
            <div>
                <div class="w-12 h-12 bg-stone-50 rounded-xl flex items-center justify-center text-stone-700 mb-4">
                    <span class="text-2xl font-bold font-sans">₹</span>
                </div>
                <h3 class="text-xs font-bold text-gray-400 uppercase tracking-wider mb-1">Delivered Revenue</h3>
            </div>
            <p class="text-3xl font-display font-black text-gray-900">₹<?= number_format($stats['delivered_revenue'], 2) ?></p>
        </div>

        <!-- Total Rejected Buyers -->
        <div class="bg-white rounded-2xl p-6 shadow-sm border border-gray-100 flex flex-col justify-between">
            <div>
                <div class="w-12 h-12 bg-red-50 rounded-xl flex items-center justify-center text-red-500 mb-4">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"></path></svg>
                </div>
                <h3 class="text-xs font-bold text-gray-400 uppercase tracking-wider mb-1">Total Rejected</h3>
            </div>
            <p class="text-3xl font-display font-black text-gray-900"><?= number_format($stats['rejected_buyers'] ?? 0) ?></p>
        </div>
    </div>

    <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
        <?php if (empty($buyers)): ?>
            <div class="p-8 text-center text-gray-500">
                <svg class="w-12 h-12 mx-auto text-gray-300 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path></svg>
                <p>No approved buyers found.</p>
            </div>
        <?php else: ?>
            <div class="overflow-x-auto admin-table-scroll pb-2">
                <table class="w-full text-left border-collapse min-w-[1050px]">
                    <thead>
                        <tr class="bg-gray-50 text-gray-500 text-xs uppercase tracking-wider border-b border-gray-100">
                            <th class="px-6 py-4 font-medium">Business Details</th>
                            <th class="px-6 py-4 font-medium">Contact Person</th>
                            <th class="px-6 py-4 font-medium">Joined Date</th>
                            <th class="px-6 py-4 font-medium text-center">Total Orders</th>
                            <th class="px-6 py-4 font-medium text-right">Delivered Spend</th>
                            <th class="px-6 py-4 font-medium text-center">Status</th>
                            <th class="px-6 py-4 font-medium text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        <?php foreach($buyers as $buyer): ?>
                        <tr class="hover:bg-gray-50/50 transition-colors">
                            <td class="px-6 py-4">
                                <div class="font-medium text-gray-900"><?= htmlspecialchars($buyer['business_name'] ?? 'N/A') ?></div>
                                <div class="flex items-center space-x-2 mt-1">
                                    <?php if($buyer['gst_number']): ?>
                                        <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-medium bg-green-100 text-green-800 border border-green-200">
                                            GST: <?= htmlspecialchars($buyer['gst_number']) ?>
                                        </span>
                                    <?php endif; ?>
                                    <?php if($buyer['pan_number']): ?>
                                        <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-medium bg-amber-50 text-amber-800 border border-amber-200">
                                            PAN: <?= htmlspecialchars($buyer['pan_number']) ?>
                                        </span>
                                    <?php endif; ?>
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                <div class="font-medium text-gray-900"><?= htmlspecialchars($buyer['name'] ?? 'N/A') ?></div>
                                <div class="text-sm text-gray-500"><?= htmlspecialchars($buyer['email']) ?></div>
                                <div class="text-sm text-gray-500"><?= htmlspecialchars($buyer['phone']) ?></div>
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-500">
                                <?= date('M d, Y', strtotime($buyer['created_at'])) ?>
                            </td>
                            <td class="px-6 py-4 text-center font-bold text-gray-800">
                                <?= number_format($buyer['total_orders'] ?? 0) ?>
                            </td>
                            <td class="px-6 py-4 text-right font-bold text-gray-900">
                                ₹<?= number_format($buyer['delivered_spend'] ?? 0, 2) ?>
                            </td>
                            <td class="px-6 py-4 text-center">
                                <?php
                                    $statusColors = [
                                        'active' => 'bg-green-100 text-green-800 border-green-200',
                                        'blocked' => 'bg-red-100 text-red-800 border-red-200',
                                        'approved' => 'bg-brand-100 text-brand-800 border-brand-200',
                                    ];
                                    $colorClass = $statusColors[$buyer['status']] ?? 'bg-gray-100 text-gray-800';
                                ?>
                                <span class="px-2.5 py-1 rounded-full text-[11px] font-bold tracking-wide border <?= $colorClass ?>">
                                    <?= strtoupper($buyer['status']) ?>
                                </span>
                            </td>
                            <td class="px-6 py-4 text-right flex justify-end items-center gap-2">
                                <a href="<?= BASE_URL ?>/admin/buyers/view?id=<?= $buyer['id'] ?>" class="inline-flex items-center justify-center p-1 text-emerald-600 hover:text-emerald-800 transition-colors" title="View Details">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                </a>
                                <?php if ($this->hasPermission('all_buyers', 'edit')): ?>
                                <form action="<?= BASE_URL ?>/admin/buyers/toggle-block" method="POST" class="inline-block m-0">
                                    <input type="hidden" name="user_id" value="<?= $buyer['id'] ?>">
                                    <?php if($buyer['status'] === 'blocked'): ?>
                                        <input type="hidden" name="action" value="unblock">
                                        <button type="submit" class="inline-flex items-center px-3 py-1.5 bg-green-50 text-green-700 hover:bg-green-100 rounded-lg text-sm font-medium transition-colors">
                                            Unblock
                                        </button>
                                    <?php else: ?>
                                        <input type="hidden" name="action" value="block">
                                        <button type="submit" class="inline-flex items-center px-3 py-1.5 bg-red-50 text-red-700 hover:bg-red-100 rounded-lg text-sm font-medium transition-colors" onclick="return confirm('Are you sure you want to block this buyer? They will not be able to view prices.')">
                                            Block
                                        </button>
                                    <?php endif; ?>
                                </form>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</div>

<script>
function exportBuyersCSV() {
    let table = document.querySelector("table");
    if (!table) return;
    
    let rows = Array.from(table.querySelectorAll("tr"));
    if (rows.length === 0) {
        alert("No buyers to export.");
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
        alert("No buyer data available to export.");
        return;
    }
    
    let csvString = "\uFEFF" + csvRows.join("\r\n");
    let blob = new Blob([csvString], { type: "text/csv;charset=utf-8;" });
    let url = URL.createObjectURL(blob);
    let link = document.createElement("a");
    link.setAttribute("href", url);
    link.setAttribute("download", "buyers_export_<?= date('Y-m-d') ?>.csv");
    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);
    URL.revokeObjectURL(url);
}
</script>
