=== QR Twitter Widget ===
Contributors: QROkes
Tags: twitter, twitter widget, timeline, twitter timeline, tweet, widget, feed, social media, sidebar, twitter sidebar, footer, plugin
License: GPLv3
License URI: http://www.gnu.org/licenses/gpl-3.0.html
Requires at least: 4.0
Tested up to: 4.8
Stable tag: 0.2.0

== Description ==
Display your latest tweets in just a few clicks on your site.

A simple widget/shortcode which lets you add your latest tweets in your content or widget areas. Get started in just a few clicks and use the provided Widget/Shortcode to easily display your Tweets on your website.

There is no need to create a Twitter application and provides a nice interface to display your tweets in an easy way, just install the plugin and set the options.

It uses the official Twitter's Embedded Timelines Widget functionality. Twitter offers embeddable timelines that allow you to display any public Twitter feed on your blog. These timelines are interactive, so readers can reply, retweet and favorite tweets straight from your blog or website.

= Features =
* Very easy to setup and use.
* Don't need to create an API.
* Widget ID is optional.
* Automatically detect your site language to load and display Twitter's text components.
* Scripts only loaded when it's necessary (only on those pages that contain the widget) improving the speed of your site.
* You can blend your tweets professionally by customizing the link color, border color, background choice and other useful options.
* Support for multiple Twitter accounts.
* Multisite support.

= Why do I develop and mantain this plugin? =
I tried a lot of plugins (Jetpack included) and all of them load unnecessary JS and CSS scripts all over the site, even when the plugin/widget is not displayed. Every file/script loaded in your site generate extra requests for your server and affect your site load time, ending up affecting the final user experience.

That's why I decided to develop this simple and easy plugin.

= Rate Us / Feedback =
Please take the time to let us and others know about your experiences by leaving a review, so that we can improve the plugin for you and other users.

== Installation ==

You can use the built in installer and upgrader, or you can install the plugin
manually.

= From your WordPress dashboard =

1. Visit 'Plugins > Add New'
1. Search for 'QR Twitter Widget'
1. Activate QR Twitter Widget from your Plugins page. 

= From WordPress.org =

1. Download QR Twitter Widget.
1. Upload the 'QR Twitter Widget' directory to your '/wp-content/plugins/' directory, using your favorite method (ftp, sftp, scp, etc...)
1. Activate QR Twitter Widget from your Plugins page. 

= Once Activated =

1. Visit 'Appearance > Widgets'
1. Place the "QR Twitter Widget" on your sidebar/footer and customize the layout options.
1. Configure your Twitter 'Username' and any other options as desired.
1. Relax.

== Frequently Asked Questions ==

= Shortcode attributes =
twittertl id="123456789" user="QROkes" limit="0" replies="false" width="325" height="500" theme="light" linkcolor="#f96e5b" bordercolor="#e8e8e8" header="true" footer="false" border="true" scrollbar="false" background="true"

= How to get a Twitter Widget ID =

Very easy, here's a link to [get your ID](https://qrokes.com/en/tutorials/my-wordpress/how-to-create-a-twitter-widget-timeline/ "How to get my Twitter Widget ID")

= Why my timeline is not shown in Private Browsing Mode? =
In Private Browsing windows, browsers will block content loaded from domains that track users across sites.

== Screenshots ==

1. That’s it! When you publish the post, it will look something like this.
2. Customize your twitter widget to blend with any WordPress theme.

== Changelog ==

= 0.2.0 =
Release date: Jul 9th, 2017

* New: Shortcode "twittertl".
* Tweak: Prevent empty parameter to be outputted.
* Tweak: Prevent empty data-chrome parameters to be outputted as spaces.
* Code re-structured.
* Minimum PHP version 5.3.

= 0.1.2 =
Release date: May 12th, 2017

* New: Widget Id is optional now.
* Update: Twitter Widget JS.

= 0.1.1 =
Release date: March 12th, 2016

* Translation ready.
* Readme.txt updated.
* Some minor fixes.

= 0.1.0 =
Release date: December 31th, 2015

* Initial release.

== Upgrade Notice ==