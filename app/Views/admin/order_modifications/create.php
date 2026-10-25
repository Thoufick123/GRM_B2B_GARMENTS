<?php
$itemSubtotal = 0;
foreach ($items as $item) {
    $itemSubtotal += $item['unit_price'] * $item['quantity'];
}
?>

<div class="max-w-5xl mx-auto space-y-6">

    <!-- Back link -->
    <a href="<?= BASE_URL ?>/admin/order-modifications" class="inline-flex items-center gap-2 text-sm text-gray-500 hover:text-gray-800">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
        Back to Modifications
    </a>

    <!-- Order Info -->
    <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-6">
        <div class="flex items-start justify-between">
            <div>
                <div class="flex items-center gap-2">
                    <h2 class="text-xl font-bold text-gray-900">Modify Order Bill <span class="font-mono text-brand-700">#<?= htmlspecialchars($order['order_number']) ?></span></h2>
                    <?php if (!empty($isCustomOrder)): ?>
                        <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-semibold bg-purple-100 text-purple-700 border border-purple-200">
                            ✂️ Customized Garments & Fabric Order
                        </span>
                    <?php endif; ?>
                </div>
                <p class="text-sm text-gray-500 mt-1">Buyer: <span class="font-medium text-gray-700"><?= htmlspecialchars($order['customer_name']) ?></span> · <?= htmlspecialchars($order['customer_email']) ?> · <?= htmlspecialchars($order['customer_phone']) ?></p>
            </div>
            <div class="text-right">
                <p class="text-sm text-gray-400">Current Grand Total</p>
                <p class="text-2xl font-bold text-gray-900">₹<?= number_format($order['grand_total'], 2) ?></p>
            </div>
        </div>
    </div>



    <form action="<?= BASE_URL ?>/admin/order-modifications/store" method="POST" id="modForm" enctype="multipart/form-data">
        <input type="hidden" name="order_id" value="<?= $order['id'] ?>">

        <!-- Reason -->
        <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-6 mb-6">
            <label class="block text-sm font-semibold text-gray-700 mb-2">Reason for Modification <span class="text-red-500">*</span></label>
            <textarea name="reason" required rows="3" placeholder="e.g., The Kurth Purple (Size L) is currently out of stock at our warehouse. We are offering an alternative below."
                class="w-full border border-gray-300 rounded-xl px-4 py-3 text-sm focus:ring-brand-500 focus:border-brand-500 resize-none"></textarea>
        </div>

        <!-- Items -->
        <div class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden mb-6">
            <div class="px-6 py-4 border-b border-gray-100 bg-gray-50">
                <h3 class="font-semibold text-gray-800">Order Items — Edit Quantities / Mark Removed</h3>
            </div>
            <div class="divide-y divide-gray-100" id="itemsList">
                <?php foreach ($items as $idx => $item): ?>
                <?php
                    $sgstRate = 0.0;
                    $cgstRate = 0.0;

                    $vSgst = (float)($item['variant_sgst'] ?? 0);
                    $vCgst = (float)($item['variant_cgst'] ?? 0);
                    $vTot  = (float)($item['variant_total_gst'] ?? 0);

                    $pSgst = (float)($item['product_sgst'] ?? 0);
                    $pCgst = (float)($item['product_cgst'] ?? 0);
                    $pTot  = (float)($item['product_total_gst'] ?? 0);

                    $cSgst = (float)($item['category_sgst'] ?? 0);
                    $cCgst = (float)($item['category_cgst'] ?? 0);

                    if ($vSgst > 0 || $vCgst > 0) {
                        $sgstRate = $vSgst;
                        $cgstRate = $vCgst;
                    } elseif ($vTot > 0) {
                        $sgstRate = round($vTot / 2, 2);
                        $cgstRate = round($vTot / 2, 2);
                    } elseif ($pSgst > 0 || $pCgst > 0) {
                        $sgstRate = $pSgst;
                        $cgstRate = $pCgst;
                    } elseif ($pTot > 0) {
                        $sgstRate = round($pTot / 2, 2);
                        $cgstRate = round($pTot / 2, 2);
                    } elseif ($cSgst > 0 || $cCgst > 0) {
                        $sgstRate = $cSgst;
                        $cgstRate = $cCgst;
                    }

                    $totGstRate = $sgstRate + $cgstRate;
                ?>
                <div class="flex items-center gap-4 px-6 py-5 item-row" id="item-row-<?= $idx ?>">
                    <!-- Image -->
                    <div class="w-14 h-14 rounded-lg overflow-hidden bg-gray-100 flex-shrink-0">
                        <?php if ($item['product_image']): ?>
                            <img src="<?= htmlspecialchars($item['product_image']) ?>" class="w-full h-full object-cover">
                        <?php else: ?>
                            <div class="w-full h-full flex items-center justify-center text-gray-300">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                            </div>
                        <?php endif; ?>
                    </div>

                    <!-- Name -->
                    <div class="flex-1 min-w-0">
                        <p class="font-semibold text-gray-900 truncate"><?= htmlspecialchars($item['product_name']) ?></p>
                        <?php if ($item['variant_name']): ?>
                        <p class="text-xs text-gray-400"><?= htmlspecialchars($item['variant_name']) ?></p>
                        <?php endif; ?>
                        
                        <div class="flex items-center gap-2 mt-1 mb-1 flex-wrap">
                            <span class="text-xs font-semibold text-emerald-700 bg-emerald-50 px-2 py-0.5 rounded border border-emerald-200">
                                Price (Incl. GST): ₹<?= number_format($item['unit_price'], 2) ?>
                            </span>
                            <?php if ($totGstRate > 0): ?>
                                <span class="text-xs text-gray-500">
                                    (Includes <?= number_format($totGstRate, 2) ?>% GST)
                                </span>
                            <?php endif; ?>
                            <?php if (!empty($item['moq'])): ?>
                                <span class="text-[10px] font-bold text-red-600 bg-red-50 px-1.5 py-0.5 rounded">MOQ: <?= $item['moq'] ?></span>
                            <?php endif; ?>
                        </div>

                        <p class="text-xs text-gray-500 mt-0.5">Original: <?= $item['quantity'] ?> × ₹<?= number_format($item['unit_price'], 2) ?> = <span class="font-medium">₹<?= number_format($item['quantity'] * $item['unit_price'], 2) ?></span></p>
                    </div>

                    <!-- Hidden fields -->
                    <input type="hidden" name="item_id[<?= $idx ?>]" value="<?= $item['id'] ?>">
                    <input type="hidden" name="old_qty[<?= $idx ?>]" value="<?= $item['quantity'] ?>">
                    <input type="hidden" name="old_price[<?= $idx ?>]" value="<?= $item['unit_price'] ?>">

                    <!-- New Qty -->
                    <div class="w-32 flex-shrink-0">
                        <label class="block text-xs text-gray-500 mb-1">New Qty</label>
                        <div class="flex items-center border border-gray-300 rounded-lg bg-white overflow-hidden">
                            <button type="button" onclick="changeQty(this, -1)" class="w-8 h-8 flex items-center justify-center text-gray-500 hover:bg-gray-100 hover:text-brand-600 transition-colors">-</button>
                            <input type="text" name="new_qty[<?= $idx ?>]" value="<?= $item['quantity'] ?>" min="0" max="9999" step="1"
                                class="new-qty w-full h-8 px-2 text-center text-sm border-0 focus:ring-0 appearance-none bg-transparent"
                                oninput="recalcTotal()">
                            <button type="button" onclick="changeQty(this, 1)" class="w-8 h-8 flex items-center justify-center text-gray-500 hover:bg-gray-100 hover:text-brand-600 transition-colors">+</button>
                        </div>
                    </div>

                    <!-- New Price -->
                    <div class="w-32 flex-shrink-0">
                        <label class="block text-xs text-gray-500 mb-1">New Unit Price (Incl. GST)</label>
                        <div class="relative">
                            <span class="absolute left-3 top-2 text-gray-400 text-sm">₹</span>
                            <input type="text" step="0.01" name="new_price[<?= $idx ?>]" value="<?= $item['unit_price'] ?>" min="0"
                                class="new-price w-full border border-gray-300 rounded-lg pl-7 pr-3 py-2 text-sm focus:ring-brand-500 focus:border-brand-500"
                                data-idx="<?= $idx ?>">
                        </div>
                    </div>

                    <!-- Note -->
                    <div class="w-40 flex-shrink-0">
                        <label class="block text-xs text-gray-500 mb-1">Note (optional)</label>
                        <input type="text" name="item_note[<?= $idx ?>]" placeholder="e.g., Out of stock"
                            class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-brand-500 focus:border-brand-500">
                    </div>

                    <!-- Remove toggle -->
                    <div class="flex-shrink-0">
                        <label class="block text-xs text-gray-500 mb-1">Change</label>
                        <button type="button" onclick="openReplacementModal(<?= $item['id'] ?>)"
                            class="h-10 px-3 rounded-lg border border-blue-200 text-blue-700 bg-blue-50 hover:bg-blue-100 text-xs font-semibold transition-colors mb-1">
                            Replace
                        </button>
                        <button type="button" onclick="toggleRemove(<?= $idx ?>, <?= $item['id'] ?>)"
                            id="remove-btn-<?= $idx ?>"
                            title="Remove this item" class="w-10 h-10 rounded-full border-2 border-gray-200 text-gray-400 hover:border-red-400 hover:text-red-500 transition-colors flex items-center justify-center">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                        </button>
                        <input type="hidden" name="removed_flags[<?= $idx ?>]" id="removed-flag-<?= $idx ?>" value="0">
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
            
            <!-- Add New Product Button -->
            <div class="px-6 py-4 bg-gray-50 border-t border-gray-100 flex justify-center">
                <button type="button" onclick="openProductModal()" class="inline-flex items-center gap-2 px-4 py-2 bg-white border-2 border-brand-200 text-brand-700 text-sm font-semibold rounded-lg hover:bg-brand-50 hover:border-brand-300 transition-colors">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                    <?= !empty($isCustomOrder) ? 'Add Customizable Fabric / Product' : 'Add New Product to Bill' ?>
                </button>
            </div>
        </div>

        <!-- Live Revised Total -->
        <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-6 mb-6">
            <div class="flex justify-between items-center text-sm text-gray-600 mb-3">
                <span class="font-medium text-gray-600">Original Items Total</span>
                <span class="font-semibold text-gray-800">₹<?= number_format($itemSubtotal, 2) ?></span>
            </div>

            <div class="border-t pt-3 flex justify-between items-center text-sm text-gray-600 mb-3">
                <span class="font-medium text-gray-700">Revised Items Total</span>
                <span class="text-lg font-semibold text-gray-900" id="revisedItemsTotal">₹<?= number_format($itemSubtotal, 2) ?></span>
            </div>

            <?php $defaultFee = max(0, $order['grand_total'] - $itemSubtotal); ?>
            <div class="border-t pt-3 pb-3 flex justify-between items-center bg-amber-50/60 p-3 rounded-lg border border-amber-100 mb-2">
                <div>
                    <span class="font-semibold text-gray-800 block text-sm">Other Fees (Shipping, Platform, Extra Charges, etc.)</span>
                    <span class="text-xs text-gray-500">Calculated dynamically based on items subtotal · <button type="button" onclick="resetFeeToAuto()" class="text-brand-700 underline font-semibold">Auto Recalculate</button></span>
                </div>
                <div class="relative w-36">
                    <span class="absolute left-3 top-2 text-gray-500 text-sm font-semibold">₹</span>
                    <input type="text" step="0.01" min="0" name="other_fees" id="otherFeesInput" 
                        value="<?= number_format($defaultFee, 2, '.', '') ?>" 
                        class="w-full border border-amber-300 bg-white rounded-lg pl-7 pr-3 py-1.5 text-sm font-bold text-right text-gray-900 focus:ring-brand-500 focus:border-brand-500 shadow-sm"
                        oninput="onFeeInput()">
                </div>
            </div>

            <div class="border-t border-gray-300 pt-3 flex justify-between items-center">
                <span class="font-bold text-gray-900 text-lg">Revised Grand Total</span>
                <span class="text-2xl font-bold text-brand-700" id="revisedGrandTotal">₹<?= number_format($order['grand_total'], 2) ?></span>
            </div>
            
            <div id="diffContainer" class="mt-4 bg-gray-50 rounded-lg p-5 border-2 border-gray-200">
                <div class="flex justify-between items-center text-sm mb-3" id="diffTopRow">
                    <span class="font-medium">Amount Already Paid (Original Total)</span>
                    <span class="font-semibold">₹<span id="originalGrandTotal"><?= number_format($order['grand_total'], 2, '.', '') ?></span></span>
                </div>
                <div class="border-t border-black/10 pt-3 flex justify-between items-center">
                    <span class="font-bold text-gray-900 text-lg">Balance Amount</span>
                    <span class="text-2xl font-black" id="amountDifference">₹0.00</span>
                </div>
                <p class="text-xs font-medium text-gray-500 mt-1 text-right" id="differenceNote">No difference</p>
            </div>
        </div>

        <!-- Payment Screenshot Upload (shown only when customer owes extra) -->
        <div id="paymentScreenshotSection" class="hidden bg-white rounded-xl border-2 border-blue-200 shadow-sm p-6 mb-6">
            <div class="flex items-start gap-3 mb-4">
                <div class="w-10 h-10 rounded-full bg-blue-100 flex items-center justify-center flex-shrink-0">
                    <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/></svg>
                </div>
                <div>
                    <h4 class="font-semibold text-gray-900">Customer Extra Payment Screenshot</h4>
                    <p class="text-sm text-gray-500 mt-0.5">The customer owes an extra amount. Upload their payment proof / screenshot here (optional but recommended).</p>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Left: Upload -->
                <div>
                    <label id="uploadDropZone" for="payment_screenshot"
                        class="flex flex-col items-center justify-center w-full h-36 border-2 border-dashed border-blue-300 rounded-xl cursor-pointer bg-blue-50 hover:bg-blue-100 transition-colors group">
                        <div id="uploadPlaceholder" class="text-center">
                            <svg class="w-10 h-10 text-blue-400 mx-auto mb-2 group-hover:text-blue-500 transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"/></svg>
                            <p class="text-sm font-semibold text-blue-700">Click to upload or drag & drop</p>
                            <p class="text-xs text-gray-400 mt-1">PNG, JPG, JPEG, PDF — max 5MB</p>
                        </div>
                        <img id="screenshotPreview" class="hidden max-h-28 rounded-lg object-contain" alt="Preview">
                    </label>
                    <input type="file" id="payment_screenshot" name="payment_screenshot"
                        accept="image/png,image/jpeg,image/jpg,application/pdf"
                        class="hidden" onchange="handleScreenshotUpload(this)">
                    <p id="uploadFileName" class="text-xs text-gray-500 mt-2 text-center hidden"></p>
                </div>

                <!-- Right: Status Dropdown and Note -->
                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Extra Payment Status</label>
                        <select name="extra_payment_status" id="extra_payment_status" onchange="handlePaymentStatusChange()" class="w-full pl-4 pr-10 py-2.5 border border-gray-300 rounded-xl focus:ring-brand-500 focus:border-brand-500 text-sm font-medium text-gray-700 bg-white shadow-sm appearance-none cursor-pointer">
                            <option value="complete">Complete</option>
                            <option value="pending" selected>Pending</option>
                        </select>
                        <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-4 text-gray-500" style="margin-top:-35px; right: 10px;">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                        </div>
                    </div>
                    
                    <div id="extra_payment_note_container" class="">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Pending Reason / Note</label>
                        <textarea name="extra_payment_note" id="extra_payment_note" rows="2" class="w-full px-4 py-2 border border-gray-300 rounded-xl focus:ring-brand-500 focus:border-brand-500 text-sm shadow-sm" placeholder="E.g., Expected to pay by tomorrow..."></textarea>
                    </div>
                </div>
            </div>
        </div>

        <!-- Submit -->
        <div class="flex items-center gap-4">
            <a href="<?= BASE_URL ?>/admin/order-modifications" class="px-6 py-2.5 bg-white border border-gray-200 text-gray-700 text-sm font-semibold rounded-full hover:bg-gray-50 hover:border-gray-300 transition-all shadow-xs inline-flex items-center justify-center cursor-pointer">
                Cancel
            </a>
            <button type="submit" class="flex-1 px-8 py-2.5 bg-brand-600 hover:bg-brand-700 text-white rounded-full text-sm font-bold shadow-md hover:shadow-lg transition-all flex items-center justify-center gap-2 cursor-pointer">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                <span>Update Bill & Apply Changes</span>
            </button>
        </div>

        <!-- Hidden removed[] for form submission -->
        <div id="removedContainer"></div>
    </form>
</div>

<script>
const removedItems = new Set();
const initialTotal = <?= (float)$itemSubtotal ?>;
const origGrandTotal = <?= (float)$order['grand_total'] ?>;
const origFee = Math.max(0, origGrandTotal - initialTotal);
const feeRatio = initialTotal > 0 ? (origFee / initialTotal) : 0;
let isFeeUserEdited = false;

function onFeeInput() {
    isFeeUserEdited = true;
    recalcTotal();
}

function resetFeeToAuto() {
    isFeeUserEdited = false;
    recalcTotal();
}

function toggleRemove(idx, itemId) {
    const row = document.getElementById('item-row-' + idx);
    const btn = document.getElementById('remove-btn-' + idx);
    const flag = document.getElementById('removed-flag-' + idx);
    
    if (removedItems.has(itemId)) {
        removedItems.delete(itemId);
        row.classList.remove('opacity-40');
        btn.classList.remove('border-red-500', 'text-red-500', 'bg-red-50');
        btn.classList.add('border-gray-200', 'text-gray-400');
        flag.value = '0';
    } else {
        removedItems.add(itemId);
        row.classList.add('opacity-40');
        btn.classList.remove('border-gray-200', 'text-gray-400');
        btn.classList.add('border-red-500', 'text-red-500', 'bg-red-50');
        flag.value = '1';
    }
    recalcTotal();
}

function recalcTotal() {
    let itemsTotal = 0;
    document.querySelectorAll('.item-row').forEach((row, idx) => {
        if (row.dataset.replaced === '1') return;

        // For existing items, check if removed flag is set
        const flag = document.getElementById('removed-flag-' + idx);
        if (flag && flag.value === '1') return;
        
        const qtyEl = row.querySelector('.new-qty');
        const priceEl = row.querySelector('.new-price');
        
        if (qtyEl && priceEl) {
            itemsTotal += parseFloat(qtyEl.value || 0) * parseFloat(priceEl.value || 0);
        }
    });
    
    document.getElementById('revisedItemsTotal').textContent = '₹' + itemsTotal.toFixed(2).replace(/\B(?=(\d{3})+(?!\d))/g, ',');
    
    const otherFeesInput = document.getElementById('otherFeesInput');
    
    if (!isFeeUserEdited && otherFeesInput) {
        const dynamicFee = itemsTotal * feeRatio;
        otherFeesInput.value = dynamicFee.toFixed(2);
    }
    
    const otherFees = parseFloat(otherFeesInput ? otherFeesInput.value : 0) || 0;
    const grandTotal = itemsTotal + otherFees;
    
    document.getElementById('revisedGrandTotal').textContent = '₹' + grandTotal.toFixed(2).replace(/\B(?=(\d{3})+(?!\d))/g, ',');
    
    // Calculate difference (Balance Amount = Revised Grand Total - Original Grand Total)
    const originalGrandTotalStr = document.getElementById('originalGrandTotal').textContent;
    const originalGrandTotal = parseFloat(originalGrandTotalStr || 0);
    const diff = grandTotal - originalGrandTotal;
    
    const diffEl = document.getElementById('amountDifference');
    const diffNote = document.getElementById('differenceNote');
    
    const diffContainer = document.getElementById('diffContainer');
    const diffTopRow = document.getElementById('diffTopRow');
    
    if (diff > 0.005) {
        diffEl.textContent = '+ ₹' + diff.toFixed(2).replace(/\B(?=(\d{3})+(?!\d))/g, ',');
        diffEl.className = 'text-2xl font-black text-red-700';
        diffNote.textContent = 'Buyer owes an additional amount for the added products/fees.';
        diffNote.className = 'text-xs font-semibold text-red-600 mt-1 text-right';
        diffContainer.className = 'mt-4 bg-red-50 rounded-lg p-5 border-2 border-red-200 shadow-sm';
        diffTopRow.className = 'flex justify-between items-center text-sm mb-3 text-red-800';
        // Show screenshot upload
        document.getElementById('paymentScreenshotSection').classList.remove('hidden');
    } else if (diff < -0.005) {
        diffEl.textContent = '- ₹' + Math.abs(diff).toFixed(2).replace(/\B(?=(\d{3})+(?!\d))/g, ',');
        diffEl.className = 'text-2xl font-black text-green-700';
        diffNote.textContent = 'Refund due to buyer for removed/decreased products.';
        diffNote.className = 'text-xs font-semibold text-green-600 mt-1 text-right';
        diffContainer.className = 'mt-4 bg-green-50 rounded-lg p-5 border-2 border-green-200 shadow-sm';
        diffTopRow.className = 'flex justify-between items-center text-sm mb-3 text-green-800';
        // Hide screenshot upload
        document.getElementById('paymentScreenshotSection').classList.add('hidden');
    } else {
        diffEl.textContent = '₹0.00';
        diffEl.className = 'text-2xl font-black text-gray-900';
        diffNote.textContent = 'No difference in order amount.';
        diffNote.className = 'text-xs font-medium text-gray-500 mt-1 text-right';
        diffContainer.className = 'mt-4 bg-gray-50 rounded-lg p-5 border-2 border-gray-200';
        diffTopRow.className = 'flex justify-between items-center text-sm mb-3 text-gray-600';
        // Hide screenshot upload
        document.getElementById('paymentScreenshotSection').classList.add('hidden');
    }
}

function handleScreenshotUpload(input) {
    const file = input.files[0];
    if (!file) return;

    const placeholder = document.getElementById('uploadPlaceholder');
    const preview = document.getElementById('screenshotPreview');
    const fileNameEl = document.getElementById('uploadFileName');

    fileNameEl.textContent = '✓ ' + file.name + ' (' + (file.size / 1024).toFixed(1) + ' KB)';
    fileNameEl.classList.remove('hidden');

    if (file.type.startsWith('image/')) {
        const reader = new FileReader();
        reader.onload = (e) => {
            preview.src = e.target.result;
            preview.classList.remove('hidden');
            placeholder.classList.add('hidden');
        };
        reader.readAsDataURL(file);
    } else {
        // PDF - show icon only
        placeholder.innerHTML = `<svg class="w-10 h-10 text-blue-500 mx-auto mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg><p class="text-sm font-semibold text-blue-700">${file.name}</p>`;
    }
}
function handlePaymentStatusChange() {
    const status = document.getElementById('extra_payment_status').value;
    const noteContainer = document.getElementById('extra_payment_note_container');
    if (status === 'pending') {
        noteContainer.classList.remove('hidden');
    } else {
        noteContainer.classList.add('hidden');
    }
}

// Drag & drop support (initialized on page load)
document.addEventListener('DOMContentLoaded', () => {
    const zone = document.getElementById('uploadDropZone');
    const input = document.getElementById('payment_screenshot');
    if (zone && input) {
        zone.addEventListener('dragover', (e) => { e.preventDefault(); zone.classList.add('border-blue-500'); });
        zone.addEventListener('dragleave', () => zone.classList.remove('border-blue-500'));
        zone.addEventListener('drop', (e) => {
            e.preventDefault();
            zone.classList.remove('border-blue-500');
            if (e.dataTransfer.files.length) {
                input.files = e.dataTransfer.files;
                handleScreenshotUpload(input);
            }
        });
    }
});

// Attach listeners
document.querySelectorAll('.new-qty, .new-price').forEach(el => {
    el.addEventListener('input', recalcTotal);
});

// On submit, convert removed items to proper name=removed[] format
document.getElementById('modForm').addEventListener('submit', function() {
    const container = document.getElementById('removedContainer');
    container.innerHTML = '';
    removedItems.forEach(id => {
        const inp = document.createElement('input');
        inp.type = 'hidden';
        inp.name = 'removed[]';
        inp.value = id;
        container.appendChild(inp);
    });
});

// --- Modal & New Product Logic ---
let newProductCount = 0;

let replacementItemId = null;

function openProductModal() {
    replacementItemId = null;
    document.getElementById('productSearchModal').classList.remove('hidden');
}

function openReplacementModal(itemId) {
    const originalRow = Array.from(document.querySelectorAll('#itemsList > .item-row'))
        .find(row => row.querySelector('input[name^="item_id["]')?.value === String(itemId));
    if (!originalRow || originalRow.dataset.replaced === '1') {
        return;
    }
    replacementItemId = itemId;
    document.getElementById('productSearchModal').classList.remove('hidden');
}

function closeProductModal() {
    document.getElementById('productSearchModal').classList.add('hidden');
    replacementItemId = null;
    document.getElementById('categorySelect').value = '';
    
    const subCatSelect = document.getElementById('subCategorySelect');
    subCatSelect.innerHTML = '<option value="">-- Sub Category --</option>';
    subCatSelect.disabled = true;
    
    document.getElementById('productSearchResults').innerHTML = '<div class="p-8 text-center flex flex-col items-center justify-center h-full text-gray-400"><svg class="w-12 h-12 mb-3 text-gray-200" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg><p class="text-sm">Select a category to view products</p></div>';
}

function onCategoryChange() {
    const categoryId = document.getElementById('categorySelect').value;
    const subCatSelect = document.getElementById('subCategorySelect');
    const resultsContainer = document.getElementById('productSearchResults');
    
    subCatSelect.innerHTML = '<option value="">-- Sub Category --</option>';
    subCatSelect.disabled = true;
    
    if (!categoryId) {
        resultsContainer.innerHTML = '<div class="p-8 text-center flex flex-col items-center justify-center h-full text-gray-400"><svg class="w-12 h-12 mb-3 text-gray-200" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg><p class="text-sm">Select a category to view products</p></div>';
        return;
    }

    resultsContainer.innerHTML = '<div class="p-8 text-center flex flex-col items-center justify-center h-full text-gray-400"><p class="text-sm">Loading sub-categories...</p></div>';

    fetch(`<?= BASE_URL ?>/admin/order-modifications/get-subcategories?category_id=${categoryId}&is_custom=<?= !empty($isCustomOrder) ? 1 : 0 ?>&order_id=<?= (int)$order['id'] ?>`)
        .then(res => res.json())
        .then(data => {
            if (data.length > 0) {
                let html = '<option value="">-- Select Sub Category --</option>';
                data.forEach(sc => {
                    html += `<option value="${sc.id}">${sc.name}</option>`;
                });
                // Also add an option for all products in this category
                html += '<option value="all">-- View All in Category --</option>';
                subCatSelect.innerHTML = html;
                subCatSelect.disabled = false;
                
                // Prompt user to select a sub category
                resultsContainer.innerHTML = '<div class="p-8 text-center flex flex-col items-center justify-center h-full text-gray-400"><svg class="w-12 h-12 mb-3 text-gray-200" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg><p class="text-sm">Please select a sub-category above</p></div>';
            } else {
                // No sub-categories, fetch products immediately
                fetchProducts(categoryId, '');
            }
        });
}

function onSubCategoryChange() {
    const categoryId = document.getElementById('categorySelect').value;
    let subCategoryId = document.getElementById('subCategorySelect').value;
    
    if (subCategoryId === 'all') {
        subCategoryId = ''; // fetch all for category
    } else if (!subCategoryId) {
        document.getElementById('productSearchResults').innerHTML = '<div class="p-8 text-center flex flex-col items-center justify-center h-full text-gray-400"><svg class="w-12 h-12 mb-3 text-gray-200" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg><p class="text-sm">Please select a sub-category above</p></div>';
        return;
    }
    
    fetchProducts(categoryId, subCategoryId);
}

function fetchProducts(categoryId, subCategoryId) {
    const resultsContainer = document.getElementById('productSearchResults');
    resultsContainer.innerHTML = '<div class="p-4 text-center text-sm text-gray-500">Loading products...</div>';

    let url = `<?= BASE_URL ?>/admin/order-modifications/search-products?category_id=${categoryId}&is_custom=<?= !empty($isCustomOrder) ? 1 : 0 ?>&order_id=<?= (int)$order['id'] ?>`;
    if (subCategoryId) {
        url += `&sub_category_id=${subCategoryId}`;
    }

    fetch(url)
        .then(res => res.json())
        .then(data => {
            if (data.length === 0) {
                resultsContainer.innerHTML = '<div class="p-4 text-center text-sm text-gray-500">No products found.</div>';
                return;
            }
            
            let html = '<div class="divide-y divide-gray-100">';
            data.forEach(item => {
                const price = parseFloat(item.price || 0).toFixed(2);
                const gstRate = parseFloat(item.gst_rate || 0);
                const moq = parseInt(item.moq || 1);
                const imgUrl = item.image ? (item.image.startsWith('http') ? item.image : `<?= BASE_URL ?>/${item.image}`) : '';
                html += `
                    <div class="flex items-center gap-3 p-3 hover:bg-gray-50 cursor-pointer transition-colors" onclick="addNewProductToBill('${item.product_id}', '${item.product_id}', '${item.variant_id || ''}', '${item.name.replace(/'/g, "\\'")}', '${price}', ${gstRate}, '${item.image || ''}', ${moq})">
                        <div class="w-10 h-10 rounded bg-gray-100 overflow-hidden flex-shrink-0">
                            ${imgUrl ? `<img src="${imgUrl}" class="w-full h-full object-cover">` : ''}
                        </div>
                        <div class="flex-1 min-w-0">
                            <p class="text-sm font-semibold text-gray-900 truncate">${item.name}</p>
                            <div class="flex items-center gap-2 mt-0.5 flex-wrap">
                                <span class="text-xs font-bold text-emerald-700 bg-emerald-50 px-2 py-0.5 rounded border border-emerald-200">₹${price} (Incl. GST)</span>
                                ${gstRate > 0 ? `<span class="text-[11px] text-gray-500">(Includes ${gstRate}% GST)</span>` : ''}
                                <span class="text-[10px] px-1.5 py-0.5 rounded-md ${item.stock > 0 ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700'} font-medium">Stock: ${item.stock}</span>
                            </div>
                        </div>
                        <button type="button" class="px-3 py-1.5 bg-brand-50 text-brand-700 text-xs font-semibold rounded-lg hover:bg-brand-100">Add</button>
                    </div>
                `;
            });
            html += '</div>';
            resultsContainer.innerHTML = html;
        });
}

function addNewProductToBill(id, productId, variantId, name, price, gstRate, image, moq = 1) {
    const list = document.getElementById('itemsList');
    const rowId = 'new-item-' + newProductCount;
    const replaceItemId = replacementItemId;
    const imgUrl = image ? (image.startsWith('http') ? image : `<?= BASE_URL ?>/${image}`) : '';
    
    const html = `
        <div class="flex items-center gap-4 px-6 py-5 item-row bg-green-50/30" id="${rowId}">
            <!-- Image -->
            <div class="w-14 h-14 rounded-lg overflow-hidden bg-gray-100 flex-shrink-0 border border-green-200 relative">
                ${imgUrl ? `<img src="${imgUrl}" class="w-full h-full object-cover">` : ''}
                <span class="absolute -top-1 -right-1 flex h-3 w-3"><span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-green-400 opacity-75"></span><span class="relative inline-flex rounded-full h-3 w-3 bg-green-500"></span></span>
            </div>

            <!-- Name -->
            <div class="flex-1 min-w-0">
                <div class="flex items-center gap-2 mb-0.5">
                    <span class="px-2 py-0.5 rounded-full ${replaceItemId ? 'bg-blue-100 text-blue-700' : 'bg-green-100 text-green-700'} text-[10px] font-bold uppercase tracking-wider">${replaceItemId ? 'Replacement' : 'New Added'}</span>
                </div>
                <p class="font-semibold text-gray-900 truncate">${name}</p>
                <div class="flex items-center gap-2 mt-1 flex-wrap">
                    <span class="text-xs font-semibold text-emerald-700 bg-emerald-50 px-2 py-0.5 rounded border border-emerald-200">Price (Incl. GST): ₹${price}</span>
                    ${gstRate > 0 ? `<span class="text-xs text-gray-500">(Includes ${gstRate}% GST)</span>` : ''}
                    <span class="text-[10px] font-bold text-brand-700 bg-brand-50 px-1.5 py-0.5 rounded">MOQ: ${moq}</span>
                </div>
            </div>

            <!-- Hidden fields for new item -->
            <input type="hidden" name="new_p_id[${newProductCount}]" value="${productId}">
            <input type="hidden" name="new_v_id[${newProductCount}]" value="${variantId}">
            <input type="hidden" name="new_replace_item_id[${newProductCount}]" value="${replaceItemId || ''}">

            <!-- Qty -->
            <div class="w-32 flex-shrink-0">
                <label class="block text-xs text-gray-500 mb-1">Qty</label>
                <div class="flex items-center border border-green-300 rounded-lg bg-white overflow-hidden">
                    <button type="button" onclick="changeQty(this, -${moq})" class="w-8 h-8 flex items-center justify-center text-gray-500 hover:bg-gray-100 hover:text-brand-600 transition-colors">-</button>
                    <input type="text" name="new_p_qty[${newProductCount}]" value="${moq}" min="${moq}" step="${moq}"
                        class="new-qty w-full h-8 px-2 text-center text-sm border-0 focus:ring-0 appearance-none bg-transparent"
                        oninput="recalcTotal()">
                    <button type="button" onclick="changeQty(this, ${moq})" class="w-8 h-8 flex items-center justify-center text-gray-500 hover:bg-gray-100 hover:text-brand-600 transition-colors">+</button>
                </div>
            </div>

            <!-- Price -->
            <div class="w-32 flex-shrink-0">
                <label class="block text-xs text-gray-500 mb-1">Unit Price (Incl. GST)</label>
                <div class="relative">
                    <span class="absolute left-3 top-2 text-gray-400 text-sm">₹</span>
                    <input type="text" step="0.01" name="new_p_price[${newProductCount}]" value="${price}" min="0"
                        class="new-price w-full border border-green-300 bg-white rounded-lg pl-7 pr-3 py-2 text-sm focus:ring-green-500 focus:border-green-500"
                        oninput="recalcTotal()">
                </div>
            </div>

            <!-- Note -->
            <div class="w-40 flex-shrink-0">
                <label class="block text-xs text-gray-500 mb-1">Note (optional)</label>
                <input type="text" name="new_p_note[${newProductCount}]" placeholder="e.g., Replacement"
                    class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-brand-500 focus:border-brand-500">
            </div>

            <!-- Remove toggle (just deletes row for new items) -->
            <div class="flex-shrink-0 pt-5">
                <button type="button" onclick="removeNewProductRow('${rowId}')"
                    class="w-10 h-10 rounded-full border-2 border-red-200 text-red-400 hover:bg-red-50 hover:text-red-600 transition-colors flex items-center justify-center">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                </button>
            </div>
        </div>
    `;
    
    list.insertAdjacentHTML('beforeend', html);
    if (replaceItemId) {
        const originalRow = Array.from(document.querySelectorAll('#itemsList > .item-row'))
            .find(row => row.querySelector('input[name^="item_id["]')?.value === String(replaceItemId));
        if (originalRow) {
            originalRow.dataset.replaced = '1';
            originalRow.classList.add('opacity-40');
        }
        replacementItemId = null;
    }
    newProductCount++;
    recalcTotal();
    closeProductModal();
}

function removeNewProductRow(rowId) {
    const row = document.getElementById(rowId);
    if (!row) return;
    const replaceItemId = row.querySelector('input[name^="new_replace_item_id["]')?.value;
    row.remove();
    if (replaceItemId) {
        const originalRow = Array.from(document.querySelectorAll('#itemsList > .item-row'))
            .find(itemRow => itemRow.querySelector('input[name^="item_id["]')?.value === replaceItemId);
        if (originalRow) {
            originalRow.dataset.replaced = '';
            originalRow.classList.remove('opacity-40');
        }
    }
    recalcTotal();
}
function changeQty(btn, step) {
    const input = btn.parentNode.querySelector('.new-qty');
    if (!input) return;
    
    let current = parseInt(input.value || 0);
    let min = parseInt(input.min || 0);
    
    let newVal = current + step;
    if (newVal < min) {
        newVal = min;
    }
    
    input.value = newVal;
    recalcTotal();
}
</script>

<!-- Add Product Modal -->
<div id="productSearchModal" class="fixed inset-0 z-50 hidden flex items-center justify-center p-4 sm:p-0">
    <div class="fixed inset-0 bg-gray-900/50 backdrop-blur-sm transition-opacity" onclick="closeProductModal()"></div>
    <div class="relative bg-white rounded-2xl shadow-xl w-full max-w-lg overflow-hidden flex flex-col max-h-[90vh]">
        
        <!-- Modal Header / Category Dropdown -->
        <div class="p-4 border-b border-gray-100 bg-gray-50 flex flex-col gap-3">
            <div class="flex items-center justify-between">
                <div>
                    <h3 class="font-semibold text-gray-800"><?= !empty($isCustomOrder) ? 'Add Customizable Fabric / Product' : 'Add Product' ?></h3>
                    <?php if (!empty($isCustomOrder)): ?>
                        <p class="text-[11px] text-purple-600 font-medium">Validation: Showing customizable categories only</p>
                    <?php endif; ?>
                </div>
                <button type="button" onclick="closeProductModal()" class="p-2 text-gray-400 hover:text-gray-600 rounded-xl hover:bg-gray-200 transition-colors">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>
            <div class="flex items-center gap-3">
                <div class="relative flex-1">
                    <select id="categorySelect" onchange="onCategoryChange()" class="w-full pl-4 pr-10 py-2 border border-gray-300 rounded-xl focus:ring-brand-500 focus:border-brand-500 text-sm font-medium text-gray-700 bg-white shadow-sm appearance-none cursor-pointer">
                        <option value="">-- <?= !empty($isCustomOrder) ? 'Customizable Category' : 'Main Category' ?> --</option>
                        <?php foreach ($categories ?? [] as $cat): ?>
                            <option value="<?= $cat['id'] ?>"><?= htmlspecialchars($cat['name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                    <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-4 text-gray-500">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                    </div>
                </div>
                
                <div class="relative flex-1">
                    <select id="subCategorySelect" disabled onchange="onSubCategoryChange()" class="w-full pl-4 pr-10 py-2 border border-gray-300 rounded-xl focus:ring-brand-500 focus:border-brand-500 text-sm font-medium text-gray-700 bg-white shadow-sm appearance-none cursor-pointer disabled:bg-gray-100 disabled:text-gray-400">
                        <option value="">-- Sub Category --</option>
                    </select>
                    <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-4 text-gray-500">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Results Container (Products List) -->
        <div id="productSearchResults" class="flex-1 overflow-y-auto min-h-[300px]">
            <div class="p-8 text-center flex flex-col items-center justify-center h-full text-gray-400">
                <svg class="w-12 h-12 mb-3 text-gray-200" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
                <p class="text-sm">Select a category to view products</p>
            </div>
        </div>
        
    </div>
</div>
