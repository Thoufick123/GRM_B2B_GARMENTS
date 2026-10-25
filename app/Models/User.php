<?php
namespace App\Models;

use Core\Model;

class User extends Model
{
    public function findByEmail(string $email)
    {
        $stmt = $this->db->prepare("SELECT * FROM users WHERE email = :email");
        $stmt->execute(['email' => $email]);
        return $stmt->fetch();
    }
    
    public function findById(int $id)
    {
        $stmt = $this->db->prepare("SELECT * FROM users WHERE id = :id");
        $stmt->execute(['id' => $id]);
        return $stmt->fetch();
    }
    
    public function findByPhone(string $phone)
    {
        $stmt = $this->db->prepare("SELECT * FROM users WHERE phone = :phone");
        $stmt->execute(['phone' => $phone]);
        return $stmt->fetch();
    }

    public function create(array $data)
    {
        $stmt = $this->db->prepare("
            INSERT INTO users (unique_buyer_id, name, email, phone, password_hash, role, role_id, status, permissions)
            VALUES (:unique_buyer_id, :name, :email, :phone, :password_hash, :role, :role_id, :status, :permissions)
        ");
        
        $stmt->execute([
            'unique_buyer_id' => $data['unique_buyer_id'] ?? null,
            'name' => $data['name'],
            'email' => $data['email'],
            'phone' => $data['phone'],
            'password_hash' => password_hash($data['password'], PASSWORD_BCRYPT),
            'role' => $data['role'] ?? 'buyer',
            'role_id' => !empty($data['role_id']) ? $data['role_id'] : null,
            'status' => $data['status'] ?? 'pending',
            'permissions' => isset($data['permissions']) ? json_encode($data['permissions']) : null
        ]);
        
        return $this->db->lastInsertId();
    }

    public function updateStatus(int $userId, string $status)
    {
        $stmt = $this->db->prepare("UPDATE users SET status = :status WHERE id = :id");
        return $stmt->execute(['status' => $status, 'id' => $userId]);
    }

    public function getPendingBuyers()
    {
        $sql = "
            SELECT u.id, u.unique_buyer_id, u.name, u.email, u.phone, u.created_at, 
                   bp.business_name, bp.shop_location, bp.gst_number, bp.pan_number, bp.no_gst_reason,
                   rr.status as request_status, rr.gst_flag
            FROM users u
            JOIN business_profiles bp ON u.id = bp.user_id
            JOIN registration_requests rr ON u.id = rr.user_id
            WHERE u.status = 'pending' AND u.role = 'buyer'
            ORDER BY u.created_at DESC
        ";
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function getRejectedBuyers()
    {
        $sql = "
            SELECT u.id, u.unique_buyer_id, u.name, u.email, u.phone, u.created_at, 
                   bp.business_name, bp.shop_location, bp.gst_number, bp.pan_number, bp.no_gst_reason,
                   rr.status as request_status, rr.gst_flag, rr.rejection_reason
            FROM users u
            JOIN business_profiles bp ON u.id = bp.user_id
            LEFT JOIN registration_requests rr ON u.id = rr.user_id
            WHERE u.status = 'rejected' AND u.role = 'buyer'
            ORDER BY u.created_at DESC
        ";
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function getActiveBuyers()
    {
        $sql = "
            SELECT u.id, u.unique_buyer_id, u.name, u.email, u.phone, u.created_at, u.status,
                   bp.business_name, bp.shop_location, bp.gst_number, bp.pan_number,
                   (SELECT COUNT(*) FROM orders o WHERE o.user_id = u.id AND o.payment_status = 'paid') as total_orders,
                   (SELECT SUM(COALESCE(o.grand_total, o.total_amount)) FROM orders o WHERE o.user_id = u.id AND o.status = 'delivered' AND o.payment_status = 'paid') as delivered_spend
            FROM users u
            LEFT JOIN business_profiles bp ON u.id = bp.user_id
            WHERE u.status IN ('active', 'approved', 'blocked') AND u.role = 'buyer'
            ORDER BY u.created_at DESC
        ";
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function getBuyerStats()
    {
        $stats = [];
        
        $stmt = $this->db->query("SELECT COUNT(*) FROM users WHERE role = 'buyer'");
        $stats['total_buyers'] = $stmt->fetchColumn();
        
        $stmt = $this->db->query("SELECT COUNT(*) FROM users WHERE role = 'buyer' AND status IN ('active', 'approved')");
        $stats['active_buyers'] = $stmt->fetchColumn();
        
        $stmt = $this->db->query("SELECT COUNT(*) FROM orders WHERE payment_status = 'paid'");
        $stats['total_orders'] = $stmt->fetchColumn();
        
        $stmt = $this->db->query("
            SELECT SUM(COALESCE(grand_total, total_amount)) 
            FROM orders 
            WHERE status = 'delivered' AND payment_status = 'paid'
        ");
        $stats['delivered_revenue'] = (float)$stmt->fetchColumn();

        $stmt = $this->db->query("SELECT COUNT(*) FROM users WHERE role = 'buyer' AND status = 'rejected'");
        $stats['rejected_buyers'] = (int)$stmt->fetchColumn();
        
        return $stats;
    }

    public function getBuyerDetails(int $id)
    {
        try {
            $sql = "
                SELECT u.id, u.unique_buyer_id, u.name, u.email, u.phone, u.created_at, u.status, u.role, u.role_id, u.permissions,
                       bp.business_name, bp.shop_location, bp.gst_number, bp.gst_document, bp.pan_number, bp.pan_document, bp.no_gst_reason, bp.instagram_link,
                       bp.verification_data, bp.tax_verified,
                       rr.status as request_status, rr.gst_flag
                FROM users u
                LEFT JOIN business_profiles bp ON u.id = bp.user_id
                LEFT JOIN registration_requests rr ON u.id = rr.user_id
                WHERE u.id = :id
            ";
            $stmt = $this->db->prepare($sql);
            $stmt->execute(['id' => $id]);
            $user = $stmt->fetch();
        } catch (\PDOException $e) {
            $sql = "
                SELECT u.id, u.unique_buyer_id, u.name, u.email, u.phone, u.created_at, u.status, u.role, u.role_id, u.permissions,
                       bp.business_name, bp.shop_location, bp.gst_number, bp.gst_document, bp.pan_number, bp.pan_document, bp.no_gst_reason, bp.instagram_link,
                       NULL as verification_data, 0 as tax_verified,
                       rr.status as request_status, rr.gst_flag
                FROM users u
                LEFT JOIN business_profiles bp ON u.id = bp.user_id
                LEFT JOIN registration_requests rr ON u.id = rr.user_id
                WHERE u.id = :id
            ";
            $stmt = $this->db->prepare($sql);
            $stmt->execute(['id' => $id]);
            $user = $stmt->fetch();
        }

        if ($user) {
            $stmtMedia = $this->db->prepare("SELECT * FROM media WHERE user_id = :id");
            $stmtMedia->execute(['id' => $id]);
            $user['media'] = $stmtMedia->fetchAll();
        }

        return $user;
    }

    public function getPendingVendors()
    {
        $sql = "
            SELECT u.id, u.unique_vendor_id, u.name, u.email, u.phone, u.created_at, u.status,
                   vp.store_name, vp.company_name, vp.vendor_type, vp.gst_number, vp.pan_number,
                   vp.city, vp.state, vp.pincode,
                   vr.status as request_status
            FROM users u
            JOIN vendor_profiles vp ON u.id = vp.user_id
            LEFT JOIN vendor_registration_requests vr ON u.id = vr.user_id
            WHERE u.role = 'vendor' AND u.status = 'pending'
            ORDER BY u.created_at DESC
        ";
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function getActiveVendors()
    {
        $sql = "
            SELECT u.id, u.unique_vendor_id, u.name, u.email, u.phone, u.created_at, u.status,
                   vp.store_name, vp.company_name, vp.vendor_type, vp.gst_number, vp.pan_number,
                   vp.city, vp.state, vp.bank_name, vp.account_number, vp.commission_rate
            FROM users u
            JOIN vendor_profiles vp ON u.id = vp.user_id
            WHERE u.role = 'vendor' AND u.status IN ('active', 'approved', 'blocked')
            ORDER BY u.created_at DESC
        ";
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function getVendorDetails(int $id)
    {
        $sql = "
            SELECT u.id, u.unique_vendor_id, u.name, u.email, u.phone, u.created_at, u.status, u.role,
                   vp.store_name, vp.company_name, vp.vendor_type, vp.gst_number, vp.pan_number, vp.no_gst_reason,
                   vp.address, vp.city, vp.state, vp.pincode, vp.bank_name, vp.account_number, vp.ifsc_code,
                   vp.account_holder_name, vp.description, vp.shop_images, vp.gst_document,
                   vr.status as request_status, vr.rejection_reason
            FROM users u
            LEFT JOIN vendor_profiles vp ON u.id = vp.user_id
            LEFT JOIN vendor_registration_requests vr ON u.id = vr.user_id
            WHERE u.id = :id AND u.role = 'vendor'
        ";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['id' => $id]);
        $vendor = $stmt->fetch();

        if ($vendor) {
            $stmtMedia = $this->db->prepare("SELECT * FROM media WHERE user_id = :id");
            $stmtMedia->execute(['id' => $id]);
            $vendor['media'] = $stmtMedia->fetchAll();
        }

        return $vendor;
    }

    public function getVendorStats()
    {
        $stats = [];
        $stmt = $this->db->query("SELECT COUNT(*) FROM users WHERE role = 'vendor'");
        $stats['total_vendors'] = (int)$stmt->fetchColumn();

        $stmt = $this->db->query("SELECT COUNT(*) FROM users WHERE role = 'vendor' AND status IN ('active', 'approved')");
        $stats['active_vendors'] = (int)$stmt->fetchColumn();

        $stmt = $this->db->query("SELECT COUNT(*) FROM users WHERE role = 'vendor' AND status = 'pending'");
        $stats['pending_vendors'] = (int)$stmt->fetchColumn();

        return $stats;
    }

    public function getStaff()
    {
        $stmt = $this->db->prepare("SELECT u.*, r.name as custom_role_name FROM users u LEFT JOIN roles r ON u.role_id = r.id WHERE u.role IN ('manager', 'staff') ORDER BY u.created_at DESC");
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function updateStaff(int $id, array $data)
    {
        $sql = "UPDATE users SET name = :name, email = :email, phone = :phone, role = :role, role_id = :role_id, status = :status, permissions = :permissions";
        $params = [
            'name' => $data['name'],
            'email' => $data['email'],
            'phone' => $data['phone'],
            'role' => $data['role'],
            'role_id' => !empty($data['role_id']) ? $data['role_id'] : null,
            'status' => $data['status'],
            'permissions' => isset($data['permissions']) ? json_encode($data['permissions']) : null,
            'id' => $id
        ];

        if (!empty($data['password'])) {
            $sql .= ", password_hash = :password_hash";
            $params['password_hash'] = password_hash($data['password'], PASSWORD_BCRYPT);
        }

        $sql .= " WHERE id = :id";
        
        $stmt = $this->db->prepare($sql);
        return $stmt->execute($params);
    }

    public function deleteUser(int $id)
    {
        $stmt = $this->db->prepare("DELETE FROM users WHERE id = :id");
        return $stmt->execute(['id' => $id]);
    }
}
