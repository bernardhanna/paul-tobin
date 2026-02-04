<?php
use StoutLogic\AcfBuilder\FieldsBuilder;

$contact_section = new FieldsBuilder('contact_section', array(
    'label' => 'Calendly Contact Section',
));

$contact_section
    ->addTab('Content')

        // LEFT BLOCK
        ->addGroup('left_block', array('label' => 'Left Block'))
            ->addText('heading_text', array(
                'label' => 'Heading Text',
                'default_value' => 'Got a question?',
            ))
            ->addSelect('heading_tag', array(
                'label' => 'Heading Tag',
                'choices' => array(
                    'h1' => 'h1',
                    'h2' => 'h2',
                    'h3' => 'h3',
                    'h4' => 'h4',
                    'h5' => 'h5',
                    'h6' => 'h6',
                    'span' => 'span',
                    'p'   => 'p',
                ),
                'default_value' => 'h2',
            ))
            ->addWysiwyg('description', array(
                'label' => 'Description',
                'media_upload' => 0,
                'tabs' => 'all',
                'toolbar' => 'full',
                'delay' => 0,
                'default_value' => 'Interested in any listed property? Book a consultation with Paul.',
            ))
            ->addLink('cta_link', array(
                'label' => 'CTA Link (Calendly)',
                'instructions' => 'ACF Link array: URL, Link Text, and Target.',
            ))
            ->addSelect('media_type', array(
                'label' => 'Left Media Type',
                'choices' => array(
                    'image'    => 'Image',
                    'calendly' => 'Calendly Shortcode',
                ),
                'default_value' => 'image',
            ))
            ->addImage('image', array(
                'label' => 'Image',
                'return_format' => 'array',
                'preview_size' => 'medium',
                'conditional_logic' => array(
                    array(
                        array(
                            'field' => 'media_type',
                            'operator' => '==',
                            'value' => 'image',
                        ),
                    ),
                ),
            ))
            ->addWysiwyg('shortcode', array(
                'label' => 'Calendly Shortcode',
                'media_upload' => 0,
                'tabs' => 'all',
                'toolbar' => 'full',
                'delay' => 0,
                'conditional_logic' => array(
                    array(
                        array(
                            'field' => 'media_type',
                            'operator' => '==',
                            'value' => 'calendly',
                        ),
                    ),
                ),
            ))
        ->endGroup()

        // RIGHT BLOCK
        ->addGroup('right_block', array('label' => 'Right Block'))
            ->addText('heading_text', array(
                'label' => 'Heading Text',
                'default_value' => 'About your property',
            ))
            ->addSelect('heading_tag', array(
                'label' => 'Heading Tag',
                'choices' => array(
                    'h1' => 'h1',
                    'h2' => 'h2',
                    'h3' => 'h3',
                    'h4' => 'h4',
                    'h5' => 'h5',
                    'h6' => 'h6',
                    'span' => 'span',
                    'p'   => 'p',
                ),
                'default_value' => 'h2',
            ))
            ->addWysiwyg('description', array(
                'label' => 'Description',
                'media_upload' => 0,
                'tabs' => 'all',
                'toolbar' => 'full',
                'delay' => 0,
                'default_value' => 'Curious what your home’s worth? Book your free evaluation today.',
            ))
            ->addLink('cta_link', array(
                'label' => 'CTA Link (Request a Call)',
                'instructions' => 'ACF Link array: URL, Link Text, and Target.',
            ))
            ->addSelect('media_type', array(
                'label' => 'Right Media Type',
                'choices' => array(
                    'image'     => 'Image',
                    'video'     => 'Video (YouTube or Local)',
                    'map_jawg'  => 'Map (Leaflet – Jawg Streets)',
                ),
                'default_value' => 'image',
            ))
            ->addImage('image', array(
                'label' => 'Image',
                'return_format' => 'array',
                'preview_size' => 'medium',
                'conditional_logic' => array(
                    array(
                        array(
                            'field' => 'media_type',
                            'operator' => '==',
                            'value' => 'image',
                        ),
                    ),
                ),
            ))
            ->addSelect('video_type', array(
                'label' => 'Video Type',
                'choices' => array(
                    'youtube' => 'YouTube URL',
                    'local'   => 'Local File',
                ),
                'conditional_logic' => array(
                    array(
                        array(
                            'field' => 'media_type',
                            'operator' => '==',
                            'value' => 'video',
                        ),
                    ),
                ),
            ))
            ->addUrl('youtube_url', array(
                'label' => 'YouTube URL',
                'placeholder' => 'https://www.youtube.com/watch?v=XXXXX',
                'conditional_logic' => array(
                    array(
                        array(
                            'field' => 'video_type',
                            'operator' => '==',
                            'value' => 'youtube',
                        ),
                    ),
                ),
            ))
            ->addFile('local_video', array(
                'label' => 'Local Video File',
                'return_format' => 'array',
                'library' => 'all',
                'mime_types' => 'mp4,webm,ogg',
                'conditional_logic' => array(
                    array(
                        array(
                            'field' => 'video_type',
                            'operator' => '==',
                            'value' => 'local',
                        ),
                    ),
                ),
            ))
            ->addImage('poster_image', array(
                'label' => 'Poster Image (optional)',
                'return_format' => 'array',
                'preview_size' => 'medium',
                'conditional_logic' => array(
                    array(
                        array(
                            'field' => 'media_type',
                            'operator' => '==',
                            'value' => 'video',
                        ),
                    ),
                ),
            ))

            // MAP (Jawg via Leaflet)
            ->addNumber('map_latitude', array(
                'label' => 'Map Latitude',
                'step' => 0.000001,
                'conditional_logic' => array(
                    array(
                        array(
                            'field' => 'media_type',
                            'operator' => '==',
                            'value' => 'map_jawg',
                        ),
                    ),
                ),
            ))
            ->addNumber('map_longitude', array(
                'label' => 'Map Longitude',
                'step' => 0.000001,
                'conditional_logic' => array(
                    array(
                        array(
                            'field' => 'media_type',
                            'operator' => '==',
                            'value' => 'map_jawg',
                        ),
                    ),
                ),
            ))
            ->addNumber('map_zoom', array(
                'label' => 'Map Zoom',
                'min' => 3,
                'max' => 20,
                'default_value' => 15,
                'conditional_logic' => array(
                    array(
                        array(
                            'field' => 'media_type',
                            'operator' => '==',
                            'value' => 'map_jawg',
                        ),
                    ),
                ),
            ))
            ->addText('jawg_access_token', array(
                'label' => 'Jawg Access Token',
                'instructions' => 'Get a token at jawg.io (required for Jawg Streets tiles).',
                'conditional_logic' => array(
                    array(
                        array(
                            'field' => 'media_type',
                            'operator' => '==',
                            'value' => 'map_jawg',
                        ),
                    ),
                ),
            ))
        ->endGroup()

    ->addTab('Layout')
        ->addRepeater('padding_settings', array(
            'label' => 'Padding Settings',
            'instructions' => 'Customize padding for different screen sizes (rem).',
            'button_label' => 'Add Screen Size Padding',
        ))
            ->addSelect('screen_size', array(
                'label' => 'Screen Size',
                'choices' => array(
                    'xxs' => 'xxs',
                    'xs' => 'xs',
                    'mob' => 'mob',
                    'sm' => 'sm',
                    'md' => 'md',
                    'lg' => 'lg',
                    'xl' => 'xl',
                    'xxl' => 'xxl',
                    'ultrawide' => 'ultrawide',
                ),
            ))
            ->addNumber('padding_top', array(
                'label' => 'Padding Top',
                'instructions' => 'Top padding in rem.',
                'min' => 0,
                'max' => 20,
                'step' => 0.1,
                'append' => 'rem',
            ))
            ->addNumber('padding_bottom', array(
                'label' => 'Padding Bottom',
                'instructions' => 'Bottom padding in rem.',
                'min' => 0,
                'max' => 20,
                'step' => 0.1,
                'append' => 'rem',
            ))
        ->endRepeater()

    ->setLocation('block', '==', 'acf/contact_section');

return $contact_section;
