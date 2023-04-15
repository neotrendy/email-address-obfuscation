# Email Address Obfuscation

## Plugin hosting

* SVN URL: https://plugins.svn.wordpress.org/email-address-obfuscation
* Public URL: https://wordpress.org/plugins/email-address-obfuscation
* SVN username is *neotrendy* and your password is the same as you use to log in to WordPress.org

## SVN

Using Subversion with the WordPress Plugin Directory:
https://developer.wordpress.org/plugins/wordpress-org/how-to-use-subversion/

### Update repository

    svn up

### Different between local copy and the central repository

    svn stat
    svn diff
    svn stat --no-ignore

### Add files to the SVN

    svn add .\trunk\filename

### Ignore files from SVN

    svn propset svn:ignore -F .svnignore .

###  Check in changes to the central repository

    svn ci -m "Write a comment"

### Tagging new versions

    svn cp trunk tags/1.2.0
    svn ci -m "tagging version 1.2.0"