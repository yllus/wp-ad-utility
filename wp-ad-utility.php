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
		// Adds "Ad Utility" under the Settings menu, points the entry 
		// to be run by WPAdUtility::do_settings_page().
		add_action( 'admin_menu', array('WPAdUtility', 'admin_menu') );

		add_action( 'admin_init', array('WPAdUtility', 'admin_init') );

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

	public static function admin_menu() {
		add_options_page( 'Ad Utility Settings', 'Ad Utility', 'manage_options', 'ad_utility_settings', array('WPAdUtility', 'do_settings_page') );
	}

	public static function do_settings_page() {
		if ( !current_user_can( 'manage_options' ) )  {
	        wp_die( __( 'You do not have sufficient permissions to access this page.' ) );
	    }
		?>
	    <div class="wrap">
	    	<div id="icon-options-general" class="icon32"><br /></div>
	    	<h2>Ad Utility Settings</h2>

			<form method="post" action="options.php">
				<table class="form-table">
					<tbody>
						<?php do_settings_sections('ad_utility_settings_page'); ?>
					</tbody>
				</table>

				<p class="submit">
					<input name="Submit" type="submit" class="button-primary" value="<?php esc_attr_e( 'Save Changes' ); ?>"/>
				</p>

				<?php settings_fields('ad_utility_settings_group'); ?>	
			</form>
	    </div>
	    <?php
	}

	public static function admin_init() {
	    // Add the settings section that all of our fields will belong to (heading not shown).
	    add_settings_section('ad_utility_settings_section', '', array('WPAdUtility', 'ad_utility_settings_section_text'), 'ad_utility_settings_page');

	    // Add the "Network ID" field, with a blank title, registered to the group "ad_utility_settings_group", output HTML using the function 
	    // WPAdUtility::settings_page_network_id_field() as part of the table of settings for "ad_utility_settings_page" in a section of "ad_utility_settings_section".
		add_settings_field('ad_utility_network_code', '', array('WPAdUtility', 'settings_page_network_id_field'), 'ad_utility_settings_page', 'ad_utility_settings_section');

		// Register the setting (WordPress "option") of "ad_utility_network_code" in the settings group "ad_utility_settings_group".
	    register_setting('ad_utility_settings_group', 'ad_utility_network_code');
	}

	public static function ad_utility_settings_section_text() {
		1;
	}

	public static function settings_page_network_id_field() {
		// Retrieve the URL for the external header.
		$ad_utility_network_code = get_option('ad_utility_network_code', '');
		?>
		<tr valign="top">
			<th colspan="2">
				<p style="font-weight: normal;">
					Below, you'll need to input the Google Ad Manager network ID value; it'll be something like <b>61175202</b>.
				</p>
			</th>
		</tr>

		<tr valign="top">
			<th scope="row"><label for="ad_utility_network_code">Network ID</label></th>
			<td>
				<input name="ad_utility_network_code" type="text" id="ad_utility_network_code" value="<?php echo $ad_utility_network_code; ?>" class="regular-text code" style="width: 600px;" />
			</td>
		</tr>
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

		$ad_utility_network_code = get_option('ad_utility_network_code', '');

		?>
		<script> window.wp_adutility_network_code = '<?php echo $ad_utility_network_code; ?>'; window.wp_adutility_mobile_px_max = 727; window.wp_adutility_page_options = '<?php echo $wp_adutility_page_options; ?>'; </script>
		<?php
	}
}


WPAdUtility::init();
?>