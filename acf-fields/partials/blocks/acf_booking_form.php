<?php

use StoutLogic\AcfBuilder\FieldsBuilder;

$booking_form = new FieldsBuilder('booking_form', [
    'label' => 'Booking Form with Find Us',
]);

$booking_form
    ->addTab('Content', ['label' => 'Content'])
        // Booking Form Section
        ->addText('heading', [
            'label' => 'Main Heading',
            'default_value' => 'Book an evaluation',
        ])
        ->addSelect('heading_tag', [
            'label' => 'Heading Tag',
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
        ->addTextarea('description', [
            'label' => 'Description',
            'default_value' => 'Curious what your home\'s worth? Book your free evaluation today.',
            'rows' => 3,
        ])
        ->addWysiwyg('form_markup', [
            'label' => 'Form HTML',
            'instructions' => 'Paste the static HTML form code here.',
            'toolbar' => 'basic',
            'media_upload' => 0,
            'wrapper' => ['class' => 'wp_editor'],
        ])
        ->addUrl('privacy_policy_url', [
            'label' => 'Privacy Policy URL',
            'default_value' => '#',
        ])

        // Find Us Section
        ->addText('find_us_heading', [
            'label' => 'Find Us Heading',
            'default_value' => 'Where you can find us',
        ])
        ->addSelect('find_us_heading_tag', [
            'label' => 'Find Us Heading Tag',
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
        ->addTextarea('find_us_description', [
            'label' => 'Find Us Description',
            'default_value' => 'Curious what your home\'s worth? Book your free evaluation today.',
            'rows' => 3,
        ])

        // Office Locations
        ->addRepeater('office_locations', [
            'label' => 'Office Locations',
            'instructions' => 'Add office locations with contact information.',
            'button_label' => 'Add Office Location',
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
                'label' => 'Show Expanded by Default',
                'instructions' => 'Show this location expanded with full details.',
                'default_value' => 0,
            ])
        ->endRepeater()

    ->addTab('Email', ['label' => 'Email'])
        ->addText('form_name', [
            'label' => 'Internal Form Name',
            'instructions' => 'Saved with each entry & used in email subject.',
            'default_value' => 'Property Evaluation Form',
        ])
        ->addText('from_name', [
            'label' => 'From Name (override)',
            'instructions' => 'Optional. Leave empty to use Theme Options.',
        ])
        ->addEmail('from_email', [
            'label' => 'From Email (override)',
            'instructions' => 'Use an address on your domain. Leave empty to use Theme Options.',
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
            'default_value' => 'Property evaluation form enquiry',
        ])
        ->addTrueFalse('save_entries_to_db', [
            'label' => 'Save to DB?',
            'ui' => 1,
            'default_value' => 1,
        ])

    ->addTab('Autoresponder', ['label' => 'Autoresponder'])
        ->addTrueFalse('enable_autoresponder', [
            'label' => 'Enable?',
            'ui' => 1,
        ])
        ->addText('autoresponder_subject', [
            'label' => 'Autoresponder Subject',
            'conditional_logic' => [[['field' => 'enable_autoresponder', 'operator' => '==', 'value' => 1]]],
            'default_value' => 'Thank you for your evaluation request',
        ])
        ->addWysiwyg('autoresponder_message', [
            'label' => 'Autoresponder Message',
            'conditional_logic' => [[['field' => 'enable_autoresponder', 'operator' => '==', 'value' => 1]]],
            'wrapper' => ['class' => 'wp_editor'],
            'default_value' => '<p>Thank you for requesting a property evaluation. We will get back to you as soon as possible.</p>',
        ])

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
                'required' => 1,
            ])
            ->addNumber('padding_top', [
                'label' => 'Padding Top',
                'min' => 0,
                'max' => 20,
                'step' => 0.1,
                'append' => 'rem',
                'default_value' => 5,
            ])
            ->addNumber('padding_bottom', [
                'label' => 'Padding Bottom',
                'min' => 0,
                'max' => 20,
                'step' => 0.1,
                'append' => 'rem',
                'default_value' => 5,
            ])
        ->endRepeater();

return $booking_form;
