<?php
namespace App\Controllers\Admin;

use Core\Controller;
use App\Models\CmsComponent;
use Core\Session;

class AboutUsController extends Controller
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
        $this->requirePermission('cms_about_us', 'view');
        $db = \Core\Database::getInstance();
        $stmt = $db->query("SELECT * FROM cms_components WHERE section_type = 'about_us' ORDER BY id DESC");
        $aboutUsList = $stmt->fetchAll();

        foreach ($aboutUsList as &$aboutUs) {
            $aboutUs['content'] = json_decode($aboutUs['content_data'], true) ?: [];
        }

        $this->render('admin/cms/about_us/index', [
            'title' => 'About Us Settings',
            'aboutUsList' => $aboutUsList
        ], 'admin');
    }

    public function create()
    {
        $this->requirePermission('cms_about_us', 'create');
        $this->render('admin/cms/about_us/create', [
            'title' => 'Create About Us Component'
        ], 'admin');
    }

    public function store()
    {
        $this->requirePermission('cms_about_us', 'create');
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/admin/cms/about-us');
        }

        $is_active = isset($_POST['is_active']) ? 1 : 0;
        $contentDataArray = $this->buildContentArray();

        $db = \Core\Database::getInstance();
        $id = 'aboutus_' . uniqid();
        $stmt = $db->prepare(
            "INSERT INTO cms_components (id, section_type, content_data, is_active)
             VALUES (:id, 'about_us', :content_data, :is_active)"
        );
        $stmt->execute([
            ':id'           => $id,
            ':content_data' => json_encode($contentDataArray),
            ':is_active'    => $is_active,
        ]);

        \Core\Cache::delete('cms_about_us');
        \Core\Cache::delete('cms_layout_home');
        Session::setFlash('success', 'About Us component created successfully.');
        $ref = $_POST['ref'] ?? '';
        $this->redirect($ref === 'builder' ? '/admin/cms/builder' : '/admin/cms/about-us');
    }

    public function edit()
    {
        $this->requirePermission('cms_about_us', 'edit');
        $id = $_GET['id'] ?? null;
        if (!$id) $this->redirect('/admin/cms/about-us');

        $db   = \Core\Database::getInstance();
        $stmt = $db->prepare("SELECT * FROM cms_components WHERE id = :id AND section_type = 'about_us'");
        $stmt->execute([':id' => $id]);
        $aboutUs = $stmt->fetch();

        if (!$aboutUs) $this->redirect('/admin/cms/about-us');

        $aboutUs['content'] = json_decode($aboutUs['content_data'], true) ?: [];

        $this->render('admin/cms/about_us/edit', [
            'title'   => 'Edit About Us Component',
            'aboutUs' => $aboutUs
        ], 'admin');
    }

    public function update()
    {
        $this->requirePermission('cms_about_us', 'edit');
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/admin/cms/about-us');
        }

        $id        = $_POST['id'] ?? null;
        $is_active = isset($_POST['is_active']) ? 1 : 0;

        if (!$id) {
            Session::setFlash('error', 'Component ID is missing.');
            $this->redirect('/admin/cms/about-us');
        }

        $contentDataArray = $this->buildContentArray();

        $db   = \Core\Database::getInstance();
        $stmt = $db->prepare(
            "UPDATE cms_components SET content_data = :content_data, is_active = :is_active
             WHERE id = :id AND section_type = 'about_us'"
        );
        $stmt->execute([
            ':content_data' => json_encode($contentDataArray),
            ':is_active'    => $is_active,
            ':id'           => $id,
        ]);

        \Core\Cache::delete('cms_about_us');
        \Core\Cache::delete('cms_layout_home');
        Session::setFlash('success', 'About Us component updated successfully.');
        $ref = $_POST['ref'] ?? '';
        $this->redirect($ref === 'builder' ? '/admin/cms/builder' : '/admin/cms/about-us');
    }

    public function delete()
    {
        $this->requirePermission('cms_about_us', 'delete');
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/admin/cms/about-us');
        }

        $id = $_POST['id'] ?? null;
        if (!$id) $this->redirect('/admin/cms/about-us');

        $db   = \Core\Database::getInstance();
        $stmt = $db->prepare("DELETE FROM cms_components WHERE id = :id AND section_type = 'about_us'");
        $stmt->execute([':id' => $id]);

        \Core\Cache::delete('cms_about_us');
        \Core\Cache::delete('cms_layout_home');
        Session::setFlash('success', 'About Us component deleted successfully.');
        $this->redirect('/admin/cms/about-us');
    }

    // ─── Private Helpers ─────────────────────────────────────────────────────

    private function decodeIconValue(string $val): string
    {
        $val = trim($val);
        if ($val === '') return '';

        // If Base64 encoded by JS to bypass WAF/ModSecurity XSS filtering
        if (str_starts_with($val, 'BASE64:')) {
            $raw = substr($val, 7);
            $decoded = base64_decode($raw, true);
            if ($decoded !== false) {
                if (strpos($decoded, '%') !== false) {
                    $urldecoded = rawurldecode($decoded);
                    if ($urldecoded !== false) return trim($urldecoded);
                }
                return trim($decoded);
            }
        }

        return $val;
    }

    private function cleanColor(?string $val, string $default): string
    {
        if ($val === null) return $default;
        $val = trim($val);
        if ($val === '') return $default;
        if ($val[0] !== '#') {
            $val = '#' . $val;
        }
        // Expand 3-digit hex #rgb to #rrggbb
        if (preg_match('/^#([0-9a-fA-F])([0-9a-fA-F])([0-9a-fA-F])$/', $val, $m)) {
            $val = '#' . $m[1] . $m[1] . $m[2] . $m[2] . $m[3] . $m[3];
        }
        // Validate 6-digit hex
        if (preg_match('/^#[0-9a-fA-F]{6}$/', $val)) {
            return strtolower($val);
        }
        return $default;
    }

    private function buildContentArray(): array
    {
        $features = [];
        if (isset($_POST['feature_title']) && is_array($_POST['feature_title'])) {
            foreach ($_POST['feature_title'] as $idx => $title) {
                if (trim($title) === '') continue;
                $icon = trim($_POST['feature_icon'][$idx] ?? '');
                $features[] = [
                    'title'       => trim($title),
                    'icon'        => $this->decodeIconValue($icon),
                    'description' => trim($_POST['feature_description'][$idx] ?? ''),
                    'bg_color'    => $this->cleanColor($_POST['feature_bg_color'][$idx] ?? '', '#ffffff'),
                    'text_color'  => $this->cleanColor($_POST['feature_text_color'][$idx] ?? '', '#111827'),
                ];
            }
        }

        $faqs = [];
        if (isset($_POST['faq_question']) && is_array($_POST['faq_question'])) {
            foreach ($_POST['faq_question'] as $idx => $question) {
                if (trim($question) === '') continue;
                $faqs[] = [
                    'question' => trim($question),
                    'answer'   => trim($_POST['faq_answer'][$idx] ?? ''),
                ];
            }
        }

        $why_choose = [];
        if (isset($_POST['why_choose_title']) && is_array($_POST['why_choose_title'])) {
            foreach ($_POST['why_choose_title'] as $idx => $title) {
                if (trim($title) === '') continue;
                $icon = trim($_POST['why_choose_icon'][$idx] ?? '');
                $why_choose[] = [
                    'title'       => trim($title),
                    'icon'        => $this->decodeIconValue($icon),
                    'description' => trim($_POST['why_choose_description'][$idx] ?? ''),
                    'bg_color'    => $this->cleanColor($_POST['why_choose_bg_color'][$idx] ?? '', '#ffffff'),
                    'text_color'  => $this->cleanColor($_POST['why_choose_text_color'][$idx] ?? '', '#111827'),
                ];
            }
        }

        return [
            'internal_name' => trim($_POST['internal_name'] ?? 'About Us Component'),
            'hero' => [
                'title'       => trim($_POST['hero_title'] ?? ''),
                'subtitle'    => trim($_POST['hero_subtitle'] ?? ''),
                'description' => trim($_POST['hero_description'] ?? ''),
                'cta_text'    => trim($_POST['hero_cta_text'] ?? ''),
                'cta_url'     => trim($_POST['hero_cta_url'] ?? ''),
                'image'       => trim($_POST['hero_image'] ?? ''),
            ],
            'features_heading'   => trim($_POST['features_heading'] ?? $_POST['features_title'] ?? ''),
            'features'           => $features,
            'why_choose_heading' => trim($_POST['why_choose_heading'] ?? $_POST['why_choose_title'] ?? ''),
            'why_choose'         => $why_choose,
            'faq_heading'        => trim($_POST['faq_heading'] ?? $_POST['faq_title'] ?? ''),
            'faqs'               => $faqs,
            'theme' => [
                'bg_color'              => $this->cleanColor($_POST['theme_bg_color'] ?? '', '#fdfbf7'),
                'hero_text_color'       => $this->cleanColor($_POST['theme_hero_text_color'] ?? '', '#111827'),
                'hero_desc_color'       => $this->cleanColor($_POST['theme_hero_desc_color'] ?? '', '#4b5563'),
                'btn_bg_color'          => $this->cleanColor($_POST['theme_btn_bg_color'] ?? '', '#059669'),
                'btn_text_color'        => $this->cleanColor($_POST['theme_btn_text_color'] ?? '', '#ffffff'),
                'feature_bg_color'      => $this->cleanColor($_POST['theme_feature_bg_color'] ?? '', '#f0fdf4'),
                'feature_icon_color'    => $this->cleanColor($_POST['theme_feature_icon_color'] ?? '', '#059669'),
                'feature_text_color'    => $this->cleanColor($_POST['theme_feature_text_color'] ?? '', '#111827'),
                'faq_bg_color'          => $this->cleanColor($_POST['theme_faq_bg_color'] ?? '', '#ffffff'),
                'faq_text_color'        => $this->cleanColor($_POST['theme_faq_text_color'] ?? '', '#111827'),
                'faq_question_color'    => $this->cleanColor($_POST['theme_faq_question_color'] ?? $_POST['theme_faq_text_color'] ?? '', '#111827'),
                'faq_answer_color'      => $this->cleanColor($_POST['theme_faq_answer_color'] ?? '', '#4b5563'),
                'why_choose_bg_color'   => $this->cleanColor($_POST['theme_why_choose_bg_color'] ?? '', '#ffffff'),
                'why_choose_text_color' => $this->cleanColor($_POST['theme_why_choose_text_color'] ?? '', '#111827'),
                'why_choose_icon_color' => $this->cleanColor($_POST['theme_why_choose_icon_color'] ?? '', '#059669'),
            ]
        ];
    }
}
