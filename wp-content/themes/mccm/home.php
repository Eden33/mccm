<?php

/**
 * Override index.php of the parent theme.
 *  
 * Only render posts of latest year - where at least one post exists - on the home page.
 * 
 * Compared to index.php the navigation was removed because we "dynamically" interrupt the loop 
 * and this would cause pagination issues. 
 * 
 * That means the maximum number of posts displayed on the hompe page is determined by the 
 * common "Blog pages show at most" ("Blogseiten zeigen maximal") config in 
 * "Reading Settings" ("Lesen") available in wordpress backend.
 * 
 * @author Edi
 */
get_header(); ?>

		<div id="primary">
			<div id="content" role="main">

			<?php if ( have_posts() ) : ?>

				<?php

                $first_post_year = NULL;

				// Start the Loop.
				while ( have_posts() ) :
					the_post();

                    if($first_post_year == NULL) {
                        $first_post_year = get_the_date('Y');
                    }
                    $current_post_year = get_the_date('Y');

                    if($current_post_year != $first_post_year) 
                        break;
					?>

					<?php get_template_part( 'content', get_post_format() ); ?>

				<?php endwhile; ?>

			<?php else : ?>

				<article id="post-0" class="post no-results not-found">
					<header class="entry-header">
						<h1 class="entry-title"><?php _e( 'Nothing Found', 'twentyeleven' ); ?></h1>
					</header><!-- .entry-header -->

					<div class="entry-content">
						<p><?php _e( 'Apologies, but no results were found for the requested archive. Perhaps searching will help find a related post.', 'twentyeleven' ); ?></p>
						<?php get_search_form(); ?>
					</div><!-- .entry-content -->
				</article><!-- #post-0 -->

			<?php endif; ?>

			</div><!-- #content -->
		</div><!-- #primary -->

<?php get_sidebar(); ?>
<?php get_footer(); ?>
