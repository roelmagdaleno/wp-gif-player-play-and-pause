# WP GIF Player - Play & Pause

Insert GIFs that can be played and paused into your WordPress posts and pages using shortcodes and Gutenberg blocks.

## How it works?

Every GIF stored in your Media Library can be used as a GIF player in your posts and pages but to make it work the plugin need to generate the next assets:

**THUMBNAIL**

The plugin will obtain the first frame image from the current GIF and use it as a thumbnail preview for the GIF Player.

**VIDEOS**
_(Only for video GIF method)_

When the user select the **GIF Method as video** the plugin will generate by default two video formats from the GIF.

These video formats are:

- WebM
- MP4

To do that we use the **FFmpeg library** and `shell_exec` PHP function, so it is necessary to have them installed in your server.

_If you don't know how to install those tools contact your hosting support to do it._

You can add more video methods and update the FFmpeg commands using the custom filter.
Check the wiki for more details.

Those generated assets will be attached into your Media Library and their parent will be the original GIF attachment.

After generating the previous assets you can now insert the GIF player in your post or page using a shortcode or a Gutenberg block.

## What if already have a GIF in my Media Library?

For those GIF files that are already uploaded in your Media Library you can generate the assets in one click.

You just have to go to your Media Library section and see the media files as list then hover the GIF you want to generate the assets and click "**Generate GIF Player**" option:

![](https://i.imgur.com/qlCzG9C.png)

You should see an admin notice after success or fail assets generation.

## Shortcode

If you're still using the WordPress Classic Editor you must use the next shortcode:

```
[gif-player id="gif_id"]
```

If you don't know the GIF ID you can add a GIF player using the "**Add GIF Player**" button next to the "**Add Media**" button.

![](https://i.imgur.com/zWPjTtO.png)

When you click the "**Add GIF Player**" button the Media Library window will open and you will be able to select or upload your GIF.

When you save and view the post, the plugin will render the GIF Player according to the selected GIF Method in the plugin options page.

## Gutenberg Block

You can use the GIF Player as a Gutenberg block and you can find it inside of the **Common Blocks** section as **GIF Player**.

![](https://i.imgur.com/0fGzT4K.png)

When you insert the GIF Player another block with two buttons will show:

![](https://i.imgur.com/6A3c8kU.png)

**UPLOAD**

The upload button will open your system directory to select a GIF file.

**MEDIA LIBRARY**

This button will open the Media Library window to select a GIF from your uploaded files.

When you finally add a GIF using the Gutenberg block you should be able to see the GIF player:

![](https://i.imgur.com/Lu6MhR2.png)

But this won't be playable in the editor. Only in the frontend.

Check the wiki for more details about replacing the GIF, choose another GIF method and more.
