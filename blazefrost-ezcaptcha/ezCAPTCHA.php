<?php

/**
 * Blazefrost ezCAPTCHA Plugin, helps set up Google reCAPTCHA v3 on WordPress sites.
 *
 * @package Blazefrost ezCAPTCHA Plugin
 * @author Blazefrost OÜ
 * @license GNU General Public License v3.0
 * @link https://digitaalne.ee
 * @copyright 2022 Blazefrost OÜ. All rights reserved.
 *
 *            @wordpress-plugin
 *            Plugin Name: ezCAPTCHA
 *            Plugin URI: https://digitaalne.ee
 *            Description: ezCAPTCHA - helps set up Google reCAPTCHA v3 on WordPress sites.
 *            Version: 0.1
 *            Author: Blazefrost OÜ
 *            Author URI: https://digitaalne.ee
 *            Text Domain: blazefrost-ezcaptcha
 *            Contributors: Blazefrost OÜ
 *            License: GNU General Public License v3.0
 *            License URI: https://github.com/KristoKalnin/ezCAPTCHA/blob/main/LICENSE
 */

/**
 * Adding Submenu under Settings Tab
 *
 */
function ezcaptcha_add_menu()
{
	// add_submenu_page( string $parent_slug, string $page_title, string $menu_title, string $capability, string $menu_slug, callable $function = '', int $position = null )
	$parent_slug = "options-general.php";
	$page_title = "ezCAPTCHA - Google reCAPTCHA V3";
	$menu_title = "ezCAPTCHA";
	$capability = "manage_options";
	$menu_slug = "ezCAPTCHA";
	$function = "ezcaptcha_page";
	add_submenu_page($parent_slug, $page_title, $menu_title, $capability, $menu_slug, $function);
}
add_action("admin_menu", "ezcaptcha_add_menu");

/**
 * Add settings link to the link in plugin view page
 *
 */


function ezcaptcha_settings_plugin_link($links, $file)
{
	if ($file == plugin_basename(dirname(__FILE__) . '/ezCAPTCHA.php')) {
		/*
         * Insert the link at the beginning
         */
		$in = '<a href="options-general.php?page=ezCAPTCHA">' . __('Settings', 'mtt') . '</a>';
		array_unshift($links, $in);

		/*
         * Insert at the end
         */
		// $links[] = '<a href="options-general.php?page=ezCAPTCHA">'.__('Settings','mtt').'</a>';
	}
	return $links;
}
add_filter('plugin_action_links', 'ezcaptcha_settings_plugin_link', 10, 2);

/**
 * Setting Page Options
 * - add setting page
 * - save setting page
 *
 */
function ezcaptcha_page()
{
?>
	<div class="wrap">
		<h1>
			ezCAPTCHA
		</h1>

		<form method="post" action="options.php">
			<?php
			// settings_fields( string $option_group )
			$option = "ezcaptcha_config";
			settings_fields($option);

			// do_settings_sections( string $page )
			$page = "blazefrost-ezcaptcha";
			do_settings_sections($page);
			submit_button();
			?>
		</form>
	</div>

<?php
}

/**
 * Init setting section, Init setting field and register settings page
 *
 */
function ezcaptcha_settings()
{
	// add_settings_section( string $id, string $title, callable $callback, string $page )
	$_id = "ezcaptcha_config";
	$_title = "";
	$_callback = null;
	$_page = "blazefrost-ezcaptcha";
	add_settings_section($_id, $_title, $_callback, $_page);

	//add_settings_field( string $id, string $title, callable $callback, string $page, string $section = 'default', array $args = array() )
	$id = "blazefrost-recaptcha-site-key";
	$title = "Title placeholder";
	$callback = "ezcaptcha_options";
	$page = "blazefrost-ezcaptcha";
	$section = "ezcaptcha_config";
	add_settings_field($id, $title, $callback, $page, $section);

	$id2 = "blazefrost-recaptcha-secret-key";
	$callback2 = "ezcaptcha_options2";
	add_settings_field($id2, $title, $callback2, $page, $section);

	//do_action( 'register_setting', string $option_group, string $option_name, array $args )
	$option_group = "ezcaptcha_config";
	$option_name = "blazefrost-recaptcha-site-key";
	$option_name2 = "blazefrost-recaptcha-secret-key";
	register_setting($option_group, $option_name);
	register_setting($option_group, $option_name2);
}
add_action("admin_init", "ezcaptcha_settings");

/**
 * Add simple textfield value to setting page
 *
 */
function ezcaptcha_options()
{
?>
	<div class="postbox" style="width: 65%; padding: 30px;">
		<label for="blazefrost-recaptcha-site-key">Site key:</label>
		<input type="text" id="blazefrost-recaptcha-site-key" name="blazefrost-recaptcha-site-key" value="<?php echo stripslashes_deep(esc_attr(get_option('blazefrost-recaptcha-site-key'))); ?>" />
	</div>
<?php
}

function ezcaptcha_options2()
{
?>
	<div class="postbox" style="width: 65%; padding: 30px;">
		<label for="blazefrost-recaptcha-secret-key">Secret key:</label>
		<input type="text" id="blazefrost-recaptcha-secret-key" name="blazefrost-recaptcha-secret-key" value="<?php echo stripslashes_deep(esc_attr(get_option('blazefrost-recaptcha-secret-key'))); ?>" />
	</div>
	<?php
}

/**
 * Show message to admin after activating the plugin
 * 
 * https://stackoverflow.com/questions/38233751/show-message-after-activating-wordpress-plugin
 */

register_activation_hook(__FILE__, 'admin_notice_activation_hook');

function admin_notice_activation_hook()
{
	set_transient('admin-notice-transient', true, 5);
}

add_action('admin_notices', 'admin_notice_activation_notice');

function admin_notice_activation_notice()
{

	/* Check transient, if available display notice */
	if (get_transient('admin-notice-transient')) {
	?>
		<div class="updated notice is-dismissible">
			<p><strong>ezCAPTCHA</strong> has been activated! Don't forget to configure it in <strong>Settings → ezCAPTCHA</strong>.</p>
		</div>
<?php
		/* Delete transient, only display this notice once. */
		delete_transient('admin-notice-transient');
	}
}

add_action('wp_footer', 'add_recaptcha');
function add_recaptcha()
{
	$site_key = stripslashes_deep(esc_attr(get_option('blazefrost-recaptcha-site-key')));

	echo "<script src='https://www.google.com/recaptcha/api.js?render=",$site_key,"'></script>";
	"<script>
		grecaptcha.ready(function() {
		grecaptcha.execute('"+ $site_key + "', {action: 'homepage'}).then(function(token) {
			$('form').prepend('<input type='hidden' name='g-recaptcha-response' value='" + token +"'>');
		});
	  });
	</script>"
	;
}
