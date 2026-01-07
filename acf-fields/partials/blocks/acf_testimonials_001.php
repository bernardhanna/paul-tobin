<?php

use StoutLogic\AcfBuilder\FieldsBuilder;

$testimonials_001 = new FieldsBuilder('testimonials_001', [
    'label' => 'Testimonials Section',
]);

$testimonials_001
    ->addTab('Content', ['label' => 'Content'])
    ->addText('heading', [
        'label' => 'Section Heading',
        'instructions' => 'Enter the main heading for the testimonials section.',
        'default_value' => 'What people say about us',
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
    ->addRadio('testimonial_source', [
        'label' => 'Testimonial Source',
        'instructions' => 'Choose whether to manually add testimonials or automatically pull from the testimonial post type.',
        'choices' => [
            'auto' => 'Auto (from Testimonial CPT)',
            'manual' => 'Manual Entry',
        ],
        'default_value' => 'auto',
        'layout' => 'horizontal',
    ])
    ->addNumber('number_of_testimonials', [
        'label' => 'Number of Testimonials',
        'instructions' => 'How many testimonials to display (for auto mode).',
        'default_value' => 6,
        'min' => 1,
        'max' => 20,
        'conditional_logic' => [
            [
                [
                    'field' => 'testimonial_source',
                    'operator' => '==',
                    'value' => 'auto',
                ],
            ],
        ],
    ])
    ->addRepeater('manual_testimonials', [
        'label' => 'Manual Testimonials',
        'instructions' => 'Add testimonials manually.',
        'button_label' => 'Add Testimonial',
        'min' => 1,
        'max' => 10,
        'conditional_logic' => [
            [
                [
                    'field' => 'testimonial_source',
                    'operator' => '==',
                    'value' => 'manual',
                ],
            ],
        ],
    ])
        ->addText('name', [
            'label' => 'Name',
            'instructions' => 'Enter the person\'s name.',
            'required' => 1,
        ])
        ->addText('title', [
            'label' => 'Title/Position',
            'instructions' => 'Enter the person\'s job title or position.',
        ])
        ->addTextarea('testimonial', [
            'label' => 'Testimonial',
            'instructions' => 'Enter the testimonial text.',
            'required' => 1,
            'rows' => 4,
        ])
    ->endRepeater()

    ->addTab('Design', ['label' => 'Design'])
    ->addColorPicker('background_color', [
        'label' => 'Background Color',
        'instructions' => 'Set the background color for the testimonials section.',
        'default_value' => '#e5e7eb',
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
            'default_value' => 3,
        ])
    ->endRepeater();

return $testimonials_001;
