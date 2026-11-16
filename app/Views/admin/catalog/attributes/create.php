<div class="max-w-4xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
    <div class="mb-8 flex justify-between items-center">
        <div>
            <h1 class="text-2xl font-bold text-gray-900 font-display"><?= !empty($isCustomize) ? 'Add Customize Attribute' : 'Add Attribute' ?></h1>
            <p class="text-sm text-gray-500 mt-1"><?= !empty($isCustomize) ? 'Create a new attribute for customizable workshop fabrics.' : 'Create a new attribute for products.' ?></p>
        </div>
        <a href="<?= BASE_URL ?>/admin/catalog/attributes<?= !empty($isCustomize) ? '?module=customize' : '' ?>" class="text-gray-500 hover:text-gray-900 font-medium text-sm flex items-center transition-colors">
            <svg class="w-5 h-5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
            Back to Attributes
        </a>
    </div>

    <form action="<?= BASE_URL ?>/admin/catalog/attributes/store" method="POST" class="space-y-8 bg-white p-8 rounded-2xl shadow-sm border border-gray-100">
        <input type="hidden" name="module" value="<?= !empty($isCustomize) ? 'customize' : '' ?>">
        
        <!-- Categorization -->
        <div>
            <h3 class="text-lg font-semibold text-gray-900 mb-4 border-b border-gray-100 pb-2">Location</h3>
            <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Category *</label>
                    <select name="category_id" id="category_id" required class="w-full border border-gray-300 rounded-lg focus:ring-brand-500 focus:border-brand-500 px-3 py-2">
                        <option value="">Select Category...</option>
                        <?php foreach($categories as $category): ?>
                            <option value="<?= $category['id'] ?>"><?= htmlspecialchars($category['name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Subcategory</label>
                    <select name="sub_category_id" id="sub_category_id" class="w-full border border-gray-300 rounded-lg focus:ring-brand-500 focus:border-brand-500 px-3 py-2">
                        <option value="">Select Category first...</option>
                    </select>
                </div>
            </div>
        </div>

        <!-- Basic Info -->
        <div>
            <h3 class="text-lg font-semibold text-gray-900 mb-4 border-b border-gray-100 pb-2">Basic Information</h3>
            <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Attribute Name *</label>
                    <input type="text" name="name" required class="w-full border border-gray-300 rounded-lg focus:ring-brand-500 focus:border-brand-500 px-3 py-2" oninput="this.form.attribute_code.value = this.value.toLowerCase().replace(/[^a-z0-9]+/g, '_').replace(/(^_|_$)+/g, '')">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Attribute Code (SKU/Slug) *</label>
                    <input type="text" name="attribute_code" required class="w-full border border-gray-300 rounded-lg focus:ring-brand-500 focus:border-brand-500 bg-gray-50 px-3 py-2">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Input Type *</label>
                    <select name="input_type" id="input_type_select" required class="w-full border border-gray-300 rounded-lg focus:ring-brand-500 focus:border-brand-500 px-3 py-2">
                        <option value="checkbox">Checkbox Group</option>
                        <option value="colorpicker">Color picker</option>
                    </select>
                </div>
                <div class="sm:col-span-2">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Description</label>
                    <textarea name="description" rows="3" class="w-full border border-gray-300 rounded-lg focus:ring-brand-500 focus:border-brand-500 px-3 py-2"></textarea>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Display Order</label>
                    <input type="text" name="display_order" value="0" class="w-full border border-gray-300 rounded-lg focus:ring-brand-500 focus:border-brand-500 px-3 py-2">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Status</label>
                    <select name="status" class="w-full border border-gray-300 rounded-lg focus:ring-brand-500 focus:border-brand-500 px-3 py-2">
                        <option value="active">Active</option>
                        <option value="inactive">Inactive</option>
                    </select>
                </div>
            </div>
        </div>

        <!-- Configuration -->
        <div>
            <h3 class="text-lg font-semibold text-gray-900 mb-4 border-b border-gray-100 pb-2">Configuration</h3>
            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 md:grid-cols-3">

                <label class="flex items-center space-x-3 cursor-pointer">
                    <input type="checkbox" name="is_variant" value="1" checked class="w-4 h-4 text-brand-600 border-gray-300 rounded focus:ring-brand-500">
                    <span class="text-sm font-medium text-gray-700">Is Variant</span>
                </label>
                <label class="flex items-center space-x-3 cursor-pointer">
                    <input type="checkbox" name="show_on_product_form" value="1" checked class="w-4 h-4 text-brand-600 border-gray-300 rounded focus:ring-brand-500">
                    <span class="text-sm font-medium text-gray-700">Show on Product Form</span>
                </label>
                <label class="flex items-center space-x-3 cursor-pointer">
                    <input type="checkbox" name="is_visible_on_website" value="1" checked class="w-4 h-4 text-brand-600 border-gray-300 rounded focus:ring-brand-500">
                    <span class="text-sm font-medium text-gray-700">Website Visible</span>
                </label>
            </div>
        </div>

        <!-- Value Options / Picklist -->
        <div class="bg-gray-50/50 p-6 rounded-2xl border border-gray-200/80">
            <div class="flex items-center justify-between mb-4 border-b border-gray-200 pb-3">
                <div>
                    <h3 class="text-lg font-bold text-gray-900" id="picklist_title">Value Options / Picklist</h3>
                    <p class="text-xs text-gray-500 mt-0.5" id="picklist_subtitle">Define options using the input below.</p>
                </div>
                <span id="picklist_type_badge" class="px-2.5 py-1 text-xs font-bold rounded-full bg-blue-50 text-blue-700 border border-blue-100">Standard Options</span>
            </div>
            
            <!-- Standard Text Picklist Adder (Default) -->
            <div id="text_picklist_section" class="mb-4">
                <div class="flex items-center space-x-3">
                    <input type="text" id="new_value_input" placeholder="e.g. Maple, Oak, 100% Cotton" class="flex-1 border border-gray-300 rounded-xl focus:ring-[#F25996] focus:border-[#F25996] px-3.5 py-2.5 text-sm bg-white shadow-xs">
                    <button type="button" id="add_value_btn" class="px-5 py-2.5 bg-[#F25996] hover:bg-[#e04481] text-white rounded-full text-xs font-bold uppercase tracking-wider transition-all shadow-sm">Add Option</button>
                </div>
            </div>

        
        
            <!-- Custom Color Picker Adder (Shown when input_type is colorpicker) -->
            <div id="color_picklist_section" class="mb-4 hidden">
                <div class="bg-white p-4 rounded-xl border border-gray-200 shadow-xs space-y-4">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 items-end">
                        <div>
                            <label class="block text-xs font-bold text-gray-600 uppercase tracking-wider mb-1">Color Name *</label>
                            <input type="text" id="color_name_input" placeholder="e.g. Crimson Red, Navy Blue, Forest Green" class="w-full h-10 border border-gray-300 rounded-lg focus:ring-[#F25996] focus:border-[#F25996] px-3 text-sm">
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-gray-600 uppercase tracking-wider mb-1">Color & Hex Code *</label>
                            <div class="flex items-center gap-3">
                                <div class="relative w-10 h-10 rounded-lg border-2 border-gray-300 overflow-hidden shadow-xs shrink-0 cursor-pointer flex items-center justify-center hover:scale-105 transition-transform" id="color_swatch_box" style="background-color: #ef4444;">
                                    <input type="color" id="color_picker_input" value="#ef4444" class="absolute inset-0 opacity-0 w-full h-full cursor-pointer">
                                </div>
                                <input type="text" id="color_hex_input" value="#EF4444" placeholder="#EF4444" maxlength="7" class="flex-1 min-w-0 h-10 border border-gray-300 rounded-lg focus:ring-[#F25996] focus:border-[#F25996] px-3 text-sm font-mono uppercase font-bold text-gray-800">
                                <button type="button" id="add_color_btn" class="px-5 h-10 bg-[#F25996] hover:bg-[#e04481] text-white rounded-full text-xs font-bold uppercase tracking-wider transition-all shadow-sm inline-flex items-center justify-center gap-1.5 cursor-pointer whitespace-nowrap shrink-0">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"/></svg>
                                    Add
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Quick Color Presets -->
                    <div>
                        <span class="text-xs font-medium text-gray-500 mr-2">Quick Palette:</span>
                        <div class="inline-flex flex-wrap gap-1.5 align-middle mt-1" id="quick_palette">
                            <?php
                            $presetPalette = [
                                ['name' => 'Red', 'hex' => '#EF4444'],
                                ['name' => 'Crimson', 'hex' => '#DC2626'],
                                ['name' => 'Orange', 'hex' => '#F97316'],
                                ['name' => 'Amber', 'hex' => '#F59E0B'],
                                ['name' => 'Yellow', 'hex' => '#EAB308'],
                                ['name' => 'Green', 'hex' => '#10B981'],
                                ['name' => 'Emerald', 'hex' => '#059669'],
                                ['name' => 'Teal', 'hex' => '#14B8A6'],
                                ['name' => 'Cyan', 'hex' => '#06B6D4'],
                                ['name' => 'Blue', 'hex' => '#3B82F6'],
                                ['name' => 'Navy', 'hex' => '#1E3A8A'],
                                ['name' => 'Indigo', 'hex' => '#6366F1'],
                                ['name' => 'Purple', 'hex' => '#8B5CF6'],
                                ['name' => 'Pink', 'hex' => '#EC4899'],
                                ['name' => 'Rose', 'hex' => '#F43F5E'],
                                ['name' => 'Brown', 'hex' => '#78350F'],
                                ['name' => 'Black', 'hex' => '#000000'],
                                ['name' => 'White', 'hex' => '#FFFFFF'],
                                ['name' => 'Gray', 'hex' => '#6B7280'],
                            ];
                            foreach($presetPalette as $pColor):
                            ?>
                            <button type="button" onclick="setPresetColor('<?= $pColor['name'] ?>', '<?= $pColor['hex'] ?>')" class="w-6 h-6 rounded-full border border-gray-300 hover:scale-110 hover:shadow-md transition-transform" style="background-color: <?= $pColor['hex'] ?>" title="<?= $pColor['name'] ?> (<?= $pColor['hex'] ?>)"></button>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
            </div>
            
            <div id="values_container" class="flex flex-wrap gap-2.5 min-h-[42px] p-2 bg-white/70 rounded-xl border border-gray-200">
                <!-- Values will be appended here as hidden inputs and visual tags -->
            </div>
        </div>

        <div class="pt-6 border-t border-gray-100 flex justify-end space-x-4 items-center">
            <a href="<?= BASE_URL ?>/admin/catalog/attributes<?= !empty($isCustomize) ? '?module=customize' : '' ?>" class="px-6 py-2.5 bg-white border border-gray-200 text-gray-700 text-sm font-semibold rounded-full hover:bg-gray-50 hover:border-gray-300 transition-all shadow-xs inline-flex items-center justify-center cursor-pointer">Cancel</a>
            <button type="submit" class="px-6 py-2.5 bg-[#F25996] hover:bg-[#e04481] text-white rounded-full text-sm font-bold shadow-md shadow-brand-600/20 transform hover:-translate-y-0.5 transition-all inline-flex items-center justify-center">Save Attribute</button>
        </div>
    </form>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const categorySelect = document.getElementById('category_id');
    const subCategorySelect = document.getElementById('sub_category_id');
    const inputTypeSelect = document.getElementById('input_type_select');
    const textPicklistSection = document.getElementById('text_picklist_section');
    const colorPicklistSection = document.getElementById('color_picklist_section');
    const picklistTitle = document.getElementById('picklist_title');
    const picklistSubtitle = document.getElementById('picklist_subtitle');
    const picklistTypeBadge = document.getElementById('picklist_type_badge');
    const valuesContainer = document.getElementById('values_container');

    // Input elements
    const newValueInput = document.getElementById('new_value_input');
    const addValueBtn = document.getElementById('add_value_btn');

    const colorNameInput = document.getElementById('color_name_input');
    const colorPickerInput = document.getElementById('color_picker_input');
    const colorHexInput = document.getElementById('color_hex_input');
    const colorSwatchBox = document.getElementById('color_swatch_box');
    const addColorBtn = document.getElementById('add_color_btn');

    let valueIdCounter = 0;

    // Toggle Input Type View
    function updateInputTypeUI() {
        const isColor = inputTypeSelect.value === 'colorpicker';
        if (isColor) {
            textPicklistSection.classList.add('hidden');
            colorPicklistSection.classList.remove('hidden');
            picklistTitle.textContent = 'Color Palette Options';
            picklistSubtitle.textContent = 'Add color names and their corresponding hex swatches below.';
            picklistTypeBadge.textContent = 'Color Picker Options';
            picklistTypeBadge.className = 'px-2.5 py-1 text-xs font-bold rounded-full bg-pink-50 text-[#F25996] border border-pink-200';
        } else {
            textPicklistSection.classList.remove('hidden');
            colorPicklistSection.classList.add('hidden');
            picklistTitle.textContent = 'Value Options / Picklist';
            picklistSubtitle.textContent = 'Define options using the input below.';
            picklistTypeBadge.textContent = 'Standard Options';
            picklistTypeBadge.className = 'px-2.5 py-1 text-xs font-bold rounded-full bg-blue-50 text-blue-700 border border-blue-100';
        }
    }

    inputTypeSelect.addEventListener('change', updateInputTypeUI);
    updateInputTypeUI();

    // Color picker & hex text sync
    colorPickerInput.addEventListener('input', function() {
        const hex = this.value.toUpperCase();
        colorHexInput.value = hex;
        colorSwatchBox.style.backgroundColor = hex;
    });

    colorHexInput.addEventListener('input', function() {
        let hex = this.value.trim();
        if (!hex.startsWith('#')) hex = '#' + hex;
        if (/^#[0-9A-Fa-f]{6}$/.test(hex)) {
            colorPickerInput.value = hex;
            colorSwatchBox.style.backgroundColor = hex;
        }
    });

    window.setPresetColor = function(name, hex) {
        if (!colorNameInput.value.trim()) {
            colorNameInput.value = name;
        }
        colorPickerInput.value = hex;
        colorHexInput.value = hex.toUpperCase();
        colorSwatchBox.style.backgroundColor = hex;
        colorNameInput.focus();
    };

    // Standard Value Adder
    function addStandardValue(valStr) {
        valStr = valStr.trim();
        if (!valStr) return;

        const values = valStr.split(',').map(v => v.trim()).filter(v => v !== '');

        values.forEach(val => {
            const existingInputs = valuesContainer.querySelectorAll('input[name="values[]"]');
            for (let input of existingInputs) {
                if (input.value.toLowerCase() === val.toLowerCase()) return;
            }

            const id = 'val_' + valueIdCounter++;
            const wrapper = document.createElement('div');
            wrapper.id = id;
            wrapper.className = 'inline-flex items-center bg-gray-100 text-gray-800 text-sm font-medium px-3.5 py-1.5 rounded-full group transition-all hover:bg-gray-200';
            
            const span = document.createElement('span');
            span.textContent = val;
            wrapper.appendChild(span);
            
            const hiddenValue = document.createElement('input');
            hiddenValue.type = 'hidden';
            hiddenValue.name = 'values[]';
            hiddenValue.value = val;
            wrapper.appendChild(hiddenValue);

            const hiddenColor = document.createElement('input');
            hiddenColor.type = 'hidden';
            hiddenColor.name = 'color_codes[]';
            hiddenColor.value = '';
            wrapper.appendChild(hiddenColor);
            
            const removeBtn = document.createElement('button');
            removeBtn.type = 'button';
            removeBtn.className = 'ml-2 text-gray-400 hover:text-red-500 focus:outline-none transition-colors';
            removeBtn.innerHTML = '<svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>';
            removeBtn.onclick = function() { wrapper.remove(); };
            wrapper.appendChild(removeBtn);
            
            valuesContainer.appendChild(wrapper);
        });
        
        newValueInput.value = '';
        newValueInput.focus();
    }

    // Color Value Adder
    function addColorValue(nameStr, hexStr) {
        nameStr = nameStr.trim();
        hexStr = hexStr.trim();
        if (!hexStr.startsWith('#')) hexStr = '#' + hexStr;
        if (!nameStr) {
            nameStr = hexStr;
        }

        const existingInputs = valuesContainer.querySelectorAll('input[name="values[]"]');
        for (let input of existingInputs) {
            if (input.value.toLowerCase() === nameStr.toLowerCase()) return;
        }

        const id = 'val_' + valueIdCounter++;
        const wrapper = document.createElement('div');
        wrapper.id = id;
        wrapper.className = 'inline-flex items-center gap-2 bg-white border border-gray-200 text-gray-800 text-sm font-semibold px-3 py-1.5 rounded-full shadow-xs group hover:border-gray-300 transition-all';
        
        // Visual Color Swatch
        const swatch = document.createElement('span');
        swatch.className = 'w-4 h-4 rounded-full border border-gray-300 shadow-xs shrink-0';
        swatch.style.backgroundColor = hexStr;
        wrapper.appendChild(swatch);

        // Name
        const nameSpan = document.createElement('span');
        nameSpan.textContent = nameStr;
        wrapper.appendChild(nameSpan);

        // Hex Code
        const hexSpan = document.createElement('span');
        hexSpan.className = 'text-xs text-gray-400 font-mono font-normal';
        hexSpan.textContent = hexStr.toUpperCase();
        wrapper.appendChild(hexSpan);

        // Hidden inputs
        const hiddenValue = document.createElement('input');
        hiddenValue.type = 'hidden';
        hiddenValue.name = 'values[]';
        hiddenValue.value = nameStr;
        wrapper.appendChild(hiddenValue);

        const hiddenColor = document.createElement('input');
        hiddenColor.type = 'hidden';
        hiddenColor.name = 'color_codes[]';
        hiddenColor.value = hexStr.toUpperCase();
        wrapper.appendChild(hiddenColor);

        // Remove Button
        const removeBtn = document.createElement('button');
        removeBtn.type = 'button';
        removeBtn.className = 'ml-1 text-gray-400 hover:text-red-500 focus:outline-none transition-colors';
        removeBtn.innerHTML = '<svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>';
        removeBtn.onclick = function() { wrapper.remove(); };
        wrapper.appendChild(removeBtn);

        valuesContainer.appendChild(wrapper);

        colorNameInput.value = '';
        colorNameInput.focus();
    }

    addValueBtn.addEventListener('click', function() {
        addStandardValue(newValueInput.value);
    });

    newValueInput.addEventListener('keypress', function(e) {
        if (e.key === 'Enter') {
            e.preventDefault();
            addStandardValue(newValueInput.value);
        }
    });

    addColorBtn.addEventListener('click', function() {
        addColorValue(colorNameInput.value, colorHexInput.value);
    });

    colorNameInput.addEventListener('keypress', function(e) {
        if (e.key === 'Enter') {
            e.preventDefault();
            addColorValue(colorNameInput.value, colorHexInput.value);
        }
    });

    colorHexInput.addEventListener('keypress', function(e) {
        if (e.key === 'Enter') {
            e.preventDefault();
            addColorValue(colorNameInput.value, colorHexInput.value);
        }
    });

    // Subcategory loading
    categorySelect.addEventListener('change', function() {
        const catId = this.value;
        if (!catId) {
            subCategorySelect.innerHTML = '<option value="">Select Category first...</option>';
            subCategorySelect.disabled = true;
            subCategorySelect.dispatchEvent(new Event('change'));
            return;
        }

        subCategorySelect.disabled = true;
        subCategorySelect.innerHTML = '<option value="">Loading...</option>';
        subCategorySelect.dispatchEvent(new Event('change'));

        const formModule = document.querySelector('input[name="module"]')?.value || '';
        const moduleParam = formModule ? '&module=' + encodeURIComponent(formModule) : '';

        fetch('/admin/catalog/subcategories/api-get-by-category?category_id=' + encodeURIComponent(catId) + moduleParam, {
            headers: {
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(res => res.json())
        .then(data => {
            subCategorySelect.innerHTML = '<option value="">None</option>';
            if (Array.isArray(data)) {
                data.forEach(sub => {
                    const option = document.createElement('option');
                    option.value = sub.id;
                    option.textContent = sub.name;
                    subCategorySelect.appendChild(option);
                });
            }
            subCategorySelect.disabled = false;
            subCategorySelect.dispatchEvent(new Event('change'));
        })
        .catch(err => {
            console.error('Error loading subcategories:', err);
            subCategorySelect.innerHTML = '<option value="">Error loading subcategories</option>';
            subCategorySelect.disabled = false;
            subCategorySelect.dispatchEvent(new Event('change'));
        });
    });

    if (categorySelect && categorySelect.value) {
        categorySelect.dispatchEvent(new Event('change'));
    }

    // Auto-add any pending text when the form is submitted
    const form = document.querySelector('form');
    if (form) {
        form.addEventListener('submit', function(e) {
            if (inputTypeSelect.value === 'colorpicker') {
                const pendingColor = colorNameInput.value.trim();
                if (pendingColor !== '') {
                    addColorValue(pendingColor, colorHexInput.value);
                }
            } else {
                const pendingVal = newValueInput.value.trim();
                if (pendingVal !== '') {
                    addStandardValue(pendingVal);
                }
            }
        });
    }
});
</script>
