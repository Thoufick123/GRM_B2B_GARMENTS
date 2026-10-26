<?php
// Helper: render a color field (picker OR link mode)
function renderColorField($label, $fieldName, $value, $mode) {
    $isLink = ($mode === 'link');
    $hex = (preg_match('/^#[0-9A-Fa-f]{3,6}$/', $value)) ? $value : '#000000';
    $id = 'clr_' . $fieldName;
    ?>
    <div class="color-field-item">
        <label><?= htmlspecialchars($label) ?></label>
        
        <input type="hidden" name="<?= $fieldName ?>" id="<?= $id ?>_final" value="<?= htmlspecialchars($value) ?>">
        
        <!-- PICKER MODE -->
        <div class="picker-mode <?= $isLink ? 'hidden' : '' ?>">
            <div class="color-link-group">
                <input type="color" id="<?= $id ?>_picker" value="<?= htmlspecialchars($hex) ?>"
                       oninput="syncPickerToHex(this,'<?= $id ?>')">
                <input type="text" class="color-link-hex" id="<?= $id ?>_hex" value="<?= htmlspecialchars($hex) ?>"
                       oninput="syncHexToPicker(this,'<?= $id ?>')" placeholder="#000000">
                <span class="color-link-preview" id="<?= $id ?>_preview" style="background:<?= htmlspecialchars($hex) ?>;"></span>
            </div>
        </div>
        <!-- LINK MODE -->
        <div class="link-mode <?= !$isLink ? 'hidden' : '' ?>">
            <div class="color-link-group">
                <input type="text" class="color-link-hex" id="<?= $id ?>_linkval" value="<?= htmlspecialchars($value) ?>"
                       oninput="syncLinkPreview(this,'<?= $id ?>')" placeholder="var(--color) or #hex">
                <span class="color-link-preview" id="<?= $id ?>_linkpreview" style="background:<?= htmlspecialchars($hex) ?>;"></span>
            </div>
        </div>
    </div>
    <?php
}
?>

<style>
@import url('https://fonts.googleapis.com/css2?family=Playfair+Display:wght@600;700&family=Inter:wght@400;500;600;700&display=swap');
.font-serif { font-family: 'Playfair Display', serif; }
.font-sans  { font-family: 'Inter', sans-serif; }
.bg-brown   { background-color: #8C6A4F; }
.border-brown { border-color: #8C6A4F; }
.text-brown { color: #8C6A4F; }
.focus\:border-brown:focus { border-color: #8C6A4F; }

/* Color UI */
.color-field-item { display:flex; flex-direction:column; gap:5px; }
.color-field-item > label { font-size:11px; font-family:'Playfair Display',serif; font-weight:700; color:#4b5563; letter-spacing:.08em; text-transform:uppercase; }
.color-link-group { display:flex; align-items:center; gap:6px; border:1px solid #e5e7eb; border-radius:10px; padding:5px 8px; background:#fff; }
.color-link-group input[type=color] { width:30px; height:30px; border-radius:6px; border:2px solid #e5e7eb; cursor:pointer; padding:2px; background:#fff; flex-shrink:0; }
.color-link-hex { flex:1; min-width:0; border:none; font-size:12px; font-family:monospace; text-transform:uppercase; outline:none; background:transparent; color:#374151; }
.color-link-preview { width:26px; height:26px; border-radius:6px; border:1px solid #e5e7eb; flex-shrink:0; }
.colors-grid { display:grid; grid-template-columns:repeat(auto-fill,minmax(160px,1fr)); gap:14px; }

/* Mode Toggle */
.mode-toggle { display:flex; gap:0; border:1px solid #e5e7eb; border-radius:8px; overflow:hidden; }
.mode-toggle label { flex:1; text-align:center; padding:6px 10px; font-size:11px; font-weight:600; cursor:pointer; transition:all .2s; color:#6b7280; }
.mode-toggle input[type=radio] { display:none; }
.mode-toggle input[type=radio]:checked + label { background:#8C6A4F; color:#fff; }

/* View columns */
.view-cols { display:grid; grid-template-columns:1fr 1fr; gap:24px; }
@media (max-width:768px) { .view-cols { grid-template-columns:1fr; } }
.view-col { background:#f9fafb; border:1px solid #e5e7eb; border-radius:1rem; padding:1.5rem; }
.view-col h3 { font-size:1rem; font-family:'Playfair Display',serif; font-weight:700; color:#0b3c7c; margin-bottom:1.25rem; display:flex; align-items:center; gap:8px; }

/* Dimension fields */
.dim-grid { display:grid; grid-template-columns:repeat(auto-fill,minmax(140px,1fr)); gap:12px; margin-bottom:1.25rem; }
.dim-field label { display:block; font-size:11px; font-family:'Playfair Display',serif; font-weight:700; color:#4b5563; letter-spacing:.08em; text-transform:uppercase; margin-bottom:4px; }
.dim-field input { width:100%; border:1px solid #e5e7eb; border-radius:8px; padding:7px 10px; font-size:13px; outline:none; transition:border-color .2s; }
.dim-field input:focus { border-color:#8C6A4F; }
.dim-field .unit { font-size:11px; color:#9ca3af; margin-top:2px; }
</style>

<div class="max-w-[95rem] mx-auto py-8 px-4 sm:px-6 font-sans">

    <?php if ($flash = \Core\Session::getFlash('success')): ?>
    <div class="mb-6 bg-green-50 border border-green-200 text-green-700 rounded-xl px-5 py-3 font-medium text-sm flex items-center gap-2">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
        <?= htmlspecialchars($flash) ?>
    </div>
    <?php endif; ?>

    <form action="<?= BASE_URL ?>/admin/cms/product-card-settings/update" method="POST" id="pcForm">

        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-8 space-y-8">

            <div class="flex items-center justify-between border-b border-gray-100 pb-4 mb-6">
                <h2 class="text-2xl font-serif text-[#0b3c7c]">Global Product Card Settings</h2>
            </div>

            <p class="text-sm text-gray-500 -mt-4">These settings apply to <strong>every product card</strong> across the entire website — catalog, wishlist, carousels, etc.</p>

            <div class="view-cols">

                <!-- ===================== DESKTOP ===================== -->
                <div class="view-col">
                    <h3>
                        <svg class="w-5 h-5 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                        Desktop View
                    </h3>

                    <!-- Dimensions -->
                    <div class="dim-grid">
                        <div class="dim-field">
                            <label>Card Height</label>
                            <input type="text" name="desktop_card_height" value="<?= htmlspecialchars($settings['desktop']['card_height']) ?>" placeholder="auto or 50">
                            <div class="unit">vh (e.g. 50 = 50vh) or "auto"</div>
                        </div>
                        <div class="dim-field">
                            <label>Image Height</label>
                            <input type="text" name="desktop_image_height" value="<?= htmlspecialchars($settings['desktop']['image_height']) ?>" placeholder="30">
                            <div class="unit">vh (e.g. 30 = 30vh) or "auto"</div>
                        </div>
                        <div class="dim-field">
                            <label>Container Height</label>
                            <input type="text" name="desktop_container_height" value="<?= htmlspecialchars($settings['desktop']['container_height']) ?>" placeholder="auto">
                            <div class="unit">vh or "auto"</div>
                        </div>
                        <div class="dim-field">
                            <label>Container Width</label>
                            <input type="text" name="desktop_container_width" value="<?= htmlspecialchars($settings['desktop']['container_width'] ?? 'auto') ?>" placeholder="auto or 70">
                            <div class="unit">vw (e.g. 70 = 70vw) or "auto"</div>
                        </div>
                    </div>

                    <!-- Color Mode Toggle -->
                    <div class="mb-4">
                        <div class="text-xs font-bold uppercase tracking-wider text-gray-500 font-serif mb-2">Color Mode</div>
                        <div class="mode-toggle" id="desktop_mode_toggle">
                            <input type="radio" name="desktop_color_mode" id="desktop_picker_mode" value="picker"
                                <?= ($settings['desktop']['color_mode'] ?? 'picker') === 'picker' ? 'checked' : '' ?>
                                onchange="setColorMode('desktop','picker')">
                            <label for="desktop_picker_mode">🎨 Color Picker</label>

                            <input type="radio" name="desktop_color_mode" id="desktop_link_mode" value="link"
                                <?= ($settings['desktop']['color_mode'] ?? 'picker') === 'link' ? 'checked' : '' ?>
                                onchange="setColorMode('desktop','link')">
                            <label for="desktop_link_mode">🔗 Color Link</label>
                        </div>
                        <p class="text-xs text-gray-400 mt-1">Only one mode is active at a time.</p>
                    </div>

                    <!-- Colors -->
                    <div class="text-xs font-bold uppercase tracking-wider text-gray-500 font-serif mb-3">Colors</div>
                    <div class="colors-grid" id="desktop_colors">
                        <?php
                        $dm = $settings['desktop']['color_mode'] ?? 'picker';
                        renderColorField('Card BG', 'desktop_card_bg_color', $settings['desktop']['card_bg_color'], $dm);
                        renderColorField('Product Name', 'desktop_name_color', $settings['desktop']['name_color'], $dm);
                        renderColorField('Price', 'desktop_price_color', $settings['desktop']['price_color'], $dm);
                        renderColorField('Star Rating', 'desktop_star_color', $settings['desktop']['star_color'], $dm);
                        renderColorField('Button BG', 'desktop_button_bg_color', $settings['desktop']['button_bg_color'], $dm);
                        renderColorField('Button Text', 'desktop_button_text_color', $settings['desktop']['button_text_color'], $dm);
                        renderColorField('Wishlist BG', 'desktop_wishlist_bg_color', $settings['desktop']['wishlist_bg_color'] ?? '#ffffff', $dm);
                        renderColorField('Wishlist Icon', 'desktop_wishlist_icon_color', $settings['desktop']['wishlist_icon_color'] ?? '#111827', $dm);
                        ?>
                    </div>
                </div>

                <!-- ===================== MOBILE ===================== -->
                <div class="view-col">
                    <h3>
                        <svg class="w-5 h-5 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z"/></svg>
                        Mobile View
                    </h3>

                    <!-- Dimensions -->
                    <div class="dim-grid">
                        <div class="dim-field">
                            <label>Card Height</label>
                            <input type="text" name="mobile_card_height" value="<?= htmlspecialchars($settings['mobile']['card_height']) ?>" placeholder="auto or 60">
                            <div class="unit">vh (e.g. 60 = 60vh) or "auto"</div>
                        </div>
                        <div class="dim-field">
                            <label>Image Height</label>
                            <input type="text" name="mobile_image_height" value="<?= htmlspecialchars($settings['mobile']['image_height']) ?>" placeholder="25">
                            <div class="unit">vh (e.g. 25 = 25vh) or "auto"</div>
                        </div>
                        <div class="dim-field">
                            <label>Container Height</label>
                            <input type="text" name="mobile_container_height" value="<?= htmlspecialchars($settings['mobile']['container_height']) ?>" placeholder="auto">
                            <div class="unit">vh or "auto"</div>
                        </div>
                        <div class="dim-field">
                            <label>Container Width</label>
                            <input type="text" name="mobile_container_width" value="<?= htmlspecialchars($settings['mobile']['container_width'] ?? 'auto') ?>" placeholder="auto or 90">
                            <div class="unit">vw (e.g. 90 = 90vw) or "auto"</div>
                        </div>
                    </div>

                    <!-- Color Mode Toggle -->
                    <div class="mb-4">
                        <div class="text-xs font-bold uppercase tracking-wider text-gray-500 font-serif mb-2">Color Mode</div>
                        <div class="mode-toggle" id="mobile_mode_toggle">
                            <input type="radio" name="mobile_color_mode" id="mobile_picker_mode" value="picker"
                                <?= ($settings['mobile']['color_mode'] ?? 'picker') === 'picker' ? 'checked' : '' ?>
                                onchange="setColorMode('mobile','picker')">
                            <label for="mobile_picker_mode">🎨 Color Picker</label>

                            <input type="radio" name="mobile_color_mode" id="mobile_link_mode" value="link"
                                <?= ($settings['mobile']['color_mode'] ?? 'picker') === 'link' ? 'checked' : '' ?>
                                onchange="setColorMode('mobile','link')">
                            <label for="mobile_link_mode">🔗 Color Link</label>
                        </div>
                        <p class="text-xs text-gray-400 mt-1">Only one mode is active at a time.</p>
                    </div>

                    <!-- Colors -->
                    <div class="text-xs font-bold uppercase tracking-wider text-gray-500 font-serif mb-3">Colors</div>
                    <div class="colors-grid" id="mobile_colors">
                        <?php
                        $mm = $settings['mobile']['color_mode'] ?? 'picker';
                        renderColorField('Card BG', 'mobile_card_bg_color', $settings['mobile']['card_bg_color'], $mm);
                        renderColorField('Product Name', 'mobile_name_color', $settings['mobile']['name_color'], $mm);
                        renderColorField('Price', 'mobile_price_color', $settings['mobile']['price_color'], $mm);
                        renderColorField('Star Rating', 'mobile_star_color', $settings['mobile']['star_color'], $mm);
                        renderColorField('Button BG', 'mobile_button_bg_color', $settings['mobile']['button_bg_color'], $mm);
                        renderColorField('Button Text', 'mobile_button_text_color', $settings['mobile']['button_text_color'], $mm);
                        renderColorField('Wishlist BG', 'mobile_wishlist_bg_color', $settings['mobile']['wishlist_bg_color'] ?? '#ffffff', $mm);
                        renderColorField('Wishlist Icon', 'mobile_wishlist_icon_color', $settings['mobile']['wishlist_icon_color'] ?? '#111827', $mm);
                        ?>
                    </div>
                </div>

            </div><!-- end view-cols -->

            <div class="flex justify-end pt-4 border-t border-gray-100">
                <?php if ($this->hasPermission('cms_product_card', 'edit')): ?>
                <button type="submit" class="px-10 py-3 bg-gray-900 text-white font-bold text-sm rounded-full shadow-sm hover:bg-black transition-all">
                    Save All Settings
                </button>
                <?php else: ?>
                <span class="px-8 py-3 bg-gray-100 text-gray-400 font-bold text-xs uppercase tracking-wider rounded-full cursor-not-allowed">
                    Read-Only Mode
                </span>
                <?php endif; ?>
            </div>

        </div>
    </form>
</div>

<script>
// ---- Color Picker Mode ----
function syncPickerToHex(pickerEl, id) {
    var hex = pickerEl.value;
    document.getElementById(id + '_hex').value = hex;
    document.getElementById(id + '_preview').style.background = hex;
    document.getElementById(id + '_final').value = hex;
}
function syncHexToPicker(hexEl, id) {
    var val = hexEl.value.trim();
    if (/^#[0-9a-fA-F]{6}$/.test(val)) {
        document.getElementById(id + '_picker').value = val;
        document.getElementById(id + '_preview').style.background = val;
    }
    document.getElementById(id + '_final').value = val;
}

// ---- Color Link Mode ----
function syncLinkPreview(linkEl, id) {
    var val = linkEl.value.trim();
    document.getElementById(id + '_linkpreview').style.background = val;
    document.getElementById(id + '_final').value = val;
}

// ---- Mode Toggle ----
function setColorMode(view, mode) {
    var colorsGrid = document.getElementById(view + '_colors');
    var items = colorsGrid.querySelectorAll('.color-field-item');
    items.forEach(function(item) {
        var picker = item.querySelector('.picker-mode');
        var link   = item.querySelector('.link-mode');
        var finalInput = item.querySelector('input[type="hidden"]');
        if (!picker || !link) return;
        if (mode === 'picker') {
            picker.classList.remove('hidden');
            link.classList.add('hidden');
            var linkVal = link.querySelector('.color-link-hex').value;
            if (/^#[0-9a-fA-F]{6}$/.test(linkVal)) {
                var pickerInput = picker.querySelector('input[type=color]');
                if (pickerInput) pickerInput.value = linkVal;
                var hexInput = picker.querySelector('.color-link-hex');
                if (hexInput) hexInput.value = linkVal;
                var prev = picker.querySelector('.color-link-preview');
                if (prev) prev.style.background = linkVal;
                finalInput.value = linkVal;
            }
        } else {
            link.classList.remove('hidden');
            picker.classList.add('hidden');
            var hexInput = picker.querySelector('.color-link-hex');
            if (hexInput) {
                var linkHexInput = link.querySelector('.color-link-hex');
                if (linkHexInput) linkHexInput.value = hexInput.value;
                finalInput.value = hexInput.value;
                var prev = link.querySelector('.color-link-preview');
                if (prev) prev.style.background = hexInput.value;
            }
        }
    });
}
</script>
