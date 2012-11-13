<?php
/**
 * The Header for our theme.
 *
 * Displays all of the <head> section and everything up till <div id="main">
 *
 * @package photoboard
 * @since photoboard 1.0
 */
?><!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
<meta charset="<?php bloginfo( 'charset' ); ?>" />
<meta http-equiv='X-UA-Compatible' content='chrome=1'>
<meta name="viewport" content="width=device-width, user-scalable=no" />
<meta name="apple-mobile-web-app-capable" content="yes" />
<title><?php
	/*
	 * Print the <title> tag based on what is being viewed.
	 */
	global $page, $paged;

	wp_title( '|', true, 'right' );

	// Add the blog name.
	bloginfo( 'name' );

	// Add the blog description for the home/front page.
	$site_description = get_bloginfo( 'description', 'display' );
	if ( $site_description && ( is_home() || is_front_page() ) )
		echo " | $site_description";

	// Add a page number if necessary:
	if ( $paged >= 2 || $page >= 2 )
		echo ' | ' . sprintf( __( 'Page %s', 'photoboard' ), max( $paged, $page ) );

	?></title>
<link rel="profile" href="http://gmpg.org/xfn/11" />
<link rel="pingback" href="<?php bloginfo( 'pingback_url' ); ?>" />
<!--[if lt IE 9]>
	<script src="<?php echo get_template_directory_uri(); ?>/js/html5.js" type="text/javascript"></script>
	<style type="text/css">
		.transparent-bg {background:transparent;filter:progid:DXImageTransform.Microsoft.gradient(startColorstr=#BB000000,endColorstr=#BB000000);zoom: 1;}
		.photo-placeholder {background-image: none !important;background-color: black !important;}
	</style>
<![endif]-->

<?php wp_head(); ?>
</head>

<body <?php body_class(); ?>>

<div id="image-preloads" class="hidden">
    <img src="<?=PHOTOBOARD_THEME_PATH_SITE?>/img/blank.png" alt="" />
    <img src="<?=PHOTOBOARD_THEME_PATH_SITE?>/img/img-invalid.png" alt="" />
    <img src="<?=PHOTOBOARD_THEME_PATH_SITE?>/img/img-too-small.png" alt="" />
    <img src="<?=PHOTOBOARD_THEME_PATH_SITE?>/img/img-too-big.png" alt="" />
    <img src="<?=PHOTOBOARD_THEME_PATH_SITE?>/extlib/tt/tt.php?src=<?=PHOTOBOARD_THEME_PATH_WP?>/img/blank.png&w=100&h=100" alt="" />
    <img src="<?=PHOTOBOARD_THEME_PATH_SITE?>/extlib/tt/tt.php?src=<?=PHOTOBOARD_THEME_PATH_WP?>/img/img-invalid.png&w=100&h=100" alt="" />
    <img src="<?=PHOTOBOARD_THEME_PATH_SITE?>/extlib/tt/tt.php?src=<?=PHOTOBOARD_THEME_PATH_WP?>/img/img-too-small.png&w=100&h=100" alt="" />
    <img src="<?=PHOTOBOARD_THEME_PATH_SITE?>/extlib/tt/tt.php?src=<?=PHOTOBOARD_THEME_PATH_WP?>/img/img-too-big.png&w=100&h=100" alt="" />
    <img src="<?=PHOTOBOARD_THEME_PATH_SITE?>/img/loading.gif" alt="" />
</div>

<div id="page" class="hfeed site">
	<?php do_action( 'before' ); ?>
	<header id="masthead" class="site-header" role="banner">
		<nav role="navigation" class="site-navigation main-navigation">
			<div class="assistive-text skip-link"><a href="#content" title="<?php esc_attr_e( 'Skip to content', 'photoboard' ); ?>"><?php _e( 'Skip to content', 'photoboard' ); ?></a></div>

            <div class="navbar navbar-inverse navbar-fixed-top">
                <div class="navbar-inner">
                   <a class="brand" href="/">Findhorn Community Photoboard</a>
                   <ul class="nav pull-right">
	                   <li><a id="show-filter" href="#"><i class="icon icon-filter"></i> filter</a></li>
	               </ul>
                </div>
	            <div class="navbar-fader"></div>
            </div>

<?php /*
            <div class="navbar navbar-inverse navbar-fixed-top">
                <div class="navbar-inner">
                    <div class="container">
                        <button type="button" class="btn btn-navbar" data-toggle="collapse" data-target=".nav-collapse">
                            <span class="icon-bar"></span>
                            <span class="icon-bar"></span>
                            <span class="icon-bar"></span>
                        </button>
                        <a class="brand" href="./index.html">Bootstrap</a>
                        <div class="nav-collapse collapse">
                            <ul class="nav">
                                <li class="">
                                    <a href="./index.html">Home</a>
                                </li>
                                <li class="">
                                    <a href="./getting-started.html">Get started</a>
                                </li>
                                <li class="">
                                    <a href="./scaffolding.html">Scaffolding</a>
                                </li>
                                <li class="">
                                    <a href="./base-css.html">Base CSS</a>
                                </li>
                                <li class="active">
                                    <a href="./components.html">Components</a>
                                </li>
                                <li class="">
                                    <a href="./javascript.html">Javascript</a>
                                </li>
                                <li class="">
                                    <a href="./customize.html">Customize</a>
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
*/ ?>
		</nav><!-- .site-navigation .main-navigation -->
	</header><!-- #masthead .site-header -->

	<div id="main" class="site-main">