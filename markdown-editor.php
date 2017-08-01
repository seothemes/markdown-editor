<?php
/**
 * Plugin Name: Markdown Editor
 * Plugin URI:  https://github.com/seothemes/markdowneditor
 * Description: Replaces the default WordPress editor with a Markdown editor for your posts and pages.
 * Version:     0.1.0
 * Author:      Seo Themes
 * Author URI:  https://www.seothemes.com
 * License:     GPL-2.0+
 * License URI: http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain: jetpack
 * Domain Path: /languages
 *
 * @package Markdown_Editor
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	 die;
}

// Define constants.
define( 'PLUGIN_VERSION', '1.0' );
define( 'MINIMUM_WP_VERSION', '4.8' );

// Check if Jetpack module is enabled.
if ( ! class_exists( 'WPCom_Markdown' ) ) {
	include_once dirname( __FILE__ ) . '/includes/class-wpcom-markdown.php';
}

// Load Markdown Editor class.
include_once dirname( __FILE__ ) . '/includes/class-markdown-editor.php';

// Get class instance.
Markdown_Editor::get_instance();
