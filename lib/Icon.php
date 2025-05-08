<?php 

namespace rasteiner\iconify;

use Exception;
use Kirby\Http\Remote;
use Kirby\Toolkit\Html;

class Icon {
    private static array $using = [];
    private static array|null $defaultAttrs = null;

    // lookup cache
    private function __construct(public string $library, public string $icon) {
        $this->library = $library;
        $this->icon = $icon;
    }

    public static function new(string $id): ?self {
        [$library, $icon] = explode(':', $id, 2);
                
        if (empty($library) || empty($icon)) {
            if (option('debug')) {
                echo 'Icon id ' . html($id) . ' is missing library or icon name';
            }

            return null;
        }

        return new Icon($library, $icon);
    }

    public function use(...$attrs): string {
        self::$using[$this->library][$this->icon] = true;
        $library = $this->library;

        if(self::$defaultAttrs === null) {
            self::$defaultAttrs = option('rasteiner.kirby-iconify.defaultAttrs', []);
        }

        // is this in cache? 
        $cache = kirby()->cache('rasteiner.kirby-iconify');
        $cached = $cache->getOrSet($library, fn() => self::downloadPrefix($library));

        // check if icon exists in cache
        if ($icon = self::resolve($cached, $this->icon)) {
            $w = $icon['width'];
            $h = $icon['height'];
            $t = $icon['top'];
            $l = $icon['left'];

            $transform = array_filter([
                $icon['rotate'] ? "rotate({$icon['rotate']})" : null,
                $icon['hFlip'] ? 'scale(-1, 1)' : null,
                $icon['vFlip'] ? 'scale(1, -1)' : null,
            ]);

            $transform = match (count($transform)) {
                0 => null,
                default => ' transform="' . join(' ', $transform) . '" transform-origin="center"',
            };

            return Html::tag('svg', [
                Html::tag('use', null, [
                    'xlink:href' => "#{$library}-{$this->icon}",
                    'transform' => $transform
                ]),
            ], [
                'viewBox' => "{$l} {$t} {$w} {$h}",
            ] + $attrs + self::$defaultAttrs);
        } else {
            if (option('debug')) {
                echo 'Icon ' . html($icon) . ' not found in library ' . html($library);
            }
        }
        
        return '';
    }

    protected static function downloadPrefix(string $prefix): array {
        // download npm package from unpkg
        // unpkg.com/@iconify-json/$prefix@latest/icons.json

        $url = "https://unpkg.com/@iconify-json/$prefix@latest/icons.json";
        $req = Remote::get($url, [
            'headers' => [
                'Accept' => 'application/json'
            ]
        ]);

        if ($req->code() !== 200 && option('debug')) {
            throw new Exception('Failed to load icons');
        }

        return $req->json();        
    }

    /*
    protected static function loadIcons(string $prefix, array $icons): array {
        if(count($icons) === 0) {
            return [];
        }

        sort($icons);
        $cache = kirby()->cache('rasteiner.kirby-iconify');
        $cached = $cache->get($prefix) ?? [];
        $query = http_build_query([
            'icons' => join(',', $icons)
        ]);


        $req = Remote::get("https://api.iconify.design/{$prefix}.json?{$query}", [
            'headers' => [
                'Accept' => 'application/json'
            ]
        ]);

        if ($req->code() !== 200 && option('debug')) {
            throw new Exception('Failed to load icons');
        }

        $resp = $req->json();
        
        $cache->set($prefix, array_merge_recursive($cached, $resp));

        return $resp;
    }
    */

    protected static function resolve(array $table, string $icon, array $props = []): ?array {
        if (array_key_exists($icon, $table['aliases'] ?? [])) {
            return self::resolve($table, $table['aliases'][$icon], $table['aliases'][$icon]);
        }

        if ($icon = $table['icons'][$icon] ?? false) {
            return [
                'width' => $props['width'] ?? $icon['width'] ?? $table['width'] ?? 16,
                'height' => $props['height'] ?? $icon['height'] ?? $table['height'] ?? 16,
                'left' => $props['left'] ?? $icon['left'] ?? 0,
                'top' => $props['top'] ?? $icon['top'] ?? 0,
                'rotate' => $props['rotate'] ?? $icon['rotate'] ?? 0,
                'hFlip' => $props['hFlip'] ?? $icon['hFlip'] ?? false,
                'vFlip' => $props['vFlip'] ?? $icon['vFlip'] ?? false,
                'body' => $icon['body'],
            ];
        }

        return null;
    }

    public static function iconsTable(): string {
        if (count(self::$using) === 0) {
            return '';
        }

        $cache = kirby()->cache('rasteiner.kirby-iconify');
        
        $icons = [];
        
        foreach(self::$using as $prefix => $used) {
            $cached = $cache->get($prefix) ?? [];
            foreach(array_keys($used) as $icon) {
                if ($found = self::resolve($cached, $icon)) {
                    $icons["$prefix-$icon"] = $found;
                }
            }
        }
        
        $symbols = array_map(function($icon, $id) {
            return "<symbol id=\"$id\" viewBox=\"{$icon['left']} {$icon['top']} {$icon['width']} {$icon['height']}\">{$icon['body']}</symbol>";
        }, $icons, array_keys($icons));

        $symbols = join("\n", $symbols);
        return <<<HTML
            <svg style="display: none">
                $symbols
            </svg>
        HTML;
    }
}
