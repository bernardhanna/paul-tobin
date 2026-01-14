<?php

use StoutLogic\AcfBuilder\FieldsBuilder;

$video_image_section = new FieldsBuilder('video_image_section', [
    'label' => 'Video/Image Section',
]);

$video_image_section
    ->addTab('Content', ['label' => 'Content'])
    ->addSelect('media_type', [
        'label' => 'Media Type',
        'instructions' => 'Choose the type of media to display.',
        'choices' => [
            'video' => 'Local Video',
            'youtube' => 'YouTube Video',
            'image' => 'Static Image',
        ],
        'default_value' => 'image',
        'required' => 1,
    ])
    ->addFile('video_file', [
        'label' => 'Video File',
        'instructions' => 'Upload a video file (MP4 recommended).',
        'mime_types' => 'mp4,mov,avi,wmv,webm',
        'conditional_logic' => [
            [
                [
                    'field' => 'media_type',
                    'operator' => '==',
                    'value' => 'video',
                ],
            ],
        ],
    ])
    ->addUrl('youtube_url', [
        'label' => 'YouTube URL',
        'instructions' => 'Enter the full YouTube video URL.',
        'placeholder' => 'https://www.youtube.com/watch?v=...',
        'conditional_logic' => [
            [
                [
                    'field' => 'media_type',
                    'operator' => '==',
                    'value' => 'youtube',
                ],
            ],
        ],
    ])
    ->addImage('poster_image', [
        'label' => 'Video Poster Image',
        'instructions' => 'Optional poster image for the video (shown before play).',
        'return_format' => 'id',
        'preview_size' => 'medium',
        'conditional_logic' => [
            [
                [
                    'field' => 'media_type',
                    'operator' => '==',
                    'value' => 'video',
                ],
            ],
        ],
    ])
    ->addImage('image', [
        'label' => 'Image',
        'instructions' => 'Select an image to display.',
        'return_format' => 'id',
        'preview_size' => 'medium',
        'required' => 1,
        'conditional_logic' => [
            [
                [
                    'field' => 'media_type',
                    'operator' => '==',
                    'value' => 'image',
                ],
            ],
        ],
    ])
    ->addTrueFalse('show_play_button', [
        'label' => 'Show Play Button Overlay',
        'instructions' => 'Display a play button icon overlay on videos.',
        'default_value' => 1,
        'conditional_logic' => [
            [
                [
                    'field' => 'media_type',
                    'operator' => '==',
                    'value' => 'video',
                ],
            ],
        ],
    ])

    ->addTab('Design', ['label' => 'Design'])
    ->addColorPicker('background_color', [
        'label' => 'Background Color',
        'instructions' => 'Set the background color for the section.',
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

return $video_image_section;
