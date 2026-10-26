<?php
namespace App\Models;

use Core\Model;

class Role extends Model
{
    public function getAllRoles()
    {
        $stmt = $this->db->prepare("SELECT * FROM roles ORDER BY created_at DESC");
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function findById(int $id)
    {
        $stmt = $this->db->prepare("SELECT * FROM roles WHERE id = :id");
        $stmt->execute(['id' => $id]);
        return $stmt->fetch();
    }

    public function findByName(string $name, ?int $excludeId = null)
    {
        $sql = "SELECT * FROM roles WHERE LOWER(TRIM(name)) = LOWER(TRIM(:name))";
        $params = ['name' => trim($name)];
        if ($excludeId !== null && $excludeId > 0) {
            $sql .= " AND id != :excludeId";
            $params['excludeId'] = $excludeId;
        }
        $sql .= " LIMIT 1";
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetch();
    }

    public function create(array $data)
    {
        $stmt = $this->db->prepare("
            INSERT INTO roles (name, permissions)
            VALUES (:name, :permissions)
        ");
        
        $stmt->execute([
            'name' => $data['name'],
            'permissions' => json_encode($data['permissions'] ?? [])
        ]);
        
        return $this->db->lastInsertId();
    }

    public function update(int $id, array $data)
    {
        $stmt = $this->db->prepare("
            UPDATE roles SET name = :name, permissions = :permissions WHERE id = :id
        ");
        
        return $stmt->execute([
            'name' => $data['name'],
            'permissions' => json_encode($data['permissions'] ?? []),
            'id' => $id
        ]);
    }

    public function deleteRole(int $id)
    {
        // First check if any users are assigned this role
        $stmt = $this->db->prepare("SELECT count(id) as count FROM users WHERE role_id = :id");
        $stmt->execute(['id' => $id]);
        $count = $stmt->fetch()['count'];

        if ($count > 0) {
            throw new \Exception("Cannot delete role: it is assigned to {$count} staff member(s).");
        }

        $stmt = $this->db->prepare("DELETE FROM roles WHERE id = :id");
        return $stmt->execute(['id' => $id]);
    }
}
