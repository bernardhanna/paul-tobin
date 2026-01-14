<?php

use StoutLogic\AcfBuilder\FieldsBuilder;

$contact_section = new FieldsBuilder('contact_section', [
    'label' => 'Get In Touch Contact Section',
]);

$contact_section
    ->addTab('Content', ['label' => 'Content'])

    // Left Column Fields
    ->addText('left_heading', [
        'label' => 'Left Column Heading',
        'instructions' => 'Enter the heading for the left column (e.g., "Got a question?")',
        'default_value' => 'Got a question?',
        'required' => 1,
    ])
    ->addSelect('left_heading_tag', [
        'label' => 'Left Heading Tag',
        'instructions' => 'Select the HTML tag for the left heading',
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
    ->addWysiwyg('left_description', [
        'label' => 'Left Column Description',
        'instructions' => 'Enter the description text for the left column',
        'default_value' => 'Interested in any listed property? Book a consultation with Paul.',
        'media_upload' => 0,
        'tabs' => 'visual,text',
        'toolbar' => 'basic',
        'required' => 1,
    ])
    ->addLink('left_button', [
        'label' => 'Left Column Button',
        'instructions' => 'Configure the button for the left column',
        'return_format' => 'array',
        'required' => 1,
    ])
    ->addTextarea('calendly_shortcode', [
        'label' => 'Calendly Shortcode',
        'instructions' => 'Enter the Calendly shortcode or embed code',
        'placeholder' => '[calendly url="your-calendly-url"]',
        'rows' => 3,
    ])

    // Right Column Fields
    ->addText('right_heading', [
        'label' => 'Right Column Heading',
        'instructions' => 'Enter the heading for the right column (e.g., "About your property")',
        'default_value' => 'About your property',
        'required' => 1,
    ])
    ->addSelect('right_heading_tag', [
        'label' => 'Right Heading Tag',
        'instructions' => 'Select the HTML tag for the right heading',
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
        'required' => 1,
    ])
    ->addWysiwyg('right_description', [
        'label' => 'Right Column Description',
        'instructions' => 'Enter the description text for the right column',
        'default_value' => 'Curious what your home\'s worth? Book your free evaluation today.',
        'media_upload' => 0,
        'tabs' => 'visual,text',
        'toolbar' => 'basic',
        'required' => 1,
    ])
    ->addLink('right_button', [
        'label' => 'Right Column Button',
        'instructions' => 'Configure the button for the right column',
        'return_format' => 'array',
        'required' => 1,
    ])

    // Video Configuration
    ->addSelect('video_type', [
        'label' => 'Video Type',
        'instructions' => 'Choose the type of video to display in the right column',
        'choices' => [
            'youtube' => 'YouTube Video',
            'local' => 'Local Video File',
        ],
        'default_value' => 'youtube',
        'required' => 1,
    ])
    ->addUrl('youtube_url', [
        'label' => 'YouTube Video URL',
        'instructions' => 'Enter the full YouTube video URL',
        'placeholder' => 'https://www.youtube.com/watch?v=VIDEO_ID',
        'conditional_logic' => [
            [
                [
                    'field' => 'video_type',
                    'operator' => '==',
                    'value' => 'youtube',
                ],
            ],
        ],
    ])
    ->addFile('local_video', [
        'label' => 'Local Video File',
        'instructions' => 'Upload a video file (MP4, WebM, etc.)',
        'return_format' => 'id',
        'mime_types' => 'mp4,webm,ogg,mov,avi',
        'conditional_logic' => [
            [
                [
                    'field' => 'video_type',
                    'operator' => '==',
                    'value' => 'local',
                ],
            ],
        ],
    ])

    ->addTab('Design', ['label' => 'Design'])
    ->addColorPicker('background_color', [
        'label' => 'Background Color',
        'instructions' => 'Set the background color for the entire section',
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
            'instructions' => 'Select the screen size for this padding setting',
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
            'instructions' => 'Set the top padding in rem units',
            'min' => 0,
            'max' => 20,
            'step' => 0.1,
            'append' => 'rem',
            'default_value' => 5,
        ])
        ->addNumber('padding_bottom', [
            'label' => 'Padding Bottom',
            'instructions' => 'Set the bottom padding in rem units',
            'min' => 0,
            'max' => 20,
            'step' => 0.1,
            'append' => 'rem',
            'default_value' => 5,
        ])
    ->endRepeater();

return $contact_section;
