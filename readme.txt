=== WP GIF Player - Play & Pause ===
Contributors: rokumetal
Donate link: https://paypal.me/roelmagdaleno
Tags: gif, player
Requires at least: 5.0
Tested up to: 5.4
Stable tag: 0.1.1
Requires PHP: 7.0
License: GPLv3 or later
License URI: https://www.gnu.org/licenses/gpl-3.0.html

Insert GIFs that can be played and paused into your WordPress posts and pages using shortcodes and Gutenberg blocks.

== Description ==

You can select one of the three GIF player methods in the plugin options page.

### GIF

This is the default method.

When a GIF is uploaded in your Media Library it will create an image thumbnail to use it as the GIF player preview and when the user clicks on the GIF player the original GIF will be loaded once.

### Canvas

With this method every GIF will be loaded in your post and then converted into a [playable canvas](https://developer.mozilla.org/en-US/docs/Web/API/Canvas_API). A thumbnail image is created as well when uploading the GIF in your Media Library.

For this process we're using the [libgif.js](https://github.com/buzzfeed/libgif-js) JavaScript library by BuzzFeed.

### Video (Recommended)

Every GIF file will be converted to these video formats:

* WebM
* MP4

Why videos?

It is known that GIF files sizes are bigger than a video so using a video instead of a GIF file [will help your website performance](https://developers.google.com/web/fundamentals/performance/optimizing-content-efficiency/replace-animated-gifs-with-video).

Want more details about converting GIF to video? [Check the GIF as Video wiki](https://github.com/roelmagdaleno/wp-gif-player-play-and-pause/wiki/GIF-as-Video).

== Thumbnail Preview ==

The plugin will grab the GIF file and extract the first frame as JPEG image and use it as thumbnail preview. This file will be saved in the same folder of the original GIF.

The thumbnail image always generate once and doesn't matter the selected GIF method.

== Shortcode ==

If you're still using the WordPress Classic Editor you must use the next shortcode:

`[gif-player id="gif_id"]`

If you don't know the GIF ID you can add a GIF player using the "**Add GIF Player**" button next to the "**Add Media**" button.

When you click the "**Add GIF Player**" button the Media Library window will open and you will be able to select or upload your GIF.

When you save and view the post, the plugin will render the GIF Player according to the selected GIF Method in the plugin options page.

== Gutenberg Block ==

You can use the GIF Player as a Gutenberg block. [Check the wiki for more details](https://github.com/roelmagdaleno/wp-gif-player-play-and-pause/wiki/Gutenberg-Block).

== Installation ==

1. Upload the plugin files to the `/wp-content/plugins/wp-gif-player-play-and-pause` directory, or install the plugin through the WordPress plugins screen directly.
2. Activate the plugin through the 'Plugins' screen in WordPress
3. Use the Settings->WP GIF Player screen to configure the plugin.

== Frequently Asked Questions ==

= What if already have a GIF in my Media Library? =

For those GIF files that are already uploaded in your Media Library you can generate the assets in one click.

You just have to go to your Media Library section and see the media files as list then hover the GIF you want to generate the assets and click "Generate GIF Player" option.

You should see an admin notice after success or fail assets generation.

= What libraries or tools do I need to use GIF players as video? =

To use the GIF Player as video you need two things:

* **FFMpeg library**. This is the library to convert the GIF files to video format.

* **shell_exec**. A PHP function to run the ffmpeg command.

[Check the GIF as Video wiki](https://github.com/roelmagdaleno/wp-gif-player-play-and-pause/wiki/GIF-as-Video) for more details.

= What video formats does the GIF convert to? =

By default the plugin **converts the GIF to WebM and MP4** but you can add more [using the custom filters](https://github.com/roelmagdaleno/wp-gif-player-play-and-pause/wiki/Hooks).

== Screenshots ==

1. screenshot-1: Plugin Options.
2. screenshot-2: Generate GIF Player for existent GIF file.
3. screenshot-3: Add GIF Player from button in Classic Editor.
4. screenshot-4: Search and insert the Gutenberg Block.
5. screenshot-5: Upload or select the GIF using the Gutenberg Block.
6. screenshot-6: Preview the Gutenberg Block GIF Player.

== Upgrade Notice ==

= 0.1.0 =
* Initial plugin functionality. Check the [GitHub Repository](https://github.com/roelmagdaleno/wp-gif-player-play-and-pause) to check the commits.

== Changelog ==

= 0.1.0 =
* Initial plugin functionality. Check the [GitHub Repository](https://github.com/roelmagdaleno/wp-gif-player-play-and-pause) to check the commits.
