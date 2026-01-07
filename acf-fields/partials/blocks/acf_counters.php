<?php

use StoutLogic\AcfBuilder\FieldsBuilder;

$counters_001 = new FieldsBuilder('counters_001', [
    'label' => 'Counters Section',
]);

$counters_001
    ->addTab('Content', ['label' => 'Content'])
    ->addText('heading', [
        'label' => 'Section Heading',
        'instructions' => 'Enter the main heading for the counters section.',
        'default_value' => 'Creativity delivers results',
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
            'p'   => 'Paragraph',
            'span'=> 'Span',
        ],
        'default_value' => 'h2',
    ])
    ->addRepeater('counters', [
        'label' => 'Counter Items',
        'instructions' => 'Add up to 3 counter items. Numbers will animate when the section comes into view.',
        'button_label' => 'Add Counter',
        'min' => 1,
        'max' => 3,
        'layout' => 'block',
    ])
        ->addNumber('number', [
            'label' => 'Counter Number',
            'instructions' => 'Enter the target number for the counter animation.',
            'required' => 1,
            'min' => 0,
            'max' => 9999,
            'default_value' => 19,
        ])
        ->addText('suffix', [
            'label' => 'Number Suffix',
            'instructions' => 'Optional suffix to display after the number (e.g., "+", "%", "K").',
            'default_value' => '+',
        ])
        ->addText('title', [
            'label' => 'Counter Title',
            'instructions' => 'Enter the title/label for this counter.',
            'required' => 1,
            'default_value' => 'years of experience',
        ])
        ->addWysiwyg('description', [
            'label' => 'Counter Description',
            'instructions' => 'Enter a brief description for this counter.',
            'tabs' => 'all',
            'media_upload' => 0,
            'delay' => 0,
            'toolbar' => 'basic',
            'default_value' => 'We are a boutique practice, offering professional bespoke services to property owners from more than 19 years.',
        ])
        ->addColorPicker('item_border_color', [
            'label' => 'Counter Border Color',
            'instructions' => 'Optional: override the global border color for this counter.',
            'default_value' => '',
        ])
    ->endRepeater()

    ->addTab('Design', ['label' => 'Design'])
    ->addColorPicker('background_color', [
        'label' => 'Background Color',
        'instructions' => 'Choose the background color for the section.',
        'default_value' => '#f8fafc',
    ])
    ->addColorPicker('border_color', [
        'label' => 'Border Color',
        'instructions' => 'Choose the color for the decorative elements and counter borders.',
        'default_value' => '#3b82f6',
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

return $counters_001;
