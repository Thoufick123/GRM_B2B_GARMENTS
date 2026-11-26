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

    /**
     * Find registered customer/user by phone number with flexible prefix handling (+91, 91, 10-digit)
     */
    public function findCustomerByPhone(string $phone)
    {
        $clean = preg_replace('/[^0-9]/', '', $phone);
        if (strlen($clean) === 12 && str_starts_with($clean, '91')) {
            $last10 = substr($clean, 2);
        } elseif (strlen($clean) === 11 && str_starts_with($clean, '0')) {
            $last10 = substr($clean, 1);
        } else {
            $last10 = substr($clean, -10);
        }

        if (empty($last10) || strlen($last10) < 10) {
            return false;
        }

        $stmt = $this->db->prepare("
            SELECT * FROM users 
            WHERE (
                phone = :exact 
                OR phone = :last10 
                OR phone = :plus91 
                OR phone = :only91
                OR RIGHT(REPLACE(REPLACE(REPLACE(phone, ' ', ''), '-', ''), '+', ''), 10) = :last10Match
            )
            LIMIT 1
        ");
        $stmt->execute([
            'exact' => $phone,
            'last10' => $last10,
            'plus91' => '+91' . $last10,
            'only91' => '91' . $last10,
            'last10Match' => $last10
        ]);
        return $stmt->fetch(\PDO::FETCH_ASSOC);
    }

    /**
     * Find registered customer/user by email address
     */
    public function findCustomerByEmail(string $email)
    {
        $cleanEmail = strtolower(trim($email));
        if (empty($cleanEmail)) {
            return false;
        }

        $stmt = $this->db->prepare("
            SELECT * FROM users 
            WHERE LOWER(TRIM(email)) = :email
            LIMIT 1
        ");
        $stmt->execute(['email' => $cleanEmail]);
        return $stmt->fetch(\PDO::FETCH_ASSOC);
    }

    /**
     * Find by unique buyer ID (e.g. BUY-123456 or BUY123456) or unique vendor ID
     */
    public function findByUniqueId(string $code)
    {
        $code = trim($code);
        if (empty($code)) return false;
        
        $cleanCode = strtoupper(str_replace('-', '', $code));
        $stmt = $this->db->prepare("
            SELECT * FROM users 
            WHERE UPPER(TRIM(unique_buyer_id)) = :c1 
               OR UPPER(REPLACE(unique_buyer_id, '-', '')) = :c2
               OR UPPER(TRIM(unique_vendor_id)) = :c3
               OR UPPER(REPLACE(unique_vendor_id, '-', '')) = :c4
            LIMIT 1
        ");
        $stmt->execute([
            'c1' => strtoupper($code),
            'c2' => $cleanCode,
            'c3' => strtoupper($code),
            'c4' => $cleanCode
        ]);
        return $stmt->fetch(\PDO::FETCH_ASSOC);
    }

    /**
     * Find all users matching a given username/name (case-insensitive)
     */
    public function findAllByName(string $name)
    {
        $name = trim($name);
        if (empty($name)) return [];

        $stmt = $this->db->prepare("
            SELECT * FROM users 
            WHERE LOWER(TRIM(name)) = :name
            ORDER BY id DESC
        ");
        $stmt->execute(['name' => strtolower($name)]);
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    /**
     * Find users by business profile store/company name
     */
    public function findByBusinessName(string $businessName)
    {
        $businessName = trim($businessName);
        if (empty($businessName)) return [];

        $stmt = $this->db->prepare("
            SELECT u.* FROM users u
            JOIN business_profiles bp ON u.id = bp.user_id
            WHERE LOWER(TRIM(bp.business_name)) = :bname
            ORDER BY u.id DESC
        ");
        $stmt->execute(['bname' => strtolower($businessName)]);
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    /**
     * Comprehensive user lookup for login supporting Username, Mobile Number, Email, and Unique Buyer/Vendor ID
     */
    public function findForLogin(string $loginInput, string $password = '')
    {
        $loginInput = trim($loginInput);
        if (empty($loginInput)) return false;

        // 1. Check if input is an email address
        if (str_contains($loginInput, '@')) {
            $user = $this->findCustomerByEmail($loginInput) ?: $this->findByEmail(strtolower($loginInput)) ?: $this->findByEmail($loginInput);
            if ($user) return $user;
        }

        // 2. Check if input is a phone number (10+ digits or formatted)
        $digitsOnly = preg_replace('/[^0-9]/', '', $loginInput);
        if (strlen($digitsOnly) >= 10) {
            $user = $this->findCustomerByPhone($loginInput) ?: $this->findByPhone($loginInput);
            if (!$user && strlen($digitsOnly) === 12 && str_starts_with($digitsOnly, '91')) {
                $user = $this->findByPhone(substr($digitsOnly, 2));
            }
            if (!$user && strlen($digitsOnly) >= 10) {
                $user = $this->findByPhone(substr($digitsOnly, -10));
            }
            if ($user) return $user;
        }

        // 3. Check by Unique Buyer/Vendor ID (e.g. BUY-123456)
        $user = $this->findByUniqueId($loginInput);
        if ($user) return $user;

        // 4. Check by Email without @ check
        $user = $this->findByEmail(strtolower($loginInput)) ?: $this->findByEmail($loginInput);
        if ($user) return $user;

        // 5. Check exact phone string match
        $user = $this->findByPhone($loginInput);
        if ($user) return $user;

        // 6. Check by Username (User's full name / account name)
        $nameCandidates = $this->findAllByName($loginInput);
        if (!empty($nameCandidates)) {
            if (!empty($password)) {
                foreach ($nameCandidates as $candidate) {
                    if (password_verify($password, $candidate['password_hash'])) {
                        return $candidate;
                    }
                }
            }
            return $nameCandidates[0];
        }

        // 7. Check by Business Name
        $bizCandidates = $this->findByBusinessName($loginInput);
        if (!empty($bizCandidates)) {
            if (!empty($password)) {
                foreach ($bizCandidates as $candidate) {
                    if (password_verify($password, $candidate['password_hash'])) {
                        return $candidate;
                    }
                }
            }
            return $bizCandidates[0];
        }

        return false;
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

    public function getPendingBuyers(int $limit = 0, int $offset = 0)
    {
        $limitClause = $limit > 0 ? "LIMIT {$limit} OFFSET {$offset}" : '';
        $sql = "
            SELECT u.id, u.unique_buyer_id, u.name, u.email, u.phone, u.created_at, 
                   bp.business_name, bp.shop_location, bp.gst_number, bp.pan_number, bp.no_gst_reason, bp.instagram_link,
                   rr.status as request_status, rr.gst_flag,
                   (SELECT COUNT(*) FROM media m WHERE m.user_id = u.id) as photo_count
            FROM users u
            JOIN business_profiles bp ON u.id = bp.user_id
            JOIN registration_requests rr ON u.id = rr.user_id
            WHERE u.status = 'pending' AND u.role = 'buyer'
            ORDER BY u.created_at DESC
            {$limitClause}
        ";
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function countPendingBuyers(): int {
        return (int)$this->db->query("SELECT COUNT(*) FROM users u JOIN business_profiles bp ON u.id = bp.user_id JOIN registration_requests rr ON u.id = rr.user_id WHERE u.status = 'pending' AND u.role = 'buyer'")->fetchColumn();
    }

    public function getRejectedBuyers(int $limit = 0, int $offset = 0)
    {
        $limitClause = $limit > 0 ? "LIMIT {$limit} OFFSET {$offset}" : '';
        $sql = "
            SELECT u.id, u.unique_buyer_id, u.name, u.email, u.phone, u.created_at, 
                   bp.business_name, bp.shop_location, bp.gst_number, bp.pan_number, bp.no_gst_reason, bp.instagram_link,
                   rr.status as request_status, rr.gst_flag, rr.rejection_reason,
                   (SELECT COUNT(*) FROM media m WHERE m.user_id = u.id) as photo_count
            FROM users u
            JOIN business_profiles bp ON u.id = bp.user_id
            LEFT JOIN registration_requests rr ON u.id = rr.user_id
            WHERE u.status = 'rejected' AND u.role = 'buyer'
            ORDER BY u.created_at DESC
            {$limitClause}
        ";
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function countRejectedBuyers(): int {
        return (int)$this->db->query("SELECT COUNT(*) FROM users u JOIN business_profiles bp ON u.id = bp.user_id WHERE u.status = 'rejected' AND u.role = 'buyer'")->fetchColumn();
    }

    public function getActiveBuyers(int $limit = 0, int $offset = 0)
    {
        $limitClause = $limit > 0 ? "LIMIT {$limit} OFFSET {$offset}" : '';
        $sql = "
            SELECT u.id, u.unique_buyer_id, u.name, u.email, u.phone, u.created_at, u.status,
                   bp.business_name, bp.shop_location, bp.gst_number, bp.pan_number, bp.instagram_link,
                   (SELECT COUNT(*) FROM orders o WHERE o.user_id = u.id AND o.payment_status = 'paid') as total_orders,
                   (SELECT SUM(COALESCE(o.grand_total, o.total_amount)) FROM orders o WHERE o.user_id = u.id AND o.status = 'delivered' AND o.payment_status = 'paid') as delivered_spend
            FROM users u
            LEFT JOIN business_profiles bp ON u.id = bp.user_id
            WHERE u.status IN ('active', 'approved', 'blocked') AND u.role = 'buyer'
            ORDER BY u.created_at DESC
            {$limitClause}
        ";
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function countActiveBuyers(): int {
        return (int)$this->db->query("SELECT COUNT(*) FROM users WHERE status IN ('active','approved','blocked') AND role = 'buyer'")->fetchColumn();
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

    /**
     * Completely delete a buyer and all associated records (orders, requests, profiles, etc.)
     */
    public function deleteBuyer(int $userId): bool
    {
        $inTx = $this->db->inTransaction();
        if (!$inTx) {
            $this->db->beginTransaction();
        }

        try {
            $stmt = $this->db->prepare("SELECT id, email, role FROM users WHERE id = ? FOR UPDATE");
            $stmt->execute([$userId]);
            $user = $stmt->fetch(\PDO::FETCH_ASSOC);

            if (!$user) {
                if (!$inTx) $this->db->rollBack();
                return false;
            }

            // Safety check: protect staff/admins from accidental deletion
            if (in_array($user['role'], ['super_admin', 'admin'])) {
                if (!$inTx) $this->db->rollBack();
                throw new \Exception("Admin accounts cannot be deleted through the buyer module.");
            }

            // 1. Delete associated orders and all order items/modifications
            $orderStmt = $this->db->prepare("SELECT id FROM orders WHERE user_id = ?");
            $orderStmt->execute([$userId]);
            $orderIds = $orderStmt->fetchAll(\PDO::FETCH_COLUMN);

            if (!empty($orderIds)) {
                $orderModel = new \App\Models\Order();
                foreach ($orderIds as $oid) {
                    $orderModel->deleteOrder((int)$oid);
                }
            }

            // 2. Delete refund requests
            $this->db->prepare("DELETE FROM refund_requests WHERE user_id = ?")->execute([$userId]);

            // 3. Delete email logs
            $this->db->prepare("DELETE FROM user_email_logs WHERE user_id = ?")->execute([$userId]);

            // 4. Delete user addresses, cart items, wishlists, reviews
            $this->db->prepare("DELETE FROM cart_items WHERE user_id = ?")->execute([$userId]);
            $this->db->prepare("DELETE FROM wishlists WHERE user_id = ?")->execute([$userId]);
            $this->db->prepare("DELETE FROM addresses WHERE user_id = ?")->execute([$userId]);
            $this->db->prepare("DELETE FROM reviews WHERE user_id = ?")->execute([$userId]);

            // 5. Delete registration requests & business profiles
            $this->db->prepare("DELETE FROM registration_requests WHERE user_id = ?")->execute([$userId]);
            $this->db->prepare("DELETE FROM business_profiles WHERE user_id = ?")->execute([$userId]);

            // 6. Delete active sessions and password resets
            $this->db->prepare("DELETE FROM sessions WHERE user_id = ?")->execute([$userId]);
            if (!empty($user['email'])) {
                $this->db->prepare("DELETE FROM password_resets WHERE email = ?")->execute([$user['email']]);
            }

            // 7. Delete user record
            $this->db->prepare("DELETE FROM users WHERE id = ?")->execute([$userId]);

            if (!$inTx) {
                $this->db->commit();
            }

            return true;
        } catch (\Exception $e) {
            if (!$inTx && $this->db->inTransaction()) {
                $this->db->rollBack();
            }
            throw $e;
        }
    }
}
