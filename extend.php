<?php

use Flarum\Extend;
use JerryMalmstrom\PasteImage\Api\Controller\UploadImageController;

return [
    (new Extend\Frontend('forum'))
        ->js(__DIR__ . '/js/dist/forum.js')
        ->css(__DIR__ . '/resources/less/forum.less'),

    (new Extend\Frontend('admin'))
        ->js(__DIR__ . '/js/dist/admin.js'),

    (new Extend\Routes('api'))
        ->post('/paste-image/upload', 'paste-image.upload', UploadImageController::class),

    (new Extend\Locales(__DIR__ . '/locale')),

    (new Extend\Settings())
        ->default('jerrymalmstrom-paste-image.max_file_size', 2048)
        ->default('jerrymalmstrom-paste-image.allowed_types', 'image/png,image/jpeg,image/gif,image/webp'),
];
