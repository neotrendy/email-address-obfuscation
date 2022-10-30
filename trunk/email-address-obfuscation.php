<?php
/**
 * Email Address Obfuscation
 *
 * @package           EmailAddressObfuscation
 * @author            Neotrendy
 * @copyright         2022 Neotrendy s.r.o.
 * @license           GPL-2.0-or-later
 *
 * @wordpress-plugin
 * Plugin Name:       Email Address Obfuscation
 * Description:       Email Address Obfuscation prevents email harvesting by hiding email address appearing in your pages, while remaining visible to your site visitors.
 * Version:           0.9.2
 * Requires at least: 2.5
 * Requires PHP:      5.6
 * Author:            Neotrendy
 * Author URI:        https://neotrendy.com
 * Text Domain:       email-address-obfuscation
 * License:           GPL v2 or later
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 */

/**
 * Adds shortcode to obfuscate email address.
 *
 * @param array $atts Associative array of attribute name and value pairs.
 * @return string Converted email address or HTML anchor element.
 */
function neotrendy_email_address_obfuscation_shortcode( $atts ) {
    $atts = shortcode_atts( array(
        'email' => '',
        'link' => false,
        'class' => '',
    ), $atts );

    if ( $atts['link'] ) {
        return sprintf( '<a href="mailto:%1$s" title="%1$s" class="%2$s">%1$s</a>', antispambot( $atts['email'] ), $atts['class']);
    } else {
        return antispambot( $atts['email'] );
    }
}
add_shortcode( 'obfuscate_email', 'neotrendy_email_address_obfuscation_shortcode' );
