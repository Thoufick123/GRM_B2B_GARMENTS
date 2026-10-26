<?php
namespace App\Controllers\Admin;

use Core\Controller;
use App\Models\CmsComponent;
use Core\Session;

class MainBannerController extends Controller
{
    private CmsComponent $cmsModel;

    public function __construct()
    {
        parent::__construct();
        $role = Session::get('user_role');
        if (!$role) {
            Session::setFlash('error', 'Unauthorized access.');
            $this->redirect('/login');
        }
        $this->cmsModel = new CmsComponent();
    }

    public function index()
    {
        $this->requirePermission('cms_main_banners', 'view');
        // Get components by section_type = 'main_banner'
        $db = \Core\Database::getInstance();
        $stmt = $db->query("SELECT * FROM cms_components WHERE section_type = 'main_banner'");
        $banners = $stmt->fetchAll();
        
        foreach ($banners as &$banner) {
            $banner['content'] = json_decode($banner['content_data'], true) ?: [];
        }

        $this->render('admin/cms/main_banners/index', [
            'title' => 'Main Banners',
            'banners' => $banners
        ], 'admin');
    }

    public function create()
    {
        $this->requirePermission('cms_main_banners', 'create');
        $db = \Core\Database::getInstance();
        $previewProducts = $db->query("SELECT id, name, slug, (SELECT image_path FROM product_images WHERE product_id = products.id AND is_primary = 1 LIMIT 1) as main_image FROM products WHERE status = 'active' ORDER BY id DESC")->fetchAll();
        $previewCategories = $db->query("SELECT id, name, slug, image_path as image FROM categories WHERE status = 'active' ORDER BY name ASC")->fetchAll();
        $previewCustomProducts = $db->query("
            SELECT p.id, p.name, p.slug, fc.id as customization_id, fc.program_name, fc.garment_type,
                   (SELECT pi.image_path FROM product_images pi WHERE pi.product_id = p.id ORDER BY pi.is_primary DESC, pi.sort_order ASC, pi.id ASC LIMIT 1) as main_image
            FROM products p
            JOIN fabric_customizations fc ON fc.fabric_id = p.id
            WHERE p.status = 'active' AND p.is_customizable = 1 AND fc.status = 'active' AND fc.customization_enabled = 1
            ORDER BY p.id DESC
        ")->fetchAll();
        $previewCustomCategories = $db->query("
            SELECT DISTINCT c.id, c.name, c.slug, c.image_path as image
            FROM categories c
            JOIN products p ON (p.category_id = c.id)
            JOIN fabric_customizations fc ON (fc.fabric_id = p.id)
            WHERE c.status = 'active' AND p.status = 'active' AND p.is_customizable = 1 AND fc.status = 'active' AND fc.customization_enabled = 1
            ORDER BY c.name ASC
        ")->fetchAll();

        $this->render('admin/cms/main_banners/create', [
            'title' => 'New Main Banner',
            'previewProducts' => $previewProducts,
            'previewCategories' => $previewCategories,
            'previewCustomProducts' => $previewCustomProducts,
            'previewCustomCategories' => $previewCustomCategories
        ], 'admin');
    }

    public function store()
    {
        $this->requirePermission('cms_main_banners', 'create');
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/admin/cms/main-banners');
        }

        $title = $_POST['title'] ?? '';
        $is_active = isset($_POST['is_active']) ? 1 : 0;
        
        $sections = ['left', 'right_top', 'right_bottom'];
        $contentDataArray = [
            'title' => $title,
            'container_height' => $_POST['container_height'] ?? '80vh',
            'mobile_container_height' => $_POST['mobile_container_height'] ?? '100vh',
            'theme' => [
                'type' => $_POST['main_color_theme_type'] ?? 'solid',
                'value' => $_POST['main_color_theme_value'] ?? 'transparent'
            ]
        ];

        foreach ($sections as $prefix) {
            $media_items = [];
            if (isset($_POST[$prefix . '_media_desktop']) && is_array($_POST[$prefix . '_media_desktop'])) {
                foreach ($_POST[$prefix . '_media_desktop'] as $index => $desktop_url) {
                    $desktop_url = trim((string)$desktop_url);
                    $mobile_url = trim((string)($_POST[$prefix . '_media_mobile'][$index] ?? ''));
                    if ($desktop_url === '' && $mobile_url === '') continue;
                    if ($desktop_url === '') $desktop_url = $mobile_url;
                    if ($mobile_url === '') $mobile_url = $desktop_url;
                    $opacity = isset($_POST[$prefix . '_media_opacity'][$index]) && $_POST[$prefix . '_media_opacity'][$index] !== '' ? (float)$_POST[$prefix . '_media_opacity'][$index] : 100;
                    $media_items[] = [
                        'type' => $_POST[$prefix . '_media_type'][$index] ?? 'image',
                        'desktop' => $desktop_url,
                        'mobile' => $mobile_url,
                        'opacity' => max(0, min(100, $opacity))
                    ];
                }
            }

            $contentDataArray[$prefix] = [
                'title' => $_POST[$prefix . '_title'] ?? '',
                'subtitle' => $_POST[$prefix . '_subtitle'] ?? '',
                'description' => $_POST[$prefix . '_description'] ?? '',
                'button_text' => $_POST[$prefix . '_button_text'] ?? '',
                'cta_url' => $_POST[$prefix . '_cta_url'] ?? '',
                'animation' => $_POST[$prefix . '_animation'] ?? 'fade',
                'show_arrows' => isset($_POST[$prefix . '_show_arrows']) ? true : false,
                'show_dots' => isset($_POST[$prefix . '_show_dots']) ? true : false,
                'start_date' => $_POST[$prefix . '_start_date'] ?? '',
                'end_date' => $_POST[$prefix . '_end_date'] ?? '',
                'content_type' => $_POST[$prefix . '_content_type'] ?? 'custom_image',
                'image_fit' => $_POST[$prefix . '_image_fit'] ?? 'cover',
                'theme' => [
                    'type' => $_POST[$prefix . '_color_theme_type'] ?? 'solid',
                    'value' => $_POST[$prefix . '_color_theme_value'] ?? '#ffffff',
                    'arrow_color' => $_POST[$prefix . '_arrow_color'] ?? '#000000',
                    'arrow_bg_color' => $_POST[$prefix . '_arrow_bg_color'] ?? '#ffffff',
                    'dot_color' => $_POST[$prefix . '_dot_color'] ?? '#8c6a4f',
                    'title_color' => $_POST[$prefix . '_title_color'] ?? '#ffffff',
                    'subtitle_color' => $_POST[$prefix . '_subtitle_color'] ?? '#ffffff',
                    'description_color' => $_POST[$prefix . '_description_color'] ?? '#ffffff',
                    'button_bg_color' => $_POST[$prefix . '_button_bg_color'] ?? '#111827',
                    'button_text_color' => $_POST[$prefix . '_button_text_color'] ?? '#ffffff'
                ],
                'alignment' => $_POST[$prefix . '_alignment'] ?? 'center',
                'media' => $media_items,
                'selected_products' => $_POST[$prefix . '_selected_products'] ?? [],
                'selected_categories' => $_POST[$prefix . '_selected_categories'] ?? [],
                'selected_custom_products' => $_POST[$prefix . '_selected_custom_products'] ?? [],
                'selected_custom_categories' => $_POST[$prefix . '_selected_custom_categories'] ?? []
            ];
        }

        $contentData = json_encode($contentDataArray);

        $id = 'main_banner_' . uniqid();

        $this->cmsModel->create([
            'id' => $id,
            'section_type' => 'main_banner',
            'content_data' => $contentData,
            'is_active' => $is_active
        ]);

        \Core\Cache::delete('cms_layout_home');
        Session::setFlash('success', 'Main Banner created successfully.');
        $this->redirect('/admin/cms/main-banners');
    }

    public function edit()
    {
        $this->requirePermission('cms_main_banners', 'edit');
        $id = $_GET['id'] ?? '';
        $banner = $this->cmsModel->getById($id);
        
        if (!$banner || $banner['section_type'] !== 'main_banner') {
            Session::setFlash('error', 'Main Banner not found.');
            $this->redirect('/admin/cms/main-banners');
            return;
        }

        $banner['content'] = json_decode($banner['content_data'], true) ?: [];

        $db = \Core\Database::getInstance();
        $previewProducts = $db->query("SELECT id, name, slug, (SELECT image_path FROM product_images WHERE product_id = products.id AND is_primary = 1 LIMIT 1) as main_image FROM products WHERE status = 'active' ORDER BY id DESC")->fetchAll();
        $previewCategories = $db->query("SELECT id, name, slug, image_path as image FROM categories WHERE status = 'active' ORDER BY name ASC")->fetchAll();
        $previewCustomProducts = $db->query("
            SELECT p.id, p.name, p.slug, fc.id as customization_id, fc.program_name, fc.garment_type,
                   (SELECT pi.image_path FROM product_images pi WHERE pi.product_id = p.id ORDER BY pi.is_primary DESC, pi.sort_order ASC, pi.id ASC LIMIT 1) as main_image
            FROM products p
            JOIN fabric_customizations fc ON fc.fabric_id = p.id
            WHERE p.status = 'active' AND p.is_customizable = 1 AND fc.status = 'active' AND fc.customization_enabled = 1
            ORDER BY p.id DESC
        ")->fetchAll();
        $previewCustomCategories = $db->query("
            SELECT DISTINCT c.id, c.name, c.slug, c.image_path as image
            FROM categories c
            JOIN products p ON (p.category_id = c.id)
            JOIN fabric_customizations fc ON (fc.fabric_id = p.id)
            WHERE c.status = 'active' AND p.status = 'active' AND p.is_customizable = 1 AND fc.status = 'active' AND fc.customization_enabled = 1
            ORDER BY c.name ASC
        ")->fetchAll();

        $this->render('admin/cms/main_banners/edit', [
            'title' => 'Edit Main Banner',
            'banner' => $banner,
            'previewProducts' => $previewProducts,
            'previewCategories' => $previewCategories,
            'previewCustomProducts' => $previewCustomProducts,
            'previewCustomCategories' => $previewCustomCategories
        ], 'admin');
    }

    public function update()
    {
        $this->requirePermission('cms_main_banners', 'edit');
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/admin/cms/main-banners');
        }

        $id = $_POST['id'] ?? '';
        $banner = $this->cmsModel->getById($id);
        
        if (!$banner || $banner['section_type'] !== 'main_banner') {
            Session::setFlash('error', 'Main Banner not found.');
            $this->redirect('/admin/cms/main-banners');
            return;
        }

        $title = $_POST['title'] ?? '';
        $is_active = isset($_POST['is_active']) ? 1 : 0;
        
        $sections = ['left', 'right_top', 'right_bottom'];
        $contentDataArray = [
            'title' => $title,
            'container_height' => $_POST['container_height'] ?? '80vh',
            'mobile_container_height' => $_POST['mobile_container_height'] ?? '100vh',
            'theme' => [
                'type' => $_POST['main_color_theme_type'] ?? 'solid',
                'value' => $_POST['main_color_theme_value'] ?? 'transparent'
            ]
        ];

        foreach ($sections as $prefix) {
            $media_items = [];
            if (isset($_POST[$prefix . '_media_desktop']) && is_array($_POST[$prefix . '_media_desktop'])) {
                foreach ($_POST[$prefix . '_media_desktop'] as $index => $desktop_url) {
                    $desktop_url = trim((string)$desktop_url);
                    $mobile_url = trim((string)($_POST[$prefix . '_media_mobile'][$index] ?? ''));
                    if ($desktop_url === '' && $mobile_url === '') continue;
                    if ($desktop_url === '') $desktop_url = $mobile_url;
                    if ($mobile_url === '') $mobile_url = $desktop_url;
                    $opacity = isset($_POST[$prefix . '_media_opacity'][$index]) && $_POST[$prefix . '_media_opacity'][$index] !== '' ? (float)$_POST[$prefix . '_media_opacity'][$index] : 100;
                    $media_items[] = [
                        'type' => $_POST[$prefix . '_media_type'][$index] ?? 'image',
                        'desktop' => $desktop_url,
                        'mobile' => $mobile_url,
                        'opacity' => max(0, min(100, $opacity))
                    ];
                }
            }

            $contentDataArray[$prefix] = [
                'title' => $_POST[$prefix . '_title'] ?? '',
                'subtitle' => $_POST[$prefix . '_subtitle'] ?? '',
                'description' => $_POST[$prefix . '_description'] ?? '',
                'button_text' => $_POST[$prefix . '_button_text'] ?? '',
                'cta_url' => $_POST[$prefix . '_cta_url'] ?? '',
                'animation' => $_POST[$prefix . '_animation'] ?? 'fade',
                'show_arrows' => isset($_POST[$prefix . '_show_arrows']) ? true : false,
                'show_dots' => isset($_POST[$prefix . '_show_dots']) ? true : false,
                'start_date' => $_POST[$prefix . '_start_date'] ?? '',
                'end_date' => $_POST[$prefix . '_end_date'] ?? '',
                'content_type' => $_POST[$prefix . '_content_type'] ?? 'custom_image',
                'image_fit' => $_POST[$prefix . '_image_fit'] ?? 'cover',
                'theme' => [
                    'type' => $_POST[$prefix . '_color_theme_type'] ?? 'solid',
                    'value' => $_POST[$prefix . '_color_theme_value'] ?? '#ffffff',
                    'arrow_color' => $_POST[$prefix . '_arrow_color'] ?? '#000000',
                    'arrow_bg_color' => $_POST[$prefix . '_arrow_bg_color'] ?? '#ffffff',
                    'dot_color' => $_POST[$prefix . '_dot_color'] ?? '#8c6a4f',
                    'title_color' => $_POST[$prefix . '_title_color'] ?? '#ffffff',
                    'subtitle_color' => $_POST[$prefix . '_subtitle_color'] ?? '#ffffff',
                    'description_color' => $_POST[$prefix . '_description_color'] ?? '#ffffff',
                    'button_bg_color' => $_POST[$prefix . '_button_bg_color'] ?? '#111827',
                    'button_text_color' => $_POST[$prefix . '_button_text_color'] ?? '#ffffff'
                ],
                'alignment' => $_POST[$prefix . '_alignment'] ?? 'center',
                'media' => $media_items,
                'selected_products' => $_POST[$prefix . '_selected_products'] ?? [],
                'selected_categories' => $_POST[$prefix . '_selected_categories'] ?? [],
                'selected_custom_products' => $_POST[$prefix . '_selected_custom_products'] ?? [],
                'selected_custom_categories' => $_POST[$prefix . '_selected_custom_categories'] ?? []
            ];
        }

        $contentData = json_encode($contentDataArray);

        $this->cmsModel->update($id, [
            'content_data' => $contentData,
            'is_active' => $is_active
        ]);

        \Core\Cache::delete('cms_layout_home');
        Session::setFlash('success', 'Main Banner updated successfully.');
        $this->redirect('/admin/cms/main-banners');
    }

    public function delete()
    {
        $this->requirePermission('cms_main_banners', 'delete');
        $id = $_POST['id'] ?? '';
        $banner = $this->cmsModel->getById($id);
        if ($banner && $banner['section_type'] === 'main_banner') {
            $this->cmsModel->delete($id);
            \Core\Cache::delete('cms_layout_home');
            Session::setFlash('success', 'Main Banner deleted.');
        }
        $this->redirect('/admin/cms/main-banners');
    }
}
