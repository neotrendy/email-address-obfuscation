<?php
/**
 * Tests for the [obfuscate_phone] shortcode.
 *
 * @package EmailAddressObfuscation
 */

require_once __DIR__ . '/class-shortcode-testcase.php';

class Test_Obfuscate_Phone extends Neotrendy_Shortcode_TestCase {

	/**
	 * Calls the shortcode callback directly with the given attributes.
	 *
	 * @param array $atts Shortcode attributes.
	 *
	 * @return string
	 */
	private function render( $atts ) {
		return neotrendy_email_address_obfuscation_phone_shortcode( $atts );
	}

	public function test_shortcode_is_registered() {
		$this->assertTrue( shortcode_exists( 'obfuscate_phone' ) );
	}

	public function test_plain_number_is_displayed_as_written() {
		$output = do_shortcode( '[obfuscate_phone phone="+421 000 000 000"]' );

		$this->assertSame( '+421 000 000 000', $this->assert_plain_text( $output ) );
	}

	public function test_link() {
		$link = $this->assert_single_link( do_shortcode( '[obfuscate_phone phone="+421 000 000 000" link=true class="my-class"]' ) );

		$this->assertSame( array( 'href', 'class' ), $this->attribute_names( $link ) );
		$this->assertSame( 'tel:+421000000000', $link->getAttribute( 'href' ) );
		$this->assertSame( '+421 000 000 000', $link->textContent );
		$this->assertSame( 'my-class', $link->getAttribute( 'class' ) );
	}

	/**
	 * @dataProvider data_valid_numbers
	 */
	public function test_valid_numbers( $phone, $expected_href ) {
		$link = $this->assert_single_link( $this->render( array( 'phone' => $phone, 'link' => 'true' ) ) );

		$this->assertSame( $expected_href, $link->getAttribute( 'href' ) );
		$this->assertSame( trim( $phone ), $link->textContent );
	}

	public function data_valid_numbers() {
		return array(
			'3 digits'          => array( '112', 'tel:112' ),
			'separators'        => array( '(02) 123-45/67', 'tel:021234567' ),
			'dots'              => array( '0000.000.000', 'tel:0000000000' ),
			'international'     => array( '+421 000 000 000', 'tel:+421000000000' ),
			'15 digits'         => array( '+123456789012345', 'tel:+123456789012345' ),
			'surrounding space' => array( ' 112 ', 'tel:112' ),
		);
	}

	/**
	 * @dataProvider data_invalid_numbers
	 */
	public function test_invalid_number_outputs_nothing( $phone ) {
		$this->assertSame( '', $this->render( array( 'phone' => $phone, 'link' => 'true' ) ) );
		$this->assertSame( '', $this->render( array( 'phone' => $phone ) ) );
	}

	public function data_invalid_numbers() {
		return array(
			'empty'          => array( '' ),
			'2 digits'       => array( '12' ),
			'16 digits'      => array( '1234567890123456' ),
			'plus in middle' => array( '900+123' ),
			'two plus signs' => array( '++421000000000' ),
			'letters'        => array( '0800 FLOWERS' ),
			'html'           => array( '123<script>' ),
			'quote'          => array( '123" onclick="alert(1)' ),
			'separators only' => array( '() - /' ),
		);
	}

	public function test_missing_phone_attribute_outputs_nothing() {
		$this->assertSame( '', do_shortcode( '[obfuscate_phone link=true]' ) );
	}

	/**
	 * Same protection as CVE-2024-11935 for the email shortcode.
	 *
	 * @dataProvider data_class_xss_payloads
	 */
	public function test_class_xss( $payload ) {
		$link = $this->assert_single_link( $this->render( array( 'phone' => '+421 000 000 000', 'link' => 'true', 'class' => $payload ) ) );

		$this->assert_no_injected_attributes( $link );
		$this->assertSame( html_entity_decode( $payload, ENT_QUOTES | ENT_HTML5, 'UTF-8' ), $link->getAttribute( 'class' ) );
	}

	/**
	 * Custom link text: the shortcode is used inside the href of the user's own link.
	 */
	public function test_shortcode_inside_own_link() {
		$link = $this->assert_single_link( do_shortcode( '<a href="tel:[obfuscate_phone phone=\'+421000000000\']">Call us</a>' ) );

		$this->assertSame( 'tel:+421000000000', $link->getAttribute( 'href' ) );
		$this->assertSame( 'Call us', $link->textContent );
	}

	public function test_shortcode_atts_filter_is_applied() {
		$filter = function ( $out ) {
			$out['class'] = 'from-filter';
			return $out;
		};
		add_filter( 'shortcode_atts_obfuscate_phone', $filter );

		$link = $this->assert_single_link( do_shortcode( '[obfuscate_phone phone="112" link=true]' ) );

		remove_filter( 'shortcode_atts_obfuscate_phone', $filter );
		$this->assertSame( 'from-filter', $link->getAttribute( 'class' ) );
	}
}
