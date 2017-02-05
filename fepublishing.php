<?php
/*
Plugin Name: Lapor Hoax
Plugin URI: http://google.com/
Description: Accept guest posts without giving your authors access to the admin area.
Version: 1.0.0
Author: Lapor Hoax
Author URI: http://google.com/
Text Domain: lapor-hoax
Domain Path: /languages
License: GPL2
*/

/**
 * Loads the plugin's text domain for localization.
 **/
function lh_load_plugin_textdomain()
{
	load_plugin_textdomain('lapor-hoax', false, plugin_basename(dirname(__FILE__)) . '/languages');
}

add_action('plugins_loaded', 'lh_load_plugin_textdomain');

/**
 * Starts output buffer so that auth_redirect() can work in shortcodes
 */
function lh_start_output_buffers()
{
	ob_start();
}

add_action('init', 'lh_start_output_buffers');

/**
 * Initializes plugin options on first run
 */
function lh_initialize_options()
{
	$activation_flag = get_option('lh_misc');

	if ($activation_flag)
		return;

	$lh_restrictions = array(
		'min_words_title'    => 2,
		'max_words_title'    => 12,
		'min_words_content'  => 250,
		'max_words_content'  => 2000,
		'min_words_bio'      => 50,
		'max_words_bio'      => 100,
		'min_tags'           => 1,
		'max_tags'           => 5,
		'max_links'          => 2,
		'max_links_bio'      => 2,
		'thumbnail_required' => false
	);

	$lh_roles = array(
		'no_check'          => false,
		'instantly_publish' => false,
		'enable_media'      => false
	);

	$lh_misc = array(
		'before_author_bio'   => '',
		'disable_author_bio'  => false,
		'remove_bios'         => false,
		'nofollow_body_links' => false,
		'nofollow_bio_links'  => false,
		'posts_per_page'      => 10
	);

	update_option('lh_post_restrictions', $lh_restrictions);
	update_option('lh_role_settings', $lh_roles);
	update_option('lh_misc', $lh_misc);
}

register_activation_hook(__FILE__, 'lh_initialize_options');

function lh_messages()
{
	$lh_messages = array(
		'unsaved_changes_warning'      => __('You have unsaved changes. Proceed anyway?', 'lapor-hoax'),
		'confirmation_message'         => __('Are you sure?', 'lapor-hoax'),
		'media_lib_string'             => __('Choose Image', 'lapor-hoax'),
		'required_field_error'         => __('You missed one or more required fields', 'lapor-hoax'),
		'general_form_error'           => __('Your submission has errors. Please try again!', 'lapor-hoax'),
		'title_short_error'            => __('The title is too short', 'lapor-hoax'),
		'title_long_error'             => __('The title is too long', 'lapor-hoax'),
		'article_short_error'          => __('The article is too short', 'lapor-hoax'),
		'article_long_error'           => __('The article is too long', 'lapor-hoax'),
		'bio_short_error'              => __('The bio is too short', 'lapor-hoax'),
		'bio_long_error'               => __('The bio is too long', 'lapor-hoax'),
		'too_many_article_links_error' => __('There are too many links in the article body', 'lapor-hoax'),
		'too_many_bio_links_error'     => __('There are too many links in the bio', 'lapor-hoax'),
		'too_few_tags_error'           => __("You haven't added the required number of tags", 'lapor-hoax'),
		'too_many_tags_error'          => __('There are too many tags', 'lapor-hoax'),
		'featured_image_error'         => __('You need to choose a featured image', 'lapor-hoax')
	);

	return $lh_messages;
}

/**
 * Removes plugin data before uninstalling
 */
function lh_rollback()
{
	wp_deregister_style('fep-style');
	wp_deregister_script('fep-script');
	delete_option('lh_post_restrictions');
	delete_option('lh_role_settings');
	delete_option('lh_misc');
	delete_option('lh_messages');
}

register_uninstall_hook(__FILE__, 'lh_rollback');

/**
 * Enqueue scripts and stylesheets
 *
 * @param array $posts WordPress posts to check for the shortcode
 * @return array $posts Checked WordPress posts
 */
function lh_register_resources()
{
	wp_register_style('fep-style', plugins_url('static/css/style.css', __FILE__), array(), '1.0', 'all');
	wp_register_script("fep-script", plugins_url('static/js/scripts.js', __FILE__), array('jquery'));
	wp_localize_script('fep-script', 'fepajaxhandler', array('ajaxurl' => admin_url('admin-ajax.php')));
	$lh_rules = get_option('lh_post_restrictions');
	$lh_roles = get_option('lh_role_settings');
	$lh_rules['check_required'] = (isset($lh_roles['no_check']) && $lh_roles['no_check'] && current_user_can($lh_roles['no_check'])) ? 0 : 1;
	wp_localize_script('fep-script', 'lh_rules', $lh_rules);
	wp_localize_script('fep-script', 'lh_messages', lh_messages());
}

add_action('init', 'lh_register_resources');

/**
 * Append post meta (author bio) to post content
 *
 * @param string $content post content to append the bio to
 * @return array $posts modified post content
 */
function lh_add_author_bio($content)
{
	$lh_misc = get_option('lh_misc');
	global $post;
	$ID = $post->ID;
	$author_bio = get_post_meta($ID, 'about_the_author', true);
	if (!$author_bio || $lh_misc['remove_bios']) return $content;
	$before_bio = $lh_misc['before_author_bio'];
	ob_start();
	?>
	<?= $content ?><?= $before_bio ?>
	<div class="fep-author-bio"><?= $author_bio ?></div>
	<?php
	return ob_get_clean();
}

add_filter('the_content', 'lh_add_author_bio', 100);

/**
 * Scans content for shortcode.
 *
 * @param string $content post content to scan
 * @param string $tag shortcode text
 * @return bool: whether or not the shortcode exists in $content
 */
if (!function_exists('has_shortcode')) {
	function has_shortcode($content, $tag)
	{
		if (stripos($content, '[' . $tag . ']') !== false)
			return true;
		return false;
	}
}

/**********************************************
 *
 * Inlcuding modules
 *
 ***********************************************/

include('inc/ajax.php');

include('inc/shortcodes.php');

include('inc/options-panel.php');

//Kses for content and bio | featured image | custom fields | excerpts | custom post types