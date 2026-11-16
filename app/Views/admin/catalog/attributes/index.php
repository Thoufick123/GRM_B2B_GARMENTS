<div class="w-full">
    <!-- Header Section -->
    <div class="mb-8 relative z-10">
        <!-- Breadcrumbs -->
        <div class="text-sm font-medium text-gray-400 mb-2 font-serif tracking-wide">
            Dashboard <span class="mx-1 text-gray-300">›</span> <?= !empty($isCustomize) ? 'Customization Management' : 'Catalog Management' ?> <span class="mx-1 text-gray-300">›</span> <span class="text-brand-700"><?= !empty($isCustomize) ? 'Customize Attributes' : 'Attributes' ?></span>
        </div>
        
        <div class="flex justify-between items-center mb-8">
            <div>
                <h2 class="text-5xl font-display font-extrabold text-gray-900 tracking-tight"><?= !empty($isCustomize) ? 'Customize Attributes' : 'Attributes' ?></h2>
                <p class="text-sm text-gray-500 mt-1"><?= !empty($isCustomize) ? 'Manage attributes configured for customizable workshop garments and fabrics.' : 'Manage general product attributes and options.' ?></p>
            </div>
            
            <div class="flex items-center space-x-3">
                <button onclick="window.location.reload()" class="bg-white hover:bg-gray-50 text-gray-700 px-5 py-2.5 rounded-full font-bold text-xs shadow-sm border border-gray-200 transition-all flex items-center uppercase tracking-wider">
                    <svg class="w-3.5 h-3.5 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path></svg>
                    Refresh
                </button>
                <button class="bg-[#F25996]/90 hover:bg-[#e04481] text-white px-5 py-2.5 rounded-full font-bold text-xs shadow-sm transition-all flex items-center uppercase tracking-wider" onclick="exportAttributes()">
                    <svg class="w-3.5 h-3.5 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path></svg>
                    Export Excel
                </button>
                <?php 
                $permAttrMod = !empty($isCustomize) ? 'customize_attributes' : 'attributes';
                ?>
                <?php if ($this->hasPermission($permAttrMod, 'create')): ?>
                <a href="<?= BASE_URL ?>/admin/catalog/attributes/create<?= !empty($isCustomize) ? '?module=customize' : '' ?>" class="bg-[#F25996] hover:bg-[#e04481] text-white px-6 py-2.5 rounded-full font-bold text-xs shadow-md hover:shadow-lg transition-all flex items-center uppercase tracking-wider">
                    <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path></svg>
                    <?= !empty($isCustomize) ? 'Add Customize Attribute' : 'Add Attribute' ?>
                </a>
                <?php endif; ?>
            </div>
        </div>

        <!-- Search Bar -->
        <div class="bg-white rounded-full shadow-sm border border-gray-100 p-1.5 flex items-center">
            <div class="flex-grow flex items-center pl-5">
                <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                <input type="text" id="attrSearchInput" onkeyup="filterAttributes()" placeholder="Search attributes..." class="w-full pl-3 pr-4 py-2.5 bg-transparent border-none focus:ring-0 text-gray-700 placeholder-gray-400 outline-none font-medium">
            </div>
            <div class="border-l border-gray-200 pl-2 pr-2">
                <select id="attrCategoryFilter" onchange="filterAttributes()" class="bg-transparent border-none text-gray-600 focus:ring-0 py-2.5 pr-8 pl-4 font-semibold outline-none cursor-pointer max-w-[200px] truncate">
                    <option value="">All Categories</option>
                    <!-- Options populated by JS -->
                </select>
            </div>
            <div class="border-l border-gray-200 pl-2 pr-2">
                <select id="attrSubCategoryFilter" onchange="filterAttributes()" class="bg-transparent border-none text-gray-600 focus:ring-0 py-2.5 pr-8 pl-4 font-semibold outline-none cursor-pointer max-w-[200px] truncate">
                    <option value="">All Sub-Categories</option>
                    <!-- Options populated by JS -->
                </select>
            </div>
        </div>
    </div>

    <!-- Background Decoration -->
    <div class="absolute top-0 left-0 w-full h-96 bg-gradient-to-b from-brand-50/40 to-transparent pointer-events-none -z-10"></div>

    <?php 
    $flashSuccess = \Core\Session::getFlash('success');
    $flashError = \Core\Session::getFlash('error');
    ?>
    <?php if ($flashSuccess): ?>
    <div class="mb-6 p-4 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-800 text-sm font-semibold flex items-center justify-between shadow-sm">
        <span>✅ <?= htmlspecialchars($flashSuccess) ?></span>
        <button onclick="this.parentElement.remove()" class="text-emerald-500 hover:text-emerald-700 font-bold text-lg">&times;</button>
    </div>
    <?php endif; ?>
    <?php if ($flashError): ?>
    <div class="mb-6 p-4 rounded-xl bg-red-50 border border-red-200 text-red-800 text-sm font-semibold flex items-center justify-between shadow-sm">
        <span>❌ <?= htmlspecialchars($flashError) ?></span>
        <button onclick="this.parentElement.remove()" class="text-red-500 hover:text-red-700 font-bold text-lg">&times;</button>
    </div>
    <?php endif; ?>

    <div class="bg-white/80 backdrop-blur-xl rounded-2xl shadow-xl shadow-brand-900/5 border border-white/60 overflow-hidden ring-1 ring-gray-900/5">
        <?php if(empty($attributes)): ?>
            <div class="p-16 text-center text-gray-400 font-medium">
                <div class="flex flex-col items-center">
                    <svg class="w-12 h-12 mb-3 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path></svg>
                    No attributes found. Let's create your first one.
                </div>
            </div>
        <?php else: ?>
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse min-w-[900px]">
                    <thead>
                        <tr class="bg-gradient-to-r from-gray-50/90 to-white/90 backdrop-blur text-gray-500 text-xs uppercase tracking-widest border-b border-gray-100">
                            <th class="px-6 py-5 font-semibold w-16">#</th>
                            <th class="px-6 py-5 font-semibold">Attribute Name</th>
                            <th class="px-6 py-5 font-semibold w-32">Type</th>
                            <th class="px-6 py-5 font-semibold w-56">Linked To</th>
                            <th class="px-6 py-5 font-semibold">Values</th>
                            <th class="px-6 py-5 font-semibold w-32">Status</th>
                            <th class="px-6 py-5 font-semibold w-40 text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-50 text-sm">
                        <?php foreach($attributes as $index => $attr): ?>
                        <tr class="group hover:bg-brand-50/20 transition-all duration-300 data-row" data-cat="<?= htmlspecialchars(strtolower($attr['category_name'] ?? '')) ?>" data-sub="<?= htmlspecialchars(strtolower($attr['sub_category_name'] ?? '')) ?>">
                            <td class="px-6 py-5 text-gray-400 font-medium group-hover:text-brand-600 transition-colors">#<?= $index + 1 ?></td>
                            <td class="px-6 py-5">
                                <div class="flex items-center">
                                    <div class="w-10 h-10 rounded-xl bg-gray-50/50 text-gray-400 border border-gray-100 flex items-center justify-center mr-3 flex-shrink-0 group-hover:bg-white group-hover:border-brand-200 group-hover:text-brand-500 transition-all shadow-sm">
                                        <?php if(strtolower($attr['input_type']) == 'colorpicker'): ?>
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21a4 4 0 01-4-4V5a2 2 0 012-2h4a2 2 0 012 2v12a4 4 0 01-4 4zm0 0h12a2 2 0 002-2v-4a2 2 0 00-2-2h-2.343M11 7.343l1.657-1.657a2 2 0 012.828 0l2.829 2.829a2 2 0 010 2.828l-8.486 8.485M7 17h.01"></path></svg>
                                        <?php elseif(strtolower($attr['input_type']) == 'checkbox'): ?>
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                        <?php else: ?>
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"></path></svg>
                                        <?php endif; ?>
                                    </div>
                                    <div>
                                        <div class="font-bold text-gray-900 group-hover:text-brand-700 transition-colors text-base tracking-tight"><?= htmlspecialchars($attr['name']) ?></div>
                                        <div class="text-[11px] text-gray-400 font-medium flex items-center mt-0.5">
                                            <span class="opacity-70">ID: ATR<?= str_pad($attr['id'], 3, '0', STR_PAD_LEFT) ?></span>
                                        </div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-5">
                                <?php
                                    $typeColor = match(strtolower($attr['input_type'])) {
                                        'dropdown' => 'bg-emerald-50 text-emerald-700 border-emerald-100',
                                        'checkbox' => 'bg-blue-50 text-blue-700 border-blue-100',
                                        'radio' => 'bg-purple-50 text-purple-700 border-purple-100',
                                        'colorpicker' => 'bg-pink-50 text-pink-700 border-pink-100',
                                        default => 'bg-indigo-50 text-indigo-700 border-indigo-100',
                                    };
                                ?>
                                <span class="px-2.5 py-1 rounded-md text-[11px] font-bold tracking-wider <?= $typeColor ?> uppercase border shadow-sm">
                                    <?= htmlspecialchars($attr['input_type']) ?>
                                </span>
                            </td>
                            <td class="px-6 py-5">
                                <?php if ($attr['category_name']): ?>
                                    <div class="text-gray-700 text-sm flex items-center flex-wrap gap-1 font-medium">
                                        <span><?= htmlspecialchars($attr['category_name']) ?></span>
                                        <?php if ($attr['sub_category_name']): ?>
                                            <svg class="w-3 h-3 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path></svg>
                                            <span class="text-gray-500"><?= htmlspecialchars($attr['sub_category_name']) ?></span>
                                        <?php endif; ?>
                                    </div>
                                <?php else: ?>
                                    <span class="px-2.5 py-1 bg-gray-50 text-gray-500 rounded-md text-xs font-semibold border border-gray-100">Global (All)</span>
                                <?php endif; ?>
                            </td>
                            <td class="px-6 py-5">
                                <div class="flex flex-wrap gap-1.5 items-center">
                                    <?php if(!empty($attr['values'])): ?>
                                        <?php foreach(array_slice($attr['values'], 0, 4) as $val): ?>
                                            <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-md bg-white border border-gray-200 text-[11px] font-medium text-gray-600 hover:border-brand-200 hover:text-brand-600 hover:shadow-sm transition-all group/val">
                                                <?= htmlspecialchars($val['value']) ?>
                                                <?php if ($this->hasPermission($permAttrMod, 'delete')): ?>
                                                <form action="<?= BASE_URL ?>/admin/catalog/attributes/delete-value" method="POST" class="inline-block" onsubmit="return confirm('Are you sure you want to remove this value?');">
                                                    <input type="hidden" name="id" value="<?= $val['id'] ?>">
                                                    <input type="hidden" name="module" value="<?= !empty($isCustomize) ? 'customize' : '' ?>">
                                                    <button type="submit" class="text-gray-300 hover:text-red-500 hover:bg-red-50 rounded-full p-0.5 transition-colors focus:outline-none" title="Remove value">
                                                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                                                    </button>
                                                </form>
                                                <?php endif; ?>
                                            </span>
                                        <?php endforeach; ?>
                                        <?php if(count($attr['values']) > 4): ?>
                                            <span class="text-[11px] text-brand-600 font-semibold px-1.5 py-1 bg-brand-50 rounded-md border border-brand-100">+<?= count($attr['values']) - 4 ?> more</span>
                                        <?php endif; ?>
                                    <?php else: ?>
                                        <span class="text-gray-400 italic text-xs">No values</span>
                                    <?php endif; ?>
                                    
                                    <?php if ($this->hasPermission($permAttrMod, 'create')): ?>
                                    <button onclick="addValue(<?= $attr['id'] ?>)" class="w-6 h-6 rounded-md bg-brand-50 text-brand-600 hover:bg-brand-600 hover:text-white flex items-center justify-center transition-all ml-1 shadow-sm border border-brand-100" title="Add Value">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                                    </button>
                                    <?php endif; ?>
                                </div>
                            </td>
                            <td class="px-6 py-5">
                                <?php if ($attr['status'] === 'active'): ?>
                                    <span class="inline-flex items-center px-3 py-1.5 rounded-full text-xs font-semibold bg-emerald-50/80 text-emerald-600 border border-emerald-100 shadow-sm"><span class="w-1.5 h-1.5 rounded-full bg-emerald-500 mr-1.5 animate-pulse"></span>Active</span>
                                <?php else: ?>
                                    <span class="inline-flex items-center px-3 py-1.5 rounded-full text-xs font-semibold bg-gray-50/80 text-gray-600 border border-gray-200 shadow-sm"><span class="w-1.5 h-1.5 rounded-full bg-gray-400 mr-1.5"></span>Inactive</span>
                                <?php endif; ?>
                            </td>
                            <td class="px-6 py-5 text-right">
                                <div class="flex justify-end space-x-2">
                                    <?php if ($this->hasPermission($permAttrMod, 'edit')): ?>
                                    <a href="<?= BASE_URL ?>/admin/catalog/attributes/edit?id=<?= $attr['id'] ?><?= !empty($isCustomize) ? '&module=customize' : '' ?>" class="p-1 text-blue-600 hover:text-blue-800 transition-colors group/btn inline-flex items-center justify-center" title="Edit">
                                        <i class="bi bi-pencil-square text-lg group-hover/btn:scale-110 transition-transform"></i>
                                    </a>
                                    <?php endif; ?>
                                    <?php if ($this->hasPermission($permAttrMod, 'delete')): ?>
                                    <button onclick="openDeleteModal(<?= $attr['id'] ?>)" class="p-1 text-red-600 hover:text-red-800 transition-colors group/btn inline-flex items-center justify-center" title="Delete">
                                        <svg class="w-5 h-5 group-hover/btn:scale-110 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                    </button>
                                    <?php endif; ?>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            
            <!-- Table Summary Footer -->
            <div class="px-6 py-4 border-t border-gray-100 bg-gray-50/50 flex items-center justify-between text-xs text-gray-500 font-medium">
                <div>Total Attributes: <strong id="attr-count-display" class="text-gray-900 font-bold"><?= count($attributes) ?></strong></div>
                <div class="text-gray-400">All attributes active & synced</div>
            </div>
        <?php endif; ?>
    </div>
    
    <!-- Info section at bottom -->
    <div class="mt-8 bg-[#F6F9F8] rounded-xl border border-[#E5ECE9] p-5 flex items-start gap-4">
        <div class="w-8 h-8 rounded-full bg-[#E8F1EE] text-[#059669] flex items-center justify-center flex-shrink-0 mt-0.5 shadow-inner">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
        </div>
        <div>
            <h4 class="text-sm font-bold text-gray-900">How attributes work?</h4>
            <p class="text-sm text-gray-600 mt-1">Attributes help you define product properties like color, size, material etc. You can add, edit or remove attributes and their values anytime.</p>
        </div>
    </div>
</div>

<form id="add-value-form" action="<?= BASE_URL ?>/admin/catalog/attributes/store-value" method="POST" class="hidden">
    <input type="hidden" name="module" value="<?= !empty($isCustomize) ? 'customize' : '' ?>">
    <input type="hidden" name="attribute_id" id="add-value-attr-id">
    <input type="hidden" name="value" id="add-value-text">
</form>

<!-- Delete Modal -->
<div id="delete-modal" class="fixed inset-0 bg-gray-900/50 backdrop-blur-sm z-50 hidden flex items-center justify-center p-4 transition-opacity">
    <div class="bg-white rounded-2xl shadow-xl w-full max-w-sm overflow-hidden text-center p-6 transform transition-all">
        <div class="mx-auto flex items-center justify-center h-12 w-12 rounded-full bg-red-100 mb-4">
            <svg class="h-6 w-6 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
        </div>
        <h3 class="text-lg font-medium text-gray-900 mb-2">Delete Attribute</h3>
        <p class="text-sm text-gray-500 mb-2">Are you sure you want to delete this attribute and all its values? This action cannot be undone.</p>
        <p class="text-xs text-amber-700 bg-amber-50 p-2.5 rounded-xl border border-amber-200 mb-4 text-left font-medium">Linked products will have this attribute dynamically unassigned.</p>
        <div id="delete-error-msg" class="hidden mb-4 p-3 bg-red-50 border border-red-200 text-red-700 text-xs font-semibold rounded-xl text-left"></div>
        <form id="attribute-delete-form" action="<?= BASE_URL ?>/admin/catalog/attributes/delete" method="POST" class="flex space-x-3 items-center">
            <input type="hidden" name="id" id="delete-id">
            <input type="hidden" name="module" value="<?= !empty($isCustomize) ? 'customize' : '' ?>">
            <button type="button" onclick="document.getElementById('delete-modal').classList.add('hidden')" class="flex-1 px-6 py-2.5 bg-white border border-gray-200 rounded-full text-sm font-semibold text-gray-700 hover:bg-gray-50 hover:border-gray-300 transition-all shadow-xs inline-flex items-center justify-center cursor-pointer">Cancel</button>
            <button type="submit" id="btn-confirm-delete-attr" class="flex-1 px-6 py-2.5 bg-gradient-to-r from-red-600 to-red-500 hover:from-red-500 hover:to-red-400 text-white rounded-full text-sm font-semibold shadow-lg shadow-red-500/30 transform hover:-translate-y-0.5 transition-all inline-flex items-center justify-center">Delete</button>
        </form>
    </div>
</div>

<script>
const allAttributes = <?= json_encode($attributes ?? [], JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP) ?>;

// Populate filter dropdowns dynamically
document.addEventListener('DOMContentLoaded', () => {
    const cats = new Set();
    const subs = new Set();
    
    allAttributes.forEach(a => {
        if (a.category_name) cats.add(a.category_name);
        if (a.sub_category_name) subs.add(a.sub_category_name);
    });
    
    const catSelect = document.getElementById('attrCategoryFilter');
    if(catSelect) {
        cats.forEach(c => {
            const opt = document.createElement('option');
            opt.value = c.toLowerCase();
            opt.textContent = c;
            catSelect.appendChild(opt);
        });
    }
    
    const subSelect = document.getElementById('attrSubCategoryFilter');
    if(subSelect) {
        subs.forEach(s => {
            const opt = document.createElement('option');
            opt.value = s.toLowerCase();
            opt.textContent = s;
            subSelect.appendChild(opt);
        });
    }

    const deleteForm = document.getElementById('attribute-delete-form');
    if (deleteForm) {
        deleteForm.addEventListener('submit', function(e) {
            e.preventDefault();
            const btn = document.getElementById('btn-confirm-delete-attr');
            const errDiv = document.getElementById('delete-error-msg');
            
            btn.disabled = true;
            btn.innerHTML = '<svg class="animate-spin -ml-1 mr-2 h-4 w-4 text-white inline" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path></svg> Deleting...';
            
            const formData = new FormData(this);
            fetch(this.action, {
                method: 'POST',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json'
                },
                body: formData
            })
            .then(res => {
                const contentType = res.headers.get('content-type');
                if (contentType && contentType.includes('application/json')) {
                    return res.json();
                }
                window.location.reload();
                return { success: true };
            })
            .then(data => {
                if (data.success) {
                    document.getElementById('delete-modal').classList.add('hidden');
                    window.location.reload();
                } else {
                    btn.disabled = false;
                    btn.innerHTML = 'Delete';
                    if (errDiv) {
                        errDiv.innerText = data.message || 'Error deleting attribute.';
                        errDiv.classList.remove('hidden');
                    } else {
                        alert(data.message || 'Error deleting attribute.');
                    }
                }
            })
            .catch(err => {
                this.submit();
            });
        });
    }
});

function exportAttributes() {
    if (allAttributes.length === 0) {
        alert('No attributes to export.');
        return;
    }
    const headers = ['ID', 'Name', 'Type', 'Category', 'Sub Category', 'Status', 'Values'];
    let csvContent = "data:text/csv;charset=utf-8," + headers.join(",") + "\n";
    allAttributes.forEach(a => {
        const valuesList = (a.values || []).map(v => v.value).join('|');
        const row = [
            a.id,
            `"${(a.name || '').replace(/"/g, '""')}"`,
            a.input_type,
            `"${(a.category_name || '').replace(/"/g, '""')}"`,
            `"${(a.sub_category_name || '').replace(/"/g, '""')}"`,
            a.status,
            `"${valuesList.replace(/"/g, '""')}"`
        ];
        csvContent += row.join(",") + "\n";
    });
    const encodedUri = encodeURI(csvContent);
    const link = document.createElement("a");
    link.setAttribute("href", encodedUri);
    link.setAttribute("download", "attributes_export.csv");
    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);
}

function filterAttributes() {
    const searchInput = document.getElementById('attrSearchInput');
    const catFilter = document.getElementById('attrCategoryFilter');
    const subFilter = document.getElementById('attrSubCategoryFilter');
    
    const searchVal = searchInput ? searchInput.value.toLowerCase() : '';
    const catVal = catFilter ? catFilter.value.toLowerCase() : '';
    const subVal = subFilter ? subFilter.value.toLowerCase() : '';
    
    const rows = document.querySelectorAll('tbody tr.data-row');
    let visibleCount = 0;
    
    rows.forEach(row => {
        const textContent = row.innerText.toLowerCase();
        const rowCat = row.getAttribute('data-cat') || '';
        const rowSub = row.getAttribute('data-sub') || '';
        
        const matchesSearch = textContent.includes(searchVal);
        const matchesCat = catVal ? rowCat === catVal : true;
        const matchesSub = subVal ? rowSub === subVal : true;
        
        if (matchesSearch && matchesCat && matchesSub) {
            row.style.display = '';
            visibleCount++;
        } else {
            row.style.display = 'none';
        }
    });

    const countDisplay = document.getElementById('attr-count-display');
    if (countDisplay) {
        countDisplay.innerText = visibleCount;
    }
}

function addValue(attrId) {
    const val = prompt("Enter new attribute value:");
    if (val && val.trim() !== '') {
        document.getElementById('add-value-attr-id').value = attrId;
        document.getElementById('add-value-text').value = val.trim();
        document.getElementById('add-value-form').submit();
    }
}

function openDeleteModal(id) {
    document.getElementById('delete-id').value = id;
    const errDiv = document.getElementById('delete-error-msg');
    if (errDiv) { errDiv.classList.add('hidden'); errDiv.innerText = ''; }
    const btn = document.getElementById('btn-confirm-delete-attr');
    if (btn) { btn.disabled = false; btn.innerHTML = 'Delete'; }
    document.getElementById('delete-modal').classList.remove('hidden');
}
</script>
