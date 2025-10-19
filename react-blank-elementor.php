<?php
/**
 * Template Name: React Blank (Astra + Elementor Ready, React on #root)
 * Template Post Type: page
 */
defined('ABSPATH') || exit;
get_header();
?>

<style>
  html,body{margin:0;padding:0;background:#000;}
  #root{margin:0;padding:0;min-height:100svh;}
  .site-content, .ast-container, main#primary{margin:0;padding:0;min-height:0;}
</style>

<!-- React mount -->
<div id="root"></div>

<?php
// Elementor detection & fallback
$post_id = get_the_ID();
$raw     = get_post_field('post_content', $post_id);
$is_empty_raw = strlen( trim( wp_strip_all_tags( (string) $raw ) ) ) === 0;

$is_elementor = class_exists('\Elementor\Plugin')
  ? \Elementor\Plugin::$instance->db->is_built_with_elementor($post_id)
  : false;

$maybe_hide = ( ! $is_elementor && $is_empty_raw ) ? 'style="display:none"' : '';
?>

<main id="primary" class="site-main">
  <?php if ( have_posts() ) : while ( have_posts() ) : the_post(); ?>
    <div class="wp-page-content" <?php echo $maybe_hide; ?>>
      <?php the_content(); ?>
    </div>
  <?php endwhile; endif; ?>
</main>

<?php get_footer(); ?>
