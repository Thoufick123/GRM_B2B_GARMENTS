<?php
namespace App\Controllers\Admin;

use Core\Controller;
use App\Models\CmsComponent;
use Core\Session;

class TopBarController extends Controller
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
        $this->requirePermission('cms_topbars', 'view');
        $db = \Core\Database::getInstance();
        $stmt = $db->query("SELECT * FROM cms_components WHERE section_type = 'top_bar' ORDER BY updated_at DESC");
        $topbars = $stmt->fetchAll();

        foreach ($topbars as &$tb) {
            $tb['content'] = json_decode($tb['content_data'], true) ?: [];
        }

        $this->render('admin/cms/topbar/index', [
            'title' => 'Top Announcement Bars',
            'topbars' => $topbars
        ], 'admin');
    }

    public function create()
    {
        $this->requirePermission('cms_topbars', 'create');
        $this->render('admin/cms/topbar/create', [
            'title' => 'Create Top Announcement Bar'
        ], 'admin');
    }

    public function store()
    {
        $this->requirePermission('cms_topbars', 'create');
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/admin/cms/topbars');
        }

        $title = trim($_POST['title'] ?? 'Top Bar Configuration');
        $is_active = isset($_POST['is_active']) ? 1 : 0;

        $contentDataArray = [
            'title' => $title,
            'global_settings' => [
                'bg_color' => $_POST['bg_color'] ?? '#3f4c38',
                'text_color' => $_POST['text_color'] ?? '#ffffff',
                'mode' => $_POST['mode'] ?? 'slider', // slider, sticky, fade
                'speed' => (int)($_POST['speed'] ?? 5), // in seconds
                'height' => (int)($_POST['height'] ?? 38),
                'font_size' => (int)($_POST['font_size'] ?? 13),
                'show_social_icon' => isset($_POST['show_social_icon']) ? 1 : 0,
                'social_platform' => $_POST['social_platform'] ?? 'instagram',
                'social_url' => $_POST['social_url'] ?? '',
            ],
            'announcements' => []
        ];

        if (isset($_POST['announcement_text']) && is_array($_POST['announcement_text'])) {
            foreach ($_POST['announcement_text'] as $index => $text) {
                if (!empty(trim($text))) {
                    $contentDataArray['announcements'][] = [
                        'text' => trim($text),
                        'cta_url' => trim($_POST['announcement_cta_url'][$index] ?? ''),
                        'icon' => $_POST['announcement_icon'][$index] ?? 'none',
                        'active' => isset($_POST['announcement_active'][$index]) ? 1 : 0
                    ];
                }
            }
        }

        $contentData = json_encode($contentDataArray);
        $id = 'topbar_' . uniqid();

        // If newly created is active, deactivate others
        if ($is_active) {
            $db = \Core\Database::getInstance();
            $db->exec("UPDATE cms_components SET is_active = 0 WHERE section_type = 'top_bar'");
        }

        $this->cmsModel->create([
            'id' => $id,
            'section_type' => 'top_bar',
            'content_data' => $contentData,
            'is_active' => $is_active
        ]);

        \Core\Cache::delete('cms_layout_home');
        \Core\Cache::delete('cms_top_bar');
        \Core\Cache::delete('layout_main_components');
        Session::setFlash('success', 'Top Announcement Bar created successfully.');
        $this->redirect('/admin/cms/topbars');
    }

    public function edit()
    {
        $this->requirePermission('cms_topbars', 'edit');
        $id = $_GET['id'] ?? '';
        $topbar = $this->cmsModel->getById($id);

        if (!$topbar || $topbar['section_type'] !== 'top_bar') {
            Session::setFlash('error', 'Top Bar component not found.');
            $this->redirect('/admin/cms/topbars');
            return;
        }

        $topbar['content'] = json_decode($topbar['content_data'], true) ?: [];

        $this->render('admin/cms/topbar/edit', [
            'title' => 'Edit Top Announcement Bar',
            'topbar' => $topbar
        ], 'admin');
    }

    public function update()
    {
        $this->requirePermission('cms_topbars', 'edit');
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/admin/cms/topbars');
        }

        $id = $_POST['id'] ?? '';
        $topbar = $this->cmsModel->getById($id);

        if (!$topbar || $topbar['section_type'] !== 'top_bar') {
            Session::setFlash('error', 'Top Bar component not found.');
            $this->redirect('/admin/cms/topbars');
            return;
        }

        $title = trim($_POST['title'] ?? 'Top Bar Configuration');
        $is_active = isset($_POST['is_active']) ? 1 : 0;

        $contentDataArray = [
            'title' => $title,
            'global_settings' => [
                'bg_color' => $_POST['bg_color'] ?? '#3f4c38',
                'text_color' => $_POST['text_color'] ?? '#ffffff',
                'mode' => $_POST['mode'] ?? 'slider',
                'speed' => (int)($_POST['speed'] ?? 5),
                'height' => (int)($_POST['height'] ?? 38),
                'font_size' => (int)($_POST['font_size'] ?? 13),
                'show_social_icon' => isset($_POST['show_social_icon']) ? 1 : 0,
                'social_platform' => $_POST['social_platform'] ?? 'instagram',
                'social_url' => $_POST['social_url'] ?? '',
            ],
            'announcements' => []
        ];

        if (isset($_POST['announcement_text']) && is_array($_POST['announcement_text'])) {
            foreach ($_POST['announcement_text'] as $index => $text) {
                if (!empty(trim($text))) {
                    $contentDataArray['announcements'][] = [
                        'text' => trim($text),
                        'cta_url' => trim($_POST['announcement_cta_url'][$index] ?? ''),
                        'icon' => $_POST['announcement_icon'][$index] ?? 'none',
                        'active' => isset($_POST['announcement_active'][$index]) ? 1 : 0
                    ];
                }
            }
        }

        $contentData = json_encode($contentDataArray);

        // If setting this to active, deactivate other topbars
        if ($is_active) {
            $db = \Core\Database::getInstance();
            $stmt = $db->prepare("UPDATE cms_components SET is_active = 0 WHERE section_type = 'top_bar' AND id != ?");
            $stmt->execute([$id]);
        }

        $this->cmsModel->update($id, [
            'content_data' => $contentData,
            'is_active' => $is_active
        ]);

        \Core\Cache::delete('cms_layout_home');
        \Core\Cache::delete('cms_top_bar');
        \Core\Cache::delete('layout_main_components');
        Session::setFlash('success', 'Top Announcement Bar updated successfully.');
        $this->redirect('/admin/cms/topbars');
    }

    public function delete()
    {
        $this->requirePermission('cms_topbars', 'delete');
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/admin/cms/topbars');
        }

        $id = $_POST['id'] ?? '';
        if ($id) {
            $this->cmsModel->delete($id);
            \Core\Cache::delete('cms_layout_home');
            \Core\Cache::delete('cms_top_bar');
            \Core\Cache::delete('layout_main_components');
            Session::setFlash('success', 'Top Bar deleted.');
        }

        $this->redirect('/admin/cms/topbars');
    }
}
