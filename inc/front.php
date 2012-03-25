<?php
/**
 * Front end functionality for Shortcode Disabler
 * 
 * @author Christopher Davis <http://christopherdavis.me>
 * @package Shortcode Disabler
 * @since 0.1
 */

add_action( 'template_redirect', 'cd_scd_add_shortcodes' );
/**
 * Adds the shortcodes that the user set up in the admin area
 * 
 * @uses get_option
 * @uses add_shortcode
 * @since 0.1
 */
function cd_scd_add_shortcodes()
{
    $codes = get_option( 'shortcode_disabler_options', array() );
    if( ! $codes ) return;
    foreach( array_keys( $codes ) as $code )
    {
        add_shortcode( $code, 'cd_scd_shortcode_cb' );
    }
}

/**
 * Callback function for disabled shortcodes
 * 
 * @uses get_option
 * @since 0.1
 */
function cd_scd_shortcode_cb( $args, $content, $tag )
{
    $codes = get_option( 'shortcode_disabler_options', array() );
    $display_content = isset( $codes[$tag] ) && 'on' == $codes[$tag] ? true : false;
    if( $content && $display_content )
    {
        return $content;
    }
    else
    {
        return '';
    }
}
