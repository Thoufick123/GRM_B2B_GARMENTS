<?php
namespace App\Models;

use Core\Model;

class Attribute extends Model
{
    public function getAll(?int $isCustomizable = null)
    {
        $sql = "
            SELECT a.*, c.name as category_name, sc.name as sub_category_name 
            FROM attributes a
            LEFT JOIN categories c ON a.category_id = c.id
            LEFT JOIN sub_categories sc ON a.sub_category_id = sc.id
        ";
        if ($isCustomizable !== null) {
            if ($isCustomizable === 1) {
                $sql .= " WHERE (c.is_customizable = 1 OR sc.is_customizable = 1)";
            } else {
                $sql .= " WHERE (c.is_customizable = 0 OR c.is_customizable IS NULL) AND (sc.is_customizable = 0 OR sc.is_customizable IS NULL)";
            }
        }
        $sql .= " ORDER BY a.display_order ASC, a.name ASC";

        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function findById($id)
    {
        $stmt = $this->db->prepare("SELECT * FROM attributes WHERE id = :id");
        $stmt->execute(['id' => $id]);
        return $stmt->fetch();
    }

    public function create(array $data)
    {
        $sql = "INSERT INTO attributes (
                    category_id, sub_category_id, name, attribute_code, input_type, description, 
                    display_order, status, is_required, is_searchable, is_filterable, is_variant, 
                    show_on_product_form, is_visible_on_website
                ) VALUES (
                    :category_id, :sub_category_id, :name, :attribute_code, :input_type, :description, 
                    :display_order, :status, :is_required, :is_searchable, :is_filterable, :is_variant, 
                    :show_on_product_form, :is_visible_on_website
                )";
                
        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            'category_id' => !empty($data['category_id']) ? $data['category_id'] : null,
            'sub_category_id' => !empty($data['sub_category_id']) ? $data['sub_category_id'] : null,
            'name' => $data['name'],
            'attribute_code' => $data['attribute_code'],
            'input_type' => $data['input_type'] ?? 'textbox',
            'description' => $data['description'] ?? null,
            'display_order' => (int)($data['display_order'] ?? 0),
            'status' => $data['status'] ?? 'active',
            'is_required' => (int)($data['is_required'] ?? 0),
            'is_searchable' => (int)($data['is_searchable'] ?? 0),
            'is_filterable' => (int)($data['is_filterable'] ?? 0),
            'is_variant' => (int)($data['is_variant'] ?? 0),
            'show_on_product_form' => (int)($data['show_on_product_form'] ?? 1),
            'is_visible_on_website' => (int)($data['is_visible_on_website'] ?? 1)
        ]);
        
        return $this->db->lastInsertId();
    }

    public function update(int $id, array $data)
    {
        $sql = "UPDATE attributes SET 
                    category_id = :category_id, 
                    sub_category_id = :sub_category_id, 
                    name = :name, 
                    attribute_code = :attribute_code, 
                    input_type = :input_type, 
                    description = :description, 
                    display_order = :display_order, 
                    status = :status, 
                    is_required = :is_required, 
                    is_searchable = :is_searchable, 
                    is_filterable = :is_filterable, 
                    is_variant = :is_variant, 
                    show_on_product_form = :show_on_product_form, 
                    is_visible_on_website = :is_visible_on_website
                WHERE id = :id";
                
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            'id' => $id,
            'category_id' => !empty($data['category_id']) ? $data['category_id'] : null,
            'sub_category_id' => !empty($data['sub_category_id']) ? $data['sub_category_id'] : null,
            'name' => $data['name'],
            'attribute_code' => $data['attribute_code'],
            'input_type' => $data['input_type'] ?? 'textbox',
            'description' => $data['description'] ?? null,
            'display_order' => (int)($data['display_order'] ?? 0),
            'status' => $data['status'] ?? 'active',
            'is_required' => (int)($data['is_required'] ?? 0),
            'is_searchable' => (int)($data['is_searchable'] ?? 0),
            'is_filterable' => (int)($data['is_filterable'] ?? 0),
            'is_variant' => (int)($data['is_variant'] ?? 0),
            'show_on_product_form' => (int)($data['show_on_product_form'] ?? 1),
            'is_visible_on_website' => (int)($data['is_visible_on_website'] ?? 1)
        ]);
    }

    public function getFilterableBySubCategory($subCategoryId)
    {
        $id = (int)$subCategoryId;
        $stmt = $this->db->prepare("
            SELECT DISTINCT a.* 
            FROM attributes a
            WHERE a.status = 'active' AND (
                EXISTS (
                    SELECT 1 FROM product_variant_attributes pva 
                    JOIN product_variants pv ON pv.id = pva.variant_id 
                    JOIN products p ON p.id = pv.product_id
                    WHERE pva.attribute_id = a.id AND p.sub_category_id = :sub_cat_id1 AND p.status = 'active'
                )
                OR EXISTS (
                    SELECT 1 FROM product_attribute_values pav
                    JOIN products p ON p.id = pav.product_id
                    WHERE pav.attribute_id = a.id AND p.sub_category_id = :sub_cat_id2 AND p.status = 'active'
                )
            )
            ORDER BY a.display_order ASC, a.name ASC
        ");
        $stmt->execute(['sub_cat_id1' => $id, 'sub_cat_id2' => $id]);
        return $stmt->fetchAll();
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

            // 1. Dynamically unassign linked products by removing attribute values & variant links
            $this->db->prepare("DELETE FROM product_variant_attributes WHERE attribute_id = ?")->execute([$id]);
            $this->db->prepare("DELETE FROM product_attribute_values WHERE attribute_id = ?")->execute([$id]);

            // 2. Delete associated attribute values
            $this->db->prepare("DELETE FROM attribute_values WHERE attribute_id = ?")->execute([$id]);

            // 3. Delete the attribute record
            $stmt = $this->db->prepare("DELETE FROM attributes WHERE id = :id");
            $result = $stmt->execute(['id' => $id]);

            $this->db->exec("SET FOREIGN_KEY_CHECKS=1");

            if ($ownsTx) {
                $this->db->commit();
            }

            if (class_exists('\Core\Cache')) {
                \Core\Cache::delete('catalog_active');
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
}
