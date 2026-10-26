<?php
namespace App\Models;

use Core\Model;

class Category extends Model
{
    public function getAllActive($search = null, ?int $isCustomizable = null)
    {
        $cacheKey = ($search === null) ? ('cat_active_' . ($isCustomizable ?? 'null')) : null;
        if ($cacheKey && class_exists('\Core\Cache')) {
            $cached = \Core\Cache::get($cacheKey);
            if ($cached !== null) {
                return $cached;
            }
        }

        $sql = "SELECT * FROM categories WHERE status = 'active'";
        $params = [];
        
        if ($isCustomizable !== null) {
            if ($isCustomizable === 1) {
                $sql .= " AND is_customizable = 1";
            } else {
                $sql .= " AND (is_customizable = 0 OR is_customizable IS NULL)";
            }
        }

        if ($search) {
            $sql .= " AND name LIKE :search";
            $params['search'] = '%' . $search . '%';
        }
        
        $sql .= " ORDER BY name ASC";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        $results = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        // Deduplicate by category name to ensure each unique category is only shown once
        $uniqueCategories = [];
        $seenNames = [];
        foreach ($results as $cat) {
            $normName = strtolower(trim($cat['name']));
            if (!isset($seenNames[$normName])) {
                $seenNames[$normName] = true;
                $uniqueCategories[] = $cat;
            }
        }

        if ($cacheKey && class_exists('\Core\Cache')) {
            \Core\Cache::set($cacheKey, $uniqueCategories, 3600);
        }

        return $uniqueCategories;
    }
    
    public function getAll(?int $isCustomizable = null)
    {
        $cacheKey = 'cat_all_' . ($isCustomizable ?? 'null');
        if (class_exists('\Core\Cache')) {
            $cached = \Core\Cache::get($cacheKey);
            if ($cached !== null) {
                return $cached;
            }
        }

        $sql = "SELECT c.*, COUNT(p.id) as product_count 
                FROM categories c 
                LEFT JOIN products p ON p.category_id = c.id";
        $conditions = [];
        if ($isCustomizable !== null) {
            if ($isCustomizable === 1) {
                $conditions[] = "c.is_customizable = 1";
            } else {
                $conditions[] = "(c.is_customizable = 0 OR c.is_customizable IS NULL)";
            }
        }
        if (!empty($conditions)) {
            $sql .= " WHERE " . implode(' AND ', $conditions);
        }
        $sql .= " GROUP BY c.id ORDER BY c.name ASC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        $results = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        // Deduplicate by category name to ensure each unique category is only shown once
        $uniqueCategories = [];
        $seenNames = [];
        foreach ($results as $cat) {
            $normName = strtolower(trim($cat['name']));
            if (!isset($seenNames[$normName])) {
                $seenNames[$normName] = true;
                $uniqueCategories[] = $cat;
            }
        }

        if (class_exists('\Core\Cache')) {
            \Core\Cache::set($cacheKey, $uniqueCategories, 3600);
        }

        return $uniqueCategories;
    }

    public function findByName(string $name, ?int $isCustomizable = null, ?int $excludeId = null)
    {
        $sql = "SELECT * FROM categories WHERE LOWER(TRIM(name)) = LOWER(TRIM(:name))";
        $params = ['name' => trim($name)];
        if ($isCustomizable !== null) {
            if ($isCustomizable === 1) {
                $sql .= " AND is_customizable = 1";
            } else {
                $sql .= " AND (is_customizable = 0 OR is_customizable IS NULL)";
            }
        }
        if ($excludeId !== null) {
            $sql .= " AND id != :exclude_id";
            $params['exclude_id'] = $excludeId;
        }
        $sql .= " LIMIT 1";
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetch(\PDO::FETCH_ASSOC);
    }

    public function generateUniqueSlug(string $nameOrSlug, ?int $excludeId = null): string
    {
        $baseSlug = preg_replace('/[^a-z0-9]+/i', '-', trim($nameOrSlug));
        $baseSlug = trim(strtolower($baseSlug), '-');
        if (empty($baseSlug)) {
            $baseSlug = 'category';
        }

        $slug = $baseSlug;
        $counter = 1;

        while (true) {
            $sql = "SELECT id FROM categories WHERE slug = :slug";
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
            INSERT INTO categories (name, slug, hsn_code, description, image_path, min_order_value, min_cart_value, sgst, cgst, status, is_customizable)
            VALUES (:name, :slug, :hsn_code, :description, :image_path, :min_order_value, :min_cart_value, :sgst, :cgst, :status, :is_customizable)
        ");
        
        $stmt->execute([
            'name' => $data['name'],
            'slug' => $uniqueSlug,
            'hsn_code' => !empty($data['hsn_code']) ? trim($data['hsn_code']) : null,
            'description' => $data['description'] ?? null,
            'image_path' => $data['image_path'] ?? null,
            'min_order_value' => !empty($data['min_order_value']) ? (float)$data['min_order_value'] : null,
            'min_cart_value' => !empty($data['min_cart_value']) ? (float)$data['min_cart_value'] : null,
            'sgst' => isset($data['sgst']) && $data['sgst'] !== '' ? (float)$data['sgst'] : 0.00,
            'cgst' => isset($data['cgst']) && $data['cgst'] !== '' ? (float)$data['cgst'] : 0.00,
            'status' => $data['status'] ?? 'active',
            'is_customizable' => !empty($data['is_customizable']) ? 1 : 0
        ]);
        
        $insertedId = $this->db->lastInsertId();
        $this->invalidateCategoryCaches();
        return $insertedId;
    }

    public function findById($id)
    {
        $cacheKey = 'cat_by_id_' . (int)$id;
        if (class_exists('\Core\Cache')) {
            $cached = \Core\Cache::get($cacheKey);
            if ($cached !== null) {
                return $cached;
            }
        }
        $stmt = $this->db->prepare("SELECT * FROM categories WHERE id = :id");
        $stmt->execute(['id' => $id]);
        $res = $stmt->fetch();
        if (class_exists('\Core\Cache')) {
            \Core\Cache::set($cacheKey, $res, 3600);
        }
        return $res;
    }

    public function getById($id)
    {
        return $this->findById($id);
    }

    public function update(int $id, array $data)
    {
        $slug = !empty($data['slug']) ? $data['slug'] : $data['name'];
        $uniqueSlug = $this->generateUniqueSlug($slug, $id);

        $stmt = $this->db->prepare("
            UPDATE categories 
            SET name = :name, 
                slug = :slug, 
                hsn_code = :hsn_code,
                description = :description, 
                image_path = :image_path,
                min_order_value = :min_order_value,
                min_cart_value = :min_cart_value,
                sgst = :sgst,
                cgst = :cgst,
                status = :status,
                is_customizable = :is_customizable
            WHERE id = :id
        ");
        
        $stmt->execute([
            'id' => $id,
            'name' => $data['name'],
            'slug' => $uniqueSlug,
            'hsn_code' => !empty($data['hsn_code']) ? trim($data['hsn_code']) : null,
            'description' => $data['description'] ?? null,
            'image_path' => $data['image_path'] ?? null,
            'min_order_value' => !empty($data['min_order_value']) ? (float)$data['min_order_value'] : null,
            'min_cart_value' => !empty($data['min_cart_value']) ? (float)$data['min_cart_value'] : null,
            'sgst' => isset($data['sgst']) && $data['sgst'] !== '' ? (float)$data['sgst'] : 0.00,
            'cgst' => isset($data['cgst']) && $data['cgst'] !== '' ? (float)$data['cgst'] : 0.00,
            'status' => $data['status'] ?? 'active',
            'is_customizable' => !empty($data['is_customizable']) ? 1 : 0
        ]);
        $this->invalidateCategoryCaches();
    }

    public function getProductCount(int $id): int
    {
        $stmt = $this->db->prepare("SELECT COUNT(*) FROM products WHERE category_id = :id");
        $stmt->execute(['id' => $id]);
        return (int)$stmt->fetchColumn();
    }

    public function getSubCategoryCount(int $id): int
    {
        $stmt = $this->db->prepare("SELECT COUNT(*) FROM sub_categories WHERE category_id = :id");
        $stmt->execute(['id' => $id]);
        return (int)$stmt->fetchColumn();
    }

    public function getAttributeCount(int $id): int
    {
        $stmt = $this->db->prepare("SELECT COUNT(*) FROM attributes WHERE category_id = :id");
        $stmt->execute(['id' => $id]);
        return (int)$stmt->fetchColumn();
    }

    public function getCounts(int $id): array
    {
        return [
            'product_count' => $this->getProductCount($id),
            'subcategory_count' => $this->getSubCategoryCount($id),
            'attribute_count' => $this->getAttributeCount($id)
        ];
    }

    public function delete(int $id)
    {
        // Ensure products.category_id allows NULL if it was previously NOT NULL
        try {
            $col = $this->db->query("SHOW COLUMNS FROM `products` LIKE 'category_id'")->fetch(\PDO::FETCH_ASSOC);
            if ($col && isset($col['Null']) && strtoupper($col['Null']) === 'NO') {
                $this->db->exec("ALTER TABLE `products` MODIFY `category_id` BIGINT NULL");
            }
        } catch (\Throwable $t) {
            // Ignore if already nullable or without DDL privileges
        }

        $ownsTx = false;
        if (!$this->db->inTransaction()) {
            $ownsTx = true;
            $this->db->beginTransaction();
        }

        try {
            $this->db->exec("SET FOREIGN_KEY_CHECKS=0");

            // 1. Fetch all subcategories of this category to delete them and their attributes
            $stmtSubIds = $this->db->prepare("SELECT id FROM sub_categories WHERE category_id = :id");
            $stmtSubIds->execute(['id' => $id]);
            $subIds = $stmtSubIds->fetchAll(\PDO::FETCH_COLUMN);

            // 2. Find all attributes linked directly to this category or its subcategories
            $attrIds = [];
            $stmtAttr = $this->db->prepare("SELECT id FROM attributes WHERE category_id = :id");
            $stmtAttr->execute(['id' => $id]);
            $directAttrIds = $stmtAttr->fetchAll(\PDO::FETCH_COLUMN);
            if (!empty($directAttrIds)) {
                $attrIds = array_merge($attrIds, $directAttrIds);
            }

            if (!empty($subIds)) {
                $subPlaceholders = implode(',', array_fill(0, count($subIds), '?'));
                $stmtSubAttr = $this->db->prepare("SELECT id FROM attributes WHERE sub_category_id IN ($subPlaceholders)");
                $stmtSubAttr->execute($subIds);
                $subAttrIds = $stmtSubAttr->fetchAll(\PDO::FETCH_COLUMN);
                if (!empty($subAttrIds)) {
                    $attrIds = array_merge($attrIds, $subAttrIds);
                }
            }

            // 3. Delete attributes, values, and variant attribute links (do NOT leave them unassigned)
            $attrIds = array_unique(array_filter(array_map('intval', $attrIds)));
            if (!empty($attrIds)) {
                $attrPlaceholders = implode(',', array_fill(0, count($attrIds), '?'));
                $this->db->prepare("DELETE FROM product_variant_attributes WHERE attribute_id IN ($attrPlaceholders)")->execute($attrIds);
                $this->db->prepare("DELETE FROM product_attribute_values WHERE attribute_id IN ($attrPlaceholders)")->execute($attrIds);
                $this->db->prepare("DELETE FROM attribute_values WHERE attribute_id IN ($attrPlaceholders)")->execute($attrIds);
                $this->db->prepare("DELETE FROM attributes WHERE id IN ($attrPlaceholders)")->execute($attrIds);
            }

            // 4. Delete all subcategories belonging to this category (do NOT leave them unassigned)
            $this->db->prepare("DELETE FROM sub_categories WHERE category_id = :id")->execute(['id' => $id]);

            // 5. Unassign products belonging to this category or its subcategories, and set status to 'draft'
            // This strictly prevents unassigned products from showing in the Storefront UI
            $this->db->prepare("UPDATE products SET category_id = NULL, sub_category_id = NULL, status = 'draft' WHERE category_id = :id")->execute(['id' => $id]);
            if (!empty($subIds)) {
                $subPlaceholders = implode(',', array_fill(0, count($subIds), '?'));
                $this->db->prepare("UPDATE products SET sub_category_id = NULL WHERE sub_category_id IN ($subPlaceholders)")->execute($subIds);
            }

            // 6. Clean up fabric customizations, order rules, size charts, category requests
            $this->db->prepare("DELETE FROM fabric_customizations WHERE category_id = :id")->execute(['id' => $id]);
            if (!empty($subIds)) {
                $subPlaceholders = implode(',', array_fill(0, count($subIds), '?'));
                $this->db->prepare("DELETE FROM fabric_customizations WHERE sub_category_id IN ($subPlaceholders)")->execute($subIds);
            }
            $this->db->prepare("DELETE FROM order_rules WHERE category_id = :id")->execute(['id' => $id]);
            $this->db->prepare("UPDATE size_charts SET category_id = NULL WHERE category_id = :id")->execute(['id' => $id]);
            $this->db->prepare("UPDATE category_requests SET parent_category_id = NULL WHERE parent_category_id = :id")->execute(['id' => $id]);

            // 7. Delete the category record
            $stmtDel = $this->db->prepare("DELETE FROM categories WHERE id = :id");
            $result = $stmtDel->execute(['id' => $id]);

            $this->db->exec("SET FOREIGN_KEY_CHECKS=1");

            if ($ownsTx) {
                $this->db->commit();
            }

            $this->invalidateCategoryCaches();
            if (class_exists('\Core\Cache')) {
                \Core\Cache::delete('subcat_all_null');
                \Core\Cache::delete('subcat_all_0');
                \Core\Cache::delete('subcat_all_1');
                \Core\Cache::delete('catalog_default_products');
                \Core\Cache::delete('home_active_products');
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

    private function invalidateCategoryCaches()
    {
        if (class_exists('\Core\Cache')) {
            \Core\Cache::delete('cat_active_null');
            \Core\Cache::delete('cat_active_0');
            \Core\Cache::delete('cat_active_1');
            \Core\Cache::delete('cat_all_null');
            \Core\Cache::delete('cat_all_0');
            \Core\Cache::delete('cat_all_1');
            \Core\Cache::delete('catalog_all_categories_list');
            \Core\Cache::delete('layout_mega_categories_data');
            \Core\Cache::delete('catalog_active');
            \Core\Cache::deleteByPrefix('nav_drop_v2_');
            \Core\Cache::delete('cms_layout_home');
        }
    }
}
