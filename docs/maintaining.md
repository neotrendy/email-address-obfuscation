# Maintaining the plugin

Internal notes for editing and publishing the plugin. This file is in Git only and is never published to WordPress.org.

## Where things live

The project directory is both a Git repository and an SVN working copy.

| What | Git (GitHub) | SVN (WordPress.org) |
|---|---|---|
| `trunk/` – the plugin | yes | yes, published |
| `tags/X.Y.Z/` – released versions | no | yes, published |
| `assets/` – banners, icons, screenshots | only `assets/.svnignore` | yes, PNG files only |
| `docs/`, `README.md`, `CLAUDE.md`, ignore files | yes | no (`svn:ignore`) |
| `tests/`, `bin/`, `composer.json`, `phpunit.xml.dist` | yes | no (`svn:ignore`) |
| `vendor/`, `.wp-tests/` | no | no |

- WordPress.org serves the version in `tags/<Stable tag>/`. The public plugin page is built from `tags/<Stable tag>/readme.txt`, not from `trunk/`.
- Find the current stable version:

      grep "Stable tag" trunk/readme.txt

- SVN commits need the separate **SVN password** from the WordPress.org profile (*Account & Security*), not the login password.
- Every `svn ci` is public immediately. Never change code in an existing tag; only its `readme.txt` may be updated.
- Reference: [Using Subversion with the Plugin Directory](https://developer.wordpress.org/plugins/wordpress-org/how-to-use-subversion/)

## Before any change

    git pull
    svn up

## Check before committing

    git status
    git diff
    svn stat              # M = modified, A = added, D = deleted, ? = not versioned
    svn stat --no-ignore  # I = ignored; dev files must be listed as I
    svn diff

Unversioned files (`?`) are not committed by `svn ci`. New plugin files must be added with `svn add trunk/<file>`, removed files with `svn rm trunk/<file>`.

## Automated tests

Integration tests run the shortcodes in a real WordPress (PHPUnit 9.6 + WordPress test library). They cover the documented behaviour, invalid input and XSS attempts including CVE-2024-11935. Test files (`tests/`, `bin/`, `composer.json`, `phpunit.xml.dist`) are in Git only.

One-time setup:

1. Create a test database and a MySQL user with access to it only. **The test suite deletes all tables in this database – never use the database of a real site.**

       sudo mysql -e "CREATE DATABASE wordpress_tests; CREATE USER 'wp_tests'@'localhost' IDENTIFIED BY '<password>'; GRANT ALL PRIVILEGES ON wordpress_tests.* TO 'wp_tests'@'localhost';"

2. Install dependencies, WordPress and the test library (into `vendor/` and `.wp-tests/`, both ignored). The script asks for the password and stores it in `.wp-tests/wordpress-tests-lib/wp-tests-config.php`.

       composer install
       bin/install-wp-tests.sh wordpress_tests wp_tests

Run the tests:

    composer test

To test against another WordPress version (e.g. for "Tested up to"), delete `.wp-tests/` and run the install script with the version as 4th parameter: `bin/install-wp-tests.sh wordpress_tests wp_tests localhost 7.2`.

## Case 1: "Tested up to" only (most common)

No new version, users get no update. Update the readme in trunk **and** in the stable tag, so the public page shows the new value.

1. Optionally run the automated tests against the new WordPress version (see *Automated tests*) and the manual test from [testing.md](testing.md).
2. Change `Tested up to: X.Y` in `trunk/readme.txt` and in `tags/<Stable tag>/readme.txt`.
3. Publish to WordPress.org (replace `1.3.0` with the current Stable tag):

       svn diff
       svn ci -m "Tested up to: X.Y" trunk/readme.txt tags/1.3.0/readme.txt

4. Commit to GitHub (tags are not in Git):

       git add trunk/readme.txt
       git commit -m "Updated readme file - tested up to X.Y"
       git push

## Case 2: Change in trunk without a release

For work in progress. Commit to Git as usual. Committing `trunk/` to SVN is optional: users do not get it, it is only available as "Development Version" on WordPress.org.

## Case 3: New version X.Y.Z

1. Update the version everywhere:
   - `trunk/email-address-obfuscation.php` – `Version:` (and `Requires at least` / `Requires PHP` if changed)
   - `trunk/readme.txt` – `Stable tag:`, `Tested up to:`, `== Changelog ==`, `== Upgrade Notice ==`
   - regenerate the POT file:

         wp i18n make-pot trunk trunk/languages/email-address-obfuscation.pot --slug=email-address-obfuscation

2. Run `composer test` and the manual test from [testing.md](testing.md) (test page, CVE-2024-11935 check, [Plugin Check](https://wordpress.org/plugins/plugin-check/)).
3. Commit to GitHub:

       git add -A
       git commit -m "Release X.Y.Z"
       git push

4. Publish to WordPress.org – trunk and the new tag in one commit:

       svn stat
       svn cp trunk tags/X.Y.Z
       svn ci -m "Tagging version X.Y.Z"

5. Within a few minutes check the plugin page and that the download ZIP contains the new version: https://wordpress.org/plugins/email-address-obfuscation/

## Plugin page assets

Banners, icons and screenshots are in `assets/` (`banner-772x250.png`, `banner-1544x500.png`, `icon-128x128.png`, `icon-256x256.png`, `screenshot-N.png`). Screenshot captions are in `== Screenshots ==` in `readme.txt`. `.psd` sources stay local.

    svn add assets/<new-file>.png
    svn ci -m "Update assets" assets

## SVN ignore rules

Files that must never reach WordPress.org are listed in `.svnignore` (root) and `assets/.svnignore`. After changing them:

    svn propset svn:ignore -F .svnignore .
    svn propset svn:ignore -F assets/.svnignore assets/
    svn ci --depth empty -m "Update svn:ignore" . assets

## Translations

Translations are not bundled with the plugin. They are made at [translate.wordpress.org](https://translate.wordpress.org/projects/wp-plugins/email-address-obfuscation/) and WordPress downloads them as language packs:

- *Stable (latest release)* – plugin name and description shown in WordPress admin (from the stable tag).
- *Stable Readme* / *Development Readme* – the plugin page on localized WordPress.org sites (from the stable tag / trunk).
