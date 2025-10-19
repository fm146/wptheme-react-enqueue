<?php
/**
 * Template Name: React App Page
 * Template Post Type: page
 */

if ( ! function_exists( 'hints_enqueue_react_assets' ) ) {
	get_header();
	echo '<main id="primary" class="site-main"><p><strong>React bundle belum tersedia.</strong><br>Pastikan functions.php sudah memuat fungsi hints_enqueue_react_assets() dan folder react-app/ sudah diupload.</p></main>';
	get_footer();
	exit;
}

get_header();
hints_enqueue_react_assets();
?>

<main id="primary" class="site-main" style="min-height:60vh">
  <div id="root"></div>
  <noscript><p>Please enable JavaScript to view this site.</p></noscript>
</main>

<?php get_footer(); ?>
