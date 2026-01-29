<?php
use StoutLogic\AcfBuilder\FieldsBuilder;

$full_width_media = new FieldsBuilder('full_width_media', [
    'label' => 'Full Width Media',
]);

$full_width_media
    ->addTab('content_tab', [
        'label' => 'Content',
        'placement' => 'left',
    ])
        ->addSelect('media_type', [
            'label' => 'Media Type',
            'choices' => [
                'image' => 'Image',
                'video' => 'Video',
            ],
            'default_value' => 'image',
        ])
        ->addImage('image', [
            'label' => 'Image',
            'return_format' => 'array',
            'preview_size' => 'large',
            'library' => 'all',
            'conditional_logic' => [
                [
                    ['field' => 'media_type', 'operator' => '==', 'value' => 'image'],
                ],
            ],
        ])
        ->addSelect('video_provider', [
            'label' => 'Video Provider',
            'choices' => [
                'local'   => 'Local (self-hosted)',
                'youtube' => 'YouTube (URL)',
                'vimeo'   => 'Vimeo (URL)',
            ],
            'default_value' => 'local',
            'conditional_logic' => [
                [
                    ['field' => 'media_type', 'operator' => '==', 'value' => 'video'],
                ],
            ],
        ])
        ->addFile('video_file', [
            'label' => 'Local Video File',
            'instructions' => 'Upload MP4/WebM file.',
            'return_format' => 'array',
            'library' => 'all',
            'mime_types' => 'mp4,webm',
            'conditional_logic' => [
                [
                    ['field' => 'media_type', 'operator' => '==', 'value' => 'video'],
                    ['field' => 'video_provider', 'operator' => '==', 'value' => 'local'],
                ],
            ],
        ])
        ->addUrl('video_url', [
            'label' => 'Video URL (YouTube/Vimeo)',
            'instructions' => 'Paste the full video URL.',
            'conditional_logic' => [
                [
                    ['field' => 'media_type', 'operator' => '==', 'value' => 'video'],
                    ['field' => 'video_provider', 'operator' => '!=', 'value' => 'local'],
                ],
            ],
        ])
        ->addImage('poster_image', [
            'label' => 'Poster (optional)',
            'instructions' => 'Shown before playback (and for iframe providers).',
            'return_format' => 'array',
            'preview_size' => 'medium',
            'library' => 'all',
            'conditional_logic' => [
                [
                    ['field' => 'media_type', 'operator' => '==', 'value' => 'video'],
                ],
            ],
        ])
        ->addNumber('height_xs', [
            'label' => 'Height (base)',
            'instructions' => 'Height at base breakpoint in px.',
            'default_value' => 400,
            'min' => 100,
            'max' => 2000,
            'step' => 1,
            'append' => 'px',
        ])
        ->addNumber('height_md', [
            'label' => 'Height (md)',
            'instructions' => 'Height at md breakpoint in px.',
            'default_value' => 500,
            'min' => 100,
            'max' => 2000,
            'step' => 1,
            'append' => 'px',
        ])
        ->addTrueFalse('autopause_on_scroll', [
            'label' => 'Auto-pause on scroll',
            'ui' => 1,
            'default_value' => 1,
            'conditional_logic' => [
                [
                    ['field' => 'media_type', 'operator' => '==', 'value' => 'video'],
                ],
            ],
        ])
        ->addTrueFalse('muted', [
            'label' => 'Start Muted',
            'ui' => 1,
            'default_value' => 0,
            'conditional_logic' => [
                [
                    ['field' => 'media_type', 'operator' => '==', 'value' => 'video'],
                ],
            ],
        ])
        ->addTrueFalse('click_toggle_pause', [
            'label' => 'Click video to toggle play/pause',
            'ui' => 1,
            'default_value' => 1,
            'conditional_logic' => [
                [
                    ['field' => 'media_type', 'operator' => '==', 'value' => 'video'],
                ],
            ],
        ])

    ->addTab('design_tab', [
        'label' => 'Design',
        'placement' => 'left',
    ])
        ->addColorPicker('background_color', [
            'label' => 'Background color',
            'default_value' => '#ffffff',
            'instructions' => 'Pick a background color for the section (defaults to white).',
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
                    'sm'  => 'sm',
                    'md'  => 'md',
                    'lg'  => 'lg',
                    'xl'  => 'xl',
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

return $full_width_media;