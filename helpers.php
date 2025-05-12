<?php 

if (function_exists('icon')) {
    return;
}

use rasteiner\iconify\Icon as Icon;
use Kirby\Cms\File;

function icon(string|File $id, ...$attrs): string {
    if($id instanceof File) {
        if ($id->asset()->extension() !== 'svg') {
            return '';
        }

        // parse xml document
        $xml = simplexml_load_string($id->read());
        if ($xml === false) {
            return '';
        }

        // check if the file is a valid SVG
        if ($xml->getName() !== 'svg') {
            return '';
        }

        // set attributes 
        foreach ($attrs as $key => $value) {
            if (is_string($key)) {
                $xml->addAttribute($key, $value);
            }
        }

        $html = $xml->asXML() ?? '';
        // remove the XML declaration
        $html = preg_replace('/<\?xml.*?\?>/', '', $html);
        return $html;
    } else {
        $icon = Icon::new($id);
        if ($icon) {
            return $icon->use(...$attrs);
        }
    }

    return '';
}

function iconTable(): string {
    return Icon::iconsTable();
}