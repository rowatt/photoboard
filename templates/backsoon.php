<?php
/**
 * Template Name: Back Soon
 */

get_header();
?>
<div id="offline">
	<div class="sorry-text transparent-bg">
		<?php while ( have_posts() ) : the_post(); ?>
		<?php the_content(); ?>
		<?php endwhile; // end of the loop. ?>
    </div>
    <img src="<?=PHOTOBOARD_THEME_PATH_SITE?>/img/blank.png" alt=""/>
</div>
<?php get_footer(); ?>