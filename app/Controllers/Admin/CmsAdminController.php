<?php
namespace App\Controllers\Admin;

use Core\Controller;
use Core\Database;

class CmsAdminController extends Controller
{
    private \PDO $db;

    public function __construct()
    {
        parent::__construct();
        $role = \Core\Session::get('user_role');
        if (!$role) {
            $this->redirect('/login');
        }
        $this->db = Database::getInstance();
    }

    public function builder()
    {
        $this->requirePermission('cms_builder', 'view');
        // Fetch current layout
        $stmt = $this->db->prepare("SELECT layout_data FROM cms_layouts WHERE page_name = 'home'");
        $stmt->execute();
        $layout = $stmt->fetch();
        
        $componentIds = $layout ? json_decode($layout['layout_data'], true) : [];

        // Fetch all components
        $stmt = $this->db->query("SELECT * FROM cms_components");
        $allComponents = $stmt->fetchAll();

        // Organize components: Active ones in layout order, then inactive ones
        $activeComponents = [];
        $componentsMap = [];
        
        foreach ($allComponents as $cmp) {
            $componentsMap[$cmp['id']] = $cmp;
        }

        foreach ($componentIds as $id) {
            if (isset($componentsMap[$id])) {
                $activeComponents[] = $componentsMap[$id];
                unset($componentsMap[$id]);
            }
        }

        $inactiveComponents = array_values($componentsMap);

        // Decode content_data for display in builder
        foreach ($activeComponents as &$cmp) {
            $cmp['content'] = !empty($cmp['content_data']) ? json_decode($cmp['content_data'], true) : [];
        }
        foreach ($inactiveComponents as &$cmp) {
            $cmp['content'] = !empty($cmp['content_data']) ? json_decode($cmp['content_data'], true) : [];
        }

        $this->render('admin/cms/builder', [
            'title' => 'Storefront Layout Builder',
            'activeComponents' => $activeComponents,
            'inactiveComponents' => $inactiveComponents
        ], 'admin');
    }

    public function deleteComponent()
    {
        $this->requirePermission('cms_builder', 'edit');
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/admin/cms/builder');
        }

        $id = trim($_POST['id'] ?? '');
        $isAjax = !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';

        if (empty($id)) {
            if ($isAjax) { header('Content-Type: application/json'); echo json_encode(['success' => false, 'error' => 'No ID provided']); exit; }
            $this->redirect('/admin/cms/builder');
        }

        // Delete from cms_components
        $stmt = $this->db->prepare("DELETE FROM cms_components WHERE id = ?");
        $stmt->execute([$id]);

        // Also remove from layout if present
        $stmt2 = $this->db->prepare("SELECT layout_data FROM cms_layouts WHERE page_name = 'home'");
        $stmt2->execute();
        $layout = $stmt2->fetch();
        if ($layout) {
            $ids = json_decode($layout['layout_data'], true) ?? [];
            $ids = array_values(array_filter($ids, fn($x) => $x !== $id));
            $stmt3 = $this->db->prepare("UPDATE cms_layouts SET layout_data = ? WHERE page_name = 'home'");
            $stmt3->execute([json_encode($ids)]);
        }

        if (class_exists('\Core\Cache')) {
            \Core\Cache::delete('cms_layout_home');
            \Core\Cache::clear();
        }

        if ($isAjax) {
            header('Content-Type: application/json');
            echo json_encode(['success' => true]);
            exit;
        }

        \Core\Session::setFlash('success', 'Component deleted.');
        $this->redirect('/admin/cms/builder');
    }
}
