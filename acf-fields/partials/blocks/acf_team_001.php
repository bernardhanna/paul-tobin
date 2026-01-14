<?php

use StoutLogic\AcfBuilder\FieldsBuilder;

$team_001 = new FieldsBuilder('team_001', [
    'label' => 'Team Section',
]);

$team_001
    ->addTab('Content', ['label' => 'Content'])
    ->addText('heading', [
        'label' => 'Section Heading',
        'instructions' => 'Enter the main heading for the team section.',
        'default_value' => 'Some properties we have recently sold',
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
    ->addRadio('team_selection_type', [
        'label' => 'Team Selection',
        'instructions' => 'Choose how to display team members.',
        'choices' => [
            'all' => 'Show All Team Members',
            'specific' => 'Select Specific Team Members',
        ],
        'default_value' => 'all',
        'layout' => 'horizontal',
        'required' => 1,
    ])
    ->addPostObject('selected_team_members', [
        'label' => 'Select Team Members',
        'instructions' => 'Choose specific team members to display.',
        'post_type' => ['team'],
        'return_format' => 'id',
        'multiple' => 1,
        'ui' => 1,
        'conditional_logic' => [
            [
                [
                    'field' => 'team_selection_type',
                    'operator' => '==',
                    'value' => 'specific',
                ],
            ],
        ],
    ])
    ->addNumber('posts_per_page', [
        'label' => 'Number of Team Members',
        'instructions' => 'Maximum number of team members to display (only applies when showing all members).',
        'default_value' => 6,
        'min' => 1,
        'max' => 50,
        'conditional_logic' => [
            [
                [
                    'field' => 'team_selection_type',
                    'operator' => '==',
                    'value' => 'all',
                ],
            ],
        ],
    ])

    ->addTab('Design', ['label' => 'Design'])
    ->addColorPicker('background_color', [
        'label' => 'Background Color',
        'instructions' => 'Set the background color for the section.',
        'default_value' => '#f9fafb',
    ])

    ->addTab('Layout', ['label' => 'Layout'])
    ->addRepeater('padding_settings', [
        'label' => 'Padding Settings',
        'instructions' => 'Customize padding for different screen sizes.',
        'button_label' => 'Add Screen Size Padding',
        'layout' => 'table',
        'min' => 0,
        'max' => 10,
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

return $team_001;
