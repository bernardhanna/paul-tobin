<?php
use StoutLogic\AcfBuilder\FieldsBuilder;

$gallery_001 = new FieldsBuilder('gallery_001', [
    'label' => 'Gallery Grid w/Modal or Carousel',
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
            'default_value' => '#f9fafb',
        ])

    ->addTab('Layout', ['label' => 'Layout'])
        ->addSelect('display_mode', [
            'label' => 'Display Mode',
            'choices' => [
                'grid'     => 'Grid',
                'carousel' => 'Carousel (Slick with dots)',
            ],
            'default_value' => 'grid',
        ])
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
                'choices' => [
                    'xxs' => 'xxs',
                    'xs' => 'xs',
                    'mob' => 'mob',
                    'sm' => 'sm',
                    'md' => 'md',
                    'lg' => 'lg',
                    'xl' => 'xl',
                    'xxl' => 'xxl',
                    'ultrawide' => 'ultrawide',
                ],
            ])
            ->addNumber('padding_top', [
                'label' => 'Padding Top',
                'min' => 0, 'max' => 20, 'step' => 0.1, 'append' => 'rem',
                'default_value' => 5,
            ])
            ->addNumber('padding_bottom', [
                'label' => 'Padding Bottom',
                'min' => 0, 'max' => 20, 'step' => 0.1, 'append' => 'rem',
                'default_value' => 5,
            ])
        ->endRepeater();

return $gallery_001;
