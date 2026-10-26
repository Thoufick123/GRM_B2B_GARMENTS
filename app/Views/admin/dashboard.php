<!-- Chart.js CDN -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<!-- jsPDF and autotable -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf-autotable/3.8.2/jspdf.plugin.autotable.min.js"></script>

<style>
    header {
        display: none !important;
    }
    .saas-dashboard-bg {
        background-color: #F8FAFC !important;
    }
    .saas-card {
        background-color: #FFFFFF !important;
        border: 1px solid #F1F5F9 !important;
        border-radius: 16px !important;
        box-shadow: 0 1px 3px 0 rgba(0, 0, 0, 0.04), 0 1px 2px -1px rgba(0, 0, 0, 0.04) !important;
    }
    .saas-btn-primary {
        background-color: #F25996 !important;
        color: #FFFFFF !important;
    }
    .saas-btn-primary:hover {
        background-color: #e04481 !important;
    }
    .saas-quick-btn {
        background-color: #F8FAFC !important;
        border: 1px solid #F1F5F9 !important;
        border-radius: 14px !important;
        transition: all 0.2s ease;
    }
    .saas-quick-btn:hover {
        background-color: #fdf2f7 !important;
        border-color: #fbaed2 !important;
        transform: translateY(-2px);
    }
    .saas-charts-grid {
        display: grid;
        grid-template-columns: 1.45fr 1fr;
        gap: 20px;
    }
    @media (max-width: 1024px) {
        .saas-charts-grid {
            grid-template-columns: 1fr;
        }
    }
</style>

<div class="w-full max-w-full font-sans text-slate-800">
    
    <!-- Top Header Bar (Breadcrumb, Title on Left, Buttons on Far Right) -->
    <div class="mb-8 relative z-10">
        <!-- Breadcrumbs -->
        <div class="text-sm font-medium text-gray-400 mb-2 font-serif tracking-wide">
            Dashboard <span class="mx-1 text-gray-300">›</span> <span class="text-[#F25996]">Overview</span>
        </div>
        
        <div class="flex justify-between items-center mb-8 w-full">
            <h2 class="text-5xl font-display font-extrabold text-gray-900 tracking-tight">Dashboard Overview</h2>
        
        <div class="flex items-center space-x-3 ml-auto">
            <?php
            $rangeLabels = ['30' => 'Last 30 Days', '7' => 'Last 7 Days', 'all' => 'All Time'];
            $currentRange = $range ?? ($_GET['range'] ?? null);
            $startDateVal = $startDate ?? ($_GET['start_date'] ?? null);
            $endDateVal = $endDate ?? ($_GET['end_date'] ?? null);

            if (!$startDateVal || !$endDateVal) {
                if ($currentRange === '7') {
                    $startDateVal = date('Y-m-d', strtotime('-7 days'));
                    $endDateVal = date('Y-m-d');
                    $currentLabel = 'Last 7 Days';
                } elseif ($currentRange === 'all') {
                    $startDateVal = '2024-01-01';
                    $endDateVal = date('Y-m-d');
                    $currentLabel = 'All Time';
                } else {
                    $currentRange = '30';
                    $startDateVal = date('Y-m-01');
                    $endDateVal = date('Y-m-t');
                    $currentLabel = 'This Month (30 Days)';
                }
            } else {
                $currentLabel = date('d M Y', strtotime($startDateVal)) . ' - ' . date('d M Y', strtotime($endDateVal));
            }
            ?>


            <!-- Date Range & Export Pill Capsule -->
            <div class="bg-white border border-slate-200 rounded-full flex items-center p-1.5 pl-3.5 shadow-xs gap-2">
                <!-- Interactive Date Range Inputs & Calendar Icons -->
                <div class="flex items-center gap-1.5 text-xs text-slate-700">
                    <!-- Start Date Picker -->
                    <div class="flex items-center gap-1.5 bg-slate-50 hover:bg-pink-50/60 transition px-2.5 py-1 rounded-full border border-slate-100 cursor-pointer group" onclick="triggerDatePicker('dash_start_date')">
                        <input type="date" id="dash_start_date" name="start_date" value="<?= $startDateVal ?>" onchange="applyCustomDateRange()" class="text-xs font-mono font-semibold text-slate-700 bg-transparent border-0 p-0 focus:ring-0 cursor-pointer outline-none w-[115px] group-hover:text-[#F25996] transition" title="Select Start Date">
                    </div>

                    <span class="text-slate-300 font-bold px-0.5">-</span>

                    <!-- End Date Picker -->
                    <div class="flex items-center gap-1.5 bg-slate-50 hover:bg-pink-50/60 transition px-2.5 py-1 rounded-full border border-slate-100 cursor-pointer group" onclick="triggerDatePicker('dash_end_date')">
                        <input type="date" id="dash_end_date" name="end_date" value="<?= $endDateVal ?>" onchange="applyCustomDateRange()" class="text-xs font-mono font-semibold text-slate-700 bg-transparent border-0 p-0 focus:ring-0 cursor-pointer outline-none w-[115px] group-hover:text-[#F25996] transition" title="Select End Date">
                    </div>

                    <!-- Presets dropdown toggle -->
                    <div class="relative">
                        <button type="button" onclick="document.getElementById('saas-date-dropdown').classList.toggle('hidden')" class="p-1.5 text-slate-400 hover:text-slate-700 hover:bg-slate-100 rounded-full transition" title="Quick Presets">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                        </button>
                        <div id="saas-date-dropdown" class="hidden absolute right-0 mt-2 w-48 bg-white border border-slate-100 rounded-2xl shadow-xl z-50 py-1.5 overflow-hidden">
                            <div class="px-3 py-1 text-[10px] font-bold uppercase tracking-wider text-slate-400">Quick Presets</div>
                            <a href="?range=30" class="block px-4 py-2 text-xs font-bold <?= $currentRange === '30' ? 'text-[#F25996] bg-pink-50' : 'text-slate-700 hover:bg-slate-50' ?>">This Month (30 Days)</a>
                            <a href="?range=7" class="block px-4 py-2 text-xs font-bold <?= $currentRange === '7' ? 'text-[#F25996] bg-pink-50' : 'text-slate-700 hover:bg-slate-50' ?>">Last 7 Days</a>
                            <a href="?range=all" class="block px-4 py-2 text-xs font-bold <?= $currentRange === 'all' ? 'text-[#F25996] bg-pink-50' : 'text-slate-700 hover:bg-slate-50' ?>">All Time</a>
                        </div>
                    </div>
                </div>

                <!-- Export Pill Button -->
                <div class="relative">
                    <button type="button" onclick="document.getElementById('saas-export-dropdown').classList.toggle('hidden')" class="bg-[#F25996] hover:bg-[#d94480] text-white text-xs font-bold uppercase tracking-wider px-5 py-2.5 rounded-full flex items-center gap-1.5 shadow-xs transition active:scale-95">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path></svg>
                        <span>EXPORT</span>
                    </button>
                    <div id="saas-export-dropdown" class="hidden absolute right-0 mt-2 w-48 bg-white border border-slate-100 rounded-2xl shadow-xl z-50 py-1.5 overflow-hidden">
                        <button type="button" onclick="exportDashboardPDF(); document.getElementById('saas-export-dropdown').classList.add('hidden');" class="w-full text-left px-4 py-2.5 text-xs font-bold text-slate-700 hover:bg-pink-50 hover:text-[#F25996] flex items-center gap-2 transition">
                            <svg class="w-4 h-4 text-rose-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"></path></svg>
                            Export PDF Report
                        </button>
                        <button type="button" onclick="exportDashboardCSV(); document.getElementById('saas-export-dropdown').classList.add('hidden');" class="w-full text-left px-4 py-2.5 text-xs font-bold text-slate-700 hover:bg-pink-50 hover:text-[#F25996] flex items-center gap-2 transition">
                            <svg class="w-4 h-4 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                            Export Excel (CSV)
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- 5 Key Metric Cards with Sparklines -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-6 gap-5 mb-6">
        
        <!-- Total Revenue Card -->
        <div class="saas-card p-5 relative overflow-hidden flex flex-col justify-between">
            <div>
                <div class="flex items-center space-x-3 mb-2">
                    <div class="w-9 h-9 rounded-xl bg-purple-50 text-purple-600 flex items-center justify-center font-bold text-sm">
                        ₹
                    </div>
                    <span class="text-xs font-medium text-slate-500">Total Revenue</span>
                </div>
                <div class="text-2xl font-extrabold text-slate-900 mt-2">
                    ₹<?= number_format($totalRevenue, 2) ?>
                </div>
                <div class="mt-1 flex flex-col gap-1">
                    <div class="flex items-center text-[11px] font-semibold text-emerald-600">
                        <span class="mr-1">↑ 18.6%</span> <span class="text-slate-400 font-normal">vs last 30 days</span>
                    </div>
                    <?php if (($totalRefunds ?? 0) > 0): ?>
                    <div class="flex items-center text-[10px] font-semibold text-rose-500">
                        <span>Refunded: ₹<?= number_format($totalRefunds, 2) ?></span>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
            <!-- Purple Sparkline -->
            <div class="w-full h-9 mt-3">
                <canvas id="sparklineRevenue"></canvas>
            </div>
        </div>

        <!-- GST Tax Revenue Card -->
        <div class="saas-card p-5 relative overflow-hidden flex flex-col justify-between">
            <div>
                <div class="flex items-center space-x-3 mb-2">
                    <div class="w-9 h-9 rounded-xl bg-teal-50 text-teal-600 flex items-center justify-center font-bold text-xs">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 14l6-6m-5.5.5h.01m4.99 5h.01M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16l3.5-2 3.5 2 3.5-2 3.5 2zM10 8.5a.5.5 0 11-1 0 .5.5 0 011 0zm5 5a.5.5 0 11-1 0 .5.5 0 011 0z"></path></svg>
                    </div>
                    <span class="text-xs font-medium text-slate-500">GST Tax Revenue</span>
                </div>
                <div class="text-2xl font-extrabold text-slate-900 mt-2">
                    ₹<?= number_format($totalGstTaxRevenue ?? 0, 2) ?>
                </div>
                <div class="mt-1 flex flex-col gap-1">
                    <div class="flex items-center text-[11px] font-semibold text-teal-700">
                        <span class="bg-teal-50 px-2 py-0.5 rounded-full">SGST: ₹<?= number_format($totalSgstRevenue ?? 0, 2) ?> | CGST: ₹<?= number_format($totalCgstRevenue ?? 0, 2) ?></span>
                    </div>
                    <div class="flex items-center text-[10px] text-slate-400 font-normal">
                        <span>Taxable Base: ₹<?= number_format($totalBaseRevenue ?? 0, 2) ?></span>
                    </div>
                </div>
            </div>
            <!-- Teal Sparkline -->
            <div class="w-full h-9 mt-3">
                <canvas id="sparklineTaxRevenue"></canvas>
            </div>
        </div>

        <!-- Total Orders Card -->
        <div class="saas-card p-5 relative overflow-hidden flex flex-col justify-between">
            <div>
                <div class="flex items-center space-x-3 mb-2">
                    <div class="w-9 h-9 rounded-xl bg-[#fdf2f7] text-[#F25996] border border-[#fbaed2] flex items-center justify-center font-bold">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"></path></svg>
                    </div>
                    <span class="text-xs font-medium text-slate-500">Total Orders</span>
                </div>
                <div class="text-2xl font-extrabold text-slate-900 mt-2">
                    <?= number_format($totalOrders) ?>
                </div>
                <div class="mt-1 flex items-center text-[11px] font-semibold">
                    <span class="text-amber-500 bg-amber-50 px-2 py-0.5 rounded-full mr-2"><?= number_format($pendingOrders ?? 0) ?> pending</span> 
                    <span class="text-slate-400 font-normal">needs attention</span>
                </div>
                <?php if (($adminCancelledOrders ?? 0) > 0 || ($buyerCancelledOrders ?? 0) > 0): ?>
                <div class="mt-1.5 flex items-center text-[10px] font-semibold text-slate-500 space-x-2">
                    <?php if (($adminCancelledOrders ?? 0) > 0): ?>
                        <span class="text-red-600 bg-red-50 px-1.5 py-0.5 rounded">Admin Canceled: <?= $adminCancelledOrders ?></span>
                    <?php endif; ?>
                    <?php if (($buyerCancelledOrders ?? 0) > 0): ?>
                        <span class="text-rose-600 bg-rose-50 px-1.5 py-0.5 rounded">Buyer Canceled: <?= $buyerCancelledOrders ?></span>
                    <?php endif; ?>
                </div>
                <?php endif; ?>
            </div>
            <!-- Blue Sparkline -->
            <div class="w-full h-9 mt-3">
                <canvas id="sparklineOrders"></canvas>
            </div>
        </div>

        <!-- Total Customers Card -->
        <div class="saas-card p-5 relative overflow-hidden flex flex-col justify-between">
            <div>
                <div class="flex items-center space-x-3 mb-2">
                    <div class="w-9 h-9 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path></svg>
                    </div>
                    <span class="text-xs font-medium text-slate-500">Total Customers</span>
                </div>
                <div class="text-2xl font-extrabold text-slate-900 mt-2">
                    <?= number_format($totalBuyers) ?>
                </div>
                <div class="mt-1 flex items-center text-[11px] font-semibold text-emerald-600">
                    <span class="mr-1">↑ 5.3%</span> <span class="text-slate-400 font-normal">vs last 30 days</span>
                </div>
            </div>
            <!-- Green Sparkline -->
            <div class="w-full h-9 mt-3">
                <canvas id="sparklineCustomers"></canvas>
            </div>
        </div>

        <!-- Regular Products Card -->
        <div class="saas-card p-5 relative overflow-hidden flex flex-col justify-between">
            <div>
                <div class="flex items-center space-x-3 mb-2">
                    <div class="w-9 h-9 rounded-xl bg-orange-50 text-orange-600 flex items-center justify-center">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path></svg>
                    </div>
                    <span class="text-xs font-medium text-slate-500">Regular Products</span>
                </div>
                <div class="text-2xl font-extrabold text-slate-900 mt-2">
                    <?= number_format($regularProducts) ?>
                </div>
                <div class="mt-1 flex items-center text-[11px] font-semibold text-rose-600">
                    <span class="text-slate-400 font-normal">Standard catalog products</span>
                </div>
            </div>
            <!-- Orange Sparkline -->
            <div class="w-full h-9 mt-3">
                <canvas id="sparklineProducts"></canvas>
            </div>
        </div>

        <!-- Customized Products Card -->
        <div class="saas-card p-5 relative overflow-hidden flex flex-col justify-between">
            <div>
                <div class="flex items-center space-x-3 mb-2">
                    <div class="w-9 h-9 rounded-xl bg-pink-50 text-[#F25996] border border-pink-100 flex items-center justify-center">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21a4 4 0 01-4-4V5a2 2 0 012-2h4a2 2 0 012 2v12a4 4 0 01-4 4zm0 0h12a2 2 0 002-2v-4a2 2 0 00-2-2h-5.5M11 5.5l6 6M14 3l7 7"/></svg>
                    </div>
                    <span class="text-xs font-medium text-slate-500">Customized Products</span>
                </div>
                <div class="text-2xl font-extrabold text-slate-900 mt-2">
                    <?= number_format($customizedProducts) ?>
                </div>
                <div class="mt-1 flex items-center text-[11px] font-semibold">
                    <span class="text-slate-400 font-normal">Customizable catalog products</span>
                </div>
            </div>
            <div class="w-full h-9 mt-3"></div>
        </div>

    </div>

    <!-- Analytics Charts Row (Revenue Analytics & Order Volume) -->
    <div class="saas-charts-grid mb-6">
        
        <!-- Revenue Analytics -->
        <div class="saas-card p-6 flex flex-col justify-between">
            <div class="flex items-center justify-between mb-4">
                <div>
                    <h2 class="text-base font-bold text-slate-900">Revenue Analytics</h2>
                    <p class="text-xs text-slate-400 mt-0.5">Daily revenue over the last 30 days</p>
                </div>
                <div class="border border-slate-200 rounded-lg px-2.5 py-1 text-xs font-medium text-slate-600 bg-white">
                    <span>Daily</span>
                </div>
            </div>
            
            <div class="relative w-full h-64">
                <canvas id="revenueAnalyticsChart"></canvas>
            </div>
        </div>

        <!-- Order Volume -->
        <div class="saas-card p-6 flex flex-col justify-between">
            <div class="flex items-center justify-between mb-4">
                <div>
                    <h2 class="text-base font-bold text-slate-900">Order Volume</h2>
                    <p class="text-xs text-slate-400 mt-0.5">Orders by day of week</p>
                </div>
                <div class="border border-slate-200 rounded-lg px-2.5 py-1 text-xs font-medium text-slate-600 bg-white">
                    <span>This Week</span>
                </div>
            </div>
            
            <div class="relative w-full h-64">
                <canvas id="orderVolumeChart"></canvas>
            </div>
        </div>

    </div>

    <!-- 2-Column Bottom Section (Recent Orders | Top Selling Products) -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
        <!-- Column 2: Recent Orders -->
        <div class="saas-card p-5 flex flex-col">
            <div class="flex items-center justify-between mb-4">
                <h2 class="text-sm font-bold text-slate-900">Recent Orders</h2>
                <a href="<?= BASE_URL ?>/admin/orders" class="text-xs font-semibold text-[#F25996] hover:text-[#e04481]">View All</a>
            </div>
            
            <div class="space-y-3 flex-grow">
                <?php foreach($recentOrders as $order): ?>
                <div class="flex items-center justify-between py-1 border-b border-slate-50 last:border-0">
                    <div class="flex items-center space-x-2.5 min-w-0">
                        <div class="w-7 h-7 rounded-lg bg-[#fdf2f7] text-[#F25996] border border-[#fbaed2] flex items-center justify-center flex-shrink-0">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"></path></svg>
                        </div>
                        <div class="min-w-0">
                            <p class="text-xs font-bold text-slate-900 truncate"><?= htmlspecialchars($order['order_number']) ?></p>
                            <p class="text-[10px] text-slate-400 truncate"><?= htmlspecialchars($order['customer_name']) ?></p>
                        </div>
                    </div>
                    
                    <div class="text-right flex-shrink-0">
                        <p class="text-xs font-bold text-slate-900">₹<?= number_format($order['total']) ?></p>
                        <?php
                        $st = strtolower($order['status']);
                        $badgeClass = match($st) {
                            'delivered', 'completed' => 'bg-emerald-50 text-emerald-600',
                            'processing', 'paid' => 'bg-pink-50 text-[#F25996] border border-pink-100',
                            'shipped' => 'bg-amber-50 text-amber-600',
                            'cancelled' => 'bg-rose-50 text-rose-600',
                            default => 'bg-slate-100 text-slate-600'
                        };
                        ?>
                        <span class="inline-block px-1.5 py-0.5 rounded text-[9px] font-bold <?= $badgeClass ?> mt-0.5">
                            <?= htmlspecialchars($order['status']) ?>
                        </span>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>

        <!-- Column 3: Top Selling Products -->
        <div class="saas-card p-5 flex flex-col">
            <div class="flex items-center justify-between mb-4">
                <h2 class="text-sm font-bold text-slate-900">Top Selling Products</h2>
                <a href="<?= BASE_URL ?>/admin/catalog/products" class="text-xs font-semibold text-[#F25996] hover:text-[#e04481]">View All</a>
            </div>
            
            <div class="space-y-3 flex-grow">
                <?php if (empty($topSellingProducts)): ?>
                <div class="flex flex-col items-center justify-center py-8 text-center text-slate-400">
                    <svg class="w-10 h-10 mb-2 text-slate-200" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
                    <p class="text-xs font-semibold text-slate-400">No sales data yet</p>
                    <p class="text-[10px] text-slate-300 mt-1">Products will appear here once orders are placed</p>
                </div>
                <?php else: ?>
                <?php foreach($topSellingProducts as $idx => $prod): ?>
                <div class="flex items-center justify-between py-2 border-b border-slate-50 last:border-0">
                    <div class="flex items-center space-x-2.5 min-w-0">
                        <!-- Rank badge -->
                        <div class="w-5 h-5 rounded-full flex items-center justify-center flex-shrink-0 text-[10px] font-bold
                            <?= $idx === 0 ? 'bg-amber-100 text-amber-700' : ($idx === 1 ? 'bg-slate-100 text-slate-600' : ($idx === 2 ? 'bg-orange-100 text-orange-600' : 'bg-[#fdf2f7] text-[#F25996]')) ?>">
                            <?= $idx + 1 ?>
                        </div>
                        <div class="w-8 h-8 rounded-lg bg-slate-100 border border-slate-200/60 overflow-hidden flex items-center justify-center flex-shrink-0 text-slate-400">
                            <?php
                                $imgSrc = '';
                                if (!empty($prod['image'])) {
                                    // Cloudinary or absolute URL
                                    if (str_starts_with($prod['image'], 'http')) {
                                        $imgSrc = htmlspecialchars($prod['image']);
                                    } else {
                                        $imgSrc = BASE_URL . '/uploads/products/' . htmlspecialchars($prod['image']);
                                    }
                                }
                            ?>
                            <?php if($imgSrc): ?>
                                <img src="<?= $imgSrc ?>" alt="" class="w-full h-full object-cover">
                            <?php else: ?>
                                <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path></svg>
                            <?php endif; ?>
                        </div>
                        <div class="min-w-0">
                            <p class="text-xs font-bold text-slate-900 truncate"><?= htmlspecialchars($prod['name']) ?></p>
                            <p class="text-[10px] text-slate-400"><?= (int)$prod['sold'] ?> units sold</p>
                        </div>
                    </div>
                    
                    <div class="text-right flex-shrink-0">
                        <p class="text-xs font-bold text-slate-900">₹<?= number_format($prod['revenue']) ?></p>
                        <p class="text-[10px] text-emerald-500 font-semibold">Revenue</p>
                    </div>
                </div>
                <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>

        <!-- Column 4: System Status -->
        

    </div>

</div>

<!-- Initialize All Charts -->
<script>
document.addEventListener('DOMContentLoaded', function() {

    // Helper for sparklines
    function createSparkline(canvasId, data, color) {
        const el = document.getElementById(canvasId);
        if (!el) return;
        new Chart(el, {
            type: 'line',
            data: {
                labels: data.map((_, i) => i),
                datasets: [{
                    data: data,
                    borderColor: color,
                    borderWidth: 2,
                    tension: 0.4,
                    pointRadius: 0,
                    fill: false
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { display: false }, tooltip: { enabled: false } },
                scales: {
                    x: { display: false },
                    y: { display: false }
                }
            }
        });
    }

    // 1. Sparklines
    createSparkline('sparklineRevenue', <?= json_encode($sparklines['revenue'] ?? [20, 24, 22, 28, 25, 30, 27, 34, 31, 38, 35, 42]) ?>, '#F25996');
    createSparkline('sparklineTaxRevenue', <?= json_encode($sparklines['tax_revenue'] ?? [12, 15, 14, 18, 16, 20, 18, 24, 21, 26, 23, 28]) ?>, '#0D9488');
    createSparkline('sparklineOrders', <?= json_encode($sparklines['orders'] ?? [15, 18, 14, 22, 19, 25, 21, 28, 24, 30, 27, 32]) ?>, '#F25996');
    createSparkline('sparklineCustomers', <?= json_encode($sparklines['customers'] ?? [10, 12, 11, 15, 13, 17, 16, 20, 18, 22, 20, 25]) ?>, '#22C55E');
    createSparkline('sparklineProducts', <?= json_encode($sparklines['products'] ?? [30, 28, 29, 26, 28, 25, 27, 24, 25, 23, 24, 22]) ?>, '#FB923C');

    // 2. Revenue Analytics Smooth Spline Area Chart
    const ctxRevenue = document.getElementById('revenueAnalyticsChart');
    if (ctxRevenue) {
        const revenueDates = <?= json_encode($chartDates ?? []) ?>;
        const revenueValues = <?= json_encode($chartRevenue ?? []) ?>;
        
        const gradient = ctxRevenue.getContext('2d').createLinearGradient(0, 0, 0, 240);
        gradient.addColorStop(0, 'rgba(242, 89, 150, 0.28)');
        gradient.addColorStop(1, 'rgba(242, 89, 150, 0.0)');

        new Chart(ctxRevenue, {
            type: 'line',
            data: {
                labels: revenueDates,
                datasets: [{
                    label: 'Revenue',
                    data: revenueValues,
                    borderColor: '#F25996',
                    backgroundColor: gradient,
                    borderWidth: 2.5,
                    tension: 0.45,
                    fill: true,
                    pointRadius: 0,
                    pointHoverRadius: 5,
                    pointHoverBackgroundColor: '#F25996',
                    pointHoverBorderColor: '#FFFFFF',
                    pointHoverBorderWidth: 2
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        backgroundColor: '#0F172A',
                        titleFont: { size: 12 },
                        bodyFont: { size: 13, weight: 'bold' },
                        padding: 10,
                        displayColors: false,
                        callbacks: {
                            label: function(context) {
                                return '₹' + Number(context.raw).toLocaleString('en-IN', { minimumFractionDigits: 2 });
                            }
                        }
                    }
                },
                scales: {
                    x: {
                        grid: { display: false, drawBorder: false },
                        ticks: { color: '#94A3B8', font: { size: 11 }, maxTicksLimit: 6 }
                    },
                    y: {
                        grid: { color: '#F1F5F9', drawBorder: false },
                        ticks: {
                            color: '#94A3B8',
                            font: { size: 11 },
                            callback: function(val) {
                                if (val >= 10000000) return (val / 10000000).toFixed(1) + 'Cr';
                                if (val >= 100000) return (val / 100000).toFixed(0) + 'L';
                                if (val >= 1000) return (val / 1000).toFixed(0) + 'k';
                                return val;
                            }
                        }
                    }
                }
            }
        });
    }

    // 3. Order Volume Bar Chart
    const ctxOrders = document.getElementById('orderVolumeChart');
    if (ctxOrders) {
        const dowLabels = ['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'];
        const dowValues = <?= json_encode($dayOfWeekOrders ?? [0, 0, 0, 2, 34, 30, 2]) ?>;

        new Chart(ctxOrders, {
            type: 'bar',
            data: {
                labels: dowLabels,
                datasets: [{
                    data: dowValues,
                    backgroundColor: '#F25996',
                    hoverBackgroundColor: '#e04481',
                    borderRadius: 4,
                    borderSkipped: false,
                    barPercentage: 0.55,
                    categoryPercentage: 0.8
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        backgroundColor: '#0F172A',
                        padding: 8,
                        displayColors: false,
                        callbacks: {
                            label: function(context) {
                                return 'Orders: ' + context.raw;
                            }
                        }
                    }
                },
                scales: {
                    x: {
                        grid: { display: false, drawBorder: false },
                        ticks: { color: '#94A3B8', font: { size: 11 } }
                    },
                    y: {
                        grid: { color: '#F1F5F9', drawBorder: false },
                        ticks: {
                            color: '#94A3B8',
                            font: { size: 11 },
                            stepSize: 10,
                            beginAtZero: true
                        }
                    }
                }
            }
        });
    }
});

// Date Picker helper & URL redirect
function triggerDatePicker(inputId) {
    const input = document.getElementById(inputId);
    if (!input) return;
    if (typeof input.showPicker === 'function') {
        try {
            input.showPicker();
            return;
        } catch(e) {}
    }
    input.focus();
    input.click();
}

function applyCustomDateRange() {
    const startDate = document.getElementById('dash_start_date')?.value;
    const endDate = document.getElementById('dash_end_date')?.value;
    if (!startDate || !endDate) return;

    if (new Date(startDate) > new Date(endDate)) {
        alert('Start date cannot be after End date');
        return;
    }

    const url = new URL(window.location.href);
    url.searchParams.set('start_date', startDate);
    url.searchParams.set('end_date', endDate);
    url.searchParams.delete('range');
    url.searchParams.delete('refresh');
    window.location.href = url.toString();
}

// Close date dropdown when clicking outside
document.addEventListener('click', function(event) {
    const dropdown = document.getElementById('saas-date-dropdown');
    if (dropdown && !dropdown.classList.contains('hidden') && !event.target.closest('.relative')) {
        dropdown.classList.add('hidden');
    }
    const exportDropdown = document.getElementById('saas-export-dropdown');
    if (exportDropdown && !exportDropdown.classList.contains('hidden') && !event.target.closest('.relative')) {
        exportDropdown.classList.add('hidden');
    }
});

function exportDashboardPDF() {
    const { jsPDF } = window.jspdf;
    const doc = new jsPDF();
    const startDate = document.getElementById('dash_start_date')?.value || '<?= $startDateVal ?>';
    const endDate = document.getElementById('dash_end_date')?.value || '<?= $endDateVal ?>';
    const dateLabel = startDate + " to " + endDate;
    
    // Title
    doc.setFontSize(18);
    doc.setTextColor(30, 41, 59); // slate-800
    doc.text("Dashboard Overview Report", 14, 22);
    
    doc.setFontSize(10);
    doc.setTextColor(100, 116, 139); // slate-500
    doc.text("Date Range: " + dateLabel + " | Generated on: " + new Date().toLocaleDateString(), 14, 30);
    
    // Key Metrics
    const metricsData = [
        ['Total Revenue (Gross/Net)', 'Rs. <?= number_format($totalRevenue, 2, '.', '') ?>'],
        ['GST Tax Revenue', 'Rs. <?= number_format($totalGstTaxRevenue ?? 0, 2, '.', '') ?> (SGST: Rs. <?= number_format($totalSgstRevenue ?? 0, 2, '.', '') ?> + CGST: Rs. <?= number_format($totalCgstRevenue ?? 0, 2, '.', '') ?>)'],
        ['Taxable Base Revenue', 'Rs. <?= number_format($totalBaseRevenue ?? 0, 2, '.', '') ?>'],
        ['Refunded Amount', 'Rs. <?= number_format($totalRefunds ?? 0, 2, '.', '') ?>'],
        ['Total Orders', '<?= $totalOrders ?> (<?= number_format($pendingOrders ?? 0) ?> pending)'],
        ['Total Customers', '<?= $totalBuyers ?>'],
        ['Total Products', '<?= $totalProducts ?>'],
        ['Regular Products', '<?= $regularProducts ?>'],
        ['Customized Products', '<?= $customizedProducts ?>']
    ];
    
    doc.autoTable({
        startY: 38,
        head: [['Metric', 'Value']],
        body: metricsData,
        theme: 'grid',
        headStyles: { fillColor: [242, 89, 150] }, // brand pink #F25996
        styles: { fontSize: 10, cellPadding: 4 }
    });
    
    // Top Selling Products
    let currentY = doc.lastAutoTable.finalY + 15;
    
    doc.setFontSize(14);
    doc.setTextColor(30, 41, 59);
    doc.text("Top Selling Products", 14, currentY);
    
    <?php if(!empty($topSellingProducts)): ?>
    const productsData = [
        <?php foreach($topSellingProducts as $p): ?>
        ['<?= addslashes($p['name']) ?>', '<?= $p['sold'] ?>', 'Rs. <?= number_format($p['revenue'], 2, '.', '') ?>'],
        <?php endforeach; ?>
    ];
    
    doc.autoTable({
        startY: currentY + 6,
        head: [['Product Name', 'Sold Qty', 'Revenue']],
        body: productsData,
        theme: 'striped',
        headStyles: { fillColor: [249, 115, 22] } // orange-500
    });
    currentY = doc.lastAutoTable.finalY + 15;
    <?php else: ?>
    doc.setFontSize(10);
    doc.text("No products sold yet in this period.", 14, currentY + 8);
    currentY += 15;
    <?php endif; ?>
    
    // Recent Orders
    doc.setFontSize(14);
    doc.setTextColor(30, 41, 59);
    doc.text("Recent Orders", 14, currentY);
    
    <?php if(!empty($recentOrders)): ?>
    const ordersData = [
        <?php foreach($recentOrders as $o): ?>
        ['<?= addslashes($o['order_number']) ?>', '<?= addslashes($o['customer_name']) ?>', 'Rs. <?= number_format($o['total'], 2, '.', '') ?>', '<?= addslashes($o['status']) ?>', '<?= date("M d, Y", strtotime($o['created_at'])) ?>'],
        <?php endforeach; ?>
    ];
    
    doc.autoTable({
        startY: currentY + 6,
        head: [['Order #', 'Customer', 'Total', 'Status', 'Date']],
        body: ordersData,
        theme: 'striped',
        headStyles: { fillColor: [242, 89, 150] } // brand pink #F25996
    });
    <?php else: ?>
    doc.setFontSize(10);
    doc.text("No orders in this period.", 14, currentY + 8);
    <?php endif; ?>

    // Download
    doc.save("Dashboard_Report_" + startDate + "_to_" + endDate + ".pdf");
}

function exportDashboardCSV() {
    const startDate = document.getElementById('dash_start_date')?.value || '<?= $startDateVal ?>';
    const endDate = document.getElementById('dash_end_date')?.value || '<?= $endDateVal ?>';
    const dateLabel = startDate + " to " + endDate;
    let csv = [];
    csv.push(["Dashboard Overview Report"]);
    csv.push(["Date Range", dateLabel]);
    csv.push(["Generated At", new Date().toLocaleString()]);
    csv.push([]);
    csv.push(["KEY METRICS", "VALUE"]);
    csv.push(["Total Revenue (Gross/Net)", "Rs. <?= number_format($totalRevenue, 2, '.', '') ?>"]);
    csv.push(["GST Tax Revenue", "Rs. <?= number_format($totalGstTaxRevenue ?? 0, 2, '.', '') ?>"]);
    csv.push(["SGST Revenue", "Rs. <?= number_format($totalSgstRevenue ?? 0, 2, '.', '') ?>"]);
    csv.push(["CGST Revenue", "Rs. <?= number_format($totalCgstRevenue ?? 0, 2, '.', '') ?>"]);
    csv.push(["Taxable Base Revenue", "Rs. <?= number_format($totalBaseRevenue ?? 0, 2, '.', '') ?>"]);
    csv.push(["Refunded Amount", "Rs. <?= number_format($totalRefunds ?? 0, 2, '.', '') ?>"]);
    csv.push(["Total Orders", "<?= $totalOrders ?>"]);
    csv.push(["Pending Orders", "<?= $pendingOrders ?? 0 ?>"]);
    csv.push(["Total Customers", "<?= $totalBuyers ?>"]);
    csv.push(["Total Products", "<?= $totalProducts ?>"]);
    csv.push(["Regular Products", "<?= $regularProducts ?>"]);
    csv.push(["Customized Products", "<?= $customizedProducts ?>"]);
    csv.push([]);
    csv.push(["TOP SELLING PRODUCTS", "SOLD QTY", "REVENUE (Rs.)"]);
    <?php foreach($topSellingProducts as $p): ?>
    csv.push([<?= json_encode($p['name']) ?>, <?= (int)$p['sold'] ?>, "<?= number_format($p['revenue'], 2, '.', '') ?>"]);
    <?php endforeach; ?>
    csv.push([]);
    csv.push(["RECENT ORDERS", "CUSTOMER", "TOTAL (Rs.)", "STATUS", "DATE"]);
    <?php foreach($recentOrders as $o): ?>
    csv.push([<?= json_encode($o['order_number']) ?>, <?= json_encode($o['customer_name']) ?>, "<?= number_format($o['total'], 2, '.', '') ?>", <?= json_encode($o['status']) ?>, <?= json_encode($o['created_at']) ?>]);
    <?php endforeach; ?>

    let csvContent = "\uFEFF" + csv.map(e => e.map(cell => '"' + ('' + (cell ?? '')).replace(/"/g, '""') + '"').join(",")).join("\r\n");
    let blob = new Blob([csvContent], { type: "text/csv;charset=utf-8;" });
    let url = URL.createObjectURL(blob);
    let link = document.createElement("a");
    link.setAttribute("href", url);
    link.setAttribute("download", "Dashboard_Report_" + startDate + "_to_" + endDate + ".csv");
    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);
}
</script>
