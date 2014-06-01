<?php
/**
 * The template for displaying 404 pages (Not Found).
 *
 * @package WordPress
 * @subpackage Twenty_Eleven
 * @since Twenty Eleven 1.0
 */

$niceurl = '<pre>'."\n".htmlspecialchars($_SERVER["REQUEST_URI"]).'</pre>';
get_header(); ?>

	<div id="primary">
		<div id="content" role="main">

			<article id="post-0" class="post error404 not-found">
				<header class="entry-header">
					<h1 class="entry-title"><?php echo __t( 'Greška 404.' ); ?></h1>
				</header>

				<div class="entry-content">
					<p>
<?php      echo sprintf(__t("Ne možemo ništa pronaći na adresi %s"), $niceurl)." ".
	   sprintf(__t('Probajte sa drugom adresom, ili <a %1$s>nas kontaktirajte</a>.'), 'href="'.cscurl('contact').'"');
?></p>

<?php echo get_search_form(); 
// pulls from google search plugin if installed, default search otherwise
 ?>

				</div><!-- .entry-content -->
			</article><!-- #post-0 -->

		</div><!-- #content -->
	</div><!-- #primary -->

<?php get_footer(); ?>
