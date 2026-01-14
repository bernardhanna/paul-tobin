<?php

use StoutLogic\AcfBuilder\FieldsBuilder;

$related_projects = new FieldsBuilder('related_projects', [
    'label' => 'Related Projects',
]);

$related_projects
    ->addTab('Content', ['label' => 'Content'])
    ->addText('heading', [
        'label' => 'Section Heading',
        'instructions' => 'Enter the main heading for the related projects section.',
        'default_value' => 'Related projects',
        'required' => 1,
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
        'required' => 1,
    ])
    ->addRepeater('projects', [
        'label' => 'Projects',
        'instructions' => 'Add related projects to display in the section.',
        'button_label' => 'Add Project',
        'min' => 1,
        'max' => 6,
        'layout' => 'block',
    ])
        ->addText('project_name', [
            'label' => 'Project Name',
            'instructions' => 'Enter the name of the project.',
            'required' => 1,
            'default_value' => 'House name',
        ])
        ->addText('project_type', [
            'label' => 'Project Type',
            'instructions' => 'Enter the type or category of the project.',
            'required' => 1,
            'default_value' => 'Residential',
        ])
        ->addImage('project_image', [
            'label' => 'Project Image',
            'instructions' => 'Upload an image for the project background.',
            'return_format' => 'id',
            'preview_size' => 'medium',
            'library' => 'all',
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
        'layout' => 'table',
    ])
        ->addSelect('screen_size', [
            'label' => 'Screen Size',
            'instructions' => 'Select the screen size for this padding setting.',
            'choices' => [
                'xxs' => 'XXS',
                'xs' => 'XS',
                'mob' => 'Mobile',
                'sm' => 'Small',
                'md' => 'Medium',
                'lg' => 'Large',
                'xl' => 'XL',
                'xxl' => 'XXL',
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

return $related_projects;
