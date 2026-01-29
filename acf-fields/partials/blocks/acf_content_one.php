<?php
use StoutLogic\AcfBuilder\FieldsBuilder;

$content_one = new FieldsBuilder('content_one', [
    'label' => 'Content One',
]);

$content_one
    ->addTab('content_tab', ['label' => 'Content'])
        ->addText('heading', [
            'label' => 'Heading',
            'default_value' => 'Lorem ipsum dolor sit amet lorem consectetur sed.',
        ])
        ->addSelect('heading_tag', [
            'label' => 'Heading Tag',
            'choices' => [
                'h1' => 'h1',
                'h2' => 'h2',
                'h3' => 'h3',
                'h4' => 'h4',
                'h5' => 'h5',
                'h6' => 'h6',
                'p'  => 'p',
                'span' => 'span',
            ],
            'default_value' => 'h2',
        ])
        ->addWysiwyg('description', [
            'label' => 'Description',
            'media_upload' => 0,
            'tabs' => 'all',
            'delay' => 0,
            'default_value' => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua.',
        ])
        ->addLink('button_link', [
            'label' => 'CTA Button (ACF Link)',
            'return_format' => 'array',
        ])

    ->addTab('layout_tab', ['label' => 'Layout'])
        ->addRepeater('padding_settings', [
            'label' => 'Padding Settings',
            'instructions' => 'Customize padding for different screen sizes.',
            'button_label' => 'Add Screen Size Padding',
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
                'min' => 0,
                'max' => 20,
                'step' => 0.1,
                'append' => 'rem',
            ])
            ->addNumber('padding_bottom', [
                'label' => 'Padding Bottom',
                'min' => 0,
                'max' => 20,
                'step' => 0.1,
                'append' => 'rem',
            ])
        ->endRepeater();

return $content_one;
