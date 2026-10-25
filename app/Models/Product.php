<?php
namespace App\Models;

use Core\Model;

class Product extends Model
{
    public function __construct()
    {
        parent::__construct();
        $this->ensureSchema();
    }

    private function ensureSchema(): void
    {
        static $checked = false;
        if ($checked) return;
        $checked = true;

        try {
            $cols = $this->db->query("SHOW COLUMNS FROM `product_variants`")->fetchAll(\PDO::FETCH_COLUMN);
            $colsLower = array_map('strtolower', $cols);
            if (!empty($colsLower) && !in_array('reserve_stock', $colsLower)) {
                $this->db->exec("ALTER TABLE `product_variants` ADD COLUMN `reserve_stock` INT NOT NULL DEFAULT 0 AFTER `current_stock`");
            }
        } catch (\Throwable $e) {
            error_log("Product::ensureSchema notice: " . $e->getMessage());
        }
    }

    public function getAllActive($search = null, $categoryId = null, $subCategoryId = null, $attributeFilters = [], $sort = 'newest', ?int $isCustomizable = 0)
    {
        $sql = "
            SELECT p.*, c.name AS category_name, c.moq AS category_moq, c.sgst AS category_sgst, c.cgst AS category_cgst, c.hsn_code AS category_hsn_code,
                   sc_chart.title AS size_chart_title,
                   vp.store_name AS vendor_store_name, vp.company_name AS vendor_company_name, vp.unique_vendor_id,
                   u_v.name AS vendor_name,
                   CASE 
                       WHEN p.is_customizable = 1 THEN GREATEST(p.stock_quantity, 0)
                       ELSE COALESCE(
                           (SELECT SUM(GREATEST(pv_stock.current_stock, 0)) FROM product_variants pv_stock WHERE pv_stock.product_id = p.id),
                           GREATEST(p.stock_quantity, 0)
                       )
                   END AS stock_quantity,
                   CASE 
                       WHEN p.is_customizable = 1 THEN GREATEST(p.stock_quantity, 0)
                       ELSE (SELECT SUM(GREATEST(pv_stock.current_stock, 0)) FROM product_variants pv_stock WHERE pv_stock.product_id = p.id)
                   END AS current_stock_quantity,
                   CASE 
                       WHEN p.is_customizable = 1 THEN GREATEST(p.stock_quantity, 0)
                       ELSE (SELECT SUM(GREATEST(pv_stock.inventory, 0)) FROM product_variants pv_stock WHERE pv_stock.product_id = p.id)
                   END AS total_stock_quantity,
                   (SELECT image_path FROM product_images WHERE product_id = p.id AND is_primary = 1 LIMIT 1) AS primary_image,
                   (SELECT GROUP_CONCAT(DISTINCT CONCAT(av.value, ':', av.color_code) SEPARATOR '||') 
                    FROM product_variant_attributes pva 
                    JOIN attribute_values av ON pva.attribute_value_id = av.id 
                    JOIN product_variants pv_col ON pva.variant_id = pv_col.id 
                    WHERE pv_col.product_id = p.id AND av.color_code IS NOT NULL AND av.color_code != '') AS variant_colors_concat,
                   pv_agg.base_price,
                   pv_agg.discount_price,
                   pv_agg.max_amount,
                   pv_agg.total_gst AS variant_total_gst,
                   pv_agg.sgst AS variant_sgst,
                   pv_agg.cgst AS variant_cgst
            FROM products p
            JOIN categories c ON p.category_id = c.id AND c.status = 'active'
            LEFT JOIN size_charts sc_chart ON p.size_chart_id = sc_chart.id
            LEFT JOIN vendor_profiles vp ON p.vendor_id = vp.user_id
            LEFT JOIN users u_v ON p.vendor_id = u_v.id
            LEFT JOIN (
                SELECT product_id,
                       MIN(base_price) AS base_price,
                       MIN(CASE WHEN discount_price > 0 THEN discount_price ELSE NULL END) AS discount_price,
                       MIN(CASE WHEN max_amount > 0 THEN max_amount ELSE NULL END) AS max_amount,
                       MIN(total_gst) AS total_gst,
                       MIN(sgst) AS sgst,
                       MIN(cgst) AS cgst
                FROM product_variants
                GROUP BY product_id
            ) pv_agg ON pv_agg.product_id = p.id
            WHERE p.status = 'active' AND (p.approval_status = 'approved' OR p.approval_status IS NULL) AND p.category_id IS NOT NULL
        ";
        
        if (!is_vendor_module_enabled()) {
            $sql .= " AND (p.vendor_id IS NULL OR p.vendor_id = 0)";
        }
        
        $params = [];

        if ($isCustomizable !== null) {
            if ($isCustomizable === 1) {
                $sql .= " AND p.is_customizable = 1";
            } else {
                $sql .= " AND (p.is_customizable = 0 OR p.is_customizable IS NULL)";
            }
        }
        
        if ($categoryId) {
            $sql .= " AND p.category_id = :cat_id";
            $params['cat_id'] = $categoryId;
        }
        
        if ($subCategoryId) {
            $sql .= " AND p.sub_category_id = :sub_cat_id";
            $params['sub_cat_id'] = $subCategoryId;
        }

        if ($search) {
            $sql .= " AND (p.name LIKE :search1 OR c.name LIKE :search2 OR EXISTS (SELECT 1 FROM product_variants pv WHERE pv.product_id = p.id AND pv.sku LIKE :search3))";
            $searchParam = '%' . $search . '%';
            $params['search1'] = $searchParam;
            $params['search2'] = $searchParam;
            $params['search3'] = $searchParam;
        }
        
        if (!empty($attributeFilters)) {
            foreach ($attributeFilters as $attrId => $valIds) {
                if (empty($valIds)) continue;
                $valIdsStr = implode(',', array_map('intval', (array)$valIds));
                $attrIdInt = (int)$attrId;
                // Match products where the attribute is set at product-level OR at variant-level
                $sql .= " AND (
                    EXISTS (
                        SELECT 1 FROM product_attribute_values pav 
                        WHERE pav.product_id = p.id 
                        AND pav.attribute_id = {$attrIdInt}
                        AND pav.attribute_value_id IN ({$valIdsStr})
                    )
                    OR EXISTS (
                        SELECT 1 FROM product_variant_attributes pva
                        JOIN product_variants pv2 ON pv2.id = pva.variant_id
                        WHERE pv2.product_id = p.id
                        AND pva.attribute_id = {$attrIdInt}
                        AND pva.attribute_value_id IN ({$valIdsStr})
                    )
                )";
            }
        }
        
        // Sort
        switch ($sort) {
            case 'price_asc':
                $sql .= " ORDER BY pv_agg.base_price ASC, p.created_at DESC";
                break;
            case 'price_desc':
                $sql .= " ORDER BY pv_agg.base_price DESC, p.created_at DESC";
                break;
            default: // newest
                $sql .= " ORDER BY p.created_at DESC";
                break;
        }
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        $products = $stmt->fetchAll();

        foreach ($products as &$p) {
            $p['colors'] = [];
            if (!empty($p['variant_colors_concat'])) {
                $pairs = explode('||', $p['variant_colors_concat']);
                foreach ($pairs as $pair) {
                    $parts = explode(':', $pair, 2);
                    if (count($parts) === 2 && !empty($parts[1])) {
                        $p['colors'][] = [
                            'name' => $parts[0],
                            'color_code' => $parts[1]
                        ];
                    }
                }
            }
            unset($p['variant_colors_concat']);
        }
        unset($p);

        return $products;
    }


    public function searchActive(string $query)
    {
        $sql = "
            SELECT DISTINCT p.id, p.name, p.slug, p.wholesale_price, p.max_amount AS product_max_amount, p.total_gst AS product_total_gst,
                   c.sgst AS category_sgst, c.cgst AS category_cgst,
                   (SELECT image_path FROM product_images WHERE product_id = p.id AND is_primary = 1 LIMIT 1) as primary_image,
                   (SELECT MIN(base_price) FROM product_variants WHERE product_id = p.id) as base_price,
                   (SELECT MIN(CASE WHEN discount_price > 0 THEN discount_price ELSE NULL END) FROM product_variants WHERE product_id = p.id) as discount_price,
                   (SELECT MIN(CASE WHEN max_amount > 0 THEN max_amount ELSE NULL END) FROM product_variants WHERE product_id = p.id) as max_amount
            FROM products p
            LEFT JOIN product_variants pv ON p.id = pv.product_id
            JOIN categories c ON p.category_id = c.id AND c.status = 'active'
            WHERE p.status = 'active' AND (p.approval_status = 'approved' OR p.approval_status IS NULL) AND p.category_id IS NOT NULL
              AND (p.is_customizable = 0 OR p.is_customizable IS NULL) " . (!is_vendor_module_enabled() ? " AND (p.vendor_id IS NULL OR p.vendor_id = 0) " : "") . "
              AND (p.name LIKE :query1 OR pv.sku LIKE :query2 OR c.name LIKE :query3)
            ORDER BY p.created_at DESC
            LIMIT 10
        ";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            'query1' => '%' . $query . '%',
            'query2' => '%' . $query . '%',
            'query3' => '%' . $query . '%'
        ]);
        return $stmt->fetchAll();
    }
    
    public function getAllAdmin(?int $vendorId = null, ?string $approvalStatus = null, ?int $isCustomizable = null)
    {
        $sql = "
            SELECT p.*, c.name as category_name, c.moq as category_moq, c.sgst as category_sgst, c.cgst as category_cgst, c.hsn_code as category_hsn_code, sc.name as sub_category_name,
                   sc_chart.title AS size_chart_title,
                   vp.store_name as vendor_store_name, vp.unique_vendor_id,
                   u_rev.name as reviewer_name,
                   CASE 
                       WHEN p.is_customizable = 1 THEN GREATEST(p.stock_quantity, 0)
                       ELSE COALESCE(
                           (SELECT SUM(GREATEST(pv_stock.current_stock, 0)) FROM product_variants pv_stock WHERE pv_stock.product_id = p.id),
                           GREATEST(p.stock_quantity, 0)
                       )
                   END as stock_quantity,
                   CASE 
                       WHEN p.is_customizable = 1 THEN GREATEST(p.stock_quantity, 0)
                       ELSE (SELECT SUM(GREATEST(pv_stock.current_stock, 0)) FROM product_variants pv_stock WHERE pv_stock.product_id = p.id)
                   END as current_stock_quantity,
                   CASE 
                       WHEN p.is_customizable = 1 THEN GREATEST(p.stock_quantity, 0)
                       ELSE (SELECT SUM(GREATEST(pv_stock.inventory, 0)) FROM product_variants pv_stock WHERE pv_stock.product_id = p.id)
                   END as total_stock_quantity,
                   (SELECT MIN(base_price) FROM product_variants WHERE product_id = p.id) as base_price,
                   (SELECT MIN(CASE WHEN discount_price > 0 THEN discount_price ELSE NULL END) FROM product_variants WHERE product_id = p.id) as discount_price,
                   (SELECT MIN(CASE WHEN max_amount > 0 THEN max_amount ELSE NULL END) FROM product_variants WHERE product_id = p.id) as max_amount,
                   (SELECT image_path FROM product_images WHERE product_id = p.id AND is_primary = 1 LIMIT 1) as primary_image
            FROM products p
            LEFT JOIN categories c ON p.category_id = c.id
            LEFT JOIN sub_categories sc ON p.sub_category_id = sc.id
            LEFT JOIN size_charts sc_chart ON p.size_chart_id = sc_chart.id
            LEFT JOIN vendor_profiles vp ON p.vendor_id = vp.user_id
            LEFT JOIN users u_rev ON p.approved_by = u_rev.id
            WHERE 1=1
        ";

        $params = [];
        if ($vendorId !== null) {
            $sql .= " AND p.vendor_id = :vendor_id";
            $params['vendor_id'] = $vendorId;
        }

        if ($isCustomizable !== null) {
            if ($isCustomizable === 1) {
                $sql .= " AND p.is_customizable = 1";
            } else {
                $sql .= " AND (p.is_customizable = 0 OR p.is_customizable IS NULL)";
            }
        }

        if ($approvalStatus !== null && $approvalStatus !== 'all' && $approvalStatus !== '') {
            if ($approvalStatus === 'draft') {
                $sql .= " AND p.status = 'draft' AND p.approval_status != 'rejected'";
            } elseif ($approvalStatus === 'active') {
                $sql .= " AND p.status = 'active' AND (p.approval_status = 'approved' OR p.approval_status IS NULL)";
            } else {
                $sql .= " AND p.approval_status = :approval_status";
                $params['approval_status'] = $approvalStatus;
            }
        }

        $sql .= " ORDER BY p.created_at DESC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public function generateUniqueSlug(string $nameOrSlug, ?int $excludeId = null): string
    {
        $baseSlug = preg_replace('/[^a-z0-9]+/i', '-', trim($nameOrSlug));
        $baseSlug = trim(strtolower($baseSlug), '-');
        if (empty($baseSlug)) {
            $baseSlug = 'product';
        }

        $slug = $baseSlug;
        $counter = 1;

        while (true) {
            $sql = "SELECT id FROM products WHERE slug = :slug";
            $params = ['slug' => $slug];
            if ($excludeId !== null) {
                $sql .= " AND id != :exclude_id";
                $params['exclude_id'] = $excludeId;
            }
            $stmt = $this->db->prepare($sql);
            $stmt->execute($params);
            if (!$stmt->fetch()) {
                return $slug;
            }
            $slug = $baseSlug . '-' . $counter;
            $counter++;
        }
    }

    public function create(array $data)
    {
        $slug = !empty($data['slug']) ? $data['slug'] : $data['name'];
        $uniqueSlug = $this->generateUniqueSlug($slug);

        $stmt = $this->db->prepare("
            INSERT INTO products (
                vendor_id, category_id, sub_category_id, size_chart_id, name, slug, description, how_to_use, why_choose, custom_fields,
                wholesale_price, retail_price, weight, stock_quantity, moq_override, status, 
                approval_status, rejection_reason, approved_by, approved_at,
                max_amount, sgst, cgst, total_gst, is_customizable
            )
            VALUES (
                :vendor_id, :category_id, :sub_category_id, :size_chart_id, :name, :slug, :description, :how_to_use, :why_choose, :custom_fields,
                :wholesale_price, :retail_price, :weight, :stock_quantity, :moq_override, :status, 
                :approval_status, :rejection_reason, :approved_by, :approved_at,
                :max_amount, :sgst, :cgst, :total_gst, :is_customizable
            )
        ");
        
        $sgst = isset($data['sgst']) && $data['sgst'] !== '' ? (float)$data['sgst'] : 0.00;
        $cgst = isset($data['cgst']) && $data['cgst'] !== '' ? (float)$data['cgst'] : 0.00;
        $totalGst = isset($data['total_gst']) && $data['total_gst'] !== '' ? (float)$data['total_gst'] : ($sgst + $cgst);
        $maxAmount = isset($data['max_amount']) && $data['max_amount'] !== '' ? (float)$data['max_amount'] : (float)($data['wholesale_price'] ?? 0);

        $stmt->execute([
            'vendor_id' => $data['vendor_id'] ?? null,
            'category_id' => $data['category_id'],
            'sub_category_id' => $data['sub_category_id'] ?? null,
            'size_chart_id' => !empty($data['size_chart_id']) ? (int)$data['size_chart_id'] : null,
            'name' => $data['name'],
            'slug' => $uniqueSlug,
            'description' => $data['description'] ?? null,
            'how_to_use' => $data['how_to_use'] ?? null,
            'why_choose' => $data['why_choose'] ?? null,
            'custom_fields' => $data['custom_fields'] ?? null,
            'wholesale_price' => $data['wholesale_price'],
            'retail_price' => $data['retail_price'] ?? $data['wholesale_price'],
            'weight' => $data['weight'] ?? null,
            'stock_quantity' => $data['stock_quantity'] ?? 0,
            'moq_override' => $data['moq_override'] ?? null,
            'status' => $data['status'] ?? 'draft',
            'approval_status' => $data['approval_status'] ?? 'approved',
            'rejection_reason' => $data['rejection_reason'] ?? null,
            'approved_by' => $data['approved_by'] ?? null,
            'approved_at' => $data['approved_at'] ?? null,
            'max_amount' => $maxAmount,
            'sgst' => $sgst,
            'cgst' => $cgst,
            'total_gst' => $totalGst,
            'is_customizable' => isset($data['is_customizable']) ? (int)$data['is_customizable'] : 0
        ]);
        
        return $this->db->lastInsertId();
    }

    public function findById($id)
    {
        $stmt = $this->db->prepare("
            SELECT p.*, c.name AS category_name, c.moq AS category_moq, c.sgst AS category_sgst, c.cgst AS category_cgst,
                   sc_chart.title AS size_chart_title,
                   vp.store_name AS vendor_store_name, vp.company_name AS vendor_company_name, vp.unique_vendor_id,
                   u_v.name AS vendor_name,
                   CASE 
                       WHEN p.is_customizable = 1 THEN GREATEST(p.stock_quantity, 0)
                       ELSE COALESCE(
                           (SELECT SUM(GREATEST(pv_stock.current_stock, 0)) FROM product_variants pv_stock WHERE pv_stock.product_id = p.id),
                           GREATEST(p.stock_quantity, 0)
                       )
                   END AS stock_quantity,
                   CASE 
                       WHEN p.is_customizable = 1 THEN GREATEST(p.stock_quantity, 0)
                       ELSE (SELECT SUM(GREATEST(pv_stock.current_stock, 0)) FROM product_variants pv_stock WHERE pv_stock.product_id = p.id)
                   END AS current_stock_quantity,
                   CASE 
                       WHEN p.is_customizable = 1 THEN GREATEST(p.stock_quantity, 0)
                       ELSE (SELECT SUM(GREATEST(pv_stock.inventory, 0)) FROM product_variants pv_stock WHERE pv_stock.product_id = p.id)
                   END AS total_stock_quantity
            FROM products p 
            LEFT JOIN categories c ON p.category_id = c.id
            LEFT JOIN size_charts sc_chart ON p.size_chart_id = sc_chart.id
            LEFT JOIN vendor_profiles vp ON p.vendor_id = vp.user_id
            LEFT JOIN users u_v ON p.vendor_id = u_v.id
            WHERE p.id = :id
        ");
        $stmt->execute(['id' => $id]);
        return $stmt->fetch();
    }

    public function findBySlug(string $slug)
    {
        $stmt = $this->db->prepare("
            SELECT p.*, c.name AS category_name, c.moq AS category_moq, c.sgst AS category_sgst, c.cgst AS category_cgst,
                   sc_chart.title AS size_chart_title,
                   vp.store_name AS vendor_store_name, vp.company_name AS vendor_company_name, vp.unique_vendor_id,
                   u_v.name AS vendor_name,
                   CASE 
                       WHEN p.is_customizable = 1 THEN GREATEST(p.stock_quantity, 0)
                       ELSE COALESCE(
                           (SELECT SUM(GREATEST(pv_stock.current_stock, 0)) FROM product_variants pv_stock WHERE pv_stock.product_id = p.id),
                           GREATEST(p.stock_quantity, 0)
                       )
                   END AS stock_quantity,
                   CASE 
                       WHEN p.is_customizable = 1 THEN GREATEST(p.stock_quantity, 0)
                       ELSE (SELECT SUM(GREATEST(pv_stock.current_stock, 0)) FROM product_variants pv_stock WHERE pv_stock.product_id = p.id)
                   END AS current_stock_quantity,
                   CASE 
                       WHEN p.is_customizable = 1 THEN GREATEST(p.stock_quantity, 0)
                       ELSE (SELECT SUM(GREATEST(pv_stock.inventory, 0)) FROM product_variants pv_stock WHERE pv_stock.product_id = p.id)
                   END AS total_stock_quantity
            FROM products p 
            LEFT JOIN categories c ON p.category_id = c.id
            LEFT JOIN size_charts sc_chart ON p.size_chart_id = sc_chart.id
            LEFT JOIN vendor_profiles vp ON p.vendor_id = vp.user_id
            LEFT JOIN users u_v ON p.vendor_id = u_v.id
            WHERE p.slug = :slug
        ");
        $stmt->execute(['slug' => $slug]);
        return $stmt->fetch();
    }

    public function update(int $id, array $data)
    {
        $slug = !empty($data['slug']) ? $data['slug'] : $data['name'];
        $uniqueSlug = $this->generateUniqueSlug($slug, $id);

        $sgst = isset($data['sgst']) && $data['sgst'] !== '' ? (float)$data['sgst'] : 0.00;
        $cgst = isset($data['cgst']) && $data['cgst'] !== '' ? (float)$data['cgst'] : 0.00;
        $totalGst = isset($data['total_gst']) && $data['total_gst'] !== '' ? (float)$data['total_gst'] : ($sgst + $cgst);
        $maxAmount = isset($data['max_amount']) && $data['max_amount'] !== '' ? (float)$data['max_amount'] : (float)($data['wholesale_price'] ?? 0);

        $sql = "
            UPDATE products 
            SET category_id = :category_id, 
                sub_category_id = :sub_category_id, 
                size_chart_id = :size_chart_id,
                name = :name, 
                slug = :slug, 
                description = :description, 
                how_to_use = :how_to_use,
                why_choose = :why_choose,
                custom_fields = :custom_fields,
                wholesale_price = :wholesale_price, 
                retail_price = :retail_price, 
                weight = :weight,
                stock_quantity = :stock_quantity,
                moq_override = :moq_override, 
                status = :status,
                max_amount = :max_amount,
                sgst = :sgst,
                cgst = :cgst,
                total_gst = :total_gst";

        $params = [
            'id' => $id,
            'category_id' => $data['category_id'],
            'sub_category_id' => $data['sub_category_id'] ?? null,
            'size_chart_id' => !empty($data['size_chart_id']) ? (int)$data['size_chart_id'] : null,
            'name' => $data['name'],
            'slug' => $uniqueSlug,
            'description' => $data['description'] ?? null,
            'how_to_use' => $data['how_to_use'] ?? null,
            'why_choose' => $data['why_choose'] ?? null,
            'custom_fields' => $data['custom_fields'] ?? null,
            'wholesale_price' => $data['wholesale_price'],
            'retail_price' => $data['retail_price'] ?? $data['wholesale_price'],
            'weight' => $data['weight'] ?? null,
            'stock_quantity' => $data['stock_quantity'] ?? 0,
            'moq_override' => $data['moq_override'] ?? null,
            'status' => $data['status'] ?? 'draft',
            'max_amount' => $maxAmount,
            'sgst' => $sgst,
            'cgst' => $cgst,
            'total_gst' => $totalGst
        ];

        if (array_key_exists('is_customizable', $data)) {
            $sql .= ", is_customizable = :is_customizable";
            $params['is_customizable'] = (int)$data['is_customizable'];
        }
        if (array_key_exists('vendor_id', $data)) {
            $sql .= ", vendor_id = :vendor_id";
            $params['vendor_id'] = $data['vendor_id'];
        }
        if (array_key_exists('approval_status', $data)) {
            $sql .= ", approval_status = :approval_status";
            $params['approval_status'] = $data['approval_status'];
        }
        if (array_key_exists('rejection_reason', $data)) {
            $sql .= ", rejection_reason = :rejection_reason";
            $params['rejection_reason'] = $data['rejection_reason'];
        }
        if (array_key_exists('approved_by', $data)) {
            $sql .= ", approved_by = :approved_by";
            $params['approved_by'] = $data['approved_by'];
        }
        if (array_key_exists('approved_at', $data)) {
            $sql .= ", approved_at = :approved_at";
            $params['approved_at'] = $data['approved_at'];
        }

        $sql .= " WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute($params);
    }

    public function approveProduct(int $id, ?int $adminId = null): bool
    {
        $stmt = $this->db->prepare("
            UPDATE products 
            SET approval_status = 'approved', 
                status = 'active', 
                rejection_reason = NULL, 
                approved_by = :admin_id, 
                approved_at = CURRENT_TIMESTAMP 
            WHERE id = :id
        ");
        $res = $stmt->execute([
            'id' => $id,
            'admin_id' => $adminId
        ]);
        if ($res && class_exists('\Core\Cache')) {
            \Core\Cache::deleteByPrefix('pending_prod_count_');
            \Core\Cache::delete('catalog_active');
            \Core\Cache::delete('home_active_products');
        }
        return $res;
    }

    public function rejectProduct(int $id, ?int $adminId = null, ?string $reason = null): bool
    {
        $stmt = $this->db->prepare("
            UPDATE products 
            SET approval_status = 'rejected', 
                status = 'draft', 
                rejection_reason = :reason, 
                approved_by = :admin_id, 
                approved_at = CURRENT_TIMESTAMP 
            WHERE id = :id
        ");
        $res = $stmt->execute([
            'id' => $id,
            'admin_id' => $adminId,
            'reason' => $reason
        ]);
        if ($res && class_exists('\Core\Cache')) {
            \Core\Cache::deleteByPrefix('pending_prod_count_');
            \Core\Cache::delete('catalog_active');
            \Core\Cache::delete('home_active_products');
        }
        return $res;
    }

    public function getPendingApprovalCount(?int $vendorId = null, bool $forceRefresh = false): int
    {
        $cacheKey = 'pending_prod_count_' . ($vendorId ?? 'all');
        if (!$forceRefresh && class_exists('\Core\Cache')) {
            $cached = \Core\Cache::get($cacheKey);
            if ($cached !== null) {
                return (int)$cached;
            }
        }
        if ($vendorId !== null) {
            $stmt = $this->db->prepare("SELECT COUNT(*) FROM products WHERE vendor_id = :vendor_id AND approval_status = 'pending'");
            $stmt->execute(['vendor_id' => $vendorId]);
        } else {
            $stmt = $this->db->query("SELECT COUNT(*) FROM products WHERE approval_status = 'pending'");
        }
        $count = (int)$stmt->fetchColumn();
        if (class_exists('\Core\Cache')) {
            \Core\Cache::set($cacheKey, $count, 120);
        }
        return $count;
    }

    public function delete(int $id)
    {
        $ownsTx = false;
        if (!$this->db->inTransaction()) {
            $ownsTx = true;
            $this->db->beginTransaction();
        }

        try {
            $this->db->exec("SET FOREIGN_KEY_CHECKS=0");

            // 1. Delete product-level images
            $this->db->prepare("DELETE FROM product_images WHERE product_id = :id")->execute(['id' => $id]);
            
            // 2. Delete product-level attributes
            $this->db->prepare("DELETE FROM product_attribute_values WHERE product_id = :id")->execute(['id' => $id]);
            
            // 3. Delete variant attributes, variant images, and variant cart items
            $stmtV = $this->db->prepare("SELECT id FROM product_variants WHERE product_id = :id");
            $stmtV->execute(['id' => $id]);
            $vIds = $stmtV->fetchAll(\PDO::FETCH_COLUMN);
            if (!empty($vIds)) {
                $vPlaceholders = implode(',', array_fill(0, count($vIds), '?'));
                $this->db->prepare("DELETE FROM product_variant_attributes WHERE variant_id IN ($vPlaceholders)")->execute($vIds);
                $this->db->prepare("DELETE FROM product_variant_images WHERE variant_id IN ($vPlaceholders)")->execute($vIds);
                $this->db->prepare("DELETE FROM product_images WHERE variant_id IN ($vPlaceholders)")->execute($vIds);
                $this->db->prepare("DELETE FROM cart_items WHERE variant_id IN ($vPlaceholders)")->execute($vIds);
            }
            
            // 4. Delete product variants
            $this->db->prepare("DELETE FROM product_variants WHERE product_id = :id")->execute(['id' => $id]);
            
            // 5. Clean up cart items, wishlists, reviews, volume tiers, and fabric customizations
            $this->db->prepare("DELETE FROM cart_items WHERE product_id = :id")->execute(['id' => $id]);
            $this->db->prepare("DELETE FROM wishlists WHERE product_id = :id")->execute(['id' => $id]);
            $this->db->prepare("DELETE FROM reviews WHERE product_id = :id")->execute(['id' => $id]);
            $this->db->prepare("DELETE FROM product_volume_tiers WHERE product_id = :id")->execute(['id' => $id]);

            // Clean up fabric customizations and related sub-tables if any
            $stmtFc = $this->db->prepare("SELECT id FROM fabric_customizations WHERE fabric_id = :id");
            $stmtFc->execute(['id' => $id]);
            $fcIds = $stmtFc->fetchAll(\PDO::FETCH_COLUMN);
            if (!empty($fcIds)) {
                $fcPlaceholders = implode(',', array_fill(0, count($fcIds), '?'));
                try {
                    $this->db->prepare("DELETE FROM fabric_customization_label_rel WHERE customization_id IN ($fcPlaceholders)")->execute($fcIds);
                    $this->db->prepare("DELETE FROM fabric_customization_sizes WHERE customization_id IN ($fcPlaceholders)")->execute($fcIds);
                } catch (\Exception $eIgnored) {}
                $this->db->prepare("DELETE FROM fabric_customizations WHERE id IN ($fcPlaceholders)")->execute($fcIds);
            }
            
            // 6. Delete product
            $stmt = $this->db->prepare("DELETE FROM products WHERE id = :id");
            $result = $stmt->execute(['id' => $id]);
            
            $this->db->exec("SET FOREIGN_KEY_CHECKS=1");

            if ($ownsTx) {
                $this->db->commit();
            }

            if (class_exists('\Core\Cache')) {
                \Core\Cache::delete('catalog_active');
                \Core\Cache::delete('catalog_default_products');
                \Core\Cache::delete('home_active_products');
                \Core\Cache::deleteByPrefix('product_details_bundle_');
                \Core\Cache::deleteByPrefix('rel_prods_diff_');
                \Core\Cache::deleteByPrefix('admin_dashboard_stats_');
            }
            return $result;
        } catch (\Exception $e) {
            $this->db->exec("SET FOREIGN_KEY_CHECKS=1");
            if ($ownsTx && $this->db->inTransaction()) {
                $this->db->rollBack();
            }
            throw $e;
        }
    }

    public function getAttributes(int $productId)
    {
        $stmt = $this->db->prepare("SELECT attribute_id, attribute_value_id, value_text FROM product_attribute_values WHERE product_id = :id");
        $stmt->execute(['id' => $productId]);
        $rows = $stmt->fetchAll();
        
        $attributes = [];
        foreach ($rows as $row) {
            $attrId = $row['attribute_id'];
            if (!isset($attributes[$attrId])) {
                $attributes[$attrId] = [];
            }
            $attributes[$attrId][] = [
                'attribute_value_id' => $row['attribute_value_id'],
                'value_text' => $row['value_text']
            ];
        }
        return $attributes;
    }

    public function syncAttributes(int $productId, array $parsedAttributes)
    {
        // First delete existing mappings
        $stmt = $this->db->prepare("DELETE FROM product_attribute_values WHERE product_id = :id");
        $stmt->execute(['id' => $productId]);

        if (empty($parsedAttributes)) {
            return;
        }

        $insertSql = "INSERT INTO product_attribute_values (product_id, attribute_id, attribute_value_id, value_text) VALUES (:product_id, :attribute_id, :attribute_value_id, :value_text)";
        $insertStmt = $this->db->prepare($insertSql);

        foreach ($parsedAttributes as $attr) {
            $insertStmt->execute([
                'product_id' => $productId,
                'attribute_id' => $attr['attribute_id'],
                'attribute_value_id' => $attr['attribute_value_id'] ?? null,
                'value_text' => $attr['value_text'] ?? null
            ]);
        }
    }

    public function getImages(int $productId)
    {
        $stmt = $this->db->prepare("SELECT * FROM product_images WHERE product_id = :id AND variant_id IS NULL ORDER BY is_primary DESC, sort_order ASC, id ASC");
        $stmt->execute(['id' => $productId]);
        return $stmt->fetchAll();
    }

    public function addImage(int $productId, string $imagePath, bool $isPrimary = false, ?int $variantId = null, int $sortOrder = 0)
    {
        $stmt = $this->db->prepare("INSERT INTO product_images (product_id, image_path, is_primary, variant_id, sort_order) VALUES (:product_id, :image_path, :is_primary, :variant_id, :sort_order)");
        $stmt->execute([
            'product_id' => $productId,
            'image_path' => $imagePath,
            'is_primary' => $isPrimary ? 1 : 0,
            'variant_id' => $variantId,
            'sort_order' => $sortOrder
        ]);
        return $this->db->lastInsertId();
    }

    public function deletePrimaryImage(int $productId)
    {
        $stmt = $this->db->prepare("DELETE FROM product_images WHERE product_id = :id AND is_primary = 1 AND variant_id IS NULL");
        return $stmt->execute(['id' => $productId]);
    }

    public function deleteVariantImages(int $variantId)
    {
        $stmt = $this->db->prepare("DELETE FROM product_images WHERE variant_id = :variant_id");
        return $stmt->execute(['variant_id' => $variantId]);
    }

    public function deleteImageByPath(int $productId, string $imagePath)
    {
        $stmt = $this->db->prepare("DELETE FROM product_images WHERE product_id = :product_id AND image_path = :image_path");
        return $stmt->execute(['product_id' => $productId, 'image_path' => $imagePath]);
    }

    public function getInventorySummary(?int $vendorId = null)
    {
        $sql = "
            SELECT 
                COUNT(DISTINCT p.id) as total_products,
                SUM(inv.prod_total_stock) as total_stock,
                SUM(inv.prod_current_stock) as current_stock,
                SUM(CASE WHEN inv.prod_current_stock <= inv.prod_low_alert AND inv.prod_current_stock > 0 THEN 1 ELSE 0 END) as low_stock_count,
                SUM(CASE WHEN inv.prod_current_stock <= 0 THEN 1 ELSE 0 END) as out_of_stock_count
            FROM products p
            JOIN (
                SELECT 
                    p2.id as product_id,
                    CASE WHEN p2.is_customizable = 1 THEN GREATEST(p2.stock_quantity, 0) ELSE SUM(GREATEST(pv.inventory, 0)) END as prod_total_stock,
                    CASE WHEN p2.is_customizable = 1 THEN GREATEST(p2.stock_quantity, 0) ELSE SUM(GREATEST(pv.current_stock, 0)) END as prod_current_stock,
                    MIN(COALESCE(pv.low_stock_alert, 5)) as prod_low_alert
                FROM products p2
                JOIN product_variants pv ON p2.id = pv.product_id
                GROUP BY p2.id, p2.is_customizable, p2.stock_quantity
            ) inv ON p.id = inv.product_id
            WHERE p.status != 'archived'
        ";
        
        $params = [];
        if ($vendorId !== null) {
            $sql .= " AND p.vendor_id = :vendor_id";
            $params['vendor_id'] = $vendorId;
        }

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetch();
    }

    /**
     * Fetch products/variants that are low on stock
     */
    public function getLowStockAlerts(?int $vendorId = null)
    {
        $sql = "
            SELECT 
                p.name as product_name, 
                pv.sku as sku, 
                GREATEST(pv.current_stock, 0) as remaining_stock
            FROM product_variants pv
            JOIN products p ON pv.product_id = p.id
            WHERE pv.current_stock <= COALESCE(pv.low_stock_alert, 5) 
              AND p.status != 'archived'
        ";
        
        $params = [];
        if ($vendorId !== null) {
            $sql .= " AND p.vendor_id = :vendor_id";
            $params['vendor_id'] = $vendorId;
        }

        $sql .= " ORDER BY remaining_stock ASC LIMIT 10";
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    /**
     * Get unified list of inventory
     */
    public function getInventoryList(?int $vendorId = null)
    {
        $sql = "
            SELECT 
                p.id as product_id,
                p.name as product_name,
                p.vendor_id,
                vp.store_name as vendor_store_name,
                vp.unique_vendor_id,
                c.name as category_name,
                COUNT(pv.id) as variant_count,
                CASE WHEN p.is_customizable = 1 THEN GREATEST(p.stock_quantity, 0) ELSE SUM(GREATEST(pv.inventory, 0)) END as total_stock,
                CASE WHEN p.is_customizable = 1 THEN 0 ELSE SUM(GREATEST(GREATEST(pv.inventory, 0) - GREATEST(pv.current_stock, 0), 0)) END as reserve_stock,
                CASE WHEN p.is_customizable = 1 THEN GREATEST(p.stock_quantity, 0) ELSE GREATEST(SUM(GREATEST(pv.current_stock, 0)), 0) END as current_stock,
                MIN(COALESCE(pv.low_stock_alert, 5)) as low_stock_alert,
                MIN(pv.sku) as sku,
                (SELECT image_path FROM product_images WHERE product_id = p.id ORDER BY is_primary DESC LIMIT 1) as image_path
            FROM products p
            JOIN product_variants pv ON p.id = pv.product_id
            LEFT JOIN categories c ON p.category_id = c.id
            LEFT JOIN vendor_profiles vp ON p.vendor_id = vp.user_id
            WHERE p.status != 'archived'
        ";
        
        $params = [];
        if ($vendorId !== null) {
            $sql .= " AND p.vendor_id = :vendor_id";
            $params['vendor_id'] = $vendorId;
        }

        $sql .= " GROUP BY p.id, p.name, p.vendor_id, vp.store_name, vp.unique_vendor_id, c.name, p.is_customizable, p.stock_quantity ORDER BY p.name ASC";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }
    
    /**
     * Get a specific variant's stock details
     */
    public function getVariantStockDetails($variantId)
    {
        $sql = "
            SELECT 
                pv.id, pv.product_id, pv.sku, p.name as product_name,
                GREATEST(pv.inventory, 0) as inventory,
                GREATEST(GREATEST(pv.inventory, 0) - GREATEST(pv.current_stock, 0), 0) as reserve_stock,
                GREATEST(pv.current_stock, 0) as current_stock
            FROM product_variants pv
            JOIN products p ON pv.product_id = p.id
            WHERE pv.id = :id
        ";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['id' => $variantId]);
        return $stmt->fetch();
    }

    /**
     * Update stock for a specific variant.
     * inventory = total/historical stock (always set to the new value).
     * current_stock = available/live stock shown on the storefront UI.
     *   If $newCurrentStock is provided it is set explicitly;
     *   otherwise it is adjusted by the same delta as the inventory change.
     */
    public function updateVariantStock($variantId, $newTotalStock, $newCurrentStock = null)
    {
        // 1. Get old inventory, current_stock, and customization status
        $stmt = $this->db->prepare("SELECT pv.product_id, pv.inventory, pv.current_stock, p.is_customizable FROM product_variants pv JOIN products p ON pv.product_id = p.id WHERE pv.id = :id");
        $stmt->execute(['id' => $variantId]);
        $row = $stmt->fetch();
        $productId = (int)($row['product_id'] ?? 0);
        $isCustomizable = (int)($row['is_customizable'] ?? 0);
        $oldTotal = (int)($row['inventory'] ?? 0);
        $oldCurrent = (int)($row['current_stock'] ?? 0);

        // 2. Determine the new current_stock
        if ($newCurrentStock !== null) {
            // Explicit value supplied from the form (preferred)
            $finalCurrentStock = max(0, (int)$newCurrentStock);
        } else {
            // Fallback: adjust current_stock by the same delta
            $diff = $newTotalStock - $oldTotal;
            $finalCurrentStock = max(0, $oldCurrent + $diff);
        }

        // 3. Update both inventory (history) and current_stock (live)
        $sql = "UPDATE product_variants SET inventory = :inventory, current_stock = :current_stock WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        $res = $stmt->execute([
            'inventory'     => $newTotalStock,
            'current_stock' => $finalCurrentStock,
            'id'            => $variantId
        ]);

        if ($isCustomizable && $productId > 0) {
            // Keep product stock_quantity and all sibling variants synchronized for raw fabric stock
            $stmtSyncProd = $this->db->prepare("UPDATE products SET stock_quantity = :stock WHERE id = :id");
            $stmtSyncProd->execute(['stock' => $finalCurrentStock, 'id' => $productId]);

            $stmtSyncVars = $this->db->prepare("UPDATE product_variants SET inventory = :inv, current_stock = :curr WHERE product_id = :pid");
            $stmtSyncVars->execute(['inv' => $newTotalStock, 'curr' => $finalCurrentStock, 'pid' => $productId]);
        }

        return $res;
    }
    /**
     * Get product details for the inventory view page
     */
    public function getInventoryProductViewDetails($id)
    {
        $sql = "
            SELECT p.*, c.name as category_name, c.moq as category_moq,
                   CASE WHEN p.is_customizable = 1 THEN GREATEST(p.stock_quantity, 0) ELSE (SELECT SUM(inventory) FROM product_variants WHERE product_id = p.id) END as total_stock_quantity,
                   CASE WHEN p.is_customizable = 1 THEN GREATEST(p.stock_quantity, 0) ELSE (SELECT SUM(current_stock) FROM product_variants WHERE product_id = p.id) END as current_stock_quantity,
                   CASE WHEN p.is_customizable = 1 THEN GREATEST(p.stock_quantity, 0) ELSE (SELECT SUM(current_stock) FROM product_variants WHERE product_id = p.id) END as stock_quantity,
                   (SELECT MIN(base_price) FROM product_variants WHERE product_id = p.id) as variant_base_price,
                   (SELECT MIN(CASE WHEN discount_price > 0 THEN discount_price ELSE NULL END) FROM product_variants WHERE product_id = p.id) as variant_discount_price,
                   (SELECT image_path FROM product_images WHERE product_id = p.id ORDER BY is_primary DESC LIMIT 1) as image_path
            FROM products p
            LEFT JOIN categories c ON p.category_id = c.id
            WHERE p.id = :id
        ";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['id' => $id]);
        return $stmt->fetch();
    }

    /**
     * Get all variants for a product — single optimized query using GROUP_CONCAT.
     * Eliminates the N+1 image query loop that previously ran 1 extra DB query per variant.
     */
    public function getInventoryProductVariants($id)
    {
        // Single query with GROUP_CONCAT — eliminates N+1 image queries
        // Compatible with MySQL ONLY_FULL_GROUP_BY: image_path uses a subquery, images_concat uses ANY_VALUE
        $sql = "
            SELECT pv.*,
                   pv.current_stock,
                   (SELECT image_path FROM product_images
                    WHERE variant_id = pv.id AND is_primary = 1 LIMIT 1) AS image_path,
                   (SELECT av.color_code 
                    FROM product_variant_attributes pva 
                    JOIN attribute_values av ON pva.attribute_value_id = av.id 
                    WHERE pva.variant_id = pv.id AND av.color_code IS NOT NULL AND av.color_code != '' 
                    LIMIT 1) AS color_code,
                   (SELECT av.value 
                    FROM product_variant_attributes pva 
                    JOIN attribute_values av ON pva.attribute_value_id = av.id 
                    JOIN attributes a ON av.attribute_id = a.id
                    WHERE pva.variant_id = pv.id AND a.input_type = 'colorpicker' 
                    LIMIT 1) AS color_name,
                   GROUP_CONCAT(
                       pi_all.image_path
                       ORDER BY pi_all.is_primary DESC, pi_all.id ASC
                       SEPARATOR '||'
                   ) AS images_concat
            FROM product_variants pv
            LEFT JOIN product_images pi_all ON pi_all.variant_id = pv.id
            WHERE pv.product_id = :id
            GROUP BY pv.id
            ORDER BY pv.id ASC
        ";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['id' => $id]);
        $variants = $stmt->fetchAll();

        // Explode the concatenated images string back into an array in PHP (no extra DB queries)
        foreach ($variants as &$v) {
            $v['images'] = !empty($v['images_concat'])
                ? explode('||', $v['images_concat'])
                : [];
            unset($v['images_concat']);
        }
        unset($v);

        return $variants;
    }

    /**
     * Batch fetch variants for multiple product IDs in a single query.
     * Eliminates N+1 DB queries on product listings.
     * Returns an associative array mapping productId => array of variants.
     */
    public function getInventoryVariantsForProducts(array $productIds): array
    {
        $productIds = array_values(array_filter(array_map('intval', $productIds), function($id) {
            return $id > 0;
        }));

        if (empty($productIds)) {
            return [];
        }

        $placeholders = implode(',', array_fill(0, count($productIds), '?'));
        $sql = "
            SELECT pv.*,
                   pv.current_stock,
                   (SELECT image_path FROM product_images
                    WHERE variant_id = pv.id AND is_primary = 1 LIMIT 1) AS image_path,
                   (SELECT av.color_code 
                    FROM product_variant_attributes pva 
                    JOIN attribute_values av ON pva.attribute_value_id = av.id 
                    WHERE pva.variant_id = pv.id AND av.color_code IS NOT NULL AND av.color_code != '' 
                    LIMIT 1) AS color_code,
                   (SELECT av.value 
                    FROM product_variant_attributes pva 
                    JOIN attribute_values av ON pva.attribute_value_id = av.id 
                    JOIN attributes a ON av.attribute_id = a.id
                    WHERE pva.variant_id = pv.id AND a.input_type = 'colorpicker' 
                    LIMIT 1) AS color_name,
                   GROUP_CONCAT(
                       pi_all.image_path
                       ORDER BY pi_all.is_primary DESC, pi_all.id ASC
                       SEPARATOR '||'
                   ) AS images_concat
            FROM product_variants pv
            LEFT JOIN product_images pi_all ON pi_all.variant_id = pv.id
            WHERE pv.product_id IN ($placeholders)
            GROUP BY pv.id
            ORDER BY pv.id ASC
        ";
        $stmt = $this->db->prepare($sql);
        $stmt->execute($productIds);
        $variants = $stmt->fetchAll();

        $map = [];
        foreach ($productIds as $pid) {
            $map[$pid] = [];
        }

        foreach ($variants as $v) {
            $v['images'] = !empty($v['images_concat'])
                ? explode('||', $v['images_concat'])
                : [];
            unset($v['images_concat']);
            $map[$v['product_id']][] = $v;
        }

        return $map;
    }

    /**
     * Get all products owned by a specific vendor with individual sales and revenue metrics.
     */
    public function getVendorProductsWithRevenue(int $vendorId): array
    {
        $stmtSetting = $this->db->prepare("SELECT key_value FROM system_settings WHERE key_name = 'vendor_revenue_holding_days' LIMIT 1");
        $stmtSetting->execute();
        $holdingDaysVal = $stmtSetting->fetchColumn();
        $holdingDays = ($holdingDaysVal !== false && $holdingDaysVal !== null && $holdingDaysVal !== '') ? (int)$holdingDaysVal : 7;

        $sql = "
            SELECT 
                p.id,
                p.name,
                p.base_sku,
                p.wholesale_price,
                p.retail_price,
                CASE 
                    WHEN p.is_customizable = 1 THEN GREATEST(p.stock_quantity, 0)
                    ELSE COALESCE(
                        (SELECT SUM(GREATEST(pv.current_stock, 0)) FROM product_variants pv WHERE pv.product_id = p.id),
                        GREATEST(p.stock_quantity, 0)
                    )
                END AS stock_quantity,
                p.status,
                p.approval_status,
                p.created_at,
                c.name AS category_name,
                (SELECT pi.image_path FROM product_images pi WHERE pi.product_id = p.id ORDER BY pi.is_primary DESC, pi.id ASC LIMIT 1) AS primary_image,
                COALESCE(SUM(CASE WHEN o.payment_status = 'paid' AND o.status NOT IN ('cancelled', 'refund_processing', 'refund_completed') THEN oi.quantity ELSE 0 END), 0) AS total_sold_qty,
                COALESCE(SUM(CASE WHEN o.payment_status = 'paid' AND o.status NOT IN ('cancelled', 'refund_processing', 'refund_completed') AND ({$holdingDays} <= 0 OR TIMESTAMPDIFF(DAY, o.created_at, NOW()) >= {$holdingDays}) THEN oi.total_price ELSE 0 END), 0) AS total_revenue,
                COALESCE(SUM(CASE WHEN o.payment_status = 'paid' AND o.status NOT IN ('cancelled', 'refund_processing', 'refund_completed') AND ({$holdingDays} > 0 AND TIMESTAMPDIFF(DAY, o.created_at, NOW()) < {$holdingDays}) THEN oi.total_price ELSE 0 END), 0) AS holding_revenue,
                COALESCE(SUM(CASE WHEN o.payment_status = 'paid' AND o.status NOT IN ('cancelled', 'refund_processing', 'refund_completed') THEN oi.total_price ELSE 0 END), 0) AS all_time_revenue,
                COUNT(DISTINCT CASE WHEN o.payment_status = 'paid' AND o.status NOT IN ('cancelled', 'refund_processing', 'refund_completed') THEN o.id ELSE NULL END) AS paid_orders_count
            FROM products p
            LEFT JOIN categories c ON p.category_id = c.id
            LEFT JOIN order_items oi ON oi.product_id = p.id
            LEFT JOIN orders o ON oi.order_id = o.id
            WHERE p.vendor_id = :vendor_id
            GROUP BY p.id, p.name, p.base_sku, p.wholesale_price, p.retail_price, p.stock_quantity, p.status, p.approval_status, p.created_at, c.name
            ORDER BY total_revenue DESC, total_sold_qty DESC, p.id DESC
        ";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            'vendor_id' => $vendorId
        ]);
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }
}


