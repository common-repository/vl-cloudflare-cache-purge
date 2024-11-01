=== VL Cloudflare Cache Purge ===
Contributors: nitinraghav
Tags: cloudflare, cache, static kv, cloudflare workers, workers, purge cache, purge workers
Donate link: https://nitinraghav.com/
Requires at least: 5.2
Tested up to: 6.0
Requires PHP: 7.0
Stable tag: 1.0.2
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

This plugin adds a updated_post hook in the post update process and with the help of it, it deletes the cache of the page in cloudflare cache and KV storage(static cache)

== Description ==
This plugin adds a updated_post hook in the post update process and with the help of it, it deletes the cache of the page in cloudflare cache and KV storage(static cache)

== Installation ==
To setup this plugin add following constants in the wp-config.php file before activating it.

`
define('CF_ZONE_ID', '');
define('CF_AUTH_TOKEN', '');
define('CF_KV_AUTH', ''); # http auth username and password if the site is password protected. If not just add \'test:test\'
`

== Frequently Asked Questions ==
= Who Can You This? =

The one who is using CloudFlare for storing Static Key Value pairs of the site and the site cache, can use this plugin

= What I need to use this plugin? =

You will need three things to run this plugin
* Zone ID - that can be seen on overview page of the site
* Auth Token - That can be created from the profile section that can manage workers
* Auth: username and password that can access the API for CLoudFlare.

== Changelog ==

= 1.0.2 =
* Again used wp_Remote_request instead of curl call to fix the timeout error.

= 1.0.1 =
* Fixed the fatal error because of the timeout occur while making API Call for purging the CLoudflare Workers Static KV.
* Added more error_log statements

== Upgrade Notice ==

= 1.0.2 =
Now wp_remote_request will not give error because of timeout issue.

= 1.0.1 =
This will fix the fatal error caused while making purge API