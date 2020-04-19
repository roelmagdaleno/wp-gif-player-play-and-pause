=== WP GIF Player - Play & Pause ===
Contributors: rokumetal
Donate link: https://paypal.me/roelmagdaleno
Tags: gif, player
Requires at least: 5.0
Tested up to: 5.4
Stable tag: 0.1.0
Requires PHP: 7.0
License: GPLv3 or later
License URI: https://www.gnu.org/licenses/gpl-3.0.html

Insert GIFs that can be played and paused into your WordPress posts and pages using shortcodes and Gutenberg blocks.

== Description ==

Insert GIFs that can be played and paused into your WordPress posts and pages using shortcodes and Gutenberg blocks.

The GIF player can be one of the three methods in the plugin:

**GIF**

It will show the thumbnail preview and will load the GIF after click the player.

**Canvas**

Adds the GIF player as HTML5 Canvas.
The GIF file will be transformed to HTML5 Canvas by using a JavaScript library.

**Video (Recommended - Only for video GIF method)**

Insert the GIF player as video.
This method is the recommended one because some GIF files sizes are bigger than the converted video.

The video formats we use are:

- WebM
- MP4

**To enable the "video" GIF method you need the "FFmpeg" library and `shell_exec` PHP function installed in your server.**

For more detailed information go to the [GitHub Repository](https://github.com/roelmagdaleno/wp-gif-player-play-and-pause).

== Shortcode ==

If you're still using the WordPress Classic Editor you must use the next shortcode:

`[gif-player id="gif_id"]`

If you don't know the GIF ID you can add a GIF player using the "**Add GIF Player**" button next to the "**Add Media**" button.

When you click the "**Add GIF Player**" button the Media Library window will open and you will be able to select or upload your GIF.

When you save and view the post, the plugin will render the GIF Player according to the selected GIF Method in the plugin options page.

== Gutenberg Block ==

You can use the GIF Player as a Gutenberg block and you can find it inside of the **Common Blocks** section as **GIF Player**.

When you insert the GIF Player another block with two buttons will show:

**UPLOAD**

The upload button will open your system directory to select a GIF file.

**MEDIA LIBRARY**

This button will open the Media Library window to select a GIF from your uploaded files.

When you finally add a GIF using the Gutenberg block you should be able to see the GIF player.

But this won't be playable in the editor. Only in the frontend.

For more detailed information go to the [GitHub Repository](https://github.com/roelmagdaleno/wp-gif-player-play-and-pause).

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

**FFMpeg library**.
This is the library to convert the GIF files to video format.

**shell_exec** PHP function.
Some servers have disabled this function due to security reasons.

If you don't know how to install those tools contact your hosting support to do it.

= What video formats does the GIF convert to? =

By default the plugin converts the GIF to WebM and MP4 but you can add more using the custom filters.

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
