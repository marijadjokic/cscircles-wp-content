	

    <?php
    /**
     * Template Name: Blank Page Template
     *
     * @package WordPress
     * @subpackage Twenty_Eleven
     */ ?>
    <?php // essential code from header.php ?>
    <?php
    /**
     * The Header for our theme.
     *
     * Displays all of the <head> section and everything up till <div id="main">
     *
     * @package WordPress
     * @subpackage Twenty_Eleven
     * @since Twenty Eleven 1.0
     */
    ?><!DOCTYPE html>
    <!--[if IE 6]>
    <html id="ie6" <?php language_attributes(); ?>>
    <![endif]-->
    <!--[if IE 7]>
    <html id="ie7" <?php language_attributes(); ?>>
    <![endif]-->
    <!--[if IE 8]>
    <html id="ie8" <?php language_attributes(); ?>>
    <![endif]-->
    <!--[if !(IE 6) | !(IE 7) | !(IE 8)  ]><!-->
    <html <?php language_attributes(); ?>>
    <!--<![endif]-->
    <head>
    <meta charset="<?php bloginfo( 'charset' ); ?>" />
    <meta name="viewport" content="width=device-width" />
    <title><?php
            /*
             * Print the <title> tag based on what is being viewed.
             */
            global $page, $paged;
     
            wp_title( '|', true, 'right' );
     
            // Add the blog name.
            //bloginfo( 'name' );
     
            // Add the blog description for the home/front page.
            /*$site_description = get_bloginfo( 'description', 'display' );
            if ( $site_description && ( is_home() || is_front_page() ) )
                    echo " | $site_description";*/
     
            // Add a page number if necessary:
            if ( $paged >= 2 || $page >= 2 )
                    echo ' | ' . sprintf( __( 'Page %s', 'twentyeleven' ), max( $paged, $page ) );
     
            ?></title>
    <link rel="profile" href="http://gmpg.org/xfn/11" />
    <link rel="stylesheet" type="text/css" media="all" href="<?php bloginfo( 'stylesheet_url' ); ?>" />
    <link rel="pingback" href="<?php bloginfo( 'pingback_url' ); ?>" />
    <!--[if lt IE 9]>
    <script src="<?php echo get_template_directory_uri(); ?>/js/html5.js" type="text/javascript"></script>
    <![endif]-->
    <?php
            if ( is_singular() && get_option( 'thread_comments' ) )
                    wp_enqueue_script( 'comment-reply' );
            wp_head();
    ?>
    </head>
     
    <body <?php body_class(); ?>>
    <div id="page" class="hfeed blank-page">
            <div id="main">
     
    <?php //end of essential header.php code ?>
     
    <?php //essential code of page.php ?>
     
                    <div id="primary">
                            <div id="content" role="main">
     
                                    <?php while ( have_posts() ) : the_post(); ?>
     
                                            <?php get_template_part( 'content', 'page' ); ?>
     
                                            <?php //comments_template( '', true ); ?>
     
                                    <?php endwhile; // end of the loop. ?>
     
                            </div><!-- #content -->
                    </div><!-- #primary -->
     
    <?php //end of essential code of page.php ?>
     
    <?php //essential code of footer.php ?>
            </div><!-- #main -->
     
    <div style="clear:both;float:none;"></div>
    <?php //to clear the floats and stretch the #page background to the bottom/// ?>
     
    </div><!-- #page -->
     
    <?php wp_footer(); ?>
     
    </body>
    </html>
    <?php //end of essential code of footer.php ?>


