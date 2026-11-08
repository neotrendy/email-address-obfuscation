# Testing

Two kinds of tests:

- **Automated integration tests** – `composer test`, see [maintaining.md](maintaining.md#automated-tests). Run them after every change.
- **Manual test** in a real browser on a local WordPress site – described below. Run it before every release and when testing a new WordPress version.

## Local test site

Link the plugin from this repository into a local WordPress site, so the site always runs the code in `trunk/`:

    ln -s /srv/email-address-obfuscation/trunk /srv/wordpress.localhost/wp-content/plugins/email-address-obfuscation
    wp plugin activate email-address-obfuscation --path=/srv/wordpress.localhost

## Test page

[`tests/fixtures/test-page.html`](../tests/fixtures/test-page.html) contains all shortcode cases in block editor markup. Every case states the expected result on the page itself.

Create the page:

    wp post create tests/fixtures/test-page.html --post_type=page --post_status=publish --post_title="Email Address Obfuscation test" --path=/srv/wordpress.localhost

Update an existing page after changing the file (replace `<ID>`):

    wp post update <ID> tests/fixtures/test-page.html --path=/srv/wordpress.localhost

When adding a feature or fixing a bug, add a case to the file. Use only `example.com` addresses and the number `+421 000 000 000`. If you export a page from WordPress instead, remove nonces, admin URLs, user names and real email addresses before committing.

## Checklist

1. **Front end** – every case shows the expected result.
2. **Page source** (`Ctrl + U`) – addresses and numbers appear as a mix of characters and entities such as `&#64;`; no plain `@` near the email addresses.
3. **Links** – clicking a `mailto:` link opens the email client with the subject and body; a `tel:` link offers to call (on a phone, or with a calling app on desktop).
4. **Block editor** – open the page in the editor: the Shortcode blocks and the paragraph with the own link are shown unchanged and the page can be saved again without changes.
5. **Plugin Check** (before a release):

       wp plugin check email-address-obfuscation --path=/srv/wordpress.localhost

## Security test: CVE-2024-11935

The vulnerability (fixed in 1.1.0) let any user with the **Contributor** role store JavaScript in a post via the `class` attribute; it ran in the browser of every visitor, including administrators.

1. Open the section *Security: CVE-2024-11935* of the test page. Expected:
   - no JavaScript alert when the page loads, nor when you press `Tab` to move through the links or click into them,
   - in the developer tools (*Elements*) the links have only `href` and `class`; `class` contains the whole text including `onfocus=...`.
2. Repeat as a Contributor, which is the real attack scenario:

       wp user create test-contributor contributor@example.com --role=contributor --prompt=user_pass --path=/srv/wordpress.localhost

   Log in as `test-contributor`, create a post with a Shortcode block containing

       [obfuscate_email email="patch@example.com" link='true' class='test"autofocus tabindex=1 onfocus="alert(1)']

   and open the preview. Expected: same as step 1. Delete the user afterwards:

       wp user delete test-contributor --yes --path=/srv/wordpress.localhost

To see what a vulnerable version does, point the symlink to `tags/1.0.1` temporarily – the alert appears on page load. Do this only on a local site and point the symlink back to `trunk` afterwards.
