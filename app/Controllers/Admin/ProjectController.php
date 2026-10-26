<?php
namespace App\Controllers\Admin;

use Core\Controller;
use Core\Database;
use Core\Session;
use Core\Cache;
use App\Core\CloudinaryUploader;

class ProjectController extends Controller
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
     * List all projects
     */
    public function index()
    {
        $this->requirePermission('cms_projects', 'view');
        $db = Database::getInstance();
        $stmt = $db->query("SELECT * FROM cms_projects ORDER BY sort_order ASC, id DESC");
        $projects = $stmt->fetchAll();

        foreach ($projects as &$project) {
            $project['images'] = json_decode($project['images'] ?? '[]', true) ?: [];
            $project['videos'] = json_decode($project['videos'] ?? '[]', true) ?: [];
        }

        $this->render('admin/cms/projects/index', [
            'title'    => 'Our Projects - CMS Builder',
            'projects' => $projects
        ], 'admin');
    }

    /**
     * Form to add a new project
     */
    public function create()
    {
        $this->requirePermission('cms_projects', 'create');
        $this->render('admin/cms/projects/create', [
            'title' => 'Add New Project'
        ], 'admin');
    }

    /**
     * Store new project
     */
    public function store()
    {
        $this->requirePermission('cms_projects', 'create');
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/admin/cms/projects');
        }

        $title       = trim($_POST['title'] ?? '');
        $subtitle    = trim($_POST['subtitle'] ?? '');
        $description = trim($_POST['description'] ?? '');
        $sortOrder   = (int)($_POST['sort_order'] ?? 0);
        $isActive    = isset($_POST['is_active']) ? 1 : 0;

        if (empty($title)) {
            Session::setFlash('error', 'Project Title is required.');
            $this->redirect('/admin/cms/projects/create');
        }

        // 1. Process Images
        $images = $this->collectImages();

        // 2. Process Videos
        $videos = $this->collectVideos();

        $db = Database::getInstance();
        $stmt = $db->prepare("
            INSERT INTO cms_projects (title, subtitle, description, images, videos, sort_order, is_active)
            VALUES (:title, :subtitle, :description, :images, :videos, :sort_order, :is_active)
        ");

        $stmt->execute([
            ':title'       => $title,
            ':subtitle'    => $subtitle,
            ':description' => $description,
            ':images'      => json_encode($images),
            ':videos'      => json_encode($videos),
            ':sort_order'  => $sortOrder,
            ':is_active'   => $isActive
        ]);

        if (class_exists('Core\Cache')) {
            Cache::delete('cms_storefront_projects');
        }

        Session::setFlash('success', 'Project created successfully.');
        $this->redirect('/admin/cms/projects');
    }

    /**
     * Form to edit an existing project
     */
    public function edit()
    {
        $this->requirePermission('cms_projects', 'edit');
        $id = (int)($_GET['id'] ?? 0);
        if (!$id) {
            $this->redirect('/admin/cms/projects');
        }

        $db = Database::getInstance();
        $stmt = $db->prepare("SELECT * FROM cms_projects WHERE id = :id");
        $stmt->execute([':id' => $id]);
        $project = $stmt->fetch();

        if (!$project) {
            Session::setFlash('error', 'Project not found.');
            $this->redirect('/admin/cms/projects');
        }

        $project['images'] = json_decode($project['images'] ?? '[]', true) ?: [];
        $project['videos'] = json_decode($project['videos'] ?? '[]', true) ?: [];

        $this->render('admin/cms/projects/edit', [
            'title'   => 'Edit Project',
            'project' => $project
        ], 'admin');
    }

    /**
     * Update project
     */
    public function update()
    {
        $this->requirePermission('cms_projects', 'edit');
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/admin/cms/projects');
        }

        $id          = (int)($_POST['id'] ?? 0);
        $title       = trim($_POST['title'] ?? '');
        $subtitle    = trim($_POST['subtitle'] ?? '');
        $description = trim($_POST['description'] ?? '');
        $sortOrder   = (int)($_POST['sort_order'] ?? 0);
        $isActive    = isset($_POST['is_active']) ? 1 : 0;

        if (!$id || empty($title)) {
            Session::setFlash('error', 'Invalid project or Title is missing.');
            $this->redirect('/admin/cms/projects' . ($id ? '/edit?id=' . $id : ''));
        }

        // 1. Process Images (existing kept + new ones)
        $images = $this->collectImages();

        // 2. Process Videos (existing kept + new ones)
        $videos = $this->collectVideos();

        $db = Database::getInstance();
        $stmt = $db->prepare("
            UPDATE cms_projects 
            SET title = :title, 
                subtitle = :subtitle, 
                description = :description, 
                images = :images, 
                videos = :videos, 
                sort_order = :sort_order, 
                is_active = :is_active
            WHERE id = :id
        ");

        $stmt->execute([
            ':title'       => $title,
            ':subtitle'    => $subtitle,
            ':description' => $description,
            ':images'      => json_encode($images),
            ':videos'      => json_encode($videos),
            ':sort_order'  => $sortOrder,
            ':is_active'   => $isActive,
            ':id'          => $id
        ]);

        if (class_exists('Core\Cache')) {
            Cache::delete('cms_storefront_projects');
        }

        Session::setFlash('success', 'Project updated successfully.');
        $this->redirect('/admin/cms/projects');
    }

    /**
     * Delete project
     */
    public function delete()
    {
        $this->requirePermission('cms_projects', 'delete');
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/admin/cms/projects');
        }

        $id = (int)($_POST['id'] ?? 0);
        if ($id) {
            $db = Database::getInstance();
            $stmt = $db->prepare("DELETE FROM cms_projects WHERE id = :id");
            $stmt->execute([':id' => $id]);

            if (class_exists('Core\Cache')) {
                Cache::delete('cms_storefront_projects');
            }

            Session::setFlash('success', 'Project deleted successfully.');
        }

        $this->redirect('/admin/cms/projects');
    }

    /**
     * AJAX/POST Toggle Active status
     */
    public function toggleStatus()
    {
        $this->requirePermission('cms_projects', 'edit');
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            echo json_encode(['success' => false, 'message' => 'Method not allowed']);
            exit;
        }

        $id = (int)($_POST['id'] ?? 0);
        if (!$id) {
            echo json_encode(['success' => false, 'message' => 'Invalid project ID']);
            exit;
        }

        $db = Database::getInstance();
        $stmt = $db->prepare("SELECT is_active FROM cms_projects WHERE id = :id");
        $stmt->execute([':id' => $id]);
        $curr = $stmt->fetch();

        if ($curr) {
            $newStatus = $curr['is_active'] ? 0 : 1;
            $updateStmt = $db->prepare("UPDATE cms_projects SET is_active = :status WHERE id = :id");
            $updateStmt->execute([':status' => $newStatus, ':id' => $id]);

            if (class_exists('Core\Cache')) {
                Cache::delete('cms_storefront_projects');
            }

            echo json_encode(['success' => true, 'is_active' => $newStatus]);
            exit;
        }

        echo json_encode(['success' => false, 'message' => 'Project not found']);
        exit;
    }

    /**
     * Collect and upload multiple images (from Cloudinary URLs, existing list, and file uploads)
     */
    private function collectImages(): array
    {
        $images = [];

        // 1. Text URLs (from Cloudinary library picker or direct URLs)
        if (!empty($_POST['existing_images']) && is_array($_POST['existing_images'])) {
            foreach ($_POST['existing_images'] as $url) {
                $clean = trim($url);
                if (!empty($clean)) {
                    $images[] = $clean;
                }
            }
        }

        if (!empty($_POST['image_urls']) && is_array($_POST['image_urls'])) {
            foreach ($_POST['image_urls'] as $url) {
                $clean = trim($url);
                if (!empty($clean) && !in_array($clean, $images)) {
                    $images[] = $clean;
                }
            }
        }

        // 2. Uploaded image files
        if (!empty($_FILES['image_files']['name']) && is_array($_FILES['image_files']['name'])) {
            $uploader = new CloudinaryUploader();
            $count = count($_FILES['image_files']['name']);

            for ($i = 0; $i < $count; $i++) {
                if ($_FILES['image_files']['error'][$i] === UPLOAD_ERR_OK) {
                    $tmpName = $_FILES['image_files']['tmp_name'][$i];
                    $origName = $_FILES['image_files']['name'][$i];

                    // Try Cloudinary upload first
                    $uploadedUrl = null;
                    try {
                        $cloudRes = $uploader->uploadImage($tmpName, null, $origName);
                        if (!empty($cloudRes['secure_url'])) {
                            $uploadedUrl = $cloudRes['secure_url'];
                        }
                    } catch (\Throwable $e) {
                        error_log("Cloudinary image upload error: " . $e->getMessage());
                    }

                    // Fallback to local upload if Cloudinary is not configured or failed
                    if (!$uploadedUrl) {
                        $ext = strtolower(pathinfo($origName, PATHINFO_EXTENSION));
                        $allowedExts = ['jpg', 'jpeg', 'png', 'webp', 'gif', 'svg'];
                        if (in_array($ext, $allowedExts)) {
                            $dir = BASE_PATH . '/public/uploads/projects/images';
                            if (!is_dir($dir)) mkdir($dir, 0777, true);
                            $newFilename = 'proj_' . uniqid() . '.' . $ext;
                            if (move_uploaded_file($tmpName, $dir . '/' . $newFilename)) {
                                $uploadedUrl = '/uploads/projects/images/' . $newFilename;
                            }
                        }
                    }

                    if ($uploadedUrl && !in_array($uploadedUrl, $images)) {
                        $images[] = $uploadedUrl;
                    }
                }
            }
        }

        return array_values(array_unique($images));
    }

    /**
     * Collect and upload multiple videos (from Cloudinary URLs, YouTube/Vimeo embeds, and file uploads)
     */
    private function collectVideos(): array
    {
        $videos = [];

        // 1. Existing videos preserved
        if (!empty($_POST['existing_videos']) && is_array($_POST['existing_videos'])) {
            foreach ($_POST['existing_videos'] as $v) {
                if (is_string($v)) {
                    $decoded = json_decode($v, true);
                    if ($decoded && !empty($decoded['url'])) {
                        $videos[] = $decoded;
                    } elseif (!empty($v)) {
                        $videos[] = ['url' => trim($v), 'type' => $this->detectVideoType($v)];
                    }
                } elseif (is_array($v) && !empty($v['url'])) {
                    $videos[] = $v;
                }
            }
        }

        // 2. Video URLs added (Cloudinary, YouTube, Vimeo, direct MP4)
        if (!empty($_POST['video_urls']) && is_array($_POST['video_urls'])) {
            foreach ($_POST['video_urls'] as $url) {
                $clean = trim($url);
                if (!empty($clean)) {
                    $videos[] = [
                        'url'  => $clean,
                        'type' => $this->detectVideoType($clean)
                    ];
                }
            }
        }

        // 3. Uploaded video files
        if (!empty($_FILES['video_files']['name']) && is_array($_FILES['video_files']['name'])) {
            $uploader = new CloudinaryUploader();
            $count = count($_FILES['video_files']['name']);

            for ($i = 0; $i < $count; $i++) {
                if ($_FILES['video_files']['error'][$i] === UPLOAD_ERR_OK) {
                    $tmpName = $_FILES['video_files']['tmp_name'][$i];
                    $origName = $_FILES['video_files']['name'][$i];

                    // Try Cloudinary upload first
                    $uploadedUrl = null;
                    try {
                        $cloudRes = $uploader->uploadMedia($tmpName, null, $origName);
                        if (!empty($cloudRes['secure_url'])) {
                            $uploadedUrl = $cloudRes['secure_url'];
                        }
                    } catch (\Throwable $e) {
                        error_log("Cloudinary video upload error: " . $e->getMessage());
                    }

                    // Fallback to local upload if Cloudinary is not configured or failed
                    if (!$uploadedUrl) {
                        $ext = strtolower(pathinfo($origName, PATHINFO_EXTENSION));
                        $allowedExts = ['mp4', 'webm', 'mov', 'ogg'];
                        if (in_array($ext, $allowedExts)) {
                            $dir = BASE_PATH . '/public/uploads/projects/videos';
                            if (!is_dir($dir)) mkdir($dir, 0777, true);
                            $newFilename = 'proj_vid_' . uniqid() . '.' . $ext;
                            if (move_uploaded_file($tmpName, $dir . '/' . $newFilename)) {
                                $uploadedUrl = '/uploads/projects/videos/' . $newFilename;
                            }
                        }
                    }

                    if ($uploadedUrl) {
                        $videos[] = [
                            'url'  => $uploadedUrl,
                            'type' => $this->detectVideoType($uploadedUrl)
                        ];
                    }
                }
            }
        }

        return $videos;
    }

    /**
     * Helper to detect video source type
     */
    private function detectVideoType(string $url): string
    {
        if (preg_match('/(youtube\.com|youtu\.be)/i', $url)) {
            return 'youtube';
        }
        if (preg_match('/vimeo\.com/i', $url)) {
            return 'vimeo';
        }
        if (preg_match('/res\.cloudinary\.com.*\/video\//i', $url)) {
            return 'cloudinary';
        }
        return 'html5';
    }
}
