<?php
/**
 * Plugin Name: WordPress Markdown
 * Plugin URI: https://github.com/seothemes/wpmarkdown
 * Description: Replaces the default WordPress editor with a Markdown editor for your posts and pages.
 * Version: 0.1.0
 * Author: Seo Themes
 * Website: https://www.seothemes.com
 * License: GPLv2 or later
 *
 * @package wpmarkdown
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	 die;
}

// Check if Jetpack module is enabled.
if ( ! class_exists( 'WPCom_Markdown' ) ) {
	include_once dirname( __FILE__ ) . '/includes/easy-markdown.php';
}

// Define constants.
define( 'PLUGIN_VERSION', '1.0' );
define( 'MINIMUM_WP_VERSION', '4.8' );

// Main class.
include_once( 'includes/class-markdown.php' );

// Get class instance.
Markdown::get_instance();
