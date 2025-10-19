<?php
/**
 * Template Name: React Blank (Elementor Ready - React on Top, Conditional Content)
 * Template Post Type: page
 */
?><!doctype html>
<html <?php language_attributes(); ?>>
<head>
<meta charset="<?php bloginfo('charset'); ?>">
<meta name="viewport" content="width=device-width, initial-scale=1">
<?php wp_head(); ?>
<style>
  html,body{margin:0;padding:0;background:#000;}
  #root{margin:0;padding:0;min-height:100svh;}
  main#primary{margin:0;padding:0;min-height:0;}
</style>
</head>
<body <?php body_class('react-blank-elementor react-top'); ?>>
<?php if ( function_exists( 'wp_body_open' ) ) { wp_body_open(); } ?>

  <!-- React di atas -->
  <div id="root"></div>

  <?php
    // === Elementor awareness ===
    $post_id = get_the_ID();
    $raw     = get_post_field( 'post_content', $post_id );
    $is_empty_raw = strlen( trim( wp_strip_all_tags( (string) $raw ) ) ) === 0;

    // Deteksi apakah halaman ini dibangun dengan Elementor
    $is_elementor = false;
    if ( class_exists( '\Elementor\Plugin' ) ) {
        try {
            $is_elementor = \Elementor\Plugin::$instance->db->is_built_with_elementor( $post_id );
        } catch ( \Throwable $e ) {
            // jika method berubah di masa depan, anggap bukan elementor
            $is_elementor = false;
        }
    }

    // Jika BUKAN elementor dan konten kosong, kita sembunyikan dengan CSS.
    // Tapi the_content() tetap DIPANGGIL agar Elementor tidak protes.
    $hide_content_css = ( ! $is_elementor && $is_empty_raw ) ? 'style="display:none"' : '';
  ?>

  <main id="primary" class="site-main">
    <?php if ( have_posts() ) : while ( have_posts() ) : the_post(); ?>
      <div class="wp-page-content" <?php echo $hide_content_css; ?>>
        <?php the_content(); ?>
      </div>
    <?php endwhile; endif; ?>
  </main>

<?php wp_footer(); ?>
</body>
</html>
