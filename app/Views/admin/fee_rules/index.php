<?php
/**
 * Admin - Fee Rules Index
 * Lists all configured fee rules with category badge, payment method, states, and quick actions.
 */
?>
<div class="w-full">
    <!-- Header Section -->
    <div class="mb-8 relative z-10">
        <!-- Breadcrumbs -->
        <div class="text-sm font-medium text-gray-400 mb-2 font-serif tracking-wide">
            Dashboard <span class="mx-1 text-gray-300">›</span> Settings <span class="mx-1 text-gray-300">›</span> <span class="text-brand-700">Fee Rules</span>
        </div>
        
        <div class="flex justify-between items-center mb-6 w-full">
            <h2 class="text-5xl font-display font-extrabold text-gray-900 tracking-tight">Fee Rules</h2>
            
            <div class="flex items-center space-x-3 ml-auto">
                <button onclick="window.location.reload()" class="bg-white hover:bg-gray-50 text-gray-700 px-6 py-3 rounded-full font-bold text-sm shadow-sm border border-gray-200 transition-all flex items-center uppercase tracking-widest">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path></svg>
                    Refresh
                </button>
                <button onclick="exportFeeRulesCSV()" class="bg-[#F25996] hover:bg-[#e04481] text-white px-6 py-3 rounded-full font-bold text-sm shadow-sm transition-all flex items-center uppercase tracking-widest cursor-pointer">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path></svg>
                    Export Excel
                </button>
                <?php if ($this->hasPermission('fee_rules', 'create')): ?>
                <a href="<?= BASE_URL ?>/admin/fee-rules/create" class="bg-[#F25996] hover:bg-[#e04481] text-white px-6 py-3 rounded-full font-bold text-sm shadow-md transition-all flex items-center uppercase tracking-widest">
                    <svg class="w-5 h-5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path></svg>
                    Add Fee Rule
                </a>
                <?php endif; ?>
            </div>
        </div>
    </div>

        <?php if ($flash = \Core\Session::getFlash('success')): ?>
            <div class="mb-4 p-4 rounded-lg bg-green-50 border border-green-200 text-green-800 text-sm font-medium"><?= htmlspecialchars($flash) ?></div>
        <?php endif; ?>
        <?php if ($flash = \Core\Session::getFlash('error')): ?>
            <div class="mb-4 p-4 rounded-lg bg-red-50 border border-red-200 text-red-800 text-sm font-medium"><?= htmlspecialchars($flash) ?></div>
        <?php endif; ?>

        <!-- Categories Quick-Add -->
        <div class="mb-6 bg-white border border-gray-200 rounded-xl p-5 shadow-sm">
            <h2 class="text-sm font-semibold text-gray-700 mb-3 uppercase tracking-wide">Fee Categories</h2>
            <div class="flex flex-wrap gap-2">
                <?php foreach ($categories as $cat): ?>
                    <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold <?= $cat['is_active'] ? 'bg-indigo-100 text-indigo-800' : 'bg-gray-100 text-gray-500 line-through' ?>">
                        <?= htmlspecialchars($cat['name']) ?>
                    </span>
                <?php endforeach; ?>
            </div>
        </div>

        <!-- Rules Table -->
        <div class="bg-white border border-gray-200 rounded-xl shadow-sm overflow-hidden">
            <?php if (empty($rules)): ?>
                <div class="text-center py-20 text-gray-400">
                    <svg class="mx-auto w-12 h-12 mb-4 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
                    <p class="font-medium text-gray-600">No fee rules yet.</p>
                    <?php if ($this->hasPermission('fee_rules', 'create')): ?>
                    <a href="<?= BASE_URL ?>/admin/fee-rules/create" class="mt-3 inline-block text-sm text-indigo-600 hover:underline font-medium">Create your first rule →</a>
                    <?php endif; ?>
                </div>
            <?php else: ?>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-100">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Fee Name</th>
                                <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Category</th>
                                <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Type / Value</th>
                                <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Payment</th>
                                <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">States</th>
                                <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Slabs</th>
                                <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Status</th>
                                <th class="px-6 py-3 text-right text-xs font-semibold text-gray-500 uppercase tracking-wider">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            <?php foreach ($rules as $rule):
                                $states = json_decode($rule['application_states'] ?? '[]', true) ?: [];
                                $slabs  = json_decode($rule['weight_slabs'] ?? '[]', true) ?: [];
                                $activeSlabs = array_filter($slabs, fn($s) => !empty($s['status']));
                            ?>
                                <tr class="hover:bg-gray-50 transition-colors">
                                    <td class="px-6 py-4">
                                        <span class="font-semibold text-gray-900 text-sm"><?= htmlspecialchars($rule['fee_name']) ?></span>
                                    </td>
                                    <td class="px-6 py-4">
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-indigo-100 text-indigo-800">
                                            <?= htmlspecialchars($rule['category_name']) ?>
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 text-sm text-gray-700">
                                        <?php if (!empty($slabs)): 
                                            $charges = array_column($slabs, 'charge');
                                            $minCharge = min($charges);
                                            $maxCharge = max($charges);
                                        ?>
                                            <?php if ($minCharge == $maxCharge): ?>
                                                <span class="font-medium text-teal-700">₹<?= number_format($minCharge, 2) ?></span>
                                            <?php else: ?>
                                                <span class="font-medium text-teal-700">₹<?= number_format($minCharge, 2) ?> – ₹<?= number_format($maxCharge, 2) ?></span>
                                            <?php endif; ?>
                                            <span class="text-gray-400 ml-1 text-xs">(Weight Based)</span>
                                        <?php elseif ($rule['fee_type'] === 'Percentage'): ?>
                                            <span class="font-medium"><?= $rule['flat_fee_value'] ?>%</span>
                                            <span class="text-gray-400 ml-1 text-xs">(<?= htmlspecialchars($rule['fee_type']) ?>)</span>
                                        <?php else: ?>
                                            <span class="font-medium">₹<?= number_format($rule['flat_fee_value'], 2) ?></span>
                                            <span class="text-gray-400 ml-1 text-xs">(<?= htmlspecialchars($rule['fee_type']) ?>)</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="px-6 py-4">
                                        <?php
                                            $pmColor = match($rule['payment_method']) {
                                                'COD' => 'bg-amber-100 text-amber-800',
                                                'Online' => 'bg-blue-100 text-blue-800',
                                                default => 'bg-gray-100 text-gray-700'
                                            };
                                        ?>
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold <?= $pmColor ?>">
                                            <?= htmlspecialchars($rule['payment_method']) ?>
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 text-sm text-gray-600 max-w-xs">
                                        <?php if (in_array('All', $states)): ?>
                                            <span class="text-green-700 font-medium">All States</span>
                                        <?php else: ?>
                                            <span title="<?= htmlspecialchars(implode(', ', $states)) ?>"><?= htmlspecialchars(implode(', ', array_slice($states, 0, 3))) ?><?= count($states) > 3 ? ' +' . (count($states) - 3) . ' more' : '' ?></span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="px-6 py-4 text-sm text-gray-600">
                                        <?php if (!empty($activeSlabs)): ?>
                                            <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-teal-50 text-teal-700">
                                                <?= count($activeSlabs) ?> active slab(s)
                                            </span>
                                        <?php else: ?>
                                            <span class="text-gray-400 text-xs">—</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="px-6 py-4">
                                        <?php if ($rule['active']): ?>
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-green-100 text-green-800">Active</span>
                                        <?php else: ?>
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-red-100 text-red-700">Inactive</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="px-6 py-4 text-right whitespace-nowrap">
                                        <div class="flex items-center justify-end space-x-2">
                                            <?php if ($this->hasPermission('fee_rules', 'edit')): ?>
                                            <a href="<?= BASE_URL ?>/admin/fee-rules/edit?id=<?= $rule['id'] ?>"
                                               class="inline-flex items-center justify-center p-1 text-blue-600 hover:text-blue-800 transition-colors" title="Edit Rule">
                                                <i class="bi bi-pencil-square text-lg"></i>
                                            </a>
                                            <?php endif; ?>
                                            <?php if ($this->hasPermission('fee_rules', 'delete')): ?>
                                            <form method="POST" action="<?= BASE_URL ?>/admin/fee-rules/delete" class="inline m-0 p-0"
                                                  onsubmit="return confirm('Delete rule \'<?= htmlspecialchars(addslashes($rule['fee_name'])) ?>\'? This cannot be undone.');">
                                                <input type="hidden" name="id" value="<?= $rule['id'] ?>">
                                                <button type="submit" class="inline-flex items-center justify-center p-1 text-red-600 hover:text-red-800 transition-colors" title="Delete Rule">
                                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                                </button>
                                            </form>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>

    </div>
</div>

<script>
// Build fee rules data from PHP for proper export (avoids reading rendered HTML text)
const feeRulesData = <?php
    $exportRules = [];
    foreach ($rules as $r) {
        $slabs  = json_decode($r['weight_slabs'] ?? '[]', true) ?: [];
        $states = json_decode($r['application_states'] ?? '[]', true) ?: [];
        $exportRules[] = [
            'fee_name'       => $r['fee_name'],
            'category_name'  => $r['category_name'],
            'fee_type'       => $r['fee_type'],
            'flat_fee_value' => $r['flat_fee_value'],
            'payment_method' => $r['payment_method'],
            'states'         => implode(', ', $states),
            'active'         => $r['active'] ? 'Active' : 'Inactive',
            'slabs'          => $slabs,
        ];
    }
    echo json_encode($exportRules);
?>;

function exportFeeRulesCSV() {
    const rows = [['Fee Name','Category','Type','Flat Value','Slab Min (kg)','Slab Max (kg)','Slab Charge (₹)','Slab Status','Payment','States','Status']];

    feeRulesData.forEach(rule => {
        if (rule.slabs && rule.slabs.length > 0) {
            rule.slabs.forEach(slab => {
                rows.push([
                    rule.fee_name,
                    rule.category_name,
                    'Weight Slab',
                    '',
                    slab.minWeight ?? '',
                    slab.maxWeight ?? '',
                    slab.charge ?? '',
                    slab.status ? 'Active' : 'Inactive',
                    rule.payment_method,
                    rule.states,
                    rule.active
                ]);
            });
        } else {
            rows.push([
                rule.fee_name,
                rule.category_name,
                rule.fee_type,
                rule.flat_fee_value,
                '', '', '', '',
                rule.payment_method,
                rule.states,
                rule.active
            ]);
        }
    });

    let csvContent = "data:text/csv;charset=utf-8,";
    rows.forEach(row => {
        csvContent += row.map(v => '"' + String(v ?? '').replace(/"/g, '""') + '"').join(',') + "\r\n";
    });

    const link = document.createElement('a');
    link.setAttribute('href', encodeURI(csvContent));
    link.setAttribute('download', 'fee_rules_export_<?= date('Y-m-d') ?>.csv');
    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);
}
</script>
