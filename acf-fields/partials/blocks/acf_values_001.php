<?php
/**
 * ACF Builder: Clients 001
 */

use StoutLogic\AcfBuilder\FieldsBuilder;

$values_001 = new FieldsBuilder('values_001', [
    'label' => 'Values',
    'menu_order' => 0,
]);

$values_001
    ->setLocation('post_type', '==', 'page'); // adjust as needed for your Flexible Content usage

// ----- CONTENT TAB
$values_001
    ->addTab('content_tab', ['label' => 'Content'])
        ->addGroup('heading_group', ['label' => 'Heading'])
            ->addText('heading_text', [
                'label' => 'Heading Text',
                'instructions' => 'Main heading text.',
                'default_value' => 'We are all about our clients',
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
        ->addImage('image', [
            'label' => 'Main Image',
            'instructions' => 'Upload/select the main image.',
            'return_format' => 'array',
        ])
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
            'default_value' => 'At Paul Tobin Estate Agents, we specialise in helping non-resident landlords and those emigrating manage, sell, or let their Irish properties with ease and confidence. With over 19 years of experience, we provide a highly personalised and boutique service, ensuring every client gets the attention they deserve: quality, tailored solutions.',
        ])
        ->addRepeater('features', [
            'label' => 'Features',
            'min' => 0,
            'max' => 6,
            'layout' => 'block',
            'button_label' => 'Add Feature',
        ])
            ->addText('feature_heading', [
                'label' => 'Feature Heading',
                'default_value' => 'Boutique practice',
            ])
            ->addWysiwyg('feature_text', [
                'label' => 'Feature Text',
                'media_upload' => 0,
                'delay' => 0,
                'default_value' => 'We are a boutique practice, offering professional bespoke services to property owners for more than 19 years.',
            ])
        ->endRepeater();

// ----- DESIGN TAB
$values_001
    ->addTab('design_tab', ['label' => 'Design'])
        ->addColorPicker('background_color', [
            'label' => 'Background Color',
            'default_value' => '#F8FAFC', // bg-bg-light analogue
        ])
        ->addColorPicker('text_color', [
            'label' => 'Text Color',
            'default_value' => '#0F172A', // text-text-dark analogue
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
        ])
        ->addSelect('image_border_radius', [
            'label' => 'Image Border Radius',
            'choices' => [
                'rounded-none' => 'rounded-none',
                'rounded' => 'rounded',
                'rounded-md' => 'rounded-md',
                'rounded-lg' => 'rounded-lg',
                'rounded-xl' => 'rounded-xl',
                'rounded-2xl' => 'rounded-2xl',
            ],
            'default_value' => 'rounded-lg',
        ]);

// ----- LAYOUT TAB
$values_001
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

return $values_001;
