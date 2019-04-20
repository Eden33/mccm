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

					<li>&copy; <?= date("Y"); ?> MCCM&nbsp;&nbsp;&nbsp;&nbsp;|</li>

					<li>Breite Lache 1, 6800 Feldkirch&nbsp;&nbsp;&nbsp;&nbsp;|</li>  

					<li><a style="color:#fff; font-style:italic; " href="mailto:zentrale@mccm-feldkirch.at">ZENTRALE@MCCM-FELDKIRCH.AT&nbsp;&nbsp;&nbsp;&nbsp;|</a></li>
                                        
                                        <li>ZVR-Zahl: 557710156</li>

				</ul>

			</div>

	</footer><!-- #colophon -->

</div><!-- #page -->



<?php wp_footer(); ?>



</body>

</html>