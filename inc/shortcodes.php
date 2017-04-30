<?php

/**
 * Creates shortcode lh_submission_form
 *
 * @return string: HTML content for the shortcode
 */
function lh_add_new_post()
{
	$lh_misc = get_option('lh_misc');
	$lh_roles = get_option('lh_role_settings');
	if (!is_user_logged_in()) {
		if (isset($lh_misc['disable_login_redirection']) && $lh_misc['disable_login_redirection']) {
			return sprintf(
				__('You need to %s to see this page.', 'lapor-hoax'),
				sprintf(
					'<a href="' . wp_login_url(get_permalink()) . '" title="%s">%s</a>',
					__('Login', 'lapor-hoax'),
					__('log in', 'lapor-hoax')
				)
			);
		} 
		else{
			auth_redirect();
			// wp_redirect(site_url('sample-page')); exit;
		} 
	}
	else{
		ob_start();
		include(dirname(dirname(__FILE__)) . '/views/submission-form.php');
		return ob_get_clean();
	}

	
}

add_shortcode('lh_submission_form', 'lh_add_new_post');

/**
 * Creates shortcode lh_article_list
 *
 * @return string: HTML content for the shortcode
 */
function lh_manage_posts()
{
	$lh_misc = get_option('lh_misc');
	if (!is_user_logged_in()) {
		if (isset($lh_misc['disable_login_redirection']) && $lh_misc['disable_login_redirection']) {
			return sprintf(
				__('You need to %s to see this page.', 'lapor-hoax'),
				sprintf(
					'<a href="' . wp_login_url(get_permalink()) . '" title="%s">%s</a>',
					__('Login', 'lapor-hoax'),
					__('log in', 'lapor-hoax')
				)
			);
		} else {
			auth_redirect();
			// wp_redirect(site_url('sample-page')); exit;
		}
	}

	ob_start();
	if (isset($_GET['lh_id']) && isset($_GET['lh_action']) && $_GET['lh_action'] == 'edit') {
		include(dirname(dirname(__FILE__)) . '/views/submission-form.php');
	} else {
		include(dirname(dirname(__FILE__)) . '/views/post-tabs.php');
	}
	return ob_get_clean();
}

add_shortcode('lh_article_list', 'lh_manage_posts');

?>