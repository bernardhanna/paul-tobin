<?php
// File: inc/acf/options/theme-options/footer.php

use StoutLogic\AcfBuilder\FieldsBuilder;

$fields = new FieldsBuilder('footer', [
    'title'     => 'Footer Settings',
    'menu_slug' => 'theme-footer-settings',
    'post_id'   => 'option',
]);

$fields
    // Column headings for menu columns
    ->addText('footer_col1_heading', [
        'label'         => 'Column 1 Heading',
        'default_value' => 'About us',
    ])
    ->addText('footer_col2_heading', [
        'label'         => 'Column 2 Heading',
        'default_value' => 'Quick links',
    ])
    ->addText('footer_col3_heading', [
        'label'         => 'Column 3 Heading',
        'default_value' => 'Homeowners',
    ])

    // Company branding
    ->addImage('company_logo', [
        'label'         => 'Company Logo',
        'instructions'  => 'Upload the footer logo (SVG/PNG).',
        'return_format' => 'id',
        'preview_size'  => 'medium',
    ])
    ->addText('company_slogan', [
        'label'         => 'Company Slogan',
        'instructions'  => 'Short tagline shown under the logo.',
        'default_value' => "Your Irish property, expertly handled while you're abroad.",
    ])

    // Accreditation + optional partner logos
    ->addImage('accreditation_image', [
        'label'         => 'Accreditation Image',
        'return_format' => 'id',
        'preview_size'  => 'medium',
    ])
    ->addLink('accreditation_link', [
        'label'         => 'Accreditation Link (optional)',
        'return_format' => 'array',
    ])
    ->addRepeater('partner_logos', [
        'label'        => 'Partner/Association Logos',
        'button_label' => 'Add Logo',
        'layout'       => 'table',
        'min'          => 0,
        'max'          => 12,
        'collapsed'    => 'logo_image',
    ])
        ->addImage('logo_image', [
            'label'         => 'Logo Image',
            'return_format' => 'id',
            'preview_size'  => 'thumbnail',
        ])
        ->addLink('logo_link', [
            'label'         => 'Logo Link (optional)',
            'return_format' => 'array',
        ])
    ->endRepeater()

    // Trustpilot
    ->addLink('trustpilot_link', [
        'label'         => 'Trustpilot Link',
        'return_format' => 'array',
    ])
    ->addImage('trustpilot_badge', [
        'label'         => 'Trustpilot Badge (optional)',
        'return_format' => 'id',
        'preview_size'  => 'medium',
    ])

    // Contact
    ->addText('phone_number', [
        'label'         => 'Phone Number',
        'default_value' => '+353 01 902 0092',
    ])
    ->addText('email_address', [
        'label'       => 'Email Address',
        'placeholder' => 'hello@example.com',
    ])
    ->addTextarea('address', [
        'label'     => 'Address',
        'new_lines' => 'br',
        'maxlength' => 300,
        'default_value' => 'Junction 6, Castleknock, Dublin, Ireland',
    ])

    // Socials
    ->addRepeater('social_icons', [
        'label'        => 'Social Icons',
        'button_label' => 'Add Social',
        'layout'       => 'row',
        'min'          => 0,
        'max'          => 10,
        'collapsed'    => 'social_label',
    ])
        ->addText('social_label', [
            'label' => 'Label (e.g. Facebook)',
        ])
        ->addLink('social_link', [
            'label'         => 'Profile (Link Array)',
            'return_format' => 'array',
            'required'      => 1,
        ])
        ->addImage('social_icon', [
            'label'         => 'Icon (SVG/PNG)',
            'return_format' => 'id',
            'preview_size'  => 'thumbnail',
            'required'      => 1,
        ])
    ->endRepeater()

    // Padding (your standard repeater)
    ->addRepeater('padding_settings', [
        'label'        => 'Padding Settings',
        'instructions' => 'Customize padding for different screen sizes.',
        'button_label' => 'Add Screen Size Padding',
        'layout'       => 'table',
        'collapsed'    => 'screen_size',
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
            'label' => 'Padding Top',
            'min' => 0, 'max' => 20, 'step' => 0.1, 'append' => 'rem',
            'default_value' => 4,
        ])
        ->addNumber('padding_bottom', [
            'label' => 'Padding Bottom',
            'min' => 0, 'max' => 20, 'step' => 0.1, 'append' => 'rem',
            'default_value' => 4,
        ])
    ->endRepeater()

    // Copyright with {year}
    ->addText('copyright_text', [
        'label'         => 'Copyright Text',
        'instructions'  => 'Use {year} to auto-insert the current year.',
        'default_value' => '© {year} Paul Tobin Estate Agents. All rights reserved.',
    ]);

return $fields;
