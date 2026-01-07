<?php
// File: inc/enqueue-fonts.php
/**
 * Enqueue Google Fonts (Montserrat + Playfair Display)
 */
function matrix_starter_enqueue_fonts() {
  wp_enqueue_style(
    'matrix-google-fonts',
    'https://fonts.googleapis.com/css2?family=Montserrat:ital,wght@0,100..900;1,100..900&family=Playfair:ital,opsz,wght@0,5..1200,300..900;1,5..1200,300..900&display=swap',
    [],
    null
  );
}
add_action('wp_enqueue_scripts', 'matrix_starter_enqueue_fonts', 5);
/**
 * Optional: Resource hints for Google Fonts
 */
function matrix_starter_resource_hints( $hints, $relation_type ) {
  if ( 'preconnect' === $relation_type ) {
    $hints[] = 'https://fonts.googleapis.com';
    $hints[] = 'https://fonts.gstatic.com';
  }
  return $hints;
}
add_filter( 'wp_resource_hints', 'matrix_starter_resource_hints', 10, 2 );