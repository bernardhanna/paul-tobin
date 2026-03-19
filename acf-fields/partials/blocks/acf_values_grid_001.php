<?php
/**
 * ACF Builder: Values Grid 001
 */

use StoutLogic\AcfBuilder\FieldsBuilder;

$values_grid_001 = new FieldsBuilder('values_grid_001', [
    'label' => 'Values Grid',
    'menu_order' => 0,
]);

$values_grid_001
    ->setLocation('post_type', '==', 'page');

// ----- CONTENT TAB
$values_grid_001
    ->addTab('content_tab', ['label' => 'Content'])
        ->addGroup('heading_group', ['label' => 'Heading'])
            ->addText('heading_text', [
                'label' => 'Heading Text',
                'instructions' => 'Main heading text.',
                'default_value' => 'Why Choose Us',
            ])
            ->addSelect('heading_tag', [
                'label' => 'Heading Tag',
                'instructions' => 'Select semantic tag to use.',
                'choices' => [
                    'h1' => 'h1', 'h2' => 'h2', 'h3' => 'h3',
                    'h4' => 'h4', 'h5' => 'h5', 'h6' => 'h6',
                    'span' => 'span', 'p' => 'p',
                ],
                'default_value' => 'h2',
            ])
        ->endGroup()
        ->addTrueFalse('show_divider', [
            'label' => 'Show Color Divider',
            'ui' => 1,
            'default_value' => 1,
        ])
        ->addWysiwyg('intro_rich_text', [
            'label' => 'Intro Copy',
            'instructions' => 'Intro paragraph content.',
            'media_upload' => 0,
            'delay' => 0,
        ])
        ->addSelect('desktop_columns', [
            'label' => 'Desktop Columns',
            'instructions' => 'Choose 2 or 3 columns for desktop screens.',
            'choices' => [
                '2' => '2 Columns',
                '3' => '3 Columns',
            ],
            'default_value' => '3',
            'ui' => 1,
        ])
        ->addRepeater('features', [
            'label' => 'Features',
            'min' => 0,
            'max' => 12,
            'layout' => 'block',
            'button_label' => 'Add Feature',
        ])
            ->addText('feature_heading', [
                'label' => 'Feature Heading',
                'default_value' => 'Proactive Maintenance',
            ])
            ->addWysiwyg('feature_text', [
                'label' => 'Feature Text',
                'media_upload' => 0,
                'delay' => 0,
            ])
            ->addColorPicker('bar_color', [
                'label' => 'Bar Color',
                'default_value' => '#0ea5e9',
            ])
        ->endRepeater();

// ----- DESIGN TAB
$values_grid_001
    ->addTab('design_tab', ['label' => 'Design'])
        ->addColorPicker('background_color', [
            'label' => 'Background Color',
            'default_value' => '#FFFFFF',
        ])
        ->addColorPicker('card_background_color', [
            'label' => 'Card Background Color',
            'default_value' => '#E0E0E0',
        ])
        ->addColorPicker('text_color', [
            'label' => 'Text Color',
            'default_value' => '#0F172A',
        ])
        ->addSelect('section_border_radius', [
            'label' => 'Section Border Radius',
            'choices' => [
                'rounded-none' => 'rounded-none',
                'rounded' => 'rounded',
                'rounded-md' => 'rounded-md',
                'rounded-lg' => 'rounded-lg',
                'rounded-xl' => 'rounded-xl',
                'rounded-2xl' => 'rounded-2xl',
            ],
            'default_value' => 'rounded-none',
        ]);

// ----- LAYOUT TAB
$values_grid_001
    ->addTab('layout_tab', ['label' => 'Layout'])
        ->addRepeater('padding_settings', [
            'label' => 'Padding Settings',
            'instructions' => 'Customize padding for different screen sizes.',
            'button_label' => 'Add Screen Size Padding',
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
                'instructions' => 'Set the top padding in rem.',
                'min' => 0,
                'max' => 20,
                'step' => 0.1,
                'append' => 'rem',
            ])
            ->addNumber('padding_bottom', [
                'label' => 'Padding Bottom',
                'instructions' => 'Set the bottom padding in rem.',
                'min' => 0,
                'max' => 20,
                'step' => 0.1,
                'append' => 'rem',
            ])
        ->endRepeater();

return $values_grid_001;
