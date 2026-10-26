<?php
namespace App\Controllers\Admin;

use Core\Controller;
use Core\Database;
use Core\Session;
use Core\Cache;

class CmsReviewController extends Controller
{
    public function __construct()
    {
        parent::__construct();
        $role = Session::get('user_role');
        if (!$role) {
            Session::setFlash('error', 'Unauthorized access.');
            $this->redirect('/login');
        }
    }

    /**
     * List all review section components
     */
    public function index()
    {
        $this->requirePermission('cms_reviews', 'view');
        $db = Database::getInstance();
        $stmt = $db->query("SELECT * FROM cms_components WHERE section_type = 'reviews' ORDER BY id DESC");
        $sections = $stmt->fetchAll();

        foreach ($sections as &$sec) {
            $sec['content'] = json_decode($sec['content_data'], true) ?: [];
        }

        $this->render('admin/cms/reviews/index', [
            'title' => 'Review Sections',
            'sections' => $sections
        ], 'admin');
    }

    /**
     * Form to create a new review section component
     */
    public function create()
    {
        $this->requirePermission('cms_reviews', 'create');
        $reviews = $this->getAllApprovedReviews();

        $this->render('admin/cms/reviews/create', [
            'title' => 'Add Review Section',
            'reviews' => $reviews
        ], 'admin');
    }

    /**
     * Save new review section component
     */
    public function store()
    {
        $this->requirePermission('cms_reviews', 'create');
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/admin/cms/reviews');
        }

        $db = Database::getInstance();
        $id = 'reviews_' . uniqid();
        $isActive = isset($_POST['is_active']) ? 1 : 0;
        $contentData = $this->buildContentArray();

        $stmt = $db->prepare("
            INSERT INTO cms_components (id, section_type, content_data, is_active)
            VALUES (:id, 'reviews', :content_data, :is_active)
        ");
        $stmt->execute([
            ':id'           => $id,
            ':content_data' => json_encode($contentData),
            ':is_active'    => $isActive,
        ]);

        if (class_exists('Core\Cache')) {
            Cache::delete('cms_layout_home');
            Cache::delete('cms_layout_sections');
            Cache::delete('cms_reviews');
            Cache::clear();
        }

        Session::setFlash('success', 'Review section created successfully.');
        $this->redirect('/admin/cms/reviews');
    }

    /**
     * Form to edit an existing review section component
     */
    public function edit()
    {
        $this->requirePermission('cms_reviews', 'edit');
        $id = $_GET['id'] ?? null;
        if (!$id) {
            $this->redirect('/admin/cms/reviews');
        }

        $db = Database::getInstance();
        $stmt = $db->prepare("SELECT * FROM cms_components WHERE id = :id AND section_type = 'reviews'");
        $stmt->execute([':id' => $id]);
        $section = $stmt->fetch();

        if (!$section) {
            Session::setFlash('error', 'Review section not found.');
            $this->redirect('/admin/cms/reviews');
        }

        $section['content'] = json_decode($section['content_data'], true) ?: [];
        $reviews = $this->getAllApprovedReviews();

        $this->render('admin/cms/reviews/edit', [
            'title'   => 'Edit Review Section',
            'section' => $section,
            'reviews' => $reviews
        ], 'admin');
    }

    /**
     * Update an existing review section component
     */
    public function update()
    {
        $this->requirePermission('cms_reviews', 'edit');
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/admin/cms/reviews');
        }

        $id = $_POST['id'] ?? null;
        if (!$id) {
            $this->redirect('/admin/cms/reviews');
        }

        $db = Database::getInstance();
        $isActive = isset($_POST['is_active']) ? 1 : 0;
        $contentData = $this->buildContentArray();

        $stmt = $db->prepare("
            UPDATE cms_components
            SET content_data = :content_data,
                is_active    = :is_active
            WHERE id = :id AND section_type = 'reviews'
        ");
        $stmt->execute([
            ':id'           => $id,
            ':content_data' => json_encode($contentData),
            ':is_active'    => $isActive,
        ]);

        if (class_exists('Core\Cache')) {
            Cache::delete('cms_layout_home');
            Cache::delete('cms_layout_sections');
            Cache::delete('cms_reviews');
            Cache::clear();
        }

        Session::setFlash('success', 'Review section updated successfully.');
        $this->redirect('/admin/cms/reviews');
    }

    /**
     * Delete a review section component
     */
    public function delete()
    {
        $this->requirePermission('cms_reviews', 'delete');
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/admin/cms/reviews');
        }

        $id = $_POST['id'] ?? null;
        if (!$id) {
            $this->redirect('/admin/cms/reviews');
        }

        $db = Database::getInstance();
        $stmt = $db->prepare("DELETE FROM cms_components WHERE id = :id AND section_type = 'reviews'");
        $stmt->execute([':id' => $id]);

        if (class_exists('Core\Cache')) {
            Cache::delete('cms_layout_home');
            Cache::delete('cms_layout_sections');
            Cache::delete('cms_reviews');
        }

        Session::setFlash('success', 'Review section deleted successfully.');
        $this->redirect('/admin/cms/reviews');
    }

    /**
     * Helper to fetch all approved reviews from database
     */
    private function getAllApprovedReviews(): array
    {
        $db = Database::getInstance();
        $sql = "
            SELECT 
                r.*,
                p.name AS product_name,
                (SELECT image_path FROM product_images WHERE product_id = p.id AND is_primary = 1 LIMIT 1) AS product_image,
                u.name AS reviewer_name,
                u.email AS reviewer_email
            FROM reviews r
            JOIN products p ON r.product_id = p.id
            JOIN users u ON r.user_id = u.id
            ORDER BY r.created_at DESC
        ";
        try {
            $stmt = $db->query($sql);
            return $stmt->fetchAll() ?: [];
        } catch (\Exception $e) {
            return [];
        }
    }

    /**
     * Build content array from $_POST
     */
    private function buildContentArray(): array
    {
        $selectedReviewIds = isset($_POST['selected_review_ids']) && is_array($_POST['selected_review_ids'])
            ? array_map('intval', $_POST['selected_review_ids'])
            : [];

        $animation = $_POST['animation'] ?? 'fade';
        $validAnimations = ['fade', 'slide_right', 'slide_left', 'zoom'];
        if (!in_array($animation, $validAnimations)) {
            $animation = 'fade';
        }

        $desktopCount = !empty($_POST['desktop_count']) ? (int)$_POST['desktop_count'] : (!empty($_POST['cards_desktop']) ? (int)$_POST['cards_desktop'] : 3);
        $desktopCount = max(1, min(8, $desktopCount));

        $mobileCount = !empty($_POST['mobile_count']) ? (int)$_POST['mobile_count'] : (!empty($_POST['cards_mobile']) ? (int)$_POST['cards_mobile'] : (!empty($_POST['cards_tablet']) ? (int)$_POST['cards_tablet'] : 1));
        $mobileCount = max(1, min(4, $mobileCount));

        $tabletCount = !empty($_POST['cards_tablet']) ? (int)$_POST['cards_tablet'] : max(1, min(3, (int)round($desktopCount * 0.6)));

        return [
            'title'          => trim($_POST['title'] ?? ''),
            'subtitle'       => trim($_POST['subtitle'] ?? ''),
            'animation'      => $animation,
            'autoplay'       => isset($_POST['autoplay']) ? 1 : 0,
            'autoplay_speed' => max(2, min(15, (int)($_POST['autoplay_speed'] ?? 4))),
            'desktop_count'  => $desktopCount,
            'mobile_count'   => $mobileCount,
            'cards_desktop'  => $desktopCount,
            'cards_tablet'   => $tabletCount,
            'cards_mobile'   => $mobileCount,
            'bg_color'       => $this->normalizeHex($_POST['bg_color'] ?? '#ffffff', '#ffffff'),
            'card_bg_color'  => $this->normalizeHex($_POST['card_bg_color'] ?? '#ffffff', '#ffffff'),
            'title_color'    => $this->normalizeHex($_POST['title_color'] ?? '#111827', '#111827'),
            'subtitle_color' => $this->normalizeHex($_POST['subtitle_color'] ?? '#6b7280', '#6b7280'),
            'text_color'     => $this->normalizeHex($_POST['text_color'] ?? '#111827', '#111827'),
            'star_color'     => $this->normalizeHex($_POST['star_color'] ?? '#f59e0b', '#f59e0b'),
            'accent_color'   => $this->normalizeHex($_POST['accent_color'] ?? '#F25996', '#F25996'),
            'arrow_bg_color' => $this->normalizeHex($_POST['arrow_bg_color'] ?? '#ffffff', '#ffffff'),
            'arrow_icon_color' => $this->normalizeHex($_POST['arrow_icon_color'] ?? '#1f2937', '#1f2937'),
            'dot_color'      => $this->normalizeHex($_POST['dot_color'] ?? '#d1d5db', '#d1d5db'),
            'dot_active_color' => $this->normalizeHex($_POST['dot_active_color'] ?? '#F25996', '#F25996'),
            'selected_review_ids' => $selectedReviewIds,
            'show_product'   => isset($_POST['show_product']) ? 1 : 0,
            'show_verified'  => isset($_POST['show_verified']) ? 1 : 0,
            'show_arrows'    => isset($_POST['show_arrows']) ? 1 : 0,
            'show_dots'      => isset($_POST['show_dots']) ? 1 : 0,
            'container_height' => trim($_POST['container_height'] ?? 'auto') ?: 'auto',
            'mobile_container_height' => trim($_POST['mobile_container_height'] ?? 'auto') ?: 'auto',
        ];
    }

    private function normalizeHex(?string $color, string $default): string
    {
        $c = trim($color ?? '');
        if (empty($c)) return $default;
        if ($c[0] !== '#' && preg_match('/^[0-9a-fA-F]{3,8}$/', $c)) {
            $c = '#' . $c;
        }
        return $c;
    }
}
