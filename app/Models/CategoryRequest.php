<?php
namespace App\Models;

use Core\Model;

class CategoryRequest extends Model
{
    public function createRequest(array $data): int
    {
        $name = trim($data['name'] ?? '');
        $slug = !empty($data['slug']) ? trim($data['slug']) : $this->generateSlug($name);
        
        $attributesJson = null;
        if (!empty($data['attributes_json'])) {
            $attributesJson = is_string($data['attributes_json']) ? $data['attributes_json'] : json_encode($data['attributes_json']);
        } elseif (!empty($data['proposed_attributes'])) {
            $attributesJson = $this->parseProposedAttributesToJSON($data['proposed_attributes']);
        }

        $stmt = $this->db->prepare("
            INSERT INTO category_requests (
                vendor_id, request_type, parent_category_id, name, slug, 
                hsn_code, sgst, cgst, description, attributes_json, status
            ) VALUES (
                :vendor_id, :request_type, :parent_category_id, :name, :slug, 
                :hsn_code, :sgst, :cgst, :description, :attributes_json, 'pending'
            )
        ");

        $stmt->execute([
            'vendor_id' => (int)$data['vendor_id'],
            'request_type' => in_array($data['request_type'] ?? '', ['category', 'subcategory']) ? $data['request_type'] : 'category',
            'parent_category_id' => !empty($data['parent_category_id']) ? (int)$data['parent_category_id'] : null,
            'name' => $name,
            'slug' => $slug,
            'hsn_code' => !empty($data['hsn_code']) ? trim($data['hsn_code']) : null,
            'sgst' => isset($data['sgst']) && $data['sgst'] !== '' ? (float)$data['sgst'] : 0.00,
            'cgst' => isset($data['cgst']) && $data['cgst'] !== '' ? (float)$data['cgst'] : 0.00,
            'description' => !empty($data['description']) ? trim($data['description']) : null,
            'attributes_json' => $attributesJson
        ]);

        $newId = (int)$this->db->lastInsertId();
        if (class_exists('\Core\Cache')) {
            \Core\Cache::delete('pending_cat_req_count');
        }
        return $newId;
    }

    public function getAllWithFilters(?string $status = null, ?string $search = null): array
    {
        $sql = "
            SELECT cr.*, 
                   u.name AS vendor_name, 
                   u.email AS vendor_email,
                   u.phone AS vendor_phone,
                   vp.store_name, 
                   vp.unique_vendor_id,
                   pc.name AS parent_category_name,
                   reviewer.name AS reviewer_name
            FROM category_requests cr
            LEFT JOIN users u ON cr.vendor_id = u.id
            LEFT JOIN vendor_profiles vp ON u.id = vp.user_id
            LEFT JOIN categories pc ON cr.parent_category_id = pc.id
            LEFT JOIN users reviewer ON cr.reviewed_by = reviewer.id
            WHERE 1=1
        ";
        $params = [];

        if (!empty($status)) {
            $sql .= " AND cr.status = :status";
            $params['status'] = $status;
        }

        if (!empty($search)) {
            $sql .= " AND (cr.name LIKE :search OR cr.hsn_code LIKE :search OR vp.store_name LIKE :search OR u.name LIKE :search)";
            $params['search'] = '%' . $search . '%';
        }

        $sql .= " ORDER BY cr.created_at DESC";

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    public function getByVendor(int $vendorId): array
    {
        $stmt = $this->db->prepare("
            SELECT cr.*, pc.name AS parent_category_name
            FROM category_requests cr
            LEFT JOIN categories pc ON cr.parent_category_id = pc.id
            WHERE cr.vendor_id = :vid
            ORDER BY cr.created_at DESC
        ");
        $stmt->execute(['vid' => $vendorId]);
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    public function findById(int $id): ?array
    {
        $stmt = $this->db->prepare("
            SELECT cr.*, 
                   u.name AS vendor_name, 
                   u.email AS vendor_email, 
                   vp.store_name, 
                   vp.unique_vendor_id,
                   pc.name AS parent_category_name,
                   reviewer.name AS reviewer_name
            FROM category_requests cr
            LEFT JOIN users u ON cr.vendor_id = u.id
            LEFT JOIN vendor_profiles vp ON u.id = vp.user_id
            LEFT JOIN categories pc ON cr.parent_category_id = pc.id
            LEFT JOIN users reviewer ON cr.reviewed_by = reviewer.id
            WHERE cr.id = :id
            LIMIT 1
        ");
        $stmt->execute(['id' => $id]);
        $row = $stmt->fetch(\PDO::FETCH_ASSOC);
        return $row ?: null;
    }

    public function getPendingCount(bool $forceRefresh = false): int
    {
        $cacheKey = 'pending_cat_req_count';
        if (!$forceRefresh && class_exists('\Core\Cache')) {
            $cached = \Core\Cache::get($cacheKey);
            if ($cached !== null) {
                return (int)$cached;
            }
        }
        $stmt = $this->db->query("SELECT COUNT(*) FROM category_requests WHERE status = 'pending'");
        $count = (int)$stmt->fetchColumn();
        if (class_exists('\Core\Cache')) {
            \Core\Cache::set($cacheKey, $count, 120);
        }
        return $count;
    }

    public function approve(int $id, ?int $adminUserId = null): array
    {
        $request = $this->findById($id);
        if (!$request) {
            throw new \Exception("Category request not found.");
        }

        if ($request['status'] !== 'pending') {
            throw new \Exception("This request has already been processed.");
        }

        $createdId = null;

        if ($request['request_type'] === 'category') {
            $catModel = new Category();
            $slug = $this->ensureUniqueCategorySlug($request['slug']);
            
            $createdId = $catModel->create([
                'name' => $request['name'],
                'slug' => $slug,
                'hsn_code' => $request['hsn_code'],
                'description' => $request['description'],
                'sgst' => $request['sgst'],
                'cgst' => $request['cgst'],
                'status' => 'active'
            ]);
        } else {
            // Subcategory
            if (empty($request['parent_category_id'])) {
                throw new \Exception("Cannot approve subcategory request without a valid parent category.");
            }

            $subModel = new SubCategory();
            $slug = $this->ensureUniqueSubcategorySlug($request['slug']);

            $createdId = $subModel->create([
                'category_id' => $request['parent_category_id'],
                'name' => $request['name'],
                'slug' => $slug,
                'status' => 'active'
            ]);
        }

        // Auto-provision proposed attributes if present
        $createdAttributes = [];
        if (!empty($request['attributes_json'])) {
            $createdAttributes = $this->provisionAttributesFromJSON(
                $request['attributes_json'],
                $request['request_type'],
                $createdId,
                $request['parent_category_id'] ? (int)$request['parent_category_id'] : null
            );
        }

        // Mark request as approved
        $stmt = $this->db->prepare("
            UPDATE category_requests 
            SET status = 'approved', 
                reviewed_by = :reviewer, 
                reviewed_at = CURRENT_TIMESTAMP 
            WHERE id = :id
        ");
        $stmt->execute([
            'id' => $id,
            'reviewer' => $adminUserId
        ]);

        if (class_exists('\Core\Cache')) {
            \Core\Cache::delete('pending_cat_req_count');
        }

        return [
            'success' => true,
            'request_type' => $request['request_type'],
            'created_id' => $createdId,
            'name' => $request['name'],
            'attributes_created' => $createdAttributes
        ];
    }

    public function reject(int $id, ?int $adminUserId = null, ?string $reason = null): bool
    {
        $request = $this->findById($id);
        if (!$request) {
            throw new \Exception("Category request not found.");
        }

        if ($request['status'] !== 'pending') {
            throw new \Exception("This request has already been processed.");
        }

        $stmt = $this->db->prepare("
            UPDATE category_requests 
            SET status = 'rejected', 
                rejection_reason = :reason,
                reviewed_by = :reviewer, 
                reviewed_at = CURRENT_TIMESTAMP 
            WHERE id = :id
        ");
        $res = $stmt->execute([
            'id' => $id,
            'reason' => $reason,
            'reviewer' => $adminUserId
        ]);
        if ($res && class_exists('\Core\Cache')) {
            \Core\Cache::delete('pending_cat_req_count');
        }
        return $res;
    }

    private function generateSlug(string $text): string
    {
        $slug = preg_replace('~[^\pL\d]+~u', '-', $text);
        $slug = iconv('utf-8', 'us-ascii//TRANSLIT', $slug);
        $slug = preg_replace('~[^-\w]+~', '', $slug);
        $slug = trim($slug, '-');
        $slug = preg_replace('~-+~', '-', $slug);
        $slug = strtolower($slug);
        return empty($slug) ? 'cat-' . time() : $slug;
    }

    private function ensureUniqueCategorySlug(string $slug): string
    {
        $stmt = $this->db->prepare("SELECT COUNT(*) FROM categories WHERE slug = ?");
        $stmt->execute([$slug]);
        if ($stmt->fetchColumn() > 0) {
            return $slug . '-' . substr(uniqid(), -4);
        }
        return $slug;
    }

    private function ensureUniqueSubcategorySlug(string $slug): string
    {
        $stmt = $this->db->prepare("SELECT COUNT(*) FROM sub_categories WHERE slug = ?");
        $stmt->execute([$slug]);
        if ($stmt->fetchColumn() > 0) {
            return $slug . '-' . substr(uniqid(), -4);
        }
        return $slug;
    }

    /**
     * Parses free-text or structured attribute string into clean JSON format.
     * Supports formats like:
     * "Size: 6, 7, 8, 9, 10 | Color: Black, White, Brown | Material: Leather, Synthetic"
     */
    public function parseProposedAttributesToJSON($input): ?string
    {
        if (empty($input)) return null;

        if (is_array($input)) {
            return json_encode($input);
        }

        $trimmed = trim((string)$input);
        if (empty($trimmed)) return null;

        // If already valid JSON
        if (str_starts_with($trimmed, '[') || str_starts_with($trimmed, '{')) {
            $decoded = json_decode($trimmed, true);
            if (json_last_error() === JSON_ERROR_NONE && !empty($decoded)) {
                return $trimmed;
            }
        }

        // Parse pipe / newline delimited pairs
        $lines = preg_split('/[|\n\r]+/', $trimmed);
        $attributes = [];

        foreach ($lines as $line) {
            $line = trim($line);
            if (empty($line)) continue;

            if (strpos($line, ':') !== false) {
                list($attrName, $valStr) = explode(':', $line, 2);
                $attrName = trim($attrName);
                $values = array_filter(array_map('trim', explode(',', $valStr)), fn($v) => $v !== '');
            } else {
                $attrName = $line;
                $values = [];
            }

            if (!empty($attrName)) {
                $isColor = (stripos($attrName, 'color') !== false);
                $attributes[] = [
                    'name' => $attrName,
                    'input_type' => $isColor ? 'colorpicker' : 'checkbox',
                    'values' => array_values($values),
                    'is_variant' => 1
                ];
            }
        }

        return !empty($attributes) ? json_encode($attributes) : null;
    }

    /**
     * Creates attributes and attribute_values in database when category/subcategory is approved
     */
    public function provisionAttributesFromJSON(string $jsonStr, string $requestType, int $createdEntityId, ?int $parentCategoryId = null): array
    {
        $attributes = json_decode($jsonStr, true);
        if (!is_array($attributes) || empty($attributes)) {
            return [];
        }

        $targetCategoryId = ($requestType === 'category') ? $createdEntityId : $parentCategoryId;
        $targetSubCategoryId = ($requestType === 'subcategory') ? $createdEntityId : null;

        if (!$targetCategoryId) {
            return [];
        }

        $created = [];

        foreach ($attributes as $attr) {
            $name = trim($attr['name'] ?? '');
            if (empty($name)) continue;

            $code = strtolower(preg_replace('/[^a-zA-Z0-9]+/', '_', $name));
            $code = trim($code, '_');
            if (empty($code)) $code = 'attr_' . time();

            // Check if code exists
            $stmtCode = $this->db->prepare("SELECT COUNT(*) FROM attributes WHERE attribute_code = ?");
            $stmtCode->execute([$code]);
            if ($stmtCode->fetchColumn() > 0) {
                $code .= '_' . substr(uniqid(), -4);
            }

            $inputType = $attr['input_type'] ?? (stripos($name, 'color') !== false ? 'colorpicker' : 'checkbox');
            $isVariant = isset($attr['is_variant']) ? (int)$attr['is_variant'] : 1;

            $stmtAttr = $this->db->prepare("
                INSERT INTO attributes (
                    category_id, sub_category_id, name, attribute_code, input_type,
                    is_required, is_searchable, is_filterable, is_variant, show_on_product_form,
                    is_visible_on_website, status
                ) VALUES (
                    :cat_id, :sub_id, :name, :code, :type,
                    1, 1, 1, :is_variant, 1, 1, 'active'
                )
            ");

            $stmtAttr->execute([
                'cat_id' => $targetCategoryId,
                'sub_id' => $targetSubCategoryId,
                'name' => $name,
                'code' => $code,
                'type' => $inputType,
                'is_variant' => $isVariant
            ]);

            $attrId = (int)$this->db->lastInsertId();

            // Insert values
            $values = $attr['values'] ?? [];
            if (is_string($values)) {
                $values = array_filter(array_map('trim', explode(',', $values)), fn($v) => $v !== '');
            }

            $colorCodes = [
                'red' => '#ef4444', 'blue' => '#3b82f6', 'green' => '#22c55e', 
                'yellow' => '#eab308', 'orange' => '#f97316', 'purple' => '#a855f7',
                'pink' => '#ec4899', 'black' => '#000000', 'white' => '#ffffff',
                'gray' => '#6b7280', 'grey' => '#6b7280', 'brown' => '#D9A05B', 
                'tan' => '#D2B48C', 'nude' => '#E5D3B3', 'navy' => '#000080'
            ];

            if (!empty($values) && $attrId > 0) {
                $stmtVal = $this->db->prepare("INSERT INTO attribute_values (attribute_id, value, color_code) VALUES (?, ?, ?)");
                foreach ($values as $val) {
                    $val = trim((string)$val);
                    if ($val === '') continue;

                    $colorCode = null;
                    if ($inputType === 'colorpicker' || stripos($name, 'color') !== false) {
                        $lowerVal = strtolower($val);
                        $colorCode = $colorCodes[$lowerVal] ?? null;
                    }

                    $stmtVal->execute([$attrId, $val, $colorCode]);
                }
            }

            $created[] = [
                'id' => $attrId,
                'name' => $name,
                'code' => $code,
                'values_count' => count($values)
            ];
        }

        return $created;
    }
}
