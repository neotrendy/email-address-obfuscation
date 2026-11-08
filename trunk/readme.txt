=== Email Address Obfuscation ===
Contributors: neotrendy
Donate link: https://paypal.me/neotrendy
Tags: anti-spam, email, phone, obfuscation, protect
Requires at least: 3.6
Tested up to: 7.1
Stable tag: 1.3.0
Requires PHP: 5.6
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.txt

Protects email addresses and phone numbers from harvesting bots by encoding them as HTML entities, while keeping them readable for visitors.

== Description ==

Email Address Obfuscation is a lightweight plugin that adds the `[obfuscate_email]` and `[obfuscate_phone]` shortcodes. They encode the email address or phone number with the built-in WordPress function [antispambot()](https://developer.wordpress.org/reference/functions/antispambot/ "antispambot"): characters are randomly converted to HTML entities, so simple bots that scan the page source cannot read them. Browsers decode the entities, so visitors see the address or number as usual.

The plugin uses no JavaScript, stores no data and does not connect to any external service.

= Features =

* Obfuscate a plain-text email address or phone number
* Create an obfuscated clickable `mailto:` or `tel:` link
* Use the shortcode inside your own link to set your own link text
* Add custom CSS classes to the link
* Add an email subject to the link

= Email address =

**Plain-text email address**

    [obfuscate_email email="your@example.com"]

**Clickable email address**

    [obfuscate_email email="your@example.com" link=true]

**Clickable email address with CSS classes**

    [obfuscate_email email="your@example.com" link=true class="my-class another-class"]

**Clickable email address with email subject**

    [obfuscate_email email='your@example.com?subject=My custom email subject' link=true]

The subject is added to the `mailto:` link only; the link text is the email address. Other `mailto:` parameters can be added the same way, separated by `&`, for example `?subject=Hello&body=Hi there`.

**Link with your own text**

Without `link=true`, the shortcode outputs the email address including the subject as text, so you can use it inside your own link, for example in a Custom HTML block:

    <a href="mailto:[obfuscate_email email='your@example.com?subject=Hello']">Write to us</a>

= Phone number =

**Plain-text phone number**

    [obfuscate_phone phone="+421 000 000 000"]

**Clickable phone number**

    [obfuscate_phone phone="+421 000 000 000" link=true class="my-class"]

The number is displayed as written. The `tel:` link contains only the digits and the leading `+`, e.g. `tel:+421000000000`.

**Link with your own text**

Write the number without spaces when you use it inside your own link, for example in a Custom HTML block:

    <a href="tel:[obfuscate_phone phone='+421000000000']">Call us</a>

= Shortcode parameters =

* `email` (required for `[obfuscate_email]`) – The email address, optionally followed by a query string such as `?subject=...`. If the address is not valid, the shortcode outputs nothing.
* `phone` (required for `[obfuscate_phone]`) – The phone number: 3 to 15 digits, optionally starting with `+`, separated by spaces or `-` `.` `/` `(` `)`. If the number is not valid, the shortcode outputs nothing.
* `link` (optional) – Set to `true` to output a clickable link. Without it, the address or number is output as plain text.
* `class` (optional) – Space-separated list of CSS classes for the link.

== Frequently Asked Questions ==

= How does it work? =

The shortcodes pass the email address or phone number to the WordPress function `antispambot()`, which randomly converts characters to HTML entities. Browsers display the text normally, so there are no visible changes for your visitors.

= Does it stop all spam bots? =

No. HTML-entity encoding stops simple harvesting bots that read the raw page source. Bots that decode HTML entities or render the page like a browser can still read the address or number.

= Where can I use the shortcodes? =

Anywhere WordPress processes shortcodes, for example in posts and pages using the Shortcode block of the block editor, or in the classic editor.

= How can I check that the plugin works? =

Open the page source (`Ctrl + U` in most browsers) and find the email address. Instead of the readable address you should see plain characters mixed with HTML entities such as `&#64;`.

Please note: the Inspector in browser developer tools decodes HTML entities automatically. Use *View page source* instead.

== Screenshots ==

1. Using the shortcode in the block editor.
2. Email address displayed on the front end.
3. Obfuscated email address in the page source.

== Changelog ==

= 1.3.0 =
* New: `[obfuscate_phone]` shortcode obfuscates phone numbers, optionally as a clickable `tel:` link.
* Fix: Multiple `mailto:` parameters (e.g. `?subject=...&body=...`) were double-encoded and the email client did not receive them.
* Fix: The link text showed the email subject; it now shows only the email address.
* Fix: Invalid email addresses (e.g. containing spaces) are no longer repaired or output as an empty `mailto:` link; the shortcode outputs nothing.
* Change: Removed the `title` attribute from the link (better accessibility).
* Change: Corrected the minimum required WordPress version to 3.6.
* Change: Translations are provided by translate.wordpress.org; the bundled Slovak translation was removed.
* Security: Added protection against direct file access.

= 1.2.0 =
* Added support for email subject.

= 1.1.0 =
* Security Fix: Resolved a Stored Cross-Site Scripting (XSS) vulnerability in the `class` parameter of the shortcode, allowing Contributor+ users to inject malicious scripts. (CVE-2024-11935 | Reported by *theviper17y*)
* Minor code improvements and optimizations.

= 1.0.1 =
* Tested compatibility with WordPress 6.2.

= 1.0.0 =
* Tested compatibility with WordPress 6.1.1.
* Added translation folder and POT file.

= 0.9.2 =
* Tested compatibility with WordPress 6.0.3.

= 0.9.1 =
* Shortcode changed from email_obfuscate to obfuscate_email.

= 0.9.0 =
* Release Candidate.

== Upgrade Notice ==

= 1.3.0 =
Adds the [obfuscate_phone] shortcode and fixes email subject handling. Links no longer have a title attribute and invalid email addresses are no longer displayed.

= 1.2.0 =
Added support for email subject. Includes the security fix from 1.1.0 (CVE-2024-11935) – update recommended.

= 1.1.0 =
Security fix: resolves a Stored Cross-Site Scripting (XSS) vulnerability in the shortcode `class` parameter (CVE-2024-11935). Update immediately.

= 1.0.0 =
Tested compatibility with WordPress 6.1.1. Added translation folder and POT file.

= 0.9.2 =
Tested compatibility with WordPress 6.0.3.

= 0.9.1 =
Upload to plugin hosting.

= 0.9.0 =
Initial release.
