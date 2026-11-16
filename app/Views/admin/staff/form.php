<div class="mb-8 flex flex-col md:flex-row md:items-center justify-between">
    <div>
        <a href="<?= BASE_URL ?>/admin/staff" class="text-emerald-600 hover:text-emerald-700 font-medium text-sm flex items-center mb-2">
            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
            Back to Staff
        </a>
        <h1 class="text-3xl font-extrabold text-gray-900 tracking-tight"><?= isset($staff) ? 'Edit Staff Member' : 'Add New Staff' ?></h1>
    </div>
</div>

<div class="bg-white rounded-2xl border border-gray-100 shadow-[0_4px_20px_-4px_rgba(0,0,0,0.05)] overflow-hidden max-w-3xl">
    <form action="<?= BASE_URL ?>/admin/staff/<?= isset($staff) ? 'update' : 'store' ?>" method="POST" class="p-8">
        <?php if(isset($staff)): ?>
            <input type="hidden" name="id" value="<?= $staff['id'] ?>">
        <?php endif; ?>

        <div class="space-y-6">
            <h3 class="text-lg font-bold text-gray-900 border-b border-gray-100 pb-2">Basic Information</h3>
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label for="name" class="block text-sm font-bold text-gray-700 mb-1">Full Name <span class="text-red-500">*</span></label>
                    <input type="text" id="name" name="name" value="<?= isset($staff) ? htmlspecialchars($staff['name']) : '' ?>" required 
                           class="w-full px-4 py-2 border border-gray-200 rounded-lg focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 bg-gray-50 focus:bg-white transition-colors">
                </div>

                <div>
                    <label for="email" class="block text-sm font-bold text-gray-700 mb-1">Email Address <span class="text-red-500">*</span></label>
                    <input type="email" id="email" name="email" value="<?= isset($staff) ? htmlspecialchars($staff['email']) : '' ?>" required 
                           class="w-full px-4 py-2 border border-gray-200 rounded-lg focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 bg-gray-50 focus:bg-white transition-colors">
                </div>

                <div>
                    <label for="phone" class="block text-sm font-bold text-gray-700 mb-1">Phone Number</label>
                    <input type="tel" id="phone" name="phone" value="<?= isset($staff) ? htmlspecialchars($staff['phone']) : '' ?>" 
                           pattern="[0-9]{10}" title="Please enter a valid 10-digit mobile number" minlength="10" maxlength="10"
                           oninput="this.value = this.value.replace(/[^0-9]/g, '').slice(0, 10)"
                           class="w-full px-4 py-2 border border-gray-200 rounded-lg focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 bg-gray-50 focus:bg-white transition-colors">
                </div>

                <div>
                    <label for="password" class="block text-sm font-bold text-gray-700 mb-1">
                        Password <?= isset($staff) ? '<span class="text-gray-400 font-normal text-xs">(Leave blank to keep current)</span>' : '<span class="text-red-500">*</span>' ?>
                    </label>
                    <div class="relative">
                        <input type="password" id="password" name="password" <?= isset($staff) ? '' : 'required' ?> 
                               class="w-full px-4 py-2 border border-gray-200 rounded-lg focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 bg-gray-50 focus:bg-white transition-colors pr-10">
                        <button type="button" onclick="togglePasswordVisibility()" class="absolute inset-y-0 right-0 pr-3 flex items-center text-gray-400 hover:text-gray-600 focus:outline-none">
                            <svg id="eye-icon" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                            </svg>
                        </button>
                    </div>
                </div>

                <div class="md:col-span-2 grid grid-cols-1 md:grid-cols-2 gap-6 mt-2">
                    <div>
                        <label for="role_id" class="block text-sm font-bold text-gray-700 mb-1">Assign Role <span class="text-red-500">*</span></label>
                        <select id="role_id" name="role_id" required class="w-full px-4 py-2 border border-gray-200 rounded-lg focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 bg-gray-50 focus:bg-white transition-colors">
                            <option value="">-- Select a preset role --</option>
                            <option value="custom" <?= (isset($staff) && empty($staff['role_id']) && $staff['permissions'] !== null) ? 'selected' : '' ?>>-- Custom Permissions --</option>
                            <?php if(!empty($roles)): ?>
                                <?php foreach($roles as $roleItem): ?>
                                    <option value="<?= $roleItem['id'] ?>" <?= (isset($staff) && $staff['role_id'] == $roleItem['id']) ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($roleItem['name']) ?>
                                    </option>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </select>
                        <p class="text-xs text-gray-500 mt-1">Need a new role? <a href="<?= BASE_URL ?>/admin/staff/roles" class="text-emerald-600 hover:underline">Create it here</a>.</p>
                    </div>
                    <div>
                        <label for="status" class="block text-sm font-bold text-gray-700 mb-1">Status</label>
                        <select id="status" name="status" class="w-full px-4 py-2 border border-gray-200 rounded-lg focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 bg-gray-50 focus:bg-white transition-colors">
                            <option value="active" <?= (isset($staff) && $staff['status'] === 'active') ? 'selected' : '' ?>>Active</option>
                            <option value="deactivated" <?= (isset($staff) && $staff['status'] === 'deactivated') ? 'selected' : '' ?>>Deactivated</option>
                            <option value="blocked" <?= (isset($staff) && $staff['status'] === 'blocked') ? 'selected' : '' ?>>Blocked</option>
                        </select>
                    </div>
                </div>
            </div>
        </div>

        <div id="custom-permissions-section" class="mt-8 <?= (isset($staff) && empty($staff['role_id']) && !empty($staff['permissions'])) ? '' : 'hidden' ?>">
            <div class="flex items-center justify-between border-b border-gray-100 pb-2 mb-4">
                <h3 class="text-lg font-bold text-gray-900">Module Permissions Overview</h3>
                <button type="button" id="edit-permissions-btn" class="hidden text-sm bg-gray-100 hover:bg-gray-200 text-gray-700 font-bold py-1 px-3 rounded-lg transition-colors flex items-center">
                    <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>
                    Edit Permissions
                </button>
            </div>
            <p class="text-sm text-gray-500 mb-4" id="permissions-helper-text">Currently assigned permissions for this staff member.</p>
            
            <div class="overflow-x-auto border border-gray-200 rounded-lg">
                <table class="min-w-full">
                    <thead>
                        <tr class="border-b border-gray-200 bg-gray-50">
                            <th class="py-3 px-4 text-left text-sm font-bold text-gray-900 w-1/3">Module</th>
                            <th class="py-3 text-center text-sm font-bold text-gray-900">
                                <div class="flex flex-col items-center">
                                    <span>View</span>
                                    <input type="checkbox" onclick="toggleColumn('view', this.checked)" class="mt-2 w-4 h-4 text-emerald-600 border-gray-300 rounded focus:ring-emerald-500 cursor-pointer" title="Select All View">
                                </div>
                            </th>
                            <th class="py-3 text-center text-sm font-bold text-gray-900">
                                <div class="flex flex-col items-center">
                                    <span>Create</span>
                                    <input type="checkbox" onclick="toggleColumn('create', this.checked)" class="mt-2 w-4 h-4 text-emerald-600 border-gray-300 rounded focus:ring-emerald-500 cursor-pointer" title="Select All Create">
                                </div>
                            </th>
                            <th class="py-3 text-center text-sm font-bold text-gray-900">
                                <div class="flex flex-col items-center">
                                    <span>Edit</span>
                                    <input type="checkbox" onclick="toggleColumn('edit', this.checked)" class="mt-2 w-4 h-4 text-emerald-600 border-gray-300 rounded focus:ring-emerald-500 cursor-pointer" title="Select All Edit">
                                </div>
                            </th>
                            <th class="py-3 text-center text-sm font-bold text-gray-900">
                                <div class="flex flex-col items-center">
                                    <span>Delete</span>
                                    <input type="checkbox" onclick="toggleColumn('delete', this.checked)" class="mt-2 w-4 h-4 text-emerald-600 border-gray-300 rounded focus:ring-emerald-500 cursor-pointer" title="Select All Delete">
                                </div>
                            </th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 bg-white">
                        <?php 
                        $existingPermissions = isset($staff) && !empty($staff['permissions']) ? $staff['permissions'] : [];
                        ?>
                        
                        <?php foreach ($modules as $groupName => $subModules): ?>
                            <tr class="bg-gray-50 border-y border-gray-100">
                                <td colspan="5" class="py-2 px-4 text-xs font-bold text-gray-500 uppercase tracking-wider">
                                    <?= htmlspecialchars($groupName) ?>
                                </td>
                            </tr>
                            <?php foreach ($subModules as $key => $details): ?>
                            <tr class="hover:bg-gray-50/30 transition-colors border-b border-gray-50">
                                <td class="py-3 px-4 text-sm font-medium text-gray-700 pl-8">
                                    <?= htmlspecialchars($details['label']) ?>
                                </td>
                                <td class="py-3 text-center">
                                    <?php if (in_array('view', $details['actions'])): ?>
                                    <input type="checkbox" name="permissions[<?= $key ?>][view]" value="1" <?= isset($existingPermissions[$key]['view']) && $existingPermissions[$key]['view'] == 1 ? 'checked' : '' ?> class="w-4 h-4 text-emerald-600 border-gray-300 rounded focus:ring-emerald-500">
                                    <?php else: ?>
                                    <span class="text-gray-300 text-xs">-</span>
                                    <?php endif; ?>
                                </td>
                                <td class="py-3 text-center">
                                    <?php if (in_array('create', $details['actions'])): ?>
                                    <input type="checkbox" name="permissions[<?= $key ?>][create]" value="1" <?= isset($existingPermissions[$key]['create']) && $existingPermissions[$key]['create'] == 1 ? 'checked' : '' ?> class="w-4 h-4 text-emerald-600 border-gray-300 rounded focus:ring-emerald-500">
                                    <?php else: ?>
                                    <span class="text-gray-300 text-xs">-</span>
                                    <?php endif; ?>
                                </td>
                                <td class="py-3 text-center">
                                    <?php if (in_array('edit', $details['actions'])): ?>
                                    <input type="checkbox" name="permissions[<?= $key ?>][edit]" value="1" <?= isset($existingPermissions[$key]['edit']) && $existingPermissions[$key]['edit'] == 1 ? 'checked' : '' ?> class="w-4 h-4 text-emerald-600 border-gray-300 rounded focus:ring-emerald-500">
                                    <?php else: ?>
                                    <span class="text-gray-300 text-xs">-</span>
                                    <?php endif; ?>
                                </td>
                                <td class="py-3 text-center">
                                    <?php if (in_array('delete', $details['actions'])): ?>
                                    <input type="checkbox" name="permissions[<?= $key ?>][delete]" value="1" <?= isset($existingPermissions[$key]['delete']) && $existingPermissions[$key]['delete'] == 1 ? 'checked' : '' ?> class="w-4 h-4 text-emerald-600 border-gray-300 rounded focus:ring-emerald-500">
                                    <?php else: ?>
                                    <span class="text-gray-300 text-xs">-</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <div class="border-t border-gray-100 mt-8 pt-6 flex justify-end">
            <button type="submit" class="bg-[#1b262c] hover:bg-black text-white font-bold py-3 px-8 rounded-xl shadow-sm transition-colors text-sm flex items-center">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                <?= isset($staff) ? 'Update Staff Member' : 'Save Staff Member' ?>
            </button>
        </div>
    </form>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const roleSelect = document.getElementById('role_id');
    const customSection = document.getElementById('custom-permissions-section');
    const checkboxes = customSection.querySelectorAll('input[type="checkbox"]');
    const editBtn = document.getElementById('edit-permissions-btn');
    const helperText = document.getElementById('permissions-helper-text');
    
    // Pass PHP variables to JS
    const rolesData = <?= json_encode($roles ?? []) ?>;
    const customPermissions = <?= json_encode($existingPermissions ?? []) ?>;
    
    function updatePermissionsUI() {
        const selectedValue = roleSelect.value;
        
        // Show section if any role or custom is selected
        if (selectedValue !== '') {
            customSection.classList.remove('hidden');
        } else {
            customSection.classList.add('hidden');
            return;
        }

        if (selectedValue === 'custom') {
            editBtn.classList.add('hidden');
            helperText.textContent = 'Configure custom permissions for this staff member.';
            // Enable all checkboxes and load custom permissions
            checkboxes.forEach(cb => {
                cb.disabled = false;
                cb.checked = false;
                
                // Parse module and action from name="permissions[dashboard][view]"
                const match = cb.name.match(/permissions\[(.*?)\]\[(.*?)\]/);
                if (match && customPermissions[match[1]] && customPermissions[match[1]][match[2]] == 1) {
                    cb.checked = true;
                }
            });
        } else {
            // Preset Role Selected: Find role data
            editBtn.classList.remove('hidden');
            helperText.textContent = 'Showing preset role permissions. Click Edit to customize.';
            
            const role = rolesData.find(r => r.id == selectedValue);
            let rolePerms = {};
            if (role && role.permissions) {
                try {
                    rolePerms = typeof role.permissions === 'string' ? JSON.parse(role.permissions) : role.permissions;
                } catch(e) {}
            }
            
            // Disable all checkboxes and load role permissions
            checkboxes.forEach(cb => {
                cb.disabled = true;
                cb.checked = false;
                
                const match = cb.name.match(/permissions\[(.*?)\]\[(.*?)\]/);
                if (match && rolePerms[match[1]] && rolePerms[match[1]][match[2]] == 1) {
                    cb.checked = true;
                }
            });
        }
    }

    if (roleSelect && customSection) {
        roleSelect.addEventListener('change', updatePermissionsUI);
        // Initialize on load
        updatePermissionsUI();
    }
    
    if (editBtn) {
        editBtn.addEventListener('click', function() {
            roleSelect.value = 'custom';
            editBtn.classList.add('hidden');
            helperText.textContent = 'Configure custom permissions for this staff member.';
            
            // Enable checkboxes but KEEP current checked state
            checkboxes.forEach(cb => {
                cb.disabled = false;
            });
        });
    }
});

    function toggleColumn(action, isChecked) {
        document.querySelectorAll(`input[type="checkbox"][name$="[${action}]"]`).forEach(cb => {
            if (!cb.disabled) {
                cb.checked = isChecked;
            }
        });
    }

    function togglePasswordVisibility() {
        const passwordInput = document.getElementById('password');
        const eyeIcon = document.getElementById('eye-icon');
        if (passwordInput.type === 'password') {
            passwordInput.type = 'text';
            eyeIcon.innerHTML = `<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21" />`;
        } else {
            passwordInput.type = 'password';
            eyeIcon.innerHTML = `<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />`;
        }
    }
</script>
