<?php

/**
 * Plugin Name: WP GIF Player - Play & Pause
 * Plugin URI:  https://roelmagdaleno.com
 * Description: Attach GIF players into your posts and pages.
 * Version:     0.1.0
 * Author:      Roel Magdaleno
 * Author URI:  https://roelmagdaleno.com
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Require the necessary files to run the classes.
require_once 'includes/required-files.php';

new WP_GP_PP();
