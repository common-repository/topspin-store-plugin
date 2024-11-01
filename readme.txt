=== Topspin ===
Contributors: stagebloc
Tags: topspin, music, merchandise
Requires at least: 2.9
Tested up to: 2.9.2
Stable tag: 2.1

Allow Topspin users to create Store pages, and embed widgets in posts.  Includes a fully-functional theme with customizable header and landing page, and widget-ready sidebar and footer.

== Description ==

The Topspin Wordpress plug-in will work out-of-the-box with any Wordpress blog as long as your theme follows standard Wordpress coding requirements.  Not to worry -- most do.  The plug-in will function with Wordpress 2.9 and newer.

We do not suggest you alter topspin.php or any files under the /lib folder.  Even if you know what you're doing, changing the content of these files could lead to unpredictable results.

= Requirements =
* Topspin account and API key
* Wordpress 2.9+
* PHP - Version 5.2+
* PHP - JSON enabled
* PHP - cURL enabled
* PHP - FTP enabled for automatic theme installer

== Installation ==

Please note: This plug-in is intended to provide "site-in-a-box" functionality for novice users.  As such, it performs an invasive operation on install: It renames the default "Uncategorized" post category to "Blog"; if it cannot find the "Uncategorized" category, or a category called "Blog", it creates one, and sets it to the default category.  This ensures a visually fluid menu system.

Copy the plug-in into your plug-in directory.  By default, this is /wp-content/plugins.

Log into your Wordpress blog, and access the Plugins menu using the left-hand menu bar.  Find the "Topspin" plug-in, and click "activate".  You will now have a new option available to you within the administrative menu bar called "Topspin".

Click the "Topspin" option, and enter your Topspin API Key (a long string of numbers and letters), and your Topspin API Username (an email address).

See the section titled "Topspin Wordpress Theme" for instructions pertaining to the included Wordpress theme.

= Uninstalling =
When the plug-in is uninstalled, your Buy Button page is retained, but set to a draft.  You made delete it after uninstalling if you wish.  All of your options are deleted.

== Topspin Wordpress Theme ==

= Manual Install Instructions =
The Topspin Wordpress theme installer works best under *nix environments. Windows (IIS) servers may have issues with correct directory pathing.

To install the theme manually:

* Locate the plug-in in the directory you downloaded it to on your local computer.
* Use an FTP client of your choice to connect to the server you installed the plug-in and Wordpress on, and find your theme folder.
** On a standard Wordpress installation, this is under your Wordpress directory in /wp-content/themes/.
* Transfer the entire directory named 'topspin-wordpress-theme' from the plug-in directory on your computer to the theme directory on your server.
* Log into the Wordpress administrative account.
* Within the Wordpress back-end, active the theme under Appearance > Themes in the primary (left screen-side) menu.

Next time you access the Topspin plugin, it will notify you that the theme is installed and active.

= Pretty Permalinks =
To activate [Pretty Permalinks](http://codex.wordpress.org/Using_Permalinks "What are Pretty Permalinks?"), access the Permalinks menu in Wordpress in the left-hand menu-bar under Settings.

Select the checkbox for "Custom Structure" and copy the following code into the black box to the right:
/%category%/%postname%/

We have included an ".htaccess" file for you within this plug-in package.  This file will tell your *nix server how to handle your new Permalink structure (if you are on an IIS server, please contact your administrator or consult the Wordpress Codex for other options).  Since files with a period in front of their name are inherently hidden, we have named ours topspin.htaccess.  Copy this file into the same directory your Wordpress index.php is in, and remove the "topspin" from the name.

You now have Pretty Permalinks activated.

= Site Header =
Edit your Site Header within the Theme Design Setup.

By default, your site header should be a valid image file (PNG, JPEG, or GIF) 960 pixels wide.  The theme will automatically reduce wider headers, or expand narrow ones -- if your header is less than 950 pixels wide, it might look pixelated.

To activate your site header, click on the "'Click here' to upload your header" link.  Select the file to upload, and wait until the upload is complete.  Once complete, save your file, set the name of the upload to "topspin-header".

= Theme Design Setup =

You can choose what your Home button text will be.  If this is blank, it will say "Home".

If you would like to suppress Categories being shown within your site navigation, turn off "Categories in Navigation".

= Landing Page =
The Landing Page replaces your header as the first thing people see on your site's Home page.  This is a great place to put your latest Topspin offers.  Your landing page may contain embedded Flash, images, or anything else you choose to place in it.  It will be wrapped in a <div> tag, and inserted into your page header.

You may choose to turn off the navigation bar or the main page content from the Landing Page Setup.  These checkboxes apply only to the Home page, and allow you to have your Landing Page "take over" your site's Home page.

To avoid using "inline CSS", you can specify an external CSS file to help you style your Landing Page.  Place the file anywhere accessible via HTTP, and enter the full URL to that file in the text box to the right of the heading "Landing Page CSS".

Below this text box is a large text area where you can paste the source code for your Landing Page.

Once you're ready, click the checkbox next to "Activate Landing Page", and the click "Save Changes" at the bottom of the plug-in settings screen.

= Customization =
You may customize the included theme as you see fit.  All base CSS is contained within style.css in the topspin-wordpress-theme directory (usually under /wp-content/themes).  Adding your own CSS file is easy -- create a new file called custom.css, and place it in the same directory as base.css.  You should keep a backup of this file elsewhere, as well.  If something goes wrong, and you wish to revert to the original version of the theme, you can re-install it from within the plugin, or by FTPing a fresh copy over your changes.

== Store Page ==

By default, the Topspin Wordpress plug-in creates a new Wordpress page using the name "Store" -- this is where your Buy Buttons will live.  You may edit the page and change this to any name you like.  When editing the Buy Button page make sure you do not delete the "[topspin_<function>]" code.  That is what tells the plug-in where to create your Buy Button page.  You may add a sub-heading, or any other text or image you like before or after this code -- including Topspin widgets (by entering their embed URL).

You may also select the number of columns to use when displaying your Buy Button page, and the maximum number of items per page.  If there are more items in your catalog than the maximum number, they will be listed on subsequent pages accessible from a "pagination" bar at the bottom of your Buy Button page.

It is possible to filter your Buy Button results by Product Type (using the Product Type drop-down menu), and/or by a name keyword (using the Name Filter entry box).

= Themes =
There are three themes included with the plug-in: Dark, Light, and Topspin.

If you want to get your hands dirty, and create additional themes, we have tried to be as verbose with our code documentation, and CSS selector names as possible.

Themes CSS files can be found in /plugins/topspin/css/themes. To create a new theme, duplicate one of the existing CSS files, and rename it.  

NOTE: Do not use spaces, or single or double quotes in your filenames.  These filenames will be ignored for security and functionality reasons.

Edit the CSS file, and re-save it with a valid filename on your server in the /plugins/topspin/css/themes directory.  When you return to the plug-in Settings page, you will now be able to select your new theme from the "Store Page Setup" > "Theme" drop-down.

If you'd like to share your themes with the Topspin community, and they adhere to strict W3C standards, contact us and we'll include your theme in an upcoming revision of the plug-in.

== Widget (Spin) Embedding ==

In addition to your new Merch page, you may also embed any Topspin widget (Spin) you have created.  All you need to do is enter the URL to the Spin within a Wordpress post or page.

For example: http://app.topspin.net/api/v1/offer/4484

This will access the offer with the code "4484".

You will find the CSS selectors available for modifying the look of these embedded spins at the end of the /css/base.css CSS file.

== Support/Contact ==

This plugin-is provided as-is, without any warranty expressed or implied.

Bug reports and suggestions: [Topspin - Get Satisfaction] (http://getsatisfaction.com/topspin/ "Topspin - Get Satisfaction")

[Topspin](http://www.topspin.net/ "Topspin") 
[StageBloc](http://stagebloc.com/ "StageBloc") 

== Changelog ==

= 1.0 =
* First version

= 2.0 =
* Admin page updated to include additional options
* Admin page visually updated
* Added Topspin Wordpress Theme
* Added Topspin Wordpress Theme installer
* Added customizable landing page
* Improved home directory location
* Updated documentation

= 2.0.1 =
* Bug fixes
* Styling tweaks
* Better width adjustment for menu items
* Documentation improvements

= 2.0.2 =
* Bug fixes
* Moved Topspin settings to top level administration menu
* Ability to forgo adding categories to navigation menu
* Removed subpages from showing up in navigation menu
* Ability to change name of Home button
* Navigation menu Page order based on user-defined Page order
* custom.css file automatically detected and used
* Can suppress site content for Landing/Home page
* Can suppress navigation for Landing/Home page
* Updated administration menus.
** Created Theme Design Setup roll-down.
** Moved Site Header to Theme Design Setup
* Documentation improvements

= 2.0.3 =
* Bug fixes
* Added support for Artist IDs
** Artist ID directs user to correct My Account location in footer
* Re-structured /lib directory
* Added support for static front page (under Settings > Reading in Wordpress)
** When a static front page is set up in Wordpress, the Home button disappears
* Documentation improvements
* Our Wiki now includes a roadmap

= 2.0.4 =
* Improved pagination function
** Added Prev link
** Cleaned up First/Last link appearance
* Updated store themes for improved pagination

= 2.0.5 =
* Bug Fix
** Home button now displays correct name when unselected
* Category archive pages now show images and excerpts
<<<<<<< HEAD

== Roadmap ==
=======
>>>>>>> c655d7b91be9bb79c1cab47d4f0144d407a6dcaa

= 2.1 =
* Added option to set site width in options
* Updated content CSS to use % instead of px values

== Roadmap ==

= 2.2 =
* More styling choices for category archive pages
* Automated analytics support

= 2.3 =
* Increased store functionality
* Store category/filter widget

= 3.0 =
* Audio pages
* Video pages
