<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// General Classes
require_once 'constants.php';
require_once 'helpers.php';

// HTML Classes
require_once 'html/class-html-radio.php';

// Traits
require_once 'traits/trait-thumbnail-creator.php';
require_once 'traits/trait-video-creator.php';

// Main Classes
require_once 'class-wp-gp-pp-options.php';
require_once 'class-wp-gp-pp-media-uploader.php';
require_once 'class-wp-gp-pp-shortcode.php';
require_once 'class-wp-gp-pp.php';
