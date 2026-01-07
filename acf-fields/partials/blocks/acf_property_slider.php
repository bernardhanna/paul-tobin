<?php

use StoutLogic\AcfBuilder\FieldsBuilder;

$property_slider = new FieldsBuilder('property_slider', [
    'label' => 'Property Slider',
]);

$property_slider
    ->addTab('Content', ['label' => 'Content'])
    ->addText('section_heading', [
        'label' => 'Section Heading',
        'instructions' => 'Enter the main heading for the property slider section.',
        'default_value' => 'A selection of properties that we have recently handled',
    ])
    ->addSelect('section_heading_tag', [
        'label' => 'Heading Tag',
        'instructions' => 'Select the HTML tag for the section heading.',
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
    ->addTrueFalse('auto_select_properties', [
        'label' => 'Auto Select Properties',
        'instructions' => 'Enable to automatically select properties based on criteria below, or disable to manually select specific properties.',
        'default_value' => 1,
    ])
    ->addNumber('number_of_properties', [
        'label' => 'Number of Properties',
        'instructions' => 'How many properties to display in the slider.',
        'default_value' => 5,
        'min' => 1,
        'max' => 20,
        'conditional_logic' => [
            [
                [
                    'field' => 'auto_select_properties',
                    'operator' => '==',
                    'value' => '1',
                ],
            ],
        ],
    ])
    ->addSelect('property_order', [
        'label' => 'Property Order',
        'instructions' => 'Choose how to order the automatically selected properties.',
        'choices' => [
            'newest' => 'Newest First',
            'oldest' => 'Oldest First',
            'random' => 'Random Order',
        ],
        'default_value' => 'newest',
        'conditional_logic' => [
            [
                [
                    'field' => 'auto_select_properties',
                    'operator' => '==',
                    'value' => '1',
                ],
            ],
        ],
    ])
    ->addPostObject('selected_properties', [
        'label' => 'Select Properties',
        'instructions' => 'Manually select which properties to display in the slider.',
        'post_type' => ['property'],
        'return_format' => 'id',
        'multiple' => 1,
        'allow_null' => 0,
        'conditional_logic' => [
            [
                [
                    'field' => 'auto_select_properties',
                    'operator' => '==',
                    'value' => '0',
                ],
            ],
        ],
    ])

    ->addTab('Design', ['label' => 'Design'])
    ->addColorPicker('background_color', [
        'label' => 'Background Color',
        'instructions' => 'Set the background color for the entire section.',
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
            'xxs' => 'XXS (Extra Extra Small)',
            'xs' => 'XS (Extra Small)',
            'mob' => 'Mobile',
            'sm' => 'SM (Small)',
            'md' => 'MD (Medium)',
            'lg' => 'LG (Large)',
            'xl' => 'XL (Extra Large)',
            'xxl' => 'XXL (Extra Extra Large)',
            'ultrawide' => 'Ultrawide',
        ],
        'required' => 1,
    ])
    ->addNumber('padding_top', [
        'label' => 'Padding Top',
        'instructions' => 'Set the top padding in rem units.',
        'min' => 0,
        'max' => 20,
        'step' => 0.1,
        'append' => 'rem',
        'default_value' => 5,
    ])
    ->addNumber('padding_bottom', [
        'label' => 'Padding Bottom',
        'instructions' => 'Set the bottom padding in rem units.',
        'min' => 0,
        'max' => 20,
        'step' => 0.1,
        'append' => 'rem',
        'default_value' => 5,
    ])
    ->endRepeater();

return $property_slider;
