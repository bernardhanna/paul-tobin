<?php

use StoutLogic\AcfBuilder\FieldsBuilder;

$gallery_001 = new FieldsBuilder('gallery_001', [
    'label' => 'Gallery 001',
]);

$gallery_001
    ->addTab('Content', ['label' => 'Content'])
    ->addRepeater('gallery_images', [
        'label' => 'Gallery Images',
        'instructions' => 'Add images to the gallery. Click on images to view in full-screen carousel.',
        'button_label' => 'Add Image',
        'min' => 1,
        'max' => 20,
        'layout' => 'block',
    ])
        ->addImage('image', [
            'label' => 'Image',
            'instructions' => 'Upload an image for the gallery. Alt text and title will be used from media library.',
            'return_format' => 'id',
            'preview_size' => 'medium',
            'library' => 'all',
        ])
    ->endRepeater()

    ->addTab('Design', ['label' => 'Design'])
    ->addColorPicker('background_color', [
        'label' => 'Background Color',
        'instructions' => 'Choose the background color for the gallery section.',
        'default_value' => '#f9fafb',
    ])

    ->addTab('Layout', ['label' => 'Layout'])
    ->addRepeater('padding_settings', [
        'label' => 'Padding Settings',
        'instructions' => 'Customize padding for different screen sizes.',
        'button_label' => 'Add Screen Size Padding',
        'min' => 0,
        'max' => 9,
        'layout' => 'table',
    ])
        ->addSelect('screen_size', [
            'label' => 'Screen Size',
            'instructions' => 'Select the screen size for this padding setting.',
            'choices' => [
                'xxs' => 'XXS (Extra Extra Small)',
                'xs' => 'XS (Extra Small)',
                'mob' => 'Mobile',
                'sm' => 'SM (Small)',
                'md' => 'MD (Medium)',
                'lg' => 'LG (Large)',
                'xl' => 'XL (Extra Large)',
                'xxl' => 'XXL (Extra Extra Large)',
                'ultrawide' => 'Ultrawide',
            ],
            'allow_null' => 0,
            'default_value' => 'md',
        ])
        ->addNumber('padding_top', [
            'label' => 'Padding Top',
            'instructions' => 'Set the top padding in rem units.',
            'min' => 0,
            'max' => 20,
            'step' => 0.1,
            'append' => 'rem',
            'default_value' => 5,
        ])
        ->addNumber('padding_bottom', [
            'label' => 'Padding Bottom',
            'instructions' => 'Set the bottom padding in rem units.',
            'min' => 0,
            'max' => 20,
            'step' => 0.1,
            'append' => 'rem',
            'default_value' => 5,
        ])
    ->endRepeater();

return $gallery_001;
