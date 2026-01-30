<?php

use StoutLogic\AcfBuilder\FieldsBuilder;

$recently_sold = new FieldsBuilder('recently_sold', [
    'label' => 'Recent/ Related Properties',
]);

$recently_sold
->addTab('Content', ['label' => 'Content'])
    ->addText('heading', [
        'label'         => 'Section Heading',
        'instructions'  => 'Main heading for this section.',
        'default_value' => '',
        'required'      => 1,
    ])
    ->addSelect('heading_tag', [
        'label'         => 'Heading Tag',
        'instructions'  => 'Select the HTML tag used for the heading.',
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
        'label'         => 'Manually Select Properties',
        'instructions'  => 'Pick up to the limit below. If set, this overrides filters and auto-related logic.',
        'post_type'     => ['property'],
        'filters'       => ['search', 'post_type', 'taxonomy'],
        'return_format' => 'object',
        'min'           => 0,
        'max'           => 12,
    ])
    ->addSelect('filter_by', [
        'label'        => 'Filter By Taxonomy',
        'instructions' => 'Choose a taxonomy to filter properties when no manual selection is set.',
        'choices'      => [
            'none'            => 'None',
            'property_status' => 'Property Status',
            'property_type'   => 'Property Type',
        ],
        'default_value'=> 'property_status',
        'required'     => 1,
    ])
    ->addTaxonomy('property_status_terms', [
        'label'        => 'Property Status Terms',
        'instructions' => 'Choose one or more statuses to filter by.',
        'taxonomy'     => 'property_status',
        'field_type'   => 'checkbox',
        'return_format'=> 'object',
        'add_term'     => 0,
        'save_terms'   => 0,
        'load_terms'   => 0,
        'multiple'     => 1,
        'allow_null'   => 1,
    ])->conditional('filter_by', '==', 'property_status')
    ->addTaxonomy('property_type_terms', [
        'label'        => 'Property Type Terms',
        'instructions' => 'Choose one or more types to filter by.',
        'taxonomy'     => 'property_type',
        'field_type'   => 'checkbox',
        'return_format'=> 'object',
        'add_term'     => 0,
        'save_terms'   => 0,
        'load_terms'   => 0,
        'multiple'     => 1,
        'allow_null'   => 1,
    ])->conditional('filter_by', '==', 'property_type')
    ->addTrueFalse('auto_related_on_single', [
        'label'        => 'Auto “Related” on Single Property',
        'instructions' => 'When viewing a single Property, automatically show related properties based on the current post’s Property Status (overrides filter when no manual selection).',
        'default_value'=> 1,
        'ui'           => 1,
    ])
    ->addNumber('limit', [
        'label'         => 'Items Limit',
        'instructions'  => 'How many properties to show (ignored if you manually select items that exceed this).',
        'default_value' => 3,
        'min'           => 1,
        'max'           => 24,
        'step'          => 1,
    ])
    ->addSelect('order_by', [
        'label'        => 'Order By',
        'choices'      => [
            'date'     => 'Date',
            'modified' => 'Last Modified',
            'title'    => 'Title',
            'menu_order' => 'Menu Order',
            'rand'     => 'Random',
        ],
        'default_value' => 'modified',
    ])
    ->addSelect('order', [
        'label'        => 'Order',
        'choices'      => [
            'DESC' => 'DESC',
            'ASC'  => 'ASC',
        ],
        'default_value' => 'DESC',
    ])

->addTab('Design', ['label' => 'Design'])
    ->addText('background_color', [
        'label'         => 'Background Color (CSS value)',
        'instructions'  => 'Any valid CSS color (e.g., #fff, rgba(0,0,0,.05), var(--color-bg)).',
        'default_value' => '',
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
