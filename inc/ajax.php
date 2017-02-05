<?php

/**
 * Matches submitted content against restrictions set in the options panel
 *
 * @param array $content The content to check
 * @return string: An html string of errors
 */
function lh_post_has_errors($content)
{
	$lh_plugin_options = get_option('lh_post_restrictions');
	$lh_messages = lh_messages();
	$min_words_title = $lh_plugin_options['min_words_title'];
	$max_words_title = $lh_plugin_options['max_words_title'];
	$min_words_content = $lh_plugin_options['min_words_content'];
	$max_words_content = $lh_plugin_options['max_words_content'];
	
	$max_links = $lh_plugin_options['max_links'];

	$min_tags = $lh_plugin_options['min_tags'];
	$max_tags = $lh_plugin_options['max_tags'];
	$thumb_required = $lh_plugin_options['thumbnail_required'];
	$error_string = '';
	$format = '<li>%s</li>';

	if(!isset($content['post_title']) || !$content['post_title']){
		$error_string .= sprintf($format, __('Post title is required', 'lapor-hoax'));
	}
	if(!isset($content['post_excerpt']) || !$content['post_excerpt']){
		$error_string .= sprintf($format, __('Post content is required', 'lapor-hoax'));
	}
	if(!isset($content['post_tags']) || !$content['post_tags']){
		$error_string .= sprintf($format, __('Post tags is required', 'lapor-hoax'));
	}
	if(!isset($content['source_url']) || !$content['source_url']){
		$error_string .= sprintf($format, __('Source URL is required', 'lapor-hoax'));
	}

	$tags_array = explode(',', $content['post_tags']);
	$stripped_content = strip_tags($content['post_content']);

	if (!empty($content['post_title']) && str_word_count($content['post_title']) < $min_words_title)
		$error_string .= sprintf($format, $lh_messages['title_short_error']);
	if (!empty($content['post_title']) && str_word_count($content['post_title']) > $max_words_title)
		$error_string .= sprintf($format, $lh_messages['title_long_error']);
	if (!empty($content['post_content']) && str_word_count($stripped_content) < $min_words_content)
		$error_string .= sprintf($format, $lh_messages['article_short_error']);
	if (str_word_count($stripped_content) > $max_words_content)
		$error_string .= sprintf($format, $lh_messages['article_long_error']);
	

	// if (substr_count($content['post_content'], '</a>') > $max_links)
	// 	$error_string .= sprintf($format, $lh_messages['too_many_article_links_error']);
	

	if (!empty($content['post_tags']) && count($tags_array) < $min_tags)
		$error_string .= sprintf($format, $lh_messages['too_few_tags_error']);
	if (count($tags_array) > $max_tags)
		$error_string .= sprintf($format, $lh_messages['too_many_tags_error']);
	if ($thumb_required == 'true' && $content['featured_img'] == -1)
		$error_string .= sprintf($format, $lh_messages['featured_image_error']);

	if (str_word_count($error_string) < 2)
		return false;
	else
		return '<ul>'.$error_string.'</ul>';
}

/**
 * Ajax function for fetching a featured image
 *
 * @uses array $_POST The id of the image
 * @return string: A JSON encoded string
 */
function lh_fetch_featured_image()
{
	$image_id = $_POST['img'];
	echo wp_get_attachment_image($image_id, array(200, 200));
	die();
}

add_action('wp_ajax_lh_fetch_featured_image', 'lh_fetch_featured_image');

/**
 * Ajax function for deleting a post
 *
 * @uses array $_POST The id of the post and a nonce value
 * @return string: A JSON encoded string
 */
function lh_delete_posts()
{
	try {
		if (!wp_verify_nonce($_POST['delete_nonce'], 'fepnonce_delete_action'))
			throw new Exception(__('Sorry! You failed the security check', 'lapor-hoax'), 1);

		if (!current_user_can('delete_post', $_POST['post_id']))
			throw new Exception(__("You don't have permission to delete this post", 'lapor-hoax'), 1);

		$result = wp_delete_post($_POST['post_id'], true);
		if (!$result)
			throw new Exception(__("The article could not be deleted", 'lapor-hoax'), 1);

		$data['success'] = true;
		$data['message'] = __('The article has been deleted successfully!', 'lapor-hoax');
	} catch (Exception $ex) {
		$data['success'] = false;
		$data['message'] = $ex->getMessage();
	}
	die(json_encode($data));
}

add_action('wp_ajax_lh_delete_posts', 'lh_delete_posts');
add_action('wp_ajax_nopriv_lh_delete_posts', 'lh_delete_posts');

/**
 * Ajax function for adding a new post.
 *
 * @uses array $_POST The user submitted post
 * @return string: A JSON encoded string
 */
function lh_process_form_input()
{
	$lh_messages = lh_messages();
	try {
		if (!wp_verify_nonce($_POST['post_nonce'], 'fepnonce_action'))
			throw new Exception(
				__("Sorry! You failed the security check", 'lapor-hoax'),
				1
			);

		if ($_POST['post_id'] != -1 && !current_user_can('edit_post', $_POST['post_id']))
			throw new Exception(
				__("You don't have permission to edit this post.", 'lapor-hoax'),
				1
			);

		$lh_role_settings = get_option('lh_role_settings');
		$lh_misc = get_option('lh_misc');

		if ($lh_role_settings['no_check'] && current_user_can($lh_role_settings['no_check'])){
			$errors = false;
		}
		else{
			$errors = lh_post_has_errors($_POST);
		}

		if ($errors){
			throw new Exception($errors, 1);
		}

		if ($lh_misc['nofollow_body_links']){
			$post_content = wp_rel_nofollow($_POST['post_content']);
			$post_excerpt = wp_rel_nofollow($_POST['post_excerpt']);
		}
		else{
			$post_content = $_POST['post_content'];
			$post_excerpt = $_POST['post_excerpt'];
		}

		$current_post = empty($_POST['post_id']) ? null : get_post($_POST['post_id']);
		$current_post_date = is_a($current_post, 'WP_Post') ? $current_post->post_date : '';

		$tax_input = array();
		if(isset($_POST['label'])){
			$tax_input['label'] = $_POST['label'];
		}

		if(isset($_POST['figure'])){
			$tax_input['figure'] = $_POST['figure'];
		}

		$meta_input = array();
		if(isset($_POST['source_url'])){
			$meta_input['source_url'] = $_POST['source_url'];
		}

		$new_post = array(
			'post_title'     => esc_sql($_POST['post_title']),
			'post_category'  => array($_POST['post_category']),
			'tags_input'     => sanitize_text_field($_POST['post_tags']),
			'post_content'   => wp_kses_post($post_content),
			'post_excerpt'   => wp_kses_post($post_excerpt),
			'post_date'      => $current_post_date,
			'tax_input'		  => $tax_input,
			'meta_input'     => $meta_input,
			'comment_status' => get_option('default_comment_status')
		);


		if ($lh_role_settings['instantly_publish'] && current_user_can($lh_role_settings['instantly_publish'])) {
			$post_action = __('published', 'lapor-hoax');
			$new_post['post_status'] = 'publish';
		} else {
			$post_action = __('submitted', 'lapor-hoax');
			$new_post['post_status'] = 'pending';
		}

		if ($_POST['post_id'] != -1) {
			$new_post['ID'] = $_POST['post_id'];
			$post_action = __('updated', 'lapor-hoax');
		}

		$new_post_id = wp_insert_post($new_post, true);
		if (is_wp_error($new_post_id))
			throw new Exception($new_post_id->get_error_message(), 1);

		// if (!$lh_misc['disable_author_bio']) {
		// 	if ($lh_misc['nofollow_bio_links'])
		// 		$about_the_author = wp_rel_nofollow($_POST['about_the_author']);
		// 	else
		// 		$about_the_author = $_POST['about_the_author'];
		// 	update_post_meta($new_post_id, 'about_the_author', $about_the_author);
		// }

		if ($_POST['featured_img'] != -1)
			set_post_thumbnail($new_post_id, $_POST['featured_img']);

		$data['success'] = true;
		$data['post_id'] = $new_post_id;
		$data['message'] = sprintf(
			'<h3>%s</h3><p class="no-margin"><a href="#" id="fep-continue-editing">%s</a></p>',
			sprintf(__('Your article has been %s successfully!', 'lapor-hoax'), $post_action),
			__('Continue Editing', 'lapor-hoax')
		);
	} catch (Exception $ex) {
		$data['success'] = false;
		$data['message'] = sprintf(
			'<h3>%s</h3><p>%s</p>',
			$lh_messages['general_form_error'],
			$ex->getMessage()
		);
	}
	die(json_encode($data));
}

add_action('wp_ajax_lh_process_form_input', 'lh_process_form_input');
add_action('wp_ajax_nopriv_lh_process_form_input', 'lh_process_form_input');