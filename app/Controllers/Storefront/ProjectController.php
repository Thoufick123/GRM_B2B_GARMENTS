<?php
namespace App\Controllers\Storefront;

use Core\Controller;
use Core\Database;

class ProjectController extends Controller
{
    /**
     * Display public storefront /project showcase page
     */
    public function index()
    {
        $db = Database::getInstance();
        $stmt = $db->query("
            SELECT * FROM cms_projects 
            WHERE is_active = 1 
            ORDER BY sort_order ASC, id DESC
        ");
        $projects = $stmt->fetchAll();

        foreach ($projects as &$project) {
            $project['images'] = json_decode($project['images'] ?? '[]', true) ?: [];
            $project['videos'] = json_decode($project['videos'] ?? '[]', true) ?: [];
        }

        $this->render('storefront/projects/index', [
            'title'           => 'Our Projects - Garment Manufacturing Showcase & Portfolio',
            'metaDescription' => 'Explore GRM B2B garment manufacturing portfolio, export orders, and custom apparel production case studies across domestic and international markets.',
            'metaKeywords'    => 'garment manufacturing portfolio, apparel export showcase, bulk apparel production cases, Tirupur garment factory projects',
            'projects'        => $projects
        ]);
    }
}
