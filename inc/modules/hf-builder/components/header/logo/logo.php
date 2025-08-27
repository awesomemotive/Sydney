<?php
/**
 * Header/Footer Builder
 * Logo Component
 * 
 * @package Sydney_Pro
 * @var array $params Contains component data
 */ 

?>

<div class="shfb-builder-item shfb-component-logo" data-component-id="logo">
    <?php $this->customizer_edit_button(); ?>
    <div class="site-branding" <?php sydney_get_schema( 'logo' ); ?>>
        <?php
        if ( get_theme_mod('site_logo') ) : ?>
            <?php
                $logo_id    = attachment_url_to_postid( get_theme_mod( 'site_logo' ) );
                $logo_attrs = wp_get_attachment_image_src( $logo_id, 'large' );
            ?>						
            <a href="<?php echo esc_url( home_url( '/' ) ); ?>" title="<?php bloginfo('name'); ?>"><img width="<?php echo esc_attr( $logo_attrs[1] ); ?>" height="<?php echo esc_attr( $logo_attrs[2] ); ?>" class="site-logo" src="<?php echo esc_url(get_theme_mod('site_logo')); ?>" alt="<?php bloginfo('name'); ?>" <?php sydney_do_schema( 'logo' ); ?> /></a>
        <?php endif;

        if ( get_theme_mod('logo_site_title', 0) || empty( get_theme_mod('site_logo') ) ) :
        echo '<div>';
        if ( ( is_front_page() ) && $params[ 'device' ] !== 'mobile' ) :
            ?>
            <h1 class="site-title"><a href="<?php echo esc_url( home_url( '/' ) ); ?>" rel="home"><?php bloginfo( 'name' ); ?></a></h1>
            <?php
        else :
            ?>
            <p class="site-title"><a href="<?php echo esc_url( home_url( '/' ) ); ?>" rel="home"><?php bloginfo( 'name' ); ?></a></p>
            <?php
        endif;
        $sydney_description = get_bloginfo( 'description', 'display' );
        if ( $sydney_description || is_customize_preview() ) :
            ?>
            <p class="site-description"><?php echo $sydney_description; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></p>
        <?php endif;
        echo '</div>';
        endif; ?>
    </div><!-- .site-branding -->
</div>