<?php
// === Variables (always use get_sub_field) ===
$form_heading = get_sub_field('form_heading') ?: 'Book an evaluation';
$form_heading_tag = get_sub_field('form_heading_tag') ?: 'h2';
$form_description = get_sub_field('form_description') ?: 'Curious what your home\'s worth? Book your free evaluation today.';

$location_heading = get_sub_field('location_heading') ?: 'Where you can find us';
$location_heading_tag = get_sub_field('location_heading_tag') ?: 'h2';
$location_description = get_sub_field('location_description') ?: 'Curious what your home\'s worth? Book your free evaluation today.';

// WYSIWYG unformatted (prevent wpautop)
$form_markup = get_sub_field('form_markup', false, false);
if ($form_markup) {
    $form_markup = preg_replace('#</?p[^>]*>#i', '', $form_markup);
    $form_markup = preg_replace('#<br\s*/?>#i', '', $form_markup);
}

$privacy_policy_url = get_sub_field('privacy_policy_url') ?: '#';

// Map settings
$enable_map = (bool) get_sub_field('enable_map');
$map_iframe_url = get_sub_field('map_iframe_url') ?: '';

// Office locations
$offices = get_sub_field('offices') ?: [];

// Colors and styles
$background_color = get_sub_field('background_color') ?: '#ffffff';
$background_css = get_sub_field('background_css');
$text_color = get_sub_field('text_color') ?: '#0a0a0a';

// Padding classes
$padding_classes = ['', ''];
if (have_rows('padding_settings')) {
    while (have_rows('padding_settings')) {
        the_row();
        $screen_size = get_sub_field('screen_size');
        $padding_top = (string) get_sub_field('padding_top');
        $padding_bottom = (string) get_sub_field('padding_bottom');
        if ($screen_size !== '') {
            $padding_classes[] = "{$screen_size}:pt-[{$padding_top}rem]";
            $padding_classes[] = "{$screen_size}:pb-[{$padding_bottom}rem]";
        }
    }
}

// Unique section id
$section_id = 'contact-map-' . esc_attr(wp_generate_uuid4());

// ===== Form plumbing: inject action, nonce, posted mail config, privacy link =====
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
        esc_attr(wp_generate_uuid4()) // <-- one-time idempotency token
    );

    if ($name = get_sub_field('form_name')) {
        $hidden .= '<input type="hidden" name="_theme_form_name" value="' . esc_attr($name) . '">';
    }
    if (get_sub_field('save_entries_to_db')) {
        $hidden .= '<input type="hidden" name="_theme_save_to_db" value="1">';
    }

    // 🔐 Mail config (posted)
    $cfg_to = get_sub_field('email_to') ?: get_option('admin_email');
    $cfg_bcc = get_sub_field('email_bcc') ?: '';
    $cfg_subject = get_sub_field('email_subject') ?: '';
    $cfg_from_name = get_sub_field('from_name') ?: '';
    $cfg_from_email = get_sub_field('from_email') ?: '';

    $hidden_cfg = '';
    $hidden_cfg .= '<input type="hidden" name="_cfg_to" value="' . esc_attr($cfg_to) . '">';
    $hidden_cfg .= '<input type="hidden" name="_cfg_bcc" value="' . esc_attr($cfg_bcc) . '">';
    $hidden_cfg .= '<input type="hidden" name="_cfg_subject" value="' . esc_attr($cfg_subject) . '">';
    $hidden_cfg .= '<input type="hidden" name="_cfg_from_name" value="' . esc_attr($cfg_from_name) . '">';
    $hidden_cfg .= '<input type="hidden" name="_cfg_from_email" value="' . esc_attr($cfg_from_email) . '">';

    if (get_sub_field('enable_autoresponder')) {
        $hidden_cfg .= '<input type="hidden" name="_cfg_auto_enabled" value="1">';
        $hidden_cfg .= '<input type="hidden" name="_cfg_auto_subject" value="' . esc_attr(get_sub_field('autoresponder_subject') ?: '') . '">';
        $hidden_cfg .= '<input type="hidden" name="_cfg_auto_message" value="' . esc_attr(get_sub_field('autoresponder_message') ?: '') . '">';
    }

    $form_markup = str_replace('</form>', ($hidden . $hidden_cfg) . '</form>', $form_markup);
    $form_markup = str_replace('href="#"', 'href="' . esc_url($privacy_policy_url) . '"', $form_markup);
}
?>

<section id="<?php echo esc_attr($section_id); ?>"
         class="relative flex overflow-hidden <?php echo esc_attr(implode(' ', $padding_classes)); ?>"
         style="<?php echo esc_attr($background_css ? ("background: {$background_css}; color: {$text_color};") : ("background-color: {$background_color}; color: {$text_color};")); ?>">
    <div class="flex flex-col items-center pt-5 pb-5 mx-auto w-full max-w-container max-lg:px-5">

        <div class="flex justify-between items-center w-full bg-white">
            <div class="flex justify-between items-start w-full bg-white max-md:flex-col max-sm:flex-col">

                <!-- Left Column - Form -->
                <div class="flex flex-col flex-1 gap-6 items-start p-20 bg-white max-md:px-8 max-md:py-12 max-sm:px-4 max-sm:py-8">

                    <!-- Form Header -->
                    <header class="flex flex-col gap-6 items-start w-full">
                        <div class="flex flex-col gap-6 items-start w-full">
                            <?php if ($form_heading): ?>
                                <<?php echo esc_attr($form_heading_tag); ?> class="text-3xl font-semibold tracking-normal leading-10 text-slate-950 max-md:text-3xl max-md:leading-9 max-sm:text-2xl max-sm:leading-8">
                                    <?php echo esc_html($form_heading); ?>
                                </<?php echo esc_attr($form_heading_tag); ?>>
                            <?php endif; ?>

                            <!-- Decorative Bar -->
                            <div class="flex   justify-between items-center w-[71px] max-sm:w-[60px]" role="presentation" aria-hidden="true">
                                <div class="bg-orange-500 flex-1 h-[5px]"></div>
                                <div class="bg-sky-500 flex-1 h-[5px]"></div>
                                <div class="bg-slate-300 flex-1 h-[5px]"></div>
                                <div class="bg-lime-600 flex-1 h-[5px]"></div>
                            </div>
                        </div>
                    </header>

                    <?php if ($form_description): ?>
                        <p class="w-full text-lg font-medium tracking-wider leading-7 text-neutral-600 max-md:text-base max-md:leading-7 max-sm:text-sm max-sm:leading-6">
                            <?php echo esc_html($form_description); ?>
                        </p>
                    <?php endif; ?>

                    <!-- Form Container -->
                    <div class="box-border flex flex-col gap-3 items-start p-8 w-full bg-gray-200 max-sm:px-4 max-sm:py-6">
                        <?php if ($form_markup): ?>
                            <?php
                            echo wp_kses(
                                $form_markup,
                                [
                                    'form' => ['class' => [], 'role' => [], 'aria-labelledby' => [], 'novalidate' => [], 'action' => [], 'method' => [], 'enctype' => [], 'data-theme-form' => []],
                                    'div' => ['class' => [], 'id' => [], 'role' => [], 'aria-live' => [], 'aria-describedby' => []],
                                    'label' => ['for' => [], 'class' => [], 'id' => []],
                                    'input' => ['type' => [], 'id' => [], 'name' => [], 'placeholder' => [], 'required' => [], 'aria-required' => [], 'aria-describedby' => [], 'autocomplete' => [], 'class' => [], 'value' => []],
                                    'select' => ['id' => [], 'name' => [], 'required' => [], 'aria-required' => [], 'aria-describedby' => [], 'class' => []],
                                    'option' => ['value' => [], 'selected' => []],
                                    'textarea' => ['id' => [], 'name' => [], 'placeholder' => [], 'required' => [], 'aria-required' => [], 'aria-describedby' => [], 'rows' => [], 'class' => []],
                                    'button' => ['type' => [], 'class' => [], 'aria-describedby' => []],
                                    'svg' => ['class' => [], 'fill' => [], 'stroke' => [], 'viewBox' => [], 'xmlns' => [], 'aria-hidden' => [], 'width' => [], 'height' => [], 'style' => []],
                                    'path' => ['stroke-linecap' => [], 'stroke-linejoin' => [], 'stroke-width' => [], 'd' => [], 'stroke' => [], 'opacity' => []],
                                    'a' => ['href' => [], 'class' => [], 'target' => [], 'aria-label' => []],
                                    'img' => ['src' => [], 'alt' => [], 'class' => [], 'width' => [], 'height' => []],
                                ]
                            );
                            ?>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Right Column - Location Info -->
                <div class="flex flex-col flex-1 gap-6 items-start p-20 bg-white max-md:px-8 max-md:py-12 max-sm:px-4 max-sm:py-8">

                    <!-- Location Header -->
                    <header class="flex flex-col gap-6 items-start w-full">
                        <div class="flex flex-col gap-6 items-start w-full">
                            <?php if ($location_heading): ?>
                                <<?php echo esc_attr($location_heading_tag); ?> class="text-3xl font-semibold tracking-normal leading-10 text-slate-950 max-md:text-3xl max-md:leading-9 max-sm:text-2xl max-sm:leading-8">
                                    <?php echo esc_html($location_heading); ?>
                                </<?php echo esc_attr($location_heading_tag); ?>>
                            <?php endif; ?>

                            <!-- Decorative Bar -->
                            <div class="flex   justify-between items-center w-[71px] max-sm:w-[60px]" role="presentation" aria-hidden="true">
                                <div class="bg-orange-500 flex-1 h-[5px]"></div>
                                <div class="bg-sky-500 flex-1 h-[5px]"></div>
                                <div class="bg-slate-300 flex-1 h-[5px]"></div>
                                <div class="bg-lime-600 flex-1 h-[5px]"></div>
                            </div>
                        </div>
                    </header>

                    <?php if ($location_description): ?>
                        <p class="w-full text-lg font-medium tracking-wider leading-7 text-neutral-600 max-md:text-base max-md:leading-7 max-sm:text-sm max-sm:leading-6">
                            <?php echo esc_html($location_description); ?>
                        </p>
                    <?php endif; ?>

                    <!-- Offices Container -->
                    <div class="box-border flex flex-col flex-1 gap-3 items-start p-8 w-full bg-slate-300 max-sm:px-4 max-sm:py-6">

                        <?php if (!empty($offices)): ?>
                            <?php foreach ($offices as $index => $office): ?>
                                <?php
                                $office_name = $office['office_name'] ?? '';
                                $office_address = $office['office_address'] ?? '';
                                $office_phone = $office['office_phone'] ?? '';
                                $office_email = $office['office_email'] ?? '';
                                $team_link = $office['team_link'] ?? [];
                                $map_image = $office['map_image'] ?? '';
                                $is_expanded = $office['is_expanded'] ?? false;
                                ?>

                                <article class="flex flex-col items-start w-full border-b border-solid border-b-slate-200 <?php echo $index === count($offices) - 1 ? '' : 'pb-4'; ?>">

                                    <!-- Office Header -->
                                    <header class="flex justify-between items-center px-0 py-4 w-full cursor-pointer"
                                            onclick="toggleOffice(this)"
                                            onkeydown="if(event.key === 'Enter' || event.key === ' ') { event.preventDefault(); toggleOffice(this); }"
                                            tabindex="0"
                                            role="button"
                                            aria-expanded="<?php echo $is_expanded ? 'true' : 'false'; ?>"
                                            aria-controls="office-content-<?php echo esc_attr($index); ?>">
                                        <div class="flex flex-col flex-1 items-start">
                                            <h3 class="text-base font-semibold tracking-normal leading-6 text-slate-950 max-sm:text-sm">
                                                <?php echo esc_html($office_name); ?>
                                            </h3>
                                        </div>
                                        <div>
                                            <svg width="16" height="16" viewBox="0 0 16 16" fill="none" xmlns="http://www.w3.org/2000/svg"
                                                 class="chevron-icon <?php echo $is_expanded ? 'chevron-up' : 'chevron-down'; ?>"
                                                 style="width: 16px; height: 16px; flex-shrink: 0">
                                                <?php if ($is_expanded): ?>
                                                    <path d="M12 10L8 6L4 10" stroke="#020617" stroke-width="1.33333" stroke-linecap="round" stroke-linejoin="round"></path>
                                                <?php else: ?>
                                                    <path d="M4 6L8 10L12 6" stroke="#020617" stroke-width="1.33333" stroke-linecap="round" stroke-linejoin="round"></path>
                                                <?php endif; ?>
                                            </svg>
                                        </div>
                                    </header>

                                    <!-- Office Content -->
                                    <div id="office-content-<?php echo esc_attr($index); ?>"
                                         class="office-content <?php echo $is_expanded ? 'expanded' : 'collapsed'; ?>"
                                         aria-hidden="<?php echo $is_expanded ? 'false' : 'true'; ?>">

                                        <div class="flex flex-col gap-2 items-start pb-4">
                                            <div class="flex flex-col gap-2 items-start w-full">

                                                <!-- Address -->
                                                <?php if ($office_address): ?>
                                                    <div class="flex gap-2 items-start">
                                                        <div class="flex items-center px-0 py-1">
                                                            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" class="map-pin-icon" style="width: 24px; height: 24px">
                                                                <path d="M20 10C20 16 12 22 12 22C12 22 4 16 4 10C4 7.87827 4.84285 5.84344 6.34315 4.34315C7.84344 2.84285 9.87827 2 12 2C14.1217 2 16.1566 2.84285 17.6569 4.34315C19.1571 5.84344 20 7.87827 20 10Z" stroke="#0A1119" stroke-width="1.25" stroke-linecap="round"></path>
                                                                <path d="M12 13C13.6569 13 15 11.6569 15 10C15 8.34315 13.6569 7 12 7C10.3431 7 9 8.34315 9 10C9 11.6569 10.3431 13 12 13Z" stroke="#0A1119" stroke-width="1.25" stroke-linecap="round"></path>
                                                            </svg>
                                                        </div>
                                                        <div class="flex flex-col gap-2.5 items-start px-0 py-1">
                                                            <address class="text-sm not-italic tracking-normal leading-6 text-slate-950 max-sm:text-sm">
                                                                <?php echo wp_kses_post(nl2br($office_address)); ?>
                                                            </address>
                                                        </div>
                                                    </div>
                                                <?php endif; ?>

                                                <!-- Phone -->
                                                <?php if ($office_phone): ?>
                                                    <div class="flex gap-2 items-start">
                                                        <div class="flex items-start px-0 py-1">
                                                            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" class="phone-icon" style="width: 24px; height: 24px">
                                                                <path d="M22.0004 16.9201V19.9201C22.0016 20.1986 21.9445 20.4743 21.8329 20.7294C21.7214 20.9846 21.5577 21.2137 21.3525 21.402C21.1473 21.5902 20.905 21.7336 20.6412 21.8228C20.3773 21.912 20.0978 21.9452 19.8204 21.9201C16.7433 21.5857 13.7874 20.5342 11.1904 18.8501C8.77425 17.3148 6.72576 15.2663 5.19042 12.8501C3.5004 10.2413 2.44866 7.27109 2.12042 4.1801C2.09543 3.90356 2.1283 3.62486 2.21692 3.36172C2.30555 3.09859 2.44799 2.85679 2.63519 2.65172C2.82238 2.44665 3.05023 2.28281 3.30421 2.17062C3.5582 2.05843 3.83276 2.00036 4.11042 2.0001H7.11042C7.59573 1.99532 8.06621 2.16718 8.43418 2.48363C8.80215 2.80008 9.0425 3.23954 9.11042 3.7201C9.23704 4.68016 9.47187 5.62282 9.81042 6.5301C9.94497 6.88802 9.97408 7.27701 9.89433 7.65098C9.81457 8.02494 9.62928 8.36821 9.36042 8.6401L8.09042 9.9101C9.51398 12.4136 11.5869 14.4865 14.0904 15.9101L15.3604 14.6401C15.6323 14.3712 15.9756 14.1859 16.3495 14.1062C16.7235 14.0264 17.1125 14.0556 17.4704 14.1901C18.3777 14.5286 19.3204 14.7635 20.2804 14.8901C20.7662 14.9586 21.2098 15.2033 21.527 15.5776C21.8441 15.9519 22.0126 16.4297 22.0004 16.9201Z" stroke="#0A1119" stroke-width="1.25" stroke-linecap="round"></path>
                                                                <path d="M14.0508 2C16.089 2.21477 17.993 3.1188 19.4476 4.56258C20.9023 6.00636 21.8207 7.90341 22.0508 9.94" stroke="#0A1119" stroke-width="1.25" stroke-linecap="round"></path>
                                                                <path d="M14.0508 6C15.0343 6.19394 15.9368 6.67903 16.6412 7.39231C17.3455 8.10559 17.8192 9.01413 18.0008 10" stroke="#0A1119" stroke-width="1.25" stroke-linecap="round"></path>
                                                            </svg>
                                                        </div>
                                                        <div class="flex flex-col gap-2.5 items-start px-0 py-1">
                                                            <div class="text-sm tracking-normal leading-6 text-slate-950 max-sm:text-sm">
                                                                <?php echo wp_kses_post(nl2br($office_phone)); ?>
                                                            </div>
                                                        </div>
                                                    </div>
                                                <?php endif; ?>

                                                <!-- Email -->
                                                <?php if ($office_email): ?>
                                                    <div class="flex gap-2 items-start">
                                                        <div>
                                                            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" class="email-icon" style="width: 24px; height: 24px">
                                                                <path d="M20 4H4C2.89543 4 2 4.89543 2 6V18C2 19.1046 2.89543 20 4 20H20C21.1046 20 22 19.1046 22 18V6C22 4.89543 21.1046 4 20 4Z" stroke="#0A1119" stroke-width="1.25" stroke-linecap="round"></path>
                                                                <path d="M22 7L13.03 12.7C12.7213 12.8934 12.3643 12.996 12 12.996C11.6357 12.996 11.2787 12.8934 10.97 12.7L2 7" stroke="#0A1119" stroke-width="1.25" stroke-linecap="round"></path>
                                                            </svg>
                                                        </div>
                                                        <div class="flex flex-col gap-2.5 items-start px-0 py-1">
                                                            <div class="text-sm tracking-normal leading-6 text-slate-950 max-sm:text-sm">
                                                                <a href="mailto:<?php echo esc_attr($office_email); ?>" class="hover:underline focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-neutral-500">
                                                                    <?php echo esc_html($office_email); ?>
                                                                </a>
                                                            </div>
                                                        </div>
                                                    </div>
                                                <?php endif; ?>

                                                <!-- Team Link -->
                                                <?php if ($team_link && is_array($team_link) && isset($team_link['url'], $team_link['title'])): ?>
                                                    <a href="<?php echo esc_url($team_link['url']); ?>"
                                                       class="text-base tracking-normal leading-7 text-black underline rounded max-sm:text-sm hover:text-blue-600 focus:text-blue-600 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-1"
                                                       target="<?php echo esc_attr($team_link['target'] ?? '_self'); ?>">
                                                        <?php echo esc_html($team_link['title']); ?>
                                                    </a>
                                                <?php endif; ?>
                                            </div>
                                        </div>

                                        <!-- Map Image -->
                                        <?php if ($map_image && $is_expanded): ?>
                                            <div class="w-full">
                                                <?php echo wp_get_attachment_image($map_image, 'full', false, [
                                                    'alt' => esc_attr($office_name . ' location map'),
                                                    'class' => 'flex object-cover w-full h-[302px]',
                                                ]); ?>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                </article>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>

        <!-- Map Section -->
        <?php if ($enable_map && $map_iframe_url): ?>
            <div class="overflow-hidden z-0 mt-12 w-full max-w-full">
                <iframe
                    src="<?php echo esc_url($map_iframe_url); ?>"
                    class="w-full h-[540px] max-md:h-[400px] max-sm:h-[300px] overflow-hidden border-0"
                    title="Office locations map"
                    loading="lazy"
                    referrerpolicy="no-referrer-when-downgrade">
                </iframe>
            </div>
        <?php endif; ?>
    </div>
</section>

<script>
function toggleOffice(element) {
    const content = element.nextElementSibling;
    const chevron = element.querySelector('.chevron-icon');
    const isExpanded = element.getAttribute('aria-expanded') === 'true';

    // Toggle expanded state
    element.setAttribute('aria-expanded', !isExpanded);
    content.setAttribute('aria-hidden', isExpanded);
    content.classList.toggle('expanded', !isExpanded);
    content.classList.toggle('collapsed', isExpanded);

    // Toggle chevron direction
    if (isExpanded) {
        chevron.classList.remove('chevron-up');
        chevron.classList.add('chevron-down');
        chevron.innerHTML = '<path d="M4 6L8 10L12 6" stroke="#020617" stroke-width="1.33333" stroke-linecap="round" stroke-linejoin="round"></path>';
    } else {
        chevron.classList.remove('chevron-down');
        chevron.classList.add('chevron-up');
        chevron.innerHTML = '<path d="M12 10L8 6L4 10" stroke="#020617" stroke-width="1.33333" stroke-linecap="round" stroke-linejoin="round"></path>';
    }
}
</script>

<style>
.office-content {
    transition: all 0.3s ease;
    overflow: hidden;
}

.office-content.collapsed {
    max-height: 0;
    opacity: 0;
}

.office-content.expanded {
    max-height: 1000px;
    opacity: 1;
}

.chevron-icon {
    transition: transform 0.3s ease;
}
</style>
<form class="w-full"
      role="form"
      aria-labelledby="form-heading"
      novalidate>

    <!-- Personal Information Row -->
    <div class="grid grid-cols-1 gap-3 mb-3 md:grid-cols-2">
        <!-- First Name -->
        <div class="flex flex-col gap-1">
            <label for="first_name" class="text-base font-semibold tracking-normal leading-6 text-slate-950 max-sm:text-sm">
                First name
            </label>
            <div class="box-border flex justify-between items-center px-3 py-2 w-full h-10 bg-white rounded border border-solid border-neutral-200 min-h-9">
                <input
                    type="text"
                    id="first_name"
                    name="first_name"
                    placeholder="First name"
                    required
                    aria-required="true"
                    aria-describedby="first-name-error"
                    class="flex-1 text-base tracking-normal leading-6 bg-transparent border-none outline-none text-slate-950 max-sm:text-sm"
                />
            </div>
            <div id="first-name-error" class="hidden mt-1 text-xs text-red-600" role="alert" aria-live="polite"></div>
        </div>

        <!-- Last Name -->
        <div class="flex flex-col gap-1">
            <label for="last_name" class="text-base font-semibold tracking-normal leading-6 text-slate-950 max-sm:text-sm">
                Last name
            </label>
            <div class="box-border flex justify-between items-center px-3 py-2 w-full h-10 bg-white rounded border border-solid border-neutral-200 min-h-9">
                <input
                    type="text"
                    id="last_name"
                    name="last_name"
                    placeholder="Last name"
                    required
                    aria-required="true"
                    aria-describedby="last-name-error"
                    class="flex-1 text-base tracking-normal leading-6 bg-transparent border-none outline-none text-slate-950 max-sm:text-sm"
                />
            </div>
            <div id="last-name-error" class="hidden mt-1 text-xs text-red-600" role="alert" aria-live="polite"></div>
        </div>
    </div>

    <!-- Contact Information Row -->
    <div class="grid grid-cols-1 gap-3 mb-3 md:grid-cols-2">
        <!-- Email Address -->
        <div class="flex flex-col gap-1">
            <label for="email_address" class="text-base font-semibold tracking-normal leading-6 text-slate-950 max-sm:text-sm">
                Email address
            </label>
            <div class="box-border flex justify-between items-center px-3 py-2 w-full h-10 bg-white rounded border border-solid border-neutral-200 min-h-9">
                <input
                    type="email"
                    id="email_address"
                    name="email"
                    placeholder="Email address"
                    required
                    aria-required="true"
                    aria-describedby="email-error"
                    autocomplete="email"
                    class="flex-1 text-base tracking-normal leading-6 bg-transparent border-none outline-none text-slate-950 max-sm:text-sm"
                />
            </div>
            <div id="email-error" class="hidden mt-1 text-xs text-red-600" role="alert" aria-live="polite"></div>
        </div>

        <!-- Phone Number -->
        <div class="flex flex-col gap-1">
            <label for="phone_number" class="text-base font-semibold tracking-normal leading-6 text-slate-950 max-sm:text-sm">
                Phone number
            </label>
            <div class="box-border flex justify-between items-center px-3 py-2 w-full h-10 bg-white rounded border border-solid border-neutral-200 min-h-9">
                <input
                    type="tel"
                    id="phone_number"
                    name="phone"
                    placeholder="Phone number"
                    aria-describedby="phone-help"
                    autocomplete="tel"
                    class="flex-1 text-base tracking-normal leading-6 bg-transparent border-none outline-none text-slate-950 max-sm:text-sm"
                />
            </div>
            <div id="phone-help" class="mt-1 text-xs text-gray-500">Include country code (e.g., +353)</div>
        </div>
    </div>

    <!-- Query Type -->
    <div class="flex flex-col gap-1 mb-3">
        <label for="query_type" class="text-base font-semibold tracking-normal leading-6 text-slate-950 max-sm:text-sm">
            Query type
        </label>
        <div class="box-border flex justify-between items-center px-3 py-2 w-full h-10 bg-white rounded border border-solid border-neutral-200 min-h-9">
            <select
                id="query_type"
                name="query_type"
                required
                aria-required="true"
                aria-describedby="query-type-error"
                class="flex-1 text-base tracking-normal leading-6 bg-transparent border-none appearance-none outline-none text-slate-950 max-sm:text-sm"
            >
                <option value="">Select a query type</option>
                <option value="property_valuation">Property Valuation</option>
                <option value="buying">Buying Property</option>
                <option value="selling">Selling Property</option>
                <option value="renting">Renting Property</option>
                <option value="general_inquiry">General Inquiry</option>
            </select>
            <svg width="16" height="16" viewBox="0 0 16 16" fill="none" xmlns="http://www.w3.org/2000/svg" class="chevron-icon" style="width: 16px; height: 16px; flex-shrink: 0">
                <path d="M4 6L8 10L12 6" stroke="#64748B" stroke-linecap="round" stroke-linejoin="round"></path>
            </svg>
        </div>
        <div id="query-type-error" class="hidden mt-1 text-xs text-red-600" role="alert" aria-live="polite"></div>
    </div>

    <!-- Property Address -->
    <div class="flex flex-col gap-1 mb-3">
        <label for="property_address" class="text-base font-semibold tracking-normal leading-6 text-slate-950 max-sm:text-sm">
            Property address
        </label>
        <div class="box-border flex justify-between items-center px-3 py-2 w-full h-10 bg-white rounded border border-solid border-neutral-200 min-h-9">
            <input
                type="text"
                id="property_address"
                name="property_address"
                placeholder="Write the property's address"
                class="flex-1 text-base tracking-normal leading-6 bg-transparent border-none outline-none text-slate-950 max-sm:text-sm"
            />
        </div>
    </div>

    <!-- Property Details Row -->
    <div class="grid grid-cols-1 gap-3 mb-3 md:grid-cols-2">
        <!-- Property Type -->
        <div class="flex flex-col gap-1">
            <label for="property_type" class="text-base font-semibold tracking-normal leading-6 text-slate-950 max-sm:text-sm">
                Property type
            </label>
            <div class="box-border flex justify-between items-center px-3 py-2 w-full h-10 bg-white rounded border border-solid border-neutral-200 min-h-9">
                <select
                    id="property_type"
                    name="property_type"
                    class="flex-1 text-base tracking-normal leading-6 bg-transparent border-none appearance-none outline-none text-slate-950 max-sm:text-sm"
                >
                    <option value="">Select property type</option>
                    <option value="house">House</option>
                    <option value="apartment">Apartment</option>
                    <option value="townhouse">Townhouse</option>
                    <option value="duplex">Duplex</option>
                    <option value="commercial">Commercial</option>
                </select>
                <svg width="16" height="16" viewBox="0 0 16 16" fill="none" xmlns="http://www.w3.org/2000/svg" class="chevron-icon" style="width: 16px; height: 16px; flex-shrink: 0">
                    <path d="M4 6L8 10L12 6" stroke="#64748B" stroke-linecap="round" stroke-linejoin="round"></path>
                </svg>
            </div>
        </div>

        <!-- Property Condition -->
        <div class="flex flex-col gap-1">
            <label for="property_condition" class="text-base font-semibold tracking-normal leading-6 text-slate-950 max-sm:text-sm">
                Condition of the property
            </label>
            <div class="box-border flex justify-between items-center px-3 py-2 w-full h-10 bg-white rounded border border-solid border-neutral-200 min-h-9">
                <select
                    id="property_condition"
                    name="property_condition"
                    class="flex-1 text-base tracking-normal leading-6 bg-transparent border-none appearance-none outline-none text-slate-950 max-sm:text-sm"
                >
                    <option value="">Select a condition</option>
                    <option value="excellent">Excellent</option>
                    <option value="good">Good</option>
                    <option value="fair">Fair</option>
                    <option value="needs_renovation">Needs Renovation</option>
                </select>
                <svg width="16" height="16" viewBox="0 0 16 16" fill="none" xmlns="http://www.w3.org/2000/svg" class="chevron-icon" style="width: 16px; height: 16px; flex-shrink: 0">
                    <path d="M4 6L8 10L12 6" stroke="#64748B" stroke-linecap="round" stroke-linejoin="round"></path>
                </svg>
            </div>
        </div>
    </div>

    <!-- Bedrooms and Bathrooms Row -->
    <div class="grid grid-cols-1 gap-3 mb-3 md:grid-cols-2">
        <!-- Number of Bedrooms -->
        <div class="flex flex-col gap-1">
            <label for="bedrooms" class="text-base font-semibold tracking-normal leading-6 text-slate-950 max-sm:text-sm">
                Number of bedrooms
            </label>
            <div class="box-border flex justify-between items-center px-3 py-2 w-full h-10 bg-white rounded border border-solid border-neutral-200 min-h-9">
                <select
                    id="bedrooms"
                    name="bedrooms"
                    class="flex-1 text-base tracking-normal leading-6 bg-transparent border-none appearance-none outline-none text-slate-950 max-sm:text-sm"
                >
                    <option value="">Select a number</option>
                    <option value="1">1</option>
                    <option value="2">2</option>
                    <option value="3">3</option>
                    <option value="4">4</option>
                    <option value="5">5+</option>
                </select>
                <svg width="16" height="16" viewBox="0 0 16 16" fill="none" xmlns="http://www.w3.org/2000/svg" class="chevron-icon" style="width: 16px; height: 16px; flex-shrink: 0">
                    <path d="M4 6L8 10L12 6" stroke="#64748B" stroke-linecap="round" stroke-linejoin="round"></path>
                </svg>
            </div>
        </div>

        <!-- Number of Bathrooms -->
        <div class="flex flex-col gap-1">
            <label for="bathrooms" class="text-base font-semibold tracking-normal leading-6 text-slate-950 max-sm:text-sm">
                Number of bathrooms
            </label>
            <div class="box-border flex justify-between items-center px-3 py-2 w-full h-10 bg-white rounded border border-solid border-neutral-200 min-h-9">
                <select
                    id="bathrooms"
                    name="bathrooms"
                    class="flex-1 text-base tracking-normal leading-6 bg-transparent border-none appearance-none outline-none text-slate-950 max-sm:text-sm"
                >
                    <option value="">Select a number</option>
                    <option value="1">1</option>
                    <option value="2">2</option>
                    <option value="3">3</option>
                    <option value="4">4+</option>
                </select>
                <svg width="16" height="16" viewBox="0 0 16 16" fill="none" xmlns="http://www.w3.org/2000/svg" class="chevron-icon" style="width: 16px; height: 16px; flex-shrink: 0">
                    <path d="M4 6L8 10L12 6" stroke="#64748B" stroke-linecap="round" stroke-linejoin="round"></path>
                </svg>
            </div>
        </div>
    </div>

    <!-- Message -->
    <div class="flex flex-col gap-1 mb-3">
        <label for="message" class="text-base font-semibold tracking-normal leading-6 text-slate-950 max-sm:text-sm">
            Message
        </label>
        <div class="box-border flex relative gap-2.5 items-start px-3 py-2 w-full h-20 bg-white rounded border border-solid border-slate-200 min-h-20">
            <textarea
                id="message"
                name="message"
                placeholder="Write a message"
                required
                aria-required="true"
                aria-describedby="message-error"
                rows="4"
                class="flex-1 text-base tracking-normal leading-6 bg-transparent border-none outline-none resize-none text-slate-950 max-sm:text-sm"
            ></textarea>
            <svg width="16" height="16" viewBox="0 0 16 16" fill="none" xmlns="http://www.w3.org/2000/svg" class="resize-thumb" style="width: 16px; height: 16px; position: absolute; right: 2px; bottom: 2px;">
                <path opacity="0.6" d="M15.9658 12.9648L12.9531 15.9776" stroke="#060706" stroke-width="0.75" stroke-linecap="round"></path>
                <path opacity="0.6" d="M16 9L9 16" stroke="#060706" stroke-width="0.75" stroke-linecap="round"></path>
            </svg>
        </div>
        <div id="message-error" class="hidden mt-1 text-xs text-red-600" role="alert" aria-live="polite"></div>
    </div>

    <!-- Privacy Policy Checkbox -->
    <div class="flex gap-2 items-center mb-3 w-full">
        <div class="flex items-start pt-2">
            <input
                type="checkbox"
                id="privacy_policy"
                name="privacy_policy"
                required
                aria-required="true"
                aria-describedby="privacy-error"
                class="w-4 h-4 rounded-sm border border-solid border-slate-900"
            />
        </div>
        <label for="privacy_policy" class="flex-1 text-base tracking-normal leading-7 cursor-pointer text-slate-950 max-sm:text-sm">
            I agree to the handling of my personal data in accordance with ALTUs
            <a href="#" class="underline rounded hover:text-blue-600 focus:text-blue-600 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-1">Privacy Policy</a>.
        </label>
        <div id="privacy-error" class="hidden mt-1 w-full text-xs text-red-600" role="alert" aria-live="polite"></div>
    </div>

    <!-- CAPTCHA Placeholder -->
    <img src="https://api.builder.io/api/v1/image/assets/TEMP/8feebd3bdb1dcdc72743b2f0c3b8d2cf1b4fdda8?width=479" alt="CAPTCHA verification" class="mb-3 w-60 h-28 max-sm:w-full max-sm:h-auto" />

    <!-- Submit Button -->
    <button
        type="submit"
        class="flex gap-2.5 justify-center items-center px-2 py-0 w-full h-11 text-sm font-semibold tracking-normal leading-6 transition-colors duration-200 cursor-pointer btn bg-slate-900 text-slate-50 max-sm:text-sm hover:bg-slate-800"
        aria-describedby="submit-help"
    >
        Send message
    </button>
    <div id="submit-help" class="mt-2 text-xs text-gray-500">
        Please fill in all required fields before submitting.
    </div>

    <!-- Success/Error Messages -->
    <div id="form-messages" class="hidden mt-4" role="alert" aria-live="polite"></div>
</form>
