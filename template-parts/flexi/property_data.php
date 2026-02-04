<?php
/**
 * Frontend: Property Data (Read More after benchmark WORD COUNT)
 * - No design options; fixed colors per snippet.
 * - Uses get_sub_field() exclusively.
 * - Random section ID.
 * - Standard section/container & responsive padding controls.
 * - WYSIWYG fields get wp_editor class.
 * - “Read more” shows based on benchmark WORD COUNT (not height).
 * - NO max-height is ever applied.
 */

// -------------------------
// Fetch fields (get_sub_field ONLY)
// -------------------------
$sector         = trim((string) get_sub_field('sector'));
$year           = trim((string) get_sub_field('year'));
$uppercase_year = (bool) get_sub_field('uppercase_year');
$client         = trim((string) get_sub_field('client'));
$size_html      = get_sub_field('size'); // WYSIWYG

$right_text      = get_sub_field('right_text'); // WYSIWYG
$read_more_label = get_sub_field('read_more_label');
$read_less_label = get_sub_field('read_less_label');

$read_more_label = !empty($read_more_label) ? $read_more_label : 'Read more';
$read_less_label = !empty($read_less_label) ? $read_less_label : 'Read less';

// -------------------------
// Build padding classes from the repeater
// -------------------------
$padding_classes = [];
if (have_rows('padding_settings')) {
    while (have_rows('padding_settings')) {
        the_row();
        $screen_size    = get_sub_field('screen_size');
        $padding_top    = get_sub_field('padding_top');
        $padding_bottom = get_sub_field('padding_bottom');

        if (!empty($screen_size) && $padding_top !== null && $padding_top !== '') {
            $padding_classes[] = $screen_size . ':pt-[' . floatval($padding_top) . 'rem]';
        }
        if (!empty($screen_size) && $padding_bottom !== null && $padding_bottom !== '') {
            $padding_classes[] = $screen_size . ':pb-[' . floatval($padding_bottom) . 'rem]';
        }
    }
}
$padding_class_string = !empty($padding_classes) ? ' ' . esc_attr(implode(' ', $padding_classes)) : '';

// -------------------------
// Random section ID & target IDs
// -------------------------
$section_id = 'section-' . wp_generate_uuid4();
$content_id = $section_id . '-right-content';
$button_id  = $section_id . '-readmore-btn';

// Benchmark text (the exact sample you provided) — used for WORD COUNT cutoff
$benchmark_text = "Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat.

Lorem ipsum dolor sit amet, consectetur adipiscing elit ullamco laboris nisi ut aliquip adipiscing elit .";
?>
<section id="<?php echo esc_attr($section_id); ?>" class="relative flex overflow-hidden bg-[#ededed]">
    <div class="flex flex-col items-center w-full mx-auto max-w-container pt-5 pb-5 max-lg:px-5<?php echo $padding_class_string; ?>">
        <div class="w-full xl:px-0">
            <div class="flex flex-col gap-[3rem] py-[2.5rem] lg:py-[5rem] md:flex-row md:items-stretch">
                <!-- Left card -->
                <div class="w-full bg-[#e0e0e0] p-4 lg:p-6 md:w-1/2 lg:w-2/5 h-full max-h-fit">
                    <div class="flex gap-4">
                        <!-- Decorative bar -->
                        <div class="w-[0.25rem] shrink-0 bg-[#0098d8]" aria-hidden="true"></div>

                        <!-- Content -->
                        <div class="flex-1 min-w-0">
                            <div class="grid grid-cols-[auto,1fr] gap-x-6 gap-y-4">
                                <?php if ($sector !== ''): ?>
                                    <div class="text-left text-[1.5rem] font-[600] leading-[1.625rem] tracking-[-0.01rem] text-[#0a1119] font-secondary">
                                        <?php echo esc_html__('Sector', 'your-textdomain'); ?>
                                    </div>
                                    <div class="text-left text-[1rem] font-[400] leading-[1.625rem] text-[#0a1119] font-primary">
                                        <?php echo esc_html($sector); ?>
                                    </div>
                                <?php endif; ?>

                                <?php if ($year !== ''): ?>
                                    <div class="text-left text-[1.5rem] font-[600] leading-[1.625rem] tracking-[-0.01rem] text-[#0a1119] font-secondary">
                                        <?php echo esc_html__('Year', 'your-textdomain'); ?>
                                    </div>
                                    <div class="text-left text-[1rem] font-[400] leading-[1.625rem] text-[#0a1119] font-primary<?php echo $uppercase_year ? ' uppercase' : ''; ?>">
                                        <?php echo esc_html($year); ?>
                                    </div>
                                <?php endif; ?>

                                <?php if ($client !== ''): ?>
                                    <div class="text-left text-[1.5rem] font-[600] leading-[1.625rem] tracking-[-0.01rem] text-[#0a1119] font-secondary">
                                        <?php echo esc_html__('Client', 'your-textdomain'); ?>
                                    </div>
                                    <div class="text-left text-[1rem] font-[400] leading-[1.625rem] text-[#0a1119] font-primary">
                                        <?php echo esc_html($client); ?>
                                    </div>
                                <?php endif; ?>

                                <?php if (!empty($size_html)): ?>
                                    <div class="text-left text-[1.5rem] font-[600] leading-[1.625rem] tracking-[-0.01rem] text-[#0a1119] font-secondary">
                                        <?php echo esc_html__('Size', 'your-textdomain'); ?>
                                    </div>
                                    <div class="text-left text-[1rem] font-[400] leading-[1.625rem] text-[#0a1119] font-primary wp_editor">
                                        <?php echo $size_html; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
                                    </div>
                                <?php endif; ?>

                                <?php if (have_rows('extra_rows')): ?>
                                    <?php while (have_rows('extra_rows')): the_row();
                                        $ex_label = trim((string) get_sub_field('label'));
                                        $ex_value = get_sub_field('value');
                                        $ex_upper = (bool) get_sub_field('uppercase_value');
                                        if ($ex_label === '' && empty($ex_value)) {
                                            continue;
                                        }
                                    ?>
                                        <div class="text-left text-[1.5rem] font-[600] leading-[1.625rem] tracking-[-0.01rem] text-[#0a1119] font-secondary">
                                            <?php echo $ex_label !== '' ? esc_html($ex_label) : esc_html__('Label', 'your-textdomain'); ?>
                                        </div>
                                        <div class="text-left text-[1rem] font-[400] leading-[1.625rem] text-[#0a1119] font-primary<?php echo $ex_upper ? ' uppercase' : ''; ?> wp_editor">
                                            <?php
                                            if (!empty($ex_value)) {
                                                echo $ex_value; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
                                            } else {
                                                echo esc_html__('—', 'your-textdomain');
                                            }
                                            ?>
                                        </div>
                                    <?php endwhile; ?>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Right text + Read more after benchmark WORD COUNT -->
                <div class="w-full md:w-1/2 lg:w-3/5">
                    <div
                        id="<?php echo esc_attr($content_id); ?>"
                        class="text-left text-[1rem] font-[400] leading-[1.625rem] text-[#000000] font-primary wp_editor"
                        data-expanded="true"
                    >
                        <?php
                        if (!empty($right_text)) {
                            echo $right_text; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
                        } else {
                            echo esc_html__('Lorem ipsum dolor sit amet, consectetur adipiscing elit...', 'your-textdomain');
                        }
                        ?>
                    </div>

                    <button
                        type="button"
                        id="<?php echo esc_attr($button_id); ?>"
                        class="mt-4 inline-block text-left text-[1rem] font-[400] leading-[1.625rem] text-[#0a1119] font-primary underline hover:no-underline"
                        aria-expanded="false"
                        aria-controls="<?php echo esc_attr($content_id); ?>"
                        hidden
                    >
                        <?php echo esc_html($read_more_label); ?>
                    </button>

                    <script>
(function(){
  var sectionId  = <?php echo json_encode($section_id); ?>;
  var contentId  = <?php echo json_encode($content_id); ?>;
  var buttonId   = <?php echo json_encode($button_id); ?>;
  var readMore   = <?php echo json_encode($read_more_label); ?>;
  var readLess   = <?php echo json_encode($read_less_label); ?>;

  // Benchmark text (definitive cutoff) — we cut after the same WORD COUNT as this text.
  var benchmarkText = <?php echo json_encode($benchmark_text); ?>;

  // Guard: if we already initialized this section, bail
  var sectionEl = document.getElementById(sectionId);
  if (!sectionEl || sectionEl.dataset.readmoreInit === '1') return;
  sectionEl.dataset.readmoreInit = '1';

  var container = document.getElementById(contentId);
  var btn       = document.getElementById(buttonId);
  if (!container || !btn) return;

  function wordCount(str){
    return (String(str).trim().match(/\S+/g) || []).length;
  }

  var cutoffWords = wordCount(benchmarkText);

  function getTextNodes(root) {
    var walker = document.createTreeWalker(root, NodeFilter.SHOW_TEXT, {
      acceptNode: function(node){
        return (node.nodeValue && node.nodeValue.match(/\S/))
          ? NodeFilter.FILTER_ACCEPT
          : NodeFilter.FILTER_REJECT;
      }
    });
    var nodes = [];
    while (walker.nextNode()) nodes.push(walker.currentNode);
    return nodes;
  }

  // Split AFTER N words, preserving existing HTML up to the split.
  function splitAfterWords(root, nWords) {
    if (root.querySelector('[data-readmore-remainder="1"]')) return true;

    var nodes = getTextNodes(root);
    var seen = 0;
    var splitNode = null;
    var splitOffset = 0;

    for (var i = 0; i < nodes.length; i++) {
      var text = nodes[i].nodeValue;
      var parts = text.split(/(\s+)/); // keep whitespace tokens
      for (var p = 0; p < parts.length; p++) {
        var token = parts[p];
        if (!token) continue;

        if (!/^\s+$/.test(token)) {
          seen++;
          if (seen === nWords) {
            // Split AFTER this word token
            var upto = parts.slice(0, p + 1).join('');
            splitNode = nodes[i];
            splitOffset = upto.length;
            break;
          }
        }
      }
      if (splitNode) break;
    }

    // If we never reached cutoff, no toggle needed
    if (!splitNode) return false;

    // Build remainder range (from split point to end of container)
    var r = document.createRange();
    r.setStart(splitNode, splitOffset);
    r.setEnd(container, container.childNodes.length);

    // Only toggle if there is meaningful remainder
    var remainderText = r.toString().trim();
    if (!remainderText) return false;

    var remainderSpan = document.createElement('span');
    remainderSpan.setAttribute('data-readmore-remainder', '1');
    remainderSpan.hidden = true;

    remainderSpan.appendChild(r.extractContents());
    container.appendChild(remainderSpan);

    return true;
  }

  function setCollapsed() {
    var rem = container.querySelector('[data-readmore-remainder="1"]');
    if (rem) rem.hidden = true;
    container.setAttribute('data-expanded', 'false');
    btn.setAttribute('aria-expanded', 'false');
    btn.textContent = readMore;
  }

  function setExpanded() {
    var rem = container.querySelector('[data-readmore-remainder="1"]');
    if (rem) rem.hidden = false;
    container.setAttribute('data-expanded', 'true');
    btn.setAttribute('aria-expanded', 'true');
    btn.textContent = readLess;
  }

  // Init: split and decide
  var needsToggle = splitAfterWords(container, cutoffWords);

  if (needsToggle) {
    btn.hidden = false;
    setCollapsed(); // show read more immediately after benchmark words
  } else {
    btn.hidden = true;
    setExpanded(); // show full content
  }

  btn.addEventListener('click', function(e){
    e.preventDefault();
    e.stopPropagation();
    var expanded = container.getAttribute('data-expanded') === 'true';
    if (expanded) setCollapsed(); else setExpanded();
  });
})();
</script>
                </div>
            </div>
        </div>
    </div>
</section>
