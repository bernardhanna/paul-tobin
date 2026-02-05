<?php
/**
 * Gallery 001 – Grid or Carousel (Slick) with modal
 * - Uses get_sub_field only
 * - Carousel: dots, infinite, autoplay, no modal on click
 * - Left overflow prevented; right side can peek
 */

$gallery_images   = get_sub_field('gallery_images');
$background_color = get_sub_field('background_color') ?: '#f9fafb';
$display_mode     = get_sub_field('display_mode') ?: 'grid'; // 'grid' or 'carousel'

// Padding controls
$padding_classes = ['pt-5', 'pb-5'];
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

$section_id = 'gallery-' . uniqid();
?>

<section
    id="<?php echo esc_attr($section_id); ?>"
    class="flex overflow-hidden relative"
    style="background-color: <?php echo esc_attr($background_color); ?>;"
    role="region"
    aria-label="Image Gallery"
    data-mode="<?php echo esc_attr($display_mode); ?>"
>
    <div class="flex flex-col items-center w-full mx-auto max-w-container lg:py-20 max-lg:px-5 <?php echo esc_attr(implode(' ', $padding_classes)); ?>">

        <?php if ($gallery_images && is_array($gallery_images)) : ?>

            <?php if ($display_mode === 'carousel') : ?>
                <!-- Carousel wrapper -->
                <div class="w-full">
                    <div class="gallery-main-slick">
                        <?php foreach ($gallery_images as $index => $image_data) :
                            $image_id    = $image_data['image'];
                            $image_alt   = get_post_meta($image_id, '_wp_attachment_image_alt', true) ?: 'Gallery image ' . ($index + 1);
                            $image_url   = wp_get_attachment_image_url($image_id, 'large');
                            $image_title = get_the_title($image_id) ?: 'Gallery image ' . ($index + 1);
                        ?>
                            <!-- right-only gutter so left side stays clean -->
                            <div class="pr-4">
                                <button
                                    type="button"
                                    class="gallery-trigger w-full h-[500px] max-md:h-[400px] max-sm:h-[300px] overflow-hidden relative flex flex-col items-start bg-[#EDEDED] rounded-lg focus:outline-none btn"
                                    data-gallery-index="<?php echo esc_attr($index); ?>"
                                    aria-label="<?php echo esc_attr($image_alt); ?>"
                                >
                                    <img
                                        src="<?php echo esc_url($image_url); ?>"
                                        alt="<?php echo esc_attr($image_alt); ?>"
                                        class="object-cover w-full h-full"
                                        loading="<?php echo $index < 2 ? 'eager' : 'lazy'; ?>"
                                    />
                                </button>
                            </div>
                        <?php endforeach; ?>
                    </div>

                    <!-- Scoped styles for this section -->
                    <style>
                        /* Hide left overflow, allow a right peek via padding-right */
                        #<?php echo esc_attr($section_id); ?> .gallery-main-slick .slick-list {
                            overflow: hidden !important;
                            padding-right: 1rem; /* right peek */
                            padding-left: 0;
                        }
                        #<?php echo esc_attr($section_id); ?> .gallery-main-slick .slick-track {
                            margin-left: 0 !important;
                        }

                        /* Dots like reference */
                        #<?php echo esc_attr($section_id); ?> .slick-dots {
                            display: flex !important;
                            justify-content: center;
                            gap: 0.75rem;
                            margin-top: 1.75rem;
                        }
                        #<?php echo esc_attr($section_id); ?> .slick-dots li { margin: 0; width: auto; height: auto; }
                        #<?php echo esc_attr($section_id); ?> .slick-dots button {
                            width: 10px; height: 10px; border-radius: 9999px;
                            background: #0a0a0a; opacity: .9; text-indent: -9999px;
                            overflow: hidden; padding: 0; border: 0;
                        }
                        #<?php echo esc_attr($section_id); ?> .slick-dots .slick-active button { background: #39a9dc; }
                    </style>
                </div>

<?php else : ?>
    <!-- GRID MODE: exact same wrapper + span rhythm as property grid -->
    <div class="w-full">
        <div class="grid grid-cols-1 gap-6 px-0 md:grid-cols-2 lg:grid-cols-10 lg:gap-12">
            <?php
            // span generator: identical rhythm to property grid
            $gallery_span = static function ($index) {
                $pair = intdiv($index, 2);
                $pos  = $index % 2;

                // md spans per pair
                switch ($pair % 6) {
                    case 0: $md = 'md:col-span-1'; break; // 0–1
                    case 1: $md = 'md:col-span-2'; break; // 2–3
                    case 2: $md = 'md:col-span-2'; break; // 4–5
                    case 3: $md = 'md:col-span-1'; break; // 6–7
                    case 4: $md = 'md:col-span-2'; break;
                    default:$md = 'md:col-span-2'; break;
                }

                // lg spans per pair (40/60 & 60/40 inside the middle pairs)
                switch ($pair % 4) {
                    case 0: $lg = 'lg:col-span-5'; break; // 0–1 (50/50)
                    case 1: $lg = ($pos === 0) ? 'lg:col-span-4' : 'lg:col-span-6'; break; // 2:4/6, 3:6/4
                    case 2: $lg = ($pos === 0) ? 'lg:col-span-6' : 'lg:col-span-4'; break; // 4:6/4, 5:4/6
                    default:$lg = 'lg:col-span-5'; break; // 6–7 (50/50)
                }

                return "col-span-1 {$md} {$lg}";
            };
            ?>

            <?php foreach ($gallery_images as $i => $image_data) :
                $image_id    = $image_data['image'];
                $image_url_l = $image_id ? wp_get_attachment_image_url($image_id, 'large') : '';
                $image_alt   = $image_id ? (get_post_meta($image_id, '_wp_attachment_image_alt', true) ?: 'Gallery image ' . ($i + 1)) : 'Gallery image';
                $image_title = $image_id ? (get_the_title($image_id) ?: 'Gallery image ' . ($i + 1)) : 'Gallery image';
                if (!$image_url_l) {
                    $image_url_l = 'https://via.placeholder.com/1200x800/e5e7eb/6b7280?text=Image';
                }
                $tile_classes = $gallery_span($i);
            ?>
                <div class="<?php echo esc_attr($tile_classes); ?>">
                    <!-- Using a button to open modal; styles mirror property card -->
                    <button
                        type="button"
                        class="block group w-full focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-[#0A1119] gallery-trigger"
                        data-gallery-index="<?php echo esc_attr($i); ?>"
                        aria-label="<?php echo esc_attr('Open ' . $image_alt . ' in gallery viewer'); ?>"
                    >
                        <div class="relative flex flex-col justify-end items-start overflow-hidden max-md:h-[18.75rem] h-[31.25rem]">
                            <!-- Background image -->
                            <div class="absolute inset-0 bg-center bg-cover" style="background-image: url('<?php echo esc_url($image_url_l); ?>');"></div>

                            <!-- Gradient overlay on hover/focus -->
                            <div
                                class="absolute inset-0 opacity-0 transition-opacity duration-300 pointer-events-none group-hover:opacity-100 group-focus:opacity-100"
                                style="background: linear-gradient(0deg, rgba(0, 152, 216, 0.25) 0%, rgba(0, 152, 216, 0.25) 100%);"
                                aria-hidden="true"
                            ></div>

                            <!-- 
                            <div class="relative p-3 px-6 m-4 bg-white sm:m-8 sm:p-4 sm:px-8">
                                <h3 class="font-secondary font-semibold text-xl sm:text-[32px] leading-tight sm:leading-[40px] tracking-[-0.16px] text-[#0A1119]">
                                    <?php echo esc_html($image_title); ?>
                                </h3>
                                <p class="font-primary text-sm sm:text-base leading-relaxed sm:leading-[26px] text-slate-600">
                                    <?php echo esc_html($image_alt); ?>
                                </p>
                            </div> Title card -->
                        </div>
                        <span class="sr-only">Image alt: <?php echo esc_html($image_alt); ?></span>
                    </button>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
<?php endif; ?>


            <!-- Modal Gallery -->
            <div
                id="gallery-modal-<?php echo esc_attr($section_id); ?>"
                class="hidden fixed inset-0 z-50 justify-center items-center p-4 bg-white bg-opacity-90 gallery-modal"
                role="dialog"
                aria-modal="true"
                aria-label="Gallery viewer"
                tabindex="-1"
            >
                <div class="overflow-hidden relative mx-auto w-full max-w-6xl">
                    <!-- Close Button -->
                    <button
                        type="button"
                        class="flex absolute top-4 right-4 z-10 justify-center items-center w-12 h-12 text-white bg-black rounded-full transition-all duration-300 gallery-close hover:bg-blue btn"
                        aria-label="Close gallery"
                    >
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>

                    <!-- Gallery Carousel (modal) -->
                    <div class="gallery-carousel">
                        <?php foreach ($gallery_images as $index => $image_data) :
                            $image_id    = $image_data['image'];
                            $image_alt   = get_post_meta($image_id, '_wp_attachment_image_alt', true) ?: 'Gallery image ' . ($index + 1);
                            $image_url   = wp_get_attachment_image_url($image_id, 'full');
                            $image_title = get_the_title($image_id) ?: 'Gallery image ' . ($index + 1);
                        ?>
                            <div class="gallery-slide">
                                <figure class="flex flex-col items-center">
                                    <img
                                        src="<?php echo esc_url($image_url); ?>"
                                        alt="<?php echo esc_attr($image_alt); ?>"
                                        class="max-w-full max-h-[80vh] object-cover w-full h-full"
                                        loading="lazy"
                                    />
                                    <?php if ($image_title) : ?>
                                        <figcaption class="mt-4 text-lg text-center text-white">
                                            <?php echo esc_html($image_title); ?>
                                        </figcaption>
                                    <?php endif; ?>
                                </figure>
                            </div>
                        <?php endforeach; ?>
                    </div>

                    <!-- Navigation Arrows -->
                    <button
                        type="button"
                        class="flex absolute left-4 top-1/2 justify-center items-center w-12 h-12 text-white bg-black rounded-full transition-all duration-300 transform -translate-y-1/2 gallery-prev hover:bg-blue btn"
                        aria-label="Previous image"
                    >
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
                        </svg>
                    </button>

                    <button
                        type="button"
                        class="flex absolute right-4 top-1/2 justify-center items-center w-12 h-12 text-white bg-black rounded-full transition-all duration-300 transform -translate-y-1/2 gallery-next hover:bg-blue btn"
                        aria-label="Next image"
                    >
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                        </svg>
                    </button>

                    <!-- Image Counter -->
                    <div class="absolute bottom-4 left-1/2 px-4 py-2 text-black rounded-full transform -translate-x-1/2 bg-blue">
                        <span class="gallery-counter" aria-live="polite">1 / <?php echo count($gallery_images); ?></span>
                    </div>
                </div>
            </div>

            <script>
            document.addEventListener('DOMContentLoaded', function() {
                const sectionId = '<?php echo esc_js($section_id); ?>';
                const root      = document.getElementById(sectionId);
                const mode      = root.getAttribute('data-mode');
                const modal     = document.getElementById('gallery-modal-' + sectionId);
                const closeBtn  = modal.querySelector('.gallery-close');
                const prevBtn   = modal.querySelector('.gallery-prev');
                const nextBtn   = modal.querySelector('.gallery-next');
                const counter   = modal.querySelector('.gallery-counter');
                const carousel  = modal.querySelector('.gallery-carousel');
                const slides    = modal.querySelectorAll('.gallery-slide');

                // Init main carousel (if mode = carousel)
                if (mode === 'carousel' && typeof jQuery !== 'undefined' && jQuery.fn.slick) {
                    const $main = jQuery('#' + sectionId + ' .gallery-main-slick');
                    if ($main.length) {
                        $main.slick({
                            dots: true,
                            arrows: false,
                            infinite: true,
                            autoplay: true,
                            autoplaySpeed: 3000,
                            pauseOnHover: true,
                            speed: 400,
                            slidesToShow: 2,
                            slidesToScroll: 1,
                            responsive: [
                                { breakpoint: 1024, settings: { slidesToShow: 2 } },
                                { breakpoint: 768,  settings: { slidesToShow: 1 } }
                            ],
                            accessibility: true
                        });
                    }
                }

                // Triggers after possible slick init
                const triggers = root.querySelectorAll('.gallery-trigger');

                let currentSlide = 0;
                let focusedElementBeforeModal = null;

                function ensureSlickInitModal() {
                    if (typeof jQuery === 'undefined' || !jQuery.fn.slick) return false;
                    const $carousel = jQuery(carousel);
                    if (!$carousel.data('slick-initialized')) {
                        $carousel.slick({
                            dots: false,
                            arrows: false,
                            infinite: true,
                            speed: 300,
                            slidesToShow: 1,
                            adaptiveHeight: true,
                            accessibility: true
                        });
                        $carousel.on('afterChange', function(event, slick, currentIndex) {
                            currentSlide = currentIndex;
                            updateCounter();
                        });
                        $carousel.data('slick-initialized', true);
                    }
                    return true;
                }

                function openModal(slideIndex = 0) {
                    focusedElementBeforeModal = document.activeElement;
                    currentSlide = slideIndex;

                    modal.classList.remove('hidden');
                    modal.classList.add('flex');

                    if (ensureSlickInitModal()) {
                        jQuery(carousel).slick('slickGoTo', slideIndex, true);
                        jQuery(carousel).slick('setPosition');
                    } else {
                        showSlide(slideIndex);
                    }

                    updateCounter();
                    modal.focus();
                    document.body.style.overflow = 'hidden';
                    trapFocus(modal);
                }

                function closeModal() {
                    modal.classList.add('hidden');
                    modal.classList.remove('flex');
                    document.body.style.overflow = '';
                    if (focusedElementBeforeModal) focusedElementBeforeModal.focus();
                }

                function showSlide(index) {
                    slides.forEach((slide, i) => {
                        slide.style.display = i === index ? 'block' : 'none';
                    });
                }

                function updateCounter() {
                    counter.textContent = (currentSlide + 1) + ' / ' + slides.length;
                }

                function nextSlide() {
                    if (typeof jQuery !== 'undefined' && jQuery.fn.slick && jQuery(carousel).data('slick-initialized')) {
                        jQuery(carousel).slick('slickNext');
                    } else {
                        currentSlide = (currentSlide + 1) % slides.length;
                        showSlide(currentSlide);
                        updateCounter();
                    }
                }

                function prevSlide() {
                    if (typeof jQuery !== 'undefined' && jQuery.fn.slick && jQuery(carousel).data('slick-initialized')) {
                        jQuery(carousel).slick('slickPrev');
                    } else {
                        currentSlide = (currentSlide - 1 + slides.length) % slides.length;
                        showSlide(currentSlide);
                        updateCounter();
                    }
                }

                function trapFocus(element) {
                    const focusables = element.querySelectorAll(
                        'button, [href], input, select, textarea, [tabindex]:not([tabindex="-1"])'
                    );
                    const first = focusables[0];
                    const last  = focusables[focusables.length - 1];

                    element.addEventListener('keydown', function(e) {
                        if (e.key === 'Tab') {
                            if (e.shiftKey) {
                                if (document.activeElement === first) { last.focus(); e.preventDefault(); }
                            } else {
                                if (document.activeElement === last) { first.focus(); e.preventDefault(); }
                            }
                        }
                    });
                }

                // Events
                triggers.forEach((trigger, index) => {
                    // Only open modal in GRID mode
                    if (mode !== 'carousel') {
                        trigger.addEventListener('click', () => openModal(index));
                        trigger.addEventListener('keydown', (e) => {
                            if (e.key === 'Enter' || e.key === ' ') {
                                e.preventDefault(); openModal(index);
                            }
                        });
                    }
                });

                modal.addEventListener('keydown', function(e) {
                    switch(e.key) {
                        case 'Escape':     closeModal(); break;
                        case 'ArrowLeft':  prevSlide();  break;
                        case 'ArrowRight': nextSlide();  break;
                    }
                });

                modal.addEventListener('click', function(e) {
                    if (e.target === modal) closeModal();
                });

                closeBtn.addEventListener('click', closeModal);
                prevBtn.addEventListener('click', prevSlide);
                nextBtn.addEventListener('click', nextSlide);

                // Fallback for non-Slick environments
                if (typeof jQuery === 'undefined' || !jQuery.fn.slick) {
                    showSlide(0);
                }
            });
            </script>

        <?php else : ?>
            <div class="py-12 text-center">
                <p class="text-lg text-gray-500">No gallery images available.</p>
            </div>
        <?php endif; ?>
    </div>
</section>
