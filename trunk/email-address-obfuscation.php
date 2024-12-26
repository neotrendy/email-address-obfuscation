<?php
/**
 * Email Address Obfuscation
 *
 * @package           EmailAddressObfuscation
 * @author            Neotrendy
 * @copyright         2022-2024 Neotrendy s.r.o.
 * @license           GPL-2.0-or-later
 *
 * @wordpress-plugin
 * Plugin Name:       Email Address Obfuscation
 * Description:       Email Address Obfuscation prevents email harvesting by hiding email address appearing in your pages, while remaining visible to your site visitors.
 * Version:           1.2.0
 * Requires at least: 2.5
 * Requires PHP:      5.6
 * Author:            Neotrendy
 * Author URI:        https://www.neotrendy.com
 * Text Domain:       email-address-obfuscation
 * Domain Path:       /languages
 * License:           GPL v2 or later
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 */

/**
 * Adds shortcode to obfuscate email address.
 *
 * @param array $atts Associative array of attribute name and value pairs.
 *
 * @return string Converted email address or HTML anchor element.
 */
function neotrendy_email_address_obfuscation_shortcode( $atts ) {
	$atts = shortcode_atts( array(
		'email' => '',
		'link'  => false,
		'class' => '',
	), $atts, 'obfuscate_email' );

	$email_parts      = explode( '?', $atts['email'], 2 );
	$query_parameters = isset( $email_parts[1] ) ? '?' . esc_attr( $email_parts[1] ) : '';
	$email            = sanitize_email( $email_parts[0] ) . $query_parameters;

	if ( empty( $email ) ) {
		return '';
	}

	$obfuscated_email = antispambot( $email );

	if ( filter_var( $atts['link'], FILTER_VALIDATE_BOOLEAN ) ) {
		$class = ! empty( $atts['class'] ) ? ' class="' . esc_attr( $atts['class'] ) . '"' : '';

		return sprintf( '<a href="mailto:%1$s" title="%1$s"%2$s>%1$s</a>', $obfuscated_email, $class );
	}

	return $obfuscated_email;
}

add_shortcode( 'obfuscate_email', 'neotrendy_email_address_obfuscation_shortcode' );
