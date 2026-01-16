<?php
// ============== Fetch ==============
$heading      = get_sub_field('heading');
$heading_tag  = get_sub_field('heading_tag');
$description  = get_sub_field('content');
$grid_rows    = get_sub_field('grid_rows');
$section_bg   = get_sub_field('background_color');

// Whitelist heading tag
$allowed_tags = ['h1','h2','h3','h4','h5','h6','p','span'];
if (!in_array($heading_tag, $allowed_tags, true)) {
    $heading_tag = 'h2';
}

// ============== Padding classes (apply to inner container) ==============
$padding_classes = [];
if (have_rows('padding_settings')) {
    while (have_rows('padding_settings')) {
        the_row();
        $screen_size    = get_sub_field('screen_size');
        $padding_top    = get_sub_field('padding_top');
        $padding_bottom = get_sub_field('padding_bottom');
        $padding_classes[] = "{$screen_size}:pt-[{$padding_top}rem]";
        $padding_classes[] = "{$screen_size}:pb-[{$padding_bottom}rem]";
    }
}

// ============== Helpers ==============
$section_id  = 'content-grid-' . uniqid();
$has_heading = !empty($heading);

// Safely clamp an integer
$clamp = function ($val, $min, $max) {
    $v = (int) $val;
    if ($v < $min) $v = $min;
    if ($v > $max) $v = $max;
    return $v;
};
?>
<section
    id="<?php echo esc_attr($section_id); ?>"
    class="flex overflow-hidden relative"
    <?php echo $has_heading ? 'aria-labelledby="' . esc_attr($section_id) . '-heading"' : ''; ?>
    <?php if (!empty($section_bg)) : ?>
        style="background-color: <?php echo esc_attr($section_bg); ?>;"
    <?php endif; ?>
>
    <div class="flex flex-col items-center w-full mx-auto max-w-container lg:py-24 pt-5 pb-5 max-xl:px-5 <?php echo esc_attr(implode(' ', $padding_classes)); ?>">

        <?php if ($has_heading): ?>
            <header class="mb-12 w-full text-left max-md:mb-8">
                <<?php echo esc_attr($heading_tag); ?>
                    id="<?php echo esc_attr($section_id); ?>-heading"
                    class="mb-6 text-3xl font-semibold tracking-normal leading-10 text-left font-secondary text-primary max-md:text-3xl max-md:leading-9 max-sm:text-2xl max-sm:leading-8"
                >
                    <?php echo esc_html($heading); ?>
                </<?php echo esc_attr($heading_tag); ?>>
                <!-- Decorative Color Bars -->
                <div class="flex   justify-between mr-auto items-left w-[71px] max-sm:w-[60px]" role="presentation" aria-hidden="true">
                    <div class="bg-orange-500 flex-1 h-[5px]"></div>
                    <div class="bg-sky-500 flex-1 h-[5px]"></div>
                    <div class="bg-slate-300 flex-1 h-[5px]"></div>
                    <div class="bg-lime-600 flex-1 h-[5px]"></div>
                </div>
            </header>
        <?php endif; ?>

        <?php if (!empty($description)): ?>
            <div class="mb-12 w-full text-left max-md:mb-10">
                <div class="w-full text-base tracking-normal leading-7 text-black wp_editor">
                    <?php echo wp_kses_post($description); ?>
                </div>
            </div>
        <?php endif; ?>

        <?php if (!empty($grid_rows) && is_array($grid_rows)): ?>
            <div class="flex flex-col gap-12 w-full">
                <?php foreach ($grid_rows as $row_index => $row): ?>
                    <?php
                    $items = $row['items'] ?? [];
                    $count = is_array($items) ? count($items) : 0;
                    if ($count < 1) continue;

                    // Columns per breakpoint
                    $cols_md = $clamp(min($count, 3), 1, 12);
                    $cols_lg = $clamp($count, 1, 12);

                    $grid_classes = "grid grid-cols-1 md:grid-cols-{$cols_md} lg:grid-cols-{$cols_lg} gap-6 w-full";
                    ?>
                    <div class="<?php echo esc_attr($grid_classes); ?>">
                        <?php foreach ($items as $i => $item): ?>
                            <?php
                            $item_content = $item['content'] ?? '';
                            $item_bg      = $item['item_background_color'] ?? '#E5E7EB';

                            // Fixed palette select
                            $bar_color    = $item['bar_color'] ?? '#0098D8';

                            $item_id = $section_id . '-row-' . ($row_index + 1) . '-item-' . ($i + 1);
                            ?>
                            <article
                                id="<?php echo esc_attr($item_id); ?>"
                                class="flex flex-col justify-center p-8 min-h-[200px] max-md:px-5"
                                style="background-color: <?php echo esc_attr($item_bg); ?>;"
                            >
                                <?php if (!empty($item_content)): ?>
                                    <div class="text-center font-secondary  font-semibold text-[1.5rem] leading-[1.625rem] tracking-[-0.01rem] text-[#0A1119] wp_editor">
                                        <?php echo wp_kses_post($item_content); ?>
                                    </div>
                                <?php endif; ?>

                                <!-- Fixed-size decorative bar -->
                                <div
                                    class="mx-auto mt-4 flex w-full max-w-[6.25rem] h-[0.625rem] justify-center items-center"
                                    role="presentation"
                                    aria-hidden="true"
                                    style="
                                        background-color: <?php echo esc_attr($bar_color); ?>;
                                    "
                                ></div>
                            </article>
                        <?php endforeach; ?>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

    </div> 
</section>
