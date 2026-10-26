<?php
namespace App\Controllers\Storefront;

use Core\Controller;
use Core\Session;
use App\Models\Product;
use App\Models\Category;
use App\Models\SubCategory;
use App\Models\Wishlist;
use App\Models\Review;
use App\Models\Attribute;
use App\Models\AttributeValue;

class CatalogController extends Controller
{
    public function index()
    {
        $productModel = new Product();
        $searchQuery = $_GET['q'] ?? null;
        $categoryId = $_GET['category_id'] ?? $_GET['id'] ?? null;
        $subCategoryId = $_GET['sub_category_id'] ?? $_GET['sub_id'] ?? null;
        $attributeFilters = $_GET['attr'] ?? [];
        $sort = in_array($_GET['sort'] ?? '', ['newest','price_asc','price_desc']) ? $_GET['sort'] : 'newest';
        
        $isDefaultCatalog = ($searchQuery === null && $categoryId === null && $subCategoryId === null && empty($attributeFilters) && $sort === 'newest');
        $catCacheKey = $isDefaultCatalog ? 'catalog_default_products' : null;
        $cachedProducts = ($catCacheKey && class_exists('\Core\Cache')) ? \Core\Cache::get($catCacheKey) : null;

        if ($cachedProducts !== null) {
            $allProducts = $cachedProducts;
        } else {
            $allProducts = $productModel->getAllActive($searchQuery, $categoryId, $subCategoryId, $attributeFilters, $sort);
            
            // Attach variants to each product (batch loaded in a single query to eliminate N+1 latency)
            $pIds = array_column($allProducts, 'id');
            $variantsMap = $productModel->getInventoryVariantsForProducts($pIds);
            foreach ($allProducts as &$product) {
                $product['variants'] = $variantsMap[$product['id']] ?? [];
            }

            // Sync live approved review stats for catalog products
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
                    foreach ($allProducts as &$product) {
                        if (isset($revMap[$product['id']])) {
                            $product['num_reviews'] = $revMap[$product['id']]['num_reviews'];
                            $product['average_rating'] = $revMap[$product['id']]['average_rating'];
                        }
                    }
                } catch (\Throwable $e) {
                    error_log("Catalog review sync error: " . $e->getMessage());
                }
            }

            if ($catCacheKey && class_exists('\Core\Cache')) {
                \Core\Cache::set($catCacheKey, $allProducts, 600);
            }
        }

        // Server-side pagination
        $perPage     = 24;
        $page        = max(1, (int)($_GET['page'] ?? 1));
        $totalProducts = count($allProducts);
        $totalPages  = max(1, (int)ceil($totalProducts / $perPage));
        $page        = min($page, $totalPages);
        $products    = array_slice($allProducts, ($page - 1) * $perPage, $perPage);
        
        $categoryModel = new Category();
        $categories = $categoryModel->getAllActive(null, 0);

        $subCategoryModel = new SubCategory();
        $subCategories = $subCategoryModel->getAll(0);
        
        // Fetch Attributes if subcategory is selected
        $filterableAttributes = [];
        $attributeValues = [];
        if ($subCategoryId) {
            $attributeModel = new Attribute();
            $attributeValueModel = new AttributeValue();
            
            $filterableAttributes = $attributeModel->getFilterableBySubCategory($subCategoryId);
            
            if (!empty($filterableAttributes)) {
                $attrIds = array_column($filterableAttributes, 'id');
                $attributeValues = $attributeValueModel->getValuesForAttributes($attrIds);
            }
        }

        // Find selected category name for the title/banner
        $selectedCategoryName = "Wholesale Catalog";
        if ($categoryId) {
            foreach ($categories as $cat) {
                if ($cat['id'] == $categoryId) {
                    $selectedCategoryName = $cat['name'];
                    break;
                }
            }
        }

        $wishlistIds = [];
        if (Session::get('user_id')) {
            $wishlistModel = new Wishlist();
            $wishlistIds = $wishlistModel->getProductIds(Session::get('user_id'));
        }

        $canAddToCart = (Session::get('user_id') && Session::get('user_status') === 'active');

        $cmsSettings = class_exists('\Core\Cache') ? \Core\Cache::get('cms_product_carousel_settings') : null;
        if ($cmsSettings === null) {
            $cmsData = \Core\Database::getInstance()->query("SELECT content_data FROM cms_components WHERE section_type = 'product_carousel' LIMIT 1")->fetchColumn();
            $cmsSettings = $cmsData ? (json_decode($cmsData, true) ?? []) : [];
            if (class_exists('\Core\Cache')) {
                \Core\Cache::set('cms_product_carousel_settings', $cmsSettings, 3600);
            }
        }

        $this->render('storefront/catalog', [
            'title' => $selectedCategoryName,
            'products' => $products,
            'categories' => $categories,
            'subCategories' => $subCategories,
            'filterableAttributes' => $filterableAttributes,
            'attributeValues' => $attributeValues,
            'wishlistIds' => $wishlistIds,
            'canAddToCart' => $canAddToCart,
            'searchQuery' => $searchQuery,
            'selectedCategoryId' => $categoryId,
            'selectedSubCategoryId' => $subCategoryId,
            'selectedCategoryName' => $selectedCategoryName,
            'attributeFilters' => $attributeFilters,
            'currentSort' => $sort,
            'wishlist_color' => $cmsSettings['wishlist_color'] ?? '#111827',
            'subtitle_color' => $cmsSettings['subtitle_color'] ?? '#6b7280',
            'text_color' => $cmsSettings['text_color'] ?? '#4b5563',
            'currentPage'   => $page,
            'totalPages'    => $totalPages,
            'totalProducts' => $totalProducts,
            'perPage'       => $perPage,
        ]);
    }

    public function ajaxSearch()
    {
        $searchQuery = $_GET['q'] ?? '';
        
        $categoryModel = new Category();
        $categories = $categoryModel->getAllActive($searchQuery, 0);
        $categories = array_slice($categories, 0, 5); // Limit to 5 categories

        $productModel = new Product();
        $products = $productModel->getAllActive($searchQuery, null, null, [], 'newest', 0);
        $products = array_slice($products, 0, 10); // Limit to 10 products
        
        $isLoggedIn = Session::get('user_id') ? true : false;
        
        $formattedProducts = [];
        foreach ($products as $p) {
            $gst = (float)($p['variant_total_gst'] ?? 0);
            if ($gst <= 0) $gst = (float)($p['total_gst'] ?? 0);
            if ($gst <= 0) $gst = (float)($p['category_sgst'] ?? 0) + (float)($p['category_cgst'] ?? 0);
            
            $basePrice = (isset($p['base_price']) && (float)$p['base_price'] > 0) ? (float)$p['base_price'] : (float)($p['wholesale_price'] ?? 0);
            $discountPrice = (!empty($p['discount_price']) && (float)$p['discount_price'] > 0) ? (float)$p['discount_price'] : null;
            $effPrice = $discountPrice ?? $basePrice;

            $displayPrice = $effPrice;

            $formattedProducts[] = [
                'id' => $p['id'],
                'name' => $p['name'],
                'category_name' => $p['category_name'],
                'primary_image' => $p['primary_image'] ? get_image_url($p['primary_image']) : null,
                'base_price' => $isLoggedIn ? $basePrice : null,
                'discount_price' => $isLoggedIn && $discountPrice ? $displayPrice : null,
                'display_price' => $isLoggedIn ? $displayPrice : null,
            ];
        }

        header('Content-Type: application/json');
        echo json_encode([
            'categories' => $categories,
            'products' => $formattedProducts
        ]);
        exit;
    }

    public function ajaxAttributes()
    {
        $subCategoryId = $_GET['sub_category_id'] ?? null;
        if (!$subCategoryId) {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'attributes' => [], 'values' => []]);
            return;
        }

        $attributeModel = new Attribute();
        $attributeValueModel = new AttributeValue();
        
        $attributes = $attributeModel->getFilterableBySubCategory($subCategoryId);
        if (!empty($attributes)) {
            $attrIds = array_column($attributes, 'id');
            $values = $attributeValueModel->getValuesForAttributes($attrIds);
            header('Content-Type: application/json');
            echo json_encode(['success' => true, 'attributes' => $attributes, 'values' => $values]);
        } else {
            header('Content-Type: application/json');
            echo json_encode(['success' => true, 'attributes' => [], 'values' => []]);
        }
    }

    public function view()
    {
        $id = (int)($_GET['id'] ?? 0);
        if (!$id) {
            $this->redirect('/catalog');
            return;
        }

        // Remember return URL for guest visitors (e.g. from Instagram / direct link)
        if (!Session::get('user_id')) {
            $currentUri = $_SERVER['REQUEST_URI'] ?? ('/product?id=' . (int)$id);
            Session::set('return_to', $currentUri);
        }

        // Cache the product view bundle (invalidated on product/review updates, bypassable with nocache)
        $prodCacheKey = 'product_details_bundle_' . $id;
        $nocache = isset($_GET['nocache']) || isset($_GET['refresh']);
        $bundle = (class_exists('\Core\Cache') && !$nocache) ? \Core\Cache::get($prodCacheKey) : null;

        if ($bundle === null) {
            $productModel = new Product();
            $product = $productModel->findById($id);

            if (!$product || $product['status'] !== 'active') {
                $this->redirect('/catalog');
                return;
            }

            // Customizable fabrics belong ONLY to the customization module
            if (!empty($product['is_customizable'])) {
                $this->redirect('/customize/fabric/' . (int)$product['id']);
                return;
            }

            $categoryModel = new Category();
            $category = !empty($product['category_id']) ? $categoryModel->findById($product['category_id']) : null;
            if (empty($category) || ($category['status'] ?? '') !== 'active') {
                $this->redirect('/catalog');
                return;
            }
            $subCategory = !empty($product['sub_category_id']) ? $categoryModel->findById($product['sub_category_id']) : null;

            $images = $productModel->getImages($id);
            $product['primary_image'] = null;
            foreach ($images as $img) {
                if ($img['is_primary']) {
                    $product['primary_image'] = $img['image_path'];
                    break;
                }
            }
            if (!$product['primary_image'] && !empty($images)) {
                $product['primary_image'] = $images[0]['image_path'];
            }

            // Fetch variants
            $variants = $productModel->getInventoryProductVariants($id);
            foreach ($variants as &$v) {
                if (!empty($v['images'])) {
                    $v['images'] = array_map(function($img) {
                        return get_image_url($img);
                    }, $v['images']);
                }
            }
            unset($v);

            // Fetch up to 5 related products from DIFFERENT categories/subcategories for Frequently Bought Together
            $relKey = 'rel_prods_diff_' . (int)$id;
            $relatedProducts = class_exists('\Core\Cache') ? \Core\Cache::get($relKey) : null;
            if ($relatedProducts === null) {
                $db = \Core\Database::getInstance();
                $subCatId = !empty($product['sub_category_id']) ? (int)$product['sub_category_id'] : null;
                $catId = !empty($product['category_id']) ? (int)$product['category_id'] : null;

                $params = ['id' => $id];
                $whereClause = "WHERE p.status = 'active' AND p.category_id IS NOT NULL AND (p.is_customizable = 0 OR p.is_customizable IS NULL) AND p.id != :id";
                
                if ($subCatId) {
                    $whereClause .= " AND (p.sub_category_id IS NULL OR p.sub_category_id != :exclude_sub_id)";
                    $params['exclude_sub_id'] = $subCatId;
                } elseif ($catId) {
                    $whereClause .= " AND p.category_id != :exclude_cat_id";
                    $params['exclude_cat_id'] = $catId;
                }

                $stmt = $db->prepare("
                    SELECT p.*, c.name AS category_name,
                           img.image_path AS primary_image,
                           pv_agg.base_price,
                           pv_agg.discount_price
                    FROM products p
                    JOIN categories c ON p.category_id = c.id AND c.status = 'active'
                    LEFT JOIN product_images img ON img.product_id = p.id AND img.is_primary = 1
                    LEFT JOIN (
                        SELECT product_id, 
                               MIN(base_price) AS base_price,
                               MIN(CASE WHEN discount_price > 0 THEN discount_price ELSE NULL END) AS discount_price 
                        FROM product_variants GROUP BY product_id
                    ) pv_agg ON pv_agg.product_id = p.id
                    $whereClause
                    ORDER BY p.id DESC
                ");
                $stmt->execute($params);
                $candidateProducts = $stmt->fetchAll();

                $relatedProducts = [];
                $usedCats = [];

                foreach ($candidateProducts as $cp) {
                    $grp = $cp['sub_category_id'] ?? $cp['category_id'];
                    if (!in_array($grp, $usedCats)) {
                        $usedCats[] = $grp;
                        $relatedProducts[] = $cp;
                        if (count($relatedProducts) >= 5) break;
                    }
                }

                if (count($relatedProducts) < 5) {
                    $existingIds = array_column($relatedProducts, 'id');
                    foreach ($candidateProducts as $cp) {
                        if (!in_array($cp['id'], $existingIds)) {
                            $relatedProducts[] = $cp;
                            if (count($relatedProducts) >= 5) break;
                        }
                    }
                }

                if (class_exists('\Core\Cache')) {
                    \Core\Cache::set($relKey, $relatedProducts, 1800);
                }
            }

            $reviewModel = new Review();
            $reviews = $reviewModel->getApprovedProductReviews($id);

            // Fetch any active B2B Category-to-Subcategory Offer Rules for this product's category
            $categoryOfferRule = null;
            if (!empty($product['category_id'])) {
                $ruleModel = new \App\Models\OrderRule();
                $activeRules = $ruleModel->getActiveRules();
                foreach ($activeRules as $r) {
                    if ($r['category_id'] == $product['category_id']) {
                        $allCats = class_exists('\Core\Cache') ? \Core\Cache::get('catalog_all_categories_list') : null;
                        if ($allCats === null) {
                            $categoryModel = new Category();
                            $allCats = $categoryModel->getAll();
                            if (class_exists('\Core\Cache')) {
                                \Core\Cache::set('catalog_all_categories_list', $allCats, 3600);
                            }
                        }
                        $catsById = [];
                        foreach ($allCats as $ac) {
                            $catsById[$ac['id']] = $ac;
                        }

                        $secondRulesRaw = json_decode($r['second_category_rules'], true) ?? [];
                        $secondaryOffers = [];
                        foreach ($secondRulesRaw as $scId => $sAmt) {
                            $scId = (int)$scId;
                            if (isset($catsById[$scId])) {
                                $secondaryOffers[] = [
                                    'id' => $scId,
                                    'name' => $catsById[$scId]['name'],
                                    'slug' => $catsById[$scId]['slug'],
                                    'min_amount' => (float)$sAmt
                                ];
                            }
                        }

                        $categoryOfferRule = [
                            'min_amount' => (float)$r['min_amount'],
                            'main_category_name' => $r['category_name'],
                            'secondary_offers' => $secondaryOffers
                        ];
                        break;
                    }
                }
            }

            // Fetch Volume Pricing Tiers for this product
            $tierCacheKey = 'prod_tiers_' . (int)$id;
            $volumeTiers = class_exists('\Core\Cache') ? \Core\Cache::get($tierCacheKey) : null;
            if ($volumeTiers === null) {
                $db = \Core\Database::getInstance();
                $tierStmt = $db->prepare("SELECT * FROM product_volume_tiers WHERE product_id = ? ORDER BY min_qty ASC");
                $tierStmt->execute([$id]);
                $volumeTiers = $tierStmt->fetchAll(\PDO::FETCH_ASSOC);
                if (class_exists('\Core\Cache')) {
                    \Core\Cache::set($tierCacheKey, $volumeTiers, 1800);
                }
            }

            $bundle = compact('product', 'category', 'subCategory', 'images', 'variants', 'relatedProducts', 'reviews', 'categoryOfferRule', 'volumeTiers');
            if (class_exists('\Core\Cache')) {
                \Core\Cache::set($prodCacheKey, $bundle, 300);
            }
        } else {
            // Unpack from fast local cache
            $product = $bundle['product'];
            $category = $bundle['category'];
            $subCategory = $bundle['subCategory'];
            $images = $bundle['images'];
            $variants = $bundle['variants'];
            $relatedProducts = $bundle['relatedProducts'];
            $categoryOfferRule = $bundle['categoryOfferRule'];
            $volumeTiers = $bundle['volumeTiers'];
        }

        // Reviews are dynamic customer submissions: ALWAYS fetch latest approved reviews directly!
        $reviewModel = new Review();
        $reviews = $reviewModel->getApprovedProductReviews($id);

        // Keep product rating & count in sync with verified reviews
        if (!empty($reviews)) {
            $product['num_reviews'] = count($reviews);
            $product['average_rating'] = round(array_sum(array_column($reviews, 'rating')) / count($reviews), 2);
        }

        // User-specific data (not cached across users)
        $wishlistIds = [];
        if (Session::get('user_id')) {
            $wishlistModel = new Wishlist();
            $wishlistIds = $wishlistModel->getProductIds(Session::get('user_id'));
        }

        $this->render('storefront/product_details', [
            'title' => $product['name'],
            'product' => $product,
            'category' => $category,
            'subCategory' => $subCategory,
            'categoryOfferRule' => $categoryOfferRule,
            'images' => $images,
            'variants' => $variants,
            'relatedProducts' => $relatedProducts,
            'wishlistIds' => $wishlistIds,
            'reviews' => $reviews,
            'volumeTiers' => $volumeTiers
        ]);
    }

    public function searchAjax()
    {
        header('Content-Type: application/json');
        
        // Unblock session file so concurrent AJAX requests from the same user don't queue up
        session_write_close();
        
        $query = $_GET['q'] ?? '';
        
        if (empty(trim($query))) {
            echo json_encode(['success' => true, 'results' => []]);
            return;
        }

        $productModel = new Product();
        $results = $productModel->searchActive($query);

        $formattedResults = [];
        foreach ($results as $r) {
            $gst = (float)($r['product_total_gst'] ?? 0);
            if ($gst <= 0) $gst = (float)($r['category_sgst'] ?? 0) + (float)($r['category_cgst'] ?? 0);

            $basePrice = (isset($r['base_price']) && (float)$r['base_price'] > 0) ? (float)$r['base_price'] : (float)($r['wholesale_price'] ?? 0);
            $discountPrice = (!empty($r['discount_price']) && (float)$r['discount_price'] > 0) ? (float)$r['discount_price'] : null;
            $effPrice = $discountPrice ?? $basePrice;

            $displayPrice = $effPrice;

            $formattedResults[] = [
                'id' => $r['id'],
                'name' => $r['name'],
                'url' => BASE_URL . '/product?id=' . $r['id'],
                'image' => $r['primary_image'] ? get_image_url($r['primary_image']) : 'https://placehold.co/100x100?text=No+Img',
                'price' => '₹' . number_format($displayPrice, 2)
            ];
        }

        echo json_encode(['success' => true, 'results' => $formattedResults]);
        return;
    }
}
