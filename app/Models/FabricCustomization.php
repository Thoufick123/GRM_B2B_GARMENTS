<?php

namespace App\Models;

use Core\Database;
use PDO;

class FabricCustomization
{
    private $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    /**
     * Get all fabric customization rules with product & category information
     */
    public function getAllWithProductInfo($filters = [])
    {
        $sql = "SELECT fc.*, 
                       p.name as fabric_name, 
                       COALESCE(
                           NULLIF(TRIM(p.base_sku), ''), 
                           (SELECT pv.sku FROM product_variants pv WHERE pv.product_id = p.id AND pv.sku IS NOT NULL AND TRIM(pv.sku) != '' ORDER BY pv.id ASC LIMIT 1),
                           CONCAT('FAB-', p.id)
                       ) as fabric_sku, 
                       COALESCE(
                           (SELECT MIN(CASE WHEN pv.discount_price > 0 THEN pv.discount_price ELSE pv.base_price END) FROM product_variants pv WHERE pv.product_id = p.id),
                           p.wholesale_price
                       ) as fabric_price,
                       COALESCE(
                           (SELECT MIN(pv.base_price) FROM product_variants pv WHERE pv.product_id = p.id),
                           p.retail_price,
                           p.wholesale_price
                       ) as fabric_base_price,
                       (SELECT MIN(CASE WHEN pv.discount_price > 0 THEN pv.discount_price ELSE NULL END) FROM product_variants pv WHERE pv.product_id = p.id) as fabric_discount_price,
                       p.stock_quantity as fabric_stock,
                       c.name as category_name,
                       sc.name as subcategory_name,
                       (SELECT COUNT(*) FROM fabric_customization_sizes fcs WHERE fcs.customization_id = fc.id AND fcs.enabled = 1) as enabled_sizes_count,
                       (SELECT MIN(fabric_consumption) FROM fabric_customization_sizes fcs WHERE fcs.customization_id = fc.id AND fcs.enabled = 1) as min_consumption,
                       (SELECT MAX(fabric_consumption) FROM fabric_customization_sizes fcs WHERE fcs.customization_id = fc.id AND fcs.enabled = 1) as max_consumption
                FROM fabric_customizations fc
                JOIN products p ON fc.fabric_id = p.id
                LEFT JOIN categories c ON fc.category_id = c.id
                LEFT JOIN sub_categories sc ON fc.sub_category_id = sc.id
                WHERE 1=1";

        $params = [];

        if (!empty($filters['status'])) {
            $sql .= " AND fc.status = ?";
            $params[] = $filters['status'];
        }

        if (!empty($filters['garment_type'])) {
            $sql .= " AND fc.garment_type = ?";
            $params[] = $filters['garment_type'];
        }

        if (!empty($filters['search'])) {
            $sql .= " AND (p.name LIKE ? OR p.base_sku LIKE ? OR fc.program_name LIKE ? OR fc.garment_type LIKE ?)";
            $searchTerm = '%' . trim($filters['search']) . '%';
            $params[] = $searchTerm;
            $params[] = $searchTerm;
            $params[] = $searchTerm;
            $params[] = $searchTerm;
        }

        $sql .= " ORDER BY fc.id DESC";

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        $programs = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Attach sizes and labels to each
        foreach ($programs as &$program) {
            $program['sizes'] = $this->getSizesByCustomizationId($program['id']);
            $program['labels'] = $this->getLabelsByCustomizationId($program['id']);
        }

        return $programs;
    }

    /**
     * Get a single fabric customization rule by ID
     */
    public function getById($id)
    {
        $stmt = $this->db->prepare("
            SELECT fc.*, 
                   p.name as fabric_name, 
                   COALESCE(
                       NULLIF(TRIM(p.base_sku), ''), 
                       (SELECT pv.sku FROM product_variants pv WHERE pv.product_id = p.id AND pv.sku IS NOT NULL AND TRIM(pv.sku) != '' ORDER BY pv.id ASC LIMIT 1),
                       CONCAT('FAB-', p.id)
                   ) as fabric_sku, 
                   COALESCE(
                       (SELECT MIN(CASE WHEN pv.discount_price > 0 THEN pv.discount_price ELSE pv.base_price END) FROM product_variants pv WHERE pv.product_id = p.id),
                       p.wholesale_price
                   ) as fabric_price,
                   COALESCE(
                       (SELECT MIN(pv.base_price) FROM product_variants pv WHERE pv.product_id = p.id),
                       p.retail_price,
                       p.wholesale_price
                   ) as fabric_base_price,
                   (SELECT MIN(CASE WHEN pv.discount_price > 0 THEN pv.discount_price ELSE NULL END) FROM product_variants pv WHERE pv.product_id = p.id) as fabric_discount_price,
                   p.stock_quantity as fabric_stock,
                   p.description as fabric_description,
                   c.name as category_name,
                   sc.name as subcategory_name
            FROM fabric_customizations fc
            JOIN products p ON fc.fabric_id = p.id
            LEFT JOIN categories c ON fc.category_id = c.id
            LEFT JOIN sub_categories sc ON fc.sub_category_id = sc.id
            WHERE fc.id = ?
        ");
        $stmt->execute([(int)$id]);
        $customization = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($customization) {
            $customization['sizes'] = $this->getSizesByCustomizationId($customization['id']);
            $customization['labels'] = $this->getLabelsByCustomizationId($customization['id']);
        }

        return $customization ?: null;
    }

    /**
     * Get active customization rule for a specific fabric product ID
     */
    public function getByFabricId($fabricId)
    {
        $cacheKey = "fabric_customization_fid_v3_" . (int)$fabricId;
        if (class_exists('\Core\Cache')) {
            $cached = \Core\Cache::get($cacheKey);
            if ($cached !== null) {
                return $cached;
            }
        }

        $stmt = $this->db->prepare("
            SELECT fc.*, 
                   p.name as fabric_name, 
                   COALESCE(
                       NULLIF(TRIM(p.base_sku), ''), 
                       (SELECT pv.sku FROM product_variants pv WHERE pv.product_id = p.id AND pv.sku IS NOT NULL AND TRIM(pv.sku) != '' ORDER BY pv.id ASC LIMIT 1),
                       CONCAT('FAB-', p.id)
                   ) as fabric_sku, 
                   (SELECT image_path FROM product_images WHERE product_id = p.id AND is_primary = 1 LIMIT 1) as primary_image,
                   COALESCE(
                       (SELECT MIN(CASE WHEN pv.discount_price > 0 THEN pv.discount_price ELSE pv.base_price END) FROM product_variants pv WHERE pv.product_id = p.id),
                       p.wholesale_price
                   ) as fabric_price,
                   COALESCE(
                       (SELECT MIN(pv.base_price) FROM product_variants pv WHERE pv.product_id = p.id),
                       p.retail_price,
                       p.wholesale_price
                   ) as fabric_base_price,
                   (SELECT MIN(CASE WHEN pv.discount_price > 0 THEN pv.discount_price ELSE NULL END) FROM product_variants pv WHERE pv.product_id = p.id) as fabric_discount_price,
                   p.retail_price as fabric_retail_price,
                   p.stock_quantity as fabric_stock,
                   p.description as fabric_description,
                   p.weight as fabric_weight,
                   p.weight_unit as fabric_weight_unit,
                   c.name as category_name,
                   sc.name as subcategory_name
            FROM fabric_customizations fc
            JOIN products p ON fc.fabric_id = p.id
            LEFT JOIN categories c ON fc.category_id = c.id
            LEFT JOIN sub_categories sc ON fc.sub_category_id = sc.id
            WHERE fc.fabric_id = ? AND fc.status = 'active' AND fc.customization_enabled = 1
            LIMIT 1
        ");
        $stmt->execute([(int)$fabricId]);
        $customization = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($customization) {
            $customization['sizes'] = $this->getSizesByCustomizationId($customization['id']);
            $customization['labels'] = $this->getLabelsByCustomizationId($customization['id']);
            $customization['images'] = $this->getProductImages($fabricId);
            if (empty($customization['primary_image']) && !empty($customization['images'][0])) {
                $customization['primary_image'] = $customization['images'][0];
            }

            if (class_exists('\Core\Cache')) {
                \Core\Cache::set($cacheKey, $customization, 900);
            }
        }

        return $customization ?: null;
    }

    /**
     * Get all customizable fabrics for the storefront listing
     * Only returns fabrics that have an active customization rule and available inventory
     */
    public function getCustomizableFabrics($filters = [])
    {
        $isDefaultQuery = empty($filters) || (
            empty($filters['category_id']) && 
            empty($filters['garment_type']) && 
            empty($filters['search']) && 
            ($filters['sort'] ?? 'newest') === 'newest'
        );
        $cacheKey = $isDefaultQuery ? 'customizable_fabrics_default_v4' : null;

        if ($cacheKey && class_exists('\Core\Cache')) {
            $cached = \Core\Cache::get($cacheKey);
            if ($cached !== null) {
                return $cached;
            }
        }

        $sql = "SELECT fc.*, 
                       p.id as product_id,
                       p.name as fabric_name, 
                       COALESCE(
                           NULLIF(TRIM(p.base_sku), ''), 
                           (SELECT pv.sku FROM product_variants pv WHERE pv.product_id = p.id AND pv.sku IS NOT NULL AND TRIM(pv.sku) != '' ORDER BY pv.id ASC LIMIT 1),
                           CONCAT('FAB-', p.id)
                       ) as fabric_sku, 
                       p.slug as fabric_slug, 
                       COALESCE(
                           (SELECT MIN(CASE WHEN pv.discount_price > 0 THEN pv.discount_price ELSE pv.base_price END) FROM product_variants pv WHERE pv.product_id = p.id),
                           p.wholesale_price
                       ) as fabric_price,
                       COALESCE(
                           (SELECT MIN(pv.base_price) FROM product_variants pv WHERE pv.product_id = p.id),
                           p.retail_price,
                           p.wholesale_price
                       ) as fabric_base_price,
                       (SELECT MIN(CASE WHEN pv.discount_price > 0 THEN pv.discount_price ELSE NULL END) FROM product_variants pv WHERE pv.product_id = p.id) as fabric_discount_price,
                       p.retail_price as fabric_retail_price,
                       p.stock_quantity as fabric_stock,
                       p.description as fabric_description,
                       c.name as category_name,
                       c.slug as category_slug,
                       sc.name as subcategory_name,
                       (SELECT pi.image_path FROM product_images pi WHERE pi.product_id = p.id ORDER BY pi.is_primary DESC, pi.sort_order ASC, pi.id ASC LIMIT 1) as primary_image
                FROM fabric_customizations fc
                JOIN products p ON fc.fabric_id = p.id
                LEFT JOIN categories c ON fc.category_id = c.id
                LEFT JOIN sub_categories sc ON fc.sub_category_id = sc.id
                WHERE fc.status = 'active' 
                  AND fc.customization_enabled = 1 
                  AND p.status = 'active'
                  AND p.is_customizable = 1
                  AND COALESCE(p.stock_quantity, 0) >= 0";

        $params = [];

        if (!empty($filters['category_id'])) {
            $sql .= " AND (fc.category_id = ? OR p.category_id = ?)";
            $params[] = (int)$filters['category_id'];
            $params[] = (int)$filters['category_id'];
        }

        if (!empty($filters['garment_type'])) {
            $sql .= " AND fc.garment_type = ?";
            $params[] = $filters['garment_type'];
        }

        if (!empty($filters['search'])) {
            $sql .= " AND (p.name LIKE ? OR p.base_sku LIKE ? OR fc.program_name LIKE ? OR fc.garment_type LIKE ?)";
            $searchTerm = '%' . trim($filters['search']) . '%';
            $params[] = $searchTerm;
            $params[] = $searchTerm;
            $params[] = $searchTerm;
            $params[] = $searchTerm;
        }

        $sort = $filters['sort'] ?? 'newest';
        switch ($sort) {
            case 'price_low':
                $sql .= " ORDER BY fabric_price ASC";
                break;
            case 'price_high':
                $sql .= " ORDER BY fabric_price DESC";
                break;
            case 'stock':
                $sql .= " ORDER BY p.stock_quantity DESC";
                break;
            case 'newest':
            default:
                $sql .= " ORDER BY fc.id DESC";
                break;
        }

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        $fabrics = $stmt->fetchAll(PDO::FETCH_ASSOC);
        // Batch-load sizes, labels, and images in single queries to eliminate N+1 latency
        if (!empty($fabrics)) {
            $custIds = array_column($fabrics, 'id');
            $prodIds = array_column($fabrics, 'fabric_id');

            // 1. Batch fetch sizes
            $sizesMap = [];
            if (!empty($custIds)) {
                $cPlaceholders = implode(',', array_fill(0, count($custIds), '?'));
                $sStmt = $this->db->prepare("SELECT * FROM fabric_customization_sizes WHERE customization_id IN ($cPlaceholders) AND enabled = 1 ORDER BY sort_order ASC, id ASC");
                $sStmt->execute($custIds);
                while ($row = $sStmt->fetch(PDO::FETCH_ASSOC)) {
                    $sizesMap[$row['customization_id']][] = $row;
                }
            }

            // 2. Batch fetch labels
            $labelsMap = [];
            if (!empty($custIds)) {
                $cPlaceholders = implode(',', array_fill(0, count($custIds), '?'));
                $lStmt = $this->db->prepare("
                    SELECT fclr.customization_id, cl.* 
                    FROM customization_labels cl
                    JOIN fabric_customization_label_rel fclr ON cl.id = fclr.label_id
                    WHERE fclr.customization_id IN ($cPlaceholders) AND cl.status = 'active'
                    ORDER BY cl.group_name ASC, cl.sort_order ASC, cl.id ASC
                ");
                $lStmt->execute($custIds);
                while ($row = $lStmt->fetch(PDO::FETCH_ASSOC)) {
                    $labelsMap[$row['customization_id']][] = $row;
                }
            }

            // 3. Batch fetch images
            $imagesMap = [];
            if (!empty($prodIds)) {
                $pPlaceholders = implode(',', array_fill(0, count($prodIds), '?'));
                $iStmt = $this->db->prepare("SELECT product_id, image_path, is_primary FROM product_images WHERE product_id IN ($pPlaceholders) ORDER BY is_primary DESC, id ASC");
                $iStmt->execute($prodIds);
                while ($row = $iStmt->fetch(PDO::FETCH_ASSOC)) {
                    $imagesMap[$row['product_id']][] = $row['image_path'];
                }
            }

            foreach ($fabrics as &$fabric) {
                $fabric['sizes'] = $sizesMap[$fabric['id']] ?? [];
                $fabric['labels'] = $labelsMap[$fabric['id']] ?? [];
                $fabric['images'] = $imagesMap[$fabric['fabric_id']] ?? [];
                if (empty($fabric['primary_image'])) {
                    $fabric['primary_image'] = !empty($fabric['images'][0]) ? $fabric['images'][0] : '/assets/images/fabric-placeholder.png';
                }
            }
        }

        if ($cacheKey && class_exists('\Core\Cache')) {
            \Core\Cache::set($cacheKey, $fabrics, 1800); // 30 minutes cache
        }

        return $fabrics;
    }

    /**
     * Get sizes configured for a customization rule
     */
    public function getSizesByCustomizationId($customizationId, $onlyEnabled = true)
    {
        $sql = "SELECT fcs.*, pv.sku as variant_sku 
                FROM fabric_customization_sizes fcs
                LEFT JOIN fabric_customizations fc ON fcs.customization_id = fc.id
                LEFT JOIN product_variants pv ON pv.product_id = fc.fabric_id AND (pv.name = fcs.size_name OR pv.id = fcs.size_id)
                WHERE fcs.customization_id = ?";
        if ($onlyEnabled) {
            $sql .= " AND fcs.enabled = 1";
        }
        $sql .= " ORDER BY fcs.sort_order ASC, fcs.id ASC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([(int)$customizationId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Get labels/templates assigned to a customization rule
     */
    public function getLabelsByCustomizationId($customizationId)
    {
        $stmt = $this->db->prepare("
            SELECT cl.* 
            FROM customization_labels cl
            JOIN fabric_customization_label_rel fclr ON cl.id = fclr.label_id
            WHERE fclr.customization_id = ? AND cl.status = 'active'
            ORDER BY cl.group_name ASC, cl.sort_order ASC, cl.id ASC
        ");
        $stmt->execute([(int)$customizationId]);
        $labels = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Fallback: If no specific labels assigned to this rule, return all active customization_labels
        if (empty($labels)) {
            $stmtAll = $this->db->query("
                SELECT * FROM customization_labels 
                WHERE status = 'active' 
                ORDER BY group_name ASC, sort_order ASC, id ASC
            ");
            if ($stmtAll) {
                $labels = $stmtAll->fetchAll(PDO::FETCH_ASSOC);
            }
        }

        return $labels;
    }

    /**
     * Sync assigned labels for a customization rule
     */
    public function syncCustomizationLabels($customizationId, array $labelIds)
    {
        $customizationId = (int)$customizationId;
        if (!$customizationId) return;

        $delStmt = $this->db->prepare("DELETE FROM fabric_customization_label_rel WHERE customization_id = ?");
        $delStmt->execute([$customizationId]);

        if (!empty($labelIds)) {
            $insStmt = $this->db->prepare("
                INSERT IGNORE INTO fabric_customization_label_rel (customization_id, label_id) 
                VALUES (?, ?)
            ");
            foreach ($labelIds as $lid) {
                $lid = (int)$lid;
                if ($lid > 0) {
                    $insStmt->execute([$customizationId, $lid]);
                }
            }
        }
    }

    /**
     * Get all images for a product
     */
    public function getProductImages($productId)
    {
        $stmt = $this->db->prepare("
            SELECT image_path, is_primary FROM product_images 
            WHERE product_id = ? 
            ORDER BY is_primary DESC, sort_order ASC, id ASC
        ");
        $stmt->execute([(int)$productId]);
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
        return array_column($rows, 'image_path');
    }

    /**
     * Get products available for fabric rules dropdown (excluding fabrics already assigned unless editing)
     */
    public function getAvailableFabricsForDropdown($excludeCustomizationId = null)
    {
        $sql = "SELECT p.id, p.name, p.base_sku, 
                       COALESCE(
                           (SELECT MIN(CASE WHEN pv.discount_price > 0 THEN pv.discount_price ELSE pv.base_price END) FROM product_variants pv WHERE pv.product_id = p.id),
                           p.wholesale_price
                       ) as wholesale_price, 
                       p.stock_quantity, p.category_id, p.sub_category_id 
                FROM products p 
                WHERE p.status = 'active' AND p.is_customizable = 1";
        
        $params = [];
        if ($excludeCustomizationId !== null) {
            $sql .= " AND (p.id NOT IN (SELECT fabric_id FROM fabric_customizations WHERE id != ?) OR p.id = (SELECT fabric_id FROM fabric_customizations WHERE id = ?))";
            $params[] = (int)$excludeCustomizationId;
            $params[] = (int)$excludeCustomizationId;
        } else {
            $sql .= " AND p.id NOT IN (SELECT fabric_id FROM fabric_customizations)";
        }

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Create a new fabric rule and its size consumption rules + assigned style labels
     */
    public function create($data, $sizes = [], $labelIds = [])
    {
        $inTx = $this->db->inTransaction();
        if (!$inTx) {
            $this->db->beginTransaction();
        }
        try {
            $stmt = $this->db->prepare("
                INSERT INTO fabric_customizations 
                (fabric_id, program_name, garment_type, category_id, sub_category_id, feeding_type, minimum_pieces, required_pieces, maximum_pieces, wastage_percentage, without_cover_discount_percentage, delivery_days, delivery_base_pieces, unit, customization_enabled, status)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
            ");
            
            $reqPieces = isset($data['required_pieces']) && (int)$data['required_pieces'] > 0 ? (int)$data['required_pieces'] : ($data['minimum_pieces'] ?? 10);

            $stmt->execute([
                $data['fabric_id'],
                $data['program_name'],
                $data['garment_type'],
                $data['category_id'] ?? null,
                $data['sub_category_id'] ?? null,
                $data['feeding_type'] ?? 'Non-Feeding',
                $reqPieces,
                $reqPieces,
                $data['maximum_pieces'] ?? 100,
                $data['wastage_percentage'] ?? 0.00,
                $data['without_cover_discount_percentage'] ?? 0.00,
                $data['delivery_days'] ?? 7,
                $data['delivery_base_pieces'] ?? 100,
                $data['unit'] ?? 'Meter',
                isset($data['customization_enabled']) ? (int)$data['customization_enabled'] : 1,
                $data['status'] ?? 'active'
            ]);

            $customizationId = (int)$this->db->lastInsertId();

            // Auto-sync variants from product_variants of fabric_id if not explicitly provided
            if (empty($sizes) && !empty($data['fabric_id'])) {
                $stmtPv = $this->db->prepare("
                    SELECT id, name FROM product_variants 
                    WHERE product_id = ? AND status = 'active' 
                    ORDER BY id ASC
                ");
                $stmtPv->execute([(int)$data['fabric_id']]);
                $pvList = $stmtPv->fetchAll(PDO::FETCH_ASSOC);
                foreach ($pvList as $pv) {
                    $sizes[] = [
                        'size_id'            => $pv['id'],
                        'size_name'          => trim($pv['name']),
                        'fabric_consumption' => 1.00,
                        'base_quantity'      => 2,
                        'max_quantity'       => 4,
                        'unit'               => $data['unit'] ?? 'Meter',
                        'enabled'            => 1
                    ];
                }
            }

            // Insert sizes
            if (!empty($sizes)) {
                $sizeStmt = $this->db->prepare("
                    INSERT INTO fabric_customization_sizes 
                    (customization_id, size_id, size_name, fabric_consumption, base_quantity, max_quantity, unit, enabled, sort_order)
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
                ");

                $sort = 1;
                foreach ($sizes as $sz) {
                    if (empty($sz['size_name'])) {
                        continue;
                    }
                    $consumption = isset($sz['fabric_consumption']) && (float)$sz['fabric_consumption'] > 0 ? (float)$sz['fabric_consumption'] : 1.00;
                    $baseQty = isset($sz['base_quantity']) && (int)$sz['base_quantity'] >= 0 ? (int)$sz['base_quantity'] : 2;
                    $maxQty = isset($sz['max_quantity']) && (int)$sz['max_quantity'] > 0 ? (int)$sz['max_quantity'] : 4;

                    $sizeStmt->execute([
                        $customizationId,
                        $sz['size_id'] ?? null,
                        trim($sz['size_name']),
                        $consumption,
                        $baseQty,
                        $maxQty,
                        $sz['unit'] ?? $data['unit'] ?? 'Meter',
                        isset($sz['enabled']) ? (int)$sz['enabled'] : 1,
                        $sz['sort_order'] ?? $sort++
                    ]);
                }
            }

            // Sync labels
            $this->syncCustomizationLabels($customizationId, $labelIds);

            if (!$inTx) {
                $this->db->commit();
            }
            if (class_exists('\Core\Cache')) {
                \Core\Cache::clear();
            }
            return $customizationId;
        } catch (\Exception $e) {
            if (!$inTx && $this->db->inTransaction()) {
                $this->db->rollBack();
            }
            throw $e;
        }
    }

    /**
     * Update an existing fabric rule and replace/sync its size consumption rules + labels
     */
    public function update($id, $data, $sizes = [], $labelIds = [])
    {
        $inTx = $this->db->inTransaction();
        if (!$inTx) {
            $this->db->beginTransaction();
        }
        try {
            $stmt = $this->db->prepare("
                UPDATE fabric_customizations 
                SET fabric_id = ?, 
                    program_name = ?, 
                    garment_type = ?, 
                    category_id = ?, 
                    sub_category_id = ?, 
                    feeding_type = ?, 
                    minimum_pieces = ?, 
                    required_pieces = ?,
                    maximum_pieces = ?, 
                    wastage_percentage = ?, 
                    without_cover_discount_percentage = ?,
                    delivery_days = ?,
                    delivery_base_pieces = ?,
                    unit = ?, 
                    customization_enabled = ?, 
                    status = ?
                WHERE id = ?
            ");

            $reqPieces = isset($data['required_pieces']) && (int)$data['required_pieces'] > 0 ? (int)$data['required_pieces'] : ($data['minimum_pieces'] ?? 10);

            $stmt->execute([
                $data['fabric_id'],
                $data['program_name'],
                $data['garment_type'],
                $data['category_id'] ?? null,
                $data['sub_category_id'] ?? null,
                $data['feeding_type'] ?? 'Non-Feeding',
                $reqPieces,
                $reqPieces,
                $data['maximum_pieces'] ?? 100,
                $data['wastage_percentage'] ?? 0.00,
                $data['without_cover_discount_percentage'] ?? 0.00,
                $data['delivery_days'] ?? 7,
                $data['delivery_base_pieces'] ?? 100,
                $data['unit'] ?? 'Meter',
                isset($data['customization_enabled']) ? (int)$data['customization_enabled'] : 1,
                $data['status'] ?? 'active',
                (int)$id
            ]);

            // Only update fabric_customization_sizes if sizes array is explicitly provided
            if (!empty($sizes)) {
                $delStmt = $this->db->prepare("DELETE FROM fabric_customization_sizes WHERE customization_id = ?");
                $delStmt->execute([(int)$id]);

                $sizeStmt = $this->db->prepare("
                    INSERT INTO fabric_customization_sizes 
                    (customization_id, size_id, size_name, fabric_consumption, base_quantity, max_quantity, unit, enabled, sort_order)
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
                ");

                $sort = 1;
                foreach ($sizes as $sz) {
                    if (empty($sz['size_name'])) {
                        continue;
                    }
                    $consumption = isset($sz['fabric_consumption']) && (float)$sz['fabric_consumption'] > 0 ? (float)$sz['fabric_consumption'] : 1.00;
                    $baseQty = isset($sz['base_quantity']) && (int)$sz['base_quantity'] >= 0 ? (int)$sz['base_quantity'] : 2;
                    $maxQty = isset($sz['max_quantity']) && (int)$sz['max_quantity'] > 0 ? (int)$sz['max_quantity'] : 4;

                    $sizeStmt->execute([
                        (int)$id,
                        $sz['size_id'] ?? null,
                        trim($sz['size_name']),
                        $consumption,
                        $baseQty,
                        $maxQty,
                        $sz['unit'] ?? $data['unit'] ?? 'Meter',
                        isset($sz['enabled']) ? (int)$sz['enabled'] : 1,
                        $sz['sort_order'] ?? $sort++
                    ]);
                }
            }

            // Sync labels if explicitly provided
            if (!empty($labelIds)) {
                $this->syncCustomizationLabels((int)$id, $labelIds);
            }

            if (!$inTx) {
                $this->db->commit();
            }
            if (class_exists('\Core\Cache')) {
                \Core\Cache::clear();
            }
            return true;
        } catch (\Exception $e) {
            if (!$inTx && $this->db->inTransaction()) {
                $this->db->rollBack();
            }
            throw $e;
        }
    }

    /**
     * Delete fabric rule
     */
    public function delete($id)
    {
        $inTx = $this->db->inTransaction();
        if (!$inTx) {
            $this->db->beginTransaction();
        }
        try {
            $delLabels = $this->db->prepare("DELETE FROM fabric_customization_label_rel WHERE customization_id = ?");
            $delLabels->execute([(int)$id]);

            $delSizes = $this->db->prepare("DELETE FROM fabric_customization_sizes WHERE customization_id = ?");
            $delSizes->execute([(int)$id]);

            $delRule = $this->db->prepare("DELETE FROM fabric_customizations WHERE id = ?");
            $delRule->execute([(int)$id]);

            if (!$inTx) {
                $this->db->commit();
            }
            if (class_exists('\Core\Cache')) {
                \Core\Cache::delete('fabric_customization_fid_' . (int)$id);
                \Core\Cache::delete('customizable_fabrics_default_v2');
            }
            return true;
        } catch (\Exception $e) {
            if (!$inTx && $this->db->inTransaction()) {
                $this->db->rollBack();
            }
            throw $e;
        }
    }

    /**
     * Toggle status active/inactive
     */
    public function toggleStatus($id)
    {
        $stmt = $this->db->prepare("
            UPDATE fabric_customizations 
            SET status = IF(status = 'active', 'inactive', 'active') 
            WHERE id = ?
        ");
        return $stmt->execute([(int)$id]);
    }
}
