# Email Address Obfuscation

WordPress plugin that protects email addresses and phone numbers from harvesting bots by encoding them as HTML entities, while keeping them readable for visitors.

- Install from the WordPress Plugin Directory: https://wordpress.org/plugins/email-address-obfuscation/
- Support: https://wordpress.org/support/plugin/email-address-obfuscation/
- Requires WordPress 3.6+ and PHP 5.6+.

## Usage

    [obfuscate_email email="your@example.com"]
    [obfuscate_email email="your@example.com" link=true class="my-class"]
    <a href="mailto:[obfuscate_email email='your@example.com?subject=Hello']">Write to us</a>

    [obfuscate_phone phone="+421 000 000 000"]
    [obfuscate_phone phone="+421 000 000 000" link=true]
    <a href="tel:[obfuscate_phone phone='+421000000000']">Call us</a>

| Parameter | Description |
|---|---|
| `email` | Email address, optionally followed by a query string such as `?subject=...`. |
| `phone` | Phone number with 3 to 15 digits, optionally starting with `+`. |
| `link` | Optional. `true` outputs a clickable `mailto:` / `tel:` link. |
| `class` | Optional. CSS classes for the link. |

Without `link=true` the shortcode outputs plain text (for email including the query string), so it can be used inside your own link. Invalid addresses and numbers produce no output. See [`trunk/readme.txt`](trunk/readme.txt) for details.

The encoding stops simple bots that read the page source, not bots that decode HTML.

## Repository

This repository mirrors the WordPress.org SVN layout: the plugin itself is in [`trunk/`](trunk/). Notes for maintainers are in [`docs/maintaining.md`](docs/maintaining.md), testing (automated tests with `composer test` and the manual test page) in [`docs/testing.md`](docs/testing.md).

## License

[GPLv2 or later](https://www.gnu.org/licenses/gpl-2.0.html)
