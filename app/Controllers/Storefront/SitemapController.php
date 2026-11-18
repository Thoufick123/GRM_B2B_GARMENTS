<?php
namespace App\Controllers\Storefront;

use Core\Controller;
use Core\Database;
use Core\Cache;

class SitemapController extends Controller
{
    /**
     * Generate dynamic XML Sitemap for search engines
     * Adheres to https://www.sitemaps.org/schemas/sitemap/0.9/
     * Includes Google Image Sitemap extensions
     */
    public function index()
    {
        // Set proper XML header
        header('Content-Type: application/xml; charset=utf-8');
        header('X-Robots-Tag: noindex'); // Don't index the sitemap XML itself as HTML

        // Check cache (1 hour) unless fresh requested
        $cacheKey = 'dynamic_xml_sitemap';
        $nocache = isset($_GET['nocache']) || isset($_GET['refresh']);
        if (!$nocache && class_exists('\Core\Cache')) {
            $cachedXml = Cache::get($cacheKey);
            if ($cachedXml) {
                echo $cachedXml;
                exit;
            }
        }

        $baseUrl = defined('BASE_URL') && !empty(BASE_URL) && strpos(BASE_URL, 'localhost') === false 
            ? rtrim(BASE_URL, '/') 
            : 'https://grmgarments.com';
        $now = date('c');

        $db = Database::getInstance();

        // 1. Static Storefront Pages
        $staticPages = [
            [
                'loc'        => $baseUrl . '/',
                'lastmod'    => $now,
                'changefreq' => 'daily',
                'priority'   => '1.0'
            ],
            [
                'loc'        => $baseUrl . '/catalog',
                'lastmod'    => $now,
                'changefreq' => 'daily',
                'priority'   => '0.9'
            ],
            [
                'loc'        => $baseUrl . '/customize',
                'lastmod'    => $now,
                'changefreq' => 'weekly',
                'priority'   => '0.8'
            ],
            [
                'loc'        => $baseUrl . '/project',
                'lastmod'    => $now,
                'changefreq' => 'weekly',
                'priority'   => '0.8'
            ],
            [
                'loc'        => $baseUrl . '/size-chart',
                'lastmod'    => $now,
                'changefreq' => 'monthly',
                'priority'   => '0.7'
            ],
            [
                'loc'        => $baseUrl . '/about',
                'lastmod'    => $now,
                'changefreq' => 'monthly',
                'priority'   => '0.7'
            ],
            [
                'loc'        => $baseUrl . '/terms',
                'lastmod'    => $now,
                'changefreq' => 'monthly',
                'priority'   => '0.5'
            ],
            [
                'loc'        => $baseUrl . '/privacy-policy',
                'lastmod'    => $now,
                'changefreq' => 'monthly',
                'priority'   => '0.5'
            ],
        ];

        // 2. Active Categories
        $categories = [];
        try {
            $catStmt = $db->query("
                SELECT id, slug, name, updated_at 
                FROM categories 
                WHERE status = 'active' 
                ORDER BY id ASC
            ");
            $categories = $catStmt->fetchAll(\PDO::FETCH_ASSOC) ?: [];
        } catch (\Throwable $e) {
            error_log("Sitemap category query error: " . $e->getMessage());
        }

        // 3. Active Subcategories
        $subCategories = [];
        try {
            $subStmt = $db->query("
                SELECT sc.id, sc.slug, sc.name, sc.category_id, sc.updated_at 
                FROM sub_categories sc 
                JOIN categories c ON sc.category_id = c.id 
                WHERE sc.status = 'active' AND c.status = 'active'
                ORDER BY sc.id ASC
            ");
            $subCategories = $subStmt->fetchAll(\PDO::FETCH_ASSOC) ?: [];
        } catch (\Throwable $e) {
            error_log("Sitemap subcategory query error: " . $e->getMessage());
        }

        // 4. Active & Approved Products
        $products = [];
        try {
            $prodStmt = $db->query("
                SELECT p.id, p.slug, p.name, p.updated_at, img.image_path AS primary_image 
                FROM products p 
                LEFT JOIN product_images img ON img.product_id = p.id AND img.is_primary = 1 
                WHERE p.status = 'active' 
                  AND (p.approval_status = 'approved' OR p.approval_status IS NULL)
                ORDER BY p.id DESC
            ");
            $products = $prodStmt->fetchAll(\PDO::FETCH_ASSOC) ?: [];
        } catch (\Throwable $e) {
            error_log("Sitemap products query error: " . $e->getMessage());
        }

        // 5. Active Fabric Customizations
        $fabrics = [];
        try {
            $fabStmt = $db->query("
                SELECT id, fabric_id, program_name, updated_at 
                FROM fabric_customizations 
                WHERE (status = 'active' OR customization_enabled = 1)
                ORDER BY id DESC
            ");
            $fabrics = $fabStmt->fetchAll(\PDO::FETCH_ASSOC) ?: [];
        } catch (\Throwable $e) {
            error_log("Sitemap fabrics query error: " . $e->getMessage());
        }

        // 6. Active CMS Projects
        $projects = [];
        try {
            $projStmt = $db->query("
                SELECT id, title, updated_at 
                FROM cms_projects 
                WHERE is_active = 1 
                ORDER BY id DESC
            ");
            $projects = $projStmt->fetchAll(\PDO::FETCH_ASSOC) ?: [];
        } catch (\Throwable $e) {
            error_log("Sitemap projects query error: " . $e->getMessage());
        }

        // Build XML string
        $xml = '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
        $xml .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9"' . "\n";
        $xml .= '        xmlns:image="http://www.google.com/schemas/sitemap-image/1.1">' . "\n";

        // Write Static Pages
        foreach ($staticPages as $page) {
            $xml .= "  <url>\n";
            $xml .= "    <loc>" . htmlspecialchars($page['loc'], ENT_XML1, 'UTF-8') . "</loc>\n";
            $xml .= "    <lastmod>" . date('Y-m-d', strtotime($page['lastmod'])) . "</lastmod>\n";
            $xml .= "    <changefreq>" . $page['changefreq'] . "</changefreq>\n";
            $xml .= "    <priority>" . $page['priority'] . "</priority>\n";
            $xml .= "  </url>\n";
        }

        // Write Categories
        foreach ($categories as $cat) {
            $catUrl = $baseUrl . '/catalog?category_id=' . (int)$cat['id'];
            $lastmod = !empty($cat['updated_at']) ? date('Y-m-d', strtotime($cat['updated_at'])) : date('Y-m-d');
            $xml .= "  <url>\n";
            $xml .= "    <loc>" . htmlspecialchars($catUrl, ENT_XML1, 'UTF-8') . "</loc>\n";
            $xml .= "    <lastmod>" . $lastmod . "</lastmod>\n";
            $xml .= "    <changefreq>weekly</changefreq>\n";
            $xml .= "    <priority>0.8</priority>\n";
            $xml .= "  </url>\n";
        }

        // Write Subcategories
        foreach ($subCategories as $sub) {
            $subUrl = $baseUrl . '/catalog?category_id=' . (int)$sub['category_id'] . '&amp;sub_category_id=' . (int)$sub['id'];
            $lastmod = !empty($sub['updated_at']) ? date('Y-m-d', strtotime($sub['updated_at'])) : date('Y-m-d');
            $xml .= "  <url>\n";
            $xml .= "    <loc>" . htmlspecialchars($subUrl, ENT_XML1, 'UTF-8') . "</loc>\n";
            $xml .= "    <lastmod>" . $lastmod . "</lastmod>\n";
            $xml .= "    <changefreq>weekly</changefreq>\n";
            $xml .= "    <priority>0.8</priority>\n";
            $xml .= "  </url>\n";
        }

        // Write Products (with Google Image Sitemap)
        foreach ($products as $prod) {
            $prodUrl = $baseUrl . '/product?id=' . (int)$prod['id'];
            $lastmod = !empty($prod['updated_at']) ? date('Y-m-d', strtotime($prod['updated_at'])) : date('Y-m-d');
            $xml .= "  <url>\n";
            $xml .= "    <loc>" . htmlspecialchars($prodUrl, ENT_XML1, 'UTF-8') . "</loc>\n";
            $xml .= "    <lastmod>" . $lastmod . "</lastmod>\n";
            $xml .= "    <changefreq>daily</changefreq>\n";
            $xml .= "    <priority>0.9</priority>\n";

            if (!empty($prod['primary_image'])) {
                $imgUrl = function_exists('get_image_url') ? get_image_url($prod['primary_image']) : $prod['primary_image'];
                if (!empty($imgUrl)) {
                    if (!str_starts_with($imgUrl, 'http://') && !str_starts_with($imgUrl, 'https://')) {
                        $imgUrl = $baseUrl . '/' . ltrim($imgUrl, '/');
                    }
                    $xml .= "    <image:image>\n";
                    $xml .= "      <image:loc>" . htmlspecialchars($imgUrl, ENT_XML1, 'UTF-8') . "</image:loc>\n";
                    $xml .= "      <image:title>" . htmlspecialchars($prod['name'] ?? '', ENT_XML1, 'UTF-8') . "</image:title>\n";
                    $xml .= "    </image:image>\n";
                }
            }
            $xml .= "  </url>\n";
        }

        // Write Fabric Customizations
        foreach ($fabrics as $fab) {
            $fabId = !empty($fab['fabric_id']) ? (int)$fab['fabric_id'] : (int)$fab['id'];
            $fabUrl = $baseUrl . '/fabrics/customize/' . $fabId;
            $lastmod = !empty($fab['updated_at']) ? date('Y-m-d', strtotime($fab['updated_at'])) : date('Y-m-d');
            $xml .= "  <url>\n";
            $xml .= "    <loc>" . htmlspecialchars($fabUrl, ENT_XML1, 'UTF-8') . "</loc>\n";
            $xml .= "    <lastmod>" . $lastmod . "</lastmod>\n";
            $xml .= "    <changefreq>weekly</changefreq>\n";
            $xml .= "    <priority>0.8</priority>\n";
            $xml .= "  </url>\n";
        }

        $xml .= '</urlset>';

        // Cache output
        if (class_exists('\Core\Cache')) {
            Cache::set($cacheKey, $xml, 3600);
        }

        // Also write static sitemap.xml files on disk as high-performance mirror
        try {
            $rootSitemap = BASE_PATH . '/sitemap.xml';
            $publicSitemap = BASE_PATH . '/public/sitemap.xml';
            @file_put_contents($rootSitemap, $xml);
            if (is_dir(BASE_PATH . '/public')) {
                @file_put_contents($publicSitemap, $xml);
            }
        } catch (\Throwable $e) {
            // Non-blocking file write
        }

        echo $xml;
        exit;
    }

    /**
     * Dynamic robots.txt route
     */
    public function robots()
    {
        header('Content-Type: text/plain; charset=utf-8');
        $baseUrl = defined('BASE_URL') && !empty(BASE_URL) && strpos(BASE_URL, 'localhost') === false 
            ? rtrim(BASE_URL, '/') 
            : 'https://grmgarments.com';

        echo "# robots.txt for " . APP_NAME . "\n";
        echo "User-agent: *\n\n";

        echo "# Allow Storefront & Public Content\n";
        echo "Allow: /\n";
        echo "Allow: /catalog\n";
        echo "Allow: /category\n";
        echo "Allow: /product\n";
        echo "Allow: /customize\n";
        echo "Allow: /fabrics/customize/\n";
        echo "Allow: /project\n";
        echo "Allow: /projects\n";
        echo "Allow: /size-chart\n";
        echo "Allow: /about\n";
        echo "Allow: /about-us\n";
        echo "Allow: /terms\n";
        echo "Allow: /terms-and-conditions\n";
        echo "Allow: /privacy-policy\n";
        echo "Allow: /css/\n";
        echo "Allow: /js/\n";
        echo "Allow: /uploads/\n\n";

        echo "# Disallow Admin & Vendor Management Portals\n";
        echo "Disallow: /admin/\n";
        echo "Disallow: /vendor/\n";
        echo "Disallow: /vendor-dashboard\n\n";

        echo "# Disallow Buyer Private Account & Order History\n";
        echo "Disallow: /dashboard/\n\n";

        echo "# Disallow Cart, Checkout & Payment Transactions\n";
        echo "Disallow: /cart\n";
        echo "Disallow: /cart/\n";
        echo "Disallow: /checkout\n";
        echo "Disallow: /checkout/\n";
        echo "Disallow: /order-success\n";
        echo "Disallow: /wishlist\n\n";

        echo "# Disallow Internal APIs & Authentication Processing\n";
        echo "Disallow: /api/\n";
        echo "Disallow: /login\n";
        echo "Disallow: /register\n";
        echo "Disallow: /vendor-register\n";
        echo "Disallow: /forgot-password\n";
        echo "Disallow: /verify-otp\n";
        echo "Disallow: /reset-password\n";
        echo "Disallow: /logout\n";
        echo "Disallow: /clear-cache\n\n";

        echo "# Search Engine Sitemaps\n";
        echo "Sitemap: " . $baseUrl . "/sitemap.xml\n";
        exit;
    }

    /**
     * Google Search Console HTML File Verification
     */
    public function googleVerification()
    {
        header('Content-Type: text/html; charset=utf-8');
        echo "google-site-verification: google74f165e30887dab5.html";
        exit;
    }
}
