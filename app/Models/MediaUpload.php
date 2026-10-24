<?php
namespace App\Models;

use Core\Database;
use PDO;

class MediaUpload
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    public function findByHash(string $hash)
    {
        $stmt = $this->db->prepare("SELECT * FROM media_uploads WHERE file_hash = :hash LIMIT 1");
        $stmt->execute(['hash' => $hash]);
        return $stmt->fetch();
    }
    
    public function findById(int $id)
    {
        if ($id >= 1000000) {
            $realId = $id - 1000000;
            $stmt = $this->db->prepare("SELECT (1000000 + id) as id, SUBSTRING_INDEX(file_path, '/', -1) as original_filename, MD5(file_path) as file_hash, file_path as cloudinary_url, '' as public_id, 0 as file_size, user_id as uploaded_by, created_at, 'image' as file_type FROM media WHERE id = :id LIMIT 1");
            $stmt->execute(['id' => $realId]);
            return $stmt->fetch();
        }
        $stmt = $this->db->prepare("SELECT * FROM media_uploads WHERE id = :id LIMIT 1");
        $stmt->execute(['id' => $id]);
        return $stmt->fetch();
    }

    public function create(array $data)
    {
        $sql = "INSERT INTO media_uploads (original_filename, file_hash, cloudinary_url, public_id, file_size, uploaded_by)
                VALUES (:original_filename, :file_hash, :cloudinary_url, :public_id, :file_size, :uploaded_by)";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            'original_filename' => $data['original_filename'],
            'file_hash' => $data['file_hash'],
            'cloudinary_url' => $data['cloudinary_url'],
            'public_id' => $data['public_id'],
            'file_size' => $data['file_size'] ?? 0,
            'uploaded_by' => $data['uploaded_by'] ?? null
        ]);
        
        return $this->db->lastInsertId();
    }
    
    public function getAll(?int $vendorId = null, ?string $scope = null)
    {
        if ($vendorId !== null) {
            $sql = "
                SELECT 
                    m.id,
                    m.original_filename,
                    m.file_hash,
                    m.cloudinary_url,
                    m.public_id,
                    m.file_size,
                    m.uploaded_by,
                    m.created_at,
                    u.name as uploader_name,
                    u.role as uploader_role,
                    vp.store_name
                FROM media_uploads m
                LEFT JOIN users u ON m.uploaded_by = u.id
                LEFT JOIN vendor_profiles vp ON u.id = vp.user_id
                WHERE m.uploaded_by = :vendor_id_1
                  AND m.public_id NOT LIKE 'shop_img_%'
                  AND m.original_filename NOT LIKE 'vendor_doc_%'
                  AND m.cloudinary_url NOT LIKE 'uploads/vendors/%'

                UNION ALL

                SELECT 
                    (1000000 + med.id) as id,
                    SUBSTRING_INDEX(med.file_path, '/', -1) as original_filename,
                    MD5(med.file_path) as file_hash,
                    med.file_path as cloudinary_url,
                    '' as public_id,
                    0 as file_size,
                    med.user_id as uploaded_by,
                    med.created_at,
                    u.name as uploader_name,
                    u.role as uploader_role,
                    vp.store_name
                FROM media med
                LEFT JOIN users u ON med.user_id = u.id
                LEFT JOIN vendor_profiles vp ON u.id = vp.user_id
                WHERE med.user_id = :vendor_id_2
                  AND (med.purpose IS NULL OR med.purpose NOT IN ('shop_image', 'vendor_verification', 'shop_verification', 'business_verification'))
                  AND med.file_path NOT LIKE 'uploads/vendors/%'
                  AND NOT EXISTS (
                      SELECT 1 FROM media_uploads mu 
                      WHERE mu.uploaded_by = med.user_id 
                        AND (mu.cloudinary_url = med.file_path OR mu.original_filename = SUBSTRING_INDEX(med.file_path, '/', -1))
                  )

                ORDER BY created_at DESC
            ";
            $stmt = $this->db->prepare($sql);
            $stmt->execute(['vendor_id_1' => $vendorId, 'vendor_id_2' => $vendorId]);
            return $stmt->fetchAll();
        }

        if ($scope === 'admin') {
            $sql = "
                SELECT m.*, u.name as uploader_name, u.role as uploader_role, vp.store_name
                FROM media_uploads m
                LEFT JOIN users u ON m.uploaded_by = u.id
                LEFT JOIN vendor_profiles vp ON u.id = vp.user_id
                WHERE (m.uploaded_by IS NULL OR u.role IN ('super_admin', 'admin', 'staff'))
                  AND (u.role != 'vendor' OR u.role IS NULL)
                  AND (m.uploaded_by NOT IN (SELECT id FROM users WHERE role = 'vendor') OR m.uploaded_by IS NULL)
                  AND m.public_id NOT LIKE 'shop_img_%'
                  AND m.original_filename NOT LIKE 'vendor_doc_%'
                  AND m.cloudinary_url NOT LIKE 'uploads/vendors/%'
                ORDER BY m.created_at DESC
            ";
            $stmt = $this->db->query($sql);
            return $stmt->fetchAll();
        }

        if ($scope === 'vendors') {
            $sql = "
                SELECT 
                    m.id,
                    m.original_filename,
                    m.file_hash,
                    m.cloudinary_url,
                    m.public_id,
                    m.file_size,
                    m.uploaded_by,
                    m.created_at,
                    u.name as uploader_name,
                    u.role as uploader_role,
                    vp.store_name
                FROM media_uploads m
                LEFT JOIN users u ON m.uploaded_by = u.id
                LEFT JOIN vendor_profiles vp ON u.id = vp.user_id
                WHERE (u.role = 'vendor' OR m.uploaded_by IN (SELECT id FROM users WHERE role = 'vendor'))
                  AND m.public_id NOT LIKE 'shop_img_%'
                  AND m.original_filename NOT LIKE 'vendor_doc_%'
                  AND m.cloudinary_url NOT LIKE 'uploads/vendors/%'

                UNION ALL

                SELECT 
                    (1000000 + med.id) as id,
                    SUBSTRING_INDEX(med.file_path, '/', -1) as original_filename,
                    MD5(med.file_path) as file_hash,
                    med.file_path as cloudinary_url,
                    '' as public_id,
                    0 as file_size,
                    med.user_id as uploaded_by,
                    med.created_at,
                    u.name as uploader_name,
                    u.role as uploader_role,
                    vp.store_name
                FROM media med
                JOIN users u ON med.user_id = u.id AND u.role = 'vendor'
                LEFT JOIN vendor_profiles vp ON u.id = vp.user_id
                WHERE (med.purpose IS NULL OR med.purpose NOT IN ('shop_image', 'vendor_verification', 'shop_verification', 'business_verification'))
                  AND med.file_path NOT LIKE 'uploads/vendors/%'
                  AND NOT EXISTS (
                    SELECT 1 FROM media_uploads mu 
                    WHERE mu.uploaded_by = med.user_id 
                      AND (mu.cloudinary_url = med.file_path OR mu.original_filename = SUBSTRING_INDEX(med.file_path, '/', -1))
                )

                ORDER BY created_at DESC
            ";
            $stmt = $this->db->query($sql);
            return $stmt->fetchAll();
        }

        // All platform media (when explicitly requested)
        $sql = "
            SELECT m.*, u.name as uploader_name, u.role as uploader_role, vp.store_name
            FROM media_uploads m
            LEFT JOIN users u ON m.uploaded_by = u.id
            LEFT JOIN vendor_profiles vp ON u.id = vp.user_id
            WHERE (m.uploaded_by IS NOT NULL OR u.role IS NULL OR u.role != 'buyer')
              AND m.public_id NOT LIKE 'shop_img_%'
              AND m.original_filename NOT LIKE 'vendor_doc_%'
              AND m.cloudinary_url NOT LIKE 'uploads/vendors/%'
              AND NOT EXISTS (SELECT 1 FROM media bm WHERE bm.file_path = m.cloudinary_url)
              AND NOT EXISTS (SELECT 1 FROM users bu WHERE bu.profile_picture = m.cloudinary_url)
            ORDER BY m.created_at DESC
        ";
        $stmt = $this->db->query($sql);
        return $stmt->fetchAll();
    }

    /**
     * Retrieve all active media references across the entire database in a single fast query.
     */
    public function getAllDatabaseMediaReferences(): array
    {
        try {
            $sql = "
                SELECT 'product' as source, pi.image_path as ref, CONCAT('Product: ', IFNULL(p.name, 'Unknown'), IF(pi.variant_id IS NOT NULL, CONCAT(' (Variant #', pi.variant_id, ')'), '')) as detail
                FROM product_images pi
                LEFT JOIN products p ON pi.product_id = p.id
                WHERE pi.image_path IS NOT NULL AND pi.image_path != ''

                UNION ALL

                SELECT 'category' as source, c.image_path as ref, CONCAT('Category: ', c.name) as detail
                FROM categories c
                WHERE c.image_path IS NOT NULL AND c.image_path != ''

                UNION ALL

                SELECT 'size_chart' as source, sc.image_url as ref, CONCAT('Size Chart: ', sc.title) as detail
                FROM size_charts sc
                WHERE sc.image_url IS NOT NULL AND sc.image_url != ''

                UNION ALL

                SELECT 'setting' as source, s.setting_value as ref, CONCAT('Setting: ', s.setting_key) as detail
                FROM settings s
                WHERE s.setting_value LIKE '%/%'

                UNION ALL

                SELECT 'system_setting' as source, ss.key_value as ref, CONCAT('System Setting: ', ss.key_name) as detail
                FROM system_settings ss
                WHERE ss.key_value LIKE '%/%'

                UNION ALL

                SELECT 'cms_component' as source, c.content_data as ref, CONCAT('CMS Component: ', c.section_type) as detail
                FROM cms_components c
                WHERE c.content_data IS NOT NULL

                UNION ALL

                SELECT 'cms_project' as source, CONCAT_WS(' ', cp.images, cp.videos) as ref, CONCAT('CMS Project: ', cp.title) as detail
                FROM cms_projects cp

                UNION ALL

                SELECT 'vendor_profile' as source, CONCAT_WS(' ', vp.shop_images, vp.gst_document) as ref, CONCAT('Vendor Store: ', vp.store_name) as detail
                FROM vendor_profiles vp
                WHERE (vp.shop_images IS NOT NULL AND vp.shop_images != '') OR (vp.gst_document IS NOT NULL AND vp.gst_document != '')

                UNION ALL

                SELECT 'review' as source, r.photos as ref, CONCAT('Review on Product #', r.product_id) as detail
                FROM reviews r
                WHERE r.photos IS NOT NULL AND r.photos != ''

                UNION ALL

                SELECT 'order' as source, CONCAT_WS(' ', o.packing_video_1, o.packing_video_2) as ref, CONCAT('Order Packing Video: #', o.order_number) as detail
                FROM orders o
                WHERE o.packing_video_1 IS NOT NULL OR o.packing_video_2 IS NOT NULL

                UNION ALL

                SELECT 'user' as source, u.profile_picture as ref, CONCAT('User Profile: ', u.name) as detail
                FROM users u
                WHERE u.profile_picture IS NOT NULL AND u.profile_picture != ''
            ";
            $stmt = $this->db->query($sql);
            return $stmt ? $stmt->fetchAll(PDO::FETCH_ASSOC) : [];
        } catch (\Exception $e) {
            error_log('[MediaUpload::getAllDatabaseMediaReferences] Error: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Check if a specific media asset is referenced anywhere in the platform.
     */
    public function getAssetUsage($mediaOrId, ?array $preloadedReferences = null): array
    {
        $media = is_array($mediaOrId) ? $mediaOrId : $this->findById((int)$mediaOrId);
        if (!$media) {
            return ['in_use' => false, 'count' => 0, 'usages' => [], 'summary' => ''];
        }

        $tokens = [];
        $rawUrl = trim($media['cloudinary_url'] ?? '');
        if (!empty($rawUrl)) {
            $tokens[] = $rawUrl;
            $parsedPath = parse_url($rawUrl, PHP_URL_PATH);
            if (!empty($parsedPath)) {
                $cleanPath = ltrim($parsedPath, '/');
                if (strlen($cleanPath) >= 4) {
                    $tokens[] = $cleanPath;
                }
            }
        }

        $publicId = trim($media['public_id'] ?? '');
        if (!empty($publicId) && strlen($publicId) >= 3) {
            $tokens[] = $publicId;
            if (strpos($publicId, '/') !== false) {
                $subId = basename($publicId);
                if (strlen($subId) >= 4) {
                    $tokens[] = $subId;
                }
            }
        }

        if (!empty($rawUrl)) {
            $fileBase = basename($rawUrl);
            if (strlen($fileBase) >= 6 && strpos($fileBase, '.') !== false) {
                $tokens[] = $fileBase;
            }
        }

        $tokens = array_unique(array_filter($tokens));
        if (empty($tokens)) {
            return ['in_use' => false, 'count' => 0, 'usages' => [], 'summary' => ''];
        }

        $dbReferences = $preloadedReferences ?? $this->getAllDatabaseMediaReferences();
        $matched = [];
        foreach ($dbReferences as $ref) {
            $refText = $ref['ref'] ?? '';
            if (empty($refText)) continue;

            foreach ($tokens as $token) {
                if (str_contains($refText, $token)) {
                    $matched[] = $ref['detail'];
                    break;
                }
            }
        }

        $uniqueMatched = array_values(array_unique($matched));
        $inUse = !empty($uniqueMatched);
        $count = count($uniqueMatched);
        $summary = $inUse ? (implode(', ', array_slice($uniqueMatched, 0, 2)) . ($count > 2 ? ' +' . ($count - 2) . ' more' : '')) : '';

        return [
            'in_use'  => $inUse,
            'count'   => $count,
            'usages'  => $uniqueMatched,
            'summary' => $summary
        ];
    }

    /**
     * Efficiently attach usage status to a list of media records.
     */
    public function attachUsageStatus(array &$mediaFiles): void
    {
        if (empty($mediaFiles)) return;
        $dbReferences = $this->getAllDatabaseMediaReferences();

        foreach ($mediaFiles as &$media) {
            $usage = $this->getAssetUsage($media, $dbReferences);
            $media['is_used'] = $usage['in_use'];
            $media['usage_count'] = $usage['count'];
            $media['usages'] = $usage['usages'];
            $media['usage_summary'] = $usage['summary'];
        }
        unset($media);
    }
    
    public function delete(int $id)
    {
        if ($id >= 1000000) {
            $realId = $id - 1000000;
            $stmt = $this->db->prepare("DELETE FROM media WHERE id = :id");
            return $stmt->execute(['id' => $realId]);
        }
        $stmt = $this->db->prepare("DELETE FROM media_uploads WHERE id = :id");
        return $stmt->execute(['id' => $id]);
    }
}
