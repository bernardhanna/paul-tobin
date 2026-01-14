<?php

use StoutLogic\AcfBuilder\FieldsBuilder;

$image_carousel = new FieldsBuilder('image_carousel', [
    'label' => 'Image Carousel',
]);

$image_carousel
    ->addTab('Content', ['label' => 'Content'])
    ->addRepeater('images', [
        'label' => 'Carousel Images',
        'instructions' => 'Add images to display in the carousel. Recommended size: 1070x1000px for best quality.',
        'button_label' => 'Add Image',
        'min' => 1,
        'max' => 10,
        'layout' => 'block',
    ])
        ->addImage('image', [
            'label' => 'Image',
            'instructions' => 'Select an image for the carousel. Alt text will be pulled from the media library.',
            'return_format' => 'id',
            'preview_size' => 'medium',
            'required' => 1,
        ])
    ->endRepeater()

    ->addTab('Settings', ['label' => 'Carousel Settings'])
    ->addTrueFalse('show_dots', [
        'label' => 'Show Navigation Dots',
        'instructions' => 'Display navigation dots below the carousel.',
        'default_value' => 1,
        'ui' => 1,
    ])
    ->addTrueFalse('autoplay', [
        'label' => 'Enable Autoplay',
        'instructions' => 'Automatically advance slides.',
        'default_value' => 0,
        'ui' => 1,
    ])
    ->addNumber('autoplay_speed', [
        'label' => 'Autoplay Speed (milliseconds)',
        'instructions' => 'Time between slide transitions when autoplay is enabled.',
        'default_value' => 3000,
        'min' => 1000,
        'max' => 10000,
        'step' => 500,
        'conditional_logic' => [
            [
                [
                    'field' => 'autoplay',
                    'operator' => '==',
                    'value' => '1',
                ],
            ],
        ],
    ])

    ->addTab('Design', ['label' => 'Design'])
    ->addColorPicker('background_color', [
        'label' => 'Background Color',
        'instructions' => 'Set the background color for the carousel section.',
        'default_value' => '#E5E7EB',
    ])

    ->addTab('Layout', ['label' => 'Layout'])
    ->addRepeater('padding_settings', [
        'label' => 'Padding Settings',
        'instructions' => 'Customize padding for different screen sizes.',
        'button_label' => 'Add Screen Size Padding',
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
            'required' => 1,
        ])
        ->addNumber('padding_top', [
            'label' => 'Padding Top',
            'instructions' => 'Set the top padding in rem.',
            'min' => 0,
            'max' => 20,
            'step' => 0.1,
            'append' => 'rem',
            'default_value' => 5,
        ])
        ->addNumber('padding_bottom', [
            'label' => 'Padding Bottom',
            'instructions' => 'Set the bottom padding in rem.',
            'min' => 0,
            'max' => 20,
            'step' => 0.1,
            'append' => 'rem',
            'default_value' => 5,
        ])
    ->endRepeater();

return $image_carousel;
