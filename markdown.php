<?php
/**
 * Plugin Name: Markdown
 * Plugin URI:  https://github.com/seothemes/markdown
 * Description: Replaces the default WordPress editor with a Markdown editor for your posts and pages.
 * Version:     0.1.0
 * Author:      SEO Themes
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
define( 'PLUGIN_VERSION', '0.1.0' );
define( 'MINIMUM_WP_VERSION', '4.8' );
define( 'DIR_PATH', plugin_dir_path( __FILE__ ) );

// Check if Jetpack module is enabled.
if ( ! class_exists( 'WPCom_Markdown' ) ) {
	include_once dirname( __FILE__ ) . '/includes/class-wpcom-markdown.php';
}

// Load Markdown class.
include_once dirname( __FILE__ ) . '/includes/class-markdown-editor.php';

// Get class instance.
Markdown_Editor::get_instance();

// Register activation hook.
register_activation_hook( __FILE__, array( 'Markdown_Editor', 'plugin_activation' ) );

// Register deactivation hook.
register_deactivation_hook( __FILE__, array( 'Markdown_Editor', 'plugin_deactivation' ) );
