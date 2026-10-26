<?php
namespace App\Controllers\Admin;

use Core\Controller;
use Core\Database;
use Core\Session;
use Core\Cache;
use App\Models\CmsComponent;
use App\Core\CloudinaryUploader;

class ReelsController extends Controller
{
    private CmsComponent $cmsModel;
    private \PDO $db;

    public function __construct()
    {
        parent::__construct();
        $role = Session::get('user_role');
        if (!$role) {
            Session::setFlash('error', 'Unauthorized access.');
            $this->redirect('/login');
        }
        $this->cmsModel = new CmsComponent();
        $this->db = Database::getInstance();
    }

    /**
     * List all Reels Containers
     */
    public function index()
    {
        $this->requirePermission('cms_reels', 'view');
        $containers = $this->cmsModel->getAllReelsContainers();
        
        foreach ($containers as &$c) {
            $c['content'] = json_decode($c['content_data'], true) ?: [];
        }

        $this->render('admin/cms/reels/index', [
            'title' => 'Reels Containers - CMS Builder',
            'containers' => $containers
        ], 'admin');
    }

    /**
     * Form to add a new Reels Container
     */
    public function create()
    {
        $this->requirePermission('cms_reels', 'create');
        $this->render('admin/cms/reels/create', [
            'title' => 'Add Reels Container'
        ], 'admin');
    }

    /**
     * Store new Reels Container
     */
    public function store()
    {
        $this->requirePermission('cms_reels', 'create');
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/admin/cms/reels');
        }

        $title = trim($_POST['title'] ?? '');
        $subtitle = trim($_POST['subtitle'] ?? '');
        $desktopHeightVh = !empty($_POST['desktop_height_vh']) ? trim((string)$_POST['desktop_height_vh']) : '70';
        $mobileHeightVh = !empty($_POST['mobile_height_vh']) ? trim((string)$_POST['mobile_height_vh']) : '55';
        $desktopCount = !empty($_POST['desktop_count']) ? (float)$_POST['desktop_count'] : (!empty($_POST['desktop_view_count']) ? (float)$_POST['desktop_view_count'] : (!empty($_POST['cards_desktop']) ? (float)$_POST['cards_desktop'] : 4));
        $mobileCount = !empty($_POST['mobile_count']) ? (float)$_POST['mobile_count'] : (!empty($_POST['mobile_view_count']) ? (float)$_POST['mobile_view_count'] : (!empty($_POST['cards_mobile']) ? (float)$_POST['cards_mobile'] : 2));
        $tabletCount = !empty($_POST['tablet_view_count']) ? (float)$_POST['tablet_view_count'] : (!empty($_POST['cards_tablet']) ? (float)$_POST['cards_tablet'] : max(1, min(3, round($desktopCount * 0.6))));
        $autoplay = isset($_POST['autoplay']) ? 1 : 0;
        $muted = isset($_POST['muted']) ? 1 : 0;
        $loop = isset($_POST['loop']) ? 1 : 0;
        $bgColor = trim($_POST['bg_color'] ?? '#ffffff');
        $isActive = isset($_POST['is_active']) ? 1 : 0;

        // Process dynamic reel items
        $items = $this->processReelItems();

        $contentData = json_encode([
            'title'              => $title,
            'subtitle'           => $subtitle,
            'desktop_height_vh'  => $desktopHeightVh,
            'mobile_height_vh'   => $mobileHeightVh,
            'desktop_count'      => $desktopCount,
            'mobile_count'       => $mobileCount,
            'desktop_view_count' => $desktopCount,
            'tablet_view_count'  => $tabletCount,
            'mobile_view_count'  => $mobileCount,
            'cards_desktop'      => $desktopCount,
            'cards_tablet'       => $tabletCount,
            'cards_mobile'       => $mobileCount,
            'autoplay'           => $autoplay,
            'muted'              => $muted,
            'loop'               => $loop,
            'bg_color'           => $bgColor,
            'items'              => $items
        ]);

        $id = 'reels_' . uniqid();

        $this->cmsModel->create([
            'id'           => $id,
            'section_type' => 'reels_container',
            'content_data' => $contentData,
            'is_active'    => $isActive
        ]);

        $this->addToHomeLayout($id);

        if (class_exists('\Core\Cache')) {
            Cache::delete('cms_layout_home');
            Cache::clear();
        }

        Session::setFlash('success', 'Reels Container created and added to Layout Builder.');
        $this->redirect('/admin/cms/reels');
    }

    /**
     * Form to edit an existing Reels Container
     */
    public function edit()
    {
        $this->requirePermission('cms_reels', 'edit');
        $id = $_GET['id'] ?? '';
        $container = $this->cmsModel->getById($id);

        if (!$container || $container['section_type'] !== 'reels_container') {
            Session::setFlash('error', 'Reels Container not found.');
            $this->redirect('/admin/cms/reels');
            return;
        }

        $container['content'] = json_decode($container['content_data'], true) ?: [];

        $this->render('admin/cms/reels/edit', [
            'title'     => 'Edit Reels Container',
            'container' => $container
        ], 'admin');
    }

    /**
     * Update existing Reels Container
     */
    public function update()
    {
        $this->requirePermission('cms_reels', 'edit');
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/admin/cms/reels');
        }

        $id = trim($_POST['id'] ?? '');
        $existing = $this->cmsModel->getById($id);

        if (!$existing || $existing['section_type'] !== 'reels_container') {
            Session::setFlash('error', 'Reels Container not found.');
            $this->redirect('/admin/cms/reels');
            return;
        }

        $title = trim($_POST['title'] ?? '');
        $subtitle = trim($_POST['subtitle'] ?? '');
        $desktopHeightVh = !empty($_POST['desktop_height_vh']) ? trim((string)$_POST['desktop_height_vh']) : '70';
        $mobileHeightVh = !empty($_POST['mobile_height_vh']) ? trim((string)$_POST['mobile_height_vh']) : '55';
        $desktopCount = !empty($_POST['desktop_count']) ? (float)$_POST['desktop_count'] : (!empty($_POST['desktop_view_count']) ? (float)$_POST['desktop_view_count'] : (!empty($_POST['cards_desktop']) ? (float)$_POST['cards_desktop'] : 4));
        $mobileCount = !empty($_POST['mobile_count']) ? (float)$_POST['mobile_count'] : (!empty($_POST['mobile_view_count']) ? (float)$_POST['mobile_view_count'] : (!empty($_POST['cards_mobile']) ? (float)$_POST['cards_mobile'] : 2));
        $tabletCount = !empty($_POST['tablet_view_count']) ? (float)$_POST['tablet_view_count'] : (!empty($_POST['cards_tablet']) ? (float)$_POST['cards_tablet'] : max(1, min(3, round($desktopCount * 0.6))));
        $autoplay = isset($_POST['autoplay']) ? 1 : 0;
        $muted = isset($_POST['muted']) ? 1 : 0;
        $loop = isset($_POST['loop']) ? 1 : 0;
        $bgColor = trim($_POST['bg_color'] ?? '#ffffff');
        $isActive = isset($_POST['is_active']) ? 1 : 0;

        // Process dynamic reel items
        $items = $this->processReelItems();

        $contentData = json_encode([
            'title'              => $title,
            'subtitle'           => $subtitle,
            'desktop_height_vh'  => $desktopHeightVh,
            'mobile_height_vh'   => $mobileHeightVh,
            'desktop_count'      => $desktopCount,
            'mobile_count'       => $mobileCount,
            'desktop_view_count' => $desktopCount,
            'tablet_view_count'  => $tabletCount,
            'mobile_view_count'  => $mobileCount,
            'cards_desktop'      => $desktopCount,
            'cards_tablet'       => $tabletCount,
            'cards_mobile'       => $mobileCount,
            'autoplay'           => $autoplay,
            'muted'              => $muted,
            'loop'               => $loop,
            'bg_color'           => $bgColor,
            'items'              => $items
        ]);

        $this->cmsModel->update($id, [
            'content_data' => $contentData,
            'is_active'    => $isActive
        ]);

        if (class_exists('\Core\Cache')) {
            Cache::delete('cms_layout_home');
            Cache::clear();
        }

        Session::setFlash('success', 'Reels Container updated successfully.');
        $this->redirect('/admin/cms/reels');
    }

    /**
     * Delete a Reels Container
     */
    public function delete()
    {
        $this->requirePermission('cms_reels', 'delete');
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/admin/cms/reels');
        }

        $id = trim($_POST['id'] ?? '');
        $isAjax = !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';

        if (!empty($id)) {
            $this->cmsModel->delete($id);

            // Also remove from cms_layouts
            $stmt = $this->db->prepare("SELECT layout_data FROM cms_layouts WHERE page_name = 'home'");
            $stmt->execute();
            $layout = $stmt->fetch();
            if ($layout) {
                $ids = json_decode($layout['layout_data'], true) ?: [];
                $ids = array_values(array_filter($ids, fn($x) => $x !== $id));
                $stmt2 = $this->db->prepare("UPDATE cms_layouts SET layout_data = ? WHERE page_name = 'home'");
                $stmt2->execute([json_encode($ids)]);
            }

            if (class_exists('\Core\Cache')) {
                Cache::delete('cms_layout_home');
                Cache::clear();
            }

            if ($isAjax) {
                header('Content-Type: application/json');
                echo json_encode(['success' => true]);
                exit;
            }

            Session::setFlash('success', 'Reels Container deleted.');
        }

        $this->redirect('/admin/cms/reels');
    }

    /**
     * Toggle active/draft status
     */
    public function toggleStatus()
    {
        $this->requirePermission('cms_reels', 'edit');
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            echo json_encode(['success' => false, 'message' => 'Method not allowed']);
            exit;
        }

        $id = trim($_POST['id'] ?? '');
        $cmp = $this->cmsModel->getById($id);

        if ($cmp) {
            $newStatus = $cmp['is_active'] ? 0 : 1;
            $this->cmsModel->update($id, [
                'content_data' => $cmp['content_data'],
                'is_active'    => $newStatus
            ]);

            if (class_exists('\Core\Cache')) {
                Cache::delete('cms_layout_home');
                Cache::clear();
            }

            echo json_encode(['success' => true, 'is_active' => $newStatus]);
            exit;
        }

        echo json_encode(['success' => false, 'message' => 'Reels Container not found']);
        exit;
    }

    /**
     * Process repeater reel items with Cloudinary image and video uploads
     */
    private function processReelItems(): array
    {
        $items = [];
        $postedTitles = $_POST['reel_title'] ?? [];

        if (!is_array($postedTitles) || empty($postedTitles)) {
            return [];
        }

        $uploader = new CloudinaryUploader();

        foreach ($postedTitles as $i => $rawTitle) {
            $itemTitle = trim((string)$rawTitle);
            $ctaUrl = trim((string)($_POST['reel_cta_url'][$i] ?? ''));
            $ctaText = trim((string)($_POST['reel_cta_text'][$i] ?? 'Shop Now'));
            $sortOrder = (int)($_POST['reel_sort_order'][$i] ?? ($i + 1));

            // 1. Process Desktop Image (Existing URL or uploaded file)
            $desktopImage = trim((string)($_POST['reel_existing_desktop_image'][$i] ?? ''));
            if (!empty($_POST['reel_desktop_image_url'][$i])) {
                $desktopImage = trim((string)$_POST['reel_desktop_image_url'][$i]);
            }
            if (!empty($_FILES['reel_desktop_image_file']['name'][$i]) && $_FILES['reel_desktop_image_file']['error'][$i] === UPLOAD_ERR_OK) {
                $tmp = $_FILES['reel_desktop_image_file']['tmp_name'][$i];
                $name = $_FILES['reel_desktop_image_file']['name'][$i];
                try {
                    $cloudRes = $uploader->uploadImage($tmp, null, $name);
                    if (!empty($cloudRes['secure_url'])) {
                        $desktopImage = $cloudRes['secure_url'];
                    }
                } catch (\Throwable $e) {
                    error_log("Reels Cloudinary desktop image upload failed: " . $e->getMessage());
                }
            }

            // 2. Process Mobile Image (Existing URL or uploaded file)
            $mobileImage = trim((string)($_POST['reel_existing_mobile_image'][$i] ?? ''));
            if (!empty($_POST['reel_mobile_image_url'][$i])) {
                $mobileImage = trim((string)$_POST['reel_mobile_image_url'][$i]);
            }
            if (!empty($_FILES['reel_mobile_image_file']['name'][$i]) && $_FILES['reel_mobile_image_file']['error'][$i] === UPLOAD_ERR_OK) {
                $tmp = $_FILES['reel_mobile_image_file']['tmp_name'][$i];
                $name = $_FILES['reel_mobile_image_file']['name'][$i];
                try {
                    $cloudRes = $uploader->uploadImage($tmp, null, $name);
                    if (!empty($cloudRes['secure_url'])) {
                        $mobileImage = $cloudRes['secure_url'];
                    }
                } catch (\Throwable $e) {
                    error_log("Reels Cloudinary mobile image upload failed: " . $e->getMessage());
                }
            }
            // Fallback mobile to desktop image if mobile is not separately provided
            if (empty($mobileImage) && !empty($desktopImage)) {
                $mobileImage = $desktopImage;
            }

            // 3. Process Video (Existing URL, direct URL, or uploaded video file to Cloudinary)
            $videoUrl = trim((string)($_POST['reel_existing_video_url'][$i] ?? ''));
            if (!empty($_POST['reel_video_url'][$i])) {
                $videoUrl = trim((string)$_POST['reel_video_url'][$i]);
            }
            if (!empty($_FILES['reel_video_file']['name'][$i]) && $_FILES['reel_video_file']['error'][$i] === UPLOAD_ERR_OK) {
                $tmp = $_FILES['reel_video_file']['tmp_name'][$i];
                $name = $_FILES['reel_video_file']['name'][$i];
                try {
                    $cloudRes = $uploader->uploadMedia($tmp, null, $name);
                    if (!empty($cloudRes['secure_url'])) {
                        $videoUrl = $cloudRes['secure_url'];
                    }
                } catch (\Throwable $e) {
                    error_log("Reels Cloudinary video upload failed: " . $e->getMessage());
                }
            }

            // Only add item if at least a video or image is present
            if (!empty($videoUrl) || !empty($desktopImage) || !empty($itemTitle)) {
                $items[] = [
                    'id'            => 'item_' . ($i + 1) . '_' . substr(md5(uniqid()), 0, 6),
                    'title'         => $itemTitle,
                    'cta_url'       => $ctaUrl,
                    'cta_text'      => $ctaText ?: 'Shop Now',
                    'desktop_image' => $desktopImage,
                    'mobile_image'  => $mobileImage,
                    'video_url'     => $videoUrl,
                    'sort_order'    => $sortOrder
                ];
            }
        }

        // Sort items by sort_order
        usort($items, fn($a, $b) => $a['sort_order'] <=> $b['sort_order']);

        return array_values($items);
    }

    /**
     * Automatically register newly created component into cms_layouts for home page
     */
    private function addToHomeLayout(string $componentId): void
    {
        $stmt = $this->db->prepare("SELECT layout_data FROM cms_layouts WHERE page_name = 'home'");
        $stmt->execute();
        $layout = $stmt->fetch();
        $componentIds = $layout && !empty($layout['layout_data'])
            ? (json_decode($layout['layout_data'], true) ?: [])
            : [];

        if (!in_array($componentId, $componentIds, true)) {
            $componentIds[] = $componentId;
        }

        if ($layout) {
            $update = $this->db->prepare("UPDATE cms_layouts SET layout_data = ? WHERE page_name = 'home'");
            $update->execute([json_encode($componentIds)]);
        } else {
            $insert = $this->db->prepare("INSERT INTO cms_layouts (page_name, layout_data) VALUES ('home', ?)");
            $insert->execute([json_encode($componentIds)]);
        }
    }
}
