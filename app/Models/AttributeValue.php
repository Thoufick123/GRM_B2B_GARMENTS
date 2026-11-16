<?php
namespace App\Models;

use Core\Model;

class AttributeValue extends Model
{
    public function getByAttributeId($attributeId)
    {
        $stmt = $this->db->prepare("SELECT * FROM attribute_values WHERE attribute_id = :attr_id ORDER BY value ASC");
        $stmt->execute(['attr_id' => $attributeId]);
        return $stmt->fetchAll();
    }

    public function getAllGroupedByAttribute()
    {
        $sql = "
            SELECT av.id as value_id, av.value, av.color_code, a.id as attribute_id, a.name as attribute_name
            FROM attribute_values av
            JOIN attributes a ON av.attribute_id = a.id
            ORDER BY a.name ASC, av.value ASC
        ";
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        $results = $stmt->fetchAll();
        
        $grouped = [];
        foreach($results as $row) {
            $grouped[$row['attribute_name']][] = [
                'id' => $row['value_id'],
                'value' => $row['value'],
                'color_code' => $row['color_code'] ?? null
            ];
        }
        return $grouped;
    }

    public function create(array $data)
    {
        $stmt = $this->db->prepare("INSERT INTO attribute_values (attribute_id, value, color_code) VALUES (:attribute_id, :value, :color_code)");
        $stmt->execute([
            'attribute_id' => $data['attribute_id'],
            'value' => $data['value'],
            'color_code' => !empty($data['color_code']) ? $data['color_code'] : null
        ]);
        return $this->db->lastInsertId();
    }

    public function update(int $id, array $data)
    {
        $stmt = $this->db->prepare("UPDATE attribute_values SET value = :value, color_code = :color_code WHERE id = :id");
        return $stmt->execute([
            'id' => $id,
            'value' => $data['value'],
            'color_code' => !empty($data['color_code']) ? $data['color_code'] : null
        ]);
    }

    public function getValuesForAttributes(array $attributeIds)
    {
        if (empty($attributeIds)) return [];
        $inQuery = implode(',', array_fill(0, count($attributeIds), '?'));
        
        $stmt = $this->db->prepare("SELECT * FROM attribute_values WHERE attribute_id IN ($inQuery) ORDER BY value ASC");
        $stmt->execute($attributeIds);
        
        $results = $stmt->fetchAll();
        $grouped = [];
        foreach ($results as $row) {
            $grouped[$row['attribute_id']][] = $row;
        }
        return $grouped;
    }

    public function delete(int $id)
    {
        $this->db->exec("SET FOREIGN_KEY_CHECKS=0");
        $this->db->prepare("DELETE FROM product_variant_attributes WHERE attribute_value_id = :id")->execute(['id' => $id]);
        $this->db->prepare("DELETE FROM product_attribute_values WHERE attribute_value_id = :id")->execute(['id' => $id]);
        $stmt = $this->db->prepare("DELETE FROM attribute_values WHERE id = :id");
        $res = $stmt->execute(['id' => $id]);
        $this->db->exec("SET FOREIGN_KEY_CHECKS=1");
        return $res;
    }
}
