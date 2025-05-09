## Config example
Best used with OPcache driver:
https://github.com/rasteiner/kirby-opcache

```php
<?php 

return [
    'rasteiner.kirby-iconify' => [
        'defaultAttrs' => [
            'class' => 'h-[1em]',
        ],
        'cache' => [
            'active' => true,
            'type' => 'opcache',
        ],
    ],
];
```

Include the icon symbol definitions at the end of the template

**site/snippets/footer.php:**  
```php-template
<?= iconTable() ?>
</body>
</html>
```
</figure>


## Usage 

### Helper method
```php
<?= icon('svg-spinners:blocks-wave', class: 'h-48 w-48 block text-emerald-500') ?>
```

### Field method
```php
<?= $block->icon()->toIcon()->use(class: 'h-16 w-16 text-emerald-500') ?>
```

### KirbyText tag
```txt
Please go (icon: line-md:home-md), you're drunk.
```
