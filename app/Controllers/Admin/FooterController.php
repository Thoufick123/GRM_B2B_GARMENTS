<?php
namespace App\Controllers\Admin;

use Core\Controller;
use App\Models\CmsComponent;
use Core\Session;

class FooterController extends Controller
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
        $this->requirePermission('cms_footers', 'view');
        $db = \Core\Database::getInstance();
        $stmt = $db->query("SELECT * FROM cms_components WHERE section_type = 'footer' ORDER BY id DESC");
        $footers = $stmt->fetchAll();

        foreach ($footers as &$footer) {
            $footer['content'] = json_decode($footer['content_data'], true) ?: [];
        }

        $this->render('admin/cms/footers/index', [
            'title' => 'Footer Settings',
            'footers' => $footers
        ], 'admin');
    }

    public function create()
    {
        $this->requirePermission('cms_footers', 'create');
        $this->render('admin/cms/footers/create', [
            'title' => 'Create Footer'
        ], 'admin');
    }

    public function store()
    {
        $this->requirePermission('cms_footers', 'create');
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/admin/cms/footers');
        }

        $title      = trim($_POST['title'] ?? 'Footer');
        $is_active  = isset($_POST['is_active']) ? 1 : 0;

        $contentDataArray = $this->buildContentArray();

        $db = \Core\Database::getInstance();
        $id = 'footer_' . uniqid();
        $stmt = $db->prepare(
            "INSERT INTO cms_components (id, section_type, content_data, is_active)
             VALUES (:id, 'footer', :content_data, :is_active)"
        );
        $stmt->execute([
            ':id'           => $id,
            ':content_data' => json_encode($contentDataArray),
            ':is_active'    => $is_active,
        ]);

        \Core\Cache::delete('cms_layout_home');
        \Core\Cache::delete('layout_main_components');
        $this->redirect('/admin/cms/footers');
    }

    public function edit()
    {
        $this->requirePermission('cms_footers', 'edit');
        $id = $_GET['id'] ?? null;
        if (!$id) $this->redirect('/admin/cms/footers');

        $db   = \Core\Database::getInstance();
        $stmt = $db->prepare("SELECT * FROM cms_components WHERE id = :id AND section_type = 'footer'");
        $stmt->execute([':id' => $id]);
        $footer = $stmt->fetch();

        if (!$footer) $this->redirect('/admin/cms/footers');

        $footer['content'] = json_decode($footer['content_data'], true) ?: [];

        $this->render('admin/cms/footers/edit', [
            'title'  => 'Edit Footer',
            'footer' => $footer
        ], 'admin');
    }

    public function update()
    {
        $this->requirePermission('cms_footers', 'edit');
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/admin/cms/footers');
        }

        $id        = $_POST['id'] ?? null;
        $is_active = isset($_POST['is_active']) ? 1 : 0;

        if (!$id) $this->redirect('/admin/cms/footers');

        $contentDataArray = $this->buildContentArray();

        $db   = \Core\Database::getInstance();
        $stmt = $db->prepare(
            "UPDATE cms_components SET content_data = :content_data, is_active = :is_active
             WHERE id = :id AND section_type = 'footer'"
        );
        $stmt->execute([
            ':content_data' => json_encode($contentDataArray),
            ':is_active'    => $is_active,
            ':id'           => $id,
        ]);

        \Core\Cache::delete('cms_layout_home');
        \Core\Cache::delete('layout_main_components');
        $this->redirect('/admin/cms/footers');
    }

    public function delete()
    {
        $this->requirePermission('cms_footers', 'delete');
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/admin/cms/footers');
        }

        $id = $_POST['id'] ?? null;
        if (!$id) $this->redirect('/admin/cms/footers');

        $db   = \Core\Database::getInstance();
        $stmt = $db->prepare("DELETE FROM cms_components WHERE id = :id AND section_type = 'footer'");
        $stmt->execute([':id' => $id]);

        \Core\Cache::delete('cms_layout_home');
        \Core\Cache::delete('layout_main_components');
        $this->redirect('/admin/cms/footers');
    }

    // ─── Private Helpers ─────────────────────────────────────────────────────

    private function buildContentArray(): array
    {
        // Color theme
        $bgType  = $_POST['bg_color_type']  ?? 'solid';
        $bgValue = $_POST['bg_color_value'] ?? '#1f2937';

        // Footer columns
        $columns = [];
        if (isset($_POST['col_title']) && is_array($_POST['col_title'])) {
            foreach ($_POST['col_title'] as $ci => $colTitle) {
                $links = [];
                $lbls  = $_POST['link_label'][$ci] ?? [];
                $urls  = $_POST['link_url'][$ci]   ?? [];
                foreach ($lbls as $li => $lbl) {
                    if (trim($lbl) === '') continue;
                    $links[] = [
                        'label' => trim($lbl),
                        'url'   => trim($urls[$li] ?? '#'),
                    ];
                }
                $columns[] = [
                    'title' => trim($colTitle),
                    'links' => $links,
                ];
            }
        }

        return [
            'title'       => trim($_POST['title'] ?? 'Footer'),
            'logo'        => trim($_POST['logo'] ?? ''),
            'description' => trim($_POST['description'] ?? ''),
            'copyright'   => trim($_POST['copyright'] ?? ''),
            'theme' => [
                'type'          => $bgType,
                'value'         => $bgValue,
                'text_color'    => trim($_POST['text_color']    ?? '#ffffff'),
                'link_color'    => trim($_POST['link_color']    ?? '#d1d5db'),
                'heading_color' => trim($_POST['heading_color'] ?? '#ffffff'),
            ],
            'contact' => [
                'email' => trim($_POST['contact_email'] ?? ''),
                'phone' => trim($_POST['contact_phone'] ?? ''),
            ],
            'maps' => [
                'address'   => trim($_POST['map_address'] ?? ''),
                'lat'       => trim($_POST['map_lat']     ?? ''),
                'lng'       => trim($_POST['map_lng']     ?? ''),
                'map_link'  => trim($_POST['map_link']    ?? ''),
            ],
            'social' => [
                'facebook'  => trim($_POST['social_facebook']  ?? ''),
                'instagram' => trim($_POST['social_instagram'] ?? ''),
                'youtube'   => trim($_POST['social_youtube']   ?? ''),
                'twitter'   => trim($_POST['social_twitter']   ?? ''),
                'pinterest' => trim($_POST['social_pinterest'] ?? ''),
            ],
            'columns' => $columns,
        ];
    }
}
