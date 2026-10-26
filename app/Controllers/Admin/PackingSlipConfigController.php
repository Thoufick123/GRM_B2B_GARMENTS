<?php
namespace App\Controllers\Admin;

use Core\Controller;
use Core\Session;
use App\Models\PackingSlipConfig;

class PackingSlipConfigController extends Controller
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

    public function index()
    {
        $this->requirePermission('packing_slip_config', 'view');

        $config = PackingSlipConfig::get();

        // Get latest CMS logo for default if empty
        $db = \Core\Database::getInstance();
        $cmsLogo = '';
        try {
            $stmt = $db->query("SELECT content_data FROM cms_components WHERE section_type = 'navbar' AND is_active = 1 ORDER BY id DESC LIMIT 1");
            $comp = $stmt->fetch(\PDO::FETCH_ASSOC);
            if ($comp && !empty($comp['content_data'])) {
                $navData = json_decode($comp['content_data'], true);
                $cmsLogo = $navData['global_settings']['logo'] ?? $navData['logo'] ?? '';
            }
        } catch (\Throwable $e) {}

        if (empty($config['logo_url']) && !empty($cmsLogo)) {
            $config['logo_url'] = $cmsLogo;
        }

        $this->render('admin/orders/packing_slip_config', [
            'title'     => 'Packing Slip Configuration',
            'config'    => $config,
            'cmsLogo'   => $cmsLogo
        ], 'admin');
    }

    public function save()
    {
        $this->requirePermission('packing_slip_config', 'edit');

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/admin/orders/packing-slip-config');
            return;
        }

        $isAjax = (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest')
               || (isset($_SERVER['CONTENT_TYPE']) && strpos($_SERVER['CONTENT_TYPE'], 'application/json') !== false)
               || (isset($_POST['is_ajax']) && $_POST['is_ajax'] === '1');

        try {
            $input = $_POST;
            if (empty($input) && $json = file_get_contents('php://input')) {
                $input = json_decode($json, true) ?: [];
            }

            PackingSlipConfig::save($input);

            if ($isAjax) {
                header('Content-Type: application/json');
                echo json_encode([
                    'success' => true,
                    'message' => 'Packing slip configuration and layout format saved successfully!'
                ]);
                exit;
            }

            Session::setFlash('success', 'Packing slip configuration and layout format saved successfully!');
            $this->redirect('/admin/orders/packing-slip-config');
        } catch (\Throwable $e) {
            if ($isAjax) {
                header('Content-Type: application/json');
                http_response_code(500);
                echo json_encode([
                    'success' => false,
                    'message' => 'Failed to save configuration: ' . $e->getMessage()
                ]);
                exit;
            }

            Session::setFlash('error', 'Error saving configuration: ' . $e->getMessage());
            $this->redirect('/admin/orders/packing-slip-config');
        }
    }
}
