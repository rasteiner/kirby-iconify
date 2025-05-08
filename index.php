<?php

use Kirby\Cms\App as Kirby;
use rasteiner\iconify\Icon;

load([
    'rasteiner\\iconify\\Icon' => 'Icon.php'
], __DIR__ . '/lib');

Kirby::plugin('rasteiner/kirby-iconify', [
    'options' => [
        'cache' => true
    ],
    'fieldMethods' => [
        'toIcon' => function ($field) {
            return Icon::new($field->value);
        }
    ],
    'tags' => [
        'icon' => [
            'html' => function ($tag) {
                $icon = Icon::new($tag->value);
                return $icon ? $icon->use() : '';                
            }
        ]
    ]
]);
