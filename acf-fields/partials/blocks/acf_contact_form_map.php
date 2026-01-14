<?php

use StoutLogic\AcfBuilder\FieldsBuilder;

$contact_form_map = new FieldsBuilder('contact_form_map', [
    'label' => 'Contact Form with Map',
]);

$contact_form_map
    ->addTab('Content')
        // Form Section
        ->addText('form_heading', [
            'label' => 'Form Heading',
            'default_value' => 'Book an evaluation'
        ])
        ->addSelect('form_heading_tag', [
            'label' => 'Form Heading Tag',
            'choices' => [
                'h1' => 'H1',
                'h2' => 'H2',
                'h3' => 'H3',
                'h4' => 'H4',
                'h5' => 'H5',
                'h6' => 'H6',
                'p' => 'Paragraph',
                'span' => 'Span'
            ],
            'default_value' => 'h2',
        ])
        ->addText('form_description', [
            'label' => 'Form Description',
            'default_value' => 'Curious what your home\'s worth? Book your free evaluation today.'
        ])
        ->addWysiwyg('form_markup', [
            'label' => 'Form HTML (paste static form here)',
            'instructions' => 'Paste the static HTML form code here.',
            'toolbar' => 'basic',
            'media_upload' => 0,
            'wrapper' => ['class' => 'wp_editor'],
        ])
        ->addUrl('privacy_policy_url', [
            'label' => 'Privacy Policy URL',
            'default_value' => '#'
        ])

        // Location Section
        ->addText('location_heading', [
            'label' => 'Location Section Heading',
            'default_value' => 'Where you can find us'
        ])
        ->addSelect('location_heading_tag', [
            'label' => 'Location Heading Tag',
            'choices' => [
                'h1' => 'H1',
                'h2' => 'H2',
                'h3' => 'H3',
                'h4' => 'H4',
                'h5' => 'H5',
                'h6' => 'H6',
                'p' => 'Paragraph',
                'span' => 'Span'
            ],
            'default_value' => 'h2',
        ])
        ->addText('location_description', [
            'label' => 'Location Description',
            'default_value' => 'Curious what your home\'s worth? Book your free evaluation today.'
        ])

        // Offices Repeater
        ->addRepeater('offices', [
            'label' => 'Office Locations',
            'instructions' => 'Add office locations with contact details.',
            'button_label' => 'Add Office',
            'layout' => 'block',
            'min' => 0,
        ])
            ->addText('office_name', [
                'label' => 'Office Name',
                'default_value' => 'Dublin M50 office'
            ])
            ->addTextarea('office_address', [
                'label' => 'Office Address',
                'rows' => 4,
                'default_value' => 'Paul Tobin Estate Agents\nClifton House\nFitzwilliam Street Lower\nDublin 2, D02 XT91'
            ])
            ->addTextarea('office_phone', [
                'label' => 'Phone Numbers',
                'rows' => 2,
                'default_value' => '01 902 0092\n086 827 1556'
            ])
            ->addEmail('office_email', [
                'label' => 'Email Address',
                'default_value' => 'info@paultobin.ie'
            ])
            ->addLink('team_link', [
                'label' => 'Team Link',
                'return_format' => 'array',
            ])
            ->addImage('map_image', [
                'label' => 'Map Image',
                'return_format' => 'id',
                'preview_size' => 'medium',
            ])
            ->addTrueFalse('is_expanded', [
                'label' => 'Expanded by Default',
                'instructions' => 'Show this office details expanded by default.',
                'default_value' => 0,
            ])
        ->endRepeater()

        // Map Settings
        ->addTrueFalse('enable_map', [
            'label' => 'Enable Map',
            'ui' => 1,
            'default_value' => 1,
        ])
        ->addUrl('map_iframe_url', [
            'label' => 'Map Iframe URL',
            'instructions' => 'Enter the iframe URL for the map (e.g., Google Maps embed URL).',
            'conditional_logic' => [['field' => 'enable_map', 'operator' => '==', 'value' => 1]],
        ])

    ->addTab('Email')
        ->addText('form_name', [
            'label' => 'Internal Form Name',
            'instructions' => 'Saved with each entry & used in email subject.',
            'default_value' => 'Contact Form with Map'
        ])
        ->addText('from_name', [
            'label' => 'From Name (override)',
            'instructions' => 'Optional. Leave empty to use Theme Options.',
        ])
        ->addEmail('from_email', [
            'label' => 'From Email (override)',
            'instructions' => 'Use an address on your domain (e.g. no-reply@domain.ie). Leave empty to use Theme Options.',
        ])
        ->addText('email_to', [
            'label' => 'Send To',
            'instructions' => 'One or more addresses. Separate with commas or semicolons.',
            'placeholder' => 'name@domain.ie, other@domain.ie',
            'default_value' => get_option('admin_email'),
        ])
        ->addText('email_bcc', [
            'label' => 'BCC',
            'instructions' => 'Optional. Separate multiple with commas or semicolons.',
            'placeholder' => 'first@domain.ie; second@domain.ie',
        ])
        ->addText('email_subject', [
            'label' => 'Subject',
            'default_value' => 'Website contact form enquiry'
        ])
        ->addTrueFalse('save_entries_to_db', [
            'label' => 'Save to DB?',
            'ui' => 1,
            'default_value' => 1
        ])

    ->addTab('Autoresponder')
        ->addTrueFalse('enable_autoresponder', [
            'label' => 'Enable?',
            'ui' => 1
        ])
        ->addText('autoresponder_subject', [
            'label' => 'Autoresponder Subject',
            'conditional_logic' => [[['field' => 'enable_autoresponder', 'operator' => '==', 'value' => 1]]],
            'default_value' => 'Thank you for your message'
        ])
        ->addWysiwyg('autoresponder_message', [
            'label' => 'Autoresponder Message',
            'conditional_logic' => [[['field' => 'enable_autoresponder', 'operator' => '==', 'value' => 1]]],
            'wrapper' => ['class' => 'wp_editor'],
            'default_value' => '<p>Thank you for contacting us. We will get back to you as soon as possible.</p>'
        ])

    ->addTab('Design')
        ->addColorPicker('background_color', [
            'label' => 'Background Color',
            'default_value' => '#ffffff'
        ])
        ->addText('background_css', [
            'label' => 'Background CSS (optional)',
            'instructions' => 'Full CSS value, e.g. linear-gradient(270deg, rgba(242,245,247,0.83) 0%, rgba(242,245,247,0.30) 51.56%, #F2F5F7 100%)',
            'default_value' => '',
        ])
        ->addColorPicker('text_color', [
            'label' => 'Text Color',
            'default_value' => '#0a0a0a'
        ])

    ->addTab('Layout')
        ->addRepeater('padding_settings', [
            'label' => 'Padding Settings',
            'instructions' => 'Customize padding for different screen sizes.',
            'button_label' => 'Add Padding',
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
                'min' => 0,
                'max' => 20,
                'step' => 0.1,
                'append' => 'rem',
            ])
            ->addNumber('padding_bottom', [
                'label' => 'Padding Bottom',
                'min' => 0,
                'max' => 20,
                'step' => 0.1,
                'append' => 'rem',
            ])
        ->endRepeater();

return $contact_form_map;
