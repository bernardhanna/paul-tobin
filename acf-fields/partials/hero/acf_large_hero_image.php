<?php
/**
 * ACF Builder: Large Hero Image
 */

use StoutLogic\AcfBuilder\FieldsBuilder;

$large_hero_image = new FieldsBuilder('large_hero_image', [
    'label' => 'Large Hero Image',
]);

$large_hero_image
        ->addImage('hero_image', [
            'label' => 'Hero Image',
            'instructions' => 'Upload or select the hero image.',
            'return_format' => 'array',
            'preview_size' => 'large',
            'library' => 'all',
        ])
        ->addNumber('height_xs', [
            'label' => 'Height (base)',
            'instructions' => 'Height at base breakpoint in pixels (Tailwind arbitrary value).',
            'default_value' => 500,
            'min' => 100,
            'max' => 2000,
            'step' => 1,
            'append' => 'px',
        ])
        ->addNumber('height_md', [
            'label' => 'Height (md)',
            'instructions' => 'Height at md breakpoint in pixels (Tailwind arbitrary value).',
            'default_value' => 665,
            'min' => 100,
            'max' => 2000,
            'step' => 1,
            'append' => 'px',
        ]);

return $large_hero_image;
