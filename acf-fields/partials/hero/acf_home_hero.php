<?php

use StoutLogic\AcfBuilder\FieldsBuilder;

$hero_001 = new FieldsBuilder('hero_001', [
    'label' => 'Home Hero with Video',
]);

$hero_001
    ->addTab('Content', ['label' => 'Content'])
        ->addText('heading', [
            'label'         => 'Heading Text',
            'instructions'  => 'Enter the main heading text for the hero section.',
            'default_value' => 'Your Irish property, expertly handled while you\'re abroad.',
            'required'      => 0,
        ])
        ->addSelect('heading_tag', [
            'label'         => 'Heading Tag',
            'instructions'  => 'Select the HTML tag for the heading.',
            'choices'       => [
                'h1' => 'H1', 'h2' => 'H2', 'h3' => 'H3',
                'h4' => 'H4', 'h5' => 'H5', 'h6' => 'H6',
                'p'  => 'Paragraph', 'span' => 'Span',
            ],
            'default_value' => 'h1',
            'required'      => 0,
        ])
        ->addWysiwyg('description', [
            'label'         => 'Description Text',
            'instructions'  => 'Enter the description text that appears below the heading.',
            'default_value' => 'From refurbishment and tenant sourcing to management, rent collection, sales, and tax agent services, all handled for you.',
            'media_upload'  => 0,
            'tabs'          => 'all',
            'toolbar'       => 'full',
        ])
        ->addLink('button', [
            'label'         => 'Call to Action Button',
            'instructions'  => 'Configure the main CTA button.',
            'return_format' => 'array',
        ])

    // Background Media Settings
    ->addSelect('background_type', [
        'label'         => 'Background Type',
        'instructions'  => 'Choose between image or video background.',
        'choices'       => [
            'image' => 'Image',
            'video' => 'Video',
        ],
        'default_value' => 'image',
        'required'      => 0,
    ])
    ->addImage('background_image', [
        'label'             => 'Background Image',
        'instructions'      => 'Upload the background image.',
        'return_format'     => 'id',
        'preview_size'      => 'medium',
        'conditional_logic' => [[['field' => 'background_type','operator' => '==','value' => 'image']]],
    ])
    ->addSelect('background_video_type', [
        'label'             => 'Video Type',
        'instructions'      => 'Select the type of video background.',
        'choices'           => [
            'local'   => 'Local Video File',
            'youtube' => 'YouTube',
            'vimeo'   => 'Vimeo',
        ],
        'default_value'     => 'local',
        'conditional_logic' => [[['field' => 'background_type','operator' => '==','value' => 'video']]],
    ])
    ->addFile('background_video_file', [
        'label'             => 'Video File',
        'instructions'      => 'Upload a video file (MP4 recommended).',
        'return_format'     => 'id',
        'mime_types'        => 'mp4,webm,ogg',
        'conditional_logic' => [[
            ['field' => 'background_type','operator' => '==','value' => 'video'],
            ['field' => 'background_video_type','operator' => '==','value' => 'local'],
        ]],
    ])
    ->addUrl('background_video_youtube', [
        'label'             => 'YouTube URL',
        'instructions'      => 'Enter the full YouTube video URL.',
        'conditional_logic' => [[
            ['field' => 'background_type','operator' => '==','value' => 'video'],
            ['field' => 'background_video_type','operator' => '==','value' => 'youtube'],
        ]],
    ])
    ->addUrl('background_video_vimeo', [
        'label'             => 'Vimeo URL',
        'instructions'      => 'Enter the full Vimeo video URL.',
        'conditional_logic' => [[
            ['field' => 'background_type','operator' => '==','value' => 'video'],
            ['field' => 'background_video_type','operator' => '==','value' => 'vimeo'],
        ]],
    ])
    ->addImage('video_poster', [
        'label'             => 'Video Poster Image',
        'instructions'      => 'Upload a poster image that displays before the video loads.',
        'return_format'     => 'id',
        'preview_size'      => 'medium',
        'conditional_logic' => [[['field' => 'background_type','operator' => '==','value' => 'video']]],
    ])

    ->addTab('Design', ['label' => 'Design'])
        ->addTrueFalse('overlay_enabled', [
            'label'         => 'Enable Background Overlay',
            'instructions'  => 'Add a color overlay on top of the background.',
            'default_value' => 0,
        ])
        ->addColorPicker('overlay_color', [
            'label'             => 'Overlay Color',
            'instructions'      => 'Choose the overlay color.',
            'default_value'     => '#000000',
            'conditional_logic' => [[['field' => 'overlay_enabled','operator' => '==','value' => '1']]],
        ])
        ->addRange('overlay_opacity', [
            'label'             => 'Overlay Opacity',
            'instructions'      => 'Set the opacity of the overlay (0-100%).',
            'min'               => 0,
            'max'               => 100,
            'step'              => 5,
            'default_value'     => 50,
            'append'            => '%',
            'conditional_logic' => [[['field' => 'overlay_enabled','operator' => '==','value' => '1']]],
        ])
        ->addColorPicker('content_box_bg_color', [
            'label'         => 'Content Box Background Color',
            'instructions'  => 'Set the background color for the content box.',
            'default_value' => '#EDEDED',
        ])
        ->addRange('content_box_bg_opacity', [
            'label'         => 'Content Box Background Opacity',
            'instructions'  => 'Set the opacity of the content box background (0-100%).',
            'min'           => 0,
            'max'           => 100,
            'step'          => 5,
            'default_value' => 90,
            'append'        => '%',
        ])
        ->addColorPicker('content_box_border_color', [
            'label'         => 'Content Box Border Color',
            'instructions'  => 'Set the border color for the content box.',
            'default_value' => '#0f172a',
        ])
        ->addNumber('content_box_border_width', [
            'label'         => 'Content Box Border Width',
            'instructions'  => 'Set the border width in pixels.',
            'min'           => 0,
            'max'           => 10,
            'step'          => 1,
            'default_value' => 4,
            'append'        => 'px',
        ])

    ->addTab('Layout', ['label' => 'Layout'])
        ->addSelect('content_box_position', [
            'label'         => 'Content Box Position',
            'instructions'  => 'Choose where to position the content box.',
            'choices'       => [
                'left'   => 'Left',
                'center' => 'Center',
                'right'  => 'Right',
            ],
            'default_value' => 'left',
        ])
        ->addSelect('max_height', [
            'label'         => 'Max Height',
            'instructions'  => 'Choose the hero height on desktop.',
            'choices'       => [
                '500' => '500px (small)',
                '665' => '665px',
            ],
            'default_value' => '500',
            'required'      => 0,
        ])
        ->addRepeater('padding_settings', [
            'label'        => 'Padding Settings',
            'instructions' => 'Customize padding for different screen sizes.',
            'button_label' => 'Add Screen Size Padding',
            'min'          => 0,
            'max'          => 10,
        ])
            ->addSelect('screen_size', [
                'label'       => 'Screen Size',
                'instructions'=> 'Select the screen size for this padding setting.',
                'choices'     => [
                    'xxs' => 'XXS', 'xs' => 'XS', 'mob' => 'Mobile', 'sm' => 'Small',
                    'md' => 'Medium', 'lg' => 'Large', 'xl' => 'Extra Large',
                    'xxl' => 'XXL', 'ultrawide' => 'Ultrawide',
                ],
                'required'    => 1,
            ])
            ->addNumber('padding_top', [
                'label'         => 'Padding Top',
                'instructions'  => 'Set the top padding in rem.',
                'min'           => 0,
                'max'           => 20,
                'step'          => 0.1,
                'default_value' => 5,
                'append'        => 'rem',
            ])
            ->addNumber('padding_bottom', [
                'label'         => 'Padding Bottom',
                'instructions'  => 'Set the bottom padding in rem.',
                'min'           => 0,
                'max'           => 20,
                'step'          => 0.1,
                'default_value' => 5,
                'append'        => 'rem',
            ])
        ->endRepeater();

return $hero_001;
