<?php
namespace App\Controllers\Admin;

use Core\Controller;
use App\Models\CmsComponent;
use App\Models\Category;
use Core\Session;
use Core\Cache;

class CategoryCarouselController extends Controller
{
    private CmsComponent $cmsModel;
    private Category $categoryModel;

    public function __construct()
    {
        parent::__construct();
        $role = Session::get('user_role');
        if (!$role) {
            Session::setFlash('error', 'Unauthorized access.');
            $this->redirect('/login');
        }
        $this->cmsModel = new CmsComponent();
        $this->categoryModel = new Category();
    }

    public function index()
    {
        $this->requirePermission('cms_category_carousels', 'view');
        $carousels = $this->cmsModel->getAllCategoryCarousels();
        
        // Decode JSON content and fetch first category image
        foreach ($carousels as &$carousel) {
            $carousel['content'] = json_decode($carousel['content_data'], true) ?: [];
            
            $carousel['first_category_image'] = null;
            if (!empty($carousel['content']['category_ids']) && is_array($carousel['content']['category_ids'])) {
                $firstCategoryId = $carousel['content']['category_ids'][0];
                $category = $this->categoryModel->findById($firstCategoryId);
                if ($category && !empty($category['image_path'])) {
                    $carousel['first_category_image'] = $category['image_path'];
                }
            }
        }

        $this->render('admin/cms/category_carousels/index', [
            'title' => 'Category Carousels',
            'carousels' => $carousels
        ], 'admin');
    }

    public function create()
    {
        $this->requirePermission('cms_category_carousels', 'create');
        $categories = $this->categoryModel->getAll();

        $this->render('admin/cms/category_carousels/create', [
            'title' => 'New Category Carousel',
            'categories' => $categories
        ], 'admin');
    }

    public function store()
    {
        $this->requirePermission('cms_category_carousels', 'create');
        $title = $_POST['title'] ?? '';
        $sort_order = $_POST['sort_order'] ?? 0;
        $mobile_count = max(1, min(6, (int)($_POST['mobile_count'] ?? 2)));
        $desktop_count = max(1, min(12, (int)($_POST['desktop_count'] ?? 6)));
        $cta_text = $_POST['cta_text'] ?? '';
        $cta_url = $_POST['cta_url'] ?? '';
        $cta_position = $_POST['cta_position'] ?? 'Right';
        $show_arrows = isset($_POST['show_arrows']) ? true : false;
        $show_dots = isset($_POST['show_dots']) ? true : false;
        
        $color_theme_type = $_POST['color_theme_type'] ?? 'solid';
        $color_theme_value = $_POST['color_theme_value'] ?? '#ffffff';
        $is_active = isset($_POST['is_active']) ? 1 : 0;
        
        // Selected Categories (Array of IDs)
        $category_ids = $_POST['category_ids'] ?? [];
        if (!is_array($category_ids)) {
            $category_ids = [];
        }

        $contentData = json_encode([
            'title' => $title,
            'sort_order' => (int)$sort_order,
            'mobile_count' => (int)$mobile_count,
            'desktop_count' => (int)$desktop_count,
            'cta_text' => $cta_text,
            'cta_url' => $cta_url,
            'cta_position' => $cta_position,
            'show_arrows' => $show_arrows,
            'show_dots' => $show_dots,
            'category_ids' => $category_ids,
            'title_color' => $_POST['title_color'] ?? '#111827',
            'arrow_color' => $_POST['arrow_color'] ?? '#ffffff',
            'arrow_bg_color' => $_POST['arrow_bg_color'] ?? '#ffffff',
            'dot_color' => $_POST['dot_color'] ?? '#8c6a4f',
            'text_color' => $_POST['text_color'] ?? '#111827',
            'button_bg_color' => $_POST['button_bg_color'] ?? '#4a3628',
            'button_text_color' => $_POST['button_text_color'] ?? '#ffffff',
            'container_height' => $_POST['container_height'] ?? '',
            'mobile_container_height' => $_POST['mobile_container_height'] ?? '',
            'image_fit' => $_POST['image_fit'] ?? 'cover',
            'theme' => [
                'type' => $color_theme_type,
                'value' => $color_theme_value
            ]
        ]);

        $this->cmsModel->create([
            'id' => uniqid('cat_cmp_'),
            'section_type' => 'category_carousel',
            'content_data' => $contentData,
            'is_active' => $is_active
        ]);

        Cache::delete('cms_layout_home');

        Session::setFlash('success', 'Category Carousel created successfully.');
        $this->redirect('/admin/cms/category-carousels');
    }

    public function edit()
    {
        $this->requirePermission('cms_category_carousels', 'edit');
        $id = $_GET['id'] ?? '';
        $carousel = $this->cmsModel->getById($id);
        
        if (!$carousel || $carousel['section_type'] !== 'category_carousel') {
            Session::setFlash('error', 'Category Carousel not found.');
            $this->redirect('/admin/cms/category-carousels');
        }

        $carousel['content'] = json_decode($carousel['content_data'], true) ?: [];
        
        $categories = $this->categoryModel->getAll();

        $this->render('admin/cms/category_carousels/edit', [
            'title' => 'Edit Category Carousel',
            'carousel' => $carousel,
            'categories' => $categories
        ], 'admin');
    }

    public function update()
    {
        $this->requirePermission('cms_category_carousels', 'edit');
        $id = $_POST['id'] ?? '';
        $carousel = $this->cmsModel->getById($id);
        
        if (!$carousel || $carousel['section_type'] !== 'category_carousel') {
            Session::setFlash('error', 'Category Carousel not found.');
            $this->redirect('/admin/cms/category-carousels');
        }

        $title = $_POST['title'] ?? '';
        $sort_order = $_POST['sort_order'] ?? 0;
        $mobile_count = max(1, min(6, (int)($_POST['mobile_count'] ?? 2)));
        $desktop_count = max(1, min(12, (int)($_POST['desktop_count'] ?? 6)));
        $cta_text = $_POST['cta_text'] ?? '';
        $cta_url = $_POST['cta_url'] ?? '';
        $cta_position = $_POST['cta_position'] ?? 'Right';
        $show_arrows = isset($_POST['show_arrows']) ? true : false;
        $show_dots = isset($_POST['show_dots']) ? true : false;
        
        $color_theme_type = $_POST['color_theme_type'] ?? 'solid';
        $color_theme_value = $_POST['color_theme_value'] ?? '#ffffff';
        $is_active = isset($_POST['is_active']) ? 1 : 0;
        
        // Selected Categories (Array of IDs)
        $category_ids = $_POST['category_ids'] ?? [];
        if (!is_array($category_ids)) {
            $category_ids = [];
        }

        $contentData = json_encode([
            'title' => $title,
            'sort_order' => (int)$sort_order,
            'mobile_count' => (int)$mobile_count,
            'desktop_count' => (int)$desktop_count,
            'cta_text' => $cta_text,
            'cta_url' => $cta_url,
            'cta_position' => $cta_position,
            'show_arrows' => $show_arrows,
            'show_dots' => $show_dots,
            'category_ids' => $category_ids,
            'title_color' => $_POST['title_color'] ?? '#111827',
            'arrow_color' => $_POST['arrow_color'] ?? '#ffffff',
            'arrow_bg_color' => $_POST['arrow_bg_color'] ?? '#ffffff',
            'dot_color' => $_POST['dot_color'] ?? '#8c6a4f',
            'text_color' => $_POST['text_color'] ?? '#111827',
            'button_bg_color' => $_POST['button_bg_color'] ?? '#4a3628',
            'button_text_color' => $_POST['button_text_color'] ?? '#ffffff',
            'container_height' => $_POST['container_height'] ?? '',
            'mobile_container_height' => $_POST['mobile_container_height'] ?? '',
            'image_fit' => $_POST['image_fit'] ?? 'cover',
            'theme' => [
                'type' => $color_theme_type,
                'value' => $color_theme_value
            ]
        ]);

        $this->cmsModel->update($id, [
            'content_data' => $contentData,
            'is_active' => $is_active
        ]);

        Cache::delete('cms_layout_home');

        Session::setFlash('success', 'Category Carousel updated successfully.');
        $this->redirect('/admin/cms/category-carousels');
    }

    public function delete()
    {
        $this->requirePermission('cms_category_carousels', 'delete');
        $id = $_POST['id'] ?? '';
        $this->cmsModel->delete($id);
        
        Cache::delete('cms_layout_home');

        Session::setFlash('success', 'Category Carousel deleted successfully.');
        $this->redirect('/admin/cms/category-carousels');
    }
}
