<?php 

if (function_exists('icon')) {
    return;
}

use rasteiner\iconify\Icon as Icon;

function icon(string $id, ...$attrs): string {
    $icon = Icon::new($id);
    if ($icon) {
        return $icon->use(...$attrs);
    }

    return '';
}

function iconTable(): string {
    return Icon::iconsTable();
}