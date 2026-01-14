<?php
get_header();
?>
  
<main class="overflow-hidden mx-auto w-full">
    <?php
    if (function_exists('load_hero_templates')) {
        load_hero_templates();
    }
    ?>
      <!-- Hero Section -->
      <section class="relative w-full">
        <img
          src="https://api.builder.io/api/v1/image/assets/TEMP/7b6998a281be8ddd33d9f526fb5abf04e11251ec?width=2560"
          alt="Property exterior view"
          class="w-full h-[500px] md:h-[665px] object-cover"
        />
      </section>

      <!-- Property Data Section -->
      <section class="flex flex-col w-full lg:flex-row bg-pt-icewhite">
        <div class="flex py-20 mx-auto w-full max-w-container max-lg:py-10 bg-pt-high-emphasis">
          <div class="flex w-full max-w-[400px] p-4 gap-4 bg-pt-medium-emphasis">
            <!-- Decorative bar -->
            <div class="flex-shrink-0 w-1 bg-pt-blue"></div>

            <div class="flex flex-col gap-3">
              <div class="flex flex-col gap-4 sm:flex-row">
                <div class="w-full sm:w-[100px] flex-shrink-0">
                  <h3 class="font-serif font-semibold text-2xl leading-[26px] text-pt-midnight">Sector</h3>
                </div>
                <div class="flex-1">
                  <p class="text-base leading-[26px] text-pt-midnight">Residential</p>
                </div>
              </div>

              <div class="flex flex-col gap-4 sm:flex-row">
                <div class="w-full sm:w-[100px] flex-shrink-0">
                  <h3 class="font-serif font-semibold text-2xl leading-[26px] text-pt-midnight">Year</h3>
                </div>
                <div class="flex-1">
                  <p class="text-base leading-[26px] text-pt-midnight">2016</p>
                </div>
              </div>

              <div class="flex flex-col gap-4 sm:flex-row">
                <div class="w-full sm:w-[100px] flex-shrink-0">
                  <h3 class="font-serif font-semibold text-2xl leading-[26px] text-pt-midnight">Client</h3>
                </div>
                <div class="flex-1">
                  <p class="text-base leading-[26px] text-pt-midnight">Client name</p>
                </div>
              </div>

              <div class="flex flex-col gap-4 sm:flex-row">
                <div class="w-full sm:w-[100px] flex-shrink-0">
                  <h3 class="font-serif font-semibold text-2xl leading-[26px] text-pt-midnight">Size</h3>
                </div>
                <div class="flex-1">
                  <p class="text-base leading-[26px] text-pt-midnight">
                    Area: 2500 square feet<br />
                    Height: 143 meters<br />
                    Stories: 42
                  </p>
                </div>
              </div>
            </div>
          </div>
        </div>

        <div class="flex flex-1 p-10 md:p-20 bg-pt-icewhite">
          <div class="flex flex-col gap-6">
            <p class="text-base leading-[26px] text-black">
              Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore
              magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo
              consequat.
            </p>
            <p class="text-base leading-[26px] text-black">
              Lorem ipsum dolor sit amet, consectetur adipiscing elit ullamco laboris nisi ut aliquip adipiscing elit .
            </p>
            <a href="#" class="text-base leading-[26px] text-pt-midnight underline max-w-fit">Read more</a>
          </div>
        </div>
      </section>

      <!-- Full Width Image -->
      <section class="flex flex-col gap-12 py-10 md:py-20 bg-pt-icewhite">
        <div class="">
          <div class="w-full h-[400px] md:h-[500px] overflow-hidden mx-auto max-w-container">
            <img
              src="https://api.builder.io/api/v1/image/assets/TEMP/fd1096fcc4ce55a2a58f694f8233314d9beab554?width=2240"
              alt="Property detail"
              class="object-cover w-full h-full"
            />
          </div>
        </div>
      </section>

      <!-- Property Text Section -->
      <section class="flex flex-col w-full lg:flex-row bg-pt-icewhite">
        <div class="flex px-5 py-20 mx-auto w-full max-w-container max-lg:py-10 bg-pt-high-emphasis">
          <div class="flex flex-col flex-1 gap-6">
            <div class="flex flex-col gap-6">
              <h2 class="font-serif font-semibold text-[32px] leading-10 text-pt-midnight">
                Lorem ipsum dolor sit amet lorem consectetur sed.
              </h2>
              <div class="flex w-[71px] h-[5px]">
                <div class="flex-1 bg-pt-orange"></div>
                <div class="flex-1 bg-pt-blue"></div>
                <div class="flex-1 bg-pt-gray-blue"></div>
                <div class="flex-1 bg-pt-green"></div>
              </div>
            </div>
            <p class="text-base leading-[26px] text-black">
              Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore
              magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo
              consequat.
            </p>
          </div>
        </div>
      </section>

      <!-- Image Gallery - Two Images -->
      <section class="flex flex-col bg-pt-icewhite">
        <div class="flex flex-col gap-12 px-5 py-20 mx-auto w-full max-w-container max-lg:py-10 md:flex-row">
          <div class="flex-1 h-[400px] md:h-[500px] overflow-hidden">
            <img
              src="https://api.builder.io/api/v1/image/assets/TEMP/07d9368de3b92eeb6a65bfdc3f423a90baee32d3?width=1072"
              alt="Property interior"
              class="object-cover w-full h-full"
            />
          </div>
          <div class="flex-1 h-[400px] md:h-[500px] overflow-hidden">
            <img
              src="https://api.builder.io/api/v1/image/assets/TEMP/16854ee8243aac420ef74f6113047e5162c4af21?width=1072"
              alt="Property detail"
              class="object-cover w-full h-full"
            />
          </div>
        </div>

        <div class="flex flex-col gap-12 px-10 md:px-20 md:flex-row">
          <div class="w-full md:w-[400px] h-[400px] md:h-[500px] overflow-hidden">
            <img
              src="https://api.builder.io/api/v1/image/assets/TEMP/677c6b1c6342c8403a88e2bd1d95152451ac2510?width=800"
              alt="Property room"
              class="object-cover w-full h-full"
            />
          </div>
          <div class="flex-1 h-[400px] md:h-[500px] overflow-hidden">
            <img
              src="https://api.builder.io/api/v1/image/assets/TEMP/ff2df95a026a8be1620d7a3ce953af2e35d4db75?width=1344"
              alt="Property interior detail"
              class="object-cover w-full h-full"
            />
          </div>
        </div>
      </section>

      <!-- Property Text Section 2 -->
      <section class="flex flex-col w-full lg:flex-row bg-pt-icewhite">
        <div class="flex mx-auto w-full max-xl:px-5 max-w-container bg-pt-high-emphasis">
          <div class="flex flex-col flex-1 gap-6">
            <div class="flex flex-col gap-6">
              <h2 class="font-serif font-semibold text-[32px] leading-10 text-pt-midnight">
                Lorem ipsum dolor sit amet lorem consectetur sed.
              </h2>
              <div class="flex w-[71px] h-[5px]">
                <div class="flex-1 bg-pt-orange"></div>
                <div class="flex-1 bg-pt-blue"></div>
                <div class="flex-1 bg-pt-gray-blue"></div>
                <div class="flex-1 bg-pt-green"></div>
              </div>
            </div>
            <p class="text-base leading-[26px] text-black">
              Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore
              magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo
              consequat.
            </p>
          </div>
        </div>
      </section>

      <!-- Carousel Section (horizontal scroll) -->
      <section class="flex flex-col gap-12 py-10 md:py-20 bg-pt-icewhite">
        <div class="flex overflow-x-auto gap-12 pl-10 md:pl-20 snap-x snap-mandatory scrollbar-hide">
          <img
            src="https://api.builder.io/api/v1/image/assets/TEMP/405fbc584138b4147b3dd5092d9cbebed3d49a30?width=1070"
            alt="Property view 1"
            class="w-[400px] md:w-[535px] h-[400px] md:h-[500px] object-cover flex-shrink-0 snap-center"
          />
          <img
            src="https://api.builder.io/api/v1/image/assets/TEMP/9d813ccd6eb6efb2fe8d9ad98f136df2b9d18f51?width=1070"
            alt="Property view 2"
            class="w-[400px] md:w-[535px] h-[400px] md:h-[500px] object-cover flex-shrink-0 snap-center"
          />
          <img
            src="https://api.builder.io/api/v1/image/assets/TEMP/b1b837fb70e3b3979412a119fd98279ad2abb198?width=1070"
            alt="Property view 3"
            class="w-[400px] md:w-[535px] h-[400px] md:h-[500px] object-cover flex-shrink-0 snap-center"
          />
          <img
            src="https://api.builder.io/api/v1/image/assets/TEMP/f3800729aba32eda0b9961a1d50b331240599a5d?width=1070"
            alt="Property view 4"
            class="w-[400px] md:w-[535px] h-[400px] md:h-[500px] object-cover flex-shrink-0 snap-center"
          />
        </div>

        <!-- Indicators -->
        <div class="flex gap-3 justify-center items-center">
          <div class="w-4 h-4 rounded-full bg-pt-midnight"></div>
          <div class="w-4 h-4 rounded-full bg-[#40BFF5]"></div>
          <div class="w-4 h-4 rounded-full bg-pt-midnight"></div>
          <div class="w-4 h-4 rounded-full bg-pt-midnight"></div>
          <div class="w-4 h-4 rounded-full bg-pt-midnight"></div>
        </div>
      </section>

      <!-- Video Section -->
      <section class="flex flex-col gap-12 py-10 md:py-20 bg-pt-high-emphasis">
        <div class="px-10 md:px-20">
          <div class="relative w-full h-[400px] md:h-[500px] overflow-hidden">
            <div
              class="flex absolute inset-0 justify-center items-center bg-center bg-cover"
              style="
                background-image: url('https://api.builder.io/api/v1/image/assets/TEMP/cba2630e447ba1572b90361be649b8684a083a62?width=2240');
              "
            >
              <!-- Play button (static SVG, replaces lucide-react) -->
              <button
                type="button"
                aria-label="Play video"
                class="flex justify-center items-center w-16 h-16 bg-transparent rounded-full border-4 transition-colors border-pt-icewhite hover:bg-pt-icewhite/10 group"
              >
                <svg
                  class="ml-1 w-8 h-8 fill-current text-pt-icewhite"
                  viewBox="0 0 24 24"
                  aria-hidden="true"
                >
                  <path d="M8 5v14l11-7z"></path>
                </svg>
              </button>
            </div>
          </div>
        </div>
      </section>

      <!-- Related Projects -->
      <section class="flex flex-col gap-12 py-10 md:py-20 bg-pt-icewhite">
        <div class="flex flex-col gap-12 px-10 md:px-20">
          <div class="flex flex-col gap-6 items-center">
            <h2 class="font-serif font-semibold text-[32px] leading-10 text-pt-midnight text-center">Related projects</h2>
            <div class="flex w-[71px] h-[5px]">
              <div class="flex-1 bg-pt-orange"></div>
              <div class="flex-1 bg-pt-blue"></div>
              <div class="flex-1 bg-pt-gray-blue"></div>
              <div class="flex-1 bg-pt-green"></div>
            </div>
          </div>

          <div class="grid grid-cols-1 gap-12 md:grid-cols-2 lg:grid-cols-3">
            <!-- Card 1 -->
            <a href="#" class="group">
              <div class="relative h-[318px] overflow-hidden">
                <div
                  class="absolute inset-0 bg-center bg-cover transition-transform duration-300 group-hover:scale-105"
                  style="
                    background-image: url('https://api.builder.io/api/v1/image/assets/TEMP/9d172ed5ff242321873631225618b7d7c30cd6a8?width=683');
                  "
                >
                  <div class="absolute right-0 bottom-0 left-0 p-8">
                    <div class="p-4 bg-pt-high-emphasis md:px-8 md:py-4">
                      <h3 class="font-serif font-semibold text-[32px] leading-10 text-pt-midnight">House name</h3>
                      <p class="text-base leading-[26px] text-pt-text-subtle">Residential</p>
                    </div>
                  </div>
                </div>
              </div>
            </a>

            <!-- Card 2 -->
            <a href="#" class="group">
              <div class="relative h-[318px] overflow-hidden">
                <div
                  class="absolute inset-0 bg-center bg-cover transition-transform duration-300 group-hover:scale-105"
                  style="
                    background-image: url('https://api.builder.io/api/v1/image/assets/TEMP/d7f12a996dba9774bb242b7fe978938816f7072f?width=683');
                  "
                >
                  <div class="absolute right-0 bottom-0 left-0 p-8">
                    <div class="p-4 bg-pt-high-emphasis md:px-8 md:py-4">
                      <h3 class="font-serif font-semibold text-[32px] leading-10 text-pt-midnight">House name</h3>
                      <p class="text-base leading-[26px] text-pt-text-subtle">Residential</p>
                    </div>
                  </div>
                </div>
              </div>
            </a>

            <!-- Card 3 -->
            <a href="#" class="group">
              <div class="relative h-[318px] overflow-hidden">
                <div
                  class="absolute inset-0 bg-center bg-cover transition-transform duration-300 group-hover:scale-105"
                  style="
                    background-image: url('https://api.builder.io/api/v1/image/assets/TEMP/d7f12a996dba9774bb242b7fe978938816f7072f?width=683');
                  "
                >
                  <div class="absolute right-0 bottom-0 left-0 p-8">
                    <div class="p-4 bg-pt-high-emphasis md:px-8 md:py-4">
                      <h3 class="font-serif font-semibold text-[32px] leading-10 text-pt-midnight">House name</h3>
                      <p class="text-base leading-[26px] text-pt-text-subtle">Residential</p>
                    </div>
                  </div>
                </div>
              </div>
            </a>
          </div>
        </div>
      </section>

      <!-- Get in Touch CTA -->
      <section class="flex w-full bg-pt-midnight">
        <div class="flex flex-1 justify-center items-center p-10 md:p-20">
          <div class="flex flex-col lg:flex-row items-center gap-16 max-w-[911px]">
            <div class="flex flex-col flex-1 gap-6">
              <h2 class="font-serif font-semibold text-[32px] leading-10 text-pt-icewhite">
                Why don't find out what we can do for you?
              </h2>
              <div class="flex w-[71px] h-[5px]">
                <div class="flex-1 bg-pt-orange"></div>
                <div class="flex-1 bg-pt-blue"></div>
                <div class="flex-1 bg-pt-gray-blue"></div>
                <div class="flex-1 bg-pt-green"></div>
              </div>
              <p class="text-base leading-[26px] text-pt-icewhite">
                From refurbishment and tenant sourcing to management, rent collection, sales, and tax agent services, all handled
                for you.
              </p>
            </div>

            <div class="flex-1 lg:max-w-[300px] w-full">
              <a
                href="/contact"
                class="flex h-11 px-8 justify-center items-center bg-pt-icewhite border-4 border-pt-midnight text-pt-midnight font-semibold text-sm leading-[22px] hover:bg-pt-high-emphasis transition-colors w-full"
              >
                Get in touch
              </a>
            </div>
          </div>
        </div>
      </section>


  <?php
    if (have_posts()) :
        while (have_posts()) : the_post();
            if (trim(get_the_content()) != '') : ?>
                <div class="max-w-[1095px] max-xl:px-5  mx-auto">
                    <?php
                    get_template_part('template-parts/content/content', 'page');
                    ?>
                </div>
    <?php endif;
        endwhile;
    else :
        echo '<p>No content found</p>';
    endif;
    ?>
     <?php load_flexible_content_templates(); ?>
  </main>
<?php
get_footer();
?>