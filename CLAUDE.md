# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## What this is

WordPress plugin **Email Address Obfuscation** (slug `email-address-obfuscation`), published in the WordPress.org Plugin Directory. It registers two shortcodes that run the value through WordPress core `antispambot()` (HTML-entity encoding) and optionally wrap it in an anchor:

- `[obfuscate_email email="..." link=true class="..."]` → `mailto:`. A query string after the address (`?subject=...`) goes into the `href` of a link; without a link it is output after the address (so the shortcode can be used inside a custom `href="mailto:[...]"`, which existing sites rely on). Addresses failing `is_email()` output nothing (no repair by `sanitize_email()`). Examples use `example.com` (RFC 2606).
- `[obfuscate_phone phone="..." link=true class="..."]` → `tel:` (3–15 digits, optional leading `+`, separators ` -./()`; `href` contains digits only). Invalid numbers output nothing.
- Enclosing form (`[tag]Text[/tag]`) is intentionally not supported: WordPress mis-parses a self-closing shortcode followed by an enclosing one of the same tag, which would break existing pages. Custom link text = shortcode inside the user's own `href`.

All plugin logic lives in `trunk/email-address-obfuscation.php`. There is no build step and no linter config yet. The plugin has no runtime dependencies; Composer is used only for dev tools.

Tests: integration tests (PHPUnit 9.6 + WordPress test library) in `tests/`, run with `composer test`. Setup is described in `docs/maintaining.md` (`bin/install-wp-tests.sh` installs WordPress and the test library into `.wp-tests/`; test DB `wordpress_tests`). Shortcode output is random (`antispambot()`), so tests parse it with DOMDocument and assert on decoded text, attributes and element lists — never on raw strings. Add a regression test for every bug fix. Manual browser testing (including CVE-2024-11935) is described in `docs/testing.md`; the test page is `tests/fixtures/test-page.html` — keep it in sync with new features and use only `example.com` / `+421 000 000 000` in it.

## Dual version control: Git + SVN in the same directory

The repository root is simultaneously:

- a **Git** repo (`origin` = `github.com/neotrendy/email-address-obfuscation`, public, branch `main`) — the source of truth for development history, and
- an **SVN working copy** of `https://plugins.svn.wordpress.org/email-address-obfuscation` — the WordPress.org deployment channel.

Layout (standard WordPress.org SVN layout):

- `trunk/` — the plugin as shipped (everything here is published to users).
- `tags/<version>/` — frozen release copies (`svn cp trunk tags/X.Y.Z`). Ignored by Git. WordPress.org serves `tags/<Stable tag>/`, including its `readme.txt` for the public plugin page.
- `assets/` — Plugin Directory banners, icons and screenshots. Only `assets/.svnignore` is in Git; `.psd` sources are excluded from SVN.
- `tests/`, `bin/`, `composer.json`, `phpunit.xml.dist` — dev/test tooling (Git only); `vendor/` and `.wp-tests/` are ignored by both.
- `docs/` — internal documentation (Git only). `docs/maintaining.md` is the maintainer's guide for editing and publishing (Tested up to bumps, releases, SVN commands).

Ignore rules must stay in sync with what each VCS should see:

- `.gitignore` hides `/tags/`, `/assets/*`, `/.svn/`, `.idea/`.
- `.svnignore` (root) and `assets/.svnignore` hold the `svn:ignore` property (apply with `svn propset svn:ignore -F .svnignore .`). Any Git-only/dev file or directory added at the root must be added to `.svnignore`. Anything placed inside `trunk/` is published unless excluded.

Never run `svn ci`, `git commit` or `git push` without the user's explicit request — `svn ci` publishes to all WordPress.org users.

## Releases

Follow `docs/maintaining.md`. Version numbers live in the PHP header `Version:`, `trunk/readme.txt` (`Stable tag:`, `== Changelog ==`, `== Upgrade Notice ==`) and the POT file. Regenerate the POT with `wp i18n make-pot trunk trunk/languages/email-address-obfuscation.pot --slug=email-address-obfuscation` (without `--slug` WP-CLI writes `…/support/plugin/trunk` into `Report-Msgid-Bugs-To`). Translations are not bundled; they come as language packs from translate.wordpress.org.

## Code conventions and constraints

- Declared minimums: WordPress 3.6 (`shortcode_atts()` third parameter), PHP 5.6 — avoid newer PHP syntax (no scalar type hints, return types, `??`, arrow functions, etc.) unless the minimums are raised.
- Global functions are prefixed `neotrendy_email_address_obfuscation_`; text domain is `email-address-obfuscation`, domain path `/languages`.
- Code, comments and documentation are written in English.
- Security history: v1.1.0 fixed **CVE-2024-11935**, a stored XSS via the shortcode `class` attribute (Contributor+ users). Every shortcode attribute that reaches HTML output must be escaped. `antispambot()` output is inserted into `href` and link text unescaped — it must only receive the `sanitize_email()` result or the regex-validated phone number (no HTML-special characters). The query string is escaped once with `esc_attr()` and must not be passed through `antispambot()` (that double-encodes `&`).
- `trunk/readme.txt` follows the WordPress.org readme format and drives the public plugin page; the PHP header `Description` should match its short description. `README.md` is the public GitHub landing page (Git only).
