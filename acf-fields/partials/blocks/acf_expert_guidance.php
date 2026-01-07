<?php

use StoutLogic\AcfBuilder\FieldsBuilder;

$expert_guidance = new FieldsBuilder('expert_guidance', [
    'label' => 'Expert Guidance Section',
]);

$expert_guidance
    ->addTab('Content', ['label' => 'Content'])
    ->addText('heading', [
        'label' => 'Main Heading',
        'instructions' => 'Enter the main heading for the section.',
        'default_value' => 'Stress-free guidance for non-residents landlords & expats',
    ])
    ->addSelect('heading_tag', [
        'label' => 'Main Heading Tag',
        'instructions' => 'Select the HTML tag for the main heading.',
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
    ->addText('left_heading', [
        'label' => 'Left Block Heading',
        'instructions' => 'Enter the heading for the left content block.',
        'default_value' => 'Expert guidance',
    ])
    ->addSelect('left_heading_tag', [
        'label' => 'Left Heading Tag',
        'instructions' => 'Select the HTML tag for the left heading.',
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
        'default_value' => 'h3',
    ])
    ->addWysiwyg('left_content', [
        'label' => 'Left Block Content',
        'instructions' => 'Enter the content for the left block.',
        'default_value' => '<p>Whether you\'re moving abroad or already living overseas, we handle everything from lettings, property management, sales, and tax collection services.</p>',
        'media_upload' => 0,
        'tabs' => 'all',
        'toolbar' => 'full',
    ])
    ->addImage('left_image', [
        'label' => 'Left Block Image',
        'instructions' => 'Upload an image for the left content block.',
        'return_format' => 'id',
        'preview_size' => 'medium',
    ])
    ->addText('right_heading', [
        'label' => 'Right Block Heading',
        'instructions' => 'Enter the heading for the right content block.',
        'default_value' => 'Hassle-free property sales & lettings',
    ])
    ->addSelect('right_heading_tag', [
        'label' => 'Right Heading Tag',
        'instructions' => 'Select the HTML tag for the right heading.',
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
        'default_value' => 'h3',
    ])
    ->addWysiwyg('right_content', [
        'label' => 'Right Block Content',
        'instructions' => 'Enter the content for the right block.',
        'default_value' => '<p>Whether it\'s a city apartment, a period home, or a country estate, we handle everything from refurbishment and maintenance to tenant sourcing and management to rent collection to ensure a smooth process in every aspect.</p>',
        'media_upload' => 0,
        'tabs' => 'all',
        'toolbar' => 'full',
    ])
    ->addImage('right_image', [
        'label' => 'Right Block Image',
        'instructions' => 'Upload an image for the right content block.',
        'return_format' => 'id',
        'preview_size' => 'medium',
    ])
    ->addLink('cta_button', [
        'label' => 'Call to Action Button',
        'instructions' => 'Add a call to action button link.',
        'return_format' => 'array',
    ])
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
        'min' => 0,
        'max' => 10,
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
            'xl' => 'Extra Large',
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

return $expert_guidance;
