<?php
/**
 * Admin — Cancellation Rules (Financial & SLA)
 *
 * @var array $rules
 * @var bool $cancellationsEnabled
 */

$isCod = false; // Always false, site only supports online payment

// Filter rules to only show online/prepaid cancellation rules (all rules configured for storefront)
$filteredRules = array_filter($rules, function($r) {
    $pm = strtolower($r['payment_method'] ?? '');
    return in_array($pm, ['prepaid', 'online', 'both', 'all', '']);
});

$userRole = \Core\Session::get('user_role');
$isPrivileged = in_array($userRole, ['super_admin', 'admin', 'manager']);
$canManage = $this->hasPermission('cancellation_rules', 'create') || $isPrivileged;
$canEdit = $this->hasPermission('cancellation_rules', 'edit') || $isPrivileged;
$canDelete = $this->hasPermission('cancellation_rules', 'delete') || $isPrivileged;
?>

<div class="w-full">
    <!-- Header Section -->
    <div class="mb-8 relative z-10">
        <!-- Breadcrumbs -->
        <div class="text-sm font-medium text-gray-400 mb-2 font-serif tracking-wide">
            Dashboard <span class="mx-1 text-gray-300">›</span> <a href="<?= BASE_URL ?>/admin/cancellations" class="hover:text-brand-600">Cancellation Management</a> <span class="mx-1 text-gray-300">›</span> <span class="text-brand-700">Cancellation Rules</span>
        </div>
        
        <div class="flex justify-between items-center mb-6 w-full">
            <h2 class="text-5xl font-display font-extrabold text-gray-900 tracking-tight">Cancellation Rules</h2>
            
            <div class="flex items-center space-x-3 ml-auto">
                <button onclick="window.location.reload()" class="bg-white hover:bg-gray-50 text-gray-700 px-6 py-3 rounded-full font-bold text-sm shadow-sm border border-gray-200 transition-all flex items-center uppercase tracking-widest cursor-pointer">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path></svg>
                    Refresh
                </button>
                <button onclick="exportCancellationRulesCSV()" class="bg-[#F25996] hover:bg-[#e04481] text-white px-6 py-3 rounded-full font-bold text-sm shadow-sm transition-all flex items-center uppercase tracking-widest cursor-pointer">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path></svg>
                    Export Excel
                </button>
            </div>
        </div>
    </div>

    <!-- Global Cancellations Toggle Banner -->
    <div id="global-toggle-banner" class="mb-6 flex items-center justify-between px-5 py-4 rounded-2xl border <?= $cancellationsEnabled ? 'bg-emerald-50 border-emerald-200' : 'bg-red-50 border-red-200' ?> shadow-sm transition-all">
        <div class="flex items-center gap-3">
            <div class="w-10 h-10 rounded-full flex items-center justify-center <?= $cancellationsEnabled ? 'bg-emerald-100' : 'bg-red-100' ?>">
                <svg class="w-5 h-5 <?= $cancellationsEnabled ? 'text-emerald-600' : 'text-red-500' ?>" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            </div>
            <div>
                <p class="font-bold text-sm <?= $cancellationsEnabled ? 'text-emerald-800' : 'text-red-800' ?>">
                    Order Cancellations: <span id="global-toggle-label"><?= $cancellationsEnabled ? 'Enabled' : 'Disabled' ?></span>
                </p>
                <p class="text-xs <?= $cancellationsEnabled ? 'text-emerald-700' : 'text-red-700' ?> mt-0.5">
                    When disabled, buyers will not see the "Cancel Order" button in their dashboard.
                </p>
            </div>
        </div>
        <!-- Toggle Switch -->
        <label class="relative inline-flex items-center cursor-pointer" title="Toggle cancellations globally">
            <input type="checkbox" id="global-cancel-toggle" class="sr-only peer" <?= $cancellationsEnabled ? 'checked' : '' ?> onchange="toggleGlobalCancellations(this.checked)">
            <div class="w-14 h-7 bg-red-200 peer-focus:outline-none peer-focus:ring-2 peer-focus:ring-emerald-400 rounded-full peer peer-checked:after:translate-x-7 peer-checked:bg-emerald-500 after:content-[''] after:absolute after:top-0.5 after:left-0.5 after:bg-white after:rounded-full after:h-6 after:w-6 after:transition-all shadow-inner"></div>
        </label>
    </div>

    <!-- Page header -->
    <div class="mb-6 flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-black text-gray-900 mb-1 font-serif">Cancellation Rules</h1>
            <p class="text-sm text-gray-500">Define cancellation fees, refund percentages, and SLAs based on order status.</p>
        </div>
        <?php if ($canManage): ?>
        <button onclick="openAddModal()"
            class="flex items-center gap-2 px-6 py-2.5 bg-[#F25996] hover:bg-[#d94480] text-white font-bold rounded-full shadow-sm transition-all text-sm uppercase tracking-wider active:scale-95 cursor-pointer">
            + Add New Rule
        </button>
        <?php endif; ?>
    </div>

    <!-- Rules list -->
    <?php if (empty($filteredRules)): ?>
        <div class="bg-white border border-dashed border-gray-300 rounded-2xl p-16 text-center">
            <svg class="w-12 h-12 mx-auto text-gray-200 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
            <h3 class="text-lg font-bold text-gray-700 mb-2">No rules defined</h3>
            <p class="text-gray-400 text-sm mb-6">All cancellations will be allowed with 0 fee and 100% refund by default.</p>
            <?php if ($canManage): ?>
            <button onclick="openAddModal()" class="px-6 py-2.5 bg-[#F25996] text-white font-bold rounded-full hover:bg-[#d94480] transition-all text-sm uppercase tracking-wider shadow-sm active:scale-95 cursor-pointer">
                + Add Your First Rule
            </button>
            <?php endif; ?>
        </div>
    <?php else: ?>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4" id="rules-list">
            <?php foreach ($filteredRules as $rule): ?>
            <div id="rule-card-<?= $rule['id'] ?>" class="bg-white border <?= $rule['is_active'] ? 'border-gray-200' : 'border-gray-100 opacity-60' ?> rounded-2xl p-5 shadow-sm transition-all hover:shadow-md">
                
                <div class="flex items-center justify-between mb-4">
                    <span class="px-3 py-1 bg-pink-50 text-[#F25996] text-xs font-bold rounded-full border border-pink-200 uppercase tracking-wider">
                        <?= htmlspecialchars(strtoupper(str_replace('_', ' ', $rule['rule_name']))) ?>
                    </span>
                    
                    <div class="flex items-center gap-1.5">
                        <?php if ($canEdit): ?>
                        <button onclick='openEditModal(<?= json_encode($rule, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP) ?>)' class="inline-flex items-center justify-center p-1.5 text-blue-600 hover:text-blue-800 hover:bg-blue-50 rounded-lg transition-colors cursor-pointer" title="Edit">
                            <i class="bi bi-pencil-square text-base"></i>
                        </button>
                        <?php endif; ?>
                        <?php if ($canDelete): ?>
                        <button onclick="deleteRule(<?= $rule['id'] ?>, '<?= htmlspecialchars(addslashes(strtoupper(str_replace('_', ' ', $rule['rule_name'])))) ?>')" class="inline-flex items-center justify-center p-1.5 text-red-600 hover:text-red-800 hover:bg-red-50 rounded-lg transition-colors cursor-pointer" title="Delete">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                        </button>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="space-y-3 mb-5">
                    <div class="flex justify-between items-center text-sm">
                        <span class="text-gray-500">Cancellation Fee</span>
                        <span class="font-bold text-gray-900">₹<?= number_format((float)$rule['cancellation_fee'], 2) ?></span>
                    </div>
                    <div class="flex justify-between items-center text-sm">
                        <span class="text-gray-500">Refund</span>
                        <span class="font-bold text-emerald-600">100% minus fee</span>
                    </div>
                    <div class="flex justify-between items-center text-sm">
                        <span class="text-gray-500">SLA Timeline</span>
                        <span class="font-bold text-gray-900"><?= (int)$rule['sla_days'] ?> Days</span>
                    </div>
                </div>

                <div class="pt-4 border-t border-gray-100 flex items-center justify-between">
                    <span class="rule-status-badge text-xs font-bold <?= $rule['is_active'] ? 'text-emerald-600' : 'text-gray-400' ?>">
                        <?= $rule['is_active'] ? 'Active' : 'Inactive' ?>
                    </span>
                    
                    <button onclick="toggleRule(<?= $rule['id'] ?>, this)"
                        id="toggle-<?= $rule['id'] ?>"
                        <?= !$canEdit ? 'disabled' : '' ?>
                        class="relative inline-flex h-5 w-9 items-center rounded-full transition-colors focus:outline-none cursor-pointer <?= $rule['is_active'] ? 'bg-[#F25996]' : 'bg-gray-200' ?> <?= !$canEdit ? 'opacity-50 cursor-not-allowed' : '' ?>">
                        <span class="inline-block h-3.5 w-3.5 transform rounded-full bg-white shadow transition-transform <?= $rule['is_active'] ? 'translate-x-4' : 'translate-x-1' ?>"></span>
                    </button>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

    <!-- Add / Edit Rule Modal -->
    <div id="rule-modal" class="fixed inset-0 items-center justify-center p-4 transition-all" style="display: none; z-index: 99999;" role="dialog" aria-modal="true">
        <!-- Backdrop -->
        <div class="fixed inset-0 bg-gray-900/60 backdrop-blur-sm transition-opacity" onclick="closeRuleModal()" style="z-index: 99998;"></div>
        
        <!-- Modal Card -->
        <div class="relative w-full max-w-lg bg-white rounded-3xl shadow-2xl border border-gray-100 overflow-hidden transform transition-all" style="z-index: 99999;">

            <!-- Header -->
            <div class="px-6 py-5 border-b border-gray-100 flex items-center justify-between">
                <div class="flex items-center gap-2.5">
                    <div class="w-8 h-8 rounded-xl bg-pink-50 text-[#F25996] flex items-center justify-center font-bold">📋</div>
                    <h3 id="modal-title" class="font-display text-lg font-bold text-gray-900">+ Add New Cancellation Rule</h3>
                </div>
                <button onclick="closeRuleModal()" class="text-gray-400 hover:text-gray-600 transition-colors p-1 rounded-lg hover:bg-gray-100 cursor-pointer">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>

            <!-- Body -->
            <div class="p-6">
                <input type="hidden" id="rule-id" value="">
                <input type="hidden" id="rule-payment-method" value="prepaid">

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-4">
                    
                    <!-- Rule Name (Order Status) -->
                    <div class="sm:col-span-2">
                        <label class="block text-xs font-bold text-gray-700 mb-1.5 uppercase tracking-wider">
                            Order Status <span class="text-red-500">*</span>
                        </label>
                        <select id="rule-name" class="w-full bg-white border border-gray-200 rounded-xl px-3.5 py-2.5 text-sm font-medium focus:outline-none focus:border-[#F25996] focus:ring-2 focus:ring-[#F25996]/20 shadow-2xs">
                            <option value="placed">Placed (Order Confirmed)</option>
                            <option value="packed">Packed (Ready to Dispatch)</option>
                            <option value="shipped">Shipped (In Transit)</option>
                            <option value="out_for_delivery">Out for Delivery</option>
                            <option value="delivered">Delivered</option>
                        </select>
                    </div>

                    <!-- Cancellation Fee -->
                    <div>
                        <label class="block text-xs font-bold text-gray-700 mb-1.5 uppercase tracking-wider">
                            Cancellation Fee (₹) <span class="text-red-500">*</span>
                        </label>
                        <input type="number" id="rule-fee" min="0" step="any" placeholder="0.00" value="0"
                               class="w-full bg-white border border-gray-200 rounded-xl px-3.5 py-2.5 text-sm font-medium focus:outline-none focus:border-[#F25996] focus:ring-2 focus:ring-[#F25996]/20 placeholder-gray-400 shadow-2xs">
                        <p class="text-[11px] text-gray-400 mt-1">Flat deduction amount (0 = Free cancellation)</p>
                    </div>

                    <!-- SLA Days -->
                    <div>
                        <label class="block text-xs font-bold text-gray-700 mb-1.5 uppercase tracking-wider">
                            SLA Timeline (Days) <span class="text-red-500">*</span>
                        </label>
                        <input type="number" id="rule-sla" min="0" step="1" placeholder="2" value="2" 
                               class="w-full bg-white border border-gray-200 rounded-xl px-3.5 py-2.5 text-sm font-medium focus:outline-none focus:border-[#F25996] focus:ring-2 focus:ring-[#F25996]/20 placeholder-gray-400 shadow-2xs">
                        <p class="text-[11px] text-gray-400 mt-1">Refund resolution window in days</p>
                    </div>

                    <input type="hidden" id="rule-refund" value="100">
                </div>

                <!-- Status Radio -->
                <div class="mb-6 p-3 bg-gray-50/70 rounded-xl border border-gray-100">
                    <label class="block text-xs font-bold text-gray-700 mb-2 uppercase tracking-wider">Rule Activation</label>
                    <div class="flex items-center gap-6">
                        <label class="flex items-center gap-2 text-sm cursor-pointer text-gray-700 font-medium">
                            <input type="radio" name="rule_status" value="1" class="accent-[#F25996]" checked> Active (Enabled)
                        </label>
                        <label class="flex items-center gap-2 text-sm cursor-pointer text-gray-700 font-medium">
                            <input type="radio" name="rule_status" value="0" class="accent-[#F25996]"> Inactive (Disabled)
                        </label>
                    </div>
                </div>

                <!-- Footer Buttons -->
                <div class="flex items-center justify-end gap-3 pt-2">
                    <button type="button" onclick="closeRuleModal()" class="px-6 py-2.5 bg-gray-100 hover:bg-gray-200 text-gray-700 font-bold text-xs uppercase tracking-wider rounded-xl transition-colors cursor-pointer">
                        Cancel
                    </button>
                    <button type="button" id="rule-submit-btn" onclick="submitRuleForm()" class="px-7 py-2.5 bg-[#F25996] text-white font-bold text-xs uppercase tracking-wider rounded-xl hover:bg-[#d94480] transition-colors shadow-sm flex items-center gap-2 disabled:opacity-60 cursor-pointer">
                        <span id="rule-submit-label">SAVE RULE</span>
                        <svg id="rule-spinner" class="w-4 h-4 animate-spin hidden" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8z"></path></svg>
                    </button>
                </div>

            </div>
        </div>
    </div>
</div>

<style>
.swal2-container {
    z-index: 100001 !important;
}
</style>

<script>
// ── Add Modal ─────────────────────────────────────────────────────────────────
function openAddModal() {
    document.getElementById('rule-id').value = '';
    document.getElementById('rule-name').value = 'placed';
    document.getElementById('rule-fee').value = '0';
    document.getElementById('rule-refund').value = '100';
    document.getElementById('rule-sla').value = '2';
    
    const activeRadio = document.querySelector('input[name="rule_status"][value="1"]');
    if (activeRadio) activeRadio.checked = true;
    
    document.getElementById('modal-title').textContent = '+ Add New Cancellation Rule';
    document.getElementById('rule-submit-label').textContent = 'SAVE RULE';
    
    const modal = document.getElementById('rule-modal');
    if (modal) {
        modal.style.display = 'flex';
        modal.classList.remove('hidden');
    }
    document.body.style.overflow = 'hidden';
}

// ── Edit Modal ────────────────────────────────────────────────────────────────
function openEditModal(rule) {
    document.getElementById('rule-id').value = rule.id;
    
    const select = document.getElementById('rule-name');
    const ruleStatus = (rule.rule_name || '').toLowerCase();
    
    if (Array.from(select.options).some(o => o.value.toLowerCase() === ruleStatus)) {
        select.value = ruleStatus;
    } else {
        const opt = new Option(rule.rule_name, rule.rule_name);
        select.add(opt);
        select.value = rule.rule_name;
    }

    document.getElementById('rule-fee').value = rule.cancellation_fee;
    document.getElementById('rule-refund').value = rule.refund_percentage || 100;
    document.getElementById('rule-sla').value = rule.sla_days;
    
    const statusRadio = document.querySelector('input[name="rule_status"][value="' + rule.is_active + '"]');
    if (statusRadio) statusRadio.checked = true;

    document.getElementById('modal-title').textContent = 'Edit Cancellation Rule (' + (rule.rule_name ? rule.rule_name.toUpperCase() : '') + ')';
    document.getElementById('rule-submit-label').textContent = 'UPDATE RULE';
    
    const modal = document.getElementById('rule-modal');
    if (modal) {
        modal.style.display = 'flex';
        modal.classList.remove('hidden');
    }
    document.body.style.overflow = 'hidden';
}

function closeRuleModal() {
    const modal = document.getElementById('rule-modal');
    if (modal) {
        modal.style.display = 'none';
        modal.classList.add('hidden');
    }
    document.body.style.overflow = '';
}

// ── Submit Rule Form ──────────────────────────────────────────────────────────
async function submitRuleForm() {
    const id = document.getElementById('rule-id').value;
    const ruleName = document.getElementById('rule-name').value;
    const feeInput = document.getElementById('rule-fee').value.trim();
    const fee = feeInput === '' ? 0 : parseFloat(feeInput);
    const slaInput = document.getElementById('rule-sla').value.trim();
    const sla = slaInput === '' ? 0 : parseInt(slaInput, 10);
    const refund = 100;
    const pm = document.getElementById('rule-payment-method').value || 'prepaid';
    const activeRadio = document.querySelector('input[name="rule_status"]:checked');
    const active = activeRadio ? activeRadio.value : 1;

    if (!ruleName) { 
        Swal.fire({ 
            icon: 'warning', 
            title: 'Status Required', 
            text: 'Please select an order status.',
            confirmButtonColor: '#F25996'
        });
        return; 
    }
    if (isNaN(fee) || fee < 0) {
        Swal.fire({ 
            icon: 'warning', 
            title: 'Invalid Fee', 
            text: 'Cancellation fee must be 0 or greater.',
            confirmButtonColor: '#F25996'
        });
        return;
    }
    if (isNaN(sla) || sla < 0) {
        Swal.fire({ 
            icon: 'warning', 
            title: 'Invalid SLA', 
            text: 'SLA days must be 0 or greater.',
            confirmButtonColor: '#F25996'
        });
        return;
    }

    const payload = {
        id: id,
        rule_name: ruleName,
        payment_method: pm,
        cancellation_fee: fee,
        refund_percentage: refund,
        sla_days: sla,
        is_active: parseInt(active)
    };

    const url = id
        ? '<?= BASE_URL ?>/admin/cancellations/rules/update'
        : '<?= BASE_URL ?>/admin/cancellations/rules/add';

    const btn   = document.getElementById('rule-submit-btn');
    const label = document.getElementById('rule-submit-label');
    const spin  = document.getElementById('rule-spinner');
    btn.disabled = true; 
    if (spin) spin.classList.remove('hidden');
    const origLabel = label.textContent;
    label.textContent = 'SAVING…';

    try {
        const res  = await fetch(url, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: JSON.stringify(payload),
        });
        const data = await res.json();
        if (data.success) {
            Swal.fire({
                toast: true,
                position: 'top-end',
                icon: 'success',
                title: data.message,
                showConfirmButton: false,
                timer: 1500
            });
            closeRuleModal();
            setTimeout(() => location.reload(), 700);
        } else {
            Swal.fire({
                icon: 'error',
                title: 'Unable to Save Rule',
                text: data.message || 'Error saving rule.',
                confirmButtonColor: '#F25996'
            });
            btn.disabled = false; 
            label.textContent = origLabel; 
            if (spin) spin.classList.add('hidden');
        }
    } catch(e) {
        Swal.fire({
            icon: 'error',
            title: 'Network Error',
            text: 'Could not connect to the server.',
            confirmButtonColor: '#F25996'
        });
        btn.disabled = false; 
        label.textContent = origLabel; 
        if (spin) spin.classList.add('hidden');
    }
}

// ── Toggle Active ─────────────────────────────────────────────────────────────
async function toggleRule(id, btn) {
    try {
        const res  = await fetch('<?= BASE_URL ?>/admin/cancellations/rules/toggle', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: JSON.stringify({id}),
        });
        const data = await res.json();
        if (data.success) {
            const isActive = data.is_active === 1;
            btn.classList.toggle('bg-[#F25996]', isActive);
            btn.classList.toggle('bg-gray-200', !isActive);
            const dot = btn.querySelector('span');
            if (dot) {
                dot.classList.toggle('translate-x-4', isActive);
                dot.classList.toggle('translate-x-1', !isActive);
            }
            
            const card = document.getElementById('rule-card-' + id);
            if (card) {
                card.classList.toggle('opacity-60', !isActive);
                card.classList.toggle('border-gray-100', !isActive);
                card.classList.toggle('border-gray-200', isActive);
                
                const txt = card.querySelector('.rule-status-badge');
                if (txt) {
                    txt.textContent = isActive ? 'Active' : 'Inactive';
                    txt.className = 'rule-status-badge text-xs font-bold ' + (isActive ? 'text-emerald-600' : 'text-gray-400');
                }
            }
            
            Swal.fire({
                toast: true,
                position: 'top-end',
                icon: 'success',
                title: data.message,
                showConfirmButton: false,
                timer: 1500
            });
        } else {
            Swal.fire({ 
                icon: 'error', 
                title: 'Error', 
                text: data.message || 'Toggle failed.',
                confirmButtonColor: '#F25996'
            });
        }
    } catch(e) { 
        Swal.fire({ 
            icon: 'error', 
            title: 'Error', 
            text: 'Network error communicating with server.',
            confirmButtonColor: '#F25996'
        });
    }
}

// ── Delete ────────────────────────────────────────────────────────────────────
function deleteRule(id, name) {
    Swal.fire({
        title: 'Delete Rule?',
        html: `Are you sure you want to delete the cancellation rule for <b>${name}</b>?`,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#e11d48',
        cancelButtonColor: '#f3f4f6',
        confirmButtonText: 'Yes, Delete',
        cancelButtonText: '<span style="color: #4b5563; font-weight: bold;">Cancel</span>',
        reverseButtons: true,
        customClass: { popup: 'rounded-2xl' }
    }).then(async (result) => {
        if (result.isConfirmed) {
            try {
                const res = await fetch('<?= BASE_URL ?>/admin/cancellations/rules/delete', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    body: JSON.stringify({id}),
                });
                const data = await res.json();
                if (data.success) {
                    Swal.fire({
                        toast: true,
                        position: 'top-end',
                        icon: 'success',
                        title: `Rule for '${name}' deleted`,
                        showConfirmButton: false,
                        timer: 1500
                    });
                    const card = document.getElementById('rule-card-' + id);
                    if (card) { 
                        card.style.opacity = '0'; 
                        card.style.transform = 'scale(0.95)'; 
                        setTimeout(() => {
                            card.remove();
                            // If no cards left, reload to show empty state
                            const remaining = document.querySelectorAll('#rules-list > div');
                            if (remaining.length === 0) {
                                location.reload();
                            }
                        }, 300); 
                    }
                } else {
                    Swal.fire({ 
                        icon: 'error', 
                        title: 'Error', 
                        text: data.message || 'Error deleting rule.',
                        confirmButtonColor: '#F25996'
                    });
                }
            } catch(e) {
                Swal.fire({ 
                    icon: 'error', 
                    title: 'Error', 
                    text: 'Network error communicating with server.',
                    confirmButtonColor: '#F25996'
                });
            }
        }
    });
}

// ── Global Cancellations Toggle ───────────────────────────────────────────────
async function toggleGlobalCancellations(enabled) {
    const toggle = document.getElementById('global-cancel-toggle');
    const label  = document.getElementById('global-toggle-label');
    const banner = document.getElementById('global-toggle-banner');
    toggle.disabled = true;
    try {
        const res  = await fetch('<?= BASE_URL ?>/admin/cancellations/rules/toggle-global', {
            method: 'POST',
            headers: { 
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: JSON.stringify({ enabled }),
        });
        const data = await res.json();
        if (data.success) {
            label.textContent = enabled ? 'Enabled' : 'Disabled';
            if (enabled) {
                banner.className = banner.className.replace('bg-red-50 border-red-200','bg-emerald-50 border-emerald-200');
            } else {
                banner.className = banner.className.replace('bg-emerald-50 border-emerald-200','bg-red-50 border-red-200');
            }
            Swal.fire({
                toast: true,
                position: 'top-end',
                icon: 'success',
                title: data.message,
                showConfirmButton: false,
                timer: 2000
            });
        } else {
            toggle.checked = !enabled; // revert
            Swal.fire({ 
                icon: 'error', 
                title: 'Error', 
                text: data.message || 'Error updating setting.',
                confirmButtonColor: '#F25996'
            });
        }
    } catch(e) {
        toggle.checked = !enabled;
        Swal.fire({ 
            icon: 'error', 
            title: 'Error', 
            text: 'Network error communicating with server.',
            confirmButtonColor: '#F25996'
        });
    }
    toggle.disabled = false;
}

// ── Export Excel / CSV ────────────────────────────────────────────────────────
function exportCancellationRulesCSV() {
    const rules = <?= json_encode(array_values($filteredRules)) ?>;
    if (!rules || rules.length === 0) {
        Swal.fire({
            icon: 'info',
            title: 'No Rules Found',
            text: 'There are currently no cancellation rules to export.',
            confirmButtonColor: '#F25996'
        });
        return;
    }
    
    let csvContent = "data:text/csv;charset=utf-8,";
    csvContent += "ID,Order Status,Cancellation Fee (INR),Refund Rule,SLA Timeline (Days),Status,Payment Method\r\n";
    
    rules.forEach(r => {
        const row = [
            r.id,
            r.rule_name,
            parseFloat(r.cancellation_fee || 0).toFixed(2),
            "100% minus fee",
            r.sla_days || 0,
            r.is_active ? 'Active' : 'Inactive',
            r.payment_method || 'prepaid'
        ].map(v => '"' + String(v).replace(/"/g, '""').trim() + '"');
        csvContent += row.join(",") + "\r\n";
    });
    
    let encodedUri = encodeURI(csvContent);
    let link = document.createElement("a");
    link.setAttribute("href", encodedUri);
    link.setAttribute("download", "cancellation_rules_export_<?= date('Y-m-d') ?>.csv");
    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);
}
</script>
