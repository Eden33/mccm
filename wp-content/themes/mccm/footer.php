<?php
/**
 * The template for displaying the footer.
 *
 * Contains the closing of the id=main div and all content after
 *
 * @package WordPress
 * @subpackage Twenty_Eleven
 * @since Twenty Eleven 1.0
 */
?>

	</div><!-- #main -->

	<footer id="colophon" role="contentinfo">

			<?php
				/* A sidebar in the footer? Yep. You can can customize
				 * your footer with three columns of widgets.
				 */
				if ( ! is_404() )
					get_sidebar( 'footer' );
			?>

			<div id="mccm-site-generator">
				<ul>
					<li>&copy; 2012 MCCM&nbsp;&nbsp;&nbsp;&nbsp;|</li>
					<li>Schillerstraße 4, 6800 Feldkirch&nbsp;&nbsp;&nbsp;&nbsp;|</li>
					<li>Tel +43 5522 79259&nbsp;&nbsp;&nbsp;&nbsp;|</li>
					<li>Fax +43 5522 31737&nbsp;&nbsp;&nbsp;&nbsp;|</li>
					<li><a style="color:#fff; font-style:italic; " href="mailto:zentrale@mccm-feldkirch.at">ZENTRALE@MCCM-FELDKIRCH.AT</a></li>
				</ul>
			</div>
	</footer><!-- #colophon -->
</div><!-- #page -->

<?php wp_footer(); ?>

</body>
</html>