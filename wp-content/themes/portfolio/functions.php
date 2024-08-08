<?php // phpcs:ignore WordPress.Files.FileName.InvalidClassFileName
/**
 * Timber starter-theme
 * https://github.com/timber/starter-theme
 *
 * @package  WordPress
 * @subpackage  Timber
 * @since   Timber 0.1
 */

/**
 * If you are installing Timber as a Composer dependency in your theme, you'll need this block
 * to load your dependencies and initialize Timber. If you are using Timber via the WordPress.org
 * plug-in, you can safely delete this block.
 */
$composer_autoload = __DIR__ . '/vendor/autoload.php';
if ( file_exists( $composer_autoload ) ) {
	require_once $composer_autoload;
	$timber = new Timber\Timber();
}

/**
 * This ensures that Timber is loaded and available as a PHP class.
 * If not, it gives an error message to help direct developers on where to activate
 */
if ( ! class_exists( 'Timber' ) ) {

	add_action(
		'admin_notices',
		function() {
			echo '<div class="error"><p>Timber not activated. Make sure you activate the plugin in <a href="' . esc_url( admin_url( 'plugins.php#timber' ) ) . '">' . esc_url( admin_url( 'plugins.php' ) ) . '</a></p></div>';
		}
	);

	add_filter(
		'template_include',
		function( $template ) {
			return get_stylesheet_directory() . '/static/no-timber.html';
		}
	);
	return;
}

/**
 * Sets the directories (inside your theme) to find .twig files
 */
Timber::$dirname = array( 'templates', 'views' );

/**
 * By default, Timber does NOT autoescape values. Want to enable Twig's autoescape?
 * No prob! Just set this value to true
 */
Timber::$autoescape = false;


/**
 * We're going to configure our theme inside of a subclass of Timber\Site
 * You can move this to its own file and include here via php's include("MySite.php")
 */
class Icarus extends Timber\Site {
	/** Add timber support. */
	public function __construct() {
		add_action( 'after_setup_theme', array( $this, 'theme_supports' ) );
		add_filter( 'timber/context', array( $this, 'add_to_context' ) );
		add_filter( 'timber/twig', array( $this, 'add_to_twig' ) );
		add_action( 'init', array( $this, 'register_post_types' ) );
		add_action( 'init', array( $this, 'register_taxonomies' ) );
		parent::__construct();
	}
	/** This is where you can register custom post types. */
	public function register_post_types() {
		include __DIR__ .  '/inc/register-post-types.php';
	}
	/** This is where you can register custom taxonomies. */
	public function register_taxonomies() {
		include __DIR__ .  '/inc/register-custom-taxonomy.php';
	}

	/** This is where you add some context
	 *
	 * @param string $context context['this'] Being the Twig's {{ this }}.
	 */
	public function add_to_context( $context ) {

		include __DIR__ . '/inc/polylang-helpers.php';

		$context['is_front_page'] = is_front_page();
		
		$context['sub_footer_section'] = get_field( 'sub_footer_section' );

		$context['featured_posts'] = Timber::get_posts(
			array(
				'post_type' => 'post',
			)
		);

		$context['projects'] = Timber::get_posts(
			array(
				'post_type'=> 'projects',
			)
		);

		$context['featured_projects'] = Timber::get_posts( 
			array(
				'post_type'=> 'projects',
				'posts_per_page' => 3,
				'order' => 'DESC',
			)
		);

		$context['latest_resume'] = Timber::get_posts(
			array(
				'post_type'=> 'resume',
				'posts_per_page' => 1,
				'order' => 'DESC',
			)
		);

		$context['translated_link'] = array(
			'home' => get_translated_link('home'),
			'contact' => get_translated_link('contact'),
		);
		
		// Set all nav menus in context.
		foreach (array_keys(get_registered_nav_menus()) as $location) {
			// Bail out if menu has no location.
			if (!has_nav_menu($location)) {
				continue;
			}
	
			$menu = new Timber\Menu($location);
		
			$context[$location] = $menu;
		}
	
		return $context;
	}

	public function theme_supports() {
		// Add default posts and comments RSS feed links to head.
		add_theme_support( 'automatic-feed-links' );

		/*
		 * Let WordPress manage the document title.
		 * By adding theme support, we declare that this theme does not use a
		 * hard-coded <title> tag in the document head, and expect WordPress to
		 * provide it for us.
		 */
		add_theme_support( 'title-tag' );

		/*
		 * Enable support for Post Thumbnails on posts and pages.
		 *
		 * @link https://developer.wordpress.org/themes/functionality/featured-images-post-thumbnails/
		 */
		add_theme_support( 'post-thumbnails' );

		/*
		 * Switch default core markup for search form, comment form, and comments
		 * to output valid HTML5.
		 */
		add_theme_support(
			'html5',
			array(
				'comment-form',
				'comment-list',
				'gallery',
				'caption',
			)
		);

		/*
		 * Enable support for Post Formats.
		 *
		 * See: https://codex.wordpress.org/Post_Formats
		 */
		add_theme_support(
			'post-formats',
			array(
				'aside',
				'image',
				'video',
				'quote',
				'link',
				'gallery',
				'audio',
			)
		);

		add_theme_support( 'menus' );

		register_nav_menus(
			array(
			'primary'            => __( 'Primary Menu' ),
			'secondary'          => __( 'Secondary Menu' ),
			'footer_quick_links' => __( 'Footer (Quick Links)' ),
			'language'           => __( 'Language Menu' ),
			)
		);

		/**
		 * Enqueue The theme css and need js files
		 */
		function register_style_and_scripts() {
			wp_enqueue_style( 'theme-style', get_template_directory_uri() . '/dist/css/style.css' );
			wp_enqueue_script( 'bundle', get_template_directory_uri() . '/dist/js/bundle.js', array(), '', true );
		}
		add_action( 'wp_enqueue_scripts', 'register_style_and_scripts' );

		function dequeue_jquery_migrate( $scripts ) {
			if ( ! is_admin() && ! empty( $scripts->registered['jquery'] ) ) {
				$scripts->registered['jquery']->deps = array_diff(
					$scripts->registered['jquery']->deps,
					[ 'jquery-migrate' ]
				);
			}
		}
		add_action( 'wp_default_scripts', 'dequeue_jquery_migrate' );
	}

	/** This is where you can add your own functions to twig.
	 *
	 * @param string $twig get extension.
	 */
	public function add_to_twig( $twig ) {
		$twig->addExtension( new Twig\Extension\StringLoaderExtension() );
		$twig->addFilter( new Twig\TwigFilter( 'myfoo', array( $this, 'myfoo' ) ) );
		return $twig;
	}

}

new Icarus();
