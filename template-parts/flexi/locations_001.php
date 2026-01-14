<?php
$locations = get_sub_field('locations');
$map_iframe = get_sub_field('map_iframe');
$background_color = get_sub_field('background_color');

$padding_classes = [];
if (have_rows('padding_settings')) {
    while (have_rows('padding_settings')) {
        the_row();
        $screen_size = get_sub_field('screen_size');
        $padding_top = get_sub_field('padding_top');
        $padding_bottom = get_sub_field('padding_bottom');
        $padding_classes[] = "{$screen_size}:pt-[{$padding_top}rem]";
        $padding_classes[] = "{$screen_size}:pb-[{$padding_bottom}rem]";
    }
}

$section_id = 'locations-' . uniqid();
?>

<section
    class="relative flex overflow-hidden <?php echo esc_attr(implode(' ', $padding_classes)); ?>"
    style="background-color: <?php echo esc_attr($background_color); ?>;"
    id="<?php echo esc_attr($section_id); ?>"
>
    <div class="flex flex-col items-center pt-5 pb-5 mx-auto w-full max-w-container max-lg:px-5">
        <div class="flex justify-between items-start w-full max-md:flex-col max-md:gap-8">

            <!-- Locations Panel -->
            <div class="w-full max-w-[400px] max-md:max-w-none">
                <div class="overflow-hidden p-8 bg-slate-300 max-md:p-6 max-sm:p-4">

                    <?php if ($locations && have_rows('locations')): ?>
                        <div class="space-y-0" role="region" aria-label="Office Locations">
                            <?php
                            $location_index = 0;
                            while (have_rows('locations')):
                                the_row();
                                $location_name = get_sub_field('location_name');
                                $address = get_sub_field('address');
                                $phone_numbers = get_sub_field('phone_numbers');
                                $email = get_sub_field('email');
                                $team_link = get_sub_field('team_link');
                                $is_expanded = get_sub_field('is_expanded');

                                $accordion_id = $section_id . '-accordion-' . $location_index;
                                $content_id = $section_id . '-content-' . $location_index;
                            ?>

                                <article class="border-b border-slate-200 <?php echo $location_index === 0 ? 'pb-4' : ''; ?>">
                                    <header>
                                        <button
                                            class="flex justify-between items-center py-4 w-full text-left btn focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-slate-600"
                                            aria-expanded="<?php echo $is_expanded ? 'true' : 'false'; ?>"
                                            aria-controls="<?php echo esc_attr($content_id); ?>"
                                            id="<?php echo esc_attr($accordion_id); ?>"
                                            data-accordion-trigger
                                        >
                                            <h2 class="text-base font-semibold leading-6 text-slate-950 max-sm:text-sm max-sm:leading-5">
                                                <?php echo esc_html($location_name); ?>
                                            </h2>

                                            <svg
                                                width="16"
                                                height="16"
                                                viewBox="0 0 16 16"
                                                fill="none"
                                                xmlns="http://www.w3.org/2000/svg"
                                                class="transition-transform duration-200 <?php echo $is_expanded ? 'rotate-180' : ''; ?>"
                                                aria-hidden="true"
                                            >
                                                <path
                                                    d="M4 6L8 10L12 6"
                                                    stroke="#020617"
                                                    stroke-width="1.33333"
                                                    stroke-linecap="round"
                                                    stroke-linejoin="round"
                                                />
                                            </svg>
                                        </button>
                                    </header>

                                    <div
                                        class="accordion-content <?php echo $is_expanded ? 'expanded' : 'collapsed'; ?>"
                                        id="<?php echo esc_attr($content_id); ?>"
                                        aria-labelledby="<?php echo esc_attr($accordion_id); ?>"
                                        role="region"
                                    >
                                        <div class="space-y-2">

                                            <?php if ($address): ?>
                                                <div class="flex gap-2 items-start">
                                                    <div class="flex items-center py-1">
                                                        <svg
                                                            width="24"
                                                            height="24"
                                                            viewBox="0 0 24 24"
                                                            fill="none"
                                                            xmlns="http://www.w3.org/2000/svg"
                                                            class="flex-shrink-0"
                                                            aria-hidden="true"
                                                        >
                                                            <path
                                                                d="M20 10C20 16 12 22 12 22C12 22 4 16 4 10C4 7.87827 4.84285 5.84344 6.34315 4.34315C7.84344 2.84285 9.87827 2 12 2C14.1217 2 16.1566 2.84285 17.6569 4.34315C19.1571 5.84344 20 7.87827 20 10Z"
                                                                stroke="#0A1119"
                                                                stroke-width="1.25"
                                                                stroke-linecap="round"
                                                            />
                                                            <path
                                                                d="M12 13C13.6569 13 15 11.6569 15 10C15 8.34315 13.6569 7 12 7C10.3431 7 9 8.34315 9 10C9 11.6569 10.3431 13 12 13Z"
                                                                stroke="#0A1119"
                                                                stroke-width="1.25"
                                                                stroke-linecap="round"
                                                            />
                                                        </svg>
                                                    </div>
                                                    <address class="py-1 not-italic">
                                                        <div class="text-sm leading-6 text-slate-950 max-sm:text-xs max-sm:leading-5 wp_editor">
                                                            <?php echo wp_kses_post($address); ?>
                                                        </div>
                                                    </address>
                                                </div>
                                            <?php endif; ?>

                                            <?php if ($phone_numbers): ?>
                                                <div class="flex gap-2 items-start">
                                                    <div class="flex items-center py-1">
                                                        <svg
                                                            width="24"
                                                            height="24"
                                                            viewBox="0 0 24 24"
                                                            fill="none"
                                                            xmlns="http://www.w3.org/2000/svg"
                                                            class="flex-shrink-0"
                                                            aria-hidden="true"
                                                        >
                                                            <path
                                                                d="M21.9999 16.92V19.92C22.0011 20.1985 21.944 20.4741 21.8324 20.7293C21.7209 20.9845 21.5572 21.2136 21.352 21.4018C21.1468 21.5901 20.9045 21.7335 20.6407 21.8227C20.3769 21.9119 20.0973 21.945 19.8199 21.92C16.7428 21.5856 13.7869 20.5341 11.1899 18.85C8.77376 17.3146 6.72527 15.2661 5.18993 12.85C3.49991 10.2412 2.44818 7.27097 2.11993 4.17997C2.09494 3.90344 2.12781 3.62474 2.21643 3.3616C2.30506 3.09846 2.4475 2.85666 2.6347 2.6516C2.82189 2.44653 3.04974 2.28268 3.30372 2.1705C3.55771 2.05831 3.83227 2.00024 4.10993 1.99997H7.10993C7.59524 1.9952 8.06572 2.16705 8.43369 2.48351C8.80166 2.79996 9.04201 3.23942 9.10993 3.71997C9.23656 4.68004 9.47138 5.6227 9.80993 6.52997C9.94448 6.8879 9.9736 7.27689 9.89384 7.65086C9.81408 8.02482 9.6288 8.36809 9.35993 8.63998L8.08993 9.90997C9.51349 12.4135 11.5864 14.4864 14.0899 15.91L15.3599 14.64C15.6318 14.3711 15.9751 14.1858 16.3491 14.1061C16.723 14.0263 17.112 14.0554 17.4699 14.19C18.3772 14.5285 19.3199 14.7634 20.2799 14.89C20.7657 14.9585 21.2093 15.2032 21.5265 15.5775C21.8436 15.9518 22.0121 16.4296 21.9999 16.92Z"
                                                                stroke="#0A1119"
                                                                stroke-width="1.25"
                                                                stroke-linecap="round"
                                                            />
                                                            <path
                                                                d="M14.0498 2C16.0881 2.21477 17.992 3.1188 19.4467 4.56258C20.9014 6.00636 21.8197 7.90341 22.0498 9.94"
                                                                stroke="#0A1119"
                                                                stroke-width="1.25"
                                                                stroke-linecap="round"
                                                            />
                                                            <path
                                                                d="M14.0498 6C15.0333 6.19394 15.9358 6.67903 16.6402 7.39231C17.3446 8.10559 17.8183 9.01413 17.9998 10"
                                                                stroke="#0A1119"
                                                                stroke-width="1.25"
                                                                stroke-linecap="round"
                                                            />
                                                        </svg>
                                                    </div>
                                                    <div class="py-1">
                                                        <div class="text-sm leading-6 text-slate-950 max-sm:text-xs max-sm:leading-5 wp_editor">
                                                            <?php echo wp_kses_post($phone_numbers); ?>
                                                        </div>
                                                    </div>
                                                </div>
                                            <?php endif; ?>

                                            <?php if ($email): ?>
                                                <div class="flex gap-2 items-center">
                                                    <div>
                                                        <svg
                                                            width="24"
                                                            height="24"
                                                            viewBox="0 0 24 24"
                                                            fill="none"
                                                            xmlns="http://www.w3.org/2000/svg"
                                                            class="flex-shrink-0"
                                                            aria-hidden="true"
                                                        >
                                                            <path
                                                                d="M20 4H4C2.89543 4 2 4.89543 2 6V18C2 19.1046 2.89543 20 4 20H20C21.1046 20 22 19.1046 22 18V6C22 4.89543 21.1046 4 20 4Z"
                                                                stroke="#0A1119"
                                                                stroke-width="1.25"
                                                                stroke-linecap="round"
                                                            />
                                                            <path
                                                                d="M22 7L13.03 12.7C12.7213 12.8934 12.3643 12.996 12 12.996C11.6357 12.996 11.2787 12.8934 10.97 12.7L2 7"
                                                                stroke="#0A1119"
                                                                stroke-width="1.25"
                                                                stroke-linecap="round"
                                                            />
                                                        </svg>
                                                    </div>
                                                    <div class="py-1">
                                                        <a
                                                            href="mailto:<?php echo esc_attr($email); ?>"
                                                            class="text-sm leading-6 text-slate-950 hover:underline max-sm:text-xs max-sm:leading-5"
                                                        >
                                                            <?php echo esc_html($email); ?>
                                                        </a>
                                                    </div>
                                                </div>
                                            <?php endif; ?>

                                            <?php if ($team_link && is_array($team_link) && isset($team_link['url'], $team_link['title'])): ?>
                                                <div class="pt-2">
                                                    <a
                                                        href="<?php echo esc_url($team_link['url']); ?>"
                                                        class="text-base leading-7 text-black underline cursor-pointer hover:no-underline max-sm:text-sm max-sm:leading-6"
                                                        target="<?php echo esc_attr($team_link['target'] ?? '_self'); ?>"
                                                    >
                                                        <?php echo esc_html($team_link['title']); ?>
                                                    </a>
                                                </div>
                                            <?php endif; ?>

                                        </div>
                                    </div>
                                </article>

                            <?php
                                $location_index++;
                            endwhile;
                            ?>
                        </div>
                    <?php endif; ?>

                </div>
            </div>

            <!-- Map Panel -->
            <div class="overflow-hidden flex-1 max-md:w-full">
                <div class="h-[500px] w-full max-md:h-[400px] max-sm:h-[300px]">
                    <?php if ($map_iframe): ?>
                        <div class="w-full h-full">
                            <?php echo wp_kses($map_iframe, [
                                'iframe' => [
                                    'src' => [],
                                    'width' => [],
                                    'height' => [],
                                    'style' => [],
                                    'frameborder' => [],
                                    'allowfullscreen' => [],
                                    'loading' => [],
                                    'referrerpolicy' => [],
                                    'title' => [],
                                    'aria-label' => [],
                                ]
                            ]); ?>
                        </div>
                    <?php else: ?>
                        <div class="flex justify-center items-center w-full h-full bg-slate-200">
                            <p class="text-slate-600">Map will be displayed here</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

        </div>
    </div>
</section>

<style>
.accordion-content {
    overflow: hidden;
    transition: max-height 0.3s ease-out, opacity 0.3s ease-out;
}

.accordion-content.collapsed {
    max-height: 0;
    opacity: 0;
}

.accordion-content.expanded {
    max-height: 1000px;
    opacity: 1;
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const accordionTriggers = document.querySelectorAll('[data-accordion-trigger]');

    accordionTriggers.forEach(trigger => {
        trigger.addEventListener('click', function() {
            const isExpanded = this.getAttribute('aria-expanded') === 'true';
            const contentId = this.getAttribute('aria-controls');
            const content = document.getElementById(contentId);
            const icon = this.querySelector('svg');

            // Toggle expanded state
            this.setAttribute('aria-expanded', !isExpanded);

            if (content) {
                if (isExpanded) {
                    content.classList.remove('expanded');
                    content.classList.add('collapsed');
                } else {
                    content.classList.remove('collapsed');
                    content.classList.add('expanded');
                }
            }

            // Rotate icon
            if (icon) {
                if (isExpanded) {
                    icon.classList.remove('rotate-180');
                } else {
                    icon.classList.add('rotate-180');
                }
            }
        });

        // Keyboard support
        trigger.addEventListener('keydown', function(e) {
            if (e.key === 'Enter' || e.key === ' ') {
                e.preventDefault();
                this.click();
            }
        });
    });
});
</script>
