<?php

/**
 * Plugin Name: WP GIF Player - Play & Pause
 * Plugin URI:  https://github.com/roelmagdaleno/wp-gif-player-play-and-pause
 * Description: Insert GIFs that can be played and paused into your WordPress posts and pages using shortcodes and Gutenberg blocks.
 * Version:     0.1.3
 * Author:      Roel Magdaleno
 * Author URI:  https://roelmagdaleno.com
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Require the necessary files to run the classes.
require_once 'includes/required-files.php';

new WP_GP_PP();
