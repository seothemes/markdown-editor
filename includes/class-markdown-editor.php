<?php
/**
 * Contains the main plugin class for the Markdown Editor.
 *
 * @package markdown-editor
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	 die;
}

/**
 * Main plugin class.
 */
class Markdown_Editor {

	/**
	 * Default instance.
	 *
	 * @since 0.1.0
	 * @var string $instance.
	 */
	private static $instance;

	/**
	 * Default post types.
	 *
	 * @since 0.1.1
	 * @var string $instance.
	 */
	private static $post_types = array(
		'post',
		'page',
	);

	/**
	 * Sets up the Markdown editor.
	 *
	 * @since 0.1.0
	 */
	private function __construct() {

		// Load markdown editor.
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_scripts_styles' ) );
		add_action( 'admin_footer', array( $this, 'init_editor' ) );

		// Remove quicktags buttons.
		add_filter( 'quicktags_settings', array( $this, 'quicktags_settings' ), 'content' );

		// Remove rich editing.
		add_filter( 'user_can_richedit', array( $this, 'disable_rich_editing' ) );

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
	 * Filter markdown post types.
	 *
	 * @since  0.1.0
	 * @return bool
	 */
	function get_post_types() {

		return apply_filters( 'markdown_editor_post_types', self::$post_types );

	}

	/**
	 * Check post types.
	 *
	 * @since  0.1.0
	 * @return bool
	 */
	function post_types() {

		// Admin only.
		if ( ! function_exists( 'get_current_screen' ) ) {
			return false;
		}

		// Edit comment screen.
		if ( WPCom_Markdown::is_commenting_enabled() && 'comment' === get_current_screen()->base ) {
			return true;
		}

		// Post edit screen.
		if ( in_array( get_current_screen()->post_type, $this->get_post_types() ) ) {
			return true;
		}
		return false;
	}

	/**
	 * Enqueue scripts and styles.
	 *
	 * @since 0.1.0
	 * @return void
	 */
	function enqueue_scripts_styles() {

		// Only enqueue on specified post types.
		if ( ! $this->post_types() ) {
			return;
		}

		wp_enqueue_script( 'simplemde-js', PLUGIN_URL . 'assets/scripts/simplemde.min.js' );
		wp_enqueue_style( 'simplemde-css', PLUGIN_URL . 'assets/styles/simplemde.min.css' );
		wp_enqueue_style( 'custom-css', PLUGIN_URL . 'assets/styles/style.css' );
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
		add_filter( 'plugin_action_links_' . PLUGIN_NAME, array( $this, 'jetpack_markdown_settings_link' ) );

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
		load_plugin_textdomain( 'jetpack', false, PLUGIN_DIR . 'languages/' );
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

		// Only initialize on specified post types.
		if ( ! $this->post_types() ) {
			return;
		}
		?>
		<script type="text/javascript">
			
			// Initialize the editor.
			var simplemde = new SimpleMDE( {
				spellChecker: false,
				element: document.getElementById( 'content' )
			} );

			// Change zIndex when toggle full screen.
			var change_zIndex = function( editor ) {

				// Give it some time to finish the transition.
				setTimeout( function() {
					var cm = editor.codemirror;
					var wrap = cm.getWrapperElement();
					if( /fullscreen/.test( wrap.previousSibling.className ) ) {
						document.getElementById( 'wp-content-editor-container' ).style.zIndex = 999999;
					} else {
						document.getElementById( 'wp-content-editor-container' ).style.zIndex = 1;
					}
				}, 2 );
			}

			var toggleFullScreenButton = document.getElementsByClassName( 'fa-arrows-alt' );
			toggleFullScreenButton[0].onclick = function() {
				SimpleMDE.toggleFullScreen( simplemde );
				change_zIndex( simplemde );
			}

			var toggleSideBySideButton = document.getElementsByClassName( 'fa-columns' );
			toggleSideBySideButton[0].onclick = function() {
				SimpleMDE.toggleSideBySide( simplemde );
				change_zIndex(simplemde);
			}

			var helpButton = document.getElementsByClassName( 'fa-question-circle' );
			helpButton[0].href = 'https://guides.github.com/features/mastering-markdown/';

			if ( typeof jQuery !== 'undefined' ) {
				jQuery( document ).ready( function() {

					// Integrate with WP Media module.
					var original_wp_media_editor_insert = wp.media.editor.insert;
					wp.media.editor.insert = function( html ) {
						original_wp_media_editor_insert( html );
						simplemde.codemirror.replaceSelection( html );
					}
				} );
			}
		</script>
		<?php
	}

	/**
	 * Quick tag settings.
	 *
	 * @since 0.1.0
	 * @param  array $qt_init Quick tag args.
	 * @return array
	 */
	function quicktags_settings( $qt_init ) {

		// Only remove buttons on specified post types.
		if ( ! $this->post_types() ) {
			return $qt_init;
		}

		$qt_init['buttons'] = ' ';
		return $qt_init;
	}

	/**
	 * Disable rich editing.
	 *
	 * @since  0.1.1
	 * @param  array $default Default post types.
	 * @return array
	 */
	function disable_rich_editing( $default ) {

		if ( in_array( get_post_type(), $this->get_post_types(), true ) ) {
			return false;
		}
		return $default;
	}
}
