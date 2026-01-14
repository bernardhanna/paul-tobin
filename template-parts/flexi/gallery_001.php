<?php
$gallery_images = get_sub_field('gallery_images');
$background_color = get_sub_field('background_color') ?: '#f9fafb';

$padding_classes = ['pt-5', 'pb-5'];
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

$section_id = 'gallery-' . uniqid();
?>

<section
    id="<?php echo esc_attr($section_id); ?>"
    class="flex overflow-hidden relative"
    style="background-color: <?php echo esc_attr($background_color); ?>;"
    role="region"
    aria-label="Image Gallery"
>
    <div class="flex flex-col items-center w-full mx-auto max-w-container lg:py-20 max-lg:px-5 <?php echo esc_attr(implode(' ', $padding_classes)); ?>">

        <?php if ($gallery_images && is_array($gallery_images)): ?>
            <div class="grid grid-cols-1 gap-12 w-full md:grid-cols-2 max-md:gap-8 max-sm:gap-6">
                <?php foreach ($gallery_images as $index => $image_data):
                    $image_id = $image_data['image'];
                    $image_alt = get_post_meta($image_id, '_wp_attachment_image_alt', true) ?: 'Gallery image ' . ($index + 1);
                    $image_url = wp_get_attachment_image_url($image_id, 'full');
                    $image_title = get_the_title($image_id) ?: 'Gallery image ' . ($index + 1);
                ?>
                    <article class="gallery-item">
                        <button
                            type="button"
                            class="gallery-trigger w-full h-[500px] max-md:h-[400px] max-sm:h-[300px] overflow-hidden relative flex flex-col items-start bg-gray-100 rounded-lg transition-transform duration-300 hover:scale-105 focus:scale-105 btn"
                            data-gallery-index="<?php echo esc_attr($index); ?>"
                            aria-label="Open <?php echo esc_attr($image_alt); ?> in gallery viewer"
                            role="button"
                            tabindex="0"
                        >
                            <?php echo wp_get_attachment_image($image_id, 'large', false, [
                                'alt' => esc_attr($image_alt),
                                'class' => 'w-full h-full object-cover',
                                'loading' => $index < 2 ? 'eager' : 'lazy'
                            ]); ?>

                            <div class="flex absolute inset-0 justify-center items-center bg-black bg-opacity-0 transition-all duration-300 hover:bg-opacity-10">
                                <svg
                                    class="w-12 h-12 text-white opacity-0 transition-opacity duration-300 hover:opacity-100"
                                    fill="none"
                                    stroke="currentColor"
                                    viewBox="0 0 24 24"
                                    aria-hidden="true"
                                >
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0zM10 7v3m0 0v3m0-3h3m-3 0H7"></path>
                                </svg>
                            </div>
                        </button>
                    </article>
                <?php endforeach; ?>
            </div>

            <!-- Modal Gallery -->
            <div
                id="gallery-modal-<?php echo esc_attr($section_id); ?>"
                class="hidden fixed inset-0 z-50 justify-center items-center p-4 bg-black bg-opacity-90 gallery-modal"
                role="dialog"
                aria-modal="true"
                aria-label="Gallery viewer"
                tabindex="-1"
            >
                <div class="relative mx-auto w-full max-w-6xl">
                    <!-- Close Button -->
                    <button
                        type="button"
                        class="flex absolute top-4 right-4 z-10 justify-center items-center w-12 h-12 text-white bg-black bg-opacity-50 rounded-full transition-all duration-300 gallery-close hover:bg-opacity-75 btn"
                        aria-label="Close gallery"
                    >
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>

                    <!-- Gallery Carousel -->
                    <div class="gallery-carousel">
                        <?php foreach ($gallery_images as $index => $image_data):
                            $image_id = $image_data['image'];
                            $image_alt = get_post_meta($image_id, '_wp_attachment_image_alt', true) ?: 'Gallery image ' . ($index + 1);
                            $image_url = wp_get_attachment_image_url($image_id, 'full');
                            $image_title = get_the_title($image_id) ?: 'Gallery image ' . ($index + 1);
                        ?>
                            <div class="gallery-slide">
                                <figure class="flex flex-col items-center">
                                    <img
                                        src="<?php echo esc_url($image_url); ?>"
                                        alt="<?php echo esc_attr($image_alt); ?>"
                                        class="max-w-full max-h-[80vh] object-contain"
                                        loading="lazy"
                                    />
                                    <?php if ($image_title): ?>
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
                        class="flex absolute left-4 top-1/2 justify-center items-center w-12 h-12 text-white bg-black bg-opacity-50 rounded-full transition-all duration-300 transform -translate-y-1/2 gallery-prev hover:bg-opacity-75 btn"
                        aria-label="Previous image"
                    >
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
                        </svg>
                    </button>

                    <button
                        type="button"
                        class="flex absolute right-4 top-1/2 justify-center items-center w-12 h-12 text-white bg-black bg-opacity-50 rounded-full transition-all duration-300 transform -translate-y-1/2 gallery-next hover:bg-opacity-75 btn"
                        aria-label="Next image"
                    >
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                        </svg>
                    </button>

                    <!-- Image Counter -->
                    <div class="absolute bottom-4 left-1/2 px-4 py-2 text-white bg-black bg-opacity-50 rounded-full transform -translate-x-1/2">
                        <span class="gallery-counter" aria-live="polite">1 / <?php echo count($gallery_images); ?></span>
                    </div>
                </div>
            </div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const sectionId = '<?php echo esc_js($section_id); ?>';
    const root      = document.getElementById(sectionId);
    const modal     = document.getElementById('gallery-modal-' + sectionId);
    const triggers  = root.querySelectorAll('.gallery-trigger');
    const closeBtn  = modal.querySelector('.gallery-close');
    const prevBtn   = modal.querySelector('.gallery-prev');
    const nextBtn   = modal.querySelector('.gallery-next');
    const counter   = modal.querySelector('.gallery-counter');
    const carousel  = modal.querySelector('.gallery-carousel');
    const slides    = modal.querySelectorAll('.gallery-slide');

    let currentSlide = 0;
    let focusedElementBeforeModal = null;

    function ensureSlickInit() {
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
                accessibility: true,
                focusOnSelect: false
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

        // Init slick only now (visible) to avoid calc issues
        if (ensureSlickInit()) {
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
        counter.textContent = `${currentSlide + 1} / ${slides.length}`;
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
                    if (document.activeElement === first) {
                        last.focus(); e.preventDefault();
                    }
                } else {
                    if (document.activeElement === last) {
                        first.focus(); e.preventDefault();
                    }
                }
            }
        });
    }

    // Events
    triggers.forEach((trigger, index) => {
        trigger.addEventListener('click', () => openModal(index));
        trigger.addEventListener('keydown', (e) => {
            if (e.key === 'Enter' || e.key === ' ') {
                e.preventDefault(); openModal(index);
            }
        });
    });

    closeBtn.addEventListener('click', closeModal);
    prevBtn.addEventListener('click', prevSlide);
    nextBtn.addEventListener('click', nextSlide);

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

    // Fallback initial render for non-Slick
    if (typeof jQuery === 'undefined' || !jQuery.fn.slick) {
        showSlide(0);
    }
});
</script>


        <?php else: ?>
            <div class="py-12 text-center">
                <p class="text-lg text-gray-500">No gallery images available.</p>
            </div>
        <?php endif; ?>
    </div>
</section>
