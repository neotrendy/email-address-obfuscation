<?php
/**
 * Base class for shortcode tests.
 *
 * antispambot() encodes characters randomly, so the raw output differs on every call.
 * The helpers parse the output as HTML (like a browser does) and the tests assert on
 * the decoded text, attribute values and the list of elements and attributes.
 *
 * @package EmailAddressObfuscation
 */

abstract class Neotrendy_Shortcode_TestCase extends WP_UnitTestCase {

	/**
	 * Parses an HTML fragment and returns the <body> element.
	 *
	 * @param string $html HTML fragment.
	 *
	 * @return DOMElement
	 */
	protected function parse( $html ) {
		$document = new DOMDocument();
		$document->loadHTML( '<!DOCTYPE html><html><head><meta charset="utf-8"></head><body>' . $html . '</body></html>', LIBXML_NOERROR | LIBXML_NOWARNING );

		return $document->getElementsByTagName( 'body' )->item( 0 );
	}

	/**
	 * Returns the names of all elements in an HTML fragment.
	 *
	 * @param string $html HTML fragment.
	 *
	 * @return string[]
	 */
	protected function element_names( $html ) {
		$names = array();

		foreach ( $this->parse( $html )->getElementsByTagName( '*' ) as $element ) {
			$names[] = $element->nodeName;
		}

		return $names;
	}

	/**
	 * Asserts that the output is plain text (no elements) and returns the decoded text.
	 *
	 * @param string $html Shortcode output.
	 *
	 * @return string Text as displayed by a browser.
	 */
	protected function assert_plain_text( $html ) {
		$this->assertSame( array(), $this->element_names( $html ), 'Output must not contain HTML elements.' );

		return $this->parse( $html )->textContent;
	}

	/**
	 * Asserts that the output is exactly one <a> element and returns it.
	 *
	 * @param string $html Shortcode output.
	 *
	 * @return DOMElement
	 */
	protected function assert_single_link( $html ) {
		$this->assertSame( array( 'a' ), $this->element_names( $html ), 'Output must be exactly one <a> element.' );

		return $this->parse( $html )->getElementsByTagName( 'a' )->item( 0 );
	}

	/**
	 * Returns the attribute names of an element.
	 *
	 * @param DOMElement $element Element.
	 *
	 * @return string[]
	 */
	protected function attribute_names( DOMElement $element ) {
		$names = array();

		foreach ( $element->attributes as $attribute ) {
			$names[] = $attribute->name;
		}

		return $names;
	}

	/**
	 * Asserts that no attribute was injected into the link (e.g. onfocus, autofocus, tabindex).
	 *
	 * Only checks security; whether `title` is output is tested separately.
	 *
	 * @param DOMElement $link Link element.
	 */
	protected function assert_no_injected_attributes( DOMElement $link ) {
		$this->assertSame( array(), array_values( array_diff( $this->attribute_names( $link ), array( 'href', 'title', 'class' ) ) ), 'Unexpected attributes were injected.' );
	}

	/**
	 * Payloads from CVE-2024-11935 (stored XSS via the `class` attribute) and similar attempts.
	 *
	 * @return array[]
	 */
	public function data_class_xss_payloads() {
		return array(
			'CVE-2024-11935 onfocus' => array( 'test"autofocus tabindex=1 onfocus="alert(1)' ),
			'close tag and script'   => array( '"><script>alert(1)</script>' ),
			'single quote'           => array( "x' onmouseover='alert(1)" ),
			'encoded quote'          => array( 'x&#x22; onclick=&#x22;alert(1)' ),
		);
	}
}
