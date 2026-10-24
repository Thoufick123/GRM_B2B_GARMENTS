<?php
namespace App\Models;

use Core\Model;

class Review extends Model
{
    /**
     * Get products the user has ordered and paid for but hasn't reviewed yet.
     */
    public function getPendingReviews(int $userId)
    {
        $sql = "
            SELECT 
                COALESCE(oi.product_id, pv.product_id, fc.fabric_id) AS product_id,
                oi.customization_id,
                oi.order_id,
                p.name AS product_name,
                fc.program_name,
                fc.garment_type,
                (SELECT image_path FROM product_images WHERE product_id = p.id AND is_primary = 1 LIMIT 1) AS primary_image,
                o.order_number,
                o.created_at AS order_date
            FROM order_items oi
            JOIN orders o ON oi.order_id = o.id
            LEFT JOIN product_variants pv ON oi.variant_id = pv.id
            LEFT JOIN fabric_customizations fc ON oi.customization_id = fc.id
            JOIN products p ON p.id = COALESCE(oi.product_id, pv.product_id, fc.fabric_id)
            WHERE o.user_id = ? 
              AND LOWER(TRIM(o.status)) = 'delivered'
              AND NOT EXISTS (
                  SELECT 1 FROM reviews r 
                  WHERE r.product_id = p.id 
                    AND r.user_id = o.user_id 
                    AND r.order_id = o.id
                    AND (
                        (oi.customization_id IS NULL AND r.customization_id IS NULL)
                        OR (oi.customization_id IS NOT NULL AND r.customization_id = oi.customization_id)
                    )
              )
            GROUP BY COALESCE(oi.product_id, pv.product_id, fc.fabric_id), oi.customization_id, oi.order_id, p.id, p.name, fc.program_name, fc.garment_type, o.order_number, o.created_at
            ORDER BY o.created_at DESC
        ";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$userId]);
        return $stmt->fetchAll();
    }

    /**
     * Submit a new review
     */
    public function createReview(array $data)
    {
        $customizationId = !empty($data['customization_id']) ? (int)$data['customization_id'] : null;

        // Check if a review already exists for this product/user/order combination
        if ($customizationId) {
            $stmtCheck = $this->db->prepare("
                SELECT id FROM reviews 
                WHERE product_id = ? AND user_id = ? AND order_id = ? AND customization_id = ?
            ");
            $stmtCheck->execute([
                $data['product_id'],
                $data['user_id'],
                $data['order_id'],
                $customizationId
            ]);
        } else {
            $stmtCheck = $this->db->prepare("
                SELECT id FROM reviews 
                WHERE product_id = ? AND user_id = ? AND order_id = ? AND customization_id IS NULL
            ");
            $stmtCheck->execute([
                $data['product_id'],
                $data['user_id'],
                $data['order_id']
            ]);
        }
        
        if ($stmtCheck->fetch()) {
            throw new \Exception("You have already reviewed this item for this order.");
        }

        $stmt = $this->db->prepare("
            INSERT INTO reviews (product_id, customization_id, user_id, order_id, rating, review_text, photos, is_approved)
            VALUES (:pid, :cid, :uid, :oid, :rating, :text, :photos, 0)
        ");

        return $stmt->execute([
            'pid' => $data['product_id'],
            'cid' => $customizationId,
            'uid' => $data['user_id'],
            'oid' => $data['order_id'],
            'rating' => $data['rating'],
            'text' => $data['review_text'] ?? null,
            'photos' => isset($data['photos']) ? json_encode($data['photos']) : null
        ]);
    }

    /**
     * Get all reviews submitted by a specific user
     */
    public function getUserReviews(int $userId)
    {
        $sql = "
            SELECT 
                r.*,
                p.name AS product_name,
                fc.program_name,
                fc.garment_type,
                (SELECT image_path FROM product_images WHERE product_id = p.id AND is_primary = 1 LIMIT 1) AS primary_image
            FROM reviews r
            LEFT JOIN products p ON r.product_id = p.id
            LEFT JOIN fabric_customizations fc ON r.customization_id = fc.id
            WHERE r.user_id = ?
            ORDER BY r.created_at DESC
        ";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$userId]);
        return $stmt->fetchAll();
    }

    /**
     * Map of reviewed items for quick lookup in orders/order_detail:
     * Key: orderId_productId_customizationId => review array
     */
    public function getUserReviewedItemMap(int $userId): array
    {
        $sql = "
            SELECT r.*
            FROM reviews r
            WHERE r.user_id = ?
        ";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$userId]);
        $rows = $stmt->fetchAll();

        $map = [];
        foreach ($rows as $row) {
            $cid = !empty($row['customization_id']) ? $row['customization_id'] : '0';
            $key = $row['order_id'] . '_' . $row['product_id'] . '_' . $cid;
            $map[$key] = $row;
            if (empty($row['customization_id'])) {
                $map[$row['order_id'] . '_' . $row['product_id']] = $row;
            }
        }
        return $map;
    }

    /**
     * Get reviews for the Admin panel
     * @param string|null $type 'standard' | 'customized' | null
     */
    public function getAdminReviews(?string $type = null)
    {
        $where = "";
        $params = [];
        if ($type === 'standard') {
            $where = "WHERE r.customization_id IS NULL";
        } elseif ($type === 'customized') {
            $where = "WHERE r.customization_id IS NOT NULL";
        }

        $sql = "
            SELECT 
                r.*,
                p.name AS product_name,
                fc.program_name,
                fc.garment_type,
                u.name AS reviewer_name,
                u.email AS reviewer_email
            FROM reviews r
            LEFT JOIN products p ON r.product_id = p.id
            JOIN users u ON r.user_id = u.id
            LEFT JOIN fabric_customizations fc ON r.customization_id = fc.id
            {$where}
            ORDER BY r.created_at DESC
        ";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    /**
     * Get all reviews for a specific vendor's products
     */
    public function getVendorReviews(int $vendorId)
    {
        $sql = "
            SELECT 
                r.*,
                p.name AS product_name,
                u.name AS reviewer_name,
                u.email AS reviewer_email
            FROM reviews r
            JOIN products p ON r.product_id = p.id
            JOIN users u ON r.user_id = u.id
            WHERE p.vendor_id = :vendor_id
            ORDER BY r.created_at DESC
        ";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['vendor_id' => $vendorId]);
        return $stmt->fetchAll();
    }

    /**
     * Check if a review belongs to a vendor's product
     */
    public function isReviewOwnedByVendor(int $reviewId, int $vendorId): bool
    {
        $sql = "
            SELECT r.id 
            FROM reviews r
            JOIN products p ON r.product_id = p.id
            WHERE r.id = :review_id AND p.vendor_id = :vendor_id
        ";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            'review_id' => $reviewId,
            'vendor_id' => $vendorId
        ]);
        return (bool)$stmt->fetch();
    }

    /**
     * Approve or reject a review
     */
    public function updateStatus(int $reviewId, bool $isApproved)
    {
        $stmt = $this->db->prepare("UPDATE reviews SET is_approved = ? WHERE id = ?");
        $success = $stmt->execute([$isApproved ? 1 : 0, $reviewId]);
        
        if ($success) {
            try {
                // Fetch product_id and customization_id for this review
                $stmt = $this->db->prepare("SELECT product_id, customization_id FROM reviews WHERE id = ?");
                $stmt->execute([$reviewId]);
                $review = $stmt->fetch();
                
                if ($review && !empty($review['product_id'])) {
                    $pid = (int)$review['product_id'];
                    $this->recalculateProductRating($pid);
                    if (class_exists('\Core\Cache')) {
                        \Core\Cache::delete('approved_reviews_' . $pid);
                        \Core\Cache::delete('product_details_bundle_' . $pid);
                        \Core\Cache::delete('rel_prods_diff_' . $pid);
                    }
                }

                if ($review && !empty($review['customization_id'])) {
                    $cid = (int)$review['customization_id'];
                    if (class_exists('\Core\Cache')) {
                        \Core\Cache::delete('approved_reviews_cst_' . $cid);
                        \Core\Cache::delete('fabric_customization_bundle_' . $cid);
                    }
                }
            } catch (\Throwable $e) {
                error_log("Non-fatal error in review post-approval rating hook: " . $e->getMessage());
            }
        }
        
        return $success;
    }

    /**
     * Ensure num_reviews and average_rating exist on products table (self-healing migration)
     */
    private function ensureProductRatingColumns(): void
    {
        static $checked = false;
        if ($checked) return;

        try {
            $colStmt = $this->db->query("SHOW COLUMNS FROM products LIKE 'num_reviews'");
            if (!$colStmt->fetch()) {
                $this->db->exec("ALTER TABLE products ADD COLUMN num_reviews INT NOT NULL DEFAULT 0 AFTER stock_quantity");
            }
        } catch (\Throwable $e) {
            // Ignore if already added or insufficient permissions
        }

        try {
            $colStmt = $this->db->query("SHOW COLUMNS FROM products LIKE 'average_rating'");
            if (!$colStmt->fetch()) {
                $this->db->exec("ALTER TABLE products ADD COLUMN average_rating DECIMAL(3,2) NOT NULL DEFAULT 0.00 AFTER num_reviews");
            }
        } catch (\Throwable $e) {
            // Ignore if already added or insufficient permissions
        }

        $checked = true;
    }

    /**
     * Recalculate average rating and number of reviews for a product
     */
    public function recalculateProductRating(int $productId)
    {
        if ($productId <= 0) {
            return false;
        }

        try {
            $this->ensureProductRatingColumns();

            $sql = "
                SELECT 
                    COUNT(*) as num_reviews,
                    AVG(rating) as average_rating
                FROM reviews 
                WHERE product_id = ? AND is_approved = 1
            ";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$productId]);
            $stats = $stmt->fetch();

            $numReviews = (int)($stats['num_reviews'] ?? 0);
            $avgRating = (float)($stats['average_rating'] ?? 0.00);

            $updateStmt = $this->db->prepare("
                UPDATE products 
                SET num_reviews = ?, average_rating = ?
                WHERE id = ?
            ");
            return $updateStmt->execute([$numReviews, round($avgRating, 2), $productId]);
        } catch (\Throwable $e) {
            error_log("recalculateProductRating failed for product #{$productId}: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Get approved reviews for a specific product
     */
    public function getApprovedProductReviews(int $productId)
    {
        $cacheKey = 'approved_reviews_' . $productId;
        if (class_exists('\Core\Cache')) {
            $cached = \Core\Cache::get($cacheKey);
            if ($cached !== null) {
                return $cached;
            }
        }

        $sql = "
            SELECT 
                r.*,
                COALESCE(u.name, 'Customer') AS reviewer_name,
                u.email AS reviewer_email
            FROM reviews r
            LEFT JOIN users u ON r.user_id = u.id
            WHERE r.product_id = ? AND r.is_approved = 1
            ORDER BY r.created_at DESC
        ";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$productId]);
        $reviews = $stmt->fetchAll();

        if (class_exists('\Core\Cache')) {
            \Core\Cache::set($cacheKey, $reviews, 300);
        }

        return $reviews;
    }

    /**
     * Get approved reviews for a specific customization or its fabric
     */
    public function getApprovedCustomizationReviews(int $customizationId, int $fabricId = 0)
    {
        $params = ['cid' => $customizationId];
        $whereFabric = "";
        if ($fabricId > 0) {
            $whereFabric = " OR r.product_id = :fid";
            $params['fid'] = $fabricId;
        }

        $sql = "
            SELECT 
                r.*,
                COALESCE(u.name, 'Customer') AS reviewer_name,
                fc.program_name,
                fc.garment_type
            FROM reviews r
            LEFT JOIN users u ON r.user_id = u.id
            LEFT JOIN fabric_customizations fc ON r.customization_id = fc.id
            WHERE (r.customization_id = :cid {$whereFabric})
              AND r.is_approved = 1
            ORDER BY r.created_at DESC
        ";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    /**
     * Get average rating and total approved review count for customization
     */
    public function getCustomizationReviewStats(int $customizationId, int $fabricId = 0): array
    {
        $params = ['cid' => $customizationId];
        $whereFabric = "";
        if ($fabricId > 0) {
            $whereFabric = " OR product_id = :fid";
            $params['fid'] = $fabricId;
        }

        $sql = "
            SELECT 
                COUNT(*) as num_reviews,
                AVG(rating) as average_rating
            FROM reviews 
            WHERE (customization_id = :cid {$whereFabric})
              AND is_approved = 1
        ";
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        $stats = $stmt->fetch();

        $numReviews = (int)($stats['num_reviews'] ?? 0);
        $avgRating = ($numReviews > 0 && !empty($stats['average_rating'])) 
            ? round((float)$stats['average_rating'], 1) 
            : 0.0;

        return [
            'total_reviews' => $numReviews,
            'avg_rating' => $avgRating
        ];
    }
}
