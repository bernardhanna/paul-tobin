<?php
$button_bg_color = get_field('back_to_top_settings_button_bg_color', 'option') ?: '#000';
$button_hover_bg_color = get_field('back_to_top_settings_button_hover_bg_color', 'option') ?: '#0098D8';
?>
<button id="backToTop"
  class="flex fixed right-5 bottom-5 invisible justify-center items-center w-14 h-14 rounded-full opacity-0 transition duration-300">
  <svg width="52" height="52" viewBox="0 0 52 52" fill="none" xmlns="http://www.w3.org/2000/svg"
    onmouseover="this.querySelector('rect').setAttribute('fill', '<?php echo esc_attr($button_hover_bg_color); ?>');"
    onmouseout="this.querySelector('rect').setAttribute('fill', '<?php echo esc_attr($button_bg_color); ?>');">
    <rect x="1" y="1" width="50" height="50" rx="25" fill="<?php echo esc_attr($button_bg_color); ?>" />
    <rect x="1" y="1" width="50" height="50" rx="25" stroke="white" stroke-width="2" />
    <path d="M26 33V19M26 19L19 26M26 19L33 26" stroke="white" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
  </svg>
</button>

<script>
  document.addEventListener("DOMContentLoaded", function() {
    const backToTop = document.getElementById("backToTop");
    window.addEventListener("scroll", function() {
      if (window.scrollY > 200) {
        backToTop.classList.remove("opacity-0", "invisible");
        backToTop.classList.add("opacity-100", "visible");
      } else {
        backToTop.classList.remove("opacity-100", "visible");
        backToTop.classList.add("opacity-0", "invisible");
      }
    });
    backToTop.addEventListener("click", function() {
      window.scrollTo({
        top: 0,
        behavior: "smooth"
      });
    });
  });
</script>