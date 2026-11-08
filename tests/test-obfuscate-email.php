<?php
/**
 * Tests for the [obfuscate_email] shortcode.
 *
 * @package EmailAddressObfuscation
 */

require_once __DIR__ . '/class-shortcode-testcase.php';

class Test_Obfuscate_Email extends Neotrendy_Shortcode_TestCase {

	/**
	 * Calls the shortcode callback directly with the given attributes.
	 *
	 * @param array $atts Shortcode attributes.
	 *
	 * @return string
	 */
	private function render( $atts ) {
		return neotrendy_email_address_obfuscation_shortcode( $atts );
	}

	public function test_shortcode_is_registered() {
		$this->assertTrue( shortcode_exists( 'obfuscate_email' ) );
	}

	public function test_plain_address_is_displayed_and_encoded() {
		$output = do_shortcode( '[obfuscate_email email="your@example.com"]' );

		$this->assertSame( 'your@example.com', $this->assert_plain_text( $output ) );
		$this->assertStringNotContainsString( '@', $output, 'The "@" must always be encoded.' );
	}

	public function test_link() {
		$link = $this->assert_single_link( do_shortcode( '[obfuscate_email email="your@example.com" link=true]' ) );

		$this->assertSame( array( 'href' ), $this->attribute_names( $link ), 'Only href is expected (no title).' );
		$this->assertSame( 'mailto:your@example.com', $link->getAttribute( 'href' ) );
		$this->assertSame( 'your@example.com', $link->textContent );
	}

	/**
	 * @dataProvider data_link_values
	 */
	public function test_link_attribute_values( $value, $expect_link ) {
		$output = $this->render( array( 'email' => 'your@example.com', 'link' => $value ) );

		$this->assertSame( $expect_link ? array( 'a' ) : array(), $this->element_names( $output ) );
	}

	public function data_link_values() {
		return array(
			'true'  => array( 'true', true ),
			'1'     => array( '1', true ),
			'yes'   => array( 'yes', true ),
			'on'    => array( 'on', true ),
			'TRUE'  => array( 'TRUE', true ),
			'false' => array( 'false', false ),
			'0'     => array( '0', false ),
			'no'    => array( 'no', false ),
			'empty' => array( '', false ),
		);
	}

	public function test_class() {
		$link = $this->assert_single_link( do_shortcode( '[obfuscate_email email="your@example.com" link=true class="my-class another-class"]' ) );

		$this->assertSame( array( 'href', 'class' ), $this->attribute_names( $link ) );
		$this->assertSame( 'my-class another-class', $link->getAttribute( 'class' ) );
	}

	public function test_subject_is_in_href_only() {
		$link = $this->assert_single_link( do_shortcode( "[obfuscate_email email='your@example.com?subject=My custom email subject' link=true]" ) );

		$this->assertSame( 'mailto:your@example.com?subject=My custom email subject', $link->getAttribute( 'href' ) );
		$this->assertSame( 'your@example.com', $link->textContent );
	}

	/**
	 * Regression: in 1.2.0 "&" was double-encoded, so "body" did not reach the email client.
	 */
	public function test_multiple_query_parameters() {
		$link = $this->assert_single_link( do_shortcode( '[obfuscate_email email="your@example.com?subject=Hi&body=Text" link=true]' ) );

		$this->assertSame( 'mailto:your@example.com?subject=Hi&body=Text', $link->getAttribute( 'href' ) );
	}

	/**
	 * Backward compatibility: without a link the subject is output after the address.
	 */
	public function test_plain_text_keeps_subject() {
		$output = do_shortcode( '[obfuscate_email email="your@example.com?subject=Hi&body=Text"]' );

		$this->assertSame( 'your@example.com?subject=Hi&body=Text', $this->assert_plain_text( $output ) );
	}

	/**
	 * Custom link text: the shortcode is used inside the href of the user's own link.
	 */
	public function test_shortcode_inside_own_link() {
		$output = do_shortcode( '<a href="mailto:[obfuscate_email email=\'your@example.com?subject=Hi&body=Text\']">Write to us</a>' );
		$link   = $this->assert_single_link( $output );

		$this->assertSame( 'mailto:your@example.com?subject=Hi&body=Text', $link->getAttribute( 'href' ) );
		$this->assertSame( 'Write to us', $link->textContent );
	}

	/**
	 * @dataProvider data_invalid_emails
	 */
	public function test_invalid_email_outputs_nothing( $email ) {
		$this->assertSame( '', $this->render( array( 'email' => $email, 'link' => 'true' ) ) );
	}

	public function data_invalid_emails() {
		return array(
			'empty'                 => array( '' ),
			'space inside'          => array( 'xxx your@example.com ' ),
			'no domain dot'         => array( 'your@example' ),
			'no at sign'            => array( 'your.example.com' ),
			'invalid with subject'  => array( 'not-an-email?subject=Hi' ),
			'subject only'          => array( '?subject=Hi' ),
			'html in address'       => array( '<b>your</b>@example.com' ),
		);
	}

	public function test_missing_email_attribute_outputs_nothing() {
		$this->assertSame( '', do_shortcode( '[obfuscate_email link=true]' ) );
	}

	public function test_surrounding_spaces_are_ignored() {
		$link = $this->assert_single_link( $this->render( array( 'email' => ' your@example.com ', 'link' => 'true' ) ) );

		$this->assertSame( 'mailto:your@example.com', $link->getAttribute( 'href' ) );
	}

	/**
	 * CVE-2024-11935: the class attribute must not break out of the attribute value.
	 *
	 * @dataProvider data_class_xss_payloads
	 */
	public function test_class_xss( $payload ) {
		foreach ( array( 'your@example.com', 'your@example.com?subject=Hi&body=Text' ) as $email ) {
			$link = $this->assert_single_link( $this->render( array( 'email' => $email, 'link' => 'true', 'class' => $payload ) ) );

			$this->assert_no_injected_attributes( $link );
			$this->assertSame( html_entity_decode( $payload, ENT_QUOTES | ENT_HTML5, 'UTF-8' ), $link->getAttribute( 'class' ) );
		}
	}

	/**
	 * CVE-2024-11935 exactly as on the manual test page (shortcode in post content).
	 */
	public function test_class_xss_in_content() {
		$link = $this->assert_single_link( do_shortcode( '[obfuscate_email email="your@example.com" link=\'true\' class=\'test"autofocus tabindex=1 onfocus="alert(1)\']' ) );

		$this->assert_no_injected_attributes( $link );
	}

	/**
	 * @dataProvider data_query_xss_payloads
	 */
	public function test_query_string_xss( $query ) {
		$email = 'your@example.com?' . $query;

		$link = $this->assert_single_link( $this->render( array( 'email' => $email, 'link' => 'true' ) ) );
		$this->assert_no_injected_attributes( $link );

		$this->assert_plain_text( $this->render( array( 'email' => $email ) ) );
	}

	public function data_query_xss_payloads() {
		return array(
			'close tag and script' => array( 'subject="><script>alert(1)</script>' ),
			'encoded quote'        => array( 'subject=&#x22; onmouseover=&#x22;alert(1)' ),
			'single quote'         => array( "subject=' onmouseover='alert(1)" ),
			'img tag'              => array( 'subject=<img src=x onerror=alert(1)>' ),
		);
	}

	public function test_shortcode_atts_filter_is_applied() {
		$filter = function ( $out ) {
			$out['class'] = 'from-filter';
			return $out;
		};
		add_filter( 'shortcode_atts_obfuscate_email', $filter );

		$link = $this->assert_single_link( do_shortcode( '[obfuscate_email email="your@example.com" link=true]' ) );

		remove_filter( 'shortcode_atts_obfuscate_email', $filter );
		$this->assertSame( 'from-filter', $link->getAttribute( 'class' ) );
	}
}
