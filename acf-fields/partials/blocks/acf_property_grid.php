<?php

use StoutLogic\AcfBuilder\FieldsBuilder;

$property_grid = new FieldsBuilder('property_grid', [
    'label' => 'Property Grid',
]);

$property_grid
    ->addTab('Content', ['label' => 'Content'])
        ->addText('section_heading', [
            'label'         => 'Section Heading',
            'default_value' => 'Featured Properties',
        ])
        ->addSelect('section_heading_tag', [
            'label'         => 'Heading Tag',
            'choices'       => [
                'h1' => 'H1', 'h2' => 'H2', 'h3' => 'H3',
                'h4' => 'H4', 'h5' => 'H5', 'h6' => 'H6',
                'p'  => 'Paragraph', 'span' => 'Span',
            ],
            'default_value' => 'h2',
        ])
        ->addSelect('selection_type', [
            'label'         => 'How to choose properties?',
            'choices'       => [
                'filter' => 'Filter by Property Status (taxonomy)',
                'manual' => 'Pick specific properties',
            ],
            'default_value' => 'filter',
            'required'      => 1,
        ])
        // Choose one or more statuses from taxonomy
        ->addTaxonomy('property_statuses', [
            'label'         => 'Property Status',
            'instructions'  => 'Select which status terms to include.',
            'taxonomy'      => 'property_status',
            'field_type'    => 'checkbox',   // checkbox UI
            'return_format' => 'id',         // we will query via term_id
            'add_term'      => 0,
            'save_terms'    => 0,
            'load_terms'    => 0,
            'allow_null'    => 0,
        ])
            ->conditional('selection_type', '==', 'filter')
        ->addRelationship('selected_properties', [
            'label'         => 'Select Properties (Manual)',
            'instructions'  => 'Choose specific properties to display.',
            'post_type'     => ['property'],
            'filters'       => ['search', 'post_type', 'taxonomy'],
            'return_format' => 'object',
            'min'           => 0,
            'max'           => 24,
        ])
            ->conditional('selection_type', '==', 'manual')
        ->addColorPicker('background_color', [
            'label'         => 'Section Background Color',
            'default_value' => '#f9fafb',
        ])

    ->addTab('Layout', ['label' => 'Layout'])
        ->addRepeater('padding_settings', [
            'label'        => 'Padding Settings',
            'instructions' => 'Customize padding for different screen sizes.',
            'button_label' => 'Add Screen Size Padding',
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

return $property_grid;
