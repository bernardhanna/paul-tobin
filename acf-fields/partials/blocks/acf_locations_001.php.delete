<?php

use StoutLogic\AcfBuilder\FieldsBuilder;

$locations_001 = new FieldsBuilder('locations_001', [
    'label' => 'Locations Section',
]);

$locations_001
    ->addTab('Content', ['label' => 'Content'])
    ->addRepeater('locations', [
        'label' => 'Locations',
        'instructions' => 'Add office locations with contact details.',
        'button_label' => 'Add Location',
        'min' => 1,
        'layout' => 'block',
    ])
        ->addText('location_name', [
            'label' => 'Location Name',
            'instructions' => 'Enter the name of the office location.',
            'default_value' => 'Dublin M50 office',
            'required' => 1,
        ])
        ->addWysiwyg('address', [
            'label' => 'Address',
            'instructions' => 'Enter the full address for this location.',
            'default_value' => 'Paul Tobin Estate Agents<br>Clifton House<br>Fitzwilliam Street Lower<br>Dublin 2, D02 XT91',
            'media_upload' => 0,
            'tabs' => 'visual',
            'toolbar' => 'basic',
        ])
        ->addWysiwyg('phone_numbers', [
            'label' => 'Phone Numbers',
            'instructions' => 'Enter phone numbers, one per line.',
            'default_value' => '01 902 0092<br>086 827 1556',
            'media_upload' => 0,
            'tabs' => 'visual',
            'toolbar' => 'basic',
        ])
        ->addEmail('email', [
            'label' => 'Email Address',
            'instructions' => 'Enter the contact email for this location.',
            'default_value' => 'info@paultobin.ie',
        ])
        ->addLink('team_link', [
            'label' => 'Team Link',
            'instructions' => 'Link to meet the team page or section.',
            'return_format' => 'array',
        ])
        ->addTrueFalse('is_expanded', [
            'label' => 'Expanded by Default',
            'instructions' => 'Show this location expanded when the page loads.',
            'default_value' => 0,
        ])
    ->endRepeater()
    ->addTextarea('map_iframe', [
        'label' => 'Map iFrame Code',
        'instructions' => 'Paste the complete iframe embed code for the map (e.g., from Google Maps).',
        'placeholder' => '<iframe src="..." width="100%" height="100%" style="border:0;" allowfullscreen="" loading="lazy" referrerpolicy="no-referrer-when-downgrade" title="Office Location Map"></iframe>',
        'rows' => 4,
    ])

    ->addTab('Design', ['label' => 'Design'])
    ->addColorPicker('background_color', [
        'label' => 'Background Color',
        'instructions' => 'Set the background color for the entire section.',
        'default_value' => '#FFFFFF',
    ])

    ->addTab('Layout', ['label' => 'Layout'])
    ->addRepeater('padding_settings', [
        'label' => 'Padding Settings',
        'instructions' => 'Customize padding for different screen sizes.',
        'button_label' => 'Add Screen Size Padding',
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

return $locations_001;
