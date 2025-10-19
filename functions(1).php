<?php
/**
 * Twenty Twenty-Five functions and definitions.
 *
 * @link https://developer.wordpress.org/themes/basics/theme-functions/
 * @package WordPress
 * @subpackage Twenty_Twenty_Five
 * @since Twenty Twenty-Five 1.0
 */

/* ======================================================
 *  BAGIAN A — Fungsi bawaan theme (jangan dihapus)
 * ====================================================== */

if ( ! function_exists( 'twentytwentyfive_post_format_setup' ) ) :
	function twentytwentyfive_post_format_setup() {
		add_theme_support(
			'post-formats',
			array( 'aside', 'audio', 'chat', 'gallery', 'image', 'link', 'quote', 'status', 'video' )
		);
	}
endif;
add_action( 'after_setup_theme', 'twentytwentyfive_post_format_setup' );

if ( ! function_exists( 'twentytwentyfive_editor_style' ) ) :
	function twentytwentyfive_editor_style() {
		add_editor_style( 'assets/css/editor-style.css' );
	}
endif;
add_action( 'after_setup_theme', 'twentytwentyfive_editor_style' );

if ( ! function_exists( 'twentytwentyfive_enqueue_styles' ) ) :
	function twentytwentyfive_enqueue_styles() {
		wp_enqueue_style(
			'twentytwentyfive-style',
			get_parent_theme_file_uri( 'style.css' ),
			array(),
			wp_get_theme()->get( 'Version' )
		);
	}
endif;
add_action( 'wp_enqueue_scripts', 'twentytwentyfive_enqueue_styles' );

if ( ! function_exists( 'twentytwentyfive_block_styles' ) ) :
	function twentytwentyfive_block_styles() {
		register_block_style(
			'core/list',
			array(
				'name'         => 'checkmark-list',
				'label'        => __( 'Checkmark', 'twentytwentyfive' ),
				'inline_style' => '
					ul.is-style-checkmark-list { list-style-type: "\2713"; }
					ul.is-style-checkmark-list li { padding-inline-start: 1ch; }',
			)
		);
	}
endif;
add_action( 'init', 'twentytwentyfive_block_styles' );

if ( ! function_exists( 'twentytwentyfive_pattern_categories' ) ) :
	function twentytwentyfive_pattern_categories() {
		register_block_pattern_category(
			'twentytwentyfive_page',
			array(
				'label'       => __( 'Pages', 'twentytwentyfive' ),
				'description' => __( 'A collection of full page layouts.', 'twentytwentyfive' ),
			)
		);
		register_block_pattern_category(
			'twentytwentyfive_post-format',
			array(
				'label'       => __( 'Post formats', 'twentytwentyfive' ),
				'description' => __( 'A collection of post format patterns.', 'twentytwentyfive' ),
			)
		);
	}
endif;
add_action( 'init', 'twentytwentyfive_pattern_categories' );

if ( ! function_exists( 'twentytwentyfive_register_block_bindings' ) ) :
	function twentytwentyfive_register_block_bindings() {
		register_block_bindings_source(
			'twentytwentyfive/format',
			array(
				'label'              => _x( 'Post format name', 'Label', 'twentytwentyfive' ),
				'get_value_callback' => 'twentytwentyfive_format_binding',
			)
		);
	}
endif;
add_action( 'init', 'twentytwentyfive_register_block_bindings' );

if ( ! function_exists( 'twentytwentyfive_format_binding' ) ) :
	function twentytwentyfive_format_binding() {
		$post_format_slug = get_post_format();
		if ( $post_format_slug && 'standard' !== $post_format_slug ) {
			return get_post_format_string( $post_format_slug );
		}
	}
endif;

/* ======================================================
 *  BAGIAN B — Integrasi React + Vite
 *  React build folder: /wp-content/themes/twentytwentyfive/react-app/
 * ====================================================== */

if ( ! defined( 'HINTS_REACT_DIR' ) ) {
	define( 'HINTS_REACT_DIR', get_stylesheet_directory() . '/react-app' );
}
if ( ! defined( 'HINTS_REACT_URI' ) ) {
	define( 'HINTS_REACT_URI', get_stylesheet_directory_uri() . '/react-app' );
}

/**
 * Enqueue React build asset dari manifest.json
 */
function hints_enqueue_react_assets() {
	$manifest_path = HINTS_REACT_DIR . '/manifest.json';
	if ( ! file_exists( $manifest_path ) ) {
		return;
	}

	$manifest = json_decode( file_get_contents( $manifest_path ), true );
	$entry    = $manifest['src/main.tsx'] ?? ( $manifest['src/main.jsx'] ?? null );
	if ( ! $entry ) {
		return;
	}

	// CSS
	if ( ! empty( $entry['css'] ) && is_array( $entry['css'] ) ) {
		foreach ( $entry['css'] as $css_file ) {
			wp_enqueue_style(
				'hints-react-style',
				HINTS_REACT_URI . '/' . ltrim( $css_file, '/' ),
				array(),
				null
			);
		}
	}

	// JS
	wp_enqueue_script(
		'hints-react-script',
		HINTS_REACT_URI . '/' . ltrim( $entry['file'], '/' ),
		array(),
		null,
		true
	);

	// type="module"
	add_filter(
		'script_loader_tag',
		function ( $tag, $handle, $src ) {
			if ( 'hints-react-script' === $handle ) {
				return '<script type="module" src="' . esc_url( $src ) . '"></script>';
			}
			return $tag;
		},
		10,
		3
	);
}

/**
 * Shortcode: [react_app]
 * Bisa ditempatkan di halaman WP apa pun.
 */
function hints_react_shortcode() {
	hints_enqueue_react_assets();
	ob_start();
	?>
	<div id="root"></div>
	<noscript><p>Please enable JavaScript to view this site.</p></noscript>
	<?php
	return ob_get_clean();
}
add_shortcode( 'react_app', 'hints_react_shortcode' );

/**
 * Auto load React di halaman dengan slug tertentu.
 * Contoh: buat Page slug `company-profile` → otomatis React.
 */
function hints_maybe_load_react_template() {
	if ( is_page() ) {
		$slugs = array( 'company-profile', 'home-react' ); // ubah sesuai kebutuhan
		$slug  = get_post_field( 'post_name', get_post() );
		if ( in_array( $slug, $slugs, true ) ) {
			add_filter(
				'the_content',
				function () {
					return do_shortcode( '[react_app]' );
				},
				999
			);
		}
	}
}
add_action( 'wp', 'hints_maybe_load_react_template' );

/**
 * SEO tetap dikelola oleh RankMath/Yoast.
 * React hanya menggantikan konten <body>.
 */

// === TITLE TAG SUPPORT + SAFE FALLBACK ===

// 1) Wajib: aktifkan dukungan <title> agar WP/Yoast menulis <title> di <head>
add_action('after_setup_theme', function () {
    add_theme_support('title-tag');
});

// 2) Fallback aman kalau Yoast/RankMath belum aktif.
//    Tidak menimpa kalau plugin SEO aktif (Yoast/Rank Math pakai hook sama).
add_filter('pre_get_document_title', function ($title) {
    // Jika Yoast aktif, biarkan Yoast yang atur
    if (defined('WPSEO_VERSION')) return $title;
    // Jika Rank Math aktif, biarkan Rank Math yang atur
    if (defined('RANK_MATH_VERSION')) return $title;

    if (is_front_page() || is_home()) {
        $site  = get_bloginfo('name');
        $tagln = get_bloginfo('description');
        return $tagln ? "$site | $tagln" : $site;
    }

    $sep   = apply_filters('document_title_separator', '–'); // mengikuti setting WP/SEO
    $page  = wp_strip_all_tags(get_the_title());
    $site  = get_bloginfo('name');
    return $page && $site ? "$page $sep $site" : ($page ?: $site);
}, 99);
