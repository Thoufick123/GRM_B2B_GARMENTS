<?php
namespace App\Controllers\Admin;

use Core\Controller;
use App\Models\CmsComponent;
use App\Models\Category;
use App\Models\Product;
use App\Models\User;
use Core\Session;

class ProductCarouselController extends Controller
{
    private CmsComponent $cmsModel;
    private Category $categoryModel;
    private Product $productModel;
    private User $userModel;

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
        $this->productModel = new Product();
        $this->userModel = new User();
    }

    public function index()
    {
        $this->requirePermission('cms_product_carousels', 'view');
        $carousels = $this->cmsModel->getAllProductCarousels();
        
        // Decode JSON content and fetch first product image
        foreach ($carousels as &$carousel) {
            $carousel['content'] = json_decode($carousel['content_data'], true) ?: [];
            
            $carousel['first_product_image'] = null;
            if (!empty($carousel['content']['product_ids']) && is_array($carousel['content']['product_ids'])) {
                $firstProductId = $carousel['content']['product_ids'][0];
                $images = $this->productModel->getImages($firstProductId);
                if (!empty($images) && !empty($images[0]['image_path'])) {
                    $carousel['first_product_image'] = $images[0]['image_path'];
                }
            }
        }

        $this->render('admin/cms/product_carousels/index', [
            'title' => 'Product Carousels',
            'carousels' => $carousels
        ], 'admin');
    }

    public function create()
    {
        $this->requirePermission('cms_product_carousels', 'create');
        $categories = $this->categoryModel->getAll(0);
        $customCategories = $this->categoryModel->getAll(1);
        $normalProducts = $this->productModel->getAllActive(null, null, null, [], 'newest', 0);
        $customProducts = $this->productModel->getAllActive(null, null, null, [], 'newest', 1);
        $products = array_merge($normalProducts, $customProducts);
        $vendors = is_vendor_module_enabled() ? $this->userModel->getActiveVendors() : [];

        $this->render('admin/cms/product_carousels/create', [
            'title' => 'New Product Carousel',
            'categories' => $categories,
            'customCategories' => $customCategories,
            'products' => $products,
            'vendors' => $vendors
        ], 'admin');
    }

    public function store()
    {
        $this->requirePermission('cms_product_carousels', 'create');
        $title = $_POST['title'] ?? '';
        $sort_order = $_POST['sort_order'] ?? 0;
        $mobile_count = $_POST['mobile_count'] ?? 2;
        $desktop_count = $_POST['desktop_count'] ?? 4;
        $cta_text = $_POST['cta_text'] ?? '';
        $cta_url = $_POST['cta_url'] ?? '';
        $cta_position = $_POST['cta_position'] ?? 'Right';
        $show_arrows = isset($_POST['show_arrows']) ? true : false;
        $show_dots = isset($_POST['show_dots']) ? true : false;
        
        $color_theme_type = $_POST['color_theme_type'] ?? 'solid';
        $color_theme_value = $_POST['color_theme_value'] ?? '#ffffff';
        $is_active = isset($_POST['is_active']) ? 1 : 0;
        
        // Selected Products (Array of IDs) & Vendor ID
        $vendor_id = $_POST['vendor_id'] ?? 'all';
        $product_ids = $_POST['product_ids'] ?? [];
        if (!is_array($product_ids)) {
            $product_ids = [];
        }

        $contentData = json_encode([
            'title' => $title,
            'vendor_id' => $vendor_id,
            'sort_order' => (int)$sort_order,
            'mobile_count' => (int)$mobile_count,
            'desktop_count' => (int)$desktop_count,
            'cta_text' => $cta_text,
            'cta_url' => $cta_url,
            'cta_position' => $cta_position,
            'show_arrows' => $show_arrows,
            'show_dots' => $show_dots,
            'product_ids' => $product_ids,
            'title_color' => $_POST['title_color'] ?? '#111827',
            'button_bg_color' => $_POST['button_bg_color'] ?? '#4a3628',
            'button_text_color' => $_POST['button_text_color'] ?? '#ffffff',
            'arrow_color' => $_POST['arrow_color'] ?? '#ffffff',
            'arrow_bg_color' => $_POST['arrow_bg_color'] ?? '#ffffff',
            'dot_color' => $_POST['dot_color'] ?? '#8c6a4f',
            'text_color' => $_POST['text_color'] ?? '#111827',
            'price_color' => $_POST['price_color'] ?? '#111827',
            'wishlist_color' => $_POST['wishlist_color'] ?? '#111827',
            'image_fit' => $_POST['image_fit'] ?? 'cover',
            'theme' => [
                'type' => $color_theme_type,
                'value' => $color_theme_value
            ]
        ]);

        $this->cmsModel->create([
            'id' => uniqid('cmp_'),
            'section_type' => 'product_carousel',
            'content_data' => $contentData,
            'is_active' => $is_active
        ]);

        \Core\Cache::clear();

        Session::setFlash('success', 'Product Carousel created successfully.');
        $this->redirect('/admin/cms/product-carousels');
    }

    public function edit()
    {
        $this->requirePermission('cms_product_carousels', 'edit');
        $id = $_GET['id'] ?? '';
        $carousel = $this->cmsModel->getById($id);
        
        if (!$carousel || $carousel['section_type'] !== 'product_carousel') {
            Session::setFlash('error', 'Product Carousel not found.');
            $this->redirect('/admin/cms/product-carousels');
        }

        $carousel['content'] = json_decode($carousel['content_data'], true) ?: [];
        
        $categories = $this->categoryModel->getAll(0);
        $customCategories = $this->categoryModel->getAll(1);
        $normalProducts = $this->productModel->getAllActive(null, null, null, [], 'newest', 0);
        $customProducts = $this->productModel->getAllActive(null, null, null, [], 'newest', 1);
        $products = array_merge($normalProducts, $customProducts);
        $vendors = is_vendor_module_enabled() ? $this->userModel->getActiveVendors() : [];

        $this->render('admin/cms/product_carousels/edit', [
            'title' => 'Edit Product Carousel',
            'carousel' => $carousel,
            'categories' => $categories,
            'customCategories' => $customCategories,
            'products' => $products,
            'vendors' => $vendors
        ], 'admin');
    }

    public function update()
    {
        $this->requirePermission('cms_product_carousels', 'edit');
        $id = $_POST['id'] ?? '';
        $carousel = $this->cmsModel->getById($id);
        
        if (!$carousel || $carousel['section_type'] !== 'product_carousel') {
            Session::setFlash('error', 'Product Carousel not found.');
            $this->redirect('/admin/cms/product-carousels');
        }

        $title = $_POST['title'] ?? '';
        $sort_order = $_POST['sort_order'] ?? 0;
        $mobile_count = $_POST['mobile_count'] ?? 2;
        $desktop_count = $_POST['desktop_count'] ?? 4;
        $cta_text = $_POST['cta_text'] ?? '';
        $cta_url = $_POST['cta_url'] ?? '';
        $cta_position = $_POST['cta_position'] ?? 'Right';
        $show_arrows = isset($_POST['show_arrows']) ? true : false;
        $show_dots = isset($_POST['show_dots']) ? true : false;
        
        $color_theme_type = $_POST['color_theme_type'] ?? 'solid';
        $color_theme_value = $_POST['color_theme_value'] ?? '#ffffff';
        $is_active = isset($_POST['is_active']) ? 1 : 0;
        
        // Selected Products (Array of IDs) & Vendor ID
        $vendor_id = $_POST['vendor_id'] ?? 'all';
        $product_ids = $_POST['product_ids'] ?? [];
        if (!is_array($product_ids)) {
            $product_ids = [];
        }

        $contentData = json_encode([
            'title' => $title,
            'vendor_id' => $vendor_id,
            'sort_order' => (int)$sort_order,
            'mobile_count' => (int)$mobile_count,
            'desktop_count' => (int)$desktop_count,
            'cta_text' => $cta_text,
            'cta_url' => $cta_url,
            'cta_position' => $cta_position,
            'show_arrows' => $show_arrows,
            'show_dots' => $show_dots,
            'product_ids' => $product_ids,
            'title_color' => $_POST['title_color'] ?? '#111827',
            'button_bg_color' => $_POST['button_bg_color'] ?? '#4a3628',
            'button_text_color' => $_POST['button_text_color'] ?? '#ffffff',
            'arrow_color' => $_POST['arrow_color'] ?? '#ffffff',
            'arrow_bg_color' => $_POST['arrow_bg_color'] ?? '#ffffff',
            'dot_color' => $_POST['dot_color'] ?? '#8c6a4f',
            'text_color' => $_POST['text_color'] ?? '#111827',
            'price_color' => $_POST['price_color'] ?? '#111827',
            'wishlist_color' => $_POST['wishlist_color'] ?? '#111827',
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

        \Core\Cache::clear();

        Session::setFlash('success', 'Product Carousel updated successfully.');
        $this->redirect('/admin/cms/product-carousels');
    }

    public function delete()
    {
        $this->requirePermission('cms_product_carousels', 'delete');
        $id = $_POST['id'] ?? '';
        $this->cmsModel->delete($id);
        
        \Core\Cache::clear();

        Session::setFlash('success', 'Product Carousel deleted successfully.');
        $this->redirect('/admin/cms/product-carousels');
    }
}
