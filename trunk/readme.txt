=== Email Address Obfuscation ===
Contributors: neotrendy
Donate link: https://paypal.me/neotrendy
Tags: anti-spam, email, obfuscation, obfuscate, protect
Requires at least: 2.5
Tested up to: 7.1
Stable tag: 1.2.0
Requires PHP: 5.6
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Email Address Obfuscation prevents email harvesting by hiding email address appearing in your pages, while remaining visible to your site visitors.

== Description ==

A lightweight plugin that protects email addresses from email-harvesting bots, by converting email addresses characters to HTML entities. Hide email from Spam Bots using a shortcode `[obfuscate_email]` and built-in WordPress function [antispambot()](https://developer.wordpress.org/reference/functions/antispambot/ "antispambot").

= Features =

* Obfuscate plain email address
* Obfuscate href mailto link in HTML anchor element
* Support for custom CSS class
* Support for email subject

= Usage =

**Obfuscate plain email address**

    [obfuscate_email email="your@email.com"]

**Create clickable email address**

    [obfuscate_email email="your@email.com" link=true]

**Add CSS class to the HTML anchor element**

    [obfuscate_email email="your@email.com" link=true class="my-class another-class"]

**Obfuscate email address with email subject**

    [obfuscate_email email='your@email.com?subject=My custom email subject']

= Shortcode parameter =

* `email` required - Email address.
* `link` optional - Set true if you want to create clickable email address.
* `class` optional - Add space separated list of classes.

== Frequently Asked Questions ==

= How does it work? =

The plugin converts email addresses characters from `email` parameter of `[obfuscate_email]` shortcode to HTML entities. Email addresses on your web page will be obfuscated (hidden) from bots, while keeping them visible to humans. In fact, there are no visible changes to your website for visitors.

= How can check if the plugin works? =

View source code of your page (press `Ctrl + U` in web browser) and look for the place, where an email address is placed. You should see decimal entities instead of email address.

** Please note ** Inspector in developer tools automatically decode decimal and hexadecimal entities. You need to use *View source code*.

== Screenshots ==

1. Using shortcode in Gutenberg Block Editor.
2. Displaying email on frontend.
3. Obfuscated email address in source code.

== Changelog ==

= 1.2.0 =
* Added support for email subject.

= 1.1.0 =
* Security Fix: Resolved a Stored Cross-Site Scripting (XSS) vulnerability in the `class` parameter of the shortcode, allowing Contributor+ users to inject malicious scripts. (CVE-2024-11935 | Reported by theviper17y)
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

= 1.0.0 =
Tested compatibility with WordPress 6.1.1. Added translation folder and POT file.

= 0.9.2 =
Tested compatibility with WordPress 6.0.3.

= 0.9.1 =
Upload to plugin hosting.

= 0.9.0 =
Initial release.
