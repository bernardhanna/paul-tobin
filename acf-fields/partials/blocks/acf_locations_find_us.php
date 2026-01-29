<?php
use StoutLogic\AcfBuilder\FieldsBuilder;

$locations_find_us = new FieldsBuilder('locations_find_us', [
    'label' => 'Locations Find Us',
]);

$locations_find_us
    ->addTab('Content', ['label' => 'Content'])
        ->addText('heading', [
            'label' => 'Main Heading',
            'default_value' => 'Where you can find us',
        ])
        ->addSelect('heading_tag', [
            'label' => 'Heading Tag',
            'choices' => [
                'h1'=>'H1','h2'=>'H2','h3'=>'H3','h4'=>'H4','h5'=>'H5','h6'=>'H6','p'=>'Paragraph','span'=>'Span',
            ],
            'default_value' => 'h2',
        ])
        ->addRepeater('locations', [
            'label' => 'Office Locations',
            'instructions' => 'Add office locations with contact information and map/embeds.',
            'button_label' => 'Add Location',
            'min' => 1,
            'layout' => 'block',
        ])
            ->addText('office_name', [
                'label' => 'Office Name',
                'required' => 1,
            ])
            ->addTextarea('address', [
                'label' => 'Address',
                'rows' => 4,
                'required' => 1,
            ])
            ->addTextarea('phone_numbers', [
                'label' => 'Phone Numbers (one per line)',
                'rows' => 3,
            ])
            ->addEmail('email', [
                'label' => 'Email Address',
            ])

            // Map display selector
            ->addSelect('map_display_type', [
                'label' => 'Map Display',
                'choices' => [
                    'leaflet' => 'OpenStreetMap (Leaflet)',
                    'iframe'  => 'Embed (iframe)',
                    'image'   => 'Static Image',
                ],
                'default_value' => 'image',
            ])

            // Static image option
            ->addImage('map_image', [
                'label' => 'Map/Location Image',
                'instructions' => 'Shown when Map Display = Static Image',
                'return_format' => 'id',
                'preview_size' => 'medium',
                'required' => 0,
                'conditional_logic' => [
                    [['field' => 'map_display_type','operator'=>'==','value'=>'image']]
                ],
            ])

            // Leaflet fields
            ->addNumber('map_latitude', [
                'label' => 'Latitude',
                'step' => 0.000001,
                'conditional_logic' => [
                    [['field' => 'map_display_type','operator'=>'==','value'=>'leaflet']]
                ],
            ])
            ->addNumber('map_longitude', [
                'label' => 'Longitude',
                'step' => 0.000001,
                'conditional_logic' => [
                    [['field' => 'map_display_type','operator'=>'==','value'=>'leaflet']]
                ],
            ])
            ->addNumber('map_zoom', [
                'label' => 'Zoom (default 15)',
                'min' => 3, 'max' => 20,
                'default_value' => 15,
                'conditional_logic' => [
                    [['field' => 'map_display_type','operator'=>'==','value'=>'leaflet']]
                ],
            ])
            ->addImage('map_icon', [
                'label' => 'Marker Icon (optional)',
                'return_format' => 'id',
                'preview_size' => 'thumbnail',
                'conditional_logic' => [
                    [['field' => 'map_display_type','operator'=>'==','value'=>'leaflet']]
                ],
            ])
              ->addText('tile_api_key', [
                  'label' => 'Map Tile API Key (Jawg)',
                  'instructions' => 'Paste your Jawg Maps access token.',
                  'default_value' => 'zxWPtYn9xCoXLAzkN6ckqMOHRw7Xf0zsTWBN0EmR7BSjUMW2F0hsBScanw15iLpX',
                  'required' => 1,
              ])
            // iframe embed option
            ->addTextarea('map_iframe_html', [
                'label' => 'Embed iframe HTML',
                'instructions' => 'Paste the full iframe embed code (Google Maps, etc.)',
                'rows' => 3,
                'conditional_logic' => [
                    [['field' => 'map_display_type','operator'=>'==','value'=>'iframe']]
                ],
            ])
        ->endRepeater()

    ->addTab('Design', ['label' => 'Design'])
        ->addColorPicker('background_color', [
            'label' => 'Background Color',
            'default_value' => '#ffffff',
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
                    'xxs'=>'xxs','xs'=>'xs','mob'=>'mob','sm'=>'sm','md'=>'md','lg'=>'lg','xl'=>'xl','xxl'=>'xxl','ultrawide'=>'ultrawide',
                ],
                'required' => 1,
            ])
            ->addNumber('padding_top', [
                'label' => 'Padding Top',
                'min' => 0, 'max' => 20, 'step' => 0.1, 'append' => 'rem',
                'default_value' => 5,
            ])
            ->addNumber('padding_bottom', [
                'label' => 'Padding Bottom',
                'min' => 0, 'max' => 20, 'step' => 0.1, 'append' => 'rem',
                'default_value' => 5,
            ])
        ->endRepeater();

return $locations_find_us;
