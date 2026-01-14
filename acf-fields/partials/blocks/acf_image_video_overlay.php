<?php

use StoutLogic\AcfBuilder\FieldsBuilder;

$image_video_overlay = new FieldsBuilder('image_video_overlay', [
    'label' => 'Image Video Overlay',
]);

$image_video_overlay
    ->addTab('Content', ['label' => 'Content'])
        ->addSelect('media_type', [
            'label'         => 'Media Type',
            'instructions'  => 'Choose the type of media to display.',
            'choices'       => [
                'image'       => 'Image',
                'local_video' => 'Local Video',
                'youtube'     => 'YouTube Video',
                'vimeo'       => 'Vimeo Video',
            ],
            'default_value' => 'image',
            'required'      => 1,
        ])
        ->addImage('image', [
            'label'             => 'Image',
            'instructions'      => 'Upload an image to display.',
            'return_format'     => 'id',
            'preview_size'      => 'medium',
            'conditional_logic' => [[['field' => 'media_type','operator' => '==','value' => 'image']]],
        ])
        ->addFile('local_video', [
            'label'             => 'Local Video',
            'instructions'      => 'Upload a video file (MP4 recommended).',
            'return_format'     => 'id',
            'mime_types'        => 'mp4,mov,avi,wmv,webm',
            'conditional_logic' => [[['field' => 'media_type','operator' => '==','value' => 'local_video']]],
        ])
        ->addUrl('youtube_url', [
            'label'             => 'YouTube URL',
            'instructions'      => 'Full YouTube URL (e.g., https://www.youtube.com/watch?v=VIDEO_ID).',
            'conditional_logic' => [[['field' => 'media_type','operator' => '==','value' => 'youtube']]],
        ])
        ->addUrl('vimeo_url', [
            'label'             => 'Vimeo URL',
            'instructions'      => 'Full Vimeo URL (e.g., https://vimeo.com/VIDEO_ID).',
            'conditional_logic' => [[['field' => 'media_type','operator' => '==','value' => 'vimeo']]],
        ])
        ->addTrueFalse('autoplay', [
            'label'             => 'Autoplay Video',
            'instructions'      => 'Enable autoplay (video will be muted).',
            'default_value'     => 0,
            'conditional_logic' => [[['field' => 'media_type','operator' => '!=','value' => 'image']]],
        ])

        ->addTrueFalse('show_overlay', [
            'label'         => 'Show Overlay',
            'instructions'  => 'Enable color/gradient overlay over the media.',
            'default_value' => 0,
        ])
        ->addText('overlay_background_css', [
            'label'             => 'Overlay Background (CSS)',
            'instructions'      => 'Enter any valid CSS background value (e.g., <code>rgba(0,0,0,.5)</code> or <code>linear-gradient(180deg, rgba(0,0,0,0), rgba(0,0,0,.65))</code>).',
            'placeholder'       => 'rgba(0,0,0,.5)',
            'default_value'     => 'rgba(0,0,0,.5)',
            'conditional_logic' => [[['field' => 'show_overlay','operator' => '==','value' => '1']]],
        ])

    ->addTab('Design', ['label' => 'Design'])
        ->addColorPicker('background_color', [
            'label'         => 'Section Background Color',
            'instructions'  => 'Set the background color for the entire section.',
            'default_value' => '#e5e7eb',
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
                    'sm' => 'sm',
                    'md' => 'md',
                    'lg' => 'lg',
                    'xl' => 'xl',
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

return $image_video_overlay;
