<?php
$userId = \Core\Session::get('user_id');
$userName = $user['name'] ?? (\Core\Session::get('user_name') ?? '');
$userProfilePic = $user['profile_picture'] ?? '';

if (($userId && empty($userName)) || ($userId && empty($userProfilePic))) {
    try {
        $db = \Core\Database::getInstance();
        $stmt = $db->prepare("SELECT name, profile_picture FROM users WHERE id = ?");
        $stmt->execute([$userId]);
        $uRow = $stmt->fetch();
        if ($uRow) {
            if (empty($userName) && !empty($uRow['name'])) {
                $userName = $uRow['name'];
                \Core\Session::set('user_name', $userName);
            }
            if (empty($userProfilePic) && !empty($uRow['profile_picture'])) {
                $userProfilePic = $uRow['profile_picture'];
            }
        }
    } catch (\Throwable $e) {
        // Fallback to name-only query if profile_picture column does not exist
        try {
            $stmt = $db->prepare("SELECT name FROM users WHERE id = ?");
            $stmt->execute([$userId]);
            $uRow = $stmt->fetch();
            if ($uRow && empty($userName) && !empty($uRow['name'])) {
                $userName = $uRow['name'];
                \Core\Session::set('user_name', $userName);
            }
        } catch (\Throwable $e2) {}
    }
}
$displayName = !empty($userName) ? $userName : 'Buyer';
$firstLetter = strtoupper(substr($displayName, 0, 1));
?>

<div class="w-full md:w-56 lg:w-72 bg-[#fafafa] border-b md:border-b-0 md:border-r border-gray-200 md:min-h-screen flex-shrink-0 flex flex-col">
    <!-- User Profile Header (desktop & tablet) -->
    <div class="hidden md:flex px-3.5 py-5 lg:px-6 lg:py-8 items-center mb-2 lg:mb-4">
        <div class="w-10 h-10 lg:w-12 lg:h-12 bg-pink-100 border border-pink-200/80 rounded-xl flex items-center justify-center flex-shrink-0 overflow-hidden shadow-xs">
            <?php if (!empty($userProfilePic)): ?>
                <img src="<?= htmlspecialchars($userProfilePic) ?>" alt="<?= htmlspecialchars($displayName) ?>" class="w-full h-full object-cover">
            <?php else: ?>
                <span class="text-[#F25996] font-black text-sm lg:text-base tracking-wider"><?= $firstLetter ?></span>
            <?php endif; ?>
        </div>
        <div class="ml-3 lg:ml-4 flex-1 min-w-0">
            <span class="block text-[11px] lg:text-xs font-semibold text-gray-500 tracking-tight leading-none mb-1">Welcome back,</span>
            <h3 class="text-xs lg:text-sm font-bold text-gray-900 truncate leading-snug"><?= htmlspecialchars($displayName) ?></h3>
        </div>
        <div class="relative flex-shrink-0" id="notification-dropdown-container">
            <button id="notification-btn" class="relative p-1 text-gray-400 hover:text-gray-600 transition-colors" aria-label="Notifications">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
                </svg>
                <span id="notification-badge" class="hidden absolute top-1 right-1 w-2 h-2 bg-red-500 rounded-full border-2 border-[#fafafa]"></span>
            </button>
            <!-- Dropdown Menu -->
            <div id="notification-menu" class="hidden absolute left-0 mt-2 w-72 sm:w-80 max-w-[calc(100vw-2rem)] bg-white rounded-xl shadow-lg border border-gray-100 z-50 overflow-hidden origin-top-left transition-all transform opacity-0 scale-95">
                <div class="px-4 py-3 border-b border-gray-50 flex justify-between items-center bg-gray-50/50">
                    <h3 class="text-sm font-bold text-gray-900">Notifications</h3>
                    <button id="mark-all-read" class="text-xs text-brand-600 hover:text-brand-700 font-medium hidden">Mark all as read</button>
                </div>
                <div id="notification-list" class="max-h-80 overflow-y-auto divide-y divide-gray-50">
                    <div class="p-4 text-center text-sm text-gray-500">Loading...</div>
                </div>
            </div>
        </div>
    </div>

    <!-- Mobile: Horizontal scrollable tab bar (< 768px) -->
    <div class="md:hidden flex overflow-x-auto scrollbar-hide border-b border-gray-200/80 bg-white/70 backdrop-blur-xs pt-3.5 pb-3.5 px-3 sm:px-4 gap-2.5" style="scrollbar-width: none; -ms-overflow-style: none;">
        <a href="<?= BASE_URL ?>/dashboard/profile" class="flex-shrink-0 min-w-[115px] flex items-center justify-center gap-2 px-5 py-2.5 rounded-full text-xs sm:text-sm font-bold whitespace-nowrap transition-all shadow-xs <?= $activeTab === 'profile' ? 'bg-[#F25996] text-white shadow-md ring-2 ring-[#F25996]/25' : 'text-gray-700 bg-gray-100/90 hover:bg-pink-50 hover:text-[#F25996]' ?>">
            <svg class="w-4 h-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" /></svg>
            <span>Profile</span>
        </a>
        <a href="<?= BASE_URL ?>/dashboard/orders" class="flex-shrink-0 min-w-[115px] flex items-center justify-center gap-2 px-5 py-2.5 rounded-full text-xs sm:text-sm font-bold whitespace-nowrap transition-all shadow-xs <?= $activeTab === 'orders' ? 'bg-[#F25996] text-white shadow-md ring-2 ring-[#F25996]/25' : 'text-gray-700 bg-gray-100/90 hover:bg-pink-50 hover:text-[#F25996]' ?>">
            <svg class="w-4 h-4 shrink-0 <?= $activeTab === 'orders' ? 'text-white' : '' ?>" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z" /></svg>
            <span>Orders</span>
        </a>
        <a href="<?= BASE_URL ?>/dashboard/addresses" class="flex-shrink-0 min-w-[125px] flex items-center justify-center gap-2 px-5 py-2.5 rounded-full text-xs sm:text-sm font-bold whitespace-nowrap transition-all shadow-xs <?= $activeTab === 'addresses' ? 'bg-[#F25996] text-white shadow-md ring-2 ring-[#F25996]/25' : 'text-gray-700 bg-gray-100/90 hover:bg-pink-50 hover:text-[#F25996]' ?>">
            <svg class="w-4 h-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" /></svg>
            <span>Addresses</span>
        </a>
        <a href="<?= BASE_URL ?>/wishlist" class="flex-shrink-0 min-w-[115px] flex items-center justify-center gap-2 px-5 py-2.5 rounded-full text-xs sm:text-sm font-bold whitespace-nowrap transition-all shadow-xs <?= $activeTab === 'wishlist' ? 'bg-[#F25996] text-white shadow-md ring-2 ring-[#F25996]/25' : 'text-gray-700 bg-gray-100/90 hover:bg-pink-50 hover:text-[#F25996]' ?>">
            <svg class="w-4 h-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z" /></svg>
            <span>Wishlist</span>
        </a>
        <a href="<?= BASE_URL ?>/dashboard/reviews" class="flex-shrink-0 min-w-[115px] flex items-center justify-center gap-2 px-5 py-2.5 rounded-full text-xs sm:text-sm font-bold whitespace-nowrap transition-all shadow-xs <?= $activeTab === 'reviews' ? 'bg-[#F25996] text-white shadow-md ring-2 ring-[#F25996]/25' : 'text-gray-700 bg-gray-100/90 hover:bg-pink-50 hover:text-[#F25996]' ?>">
            <svg class="w-4 h-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z" /></svg>
            <span>Reviews</span>
        </a>
    </div>

    <!-- Desktop & Tablet: Vertical navigation -->
    <nav class="hidden md:flex flex-1 px-2.5 lg:px-4 space-y-1 flex-col">
        <a href="<?= BASE_URL ?>/dashboard/profile" class="group flex items-center px-3 py-2.5 lg:px-4 lg:py-3 rounded-xl transition-all cursor-pointer <?= $activeTab === 'profile' ? 'bg-[#F25996] text-white font-bold shadow-md' : 'text-gray-600 hover:bg-pink-50/60 hover:text-[#F25996] font-medium' ?>">
            <svg class="w-4 h-4 lg:w-5 lg:h-5 mr-2.5 lg:mr-4 transition-colors shrink-0 <?= $activeTab === 'profile' ? 'text-white' : 'text-gray-500 group-hover:text-[#F25996]' ?>" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
            </svg>
            <span class="text-xs lg:text-sm truncate">Profile Settings</span>
        </a>
        
        <a href="<?= BASE_URL ?>/dashboard/orders" class="group flex items-center px-3 py-2.5 lg:px-4 lg:py-3 rounded-xl transition-all cursor-pointer <?= $activeTab === 'orders' ? 'bg-[#F25996] text-white font-bold shadow-md' : 'text-gray-600 hover:bg-pink-50/60 hover:text-[#F25996] font-medium' ?>">
            <svg class="w-4 h-4 lg:w-5 lg:h-5 mr-2.5 lg:mr-4 transition-colors shrink-0 <?= $activeTab === 'orders' ? 'text-white' : 'text-gray-500 group-hover:text-[#F25996]' ?>" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z" />
            </svg>
            <span class="text-xs lg:text-sm truncate">Order History</span>
        </a>
        
        <a href="<?= BASE_URL ?>/dashboard/addresses" class="group flex items-center px-3 py-2.5 lg:px-4 lg:py-3 rounded-xl transition-all cursor-pointer <?= $activeTab === 'addresses' ? 'bg-[#F25996] text-white font-bold shadow-md' : 'text-gray-600 hover:bg-pink-50/60 hover:text-[#F25996] font-medium' ?>">
            <svg class="w-4 h-4 lg:w-5 lg:h-5 mr-2.5 lg:mr-4 transition-colors shrink-0 <?= $activeTab === 'addresses' ? 'text-white' : 'text-gray-500 group-hover:text-[#F25996]' ?>" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
            </svg>
            <span class="text-xs lg:text-sm truncate">My Addresses</span>
        </a>
        
        <a href="<?= BASE_URL ?>/wishlist" class="group flex items-center px-3 py-2.5 lg:px-4 lg:py-3 rounded-xl transition-all cursor-pointer <?= $activeTab === 'wishlist' ? 'bg-[#F25996] text-white font-bold shadow-md' : 'text-gray-600 hover:bg-pink-50/60 hover:text-[#F25996] font-medium' ?>">
            <svg class="w-4 h-4 lg:w-5 lg:h-5 mr-2.5 lg:mr-4 transition-colors shrink-0 <?= $activeTab === 'wishlist' ? 'text-white' : 'text-gray-500 group-hover:text-[#F25996]' ?>" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z" />
            </svg>
            <span class="text-xs lg:text-sm truncate">My Wishlist</span>
        </a>

        <a href="<?= BASE_URL ?>/dashboard/reviews" class="group flex items-center px-3 py-2.5 lg:px-4 lg:py-3 rounded-xl transition-all cursor-pointer <?= $activeTab === 'reviews' ? 'bg-[#F25996] text-white font-bold shadow-md' : 'text-gray-600 hover:bg-pink-50/60 hover:text-[#F25996] font-medium' ?>">
            <svg class="w-4 h-4 lg:w-5 lg:h-5 mr-2.5 lg:mr-4 transition-colors shrink-0 <?= $activeTab === 'reviews' ? 'text-white' : 'text-gray-400 group-hover:text-[#F25996]' ?>" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z" />
            </svg>
            <span class="text-xs lg:text-sm truncate">My Reviews</span>
        </a>

        <a href="<?= BASE_URL ?>/logout" onclick="event.preventDefault(); document.getElementById('logout-form').submit();" class="group flex items-center px-3 py-2.5 lg:px-4 lg:py-3 rounded-xl transition-all text-gray-600 hover:bg-rose-50 hover:text-rose-600 font-medium cursor-pointer">
            <svg class="w-4 h-4 lg:w-5 lg:h-5 mr-2.5 lg:mr-4 text-gray-400 group-hover:text-rose-600 transition-colors shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
            </svg>
            <span class="text-xs lg:text-sm truncate">Logout</span>
        </a>
    </nav>
    <form id="logout-form" action="<?= BASE_URL ?>/logout" method="POST" class="hidden"></form>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const btn = document.getElementById('notification-btn');
    const menu = document.getElementById('notification-menu');
    const badge = document.getElementById('notification-badge');
    const list = document.getElementById('notification-list');
    const markAllReadBtn = document.getElementById('mark-all-read');
    let isOpen = false;

    // Fetch notifications on load
    fetchNotifications();

    btn.addEventListener('click', function(e) {
        e.stopPropagation();
        isOpen = !isOpen;
        if (isOpen) {
            menu.classList.remove('hidden');
            setTimeout(() => {
                menu.classList.remove('opacity-0', 'scale-95');
                menu.classList.add('opacity-100', 'scale-100');
            }, 10);
            
            // Mark all as read when opening dropdown if there are unread
            if (!badge.classList.contains('hidden')) {
                markAsRead('all');
                badge.classList.add('hidden');
                markAllReadBtn.classList.add('hidden');
            }
        } else {
            closeMenu();
        }
    });

    document.addEventListener('click', function(e) {
        if (isOpen && !menu.contains(e.target)) {
            closeMenu();
        }
    });

    function closeMenu() {
        isOpen = false;
        menu.classList.remove('opacity-100', 'scale-100');
        menu.classList.add('opacity-0', 'scale-95');
        setTimeout(() => {
            menu.classList.add('hidden');
        }, 150); // match transition duration
    }

    function fetchNotifications() {
        fetch('<?= BASE_URL ?>/api/notifications')
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    if (data.unread_count > 0) {
                        badge.classList.remove('hidden');
                        markAllReadBtn.classList.remove('hidden');
                    }
                    
                    if (data.notifications.length === 0) {
                        list.innerHTML = '<div class="p-4 text-center text-sm text-gray-500">No notifications yet</div>';
                    } else {
                        list.innerHTML = data.notifications.map(n => `
                            <div class="p-4 hover:bg-gray-50 transition-colors ${n.is_read == 0 ? 'bg-brand-50/30' : ''}">
                                <div class="flex items-start">
                                    <div class="flex-shrink-0 mt-0.5">
                                        ${getIconForType(n.type)}
                                    </div>
                                    <div class="ml-3 flex-1">
                                        <p class="text-sm font-bold text-gray-900">${n.title}</p>
                                        <p class="mt-1 text-sm text-gray-600 leading-relaxed">${n.message}</p>
                                        <p class="mt-2 text-xs text-gray-400 font-medium">${timeAgo(n.created_at)}</p>
                                    </div>
                                    ${n.is_read == 0 ? '<div class="flex-shrink-0 w-2 h-2 rounded-full bg-brand-500 mt-1.5 ml-2"></div>' : ''}
                                </div>
                            </div>
                        `).join('');
                    }
                }
            });
    }

    function markAsRead(id) {
        fetch('<?= BASE_URL ?>/api/notifications/read', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ id: id })
        });
    }

    function getIconForType(type) {
        if (type === 'success') {
            return '<svg class="w-5 h-5 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>';
        } else if (type === 'warning') {
            return '<svg class="w-5 h-5 text-yellow-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>';
        } else if (type === 'error') {
            return '<svg class="w-5 h-5 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>';
        }
        return '<svg class="w-5 h-5 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>';
    }

    function timeAgo(dateString) {
        const date = new Date(dateString);
        const seconds = Math.floor((new Date() - date) / 1000);
        let interval = seconds / 31536000;
        if (interval > 1) return Math.floor(interval) + " years ago";
        interval = seconds / 2592000;
        if (interval > 1) return Math.floor(interval) + " months ago";
        interval = seconds / 86400;
        if (interval > 1) return Math.floor(interval) + " days ago";
        interval = seconds / 3600;
        if (interval > 1) return Math.floor(interval) + " hours ago";
        interval = seconds / 60;
        if (interval > 1) return Math.floor(interval) + " minutes ago";
        return Math.floor(seconds) + " seconds ago";
    }
});
</script>
