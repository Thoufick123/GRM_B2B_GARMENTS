<?php
// Sanitize products array securely for JSON output to prevent XSS
$safeProducts = array_map(function($p) {
    // Determine category GST fallback
    $catSgst = (float)($p['category_sgst'] ?? 0);
    $catCgst = (float)($p['category_cgst'] ?? 0);
    $catGst = $catSgst + $catCgst;

    $prodSgst = $catGst > 0 ? $catSgst : (float)($p['sgst'] ?? 0);
    $prodCgst = $catGst > 0 ? $catCgst : (float)($p['cgst'] ?? 0);
    $prodGst = $catGst > 0 ? $catGst : ((isset($p['total_gst']) && $p['total_gst'] !== null && $p['total_gst'] !== '' && (float)$p['total_gst'] > 0) ? (float)$p['total_gst'] : ($prodSgst + $prodCgst));

    return [
        'id' => $p['id'],
        'name' => htmlspecialchars($p['name'] ?? ''),
        'slug' => htmlspecialchars($p['slug'] ?? ''),
        'category_name' => htmlspecialchars($p['category_name'] ?? ''),
        'category_hsn_code' => htmlspecialchars($p['category_hsn_code'] ?? ''),
        'sub_category_name' => htmlspecialchars($p['sub_category_name'] ?? ''),
        'primary_image' => htmlspecialchars($p['primary_image'] ?? ''),
        'base_price' => (float)($p['base_price'] ?? $p['wholesale_price']),
        'discount_price' => isset($p['discount_price']) && $p['discount_price'] > 0 ? (float)$p['discount_price'] : null,
        'stock_quantity' => (int)($p['stock_quantity'] ?? 0),
        'moq_override' => $p['moq_override'] ? (int)$p['moq_override'] : null,
        'status' => htmlspecialchars($p['status'] ?? 'draft'),
        'approval_status' => htmlspecialchars($p['approval_status'] ?? 'approved'),
        'rejection_reason' => htmlspecialchars($p['rejection_reason'] ?? ''),
        'reviewer_name' => htmlspecialchars($p['reviewer_name'] ?? ''),
        'vendor_id' => $p['vendor_id'] ?? null,
        'vendor_store_name' => htmlspecialchars($p['vendor_store_name'] ?? ''),
        'unique_vendor_id' => htmlspecialchars($p['unique_vendor_id'] ?? ''),
        'sgst' => $prodSgst,
        'cgst' => $prodCgst,
        'total_gst' => $prodGst,
        'category_sgst' => $catSgst,
        'category_cgst' => $catCgst,
        'max_amount' => isset($p['max_amount']) && (float)$p['max_amount'] > 0 ? (float)$p['max_amount'] : null,
        'variants' => array_map(function($v) use ($prodGst, $prodSgst, $prodCgst, $catGst, $catSgst, $catCgst) {
            $vSgst = $catGst > 0 ? $catSgst : (isset($v['sgst']) && $v['sgst'] !== null && $v['sgst'] !== '' ? (float)$v['sgst'] : $prodSgst);
            $vCgst = $catGst > 0 ? $catCgst : (isset($v['cgst']) && $v['cgst'] !== null && $v['cgst'] !== '' ? (float)$v['cgst'] : $prodCgst);
            $vGst = $catGst > 0 ? $catGst : (isset($v['total_gst']) && $v['total_gst'] !== null && $v['total_gst'] !== '' && (float)$v['total_gst'] > 0 ? (float)$v['total_gst'] : ($vSgst + $vCgst));
            return [
                'name' => htmlspecialchars($v['name'] ?: $v['sku']),
                'base_price' => (float)$v['base_price'],
                'discount_price' => isset($v['discount_price']) && $v['discount_price'] > 0 ? (float)$v['discount_price'] : null,
                'inventory' => (int)$v['inventory'],
                'current_stock' => (int)($v['current_stock'] ?? $v['inventory']),
                'base_quantity' => isset($v['base_quantity']) ? (int)$v['base_quantity'] : 2,
                'max_quantity' => isset($v['max_quantity']) ? (int)$v['max_quantity'] : 4,
                'sgst' => $vSgst,
                'cgst' => $vCgst,
                'total_gst' => $vGst,
                'max_amount' => isset($v['max_amount']) && (float)$v['max_amount'] > 0 ? (float)$v['max_amount'] : null
            ];
        }, $p['variants'] ?? [])
    ];
}, $products ?? []);
$permProdMod = !empty($isCustomize) ? 'customize_products' : 'products';
?>

<div class="w-full">
    <!-- Header Section -->
    <div class="mb-8 relative z-10">
        <!-- Breadcrumbs -->
        <div class="text-sm font-medium text-gray-400 mb-2 font-serif tracking-wide">
            Dashboard <span class="mx-1 text-gray-300">›</span> <?= !empty($isCustomize) ? 'Customization Management' : 'Catalog Management' ?> <span class="mx-1 text-gray-300">›</span> <span class="text-brand-700"><?= !empty($isCustomize) ? 'Fabrics' : 'Products' ?></span>
        </div>
        
        <div class="flex justify-between items-center mb-6">
            <div>
                <h2 class="text-4xl sm:text-5xl font-display font-extrabold text-gray-900 tracking-tight"><?= !empty($isCustomize) ? 'Fabrics' : 'Products' ?></h2>
                <p class="text-sm text-gray-500 mt-1"><?= !empty($isCustomize) ? 'Manage customizable fabrics and wholesale base materials.' : ($isVendor ? 'Manage your product listings and view approval status.' : 'Review, approve, and manage products across the marketplace.') ?></p>
            </div>
            
            <div class="flex items-center space-x-3">
                <button onclick="refreshTable()" class="bg-white hover:bg-gray-50 text-gray-700 px-5 py-2.5 rounded-full font-bold text-xs shadow-sm border border-gray-200 transition-all flex items-center uppercase tracking-widest">
                    <svg class="w-4 h-4 mr-1.5 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path></svg>
                    Refresh
                </button>
                <button onclick="exportToExcel()" class="bg-brand-600/90 hover:bg-brand-700 text-white px-5 py-2.5 rounded-full font-bold text-xs shadow-sm transition-all flex items-center uppercase tracking-widest">
                    <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path></svg>
                    Export Excel
                </button>
                <?php if ($this->hasPermission($permProdMod, 'create')): ?>
                <a href="<?= BASE_URL ?>/admin/catalog/products/create<?= !empty($isCustomize) ? '?module=customize' : '' ?>" class="<?= !empty($isCustomize) ? 'bg-[#F25996] hover:bg-[#d8407d]' : 'bg-brand-600 hover:bg-brand-700' ?> text-white px-5 py-2.5 rounded-full font-bold text-xs shadow-md transition-all flex items-center uppercase tracking-widest">
                    <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path></svg>
                    <?= !empty($isCustomize) ? 'Add Fabric' : 'Add Product' ?>
                </a>
                <?php endif; ?>
            </div>
        </div>

        <!-- Approval Status Tabs -->
        <div class="flex items-center space-x-2 overflow-x-auto mb-4 bg-white/70 backdrop-blur p-1.5 rounded-full border border-gray-100 shadow-sm w-fit">
            <a href="<?= BASE_URL ?>/admin/catalog/products?status=all<?= !empty($isCustomize) ? '&module=customize' : '' ?>" class="px-4 py-2 rounded-full text-xs font-bold transition-all flex items-center gap-1.5 whitespace-nowrap <?= ($currentStatus === 'all' || empty($currentStatus)) ? 'bg-[#F25996] text-white shadow-sm shadow-[#F25996]/30' : 'text-gray-600 hover:bg-gray-100' ?>">
                <span>🌐 <?= !empty($isCustomize) ? 'All Fabrics' : 'All Products' ?></span>
            </a>
            <?php if (is_vendor_module_enabled() || !empty($pendingCount)): ?>
            <a href="<?= BASE_URL ?>/admin/catalog/products?status=pending<?= !empty($isCustomize) ? '&module=customize' : '' ?>" class="px-4 py-2 rounded-full text-xs font-bold transition-all flex items-center gap-1.5 whitespace-nowrap <?= ($currentStatus === 'pending') ? 'bg-[#F25996] text-white shadow-sm shadow-[#F25996]/30' : 'text-gray-600 hover:bg-gray-100' ?>">
                <span>⏳ Pending Approval</span>
                <?php if (!empty($pendingCount) && $pendingCount > 0): ?>
                    <span class="px-1.5 py-0.5 rounded-full text-[10px] font-black <?= ($currentStatus === 'pending') ? 'bg-white text-[#F25996]' : 'bg-pink-100 text-[#F25996]' ?>">
                        <?= $pendingCount ?>
                    </span>
                <?php endif; ?>
            </a>
            <?php endif; ?>
            <a href="<?= BASE_URL ?>/admin/catalog/products?status=active<?= !empty($isCustomize) ? '&module=customize' : '' ?>" class="px-4 py-2 rounded-full text-xs font-bold transition-all flex items-center gap-1.5 whitespace-nowrap <?= ($currentStatus === 'active') ? 'bg-[#F25996] text-white shadow-sm shadow-[#F25996]/30' : 'text-gray-600 hover:bg-gray-100' ?>">
                <span>✅ Active / Approved</span>
            </a>
            <?php if (is_vendor_module_enabled()): ?>
            <a href="<?= BASE_URL ?>/admin/catalog/products?status=rejected<?= !empty($isCustomize) ? '&module=customize' : '' ?>" class="px-4 py-2 rounded-full text-xs font-bold transition-all flex items-center gap-1.5 whitespace-nowrap <?= ($currentStatus === 'rejected') ? 'bg-[#F25996] text-white shadow-sm shadow-[#F25996]/30' : 'text-gray-600 hover:bg-gray-100' ?>">
                <span>❌ Rejected</span>
            </a>
            <?php endif; ?>
            <a href="<?= BASE_URL ?>/admin/catalog/products?status=draft<?= !empty($isCustomize) ? '&module=customize' : '' ?>" class="px-4 py-2 rounded-full text-xs font-bold transition-all flex items-center gap-1.5 whitespace-nowrap <?= ($currentStatus === 'draft') ? 'bg-[#F25996] text-white shadow-sm shadow-[#F25996]/30' : 'text-gray-600 hover:bg-gray-100' ?>">
                <span>📝 Drafts</span>
            </a>
        </div>

        <!-- Search Bar -->
        <div class="bg-white rounded-full shadow-sm border border-gray-100 p-1.5 flex items-center">
            <div class="flex-grow flex items-center pl-5">
                <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                <input type="text" id="searchInput" oninput="filterProducts()" placeholder="Search <?= !empty($isCustomize) ? 'fabrics' : 'products' ?> by name, slug, category<?= is_vendor_module_enabled() ? ', vendor' : '' ?>..." class="w-full pl-3 pr-4 py-2.5 bg-transparent border-none focus:ring-0 text-gray-700 placeholder-gray-400 outline-none font-medium">
            </div>
            <?php if (!$isVendor && is_vendor_module_enabled()): ?>
            <div class="border-l border-gray-200 pl-2 pr-2">
                <select id="sourceFilter" onchange="filterProducts()" class="bg-transparent border-none text-gray-600 focus:ring-0 py-2.5 pr-8 pl-4 font-semibold outline-none cursor-pointer text-xs">
                    <option value="">All Sources</option>
                    <option value="admin">🏢 In-House / Admin Direct</option>
                    <option value="vendor">🏬 Vendor Products</option>
                </select>
            </div>
            <?php endif; ?>
            <div class="border-l border-gray-200 pl-2 pr-2">
                <select id="statusFilter" onchange="filterProducts()" class="bg-transparent border-none text-gray-600 focus:ring-0 py-2.5 pr-8 pl-4 font-semibold outline-none cursor-pointer text-xs">
                    <option value="">All Statuses</option>
                    <option value="pending" <?= $currentStatus === 'pending' ? 'selected' : '' ?>>⏳ Pending Approval</option>
                    <option value="active" <?= $currentStatus === 'active' ? 'selected' : '' ?>>✅ Active / Approved</option>
                    <option value="rejected" <?= $currentStatus === 'rejected' ? 'selected' : '' ?>>❌ Rejected</option>
                    <option value="draft" <?= $currentStatus === 'draft' ? 'selected' : '' ?>>📝 Draft</option>
                </select>
            </div>
        </div>
    </div>

    <!-- Background Decoration -->
    <div class="absolute top-0 left-0 w-full h-96 bg-gradient-to-b from-brand-50/40 to-transparent pointer-events-none -z-10"></div>

    <div class="bg-white/80 backdrop-blur-xl rounded-2xl shadow-xl shadow-brand-900/5 border border-white/60 overflow-hidden ring-1 ring-gray-900/5 flex flex-col">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-gradient-to-r from-gray-50/90 to-white/90 backdrop-blur text-gray-700 text-sm font-bold uppercase tracking-wider border-b border-gray-100 shadow-sm">
                        <th class="px-6 py-5">ID</th>
                        <th class="px-6 py-5"><?= !empty($isCustomize) ? 'Fabric Name' : 'Product Name' ?></th>
                        <th class="px-6 py-5">Category / Sub</th>
                        <th class="px-6 py-5">Price</th>
                        <th class="px-6 py-5">MOQ</th>
                        <th class="px-6 py-5">Status</th>
                        <th class="px-6 py-5 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody id="products-table-body" class="divide-y divide-gray-50 text-sm">
                    <!-- Rendered Instantly via JavaScript -->
                </tbody>
            </table>
        </div>
        
        <!-- Pagination Controls -->
        <div class="px-6 py-4 border-t border-gray-100 bg-gray-50/50 flex items-center justify-between">
            <div class="text-sm text-gray-500">
                Showing <span id="page-start" class="font-bold text-gray-900">0</span> to <span id="page-end" class="font-bold text-gray-900">0</span> of <span id="total-items" class="font-bold text-gray-900">0</span> results
            </div>
            <div class="flex space-x-2">
                <button id="btn-prev" onclick="changePage(-1)" class="px-4 py-2 bg-white border border-gray-200 rounded-lg text-sm font-semibold text-gray-700 hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed shadow-sm transition-all hover:-translate-y-0.5">Previous</button>
                <button id="btn-next" onclick="changePage(1)" class="px-4 py-2 bg-white border border-gray-200 rounded-lg text-sm font-semibold text-gray-700 hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed shadow-sm transition-all hover:-translate-y-0.5">Next</button>
            </div>
        </div>
    </div>
</div>

<!-- Delete Modal -->
<div id="delete-modal" class="fixed inset-0 bg-gray-900/60 backdrop-blur-sm z-50 hidden flex items-center justify-center p-4 transition-opacity">
    <div class="bg-white/95 backdrop-blur-xl rounded-3xl shadow-2xl w-full max-w-sm overflow-hidden text-center p-8 border border-white transform transition-all">
        <div class="mx-auto flex items-center justify-center h-16 w-16 rounded-full bg-red-50 border-4 border-red-100/50 mb-5 relative">
            <svg class="h-8 w-8 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
            <div class="absolute -right-1 -top-1 w-4 h-4 bg-red-500 rounded-full animate-ping opacity-75"></div>
        </div>
        <h3 class="text-xl font-display font-bold text-gray-900 mb-2">Delete <?= !empty($isCustomize) ? 'Fabric' : 'Product' ?>?</h3>
        <p class="text-sm text-gray-500 mb-6 font-medium">Are you sure you want to delete this <?= !empty($isCustomize) ? 'fabric' : 'product' ?>? This action <strong class="text-gray-900">cannot be undone</strong>.</p>
        <div id="delete-error-msg" class="hidden mb-4 p-3 bg-red-50 border border-red-200 text-red-700 text-xs font-semibold rounded-xl text-left"></div>
        <form id="product-delete-form" action="<?= BASE_URL ?>/admin/catalog/products/delete" method="POST" class="flex space-x-3">
            <input type="hidden" name="id" id="delete-id">
            <input type="hidden" name="module" value="<?= !empty($isCustomize) ? 'customize' : '' ?>">
            <button type="button" onclick="document.getElementById('delete-modal').classList.add('hidden')" class="flex-1 px-5 py-2.5 bg-white border border-gray-200 rounded-xl text-sm font-semibold text-gray-700 hover:bg-gray-50 hover:border-gray-300 transition-all shadow-sm">Cancel</button>
            <button type="submit" id="btn-confirm-delete-prod" class="flex-1 px-5 py-2.5 bg-gradient-to-r from-red-600 to-red-500 hover:from-red-500 hover:to-red-400 text-white rounded-xl text-sm font-semibold shadow-lg shadow-red-500/30 transform hover:-translate-y-0.5 transition-all">Yes, Delete</button>
        </form>
    </div>
</div>

<script>
function openDeleteModal(id) {
    document.getElementById('delete-id').value = id;
    const errDiv = document.getElementById('delete-error-msg');
    if (errDiv) { errDiv.classList.add('hidden'); errDiv.innerText = ''; }
    const btn = document.getElementById('btn-confirm-delete-prod');
    if (btn) { btn.disabled = false; btn.innerHTML = 'Yes, Delete'; }
    document.getElementById('delete-modal').classList.remove('hidden');
}

document.addEventListener('DOMContentLoaded', () => {
    const deleteForm = document.getElementById('product-delete-form');
    if (deleteForm) {
        deleteForm.addEventListener('submit', function(e) {
            e.preventDefault();
            const btn = document.getElementById('btn-confirm-delete-prod');
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
                    btn.innerHTML = 'Yes, Delete';
                    if (errDiv) {
                        errDiv.innerText = data.message || 'Error deleting product.';
                        errDiv.classList.remove('hidden');
                    } else {
                        alert(data.message || 'Error deleting product.');
                    }
                }
            })
            .catch(err => {
                this.submit();
            });
        });
    }
});

// Client-Side Caching & Pagination Logic
const allProducts = <?= json_encode($safeProducts, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP) ?>;
let filteredProducts = [...allProducts];
let currentPage = 1;
const itemsPerPage = 10;
const baseUrl = "<?= BASE_URL ?>";
const isCustomizeMod = <?= !empty($isCustomize) ? 'true' : 'false' ?>;

function refreshTable() {
    window.location.reload();
}

function exportToExcel() {
    if (filteredProducts.length === 0) {
        alert('No products to export.');
        return;
    }
    const headers = ['ID', 'Product Name', 'Slug', 'Category', 'HSN Code', 'Sub Category', 'Base Price (Incl. GST)', 'GST (%)', 'Price (Incl. GST)', 'MOQ Override', 'Status'];
    let csvContent = "data:text/csv;charset=utf-8," + headers.join(",") + "\n";
    filteredProducts.forEach(p => {
        const effPrice = (p.discount_price && parseFloat(p.discount_price) > 0) ? parseFloat(p.discount_price) : (parseFloat(p.base_price) || 0);
        const gst = parseFloat(p.total_gst) || 0;
        const priceInclGst = effPrice;

        const row = [
            p.id,
            `"${(p.name || '').replace(/"/g, '""')}"`,
            p.slug,
            `"${(p.category_name || '').replace(/"/g, '""')}"`,
            `"${(p.category_hsn_code || '').replace(/"/g, '""')}"`,
            `"${(p.sub_category_name || '').replace(/"/g, '""')}"`,
            p.base_price,
            gst + '%',
            priceInclGst.toFixed(2),
            p.moq_override || '',
            p.status
        ];
        csvContent += row.join(",") + "\n";
    });
    const encodedUri = encodeURI(csvContent);
    const link = document.createElement("a");
    link.setAttribute("href", encodedUri);
    link.setAttribute("download", "products_export.csv");
    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);
}

function filterProducts() {
    const searchVal = document.getElementById('searchInput').value.toLowerCase();
    const statusVal = document.getElementById('statusFilter').value.toLowerCase();
    const sourceEl = document.getElementById('sourceFilter');
    const sourceVal = sourceEl ? sourceEl.value.toLowerCase() : '';
    
    filteredProducts = allProducts.filter(p => {
        const matchesSearch = (p.name || '').toLowerCase().includes(searchVal) || 
                              (p.slug || '').toLowerCase().includes(searchVal) || 
                              (p.category_name || '').toLowerCase().includes(searchVal) ||
                              (p.vendor_store_name || '').toLowerCase().includes(searchVal) ||
                              (p.category_hsn_code || '').toLowerCase().includes(searchVal);
                              
        let matchesStatus = true;
        if (statusVal) {
            if (statusVal === 'pending') {
                matchesStatus = (p.approval_status === 'pending');
            } else if (statusVal === 'rejected') {
                matchesStatus = (p.approval_status === 'rejected');
            } else if (statusVal === 'active') {
                matchesStatus = (p.status === 'active' && (p.approval_status === 'approved' || !p.approval_status));
            } else if (statusVal === 'draft') {
                matchesStatus = (p.status === 'draft' && p.approval_status !== 'rejected' && p.approval_status !== 'pending');
            }
        }

        let matchesSource = true;
        if (sourceVal === 'admin') {
            matchesSource = (!p.vendor_id || p.vendor_id === null || p.vendor_id === 0);
        } else if (sourceVal === 'vendor') {
            matchesSource = (p.vendor_id && p.vendor_id > 0);
        }

        return matchesSearch && matchesStatus && matchesSource;
    });
    
    currentPage = 1;
    renderTable();
}

function renderTable() {
    const tbody = document.getElementById('products-table-body');
    tbody.innerHTML = ''; // Clear current rows

    if (filteredProducts.length === 0) {
        tbody.innerHTML = `<tr>
            <td colspan="7" class="px-6 py-16 text-center text-gray-400 font-medium">
                <div class="flex flex-col items-center">
                    <svg class="w-12 h-12 mb-3 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path></svg>
                    No products found matching your current filter.
                </div>
            </td>
        </tr>`;
        updatePaginationInfo();
        return;
    }

    const startIndex = (currentPage - 1) * itemsPerPage;
    const endIndex = Math.min(startIndex + itemsPerPage, filteredProducts.length);
    const paginatedItems = filteredProducts.slice(startIndex, endIndex);

    const getImgUrl = (path) => {
        if (!path) return '';
        if (path.startsWith('http://') || path.startsWith('https://')) return path;
        return baseUrl.replace(/\/$/, '') + '/' + path.replace(/^\//, '');
    };

    const isSuperAdmin = <?= ($this->hasPermission('products', 'edit') && !$isVendor) ? 'true' : 'false' ?>;

    paginatedItems.forEach((prod, index) => {
        // Build the HTML for the row securely
        let variantsHtml = '';
        let minPrice = null;
        let maxPrice = null;
        let matchedGst = 0;

        if (prod.variants && prod.variants.length > 0) {
            const prices = [];
            
            if (isCustomizeMod) {
                const totalBase = prod.variants.reduce((sum, v) => sum + (parseInt(v.base_quantity) || 2), 0);
                variantsHtml = `
                    <div class="mt-2.5 bg-gray-50/80 p-2.5 rounded-xl border border-gray-100">
                        <div class="flex items-center justify-between mb-1.5">
                            <span class="text-[10px] font-bold uppercase tracking-wider text-gray-500">Configured Size Variants & Allowed Limits:</span>
                            <span class="text-[10px] font-semibold text-gray-400">Total Base: ${totalBase} pcs</span>
                        </div>
                        <div class="flex items-center gap-1.5 flex-wrap">
                `;
                prod.variants.forEach(v => {
                    const baseQty = (v.base_quantity !== null && v.base_quantity !== undefined) ? v.base_quantity : 2;
                    const maxQty = (v.max_quantity !== null && v.max_quantity !== undefined) ? v.max_quantity : 4;
                    variantsHtml += `
                        <span class="inline-flex items-center gap-1.5 text-xs font-bold bg-white text-gray-800 px-2.5 py-1 rounded-lg border border-gray-200 shadow-2xs">
                            <span class="text-amber-800 font-black">${v.name}</span>
                            <span class="text-gray-300">•</span>
                            <span class="text-gray-600">Base: ${baseQty}</span>
                            <span class="text-gray-300">|</span>
                            <span class="text-amber-700">Max: ${maxQty} pcs</span>
                        </span>
                    `;
                    const baseVal = parseFloat(v.base_price) || 0;
                    const discVal = (v.discount_price && parseFloat(v.discount_price) > 0) ? parseFloat(v.discount_price) : null;
                    prices.push(discVal ?? baseVal);
                });
                variantsHtml += `</div></div>`;
            } else {
                variantsHtml = `<div class="mt-2.5 flex flex-wrap gap-1.5">`;
                prod.variants.forEach(v => {
                    const catGst = (parseFloat(prod.category_sgst) || 0) + (parseFloat(prod.category_cgst) || 0);
                    const gst = catGst > 0 ? catGst : ((v.total_gst !== null && v.total_gst !== undefined && v.total_gst !== '' && parseFloat(v.total_gst) > 0) 
                        ? parseFloat(v.total_gst) 
                        : (((parseFloat(v.sgst) || 0) + (parseFloat(v.cgst) || 0)) || (parseFloat(prod.total_gst) || 0) || ((parseFloat(prod.sgst) || 0) + (parseFloat(prod.cgst) || 0)) || 0));
                    
                    matchedGst = gst;
                    const baseVal = parseFloat(v.base_price) || 0;
                    const discVal = (v.discount_price && parseFloat(v.discount_price) > 0) ? parseFloat(v.discount_price) : null;
                    const effVal = discVal ?? baseVal;

                    const maxAmt = effVal;
                    const baseInclGst = baseVal;

                    prices.push(maxAmt);
                    const hasDiscount = discVal && discVal < baseVal;
                    variantsHtml += `
                        <div class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-md bg-gray-50 border border-gray-100 text-[11px] font-medium text-gray-600 group-hover:bg-white group-hover:border-gray-200 group-hover:shadow-sm transition-all duration-300">
                            <span class="font-bold text-gray-800">${v.name}</span>
                            <span class="text-gray-300">•</span>
                            ${hasDiscount 
                                ? `<span class="text-brand-600 font-bold">₹${maxAmt.toFixed(2)}</span> <span class="text-gray-400 line-through text-[10px]">₹${baseInclGst.toFixed(2)}</span>` 
                                : `<span class="text-brand-600 font-bold">₹${maxAmt.toFixed(2)}</span>`}
                            ${gst > 0 ? `<span class="text-[9px] bg-emerald-50 text-emerald-700 px-1.5 py-0.5 rounded font-bold border border-emerald-100">Incl. ${gst}% GST</span>` : ''}
                            <span class="text-gray-400 ml-1">(${v.current_stock ?? v.inventory} in stock)</span>
                        </div>
                    `;
                });
                variantsHtml += `</div>`;
            }

            if (prices.length > 0) {
                minPrice = Math.min(...prices);
                maxPrice = Math.max(...prices);
            }
        }

        let priceDisplay = '';
        if (minPrice !== null && maxPrice !== null) {
            const taxBadge = matchedGst > 0 
                ? `<div class="text-[11px] font-medium text-emerald-600 flex items-center gap-1 mt-0.5"><span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>Incl. ${matchedGst}% GST</div>` 
                : `<div class="text-[10px] text-gray-400 font-medium mt-0.5">Tax exempt</div>`;

            if (minPrice !== maxPrice) {
                priceDisplay = `<div><span class="font-bold text-gray-900">₹${minPrice.toFixed(2)} – ₹${maxPrice.toFixed(2)}</span>${taxBadge}</div>`;
            } else {
                priceDisplay = `<div><span class="font-bold text-gray-900">₹${minPrice.toFixed(2)}</span>${taxBadge}</div>`;
            }
        } else {
            const productGst = (prod.total_gst !== null && prod.total_gst !== undefined && prod.total_gst !== '') 
                ? parseFloat(prod.total_gst) 
                : (((parseFloat(prod.sgst)||0) + (parseFloat(prod.cgst)||0)) || ((parseFloat(prod.category_sgst)||0) + (parseFloat(prod.category_cgst)||0)));
            const rawEffective = (prod.discount_price && parseFloat(prod.discount_price) > 0) ? parseFloat(prod.discount_price) : (parseFloat(prod.base_price) || 0);
            const fallbackPrice = (prod.max_amount && parseFloat(prod.max_amount) > 0) ? parseFloat(prod.max_amount) : rawEffective;
            const taxBadge = productGst > 0 
                ? `<div class="text-[11px] font-medium text-emerald-600 flex items-center gap-1 mt-0.5"><span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>Incl. ${productGst}% GST</div>` 
                : `<div class="text-[10px] text-gray-400 font-medium mt-0.5">Tax exempt</div>`;
            priceDisplay = `<div><span class="font-bold text-gray-900">₹${fallbackPrice.toFixed(2)}</span>${taxBadge}</div>`;
        }

        let imgHtml = prod.primary_image 
            ? `<div class="flex-shrink-0 w-14 h-14 bg-white rounded-2xl shadow-sm border border-gray-100/80 overflow-hidden group-hover:shadow-md group-hover:scale-105 group-hover:border-brand-200 transition-all duration-300 p-0.5"><img src="${getImgUrl(prod.primary_image)}" class="w-full h-full object-cover rounded-xl"></div>`
            : `<div class="flex-shrink-0 w-14 h-14 bg-gray-50/50 rounded-2xl shadow-sm border border-gray-100/80 flex items-center justify-center text-gray-400 group-hover:bg-white group-hover:border-brand-200 group-hover:scale-105 transition-all duration-300"><svg class="w-6 h-6 opacity-40" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg></div>`;

        let subCatHtml = prod.sub_category_name ? `<div class="text-gray-500 text-xs mt-1 flex items-center group-hover:text-gray-600 transition-colors"><svg class="w-3 h-3 mr-1 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path></svg> ${prod.sub_category_name}</div>` : '';
        let moqHtml = prod.moq_override 
            ? `<span class="px-2.5 py-1 bg-blue-50/50 text-blue-700 rounded-lg text-xs font-semibold border border-blue-100/50 shadow-sm">${prod.moq_override}</span>` 
            : `<?= !empty($isCustomize) ? '<span class="italic text-[#F25996] font-medium text-xs bg-pink-50 px-2 py-0.5 rounded border border-pink-100">Custom Rules</span>' : '<span class="italic text-gray-400 font-medium text-xs">Inherited</span>' ?>`;
        
        // Status Badge Logic
        let statusHtml = '';
        if (prod.approval_status === 'pending') {
            statusHtml = `
                <div>
                    <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-bold bg-amber-50 text-amber-800 border border-amber-200 shadow-sm">
                        <span class="w-1.5 h-1.5 rounded-full bg-amber-500 mr-1.5 animate-pulse"></span>
                        Pending Review
                    </span>
                </div>
            `;
        } else if (prod.approval_status === 'rejected') {
            statusHtml = `
                <div>
                    <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-bold bg-red-50 text-red-700 border border-red-200 shadow-sm cursor-help" title="${escapeHtml(prod.rejection_reason || 'Rejected by administrator')}">
                        <span class="w-1.5 h-1.5 rounded-full bg-red-500 mr-1.5"></span>
                        Rejected
                    </span>
                    ${prod.rejection_reason ? `<div class="text-[10px] text-red-600 font-medium mt-1 max-w-[150px] truncate" title="${escapeHtml(prod.rejection_reason)}">Reason: ${escapeHtml(prod.rejection_reason)}</div>` : ''}
                </div>
            `;
        } else if (prod.status === 'active' && (prod.approval_status === 'approved' || !prod.approval_status)) {
            statusHtml = `
                <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-bold bg-emerald-50 text-emerald-700 border border-emerald-200 shadow-sm">
                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 mr-1.5"></span>
                    Active
                </span>
            `;
        } else {
            statusHtml = `
                <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold bg-gray-50 text-gray-600 border border-gray-200 shadow-sm">
                    <span class="w-1.5 h-1.5 rounded-full bg-gray-400 mr-1.5"></span>
                    Draft
                </span>
            `;
        }

        let vendorBadge = '';
        if (!<?= $isVendor ? 'true' : 'false' ?> && <?= is_vendor_module_enabled() ? 'true' : 'false' ?>) {
            if (prod.vendor_id && (prod.vendor_store_name || prod.unique_vendor_id)) {
                vendorBadge = `<span class="inline-flex items-center gap-1 text-[10px] font-bold bg-indigo-50 text-indigo-700 px-2 py-0.5 rounded-md border border-indigo-100 ml-2" title="Vendor: ${escapeHtml(prod.vendor_store_name || '')} (${escapeHtml(prod.unique_vendor_id || ('VN'+prod.vendor_id))})"><svg class="w-2.5 h-2.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg> ${escapeHtml(prod.vendor_store_name || ('Vendor #' + prod.vendor_id))}</span>`;
            } else {
                vendorBadge = `<span class="inline-flex items-center gap-1 text-[10px] font-bold bg-gray-100 text-gray-700 px-2 py-0.5 rounded-md border border-gray-200 ml-2"><span class="w-1.5 h-1.5 rounded-full bg-gray-500"></span>In-House / Admin Direct</span>`;
            }
        }

        // Admin Approval Buttons
        let approvalActions = '';
        if (isSuperAdmin && prod.approval_status === 'pending') {
            approvalActions = `
                <button type="button" onclick="confirmApproveProduct(${prod.id}, '${escapeJs(prod.name)}')" class="px-2.5 py-1.5 bg-emerald-600 hover:bg-emerald-700 text-white rounded-lg text-xs font-bold shadow-sm transition-all flex items-center gap-1" title="Approve Product">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                    <span>Approve</span>
                </button>
                <button type="button" onclick="promptRejectProduct(${prod.id}, '${escapeJs(prod.name)}')" class="px-2.5 py-1.5 bg-red-50 hover:bg-red-100 text-red-700 border border-red-200 rounded-lg text-xs font-bold transition-all flex items-center gap-1" title="Reject Product">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                    <span>Reject</span>
                </button>
            `;
        } else if (isSuperAdmin && prod.approval_status === 'rejected') {
            approvalActions = `
                <button type="button" onclick="confirmApproveProduct(${prod.id}, '${escapeJs(prod.name)}')" class="px-2.5 py-1.5 bg-emerald-50 hover:bg-emerald-100 text-emerald-700 border border-emerald-200 rounded-lg text-xs font-bold transition-all flex items-center gap-1" title="Re-approve Product">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                    <span>Re-approve</span>
                </button>
            `;
        }

        const row = document.createElement('tr');
        row.className = 'group hover:bg-brand-50/20 transition-all duration-300 relative';
        row.innerHTML = `
            <td class="px-6 py-5 text-sm text-gray-400 font-medium group-hover:text-brand-600 transition-colors">#${startIndex + index + 1}</td>
            <td class="px-6 py-5">
                <div class="flex items-start space-x-4">
                    ${imgHtml}
                    <div class="flex-grow pt-0.5">
                        <div class="flex items-center flex-wrap">
                            <span class="font-bold text-gray-900 group-hover:text-brand-700 transition-colors text-base tracking-tight">${prod.name}</span>
                            ${vendorBadge}
                        </div>
                        <div class="text-[11px] text-gray-400 font-medium flex items-center mt-1">
                            <svg class="w-3 h-3 mr-1 opacity-70" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1"></path></svg>
                            ${prod.slug}
                        </div>
                        ${variantsHtml}
                    </div>
                </div>
            </td>
            <td class="px-6 py-5 text-sm">
                <div class="font-semibold text-gray-900">${prod.category_name ? prod.category_name : '<span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-semibold bg-amber-50 text-amber-700 border border-amber-200">Unassigned</span>'}</div>
                ${prod.category_hsn_code ? `<div class="mt-0.5"><span class="inline-flex items-center px-1.5 py-0.5 rounded text-[10px] font-mono font-bold bg-blue-50 text-blue-700 border border-blue-200">HSN: ${prod.category_hsn_code}</span></div>` : ''}
                ${subCatHtml}
            </td>
            <td class="px-6 py-5 text-sm font-semibold text-gray-900">
                ${priceDisplay}
            </td>
            <td class="px-6 py-5 text-sm text-gray-500">
                ${moqHtml}
                ${isCustomizeMod ? `
                    <div class="mt-1">
                        ${(parseInt(prod.stock_quantity) || 0) > 0 
                            ? `<span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">Stock: ${prod.stock_quantity} Qty</span>`
                            : `<span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold bg-rose-50 text-rose-700 border border-rose-200">⚠️ Stock: 0 Qty</span>`
                        }
                    </div>
                ` : ''}
            </td>
            <td class="px-6 py-5">
                ${statusHtml}
            </td>
            <td class="px-6 py-5 text-right">
                <div class="flex items-center justify-end space-x-2">
                    ${approvalActions}
                    <?php if ($this->hasPermission($permProdMod, 'view')): ?>
                    <a href="${baseUrl}/admin/catalog/products/view?id=${prod.id}<?= !empty($isCustomize) ? '&module=customize' : '' ?>" class="p-1 text-emerald-600 hover:text-emerald-800 transition-colors group/btn inline-flex items-center justify-center" title="View Details">
                        <svg class="w-5 h-5 group-hover/btn:scale-110 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path></svg>
                    </a>
                    <?php endif; ?>
                    <?php if ($this->hasPermission($permProdMod, 'edit')): ?>
                    <a href="${baseUrl}/admin/catalog/products/edit?id=${prod.id}<?= !empty($isCustomize) ? '&module=customize' : '' ?>" class="p-1 text-blue-600 hover:text-blue-800 transition-colors group/btn inline-flex items-center justify-center" title="Edit">
                        <i class="bi bi-pencil-square text-lg group-hover/btn:scale-110 transition-transform"></i>
                    </a>
                    <?php endif; ?>
                    <?php if ($this->hasPermission($permProdMod, 'delete')): ?>
                    <button onclick="openDeleteModal(${prod.id})" class="p-1 text-red-600 hover:text-red-800 transition-colors group/btn inline-flex items-center justify-center" title="Delete">
                        <svg class="w-5 h-5 group-hover/btn:scale-110 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                    </button>
                    <?php endif; ?>
                </div>
            </td>
        `;
        tbody.appendChild(row);
    });

    updatePaginationInfo();
}

function updatePaginationInfo() {
    const totalItems = filteredProducts.length;
    const startIndex = (currentPage - 1) * itemsPerPage;
    const endIndex = Math.min(startIndex + itemsPerPage, totalItems);

    document.getElementById('page-start').innerText = totalItems > 0 ? startIndex + 1 : 0;
    document.getElementById('page-end').innerText = endIndex;
    document.getElementById('total-items').innerText = totalItems;

    document.getElementById('btn-prev').disabled = currentPage === 1;
    document.getElementById('btn-next').disabled = endIndex >= totalItems;
}

function changePage(delta) {
    currentPage += delta;
    renderTable();
}

function confirmApproveProduct(id, name) {
    Swal.fire({
        title: 'Approve Product?',
        text: `Are you sure you want to approve "${name}"? It will be activated and immediately live on the customer storefront.`,
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: '#059669',
        cancelButtonColor: '#6b7280',
        confirmButtonText: 'Yes, Approve & Activate'
    }).then((result) => {
        if (result.isConfirmed) {
            const form = document.createElement('form');
            form.method = 'POST';
            form.action = `${baseUrl}/admin/catalog/products/approve`;
            
            const input = document.createElement('input');
            input.type = 'hidden';
            input.name = 'id';
            input.value = id;
            form.appendChild(input);
            
            document.body.appendChild(form);
            form.submit();
        }
    });
}

function promptRejectProduct(id, name) {
    Swal.fire({
        title: `Reject "${name}"?`,
        text: 'Please specify the reason for rejecting this vendor product:',
        input: 'textarea',
        inputPlaceholder: 'e.g. Inappropriate images / Pricing or tax error / Missing required specification details...',
        inputAttributes: {
            'aria-label': 'Type rejection reason here'
        },
        showCancelButton: true,
        confirmButtonColor: '#ef4444',
        cancelButtonColor: '#6b7280',
        confirmButtonText: 'Confirm Reject',
        preConfirm: (reason) => {
            if (!reason || !reason.trim()) {
                Swal.showValidationMessage('Please enter a rejection reason for the vendor.');
            }
            return reason;
        }
    }).then((result) => {
        if (result.isConfirmed) {
            const form = document.createElement('form');
            form.method = 'POST';
            form.action = `${baseUrl}/admin/catalog/products/reject`;
            
            const inputId = document.createElement('input');
            inputId.type = 'hidden';
            inputId.name = 'id';
            inputId.value = id;
            form.appendChild(inputId);

            const inputReason = document.createElement('input');
            inputReason.type = 'hidden';
            inputReason.name = 'rejection_reason';
            inputReason.value = result.value.trim();
            form.appendChild(inputReason);
            
            document.body.appendChild(form);
            form.submit();
        }
    });
}

function escapeJs(str) {
    return (str || '').replace(/'/g, "\\'").replace(/"/g, '&quot;');
}

function escapeHtml(str) {
    if (!str) return '';
    return str.toString().replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;');
}

// Initial render
document.addEventListener('DOMContentLoaded', renderTable);
</script>
