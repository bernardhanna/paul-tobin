<?php
$section_id       = 'counters_' . uniqid();
$heading          = get_sub_field('heading');
$heading_tag      = get_sub_field('heading_tag');
$background_color = get_sub_field('background_color');
$border_color     = get_sub_field('border_color');

$padding_classes = [];
if (have_rows('padding_settings')) {
    while (have_rows('padding_settings')) {
        the_row();
        $screen_size   = get_sub_field('screen_size');
        $padding_top   = get_sub_field('padding_top');
        $padding_bottom= get_sub_field('padding_bottom');
        if ($screen_size !== '' && $padding_top !== '' && $padding_bottom !== '') {
            $padding_classes[] = "{$screen_size}:pt-[{$padding_top}rem]";
            $padding_classes[] = "{$screen_size}:pb-[{$padding_bottom}rem]";
        }
    }
}

$counters = [];
if (have_rows('counters')) {
    while (have_rows('counters')) {
        the_row();
        $counters[] = [
            'number'       => get_sub_field('number'),
            'suffix'       => get_sub_field('suffix'),
            'title'        => get_sub_field('title'),
            'description'  => get_sub_field('description'),
            'item_color'   => get_sub_field('item_border_color'), // per-item override
        ];
    }
}
?>

<section
    id="<?php echo esc_attr($section_id); ?>"
    class="flex overflow-hidden relative pb-9"
    style="background-color: <?php echo esc_attr($background_color); ?>;"
    x-data="countersSection()"
    x-intersect.once="startCounters()"
>
    <div class="flex flex-col items-center w-full mx-auto max-w-container max-lg:px-5 py-20 <?php echo esc_attr(implode(' ', $padding_classes)); ?>">

        <header class="flex flex-col gap-6 items-center self-stretch">
            <?php if (!empty($heading)): ?>
                <div class="flex flex-col gap-6 items-center self-stretch">
                    <<?php echo esc_attr($heading_tag); ?> class="text-3xl font-semibold tracking-normal leading-10 text-center font-secondary text-primary max-md:text-3xl max-md:leading-9 max-sm:text-2xl max-sm:leading-8">
                        <?php echo esc_html($heading); ?>
                    </<?php echo esc_attr($heading_tag); ?>>

                    <div
                        class="flex gap-0.5 justify-between items-start w-[71px] max-sm:w-[60px]"
                        role="presentation"
                        aria-hidden="true"
                    >
                      <div class="bg-orange-500 flex-1 h-[5px]"></div>
                      <div class="bg-sky-500 flex-1 h-[5px]"></div>
                      <div class="bg-slate-300 flex-1 h-[5px]"></div>
                      <div class="bg-lime-600 flex-1 h-[5px]"></div>
                    </div>
                </div>
            <?php endif; ?>
        </header>

        <?php if (!empty($counters)): ?>
            <div class="grid grid-cols-1 gap-8 items-start self-stretch mt-12 md:grid-cols-3 max-md:gap-10 max-sm:gap-8 max-md:mt-10 max-sm:mt-8">
                <?php foreach ($counters as $index => $counter): ?>
                    <?php
                        $this_border = !empty($counter['item_color']) ? $counter['item_color'] : $border_color;
                        $safe_suffix = isset($counter['suffix']) ? $counter['suffix'] : '';
                        $safe_number = isset($counter['number']) ? intval($counter['number']) : 0;
                    ?>
                    <article class="flex flex-col gap-4 items-center">
                        <div class="flex flex-col gap-4 justify-center items-center self-stretch">
                            <div class="flex justify-center items-center pt-4 border-b-8 border-solid" style="border-color: <?php echo esc_attr($this_border); ?>;">
                                <div
                                    class="text-7xl font-bold tracking-normal text-center leading-[92px] text-primary max-md:text-6xl max-md:leading-[70px] max-sm:text-5xl max-sm:leading-[56px]"
                                    x-text="displayNumbers[<?php echo (int)$index; ?>] + '<?php echo esc_js($safe_suffix); ?>'"
                                    aria-live="polite"
                                    aria-label="<?php echo esc_attr('Counter showing ' . $safe_number . $safe_suffix); ?>"
                                >
                                    <?php echo esc_html('0' . $safe_suffix); ?>
                                </div>
                            </div>

                            <?php if (!empty($counter['title'])): ?>
                                <h3 class="self-stretch text-2xl font-semibold tracking-normal leading-7 text-center font-secondary text-primary max-md:text-xl max-md:leading-6 max-sm:text-lg max-sm:leading-6">
                                    <?php echo esc_html($counter['title']); ?>
                                </h3>
                            <?php endif; ?>

                            <?php if (!empty($counter['description'])): ?>
                                <div class="self-stretch text-base tracking-normal leading-7 text-center text-black max-sm:text-sm max-sm:leading-6 wp_editor">
                                    <?php echo wp_kses_post($counter['description']); ?>
                                </div>
                            <?php endif; ?>
                        </div>
                    </article>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</section>

<script>
function countersSection() {
    return {
        displayNumbers: [0, 0, 0],
        targetNumbers: [
            <?php echo !empty($counters[0]) ? intval($counters[0]['number']) : 0; ?>,
            <?php echo !empty($counters[1]) ? intval($counters[1]['number']) : 0; ?>,
            <?php echo !empty($counters[2]) ? intval($counters[2]['number']) : 0; ?>
        ],
        animationDuration: 2000,

        startCounters() {
            this.targetNumbers.forEach((target, index) => {
                if (target > 0) {
                    this.animateCounter(index, target);
                }
            });
        },

        animateCounter(index, target) {
            const startTime = Date.now();
            const startValue = 0;

            const animate = () => {
                const elapsed = Date.now() - startTime;
                const progress = Math.min(elapsed / this.animationDuration, 1);
                const easeOutQuart = 1 - Math.pow(1 - progress, 4);

                this.displayNumbers[index] = Math.floor(startValue + (target - startValue) * easeOutQuart);

                if (progress < 1) {
                    requestAnimationFrame(animate);
                } else {
                    this.displayNumbers[index] = target;
                }
            };

            requestAnimationFrame(animate);
        }
    }
}
</script>
