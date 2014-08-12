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
get_sidebar( 'footer' );
global $wpdb;
$here = $post->ID;
//echo $here;
// show the prev lesson / next lesson buttons
require_once("footer-prevnext.php");

$is_test = $wpdb->get_var("SELECT is_test FROM wp_pb_lessons WHERE id = $here");
if(!$is_test) showPrevNext(); 

echo pageSourceWidget();
?>
<!-- modifided by Marija Djokic  -->
  <div id="site-generator">
  <a href="http://imi.pmf.kg.ac.rs"><div class="cemc logo"></div></a>
 <!-- <a href='http://cemc.uwaterloo.ca/copyright.html'> 
<img src='/wp-content/plugins/pybox/files/cc.png' style='height:0.8em; vertical-align: baseline; top: 0px' /> -->
  <?php echo sprintf("2013&ndash;".strftime("%G").".</a> " );
echo sprintf(    __t('Naš sajt je besplatan servis <a %1$s target="_blank">Prirodno-matematičkog fakulteta</a> <a %2$s target="_blank">Univerziteta u Kragujevcu</a>. <br>Ukupan broj korisnika našeg sajta je: %3$s |  Ukupan broj izvršenih zadataka je: %4$s'), 'href="http://www.pmf.kg.ac.rs"', 'href="http://www.kg.ac.rs"', '<b>'.allUsers().'</b>','<b>'.allSolvedCount().'</b>'); ?>

   <div id="departmentaddress">
  <?php echo __t('PMF');?> |
  <?php echo __t('Univerzitet u Kragujevcu');?> |
  <?php echo __t('Radoja Domanovića 12');?> |
  <?php echo __t('34 000, Kragujevac');?> |
  <?php echo __t('Telefon: 034/336-223');?> |
  <a href="<?php echo cscurl('kontakt'); ?>"><?php 
  echo __t('Kontaktirajte nas');?></a>
   </div>

  </div> 

 </footer><!-- #colophon -->
</div><!-- #page -->


<?php wp_footer(); ?>

</body>
</html>
