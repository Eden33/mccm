<?php
/** 
 * Template Name: Direct Subpages list
 * Template Post Type: page
 * 
 * Template for displaying direct sub pages in list form.
 * Used at the moment from "Verein", "Rennen" and "Archiv" page.
 */
?>

<?php
get_header(); ?>
		<div id="primary">
			<div id="content" role="main">
				<?php while ( have_posts() ) : the_post(); ?>

					<?php get_template_part( 'content', 'page' ); ?>

					<?php
					  $children = wp_list_pages('title_li=&child_of='.$post->ID.'&echo=0&depth=1');
					  if ($children) { ?>
					  <ul class="mccm-list-style subpages-list subpages-list-post-id-<?php echo $post->ID?>">
					  <?php echo $children; ?>
					  </ul>
					<?php } ?>
					
					<?php comments_template( '', true ); ?>

				<?php endwhile; // end of the loop. ?>
			</div><!-- #content -->
		</div><!-- #primary -->

<?php get_footer(); ?>