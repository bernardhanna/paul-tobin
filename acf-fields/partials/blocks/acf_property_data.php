<?php
/**
 * ACF Builder: Property Data
 * - Hard-coded labels (Sector, Year, Client, Size) – each only shows if it has a value.
 * - Extra rows repeater appears after Size.
 * - Right column text with AUTO “Read more” (based on measured content height).
 */

use StoutLogic\AcfBuilder\FieldsBuilder;

$property_data = new FieldsBuilder('property_data', [
    'label' => 'Property Data',
]);

$property_data
    ->addTab('content_tab', [
        'label' => 'Content',
        'placement' => 'left',
    ])
        ->addText('sector', [
            'label' => 'Sector',
            'instructions' => 'e.g., Residential',
            'default_value' => '',
        ])
        ->addText('year', [
            'label' => 'Year',
            'instructions' => 'e.g., 2016',
            'default_value' => '',
        ])
        ->addTrueFalse('uppercase_year', [
            'label' => 'Uppercase Year',
            'ui' => 1,
            'default_value' => 1,
        ])
        ->addText('client', [
            'label' => 'Client',
            'instructions' => 'e.g., Client name',
            'default_value' => '',
        ])
        ->addWysiwyg('size', [
            'label' => 'Size (Rich Text)',
            'instructions' => 'e.g., “Area: 2500 square feet<br>Height: 143 meters<br>Stories: 42”',
            'tabs' => 'visual',
            'media_upload' => 0,
            'delay' => 1,
        ])
        ->addRepeater('extra_rows', [
            'label' => 'Extra Rows (appear after Size)',
            'instructions' => 'Optional additional key/value rows.',
            'button_label' => 'Add Row',
            'layout' => 'row',
            'min' => 0,
            'max' => 20,
        ])
            ->addText('label', [
                'label' => 'Label',
                'wrapper' => ['width' => 40],
            ])
            ->addWysiwyg('value', [
                'label' => 'Value',
                'tabs' => 'visual',
                'media_upload' => 0,
                'delay' => 1,
                'wrapper' => ['width' => 60],
            ])
            ->addTrueFalse('uppercase_value', [
                'label' => 'Uppercase Value',
                'ui' => 1,
                'default_value' => 0,
            ])
        ->endRepeater()
        ->addWysiwyg('right_text', [
            'label' => 'Right Column: Text',
            'instructions' => 'Main paragraph content.',
            'tabs' => 'visual',
            'media_upload' => 0,
            'delay' => 1,
        ])
        ->addText('read_more_label', [
            'label' => 'Read more label',
            'default_value' => 'Read more',
        ])
        ->addText('read_less_label', [
            'label' => 'Read less label',
            'default_value' => 'Read less',
        ])

    ->addTab('layout_tab', [
        'label' => 'Layout',
        'placement' => 'left',
    ])
        ->addRepeater('padding_settings', [
            'label' => 'Padding Settings',
            'instructions' => 'Customize padding for different screen sizes.',
            'button_label' => 'Add Screen Size Padding',
            'layout' => 'table',
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
                'wrapper' => ['width' => 25],
            ])
            ->addNumber('padding_top', [
                'label' => 'Padding Top',
                'instructions' => 'Set the top padding in rem.',
                'min' => 0,
                'max' => 20,
                'step' => 0.1,
                'append' => 'rem',
                'wrapper' => ['width' => 25],
            ])
            ->addNumber('padding_bottom', [
                'label' => 'Padding Bottom',
                'instructions' => 'Set the bottom padding in rem.',
                'min' => 0,
                'max' => 20,
                'step' => 0.1,
                'append' => 'rem',
                'wrapper' => ['width' => 25],
            ])
        ->endRepeater();

return $property_data;
