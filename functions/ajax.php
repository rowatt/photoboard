<?php

function photoboard_ajax_photofull()
{
	$id = empty( $_REQUEST['id'] ) ? 0 : (int) $_REQUEST['id'];
	$person = new Photoboard_Person($id);

	echo $person->get_full_img_info();
?>
	<img src="<?=PHOTOBOARD_THEME_PATH_SITE?>/img/loading.gif" class="loading" alt="loading..." />
<?php
	exit();
}
add_action('wp_ajax_photofull', 'photoboard_ajax_photofull');
add_action('wp_ajax_nopriv_photofull', 'photoboard_ajax_photofull');

function photoboard_ajax_photothumbs()
{
	$people = new Photoboard_People();
	echo $people->get_html_thumbs();
	exit();
}
add_action('wp_ajax_photothumbs', 'photoboard_ajax_photothumbs');
add_action('wp_ajax_nopriv_photothumbs', 'photoboard_ajax_photothumbs');

/* EOF */