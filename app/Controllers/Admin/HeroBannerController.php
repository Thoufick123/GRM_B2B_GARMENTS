<?php
namespace App\Controllers\Admin;

use Core\Controller;
use App\Models\CmsComponent;
use Core\Session;

class HeroBannerController extends Controller
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
        $this->requirePermission('cms_hero_banners', 'view');
        $banners = $this->cmsModel->getAllHeroBanners();
        
        // Decode JSON content
        foreach ($banners as &$banner) {
            $banner['content'] = json_decode($banner['content_data'], true) ?: [];
        }

        $this->render('admin/cms/hero_banners/index', [
            'title' => 'Hero Banners',
            'banners' => $banners
        ], 'admin');
    }

    public function create()
    {
        $this->requirePermission('cms_hero_banners', 'create');
        $this->render('admin/cms/hero_banners/create', [
            'title' => 'New Hero Banner'
        ], 'admin');
    }

    public function store()
    {
        $this->requirePermission('cms_hero_banners', 'create');
        $title = $_POST['title'] ?? '';
        $subtitle = $_POST['subtitle'] ?? '';
        $button_text = $_POST['button_text'] ?? '';
        $cta_url = $_POST['cta_url'] ?? '';
        $animation = $_POST['animation'] ?? 'fade';
        $sort_order = $_POST['sort_order'] ?? 0;
        $start_date = $_POST['start_date'] ?? '';
        $end_date = $_POST['end_date'] ?? '';
        $description = $_POST['description'] ?? '';
        $color_theme_type = $_POST['color_theme_type'] ?? 'solid';
        $color_theme_value = $_POST['color_theme_value'] ?? '#ffffff';
        $arrow_color = $_POST['arrow_color'] ?? '#000000';
        $arrow_bg_color = $_POST['arrow_bg_color'] ?? '#ffffff';
        $dot_color = $_POST['dot_color'] ?? '#8c6a4f';
        $title_color = $_POST['title_color'] ?? '#ffffff';
        $subtitle_color = $_POST['subtitle_color'] ?? '#ffffff';
        $description_color = $_POST['description_color'] ?? '#ffffff';
        $button_bg_color = $_POST['button_bg_color'] ?? '#111827';
        $button_text_color = $_POST['button_text_color'] ?? '#ffffff';
        $is_active = isset($_POST['is_active']) ? 1 : 0;
        
        $media_items = [];
        if (isset($_POST['media_desktop']) && is_array($_POST['media_desktop'])) {
            foreach ($_POST['media_desktop'] as $index => $desktop_url) {
                $desktop_url = trim((string)$desktop_url);
                $mobile_url = trim((string)($_POST['media_mobile'][$index] ?? ''));
                if ($desktop_url === '' && $mobile_url === '') continue;
                if ($desktop_url === '') $desktop_url = $mobile_url;
                if ($mobile_url === '') $mobile_url = $desktop_url;
                $opacity = isset($_POST['media_opacity'][$index]) && $_POST['media_opacity'][$index] !== '' ? (float)$_POST['media_opacity'][$index] : 100;
                $media_items[] = [
                    'type' => $_POST['media_type'][$index] ?? 'image',
                    'desktop' => $desktop_url,
                    'mobile' => $mobile_url,
                    'opacity' => max(0, min(100, $opacity))
                ];
            }
        }

        $show_arrows = isset($_POST['show_arrows']) ? true : false;
        $show_dots = isset($_POST['show_dots']) ? true : false;

        $contentData = json_encode([
            'title' => $title,
            'subtitle' => $subtitle,
            'button_text' => $button_text,
            'cta_url' => $cta_url,
            'animation' => $animation,
            'show_arrows' => $show_arrows,
            'show_dots' => $show_dots,
            'sort_order' => (int)$sort_order,
            'start_date' => $start_date,
            'end_date' => $end_date,
            'description' => $description,
            'container_height' => $_POST['container_height'] ?? '',
            'mobile_container_height' => $_POST['mobile_container_height'] ?? '',
            'font_size' => $_POST['font_size'] ?? '',
            'icon_size' => $_POST['icon_size'] ?? '',
            'image_fit' => $_POST['image_fit'] ?? 'cover',
            'theme' => [
                'type' => $color_theme_type,
                'value' => $color_theme_value,
                'arrow_color' => $arrow_color,
                'arrow_bg_color' => $arrow_bg_color,
                'dot_color' => $dot_color,
                'title_color' => $title_color,
                'subtitle_color' => $subtitle_color,
                'description_color' => $description_color,
                'button_bg_color' => $button_bg_color,
                'button_text_color' => $button_text_color
            ],
            'media' => $media_items
        ]);

        $id = 'hero_banner_' . uniqid();

        $this->cmsModel->create([
            'id' => $id,
            'section_type' => 'hero_banner',
            'content_data' => $contentData,
            'is_active' => $is_active
        ]);

        $this->addToHomeLayout($id);

        \Core\Cache::delete('cms_layout_home');

        Session::setFlash('success', 'Hero Banner created successfully.');
        $this->redirect('/admin/cms/hero-banners');
    }

    private function addToHomeLayout(string $componentId): void
    {
        $db = \Core\Database::getInstance();
        $stmt = $db->prepare("SELECT layout_data FROM cms_layouts WHERE page_name = 'home'");
        $stmt->execute();
        $layout = $stmt->fetch();
        $componentIds = $layout && !empty($layout['layout_data'])
            ? (json_decode($layout['layout_data'], true) ?: [])
            : [];

        if (!in_array($componentId, $componentIds, true)) {
            $componentIds[] = $componentId;
        }

        if ($layout) {
            $update = $db->prepare("UPDATE cms_layouts SET layout_data = ? WHERE page_name = 'home'");
            $update->execute([json_encode($componentIds)]);
        } else {
            $insert = $db->prepare("INSERT INTO cms_layouts (page_name, layout_data) VALUES ('home', ?)");
            $insert->execute([json_encode($componentIds)]);
        }
    }

    public function edit()
    {
        $this->requirePermission('cms_hero_banners', 'edit');
        $id = $_GET['id'] ?? '';
        $banner = $this->cmsModel->getById($id);
        
        if (!$banner || $banner['section_type'] !== 'hero_banner') {
            Session::setFlash('error', 'Hero Banner not found.');
            $this->redirect('/admin/cms/hero-banners');
            return;
        }

        $banner['content'] = json_decode($banner['content_data'], true) ?: [];

        $this->render('admin/cms/hero_banners/edit', [
            'title' => 'Edit Hero Banner',
            'banner' => $banner
        ], 'admin');
    }

    public function update()
    {
        $this->requirePermission('cms_hero_banners', 'edit');
        $id = $_POST['id'] ?? '';
        $banner = $this->cmsModel->getById($id);
        
        if (!$banner || $banner['section_type'] !== 'hero_banner') {
            Session::setFlash('error', 'Hero Banner not found.');
            $this->redirect('/admin/cms/hero-banners');
            return;
        }

        $title = $_POST['title'] ?? '';
        $subtitle = $_POST['subtitle'] ?? '';
        $button_text = $_POST['button_text'] ?? '';
        $cta_url = $_POST['cta_url'] ?? '';
        $animation = $_POST['animation'] ?? 'fade';
        $sort_order = $_POST['sort_order'] ?? 0;
        $start_date = $_POST['start_date'] ?? '';
        $end_date = $_POST['end_date'] ?? '';
        $description = $_POST['description'] ?? '';
        $color_theme_type = $_POST['color_theme_type'] ?? 'solid';
        $color_theme_value = $_POST['color_theme_value'] ?? '#ffffff';
        $arrow_color = $_POST['arrow_color'] ?? '#000000';
        $arrow_bg_color = $_POST['arrow_bg_color'] ?? '#ffffff';
        $dot_color = $_POST['dot_color'] ?? '#8c6a4f';
        $title_color = $_POST['title_color'] ?? '#ffffff';
        $subtitle_color = $_POST['subtitle_color'] ?? '#ffffff';
        $description_color = $_POST['description_color'] ?? '#ffffff';
        $button_bg_color = $_POST['button_bg_color'] ?? '#111827';
        $button_text_color = $_POST['button_text_color'] ?? '#ffffff';
        $is_active = isset($_POST['is_active']) ? 1 : 0;
        
        $media_items = [];
        if (isset($_POST['media_desktop']) && is_array($_POST['media_desktop'])) {
            foreach ($_POST['media_desktop'] as $index => $desktop_url) {
                $desktop_url = trim((string)$desktop_url);
                $mobile_url = trim((string)($_POST['media_mobile'][$index] ?? ''));
                if ($desktop_url === '' && $mobile_url === '') continue;
                if ($desktop_url === '') $desktop_url = $mobile_url;
                if ($mobile_url === '') $mobile_url = $desktop_url;
                $opacity = isset($_POST['media_opacity'][$index]) && $_POST['media_opacity'][$index] !== '' ? (float)$_POST['media_opacity'][$index] : 100;
                $media_items[] = [
                    'type' => $_POST['media_type'][$index] ?? 'image',
                    'desktop' => $desktop_url,
                    'mobile' => $mobile_url,
                    'opacity' => max(0, min(100, $opacity))
                ];
            }
        }

        $show_arrows = isset($_POST['show_arrows']) ? true : false;
        $show_dots = isset($_POST['show_dots']) ? true : false;

        $contentData = json_encode([
            'title' => $title,
            'subtitle' => $subtitle,
            'button_text' => $button_text,
            'cta_url' => $cta_url,
            'animation' => $animation,
            'show_arrows' => $show_arrows,
            'show_dots' => $show_dots,
            'sort_order' => (int)$sort_order,
            'start_date' => $start_date,
            'end_date' => $end_date,
            'description' => $description,
            'container_height' => $_POST['container_height'] ?? '',
            'mobile_container_height' => $_POST['mobile_container_height'] ?? '',
            'font_size' => $_POST['font_size'] ?? '',
            'icon_size' => $_POST['icon_size'] ?? '',
            'image_fit' => $_POST['image_fit'] ?? 'cover',
            'theme' => [
                'type' => $color_theme_type,
                'value' => $color_theme_value,
                'arrow_color' => $arrow_color,
                'arrow_bg_color' => $arrow_bg_color,
                'dot_color' => $dot_color,
                'title_color' => $title_color,
                'subtitle_color' => $subtitle_color,
                'description_color' => $description_color,
                'button_bg_color' => $button_bg_color,
                'button_text_color' => $button_text_color
            ],
            'media' => $media_items
        ]);

        $this->cmsModel->update($id, [
            'content_data' => $contentData,
            'is_active' => $is_active
        ]);

        \Core\Cache::delete('cms_layout_home');

        Session::setFlash('success', 'Hero Banner updated successfully.');
        $this->redirect('/admin/cms/hero-banners');
    }

    public function delete()
    {
        $this->requirePermission('cms_hero_banners', 'delete');
        $id = $_POST['id'] ?? '';
        $this->cmsModel->delete($id);
        \Core\Cache::delete('cms_layout_home');
        Session::setFlash('success', 'Hero Banner deleted.');
        $this->redirect('/admin/cms/hero-banners');
    }
}
