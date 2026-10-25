<?php
$isCreate = true;
$topbar = [
    'is_active' => 1,
    'content' => [
        'title' => 'Top Bar Configuration',
        'global_settings' => [
            'bg_color' => '#3f4c38',
            'text_color' => '#ffffff',
            'mode' => 'slider',
            'speed' => 5,
            'height' => 38,
            'font_size' => 13,
            'show_social_icon' => 1,
            'social_platform' => 'instagram',
            'social_url' => '',
        ],
        'announcements' => [
            [
                'text' => '',
                'cta_url' => '',
                'icon' => 'truck',
                'active' => 1,
            ],
        ],
    ],
];

require __DIR__ . '/edit.php';