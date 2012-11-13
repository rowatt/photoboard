<?php
/**
 * photoboard functions and definitions
 *
 * @package photoboard
 * @since photoboard 1.0
 */

//define( 'PHOTOBOARD_NO_CACHE', FALSE );

//max height * width for photo to avoid out of memory errors
define( 'PHOTOBOARD_MAX_IMAGE_SIZE', 10000000 );

//min height or width for photo
define( 'PHOTOBOARD_MIN_IMAGE_SIZE', 100 );


//initialise constants
define( 'PHOTOBOARD_THEME_URL', get_stylesheet_directory_uri() );
define( 'PHOTOBOARD_THEME_PATH_SITE', '/board/wp-content/themes/photoboard' );
define( 'PHOTOBOARD_THEME_PATH_WP', '/wp/wp-content/themes/photoboard' );
define( 'PHOTOBOARD_PIX_PATH', '/pix/' );
define( 'PHOTOBOARD_PIX_DIR', $_SERVER['DOCUMENT_ROOT'] . '/pix/' );
define( 'TT_URL', PHOTOBOARD_THEME_URL . '/extlib/tt/tt.php' );
define( 'TT_URL_2', str_replace( '//photoboard', '//img.photoboard', TT_URL ));

//bit of a hack - makes sure we have a URL for the real path to images
//using the multisite path confuses TT sometimes
define( 'PHOTOBOARD_RAW_THEME_URL', str_replace( '/board/', '/wp/', get_stylesheet_directory_uri() ) );


//path to where photos are stored by gravity forms
//define( 'PHOTOBOARD_GRAVITY_DIR', '/wp/wp-content/uploads/gravity_forms/2-eeb7716ba96df13867debe99f05ec6f1' );
define( 'PHOTOBOARD_GRAVITY_DIR', '/wp/wp-content/uploads/gravity_forms/1-a13b2848a36456456ab5f25b33f14fcc' );

//prefix to strip from photo url location when importing
define( 'PHOTOBOARD_PIX_URL_STRIP', 'http://photoboard.findhorn.cc' . PHOTOBOARD_GRAVITY_DIR );

//if TRUE will import all records overwriting any existing ones
define ('PHOTOBOARD_IMPORT_ALL', TRUE );

if( 'photoboard.dev' == $_SERVER['SERVER_NAME'] )
{
	define( 'PHOTOBOARD_IMPORT_FILE', '/Users/mark/Sites/photoboard/import.csv');
	define( 'PHOTOBOARD_PIX_IMPORT_DIR', '/Users/mark/Sites/photoboard/photo_import' . PHOTOBOARD_GRAVITY_DIR);
	define( 'PHOTOBOARD_OLD_PIX_IMPORT_DIR', '/Users/mark/Documents/Dox/Business/Clients/NFA/Photoboard/existing_photos' );
}
else
{
	//absolute path to csv file used for import
	define( 'PHOTOBOARD_IMPORT_FILE', '/home/httpd/vhosts/photoboard/import.csv');

	//absolute path to dir where pix are imported from - replaces URL constant above in file location
	define( 'PHOTOBOARD_PIX_IMPORT_DIR', '/home/httpd/vhosts/photoboard/sites/live' . PHOTOBOARD_GRAVITY_DIR);

	//where are existing photoboard pix stored
	define( 'PHOTOBOARD_OLD_PIX_IMPORT_DIR', '/home/httpd/vhosts/photoboard/existing_photos' );

	//separate url for serving large images
	define( 'PHOTOBOARD_IMG_SITE', 'http://img.photoboard.findhorn.cc');
}

//define cachebuster constant if not already defined
//define as false above to disable
if( !defined('PHOTOBOARD_NO_CACHE') )
	define( 'PHOTOBOARD_NO_CACHE', "no-cache-" . microtime(TRUE) );

foreach( scandir(__DIR__ . '/functions') as $file )
{
	if( '.'==$file || '..'==$file || substr( $file, -4 )!='.php' ) continue;
	require_once("functions/$file");
}

/**
 * Set the content width based on the theme's design and stylesheet.
 *
 * @since photoboard 1.0
 */
if ( ! isset( $content_width ) )
	$content_width = 640; /* pixels */

if ( ! function_exists( 'photoboard_setup' ) ):
/**
 * Sets up theme defaults and registers support for various WordPress features.
 *
 * Note that this function is hooked into the after_setup_theme hook, which runs
 * before the init hook. The init hook is too late for some features, such as indicating
 * support post thumbnails.
 *
 * @since photoboard 1.0
 */
function photoboard_setup() {

	/**
	 * Custom template tags for this theme.
	 */
	require( get_template_directory() . '/inc/template-tags.php' );

	/**
	 * Custom functions that act independently of the theme templates
	 */
	//require( get_template_directory() . '/inc/tweaks.php' );

	/**
	 * Custom Theme Options
	 */
	//require( get_template_directory() . '/inc/theme-options/theme-options.php' );

	/**
	 * Make theme available for translation
	 * Translations can be filed in the /languages/ directory
	 * If you're building a theme based on photoboard, use a find and replace
	 * to change 'photoboard' to the name of your theme in all the template files
	 */
	load_theme_textdomain( 'photoboard', get_template_directory() . '/languages' );

	/**
	 * Add default posts and comments RSS feed links to head
	 */
	add_theme_support( 'automatic-feed-links' );

	/**
	 * Enable support for Post Thumbnails
	 */
	add_theme_support( 'post-thumbnails' );

	/**
	 * This theme uses wp_nav_menu() in one location.
	 */
	register_nav_menus( array(
		'primary' => __( 'Primary Menu', 'photoboard' ),
	) );

	/**
	 * Add support for the Aside Post Formats
	 */
	add_theme_support( 'post-formats', array( 'aside', ) );
}
endif; // photoboard_setup
add_action( 'after_setup_theme', 'photoboard_setup' );

/**
 * Register widgetized area and update sidebar with default widgets
 *
 * @since photoboard 1.0
 */
function photoboard_widgets_init() {
	register_sidebar( array(
		'name' => __( 'Sidebar', 'photoboard' ),
		'id' => 'sidebar-1',
		'before_widget' => '<aside id="%1$s" class="widget %2$s">',
		'after_widget' => '</aside>',
		'before_title' => '<h1 class="widget-title">',
		'after_title' => '</h1>',
	) );
}
add_action( 'widgets_init', 'photoboard_widgets_init' );

/**
 * Enqueue scripts and styles
 */
function photoboard_scripts() {
	wp_enqueue_style( 'style', PHOTOBOARD_THEME_URL . '/css/screen.css', NULL, PHOTOBOARD_NO_CACHE );
	wp_enqueue_style( 'font-merienda', 'http://fonts.googleapis.com/css?family=Merienda+One', 'style');

	//wp_enqueue_script( 'small-menu', PHOTOBOARD_THEME_URL . '/js/small-menu.js', array( 'jquery' ), '20120206', true );

	wp_enqueue_script( 'photoboard', PHOTOBOARD_THEME_URL . '/js/photoboard.js', array( 'jquery' ), PHOTOBOARD_NO_CACHE, true );
	wp_enqueue_script( 'bootstrap', PHOTOBOARD_THEME_URL . '/js/bootstrap.min.js', array( 'jquery' ), '20120206', true );

	$photoboard_js_vars = array(
		'ajaxurl' => admin_url( 'admin-ajax.php' )
	);
	wp_localize_script( 'photoboard', 'photoboard_params', $photoboard_js_vars );

	if ( is_singular() && comments_open() && get_option( 'thread_comments' ) ) {
		wp_enqueue_script( 'comment-reply' );
	}

	if ( is_singular() && wp_attachment_is_image() ) {
		wp_enqueue_script( 'keyboard-image-navigation', get_template_directory_uri() . '/js/keyboard-image-navigation.js', array( 'jquery' ), '20120202' );
	}
}
add_action( 'wp_enqueue_scripts', 'photoboard_scripts' );

/**
 * Implement the Custom Header feature
 */
//require( get_template_directory() . '/inc/custom-header.php' );
