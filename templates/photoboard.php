<?php
/**
 * Template Name: Photoboard
 *
 * Description: Twenty Twelve loves the no-sidebar look as much as
 * you do. Use this page template to remove the sidebar from any page.
 *
 * Tip: to remove the sidebar from all posts and pages simply remove
 * any active widgets from the Main Sidebar area, and the sidebar will
 * disappear everywhere.
 *
 * @package WordPress
 * @subpackage Twenty_Twelve
 * @since Twenty Twelve 1.0
 */

get_header();
?>
<div id="primary" class="content-area" xmlns="http://www.w3.org/1999/html">
    <div id="main-container">

	    <div id='full-wrapper'>
            <aside id='ajax-full' class='full-container full-info'></aside>
		    <aside id='help-full' class='full-container full-info'>
                <article class="transparent-bg">
                    <h1 class="name primary">How to use the photoboard <i class="icon-info-sign"></i></h1>
                    <div class="primary hide-on-hover">Click <span class="when-small-hide">or hover here </span>for more information...</div>
                    <div class="secondary"><i class="icon icon-user"></i> Click on any thumb to see the full sized image.</div>
                    <div class="secondary"><i class="icon icon-hand-right"></i> Click on <span class="when-small-hide">or hover your mouse over </span>the full sized image to see more information abou that person.</div>
                    <div class="secondary when-small-hide"><i class="icon icon-search"></i> Click on the filter icon on the right of the top menubar to filter which thumbnails are shown.</div>
                    <div class="secondary when-small-show"><i class="icon icon-search"></i> Click on the filter icon at the top to filter which thumbnails are shown.</div>
                </article>
	        </aside>
            <div class="full-img"><img src="<?=PHOTOBOARD_THEME_PATH_SITE?>/img/blank.png" alt=""/></div>
	    </div>

        <div id="photoboard-thumbs"></div>

    </div>
</div>
<div id="js-msg">
    <p>Please enable javascript for full functionality</p>
</div>
<script type="text/javascript">document.getElementById('js-msg').style.display = 'none';</script>
<div id="sorry">
	<div class="sorry-text transparent-bg">
		<h1>Sorry, the photoboard won't work with this browser</h1>
		<p>The photoboard currently works with the following browsers:</p>
		<ul>
			<li>Chrome</li>
			<li>Safari 4 or better</li>
            <li>Firefox 4 or better</li>
            <li>Internet Explorer 8 or better</li>
		</ul>
    </div>
    <img src="<?=PHOTOBOARD_THEME_PATH_SITE?>/img/blank.png" alt=""/>
</div>
<aside id='filters' class='closeable'>
    <button type="button" class="close" data-dismiss="alert"><i class="icon-remove-circle"></i></button>
    <h1 class='assistive-text'>Filter the Photoboard</h1>
    <p><i>Uncheck the filters to hide any thumbs which do not match.</i></p>

	<h2>Current/past community members</h2>
    <div class="check-buttons"><span class="label label-success check-all">all</span> <span class="label label-important check-none">none</span></div>
	<?=get_filter_checkboxes( array('current'=>'Current community member','not-current'=>'Former community member') )?>

	<h2>Affiliation</h2>
    <div class="check-buttons"><span class="label label-success check-all">all</span> <span class="label label-important check-none">none</span></div>
	<?=get_filter_checkboxes( get_affiliation_filters() )?>

    <h2>Location</h2>
    <div class="check-buttons"><span class="label label-success check-all">all</span> <span class="label label-important check-none">none</span></div>
	<?=get_filter_checkboxes( get_location_filters() )?>
</aside>
<?php get_footer(); ?>