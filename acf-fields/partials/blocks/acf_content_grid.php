<?php

use StoutLogic\AcfBuilder\FieldsBuilder;

$content_grid = new FieldsBuilder('content_grid', [
    'label' => 'Content Grid',
]);

$content_grid
    ->addTab('Content', ['label' => 'Content'])
        ->addText('heading', [
            'label'         => 'Section Heading',
            'instructions'  => 'Enter the main heading for this section.',
            'default_value' => 'We take care of it all',
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
        ->addWysiwyg('content', [
            'label'         => 'Section Description',
            'instructions'  => 'Add the main description content for this section.',
            'default_value' => '<p>We offer a comprehensive Property Letting and Managing service relieving the commitment of being a full-time landlord.</p>',
            'media_upload'  => 0,
            'tabs'          => 'all',
            'toolbar'       => 'full',
        ])

        // Row-based grid
        ->addRepeater('grid_rows', [
            'label'        => 'Grid Rows',
            'instructions' => 'Add rows. The number of items in a row controls the number of columns (1 → 1 col, 2 → 2 cols, etc.).',
            'layout'       => 'block',
            'button_label' => 'Add Grid Row',
            'min'          => 1,
        ])
            ->addRepeater('items', [
                'label'        => 'Grid Items',
                'instructions' => 'Add items for this row.',
                'button_label' => 'Add Grid Item',
                'layout'       => 'block',
                'min'          => 1,
                'max'          => 12,
            ])
                ->addWysiwyg('content', [
                    'label'         => 'Item Content',
                    'instructions'  => 'Enter the content for this grid item.',
                    'default_value' => '<p>We can refurbish and dress your property for market</p>',
                    'media_upload'  => 0,
                    'tabs'          => 'visual',
                    'toolbar'       => 'basic',
                ])
                ->addColorPicker('item_background_color', [
                    'label'         => 'Item Background Color',
                    'instructions'  => 'Set the background color for this grid item.',
                    'default_value' => '#EDEDED',
                ])
                // Fixed palette for the decorative bar color
                ->addSelect('bar_color', [
                    'label'         => 'Decorative Bar Color',
                    'instructions'  => 'Choose from the approved palette.',
                    'choices'       => [
                        '#0098D8' => 'Blue (#0098D8)',
                        '#74AF27' => 'Green (#74AF27)',
                        '#EF7B10' => 'Orange (#EF7B10)',
                        '#B6C0CB' => 'Grey (#B6C0CB)',
                        '#0A1119' => 'Black (#0A1119)',
                    ],
                    'default_value' => '#0098D8',
                    'required'      => 1,
                ])
            ->endRepeater()
        ->endRepeater()

    ->addTab('Design', ['label' => 'Design'])
        ->addColorPicker('background_color', [
            'label'         => 'Section Background Color',
            'instructions'  => 'Set the background color for this section.',
            'default_value' => '#FFFFFF',
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
                    'sm'  => 'sm',
                    'md'  => 'md',
                    'lg'  => 'lg',
                    'xl'  => 'xl',
                    'xxl' => 'xxl',
                    'ultrawide' => 'ultrawide',
                ],
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

return $content_grid;
