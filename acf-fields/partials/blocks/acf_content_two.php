<?php

use StoutLogic\AcfBuilder\FieldsBuilder;

$content_two = new FieldsBuilder('content_two', [
    'label' => 'Content Two - About Us Section',
]);

$content_two
    ->addTab('Content', ['label' => 'Content'])
    ->addText('heading', [
        'label' => 'Heading Text',
        'instructions' => 'Enter the main heading for this section.',
        'default_value' => 'We are all about our clients',
        'required' => 1,
    ])
    ->addSelect('heading_tag', [
        'label' => 'Heading Tag',
        'instructions' => 'Select the appropriate HTML heading tag for SEO and accessibility.',
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
    ->addWysiwyg('content', [
        'label' => 'Content Text',
        'instructions' => 'Enter the main content text for this section.',
        'default_value' => '<p>At Paul Tobin Estate Agents, we specialise in helping non-resident landlords and those emigrating manage, sell, or let their Irish properties with ease and confidence. With over 19 years of experience, we provide a highly personalised and boutique service, ensuring every client gets the attention they deserve: quality, tailored solutions.</p>',
        'media_upload' => 0,
        'tabs' => 'all',
        'toolbar' => 'full',
        'required' => 1,
    ])
    ->addImage('image', [
        'label' => 'Section Image',
        'instructions' => 'Upload an image for this section. Recommended size: 1072px width.',
        'return_format' => 'id',
        'preview_size' => 'medium',
        'library' => 'all',
        'required' => 1,
    ])
    ->addTrueFalse('show_decorative_bars', [
        'label' => 'Show Decorative Color Bars',
        'instructions' => 'Toggle to show or hide the decorative color bars under the heading.',
        'default_value' => 1,
        'ui' => 1,
    ])

    ->addTab('Design', ['label' => 'Design'])
    ->addColorPicker('background_color', [
        'label' => 'Background Color',
        'instructions' => 'Choose the background color for this section.',
        'default_value' => '#e5e5e5',
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

return $content_two;
