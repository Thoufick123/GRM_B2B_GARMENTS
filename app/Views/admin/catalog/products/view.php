<?php
$catSgst = (float)($category['sgst'] ?? 0);
$catCgst = (float)($category['cgst'] ?? 0);
$catTotalGst = $catSgst + $catCgst;

$prodSgst = $catTotalGst > 0 ? $catSgst : (float)($product['sgst'] ?? 0);
$prodCgst = $catTotalGst > 0 ? $catCgst : (float)($product['cgst'] ?? 0);
$prodTotalGst = $catTotalGst > 0 ? $catTotalGst : ((isset($product['total_gst']) && (float)$product['total_gst'] > 0) ? (float)$product['total_gst'] : ($prodSgst + $prodCgst));

$wholesalePrice = (float)($product['wholesale_price'] ?? 0);
$prodTaxAmount = $prodTotalGst > 0 ? round($wholesalePrice - ($wholesalePrice / (1 + ($prodTotalGst / 100))), 2) : 0;
$prodMaxAmount = $wholesalePrice;
?>

<div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
    <div class="mb-8 flex justify-between items-center">
        <div>
            <h1 class="text-2xl font-bold text-gray-900 font-display">Product Details</h1>
            <p class="text-sm text-gray-500 mt-1">Viewing detailed information for product #<?= $product['id'] ?></p>
        </div>
        <a href="<?= BASE_URL ?>/admin/catalog/products" class="text-gray-500 hover:text-gray-900 font-medium text-sm flex items-center transition-colors">
            <svg class="w-5 h-5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
            Back to Products
        </a>
    </div>

    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
        
        <!-- Header Section -->
        <div class="p-8 border-b border-gray-100 flex flex-col md:flex-row justify-between items-start md:items-center">
            <div>
                <h2 class="text-3xl font-bold text-gray-900 mb-2"><?= htmlspecialchars($product['name']) ?></h2>
                <p class="text-sm text-gray-500">Slug: <span class="font-mono text-gray-700"><?= htmlspecialchars($product['slug']) ?></span></p>
                <div class="mt-4 flex space-x-3 items-center flex-wrap gap-y-2">
                    <?php 
                    $vBrand = !empty($product['vendor_store_name']) ? $product['vendor_store_name'] : (!empty($product['vendor_company_name']) ? $product['vendor_company_name'] : (!empty($product['vendor_name']) ? $product['vendor_name'] : ''));
                    ?>
                    <?php if (is_vendor_module_enabled() && !empty($vBrand)): ?>
                    <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold bg-indigo-50 text-indigo-700 border border-indigo-200">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
                        Brand: <?= htmlspecialchars($vBrand) ?>
                        <?php if (!empty($product['unique_vendor_id'])): ?>
                            <span class="text-[10px] font-mono opacity-80">(<?= htmlspecialchars($product['unique_vendor_id']) ?>)</span>
                        <?php endif; ?>
                    </span>
                    <?php endif; ?>
                    <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-bold bg-brand-50 text-brand-700">
                        <?= htmlspecialchars($category['name']) ?>
                        <?= $subCategory ? ' / ' . htmlspecialchars($subCategory['name']) : '' ?>
                    </span>
                    <?php if (!empty($category['hsn_code'])): ?>
                    <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-bold bg-blue-50 text-blue-700 border border-blue-200">
                        HSN: <?= htmlspecialchars($category['hsn_code']) ?>
                    </span>
                    <?php endif; ?>
                    <?php if (!empty($sizeChart) || !empty($product['size_chart_title'])): ?>
                    <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-bold bg-purple-50 text-purple-700 border border-purple-200">
                        <svg class="w-3.5 h-3.5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 6h18M3 12h18M3 18h18M7 6v3m4-3v2m4-2v3m4-3v2M7 12v3m4-3v2m4-3v3m4-3v2"></path></svg>
                        Size Chart: <?= htmlspecialchars($sizeChart['title'] ?? ($product['size_chart_title'] ?? '')) ?>
                    </span>
                    <?php endif; ?>
                    <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-bold <?= $product['status'] === 'active' ? 'bg-green-50 text-green-700' : 'bg-gray-100 text-gray-700' ?>">
                        <?= ucfirst($product['status']) ?>
                    </span>
                </div>
            </div>
            
            <div class="mt-6 md:mt-0 text-right">
                <?php 
                $permProdMod = !empty($isCustomize) ? 'customize_products' : 'products';
                if ($this->hasPermission($permProdMod, 'edit')): 
                ?>
                <a href="<?= BASE_URL ?>/admin/catalog/products/edit?id=<?= $product['id'] ?><?= !empty($isCustomize) ? '&module=customize' : '' ?>" class="inline-flex items-center px-5 py-2.5 bg-brand-600 text-white rounded-lg text-sm font-bold hover:bg-brand-700 shadow-sm transition-all">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"></path></svg>
                    <?= !empty($isCustomize) ? 'Edit Fabric' : 'Edit Product' ?>
                </a>
                <?php endif; ?>
            </div>
        </div>

        <!-- Description Section -->
        <?php 
        $rawCustomFields = $product['custom_fields'] ?? [];
        if (is_string($rawCustomFields)) {
            $viewCustomFields = json_decode($rawCustomFields, true) ?: [];
        } elseif (is_array($rawCustomFields)) {
            $viewCustomFields = $rawCustomFields;
        } else {
            $viewCustomFields = [];
        }
        ?>
        <?php if (!empty($product['description']) || !empty($viewCustomFields)): ?>
        <div class="p-8 border-b border-gray-100 bg-gray-50 space-y-6">
            <?php if (!empty($product['description'])): ?>
            <div>
                <h3 class="text-sm font-bold text-gray-400 uppercase tracking-wider mb-2">Description</h3>
                <div class="prose prose-sm max-w-none text-gray-700 leading-relaxed">
                    <?= nl2br(htmlspecialchars($product['description'])) ?>
                </div>
            </div>
            <?php endif; ?>

            <?php if (!empty($viewCustomFields)): ?>
            <div class="pt-4 border-t border-gray-200">
                <h3 class="text-sm font-bold text-gray-400 uppercase tracking-wider mb-3">Custom Specifications / Fields</h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <?php foreach ($viewCustomFields as $cf): ?>
                        <?php if (!empty($cf['name']) || !empty($cf['value'])): ?>
                        <div class="bg-white p-4 rounded-xl border border-gray-200 shadow-2xs">
                            <h4 class="text-xs font-bold text-[#9C6228] uppercase tracking-wider mb-1"><?= htmlspecialchars($cf['name'] ?? 'Field') ?></h4>
                            <div class="text-sm text-gray-700 leading-relaxed"><?= nl2br(htmlspecialchars($cf['value'] ?? '')) ?></div>
                        </div>
                        <?php endif; ?>
                    <?php endforeach; ?>
                </div>
            </div>
            <?php endif; ?>
        </div>
        <?php endif; ?>

        <!-- Images Section -->
        <?php if (!empty($images)): ?>
        <div class="p-8 border-b border-gray-100">
            <h3 class="text-sm font-bold text-gray-400 uppercase tracking-wider mb-6">Product Images</h3>
            <div class="flex gap-4 overflow-x-auto pb-4">
                <?php foreach ($images as $img): ?>
                    <div class="relative flex-none w-48 h-48 rounded-xl border border-gray-200 overflow-hidden bg-gray-50 shadow-sm">
                        <?php if ($img['is_primary']): ?>
                            <span class="absolute top-2 left-2 bg-brand-600 text-white text-[10px] px-2 py-0.5 rounded shadow uppercase font-bold tracking-wider z-10">Primary</span>
                        <?php endif; ?>
                        <img src="<?= htmlspecialchars(get_image_url($img['image_path'])) ?>" class="w-full h-full object-contain">
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
        <?php endif; ?>

        <!-- General Info Section -->
        <div class="p-8 border-b border-gray-100">
            <h3 class="text-sm font-bold text-gray-400 uppercase tracking-wider mb-6">General Information</h3>
            <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-6">
                <div class="bg-gray-50/70 p-4 rounded-xl border border-gray-100">
                    <p class="text-xs text-gray-500 mb-1 font-medium">Wholesale Price (Incl. GST)</p>
                    <p class="text-lg font-bold text-gray-900">₹<?= number_format($wholesalePrice, 2) ?></p>
                </div>
                <div class="bg-gray-50/70 p-4 rounded-xl border border-gray-100">
                    <p class="text-xs text-gray-500 mb-1 font-medium">GST Rate</p>
                    <p class="text-lg font-bold text-gray-900"><?= number_format($prodTotalGst, 2) ?>%</p>
                    <p class="text-[10px] text-gray-400 mt-0.5">SGST: <?= number_format($prodSgst, 2) ?>% | CGST: <?= number_format($prodCgst, 2) ?>%</p>
                </div>
                <div class="bg-gray-50/70 p-4 rounded-xl border border-gray-100">
                    <p class="text-xs text-gray-500 mb-1 font-medium">GST Included</p>
                    <p class="text-lg font-bold text-amber-700">₹<?= number_format($prodTaxAmount, 2) ?></p>
                </div>
                <div class="bg-brand-50/50 p-4 rounded-xl border border-brand-100/80">
                    <p class="text-xs text-brand-700 mb-1 font-bold">Price (Incl. GST)</p>
                    <p class="text-lg font-bold text-brand-700">₹<?= number_format($prodMaxAmount, 2) ?></p>
                    <span class="inline-block mt-0.5 text-[9px] bg-emerald-50 text-emerald-700 px-1.5 py-0.5 rounded font-bold border border-emerald-100">GST Included</span>
                </div>
                <div class="bg-gray-50/70 p-4 rounded-xl border border-gray-100">
                    <p class="text-xs text-gray-500 mb-1 font-medium">MOQ Override</p>
                    <p class="text-lg font-bold text-gray-900"><?= $product['moq_override'] ?: '<span class="text-gray-400 font-normal text-sm">Category Default</span>' ?></p>
                </div>
                <?php if (!empty($category['hsn_code'])): ?>
                <div class="bg-gray-50/70 p-4 rounded-xl border border-gray-100">
                    <p class="text-xs text-gray-500 mb-1 font-medium">Category HSN Code</p>
                    <p class="text-lg font-bold font-mono text-blue-900"><?= htmlspecialchars($category['hsn_code']) ?></p>
                </div>
                <?php endif; ?>
                <?php if (is_vendor_module_enabled() && !empty($vBrand)): ?>
                <div class="bg-indigo-50/50 p-4 rounded-xl border border-indigo-100">
                    <p class="text-xs text-indigo-700 mb-1 font-bold">Vendor / Brand</p>
                    <p class="text-lg font-bold text-gray-900 capitalize"><?= htmlspecialchars($vBrand) ?></p>
                    <?php if (!empty($product['unique_vendor_id'])): ?>
                        <p class="text-[10px] text-indigo-600 font-mono mt-0.5"><?= htmlspecialchars($product['unique_vendor_id']) ?></p>
                    <?php endif; ?>
                </div>
                <?php endif; ?>
                <div class="bg-gray-50/70 p-4 rounded-xl border border-gray-100">
                    <p class="text-xs text-gray-500 mb-1 font-medium">Weight</p>
                    <p class="text-lg font-bold text-gray-900"><?= $product['weight'] ? (float)$product['weight'] . ' kg' : '<span class="text-gray-400 font-normal text-sm">Not set</span>' ?></p>
                </div>
            </div>
        </div>

        <!-- Variants Section -->
        <div class="p-8 bg-gray-50/50">
            <div class="flex items-center justify-between mb-6">
                <h3 class="text-sm font-bold text-gray-400 uppercase tracking-wider">Variants & Inventory</h3>
                <span class="bg-gray-200 text-gray-700 text-xs font-bold px-3 py-1 rounded-full"><?= count($variants) ?> variants</span>
            </div>
            
            <?php if (empty($variants)): ?>
                <div class="text-center p-8 bg-white border border-gray-200 rounded-xl">
                    <p class="text-gray-500">No variants exist for this product.</p>
                </div>
            <?php else: ?>
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    <?php foreach ($variants as $variant): 
                        $vSgst = $catTotalGst > 0 ? $catSgst : (isset($variant['sgst']) && $variant['sgst'] !== null && $variant['sgst'] !== '' ? (float)$variant['sgst'] : $prodSgst);
                        $vCgst = $catTotalGst > 0 ? $catCgst : (isset($variant['cgst']) && $variant['cgst'] !== null && $variant['cgst'] !== '' ? (float)$variant['cgst'] : $prodCgst);
                        $vTotalGst = $catTotalGst > 0 ? $catTotalGst : (isset($variant['total_gst']) && $variant['total_gst'] !== null && $variant['total_gst'] !== '' ? (float)$variant['total_gst'] : ($vSgst + $vCgst));

                        $vBase = (float)($variant['base_price'] ?? 0);
                        $vDiscount = (!empty($variant['discount_price']) && (float)$variant['discount_price'] > 0) ? (float)$variant['discount_price'] : null;
                        $vEff = $vDiscount ?? $vBase;

                        $vTaxAmount = $vTotalGst > 0 ? round($vEff - ($vEff / (1 + ($vTotalGst / 100))), 2) : 0;
                        $vBaseInclGst = $vBase;
                        $vMaxAmount = $vEff;
                        $hasDiscount = $vDiscount !== null && $vDiscount < $vBase;
                    ?>
                        <div class="bg-white border border-gray-200 rounded-xl overflow-hidden shadow-sm hover:shadow-md transition-shadow">
                            <div class="p-5 border-b border-gray-100 flex justify-between items-start bg-gray-50/80">
                                <div>
                                    <h4 class="font-bold text-gray-900"><?= htmlspecialchars($variant['name'] ?: 'Default Variant') ?></h4>
                                    <p class="text-xs text-gray-500 font-mono mt-0.5">SKU: <?= htmlspecialchars($variant['sku']) ?></p>
                                </div>
                                <div class="text-right">
                                    <div class="text-xl font-bold text-brand-600">₹<?= number_format($vMaxAmount, 2) ?></div>
                                    <?php if ($hasDiscount): ?>
                                        <p class="text-xs text-gray-400 line-through">Base incl. GST: ₹<?= number_format($vBaseInclGst, 2) ?></p>
                                    <?php endif; ?>
                                    <?php if ($vTotalGst > 0): ?>
                                        <span class="inline-block mt-1 text-[9px] bg-emerald-50 text-emerald-700 px-1.5 py-0.5 rounded font-bold border border-emerald-100">Incl. <?= number_format($vTotalGst, 1) ?>% GST</span>
                                    <?php endif; ?>
                                </div>
                            </div>
                            <div class="p-5 grid grid-cols-2 gap-4">
                                <div>
                                    <p class="text-xs text-gray-500 mb-1">Base Price (Incl. GST)</p>
                                    <p class="font-bold text-gray-900">₹<?= number_format($vBase, 2) ?></p>
                                </div>
                                <div>
                                    <p class="text-xs text-gray-500 mb-1">Discount Price (Incl. GST)</p>
                                    <p class="font-bold text-brand-600">
                                        <?= $vDiscount 
                                            ? '₹' . number_format($vDiscount, 2) 
                                            : '<span class="text-gray-400 font-normal">None</span>' ?>
                                    </p>
                                </div>
                                <div>
                                    <p class="text-xs text-gray-500 mb-1">GST Rate</p>
                                    <p class="font-bold text-gray-900"><?= number_format($vTotalGst, 2) ?>%</p>
                                    <p class="text-[9px] text-gray-400">SGST <?= number_format($vSgst, 1) ?>% + CGST <?= number_format($vCgst, 1) ?>%</p>
                                </div>
                                <div>
                                    <p class="text-xs text-gray-500 mb-1">GST Included</p>
                                    <p class="font-bold text-amber-700">₹<?= number_format($vTaxAmount, 2) ?></p>
                                </div>
                                <div class="col-span-2 bg-brand-50/40 p-3 rounded-lg border border-brand-100/50 flex justify-between items-center">
                                    <div>
                                        <p class="text-[11px] font-bold text-brand-800">Final Price (Incl. GST)</p>
                                        <p class="text-xs text-gray-500">No additional tax at checkout</p>
                                    </div>
                                    <div class="text-lg font-bold text-brand-700">
                                        ₹<?= number_format($vMaxAmount, 2) ?>
                                    </div>
                                </div>
                                <div>
                                    <p class="text-xs text-gray-500 mb-1">Current Stock</p>
                                    <p class="font-medium <?= (int)($variant['current_stock'] ?? $variant['inventory']) <= ($variant['low_stock_alert'] ?? 5) ? 'text-red-600' : 'text-gray-900' ?>">
                                        <?= htmlspecialchars((int)($variant['current_stock'] ?? $variant['inventory'])) ?> in stock
                                    </p>
                                    <p class="text-[10px] text-gray-400">Total: <?= htmlspecialchars($variant['inventory']) ?></p>
                                </div>
                                <div>
                                    <p class="text-xs text-gray-500 mb-1">Weight</p>
                                    <p class="font-medium text-gray-900"><?= $variant['weight'] ? (float)$variant['weight'] . ' kg' : 'N/A' ?></p>
                                </div>
                                <div>
                                    <p class="text-xs text-gray-500 mb-1">Status</p>
                                    <p class="font-medium <?= ($variant['status'] ?? 'active') === 'active' ? 'text-green-600' : 'text-gray-400' ?>"><?= ucfirst($variant['status'] ?? 'Active') ?></p>
                                </div>
                                <?php if (!empty($product['is_customizable'])): ?>
                                <div class="col-span-2 bg-amber-50 p-2.5 rounded-lg border border-amber-200 flex items-center justify-between text-xs">
                                    <span class="font-bold text-amber-900">Configured Limits:</span>
                                    <span class="text-amber-800 font-semibold">Base: <strong><?= (int)($variant['base_quantity'] ?? 2) ?></strong> | Max: <strong><?= (int)($variant['max_quantity'] ?? 4) ?> pcs</strong></span>
                                </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>

    </div>
</div>
