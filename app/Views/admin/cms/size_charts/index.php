<div class="mb-8 relative z-10">
    <!-- Breadcrumbs -->
    <div class="text-sm font-medium text-gray-400 mb-2 font-serif tracking-wide">
        Dashboard <span class="mx-1 text-gray-300">›</span> CMS Management <span class="mx-1 text-gray-300">›</span> <span class="text-brand-700">Size Charts</span>
    </div>
    
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h2 class="text-5xl font-display font-extrabold text-gray-900 tracking-tight">Size Guides & Charts</h2>
            <p class="text-sm text-gray-500 mt-1">Create and customize measurement size guides for Baby & Kids, Women's, and all dress categories.</p>
        </div>
        <div class="flex items-center gap-3">
            <a href="<?= BASE_URL ?>/size-chart" target="_blank" class="px-6 py-3 border border-gray-200 text-gray-700 hover:bg-gray-50 font-bold text-xs rounded-full transition uppercase tracking-wider flex items-center gap-1.5 shadow-sm">
                <svg class="w-4 h-4 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
                View Storefront
            </a>
            <?php if ($this->hasPermission('cms_size_charts', 'create')): ?>
            <a href="<?= BASE_URL ?>/admin/cms/size-charts/create" class="bg-[#F25996] hover:bg-[#e04481] text-white px-6 py-3 rounded-full font-bold text-xs shadow-sm transition-all flex items-center gap-2 uppercase tracking-wider">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                <span>Add Size Chart</span>
            </a>
            <?php endif; ?>
        </div>
    </div>
</div>

    <!-- Stats & Filters -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <div class="bg-white p-5 rounded-2xl shadow-custom border border-gray-100 flex items-center justify-between">
            <div>
                <p class="text-xs font-bold text-gray-400 uppercase tracking-wider">Total Size Charts</p>
                <h3 class="text-2xl font-black text-gray-900 mt-1"><?= $totalCharts ?></h3>
            </div>
            <div class="w-12 h-12 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
            </div>
        </div>
        <div class="bg-white p-5 rounded-2xl shadow-custom border border-gray-100 flex items-center justify-between">
            <div>
                <p class="text-xs font-bold text-gray-400 uppercase tracking-wider">Active on Storefront</p>
                <h3 class="text-2xl font-black text-emerald-600 mt-1"><?= $activeCharts ?></h3>
            </div>
            <div class="w-12 h-12 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
            </div>
        </div>
        <div class="bg-white p-5 rounded-2xl shadow-custom border border-gray-100 flex items-center justify-between">
            <div>
                <p class="text-xs font-bold text-gray-400 uppercase tracking-wider">Categories Covered</p>
                <h3 class="text-2xl font-black text-purple-600 mt-1">
                    <?= count(array_unique(array_filter(array_column($charts, 'category_name')))) ?>
                </h3>
            </div>
            <div class="w-12 h-12 rounded-xl bg-purple-50 text-purple-600 flex items-center justify-center">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"/></svg>
            </div>
        </div>
    </div>

    <!-- Filter & Search Bar -->
    <div class="bg-white p-4 rounded-2xl shadow-custom border border-gray-100 flex flex-col sm:flex-row justify-between items-center gap-3">
        <div class="flex items-center gap-2 w-full sm:w-auto overflow-x-auto pb-1 sm:pb-0">
            <button onclick="filterCategory('all', this)" class="cat-filter-btn px-4 py-1.5 rounded-full text-xs font-bold transition-colors bg-brand-600 text-white">All Charts</button>
            <?php 
            $uniqueCats = array_unique(array_filter(array_column($charts, 'category_name')));
            foreach($uniqueCats as $cName): 
            ?>
                <button onclick="filterCategory('<?= htmlspecialchars(strtolower($cName)) ?>', this)" class="cat-filter-btn px-4 py-1.5 rounded-full text-xs font-bold transition-colors bg-gray-100 text-gray-700 hover:bg-gray-200"><?= htmlspecialchars($cName) ?></button>
            <?php endforeach; ?>
        </div>
        <div class="relative w-full sm:w-64">
            <input type="text" id="chartSearch" oninput="searchCharts()" placeholder="Search size charts..." class="w-full pl-9 pr-4 py-2 border border-gray-200 rounded-full text-xs font-medium focus:ring-2 focus:ring-brand-500 focus:border-brand-500 outline-none">
            <svg class="w-4 h-4 text-gray-400 absolute left-3 top-2.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
        </div>
    </div>

    <!-- Size Charts Table / Card Grid -->
    <div class="bg-white rounded-[1.5rem] shadow-custom border border-gray-100 overflow-hidden">
        <?php if (empty($charts)): ?>
            <div class="p-12 text-center">
                <div class="w-16 h-16 bg-amber-50 text-amber-500 rounded-full flex items-center justify-center mx-auto mb-4">
                    <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z"/></svg>
                </div>
                <h3 class="text-lg font-bold text-gray-900 mb-1">No Size Charts Found</h3>
                <p class="text-sm text-gray-500 mb-6 max-w-sm mx-auto">Create measurement charts for your dresses, baby & kids clothing, kurtis, and coord sets.</p>
                <?php if ($this->hasPermission('cms_size_charts', 'create')): ?>
                <a href="<?= BASE_URL ?>/admin/cms/size-charts/create" class="inline-flex items-center gap-2 bg-brand-600 text-white px-6 py-2.5 rounded-full font-bold text-sm shadow-sm hover:bg-brand-700 transition-colors">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                    Create Your First Size Chart
                </a>
                <?php endif; ?>
            </div>
        <?php else: ?>
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse" id="sizeChartsTable">
                    <thead>
                        <tr class="border-b border-gray-100 bg-gray-50/50 text-[11px] font-black tracking-wider text-gray-400 uppercase">
                            <th class="py-4 px-6">Size Chart / Dress Name</th>
                            <th class="py-4 px-6">Category / Type</th>
                            <th class="py-4 px-6">Tolerance Note</th>
                            <th class="py-4 px-6">Columns & Sizes</th>
                            <th class="py-4 px-6 text-center">Status</th>
                            <th class="py-4 px-6 text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 text-sm">
                        <?php foreach ($charts as $chart): 
                            $catAttr = strtolower($chart['category_name'] ?: 'general');
                            $colsList = $chart['columns'];
                            $sizesList = $chart['sizes_list'];
                        ?>
                            <tr class="chart-row hover:bg-gray-50/80 transition-colors" data-category="<?= htmlspecialchars($catAttr) ?>" data-title="<?= htmlspecialchars(strtolower($chart['title'])) ?>">
                                <td class="py-4 px-6">
                                    <div class="flex items-center gap-3">
                                        <?php if (!empty($chart['image_url'])): ?>
                                            <div class="w-10 h-10 rounded-xl border border-gray-200 bg-white overflow-hidden shrink-0 shadow-2xs">
                                                <img src="<?= htmlspecialchars($chart['image_url']) ?>" alt="Thumbnail" class="w-full h-full object-contain">
                                            </div>
                                        <?php else: ?>
                                            <span class="w-10 h-10 rounded-xl bg-amber-50 text-amber-800 border border-amber-200/60 font-black text-xs flex items-center justify-center shrink-0">
                                                📏
                                            </span>
                                        <?php endif; ?>
                                        <div>
                                            <p class="font-bold text-gray-900 leading-snug"><?= htmlspecialchars($chart['title']) ?></p>
                                            <?php if (!empty($chart['dress_type'])): ?>
                                                <span class="text-[11px] font-semibold text-gray-400"><?= htmlspecialchars($chart['dress_type']) ?></span>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </td>
                                <td class="py-4 px-6">
                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-bold bg-indigo-50 text-indigo-700 border border-indigo-100">
                                        <?= htmlspecialchars($chart['category_name'] ?: 'All Categories') ?>
                                    </span>
                                </td>
                                <td class="py-4 px-6">
                                    <span class="text-xs font-semibold text-gray-600 bg-gray-100 px-2.5 py-1 rounded-full">
                                        <?= htmlspecialchars($chart['tolerance_note']) ?>
                                    </span>
                                </td>
                                <td class="py-4 px-6">
                                    <div class="flex flex-col gap-1">
                                        <div class="text-[11px] text-gray-500 font-medium truncate max-w-xs">
                                            <strong class="text-gray-700">Cols:</strong> <?= implode(', ', array_slice($colsList, 0, 4)) ?><?= count($colsList) > 4 ? '...' : '' ?>
                                        </div>
                                        <div class="flex items-center gap-1 flex-wrap">
                                            <?php foreach(array_slice($sizesList, 0, 5) as $sz): ?>
                                                <span class="text-[10px] font-bold px-1.5 py-0.5 bg-gray-100 text-gray-700 rounded border border-gray-200"><?= htmlspecialchars($sz) ?></span>
                                            <?php endforeach; ?>
                                            <?php if (count($sizesList) > 5): ?>
                                                <span class="text-[10px] font-semibold text-gray-400">+<?= count($sizesList) - 5 ?> more</span>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </td>
                                <td class="py-4 px-6 text-center">
                                    <?php if ($this->hasPermission('cms_size_charts', 'edit')): ?>
                                    <form action="<?= BASE_URL ?>/admin/cms/size-charts/toggle" method="POST" class="inline">
                                        <input type="hidden" name="id" value="<?= $chart['id'] ?>">
                                        <button type="submit" class="inline-flex items-center px-3 py-1 rounded-full text-xs font-bold transition-colors <?= $chart['is_active'] ? 'bg-emerald-50 text-emerald-700 border border-emerald-200 hover:bg-emerald-100' : 'bg-gray-100 text-gray-500 border border-gray-200 hover:bg-gray-200' ?>" title="Click to toggle status">
                                            <span class="w-1.5 h-1.5 rounded-full <?= $chart['is_active'] ? 'bg-emerald-500' : 'bg-gray-400' ?> mr-1.5"></span>
                                            <?= $chart['is_active'] ? 'Active' : 'Hidden' ?>
                                        </button>
                                    </form>
                                    <?php else: ?>
                                    <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-bold <?= $chart['is_active'] ? 'bg-emerald-50 text-emerald-700 border border-emerald-200' : 'bg-gray-100 text-gray-500 border border-gray-200' ?>">
                                        <span class="w-1.5 h-1.5 rounded-full <?= $chart['is_active'] ? 'bg-emerald-500' : 'bg-gray-400' ?> mr-1.5"></span>
                                        <?= $chart['is_active'] ? 'Active' : 'Hidden' ?>
                                    </span>
                                    <?php endif; ?>
                                </td>
                                <td class="py-4 px-6 text-right">
                                    <div class="flex items-center justify-end gap-2">
                                        <!-- Live Preview Button -->
                                        <button type="button" onclick="openPreviewModal(<?= htmlspecialchars(json_encode($chart), ENT_QUOTES, 'UTF-8') ?>)" class="inline-flex items-center justify-center p-1 text-emerald-600 hover:text-emerald-800 transition-colors" title="Live Preview Size Chart">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                        </button>
                                        <?php if ($this->hasPermission('cms_size_charts', 'edit')): ?>
                                        <a href="<?= BASE_URL ?>/admin/cms/size-charts/edit?id=<?= $chart['id'] ?>" class="inline-flex items-center justify-center p-1 text-blue-600 hover:text-blue-800 transition-colors" title="Edit Size Chart">
                                            <i class="bi bi-pencil-square text-lg"></i>
                                        </a>
                                        <?php endif; ?>
                                        <?php if ($this->hasPermission('cms_size_charts', 'delete')): ?>
                                        <form action="<?= BASE_URL ?>/admin/cms/size-charts/delete" method="POST" class="inline" onsubmit="return confirm('Are you sure you want to delete this size chart?');">
                                            <input type="hidden" name="id" value="<?= $chart['id'] ?>">
                                            <button type="submit" class="inline-flex items-center justify-center p-1 text-red-600 hover:text-red-800 transition-colors" title="Delete">
                                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
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

<!-- Live Preview Modal Styled Exactly Like The Reference Images -->
<div id="previewModal" class="fixed inset-0 bg-black/60 backdrop-blur-xs z-50 hidden flex items-center justify-center p-4">
    <div class="bg-white rounded-[2rem] max-w-2xl w-full p-6 sm:p-8 shadow-2xl relative border border-gray-100 max-h-[90vh] overflow-y-auto transform transition-all">
        <!-- Close Button -->
        <button onclick="closePreviewModal()" class="absolute top-5 right-5 w-8 h-8 rounded-full bg-gray-100 hover:bg-gray-200 text-gray-500 flex items-center justify-center transition-colors">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
        </button>

        <!-- Preview Card Header (Styled like the Image) -->
        <div class="flex items-start justify-between gap-4 mb-6 pr-8">
            <div>
                <h2 id="modalTitle" class="text-xl sm:text-2xl font-black text-gray-900 uppercase tracking-tight font-serif inline-block pb-1.5" style="border-bottom: 4px solid #5a3e2b;">
                    CURVY PEPLUM MAXI
                </h2>
            </div>
            <div id="modalToleranceWrap">
                <span id="modalTolerance" class="inline-block px-4 py-1.5 rounded-full border border-gray-300 text-gray-700 text-xs font-bold tracking-tight bg-gray-50 whitespace-nowrap">
                    Size in inches (+ or - 0.5")
                </span>
            </div>
        </div>

        <!-- Preview Table + Image -->
        <div class="grid grid-cols-1 gap-5 items-start" id="modalGridWrap">
            <div id="modalTableWrap">
                <div class="overflow-x-auto rounded-xl border border-gray-100">
                    <table class="w-full text-center border-collapse min-w-[360px]">
                        <thead id="modalTableHead" class="bg-white text-[#5a3e2b] font-bold text-xs sm:text-sm border-b-2 border-gray-200">
                            <!-- Column Headers Dynamically Injected -->
                        </thead>
                        <tbody id="modalTableBody" class="divide-y divide-gray-100 text-xs sm:text-sm font-semibold text-gray-800">
                            <!-- Row Data Dynamically Injected -->
                        </tbody>
                    </table>
                </div>
            </div>
            <div id="modalImageWrap" class="hidden text-center bg-gray-50 p-3 rounded-xl border border-gray-200">
                <span class="text-[10px] font-bold text-gray-400 uppercase tracking-wider block mb-1.5">Measurement Diagram</span>
                <img id="modalImg" src="" alt="Size Chart Diagram" class="max-h-56 mx-auto rounded-lg object-contain">
            </div>
        </div>

        <div class="mt-6 flex justify-end">
            <button onclick="closePreviewModal()" class="px-6 py-2 bg-gray-900 hover:bg-black text-white font-bold text-xs rounded-full transition-colors">
                Close Preview
            </button>
        </div>
    </div>
</div>

<script>
function filterCategory(cat, btn) {
    document.querySelectorAll('.cat-filter-btn').forEach(b => {
        b.classList.remove('bg-brand-600', 'text-white');
        b.classList.add('bg-gray-100', 'text-gray-700');
    });
    btn.classList.remove('bg-gray-100', 'text-gray-700');
    btn.classList.add('bg-brand-600', 'text-white');

    const rows = document.querySelectorAll('.chart-row');
    rows.forEach(r => {
        const rowCat = r.getAttribute('data-category');
        if (cat === 'all' || rowCat === cat) {
            r.style.display = '';
        } else {
            r.style.display = 'none';
        }
    });
}

function searchCharts() {
    const q = document.getElementById('chartSearch').value.toLowerCase().trim();
    const rows = document.querySelectorAll('.chart-row');
    rows.forEach(r => {
        const title = r.getAttribute('data-title') || '';
        const cat = r.getAttribute('data-category') || '';
        if (title.includes(q) || cat.includes(q)) {
            r.style.display = '';
        } else {
            r.style.display = 'none';
        }
    });
}

function openPreviewModal(chart) {
    document.getElementById('modalTitle').textContent = chart.title;
    document.getElementById('modalTolerance').textContent = chart.tolerance_note || 'Size in inches (+ or - 0.5")';

    const cols = chart.columns || [];
    const rows = chart.rows || [];

    // Build Head
    let headHtml = '<tr>';
    cols.forEach(c => {
        headHtml += `<th class="py-3 px-3 sm:px-4 font-bold text-gray-900">${c}</th>`;
    });
    headHtml += '</tr>';
    document.getElementById('modalTableHead').innerHTML = headHtml;

    // Build Body
    let bodyHtml = '';
    rows.forEach((r, idx) => {
        bodyHtml += `<tr class="hover:bg-gray-50/60 transition-colors">`;
        cols.forEach((c, cIdx) => {
            const val = r[c] ?? (r[c.toLowerCase()] ?? '-');
            const isBold = (cIdx === 0 || c.toLowerCase() === 'size');
            bodyHtml += `<td class="py-3 px-3 sm:px-4 ${isBold ? 'font-black text-gray-900' : 'font-medium text-gray-700'}">${val}</td>`;
        });
        bodyHtml += `</tr>`;
    });
    document.getElementById('modalTableBody').innerHTML = bodyHtml;

    // Image
    const imgWrap = document.getElementById('modalImageWrap');
    const modalImg = document.getElementById('modalImg');
    if (chart.image_url) {
        modalImg.src = chart.image_url;
        imgWrap.classList.remove('hidden');
    } else {
        modalImg.src = '';
        imgWrap.classList.add('hidden');
    }

    document.getElementById('previewModal').classList.remove('hidden');
}

function closePreviewModal() {
    document.getElementById('previewModal').classList.add('hidden');
}
</script>
