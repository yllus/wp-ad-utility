<?php
/*
 Plugin Name: Ad Utility (for Google Ad Manager)
 Plugin URI: https://github.com/yllus/wp-ad-utility
 Description: Allows easy and comprehensive targeting of ads with Google Ad Manager.
 Author: Sully Syed
 Version: 1.0.0
 Author URI: http://www.yllus.com/
 */

class WPAdUtility {
	public static function init() {
		// Add our Ads metabox to a few post types.
		add_action( 'add_meta_boxes', array('WPAdUtility', 'add_meta_boxes') );

		// Save the contents of our Ads metabox.
		add_action( 'save_post', array('WPAdUtility', 'save_post') );

		// Add JavaScript class files to the header of external pages.
		add_action( 'wp_enqueue_scripts', array('WPAdUtility', 'wp_enqueue_scripts'), 10 );

		// Inject post-specific ad rules into the header.
		add_action( 'wp_head', array('WPAdUtility', 'wp_head'), 1 );
	}

	public static function add_meta_boxes() {
		add_meta_box( 'ad-utility-metabox', 'Ads', array('WPAdUtility', 'display_ads_metabox'), 'post', 'side', 'default' );
    	add_meta_box( 'ad-utility-metabox', 'Ads', array('WPAdUtility', 'display_ads_metabox'), 'page', 'side', 'default' );
	}

	public static function display_ads_metabox( $post ) {
		$wp_adutility_page_options = get_post_meta($post->ID, 'wp_adutility_page_options', true);

		$str_permalink = get_permalink($post->ID);
		$str_pathname = WPAdUtility::get_pathname_keyvalue($str_permalink);

		if ( $post->post_status != 'publish' ) {
			$str_pathname = 'None; publish to set a value.';
		}
		?>
		<div class="inside">
			<p>In Google Ad Manager, the key-value key of <b>pathname</b> will be set to the value shown below:</p>

			<p>
				<input type="text" name="" class="form-input-tip" value="<?php echo $str_pathname; ?>" readonly="readonly" style="width: 100%; font-size: 12px;">
			</p>

			<p>Enable / disable ads from being displayed:</p>

			<p>
				<select name="wp_adutility_page_options">
					<option value="all" <?php selected( $wp_adutility_page_options, 'all' ); ?>>Show ads on this page</option>
					<option value="none" <?php selected( $wp_adutility_page_options, 'none' ); ?>>Don't show ads</option>
				</select>
			</p>
		</div>
		<?php
	}

	// Actually save the values of the "Ads" metabox as post metadata.
	public static function save_post( $post_id ) {
	    // Bail if we're doing an auto save.
	    if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) return;

	    // If our current user can't edit this post, bail.
	    if ( !current_user_can( 'edit_posts' ) ) return;

	    if( isset( $_POST['wp_adutility_page_options'] ) ) {
	        update_post_meta( $post_id, 'wp_adutility_page_options', $_POST['wp_adutility_page_options'] );
	    }
	}

	public static function get_pathname_keyvalue( $str_path ) {
		$str_path = str_replace(home_url('/'), '/', $str_path);
        $str_path = strtolower($str_path);

        return $str_path;
	}

	public static function wp_enqueue_scripts() {
	    wp_enqueue_script( 'wp-ad-utility', plugin_dir_url(__FILE__) . 'wp-ad-utility.js', array(), false, false );
	    wp_enqueue_style( 'wp-ad-utility', plugin_dir_url(__FILE__) . 'wp-ad-utility.css' );
	}


	public static function wp_head() {
		global $post;

		$wp_adutility_page_options = 'all';
		if ( isset($post) ) {
			$wp_adutility_page_options = get_post_meta($post->ID, 'wp_adutility_page_options', true);
		}

		?>
		<script> window.wp_adutility_network_code = ''; window.wp_adutility_page_options = '<?php echo $wp_adutility_page_options; ?>'; </script>
		<?php
	}
}


WPAdUtility::init();
?>