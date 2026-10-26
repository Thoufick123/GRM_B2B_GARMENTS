<?php
namespace App\Controllers\Storefront;

use Core\Controller;
use App\Models\Product;

class HomeController extends Controller
{
    public function index()
    {
        $cachedProducts = class_exists('\Core\Cache') ? \Core\Cache::get('home_active_products') : null;
        if ($cachedProducts !== null) {
            $products = $cachedProducts;
        } else {
            $productModel = new Product();
            $products = $productModel->getAllActive(null, null, null, [], 'newest', 0);

            // Attach variants to each product (batch loaded in a single query to eliminate N+1 latency)
            $pIds = array_column($products, 'id');
            $variantsMap = $productModel->getInventoryVariantsForProducts($pIds);
            foreach ($products as &$product) {
                $product['variants'] = $variantsMap[$product['id']] ?? [];
            }

            // Sync live approved review stats for home products
            if (!empty($pIds)) {
                try {
                    $pIdList = implode(',', array_map('intval', $pIds));
                    $revStats = \Core\Database::getInstance()->query("
                        SELECT product_id, COUNT(*) as cnt, AVG(rating) as avg_rating 
                        FROM reviews 
                        WHERE product_id IN ($pIdList) AND is_approved = 1 
                        GROUP BY product_id
                    ")->fetchAll(\PDO::FETCH_ASSOC);
                    $revMap = [];
                    foreach ($revStats as $rs) {
                        $revMap[$rs['product_id']] = [
                            'num_reviews' => (int)$rs['cnt'],
                            'average_rating' => round((float)$rs['avg_rating'], 2)
                        ];
                    }
                    foreach ($products as &$product) {
                        if (isset($revMap[$product['id']])) {
                            $product['num_reviews'] = $revMap[$product['id']]['num_reviews'];
                            $product['average_rating'] = $revMap[$product['id']]['average_rating'];
                        }
                    }
                    unset($product);
                } catch (\Throwable $e) {
                    error_log("Home review sync error: " . $e->getMessage());
                }
            }

            if (class_exists('\Core\Cache')) {
                \Core\Cache::set('home_active_products', $products, 600); // 10 minutes cache
            }
        }

        $categoryModel = new \App\Models\Category();
        $categories = $categoryModel->getAll(0);

        $wishlistIds = [];
        if (\Core\Session::get('user_id')) {
            $wishlistModel = new \App\Models\Wishlist();
            $wishlistIds = $wishlistModel->getProductIds(\Core\Session::get('user_id'));
        } else {
            \Core\Session::remove('return_to');
        }

        // Fetch layout dynamically without curl to avoid PHP dev server deadlock
        $db = \Core\Database::getInstance();
        $isPreview = isset($_GET['preview']) && $_GET['preview'] == '1';
        $isAdmin = in_array(\Core\Session::get('user_role'), ['super_admin', 'manager', 'staff']);
        $noCache = $isPreview || $isAdmin || isset($_GET['nocache']);
        $cachedData = $noCache ? null : (class_exists('\Core\Cache') ? \Core\Cache::get('cms_layout_home') : null);
        
        if ($cachedData !== null) {
            $layoutSections = $cachedData;
        } else {
            $stmt = $db->prepare("SELECT layout_data FROM cms_layouts WHERE page_name = 'home'");
            $stmt->execute();
            $layout = $stmt->fetch();
            
            if ($isPreview && isset($_GET['layout']) && !empty($_GET['layout'])) {
                $componentIds = explode(',', $_GET['layout']);
            } else {
                $componentIds = $layout && !empty($layout['layout_data']) ? json_decode($layout['layout_data'], true) : [];
            }
            
            $layoutSections = [];
            if (!empty($componentIds)) {
                $placeholders = implode(',', array_fill(0, count($componentIds), '?'));
                $activeCond = $isPreview ? "" : " AND is_active = 1";
                $stmt = $db->prepare("SELECT * FROM cms_components WHERE id IN ($placeholders) $activeCond");
                $stmt->execute($componentIds);
                $componentsData = $stmt->fetchAll();
                
                $componentsMap = [];
                foreach ($componentsData as $cmp) {
                    $cmp['content_data'] = json_decode($cmp['content_data'], true);
                    $componentsMap[$cmp['id']] = $cmp;
                }
                
                foreach ($componentIds as $id) {
                    if (isset($componentsMap[$id])) {
                        $layoutSections[] = $componentsMap[$id];
                    }
                }
                if (!$isPreview && class_exists('\Core\Cache')) {
                    \Core\Cache::set('cms_layout_home', $layoutSections, 300);
                }
            }
        }

        $this->render('storefront/home', [
            'title' => 'Wholesale Fashion Hub',
            'products' => $products,
            'categories' => $categories,
            'wishlistIds' => $wishlistIds,
            'layoutSections' => $layoutSections
        ]);
    }
    public function about()
    {
        $db = \Core\Database::getInstance();
        $isPreview = isset($_GET['preview']) && $_GET['preview'] == '1';
        $cachedData = $isPreview ? null : \Core\Cache::get('cms_about_us');
        
        if ($cachedData !== null) {
            $aboutUsComponent = $cachedData;
        } else {
            $activeCond = $isPreview ? "" : " AND is_active = 1";
            $stmt = $db->prepare("SELECT * FROM cms_components WHERE section_type = 'about_us' $activeCond ORDER BY id DESC LIMIT 1");
            $stmt->execute();
            $aboutUsComponent = $stmt->fetch();
            
            if ($aboutUsComponent) {
                $aboutUsComponent['content_data'] = json_decode($aboutUsComponent['content_data'], true);
            }
            if (!$isPreview) {
                \Core\Cache::set('cms_about_us', $aboutUsComponent, 3600);
            }
        }

        $this->render('storefront/about', [
            'title' => 'About Us - GRM B2B Wholesale',
            'aboutUs' => $aboutUsComponent
        ]);
    }

    public function terms()
    {
        $this->render('storefront/terms', [
            'title' => 'Terms & Conditions'
        ]);
    }

    public function privacyPolicy()
    {
        $this->render('storefront/privacy_policy', [
            'title' => 'GRM B2B Wholesale Privacy Policy'
        ]);
    }

    public function sizeChart()
    {
        $sizeChartModel = new \App\Models\SizeChart();
        $charts = $sizeChartModel->getAllActive();
        $categoryModel = new \App\Models\Category();
        $categories = $categoryModel->getAll();

        foreach ($charts as &$chart) {
            $chart['columns'] = json_decode($chart['columns_json'] ?? '[]', true) ?: [];
            $chart['rows'] = json_decode($chart['rows_json'] ?? '[]', true) ?: [];
        }
        unset($chart);

        $this->render('storefront/size_chart', [
            'title' => 'Garment Size & Measurement Charts',
            'charts' => $charts,
            'categories' => $categories
        ]);
    }

    public function apiSizeChart()
    {
        header('Content-Type: application/json');
        $chartId = (int)($_GET['chart_id'] ?? 0);
        $productId = (int)($_GET['product_id'] ?? 0);
        $categoryId = (int)($_GET['category_id'] ?? 0);
        $subCategoryId = (int)($_GET['sub_category_id'] ?? 0);
        
        $sizeChartModel = new \App\Models\SizeChart();

        // 1. If product_id is given, resolve product-specific size_chart_id and category/subcategory
        if ($productId > 0) {
            $productModel = new Product();
            $product = $productModel->findById($productId);
            if ($product) {
                if (empty($chartId) && !empty($product['size_chart_id'])) {
                    $chartId = (int)$product['size_chart_id'];
                }
                if (empty($categoryId) && !empty($product['category_id'])) {
                    $categoryId = (int)$product['category_id'];
                }
                if (empty($subCategoryId) && !empty($product['sub_category_id'])) {
                    $subCategoryId = (int)$product['sub_category_id'];
                }
            }
        }

        $charts = [];
        // 2. If a specific chart_id is requested or assigned to the product
        if ($chartId > 0) {
            $specificChart = $sizeChartModel->getById($chartId);
            if ($specificChart && (!isset($specificChart['is_active']) || $specificChart['is_active'])) {
                $charts[] = $specificChart;
            }
        }

        // 3. If no specific chart was found, fall back to subcategory/category matching
        if (empty($charts)) {
            $productName = !empty($product['name']) ? $product['name'] : null;
            if ($subCategoryId > 0 || $categoryId > 0) {
                $charts = $sizeChartModel->getByCategoryAndSubCategory($categoryId, $subCategoryId, $productName);
            }
        }

        foreach ($charts as &$chart) {
            $chart['columns'] = is_array($chart['columns_json'] ?? null) 
                ? $chart['columns_json'] 
                : (json_decode($chart['columns_json'] ?? '[]', true) ?: []);
            $chart['rows'] = is_array($chart['rows_json'] ?? null) 
                ? $chart['rows_json'] 
                : (json_decode($chart['rows_json'] ?? '[]', true) ?: []);
        }
        unset($chart);

        echo json_encode(['success' => true, 'charts' => $charts]);
        exit;
    }

    public function clearCache()
    {
        if (class_exists('\Core\Cache')) {
            \Core\Cache::clear();
        }
        header('Content-Type: text/html');
        echo "<div style='font-family:sans-serif;text-align:center;padding:50px;'>
            <h2 style='color:#16a34a;'>All Application Cache Cleared Successfully!</h2>
            <p>Database synchronization is complete.</p>
            <p><a href='" . BASE_URL . "/' style='display:inline-block;padding:10px 20px;background:#16a34a;color:#fff;text-decoration:none;border-radius:6px;'>Return to Storefront</a></p>
        </div>";
        exit;
    }
}


