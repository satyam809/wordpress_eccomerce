<?php
/**
 * The template for displaying the footer.
 *
 * @package OceanWP WordPress theme
 */ ?>

        </main><!-- #main -->

        <?php do_action( 'ocean_after_main' ); ?>

        <?php do_action( 'ocean_before_footer' ); ?>

        <?php
        // Elementor `footer` location
        if ( ! function_exists( 'elementor_theme_do_location' ) || ! elementor_theme_do_location( 'footer' ) ) { ?>

            <?php do_action( 'ocean_footer' ); ?>
            
        <?php } ?>

        <?php do_action( 'ocean_after_footer' ); ?>
                
    </div><!-- #wrap -->

    <?php do_action( 'ocean_after_wrap' ); ?>

</div><!-- #outer-wrap -->

<?php do_action( 'ocean_after_outer_wrap' ); ?>

<?php
// If is not sticky footer
if ( ! class_exists( 'Ocean_Sticky_Footer' ) ) {
    get_template_part( 'partials/scroll-top' );
} ?>

<?php
// Search overlay style
if ( 'overlay' == oceanwp_menu_search_style() ) {
    get_template_part( 'partials/header/search-overlay' );
} ?>

<?php
// If sidebar mobile menu style
if ( 'sidebar' == oceanwp_mobile_menu_style() ) {
    
    // Mobile panel close button
    if ( get_theme_mod( 'ocean_mobile_menu_close_btn', true ) ) {
        get_template_part( 'partials/mobile/mobile-sidr-close' );
    } ?>

    <?php
    // Mobile Menu (if defined)
    get_template_part( 'partials/mobile/mobile-nav' ); ?>

    <?php
    // Mobile search form
    if ( get_theme_mod( 'ocean_mobile_menu_search', true ) ) {
        get_template_part( 'partials/mobile/mobile-search' );
    }

} ?>

<?php
// If full screen mobile menu style
if ( 'fullscreen' == oceanwp_mobile_menu_style() ) {
    get_template_part( 'partials/mobile/mobile-fullscreen' );
} ?>

<?php wp_footer(); ?>
<!--Start of Tawk.to Script-->
    <script type="text/javascript">
    var Tawk_API=Tawk_API||{}, Tawk_LoadStart=new Date();
    (function(){
    var s1=document.createElement("script"),s0=document.getElementsByTagName("script")[0];
    s1.async=true;
    s1.src='https://embed.tawk.to/5d88edfe9f6b7a4457e3240a/default';
    s1.charset='UTF-8';
    s1.setAttribute('crossorigin','*');
    s0.parentNode.insertBefore(s1,s0);
    })();
    </script>
    <!--End of Tawk.to Script-->
</body>
</html>