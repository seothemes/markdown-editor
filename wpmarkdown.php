<?php
/**
 * Plugin Name: WordPress Markdown
 * Plugin URI:  https://github.com/seothemes/wpmarkdown
 * Description: Replaces the default WordPress editor with a Markdown editor for your posts and pages.
 * Version:     0.1.0
 * Author:      Seo Themes
 * Author URI:  https://www.seothemes.com
 * License:     GPL-2.0+
 * License URI: http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain: jetpack
 * Domain Path: /languages
 *
 * @package wpmarkdown
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
	include_once dirname( __FILE__ ) . '/includes/easy-markdown.php';
}

/**
 * Main plugin class.
 */
class WP_Markdown {

	/**
	 * Default instance.
	 *
	 * @since 0.1.0
	 * @var string $instance.
	 */
	private static $instance;

	/**
	 * Sets up the Markdown editor.
	 *
	 * @since 0.1.0
	 */
	private function __construct() {

		// Activation / Deactivation hooks.
		register_activation_hook( __FILE__, array( $this, 'plugin_activation' ) );
		register_deactivation_hook( __FILE__, array( $this, 'plugin_deactivation' ) );

		// Load markdown editor.
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_scripts_styles' ) );
		add_action( 'admin_footer', array( $this, 'init_editor' ) );

		// Remove quicktags buttons.
		add_filter( 'quicktags_settings', array( $this, 'quicktags_settings' ), $editor_id = 'content' );

		// Load Jetpack Markdown module.
		$this->load_jetpack_markdown_module();
	}

	/**
	 * Get instance.
	 *
	 * @since 0.1.0
	 * @return object $instance Plugin instance.
	 */
	public static function get_instance() {
		if ( ! isset( self::$instance ) ) {
			$c = __CLASS__;
			self::$instance = new $c;
		}
		return self::$instance;
	}

	/**
	 * Prevent cloning.
	 *
	 * @since 0.1.0
	 * @return void
	 */
	public function __clone() {
		trigger_error( 'Clone is not allowed.', E_USER_ERROR );
	}

	/**
	 * Enqueue scripts and styles.
	 *
	 * @since 0.1.0
	 * @return void
	 */
	function enqueue_scripts_styles() {

		// Only enqueue stuff on the post editor page.
		if ( get_current_screen()->base !== 'post' ) {
			return;
		}

		wp_enqueue_script( 'simplemde-js', $this->plugin_url( '/assets/scripts/simplemde.min.js' ) );
		wp_enqueue_style( 'simplemde-css', $this->plugin_url( '/assets/styles/simplemde.min.css' ) );
		wp_enqueue_style( 'custom-css', $this->plugin_url( '/assets/styles/style.css' ) );
	}

	/**
	 * Load Jetpack Markdown Module.
	 *
	 * @since 0.1.0
	 * @return void
	 */
	function load_jetpack_markdown_module() {

		// If the module is active, let's make this active for posting. Comments will still be optional.
		add_filter( 'pre_option_' . WPCom_Markdown::POST_OPTION, '__return_true' );
		add_action( 'admin_init', array( $this, 'jetpack_markdown_posting_always_on' ), 11 );
		add_action( 'plugins_loaded', array( $this, 'jetpack_markdown_load_textdomain' ) );
		add_filter( 'plugin_action_links_' . plugin_basename( __FILE__ ), array( $this, 'jetpack_markdown_settings_link' ) );
	}

	/**
	 * Set Jetpack posting to always on.
	 *
	 * @since 0.1.0
	 * @return void
	 */
	function jetpack_markdown_posting_always_on() {
		global $wp_settings_fields;
		if ( isset( $wp_settings_fields['writing']['default'][ WPCom_Markdown::POST_OPTION ] ) ) {
			unset( $wp_settings_fields['writing']['default'][ WPCom_Markdown::POST_OPTION ] );
		}
	}

	/**
	 * Load JetPack text domain (already translated).
	 *
	 * @since 0.1.0
	 * @return void
	 */
	function jetpack_markdown_load_textdomain() {
		load_plugin_textdomain( 'jetpack', false, dirname( plugin_basename( __FILE__ ) ) . '/languages/' );
	}

	/**
	 * Add settings link.
	 *
	 * @since 0.1.0
	 * @param  string $actions Markdown settings.
	 * @return string
	 */
	function jetpack_markdown_settings_link( $actions ) {
		return array_merge(
			array(
				'settings' => sprintf( '<a href="%s">%s</a>', 'options-discussion.php#' . WPCom_Markdown::COMMENT_OPTION, __( 'Settings', 'jetpack' ) ),
			),
			$actions
		);
		return $actions;
	}

	/**
	 * Initialize editor.
	 *
	 * @since 0.1.0
	 * @return void
	 */
	function init_editor() {
		if ( get_current_screen()->base !== 'post' ) {
			return;
		}

		echo '<script type="text/javascript">
			// Init the editor
			var simplemde = new SimpleMDE({
				spellChecker: false,
				element: document.getElementById("content")
			});

			// Change zIndex when toggle full screen
			var change_zIndex = function(editor) {
				// Give it some time to finish the transition
				setTimeout(function() {
					var cm = editor.codemirror;
					var wrap = cm.getWrapperElement();
					if(/fullscreen/.test(wrap.previousSibling.className) ) {
						document.getElementById("wp-content-editor-container").style.zIndex = 999999;
					} else {
						document.getElementById("wp-content-editor-container").style.zIndex = 1;
					}
				}, 2);
			}

			var toggleFullScreenButton = document.getElementsByClassName("fa-arrows-alt");
			toggleFullScreenButton[0].onclick = function() {
				SimpleMDE.toggleFullScreen(simplemde);
				change_zIndex(simplemde);
			}

			var toggleSideBySideButton = document.getElementsByClassName("fa-columns");
			toggleSideBySideButton[0].onclick = function() {
				SimpleMDE.toggleSideBySide(simplemde);
				change_zIndex(simplemde);
			}

			var helpButton = document.getElementsByClassName("fa-question-circle");
			helpButton[0].href = "http://seothemes.com/markdown-guide.html";

			if (typeof jQuery !== "undefined") {
				jQuery(document).ready(function(){
					// Remove the quicktags-toolbar
					document.getElementById("ed_toolbar").style.display = "none";

					// Integrate with WP Media module
					var original_wp_media_editor_insert = wp.media.editor.insert;
					wp.media.editor.insert = function( html ) {
						original_wp_media_editor_insert(html);
						simplemde.codemirror.replaceSelection(html);
					}
				});
			}
		</script>';
	}

	/**
	 * Quick tag settings.
	 *
	 * @since 0.1.0
	 * @param  array $qt_init Quick tag args.
	 * @return array
	 */
	function quicktags_settings( $qt_init ) {
		$qt_init['buttons'] = ' ';
		return $qt_init;
	}

	/**
	 * Get plugin URl.
	 *
	 * @since 0.1.0
	 * @param  string $path Plugin URL path.
	 * @return string
	 */
	function plugin_url( $path ) {
		return plugins_url( 'wpmarkdown/' . $path );
	}

	/**
	 * Plugin activation function.
	 *
	 * @since 0.1.0
	 * @return void
	 */
	function plugin_activation() {
		global $wpdb;
		$wpdb->query( 'UPDATE `' . $wpdb->prefix . "usermeta` SET `meta_value` = 'false' WHERE `meta_key` = 'rich_editing'" );
	}

	/**
	 * Plugin deactivation function.
	 *
	 * @since 0.1.0
	 * @return void
	 */
	function plugin_deactivation() {
		global $wpdb;
		$wpdb->query( 'UPDATE `' . $wpdb->prefix . "usermeta` SET `meta_value` = 'true' WHERE `meta_key` = 'rich_editing'" );
	}

}

// Get class instance.
WP_Markdown::get_instance();
