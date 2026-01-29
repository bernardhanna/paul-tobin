<?php
// Variables
$heading = get_sub_field('heading') ?: 'Book an evaluation';
$heading_tag = get_sub_field('heading_tag') ?: 'h2';
$description = get_sub_field('description') ?: 'Curious what your home\'s worth? Book your free evaluation today.';
$form_markup = get_sub_field('form_markup', false, false);
$privacy_policy_url = get_sub_field('privacy_policy_url') ?: '#';

// Find Us Section
$find_us_heading = get_sub_field('find_us_heading') ?: 'Where you can find us';
$find_us_heading_tag = get_sub_field('find_us_heading_tag') ?: 'h2';
$find_us_description = get_sub_field('find_us_description') ?: 'Curious what your home\'s worth? Book your free evaluation today.';

// Colors and styling
$background_color = get_sub_field('background_color') ?: '#ffffff';

// Padding classes
$padding_classes = [];
if (have_rows('padding_settings')) {
    while (have_rows('padding_settings')) {
        the_row();
        $screen_size = get_sub_field('screen_size');
        $padding_top = get_sub_field('padding_top');
        $padding_bottom = get_sub_field('padding_bottom');
        if ($screen_size !== '' && $padding_top !== null) $padding_classes[] = "{$screen_size}:pt-[{$padding_top}rem]";
        if ($screen_size !== '' && $padding_bottom !== null) $padding_classes[] = "{$screen_size}:pb-[{$padding_bottom}rem]";
    }
}

// Unique section ID
$section_id = 'booking-form-' . wp_generate_uuid4();

// Form plumbing: inject action, nonce, posted mail config, privacy link
if ($form_markup) {
    $form_markup = str_replace(
        '<form',
        sprintf(
            '<form action="%1$s" method="post" enctype="multipart/form-data" data-theme-form="%2$s"',
            esc_url(admin_url('admin-post.php')),
            esc_attr(get_row_index())
        ),
        $form_markup
    );

    $hidden = sprintf(
        '<input type="hidden" name="action" value="theme_form_submit">
        <input type="hidden" name="theme_form_nonce" value="%1$s">
        <input type="hidden" name="_theme_form_id" value="%2$s">
        <input type="hidden" name="_submission_uid" value="%3$s">',
        esc_attr(wp_create_nonce('theme_form_submit')),
        esc_attr(get_row_index()),
        esc_attr(wp_generate_uuid4())
    );

    if ($name = get_sub_field('form_name')) {
        $hidden .= '<input type="hidden" name="_theme_form_name" value="' . esc_attr($name) . '">';
    }
    if (get_sub_field('save_entries_to_db')) {
        $hidden .= '<input type="hidden" name="_theme_save_to_db" value="1">';
    }

    // Mail config (posted)
    $cfg_to = get_sub_field('email_to') ?: get_option('admin_email');
    $cfg_bcc = get_sub_field('email_bcc') ?: '';
    $cfg_subject = get_sub_field('email_subject') ?: '';
    $cfg_from_name = get_sub_field('from_name') ?: '';
    $cfg_from_email = get_sub_field('from_email') ?: '';

    $hidden_cfg = '';
    $hidden_cfg .= '<input type="hidden" name="_cfg_to" value="'.esc_attr($cfg_to).'">';
    $hidden_cfg .= '<input type="hidden" name="_cfg_bcc" value="'.esc_attr($cfg_bcc).'">';
    $hidden_cfg .= '<input type="hidden" name="_cfg_subject" value="'.esc_attr($cfg_subject).'">';
    $hidden_cfg .= '<input type="hidden" name="_cfg_from_name" value="'.esc_attr($cfg_from_name).'">';
    $hidden_cfg .= '<input type="hidden" name="_cfg_from_email" value="'.esc_attr($cfg_from_email).'">';

    if (get_sub_field('enable_autoresponder')) {
        $hidden_cfg .= '<input type="hidden" name="_cfg_auto_enabled" value="1">';
        $hidden_cfg .= '<input type="hidden" name="_cfg_auto_subject" value="'.esc_attr(get_sub_field('autoresponder_subject') ?: '').'">';
        $hidden_cfg .= '<input type="hidden" name="_cfg_auto_message" value="'.esc_attr(get_sub_field('autoresponder_message') ?: '').'">';
    }

    $form_markup = str_replace('</form>', ($hidden . $hidden_cfg) . '</form>', $form_markup);
    $form_markup = str_replace('href="#"', 'href="' . esc_url($privacy_policy_url) . '"', $form_markup);
}
?>

<section
    id="<?php echo esc_attr($section_id); ?>"
    class="relative flex overflow-hidden <?php echo esc_attr(implode(' ', $padding_classes)); ?>"
    style="background-color: <?php echo esc_attr($background_color); ?>;"
    aria-labelledby="<?php echo esc_attr($section_id); ?>-heading"
>
    <div class="flex flex-col items-center pt-20 pb-20 mx-auto w-full max-w-container max-lg:px-5 max-md:pt-16 max-md:pb-16 max-sm:pt-10 max-sm:pb-10">

        <div class="grid grid-cols-1 gap-20 w-full md:grid-cols-2 max-md:gap-16 max-sm:gap-16">

            <!-- Left Column - Booking Form -->
            <div class="flex flex-col">
                <?php if ($heading): ?>
                    <<?php echo esc_attr($heading_tag); ?>
                        id="<?php echo esc_attr($section_id); ?>-heading"
                        class="mb-4 text-3xl font-semibold leading-10 text-gray-900"
                    >
                        <?php echo esc_html($heading); ?>
                    </<?php echo esc_attr($heading_tag); ?>>
                <?php endif; ?>

                <!-- Decorative bars -->
                <div class="flex gap-1 mb-6" aria-hidden="true">
                    <div class="h-1 bg-orange-500 rounded-full w-[30px]"></div>
                    <div class="h-1 bg-green-500 rounded-full w-[30px]"></div>
                    <div class="h-1 bg-yellow-400 rounded-full w-[30px]"></div>
                </div>

                <?php if ($description): ?>
                    <p class="mb-8 text-base leading-6 text-gray-500">
                        <?php echo esc_html($description); ?>
                    </p>
                <?php endif; ?>

                <!-- Form Container -->
                <div class="p-10 bg-gray-100 rounded-lg max-sm:p-6">
                    <?php if ($form_markup): ?>
                        <?php
                        echo wp_kses(
                            $form_markup,
                            [
                                'form'=>['class'=>[], 'role'=>[], 'aria-labelledby'=>[], 'novalidate'=>[], 'action'=>[], 'method'=>[], 'enctype'=>[], 'data-theme-form'=>[]],
                                'div'=>['class'=>[],'id'=>[],'role'=>[],'aria-live'=>[],'aria-describedby'=>[]],
                                'label'=>['for'=>[], 'class'=>[], 'id'=>[]],
                                'input'=>['type'=>[], 'id'=>[], 'name'=>[], 'placeholder'=>[], 'required'=>[], 'aria-required'=>[], 'aria-describedby'=>[], 'autocomplete'=>[], 'class'=>[], 'value'=>[], 'accept'=>[]],
                                'select'=>['id'=>[], 'name'=>[], 'required'=>[], 'aria-required'=>[], 'aria-describedby'=>[], 'class'=>[]],
                                'option'=>['value'=>[], 'selected'=>[], 'disabled'=>[]],
                                'textarea'=>['id'=>[], 'name'=>[], 'placeholder'=>[], 'required'=>[], 'aria-required'=>[], 'aria-describedby'=>[], 'rows'=>[], 'class'=>[]],
                                'button'=>['type'=>[], 'class'=>[], 'aria-describedby'=>[]],
                                'span'=>['class'=>[], 'id'=>[]],
                                'i'=>['class'=>[]],
                                'img'=>['src'=>[], 'alt'=>[], 'class'=>[]],
                                'a'=>['href'=>[], 'class'=>[], 'target'=>[], 'aria-label'=>[]],
                            ]
                        );
                        ?>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Right Column - Find Us Section -->
            <div class="flex flex-col">
                <?php if ($find_us_heading): ?>
                    <<?php echo esc_attr($find_us_heading_tag); ?> class="mb-4 text-3xl font-semibold leading-10 text-gray-900">
                        <?php echo esc_html($find_us_heading); ?>
                    </<?php echo esc_attr($find_us_heading_tag); ?>>
                <?php endif; ?>

                <!-- Decorative bars -->
                <div class="flex gap-1 mb-6" aria-hidden="true">
                    <div class="h-1 bg-orange-500 rounded-full w-[30px]"></div>
                    <div class="h-1 bg-green-500 rounded-full w-[30px]"></div>
                    <div class="h-1 bg-yellow-400 rounded-full w-[30px]"></div>
                </div>

                <?php if ($find_us_description): ?>
                    <p class="mb-8 text-base leading-6 text-gray-500">
                        <?php echo esc_html($find_us_description); ?>
                    </p>
                <?php endif; ?>

                <!-- Office Locations -->
                <?php if (have_rows('office_locations')): ?>
                    <div class="space-y-6">
                        <?php while (have_rows('office_locations')): the_row();
                            $office_name = get_sub_field('office_name');
                            $address = get_sub_field('address');
                            $phone_numbers = get_sub_field('phone_numbers');
                            $email = get_sub_field('email');
                            $team_link = get_sub_field('team_link');
                            $map_image_id = get_sub_field('map_image');
                            $is_expanded = get_sub_field('is_expanded');
                            $location_id = 'location-' . wp_generate_uuid4();
                        ?>
                            <article class="overflow-hidden rounded-lg bg-slate-300" aria-labelledby="<?php echo esc_attr($location_id); ?>-title">
                                <!-- Expanded Location -->
                                <?php if ($is_expanded): ?>
                                    <div class="p-8 max-sm:p-6">
                                        <header class="flex justify-between items-center mb-6">
                                            <h3 id="<?php echo esc_attr($location_id); ?>-title" class="text-xl font-semibold leading-7 text-gray-900">
                                                <?php echo esc_html($office_name); ?>
                                            </h3>
                                            <button
                                                class="text-2xl text-gray-900 btn hover:text-gray-700 focus:text-gray-700"
                                                aria-expanded="true"
                                                aria-controls="<?php echo esc_attr($location_id); ?>-content"
                                                aria-label="Collapse <?php echo esc_attr($office_name); ?> details"
                                            >
                                                <i class="ti ti-chevron-up" aria-hidden="true"></i>
                                            </button>
                                        </header>

                                        <div id="<?php echo esc_attr($location_id); ?>-content" class="space-y-6">
                                            <?php if ($address): ?>
                                                <div class="flex gap-3 items-start">
                                                    <i class="flex-shrink-0 mt-0.5 text-xl text-gray-900 ti ti-map-pin" aria-hidden="true"></i>
                                                    <address class="text-sm not-italic leading-5 text-gray-900">
                                                        <?php echo wp_kses_post(nl2br($address)); ?>
                                                    </address>
                                                </div>
                                            <?php endif; ?>

                                            <?php if ($phone_numbers): ?>
                                                <div class="flex gap-3 items-start">
                                                    <i class="flex-shrink-0 text-xl text-gray-900 ti ti-phone" aria-hidden="true"></i>
                                                    <div class="text-sm leading-5 text-gray-900">
                                                        <?php
                                                        $phone_lines = explode("\n", $phone_numbers);
                                                        foreach ($phone_lines as $index => $phone):
                                                            $clean = preg_replace('/[^+\d]/', '', trim($phone));
                                                            if (!empty(trim($phone))):
                                                        ?>
                                                            <div class="<?php echo $index > 0 ? 'ml-6' : ''; ?>">
                                                                <a href="tel:<?php echo esc_attr($clean); ?>"
                                                                   class="hover:underline focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-500"
                                                                   aria-label="Call <?php echo esc_attr(trim($phone)); ?>">
                                                                    <?php echo esc_html(trim($phone)); ?>
                                                                </a>
                                                            </div>
                                                        <?php
                                                            endif;
                                                        endforeach;
                                                        ?>
                                                    </div>
                                                </div>
                                            <?php endif; ?>

                                            <?php if ($email): ?>
                                                <div class="flex gap-3 items-center">
                                                    <i class="flex-shrink-0 text-xl text-gray-900 ti ti-mail" aria-hidden="true"></i>
                                                    <a href="mailto:<?php echo esc_attr($email); ?>"
                                                       class="text-sm leading-5 text-gray-900 hover:underline focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-500"
                                                       aria-label="Send email to <?php echo esc_attr($email); ?>">
                                                        <?php echo esc_html($email); ?>
                                                    </a>
                                                </div>
                                            <?php endif; ?>

                                            <?php if ($team_link && is_array($team_link) && isset($team_link['url'], $team_link['title'])): ?>
                                                <div class="mb-6">
                                                    <a href="<?php echo esc_url($team_link['url']); ?>"
                                                       class="text-sm leading-5 text-gray-900 underline hover:no-underline focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-500"
                                                       target="<?php echo esc_attr($team_link['target'] ?? '_self'); ?>">
                                                        <?php echo esc_html($team_link['title']); ?>
                                                    </a>
                                                </div>
                                            <?php endif; ?>

                                            <?php if ($map_image_id): ?>
                                                <div class="overflow-hidden w-full bg-gray-400 rounded-lg h-[300px]">
                                                    <?php
                                                    $map_image_alt = get_post_meta($map_image_id, '_wp_attachment_image_alt', true) ?: 'Office location map';
                                                    echo wp_get_attachment_image($map_image_id, 'full', false, [
                                                        'alt' => esc_attr($map_image_alt),
                                                        'class' => 'w-full h-full object-cover',
                                                        'loading' => 'lazy'
                                                    ]);
                                                    ?>
                                                </div>
                                            <?php endif; ?>
                                        </div>
                                    </div>

                                <!-- Collapsed Location -->
                                <?php else: ?>
                                    <div class="p-8 max-sm:p-6">
                                        <button
                                            class="flex justify-between items-center w-full text-left btn"
                                            aria-expanded="false"
                                            aria-controls="<?php echo esc_attr($location_id); ?>-content"
                                            aria-label="Expand <?php echo esc_attr($office_name); ?> details"
                                        >
                                            <h3 id="<?php echo esc_attr($location_id); ?>-title" class="text-xl font-semibold leading-7 text-gray-900">
                                                <?php echo esc_html($office_name); ?>
                                            </h3>
                                            <i class="text-2xl text-gray-900 ti ti-chevron-down" aria-hidden="true"></i>
                                        </button>
                                    </div>
                                <?php endif; ?>
                            </article>
                        <?php endwhile; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</section>
