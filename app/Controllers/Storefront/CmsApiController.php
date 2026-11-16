<?php
namespace App\Controllers\Storefront;

use Core\Controller;
use Core\Database;
use Core\Cache;

class CmsApiController extends Controller
{
    private \PDO $db;

    public function __construct()
    {
        parent::__construct();
        $this->db = Database::getInstance();
    }

    public function getLayout($pageName = 'home')
    {
        $cacheKey = "cms_layout_{$pageName}";
        
        // 1. Try Cache First (Public read endpoint)
        $cachedData = Cache::get($cacheKey);
        if ($cachedData !== null) {
            $this->jsonResponse($cachedData);
            return;
        }

        // 2. Fetch from DB
        $stmt = $this->db->prepare("SELECT layout_data FROM cms_layouts WHERE page_name = :page");
        $stmt->execute(['page' => $pageName]);
        $layout = $stmt->fetch();

        if (!$layout) {
            $this->jsonResponse(['error' => 'Layout not found'], 404);
            return;
        }

        $componentIds = json_decode($layout['layout_data'], true);
        if (!is_array($componentIds) || empty($componentIds)) {
            $this->jsonResponse(['error' => 'Invalid layout data'], 500);
            return;
        }

        // 3. Fetch active components in order
        $placeholders = implode(',', array_fill(0, count($componentIds), '?'));
        $sql = "SELECT * FROM cms_components WHERE id IN ($placeholders) AND is_active = 1";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute($componentIds);
        $componentsData = $stmt->fetchAll();

        // Map them by ID for O(1) lookup
        $componentsMap = [];
        foreach ($componentsData as $cmp) {
            $cmp['content_data'] = json_decode($cmp['content_data'], true);
            $componentsMap[$cmp['id']] = $cmp;
        }

        // 4. Construct Final Payload based on layout order
        $payload = [];
        foreach ($componentIds as $id) {
            if (isset($componentsMap[$id])) {
                $payload[] = $componentsMap[$id];
            }
        }

        // 5. Cache the payload
        Cache::set($cacheKey, $payload, 3600); // 1 hour cache

        $this->jsonResponse($payload);
    }

    // Protected Admin Endpoint for mutations (mock auth check here)
    public function updateLayout($pageName = 'home')
    {
        $userId = \Core\Session::get('user_id');
        $userRole = \Core\Session::get('user_role');
        if (!$userId || ($userRole !== 'super_admin' && !$this->hasPermission('cms_builder', 'edit'))) {
            $this->jsonResponse(['error' => 'Unauthorized'], 401);
            return;
        }

        $input = file_get_contents('php://input');
        $data = json_decode($input, true);

        if (!isset($data['layout']) || !is_array($data['layout'])) {
            $this->jsonResponse(['error' => 'Invalid input'], 400);
            return;
        }

        try {
            $this->db->beginTransaction();
            $ids = [];
            foreach ($data['layout'] as $item) {
                if (is_array($item) && isset($item['id'])) {
                    $ids[] = $item['id'];
                    if (isset($item['is_active'])) {
                        $stmt = $this->db->prepare("UPDATE cms_components SET is_active = :is_active WHERE id = :id");
                        $stmt->execute([
                            'is_active' => $item['is_active'] ? 1 : 0,
                            'id' => $item['id']
                        ]);
                    }
                } else if (is_string($item)) {
                    // Fallback for old format
                    $ids[] = $item;
                }
            }

            $stmt = $this->db->prepare("UPDATE cms_layouts SET layout_data = :layout WHERE page_name = :page");
            $stmt->execute([
                'layout' => json_encode($ids),
                'page' => $pageName
            ]);
            $this->db->commit();

            // Cache Invalidation (Immediate Cache Busting)
            Cache::delete("cms_layout_{$pageName}");
            Cache::delete("cms_about_us");

            $this->jsonResponse(['success' => true]);
        } catch (\Exception $e) {
            if ($this->db->inTransaction()) {
                $this->db->rollBack();
            }
            $this->jsonResponse(['error' => $e->getMessage()], 500);
        }
    }

    private function jsonResponse($data, $status = 200)
    {
        http_response_code($status);
        header('Content-Type: application/json');
        echo json_encode($data);
        exit;
    }
}
