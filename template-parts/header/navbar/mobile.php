<?php
// Import Navi if not already imported
use Log1x\Navi\Navi;

// Build menus if not already provided
if (!isset($primary_navigation)) {
  $primary_navigation = Navi::make()->build('primary');
}
if (!isset($secondary_navigation)) {
  $secondary_navigation = Navi::make()->build('secondary');
}

// Options
$enable_hamburger     = get_field('enable_hamburger', 'option');
$hamburger_style      = get_field('hamburger_style', 'option');
$mobile_menu_effect   = get_field('mobile_menu_effect', 'option') ?: 'slide_up';
$mobile_menu_width    = (int) (get_field('mobile_menu_width', 'option') ?: 100);
$mobile_menu_bg       = get_field('mobile_menu_background', 'option') ?: '#FFFFFF';
$sticky_menu          = get_field('sticky_menu', 'option');

// Transition mapping for panel
$effect_classes = [
  'slide_up'    => 'translate-y-full',
  'slide_left'  => '-translate-x-full',
  'slide_right' => 'translate-x-full',
  'fullscreen'  => 'translate-y-full',
];
$transition_class = $effect_classes[$mobile_menu_effect] ?? 'translate-y-full';

// Validate hamburger style
$valid_styles = [
  'hamburger--3dx','hamburger--3dx-r','hamburger--3dy','hamburger--3dy-r','hamburger--3dxy','hamburger--3dxy-r',
  'hamburger--arrow','hamburger--arrow-r','hamburger--arrowalt','hamburger--arrowalt-r','hamburger--arrowturn','hamburger--arrowturn-r',
  'hamburger--boring','hamburger--collapse','hamburger--collapse-r','hamburger--elastic','hamburger--elastic-r',
  'hamburger--emphatic','hamburger--emphatic-r','hamburger--minus','hamburger--slider','hamburger--slider-r',
  'hamburger--spin','hamburger--spin-r','hamburger--spring','hamburger--spring-r','hamburger--stand','hamburger--stand-r',
  'hamburger--squeeze','hamburger--vortex','hamburger--vortex-r',
];
if (!in_array($hamburger_style, $valid_styles, true)) {
  $hamburger_style = 'hamburger--spin';
}

// Panel width style
$menu_width_style = ($mobile_menu_effect !== 'fullscreen')
  ? "width: {$mobile_menu_width}%; left: 0;"
  : "width: 100%;";

// Prepare data for Alpine (Navi array is fine to JSON)
$menu_array = $primary_navigation->toArray();
?>

<?php if ($enable_hamburger): ?>
  <!-- Mobile Hamburger -->
  <button
    :class="{ 'is-active z-50 bg-transparent hover:bg-transparent flex items-center justify-center': isOpen }"
    class="hamburger <?php echo esc_attr($hamburger_style); ?> lg:hidden"
    type="button"
    aria-label="Menu"
    aria-expanded="false"
    @click="isOpen = !isOpen"
  >
    <span class="hamburger-box">
      <span class="hamburger-inner"></span>
    </span>
  </button>
<?php endif; ?>

<?php if ($enable_hamburger && $primary_navigation->isNotEmpty()) : ?>
  <?php
  // Minimal recursive encoder for a Navi item -> plain array (label, url, children[])
  if (!function_exists('matrix_encode_menu_subtree')) {
    function matrix_encode_menu_subtree($node) {
      $out = [
        'label'    => isset($node->label) ? (string) $node->label : '',
        'url'      => isset($node->url)   ? (string) $node->url   : '',
        'children' => [],
      ];
      if (!empty($node->children) && is_iterable($node->children)) {
        foreach ($node->children as $child) {
          $out['children'][] = matrix_encode_menu_subtree($child);
        }
      }
      return $out;
    }
  }
  ?>
  <div
    x-data='{
      // Flyout state (hamburger’s isOpen lives on the parent)
      flyLevel: 0,          // 0: top, 1: second tier, 2: flattened descendants
      secondItems: [],      // items under the selected top-level item
      flattened: [],        // flattened descendants of selected second-tier item

      openSecondFrom(el) {
        try {
          this.secondItems = JSON.parse(el.dataset.children || "[]");
        } catch(e) {
          this.secondItems = [];
        }
        this.flyLevel = 1;
      },

      hasDescendants(node) {
        if (!node || !Array.isArray(node.children)) return false;
        const stack = [...node.children];
        while (stack.length) {
          const n = stack.pop();
          if (Array.isArray(n.children) && n.children.length) return true;
        }
        return false;
      },

      openFlatten(index) {
        const node = this.secondItems[index] || null;
        const flatten = (n) => {
          let out = [];
          if (!n || !Array.isArray(n.children)) return out;
          n.children.forEach(ch => {
            if (ch.url && ch.label) out.push({ url: ch.url, label: ch.label });
            if (Array.isArray(ch.children) && ch.children.length) {
              out = out.concat(flatten(ch));
            }
          });
          return out;
        };
        // de-dupe by URL
        const seen = new Set();
        this.flattened = flatten(node).filter(i => i.url && !seen.has(i.url) && seen.add(i.url));
        this.flyLevel = 2;
      },

      back() {
        if (this.flyLevel === 2) {
          this.flyLevel = 1;
          this.flattened = [];
        } else if (this.flyLevel === 1) {
          this.flyLevel = 0;
          this.secondItems = [];
        }
      }
    }'
    x-show="isOpen"
    :class="{ '<?php echo esc_attr($transition_class); ?>': !isOpen, 'translate-x-0 translate-y-0': isOpen }"
    class="absolute top-0 left-0 z-40 h-screen <?php echo esc_attr($transition_class); ?> bg-white transition-transform duration-500 ease-out overflow-hidden"
    style="background-color: <?php echo esc_attr($mobile_menu_bg); ?>; <?php echo esc_attr($menu_width_style); ?>"
    x-transition:enter="transition ease-out duration-500"
    x-transition:leave="transition ease-in duration-300"
    @click.away="isOpen = false"
    @keydown.escape="isOpen = false"
  >
    <nav class="relative h-full">
      <div class="overflow-hidden relative h-full">
        <!-- LEVEL 0: Top-level -->
        <div
          class="pt-[8rem] overflow-y-auto absolute inset-0 px-8 pb-8 transition-transform duration-300"
          :class="flyLevel === 0 ? 'translate-x-0' : '-translate-x-full'"
        >
          <ul class="space-y-8">
            <?php foreach ($primary_navigation->toArray() as $i => $item): ?>
              <?php
              $subtree = [];
              if (!empty($item->children)) {
                // Encode the entire subtree (second tier + deeper)
                $subtree = [];
                foreach ($item->children as $child) {
                  $subtree[] = matrix_encode_menu_subtree($child);
                }
              }
              $data_children = esc_attr(wp_json_encode($subtree, JSON_HEX_TAG|JSON_HEX_APOS|JSON_HEX_QUOT|JSON_HEX_AMP));
              ?>
              <li class="relative mb-4 border-b border-[#CCDEE2] pb-6 <?php echo esc_attr($item->classes); ?> <?php echo $item->active ? 'current-item' : ''; ?>">
                <div class="flex justify-between items-center">
                  <a
                    href="<?php echo esc_url($item->url); ?>"
                    class="text-lg font-normal leading-7 text-secondary-800">
                    <?php echo esc_html($item->label); ?>
                  </a>

                  <?php if (!empty($item->children)) : ?>
                    <button
                      type="button"
                      class="ml-4"
                      data-children="<?php echo $data_children; ?>"
                      @click.prevent="openSecondFrom($el)"
                      aria-label="View sub-menu">
                          <svg width="18" height="18" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                          <path d="M10 6L16 12L10 18" stroke="#1D2939" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"></path>
                          </svg>
                    </button>
                  <?php endif; ?>
                </div>
              </li>
            <?php endforeach; ?>
          </ul>
        </div>

        <!-- LEVEL 1: Second-tier of selected top item -->
        <div
          class="pt-[8rem] overflow-y-auto absolute inset-0 px-8 pb-8 bg-white transition-transform duration-300"
          :class="flyLevel === 1 ? 'translate-x-0' : (flyLevel < 1 ? 'translate-x-full' : '-translate-x-full')"
          style="display:block;"
        >
          <div class="mb-6">
            <button type="button" class="inline-flex gap-2 items-center text-base text-secondary-800" @click="back()" aria-label="Back">
              <svg width="18" height="18" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                <path d="M15 18L9 12L15 6" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
              </svg>
              <span>Back</span>
            </button>
          </div>

          <ul class="space-y-8">
            <template x-for="(child, cidx) in secondItems" :key="cidx">
              <li class="border-b border-[#CCDEE2] pb-6">
                <div class="flex justify-between items-center">
                  <a :href="child.url" class="text-lg font-normal leading-7 text-secondary-800" x-text="child.label"></a>

                  <template x-if="hasDescendants(child)">
                    <button
                      type="button"
                      class="ml-4"
                      @click.prevent="openFlatten(cidx)"
                      aria-label="View deeper items">
                      <svg width="18" height="18" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                        <path d="M10 6L16 12L10 18" stroke="#1D2939" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                      </svg>
                    </button>
                  </template>
                </div>
              </li>
            </template>
          </ul>
        </div>

        <!-- LEVEL 2: Flattened descendants (3rd/4th/5th) -->
        <div
          class="pt-[8rem] overflow-y-auto absolute inset-0 px-8 pb-8 bg-white transition-transform duration-300"
          :class="flyLevel === 2 ? 'translate-x-0' : 'translate-x-full'"
          style="display:block;"
        >
          <div class="mb-6">
            <button type="button" class="inline-flex gap-2 items-center text-base text-secondary-800" @click="back()" aria-label="Back">
              <svg width="18" height="18" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                <path d="M15 18L9 12L15 6" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
              </svg>
              <span>Back</span>
            </button>
          </div>

          <ul class="space-y-6">
            <template x-for="(leaf, lidx) in flattened" :key="lidx">
              <li class="border-b border-[#CCDEE2] pb-4">
                <a :href="leaf.url" class="block text-lg leading-7 text-secondary-800" x-text="leaf.label"></a>
              </li>
            </template>
            <template x-if="!flattened.length">
              <li class="py-6 text-sm text-gray-500">No additional items.</li>
            </template>
          </ul>
        </div>
      </div>
    </nav>
  </div>
<?php endif; ?>
