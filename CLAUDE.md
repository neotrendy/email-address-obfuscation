# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## What this is

WordPress plugin **Email Address Obfuscation** (slug `email-address-obfuscation`), published in the WordPress.org Plugin Directory. It registers one shortcode, `[obfuscate_email email="..." link=true class="..."]`, that runs the address through WordPress core `antispambot()` (HTML-entity encoding) and optionally wraps it in a `mailto:` anchor. All plugin logic lives in `trunk/email-address-obfuscation.php` (function `neotrendy_email_address_obfuscation_shortcode`). There is no build step, no Composer/npm, no tests, and no linter config yet.

## Dual version control: Git + SVN in the same directory

The repository root is simultaneously:

- a **Git** repo (`origin` = `github.com/neotrendy/email-address-obfuscation`, branch `main`) — the source of truth for development history, and
- an **SVN working copy** of `https://plugins.svn.wordpress.org/email-address-obfuscation` — the WordPress.org deployment channel.

The layout is therefore the standard WordPress.org SVN layout, not a typical plugin repo:

- `trunk/` — the plugin as shipped (everything here is published to users).
- `tags/<version>/` — frozen release copies, created with `svn cp trunk tags/X.Y.Z`. Ignored by Git.
- `assets/` — Plugin Directory banners, icons and screenshots (`banner-*.png`, `icon-*.png`, `screenshot-N.png`). Only `assets/.svnignore` is in Git; `.psd` sources are excluded from SVN.

Ignore rules are kept in two places and must stay in sync with what each VCS should see:

- `.gitignore` hides `/tags/*`, `/assets/*`, `/.svn/`, `.idea/`.
- `.svnignore` (root) and `assets/.svnignore` hold the `svn:ignore` property; apply them with
  `svn propset svn:ignore -F .svnignore .` and `svn propset svn:ignore -F assets/.svnignore assets/`.
  Any Git-only/dev file added at the root (e.g. `CLAUDE.md`, `.claude/`) must be added to `.svnignore` so it is never committed to WordPress.org. Anything placed inside `trunk/` is published unless excluded.

Common SVN commands (from `README.md`): `svn up`, `svn stat`, `svn stat --no-ignore`, `svn diff`, `svn add trunk/<file>`, `svn ci -m "..."`. Never run `svn ci` or `git push` without the user's explicit request — `svn ci` publishes to all WordPress.org users.

## Release checklist (version numbers live in several places)

When bumping a version, update all of these consistently:

- `trunk/email-address-obfuscation.php` header `Version:` (and `Requires at least` / `Requires PHP` if changed).
- `trunk/readme.txt`: `Stable tag:`, `Tested up to:`, `== Changelog ==`, and `== Upgrade Notice ==`.
- `trunk/languages/email-address-obfuscation.pot` `Project-Id-Version` (regenerate with WP-CLI `wp i18n make-pot trunk trunk/languages/email-address-obfuscation.pot`), then update `sk_SK.po`/`sk_SK.mo`.
- Then `svn cp trunk tags/X.Y.Z` and commit.

"Tested up to" bumps are a recurring commit type (see git log) and only touch `trunk/readme.txt`.

## Code conventions and constraints

- Declared minimums: WordPress 2.5, PHP 5.6 — avoid newer PHP syntax (no scalar type hints, return types, `??=`, arrow functions, etc.) unless the minimums are raised.
- Global functions are prefixed `neotrendy_email_address_obfuscation_`; text domain is `email-address-obfuscation`, domain path `/languages`.
- Security history: v1.1.0 fixed **CVE-2024-11935**, a stored XSS via the shortcode `class` attribute (Contributor+ users). Every shortcode attribute that reaches HTML output must be escaped (`esc_attr`, etc.); note the output of `antispambot()` is inserted into the `href`/`title` attributes unescaped, so changes to email/query-string handling need care.
- `readme.txt` must follow the WordPress.org readme format (it drives the public plugin page); `README.md` is internal (Git only, ignored by SVN).
