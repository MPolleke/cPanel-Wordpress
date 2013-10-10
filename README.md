
### cPanel plugin: Installer for WordPress

cPanel is a software package that is used to configure and maintain Linux webservers. It’s a really nice package, with lots of integration of services and accounts. I use it at my workplace and am quite satisfied with it.

At my work we use mostly WordPress as a CMS, and we liked to have an installer for WordPress integrated in cPanel. According to the cPanel website, there are 5 different packages which can do that.
Simplescripts is a free alternative, but it wasn’t my cup of tea. You have to configure WordPress on the simplescript website, and I don’t like handing over login data like that, especially when I don’t see the need for that. Further I just couldn’t get it working, I just didn’t understand it.
All the other installers cost around 50 or 60 Euro’s, and I don’t like to pay that. Also, I don’t need a complete suite of installers, just a WordPress installer.
So I built a script myself.

## Requirements

There are some requirements to installing and using this plugin:

    Linux server
    root access
    cPanel (ofcourse)
    Apache with Su-exec

## Installation

At the cPanel website you can generate the plugin file easily, but it’s also included in my zipfile.
This wordpress.panelplugin file can be placed in /usr/local/cpanel/whostmgr/docroot/cgi/wordpress.

This file can be installed with 2 commands at the rootshell:

/usr/local/cpanel/bin/rebuild_sprites –force 2>&1 >/dev/null
/usr/local/cpanel/bin/register_cpanelplugin /usr/local/cpanel/whostmgr/docroot/cgi/wordpress/wordpress.cpanelplugin

The real plugin, which does the work can then be placed in /usr/local/cpanel/base/frontend/x3/wordpress/.
Note the “x3″ in the path. If you’re using a different theme, make sure you place it in the right directory.

## Localisation

Currently the php script will download and install the Dutch version of WordPress. You will want to adapt the script for your locale, and check that it all works allright.

Instead of having to wait for half an hour for the upload via ftp to finish, you can now install or update within 10 seconds.
