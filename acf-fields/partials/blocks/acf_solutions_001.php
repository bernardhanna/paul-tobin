<?php

use StoutLogic\AcfBuilder\FieldsBuilder;

$solutions_001 = new FieldsBuilder('solutions_001', [
    'label' => 'Our Solutions',
]);

$solutions_001
    ->addTab('Content', ['label' => 'Content'])
    ->addText('heading', [
        'label' => 'Section Heading',
        'instructions' => 'Enter the main heading for the solutions section.',
        'default_value' => 'Our solutions',
    ])
    ->addSelect('heading_tag', [
        'label' => 'Heading Tag',
        'instructions' => 'Select the HTML tag for the heading.',
        'choices' => [
            'h1' => 'H1',
            'h2' => 'H2',
            'h3' => 'H3',
            'h4' => 'H4',
            'h5' => 'H5',
            'h6' => 'H6',
            'p' => 'Paragraph',
            'span' => 'Span',
        ],
        'default_value' => 'h2',
    ])
    ->addImage('decorative_image', [
        'label' => 'Decorative Underline Image',
        'instructions' => 'Upload a decorative image to display under the heading.',
        'return_format' => 'id',
        'preview_size' => 'medium',
    ])
    ->addRepeater('solutions', [
        'label' => 'Solution Cards',
        'instructions' => 'Add solution cards for rent, sell, buy, etc.',
        'button_label' => 'Add Solution',
        'min' => 1,
        'max' => 6,
        'layout' => 'block',
    ])
        ->addText('action_word', [
            'label' => 'Action Word',
            'instructions' => 'Enter the main action word (e.g., rent, sell, buy).',
            'placeholder' => 'rent',
        ])
        ->addColorPicker('underline_color', [
            'label' => 'Underline Color',
            'instructions' => 'Choose the color for the action word underline.',
            'default_value' => '#0ea5e9',
        ])
        ->addWysiwyg('description', [
            'label' => 'Description',
            'instructions' => 'Enter the description text for this solution.',
            'default_value' => 'Not only do we give you a realistic valuation of your property, we also know how best to present your property to the marketplace.',
            'media_upload' => 0,
            'tabs' => 'all',
            'toolbar' => 'full',
        ])
        ->addLink('button_link', [
            'label' => 'Button Link',
            'instructions' => 'Add a link for the solution button.',
            'return_format' => 'array',
        ])
    ->endRepeater()

    ->addTab('Design', ['label' => 'Design'])
    ->addColorPicker('background_color', [
        'label' => 'Background Color',
        'instructions' => 'Choose the background color for the section.',
        'default_value' => '#f9fafb',
    ])

    ->addTab('Layout', ['label' => 'Layout'])
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

return $solutions_001;
