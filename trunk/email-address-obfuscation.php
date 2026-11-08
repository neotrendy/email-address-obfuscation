<?php
/**
 * Email Address Obfuscation
 *
 * @package           EmailAddressObfuscation
 * @author            Neotrendy
 * @copyright         2022-2026 Neotrendy s.r.o.
 * @license           GPL-2.0-or-later
 *
 * @wordpress-plugin
 * Plugin Name:       Email Address Obfuscation
 * Description:       Protects email addresses and phone numbers from harvesting bots by encoding them as HTML entities, while keeping them readable for visitors.
 * Version:           1.3.0
 * Requires at least: 3.6
 * Requires PHP:      5.6
 * Author:            Neotrendy
 * Author URI:        https://www.neotrendy.com
 * Text Domain:       email-address-obfuscation
 * Domain Path:       /languages
 * License:           GPL v2 or later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.txt
 */

defined( 'ABSPATH' ) || exit;

/**
 * Builds an HTML anchor element.
 *
 * @param string $href  Escaped and obfuscated URL.
 * @param string $text  Escaped and obfuscated link text.
 * @param string $class Raw value of the shortcode `class` attribute.
 *
 * @return string HTML anchor element.
 */
function neotrendy_email_address_obfuscation_link( $href, $text, $class ) {
	$class_attribute = '' !== $class ? ' class="' . esc_attr( $class ) . '"' : '';

	return sprintf( '<a href="%1$s"%2$s>%3$s</a>', $href, $class_attribute, $text );
}

/**
 * Shortcode [obfuscate_email]: obfuscates an email address.
 *
 * - `[obfuscate_email email="..."]` outputs the address as text. An optional query
 *   string (e.g. `?subject=...`) is appended, so the output can be used inside
 *   a custom `mailto:` link.
 * - `[obfuscate_email email="..." link=true]` outputs a `mailto:` link; the query
 *   string is used in the href only.
 *
 * @param array|string $atts Associative array of attribute name and value pairs.
 *
 * @return string Obfuscated email address, HTML anchor element, or empty string for an invalid address.
 */
function neotrendy_email_address_obfuscation_shortcode( $atts ) {
	$atts = shortcode_atts( array(
		'email' => '',
		'link'  => false,
		'class' => '',
	), $atts, 'obfuscate_email' );

	$email_parts = explode( '?', $atts['email'], 2 );
	$address     = trim( $email_parts[0] );

	// Reject invalid addresses instead of letting sanitize_email() repair them (e.g. "xxx your@example.com" would become "xxxyour@example.com").
	if ( ! is_email( $address ) ) {
		return '';
	}

	$email = sanitize_email( $address );

	if ( '' === $email ) {
		return '';
	}

	// antispambot() only receives the sanitized address: sanitize_email() strips quotes and angle brackets, so it cannot break out of the HTML attribute or create tags.
	$obfuscated_email = antispambot( $email );

	// The query string is escaped exactly once and is not passed through antispambot(), which would double-encode "&".
	$query_string = isset( $email_parts[1] ) && '' !== $email_parts[1] ? '?' . esc_attr( $email_parts[1] ) : '';

	if ( ! filter_var( $atts['link'], FILTER_VALIDATE_BOOLEAN ) ) {
		return $obfuscated_email . $query_string;
	}

	return neotrendy_email_address_obfuscation_link( 'mailto:' . $obfuscated_email . $query_string, $obfuscated_email, $atts['class'] );
}

/**
 * Shortcode [obfuscate_phone]: obfuscates a phone number.
 *
 * A valid number contains 3 to 15 digits, an optional leading "+" and the separators
 * space, "-", ".", "/", "(" and ")". The number is displayed as written; the `tel:`
 * link contains only the digits and the optional leading "+".
 *
 * - `[obfuscate_phone phone="..."]` outputs the number as text.
 * - `[obfuscate_phone phone="..." link=true]` outputs a `tel:` link.
 *
 * @param array|string $atts Associative array of attribute name and value pairs.
 *
 * @return string Obfuscated phone number, HTML anchor element, or empty string for an invalid number.
 */
function neotrendy_email_address_obfuscation_phone_shortcode( $atts ) {
	$atts = shortcode_atts( array(
		'phone' => '',
		'link'  => false,
		'class' => '',
	), $atts, 'obfuscate_phone' );

	$phone = trim( $atts['phone'] );

	// Only digits, a leading "+" and the separators are allowed, so antispambot() never receives HTML-special characters.
	if ( ! preg_match( '/^\+?[0-9 ().\/-]+$/', $phone ) ) {
		return '';
	}

	$digits        = preg_replace( '/[^0-9]/', '', $phone );
	$digits_length = strlen( $digits );

	if ( $digits_length < 3 || $digits_length > 15 ) {
		return '';
	}

	$obfuscated_phone = antispambot( $phone );

	if ( ! filter_var( $atts['link'], FILTER_VALIDATE_BOOLEAN ) ) {
		return $obfuscated_phone;
	}

	$tel = ( '+' === $phone[0] ? '+' : '' ) . $digits;

	return neotrendy_email_address_obfuscation_link( 'tel:' . antispambot( $tel ), $obfuscated_phone, $atts['class'] );
}

add_shortcode( 'obfuscate_email', 'neotrendy_email_address_obfuscation_shortcode' );
add_shortcode( 'obfuscate_phone', 'neotrendy_email_address_obfuscation_phone_shortcode' );
