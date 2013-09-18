Advanced wpCAS
==============

Description
-----------

Advanced wpCAS integrates WordPress into an established CAS architecture, allowing centralized management and authentication of user credentials in a heterogeneous environment.

From [Wikipedia][1]:

> The Central Authentication Service (CAS) is a single sign-on protocol for the web. Its purpose is to permit a user to log into multiple applications simultaneously and automatically. It also allows untrusted web applications to authenticate users without gaining access to a user's security credentials, such as a password. The name CAS also refers to a software package that implements this protocol.

Users who attempt to login to WordPress are redirected to the central CAS sign-on screen. After the user's credentials are verified, s/he is then redirected back to the WordPress site. If the CAS username matches the WordPress username, the user is recognized as valid and allowed access. 

[Authorization][2] of that user's capabilities is based on native WordPress settings and functions. CAS only authenticates that the user is who s/he claims to be.

If the CAS user does not have an account in the WordPress site, an administrator defined function can be called to provision the account or do other actions. By default, CAS users without WordPress accounts are simply refused access.

[1]: http://en.wikipedia.org/wiki/Central_Authentication_Service "Wikipedia"
[2]: http://en.wikipedia.org/wiki/AuthZ "Authorization"

Acknowlegements
---------------

This is a fork of Casey Bissons' wpCAS plugin: http://wordpress.org/plugins/wpcas/

Support for CAS gateway mode and user profile sync callbacks added.


Installation
------------

1. [Download Advanced wpCAS][3] and place it on your webserver so that it can be included by the wpCAS plugin.
2. Place the plugin folder in your `wp-content/plugins/` directory and activate it.
3. Set any options you want in the `wpcas-conf.php` file.
4. The plugin starts intercepting authentication attempts as soon as you activate it. Use another browser or another computer to test the configuration.

Note: Configuration through admin interface is removed from this fork. Use wpcas-conf.php.

[3]: https://github.com/neopeak/wpcas/releases "Download Advanced wpCAS"

### wpcas-conf.php

See `wpcas-conf-sample.php` for an example.


Frequently Asked Questions
--------------------------

### What version of phpCAS should I use?
I've only tested it with the 1.0 release available from ja-sig.

### How's it work?
Users who attempt to login to WordPress are redirected to the central CAS sign-on screen. After the user's credentials are verified, s/he is then redirected back to the WordPress site. If the CAS username matches the WordPress username, the user is recognized as valid and allowed access. If the CAS username does not exist in WordPress, you can define a function that could provision the user in the site.

### You keep talking about provisioning users. How?
An example will come.



