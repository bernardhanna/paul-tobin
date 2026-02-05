<?php
// ============== Fetch ==============
$heading      = get_sub_field('heading');
$heading_tag  = get_sub_field('heading_tag');
$description  = get_sub_field('content');
$grid_rows    = get_sub_field('grid_rows');
$section_bg   = get_sub_field('background_color');

$allowed_tags = ['h1','h2','h3','h4','h5','h6','p','span'];
if (!in_array($heading_tag, $allowed_tags, true)) {
    $heading_tag = 'h2';
}

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

$section_id  = 'content-grid-' . uniqid();
$has_heading = !empty($heading);

$clamp = function ($val, $min, $max) {
    $v = (int) $val;
    if ($v < $min) $v = $min;
    if ($v > $max) $v = $max;
    return $v;
};

// Hover colour sequence (repeat every 5 items across the whole section)
$hover_colors = ['#D9F1FC', '#E0F4C5', '#FFE5CC', '#E0E0E0', '#D4D4D4'];

// Prebuild a flat list of item IDs + hover colours so we can output a single <style> block
$hover_map = [];
$hover_index = 0;
if (!empty($grid_rows) && is_array($grid_rows)) {
    foreach ($grid_rows as $row) {
        $items = $row['items'] ?? [];
        if (!is_array($items) || empty($items)) continue;

        foreach ($items as $_) {
            $id = $section_id . '-item-' . ($hover_index + 1);
            $hover_bg = $hover_colors[$hover_index % count($hover_colors)];
            $hover_map[] = ['id' => $id, 'hover' => $hover_bg];
            $hover_index++;
        }
    }
}
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
                    class="mb-6 text-[2.125rem] font-semibold tracking-normal leading-10 text-left font-secondary text-primary max-md:text-[2.125rem] max-md:leading-9 max-sm:leading-8"
                >
                    <?php echo esc_html($heading); ?>
                </<?php echo esc_attr($heading_tag); ?>>
                <div class="flex justify-between mr-auto items-left w-[71px] max-sm:w-[60px]" role="presentation" aria-hidden="true">
                    <div class="bg-orange-500 flex-1 h-[5px]"></div>
                    <div class="bg-sky-500 flex-1 h-[5px]"></div>
                    <div class="bg-[#B6C0CB] flex-1 h-[5px]"></div>
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

        <?php if (!empty($hover_map)): ?>
            <style>
                <?php foreach ($hover_map as $h): ?>
                    #<?php echo esc_html($h['id']); ?>:hover,
                    #<?php echo esc_html($h['id']); ?>:focus-within {
                        background-color: <?php echo esc_html($h['hover']); ?> !important;
                    }
                <?php endforeach; ?>
            </style>
        <?php endif; ?>

        <?php if (!empty($grid_rows) && is_array($grid_rows)): ?>
            <?php $render_index = 0; ?>
            <div class="flex flex-col gap-6 w-full">
                <?php foreach ($grid_rows as $row_index => $row): ?>
                    <?php
                    $items = $row['items'] ?? [];
                    $count = is_array($items) ? count($items) : 0;
                    if ($count < 1) continue;

                    // Default grid (your original logic)
                    $cols_md = $clamp(min($count, 3), 1, 12);
                    $cols_lg = $clamp($count, 1, 12);
                    $grid_classes = "grid grid-cols-1 md:grid-cols-{$cols_md} lg:grid-cols-{$cols_lg} gap-6 w-full";

                    // Special case: exactly 3 items => 2 cols @ md/lg with last item full-width; 3 cols again @ xl+
                    if ($count === 3) {
                        $grid_classes = "grid grid-cols-1 sm:grid-cols-1 md:grid-cols-2 lg:grid-cols-2 xl:grid-cols-3 gap-6 w-full";
                    }
                    ?>
                    <div class="<?php echo esc_attr($grid_classes); ?>">
                        <?php foreach ($items as $i => $item): ?>
                            <?php
                            $item_content = $item['content'] ?? '';
                            $item_bg      = $item['item_background_color'] ?? '#EDEDED';
                            $bar_color    = $item['bar_color'] ?? '#0098D8';

                            // Hover ID based on global render order (matches the <style> map)
                            $hover_id = $section_id . '-item-' . ($render_index + 1);

                            // Span rules: for 3-item rows, make the last item 100% @ md & lg; reset at xl
                            $span_classes = ($count === 3 && $i === 2)
                                ? 'md:col-span-2 lg:col-span-2 xl:col-span-1'
                                : '';
                            ?>
                            <article
                                id="<?php echo esc_attr($hover_id); ?>"
                                class="flex flex-col justify-center p-8 min-h-[8.75rem] max-md:px-5 <?php echo esc_attr($span_classes); ?>"
                                style="background-color: <?php echo esc_attr($item_bg); ?>;"
                            >
                                <?php if (!empty($item_content)): ?>
                                    <div class="text-center font-secondary font-semibold text-[1.5rem] leading-[1.625rem] tracking-[-0.01rem] text-[#0A1119] max-w-[20rem] w-full mx-auto">
                                        <?php echo wp_kses_post($item_content); ?>
                                    </div>
                                <?php endif; ?>

                                <div
                                    class="mx-auto mt-4 flex w-full max-w-[6.25rem] h-[8px] justify-center items-center"
                                    role="presentation"
                                    aria-hidden="true"
                                    style="background-color: <?php echo esc_attr($bar_color); ?>;"
                                ></div>
                            </article>
                            <?php $render_index++; ?>
                        <?php endforeach; ?>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

    </div>
</section>
