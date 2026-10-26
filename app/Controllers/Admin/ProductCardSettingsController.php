<?php
namespace App\Controllers\Admin;

use Core\Controller;
use Core\Database;
use Core\Session;

class ProductCardSettingsController extends Controller
{
    private \PDO $db;

    public function __construct()
    {
        parent::__construct();
        $role = Session::get('user_role');
        if (!$role) {
            Session::setFlash('error', 'Unauthorized access.');
            $this->redirect('/login');
        }
        $this->db = Database::getInstance();
    }

    public function index()
    {
        $this->requirePermission('cms_product_card', 'view');
        $stmt = $this->db->prepare("SELECT setting_value FROM settings WHERE setting_key = 'product_card_settings'");
        $stmt->execute();
        $row = $stmt->fetch();
        $settings = $row ? (json_decode($row['setting_value'], true) ?? []) : [];

        $defaults = [
            'desktop' => [
                'card_height'       => 'auto',
                'image_height'      => 'auto',
                'container_height'  => 'auto',
                'container_width'   => 'auto',
                'color_mode'        => 'picker',
                'card_bg_color'     => '#ffffff',
                'name_color'        => '#111827',
                'price_color'       => '#111827',
                'star_color'        => '#f59e0b',
                'button_bg_color'   => '#F25996',
                'button_text_color' => '#ffffff',
                'wishlist_bg_color'   => '#ffffff',
                'wishlist_icon_color' => '#111827',
            ],
            'mobile' => [
                'card_height'       => 'auto',
                'image_height'      => 'auto',
                'container_height'  => 'auto',
                'container_width'   => 'auto',
                'color_mode'        => 'picker',
                'card_bg_color'     => '#ffffff',
                'name_color'        => '#111827',
                'price_color'       => '#111827',
                'star_color'        => '#f59e0b',
                'button_bg_color'   => '#F25996',
                'button_text_color' => '#ffffff',
                'wishlist_bg_color'   => '#ffffff',
                'wishlist_icon_color' => '#111827',
            ],
        ];

        // Merge saved settings over defaults, ignoring empty strings
        foreach (['desktop', 'mobile'] as $view) {
            if (!empty($settings[$view]) && is_array($settings[$view])) {
                foreach ($settings[$view] as $k => $v) {
                    if (trim((string)$v) !== '') {
                        $defaults[$view][$k] = $v;
                    }
                }
            }
        }

        $this->render('admin/cms/product_card_settings/edit', [
            'title'    => 'Global Product Card Settings',
            'settings' => $defaults,
        ], 'admin');
    }

    public function update()
    {
        $this->requirePermission('cms_product_card', 'edit');
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/admin/cms/product-card-settings');
        }

        $getVal = function($key, $default) {
            $v = trim($_POST[$key] ?? '');
            return $v !== '' ? $v : $default;
        };

        $data = [
            'desktop' => [
                'card_height'       => $getVal('desktop_card_height', 'auto'),
                'image_height'      => $getVal('desktop_image_height', 'auto'),
                'container_height'  => $getVal('desktop_container_height', 'auto'),
                'container_width'   => $getVal('desktop_container_width', 'auto'),
                'color_mode'        => ($_POST['desktop_color_mode'] ?? 'picker') === 'link' ? 'link' : 'picker',
                'card_bg_color'     => $getVal('desktop_card_bg_color', '#ffffff'),
                'name_color'        => $getVal('desktop_name_color', '#111827'),
                'price_color'       => $getVal('desktop_price_color', '#111827'),
                'star_color'        => $getVal('desktop_star_color', '#f59e0b'),
                'button_bg_color'   => $getVal('desktop_button_bg_color', '#F25996'),
                'button_text_color' => $getVal('desktop_button_text_color', '#ffffff'),
                'wishlist_bg_color'   => $getVal('desktop_wishlist_bg_color', '#ffffff'),
                'wishlist_icon_color' => $getVal('desktop_wishlist_icon_color', '#111827'),
            ],
            'mobile' => [
                'card_height'       => $getVal('mobile_card_height', 'auto'),
                'image_height'      => $getVal('mobile_image_height', 'auto'),
                'container_height'  => $getVal('mobile_container_height', 'auto'),
                'container_width'   => $getVal('mobile_container_width', 'auto'),
                'color_mode'        => ($_POST['mobile_color_mode'] ?? 'picker') === 'link' ? 'link' : 'picker',
                'card_bg_color'     => $getVal('mobile_card_bg_color', '#ffffff'),
                'name_color'        => $getVal('mobile_name_color', '#111827'),
                'price_color'       => $getVal('mobile_price_color', '#111827'),
                'star_color'        => $getVal('mobile_star_color', '#f59e0b'),
                'button_bg_color'   => $getVal('mobile_button_bg_color', '#F25996'),
                'button_text_color' => $getVal('mobile_button_text_color', '#ffffff'),
                'wishlist_bg_color'   => $getVal('mobile_wishlist_bg_color', '#ffffff'),
                'wishlist_icon_color' => $getVal('mobile_wishlist_icon_color', '#111827'),
            ],
        ];

        $json = json_encode($data);

        $stmt = $this->db->prepare("
            INSERT INTO settings (setting_key, setting_value)
            VALUES ('product_card_settings', ?)
            ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value), updated_at = NOW()
        ");
        $stmt->execute([$json]);
        if (class_exists('\Core\Cache')) {
            \Core\Cache::delete('setting_product_card_settings');
        }

        Session::setFlash('success', 'Product card settings saved successfully!');
        $this->redirect('/admin/cms/product-card-settings');
    }
}
