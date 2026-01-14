<?php

use StoutLogic\AcfBuilder\FieldsBuilder;

$recently_sold = new FieldsBuilder('recently_sold', [
    'label' => 'Recenty Sold',
]);

$recently_sold
->addTab('Content', ['label' => 'Content'])
    ->addText('heading', [
        'label'         => 'Section Heading',
        'instructions'  => 'Enter the main heading for the recently sold properties section.',
        'default_value' => 'Some properties we have recently sold',
        'required'      => 1,
    ])
    ->addSelect('heading_tag', [
        'label'         => 'Heading Tag',
        'instructions'  => 'Select the HTML tag for the heading.',
        'choices'       => [
            'h1'  => 'H1',
            'h2'  => 'H2',
            'h3'  => 'H3',
            'h4'  => 'H4',
            'h5'  => 'H5',
            'h6'  => 'H6',
            'p'   => 'Paragraph',
            'span'=> 'Span',
        ],
        'default_value' => 'h2',
        'required'      => 1,
    ])
    ->addRelationship('selected_properties', [
        'label'         => 'Select Properties',
        'instructions'  => 'Choose up to 3 sold properties to display. Leave empty to automatically show the 3 most recent sold properties.',
        'post_type'     => ['property'],
        // Keep taxonomy filter UI available in the picker; not restrictive.
        'taxonomy'      => ['property_status'],
        'filters'       => ['search', 'post_type', 'taxonomy'],
        'return_format' => 'object',
        'min'           => 0,
        'max'           => 3,
    ])

->addTab('Layout', ['label' => 'Layout'])
    ->addRepeater('padding_settings', [
        'label'         => 'Padding Settings',
        'instructions'  => 'Customize padding for different screen sizes.',
        'button_label'  => 'Add Screen Size Padding',
        'layout'        => 'table',
    ])
        ->addSelect('screen_size', [
            'label'   => 'Screen Size',
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
            'required' => 1,
        ])
        ->addNumber('padding_top', [
            'label'         => 'Padding Top',
            'instructions'  => 'Set the top padding in rem.',
            'min'           => 0,
            'max'           => 20,
            'step'          => 0.1,
            'append'        => 'rem',
            'default_value' => 5,
        ])
        ->addNumber('padding_bottom', [
            'label'         => 'Padding Bottom',
            'instructions'  => 'Set the bottom padding in rem.',
            'min'           => 0,
            'max'           => 20,
            'step'          => 0.1,
            'append'        => 'rem',
            'default_value' => 5,
        ])
    ->endRepeater();

return $recently_sold;
