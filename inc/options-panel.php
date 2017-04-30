<?php
/*
The settings page
*/

function lh_menu_item()
{
	global $lh_settings_page_hook;
	$lh_settings_page_hook = add_menu_page(
		__('Lapor Hoax Settings', 'lapor-hoax'),
		__('Lapor Hoax', 'lapor-hoax'),
		'manage_options',
		'lh_settings',
		'lh_render_settings_page'
	);
}

add_action('admin_menu', 'lh_menu_item');

function lh_scripts_styles($hook)
{
	global $lh_settings_page_hook;
	if ($lh_settings_page_hook != $hook)
		return;

	wp_enqueue_style("lh_options_panel_stylesheet", plugins_url("static/css/options-panel.css", dirname(__FILE__)), false, "1.0", "all");
	wp_enqueue_script("lh_options_panel_script", plugins_url("static/js/options-panel.js", dirname(__FILE__)), false, "1.0");
	wp_enqueue_script('common');
	wp_enqueue_script('wp-lists');
	wp_enqueue_script('postbox');
}

add_action('admin_enqueue_scripts', 'lh_scripts_styles');

function lh_render_settings_page()
{
	?>
	<div class="wrap">
		<div id="icon-options-general" class="icon32"></div>
		<h2><?php _e('Lapor Hoax Settings', 'lapor-hoax'); ?></h2>
		<?php settings_errors(); ?>
		<div class="clearfix paddingtop20">
			<!-- <div class="first ninecol"> -->
				<form method="post" action="options.php">
					<?php settings_fields('lh_settings'); ?>
					<?php do_meta_boxes('lh_metaboxes', 'advanced', null); ?>
					<?php wp_nonce_field('closedpostboxes', 'closedpostboxesnonce', false); ?>
					<?php wp_nonce_field('meta-box-order', 'meta-box-order-nonce', false); ?>
				</form>
			<!-- </div> -->
		</div>
	</div>
<?php }

function lh_create_options()
{
	add_settings_section('lh_restrictions_section', null, null, 'lh_settings');
	add_settings_section('lh_role_section', null, null, 'lh_settings');
	add_settings_section('lh_misc_section', null, null, 'lh_settings');

	add_settings_field(
		'title_word_count', '', 'lh_render_settings_field', 'lh_settings', 'lh_restrictions_section',
		array(
			'title' => __('Title Word Count', 'lapor-hoax'),
			'desc'  => __('Required word count for article title', 'lapor-hoax'),
			'id'    => 'title_word_count',
			'type'  => 'multitext',
			'items' => array(
				'min_words_title' => __('Minimum', 'lapor-hoax'),
				'max_words_title' => __('Maximum', 'lapor-hoax')
			),
			'group' => 'lh_post_restrictions'
		)
	);
	add_settings_field(
		'content_word_count', '', 'lh_render_settings_field', 'lh_settings', 'lh_restrictions_section',
		array(
			'title' => __('Content Word Count', 'lapor-hoax'),
			'desc'  => __('Required word count for article content', 'lapor-hoax'),
			'id'    => 'content_word_count',
			'type'  => 'multitext',
			'items' => array(
				'min_words_content' => __('Minimum', 'lapor-hoax'),
				'max_words_content' => __('Maximum', 'lapor-hoax')
			),
			'group' => 'lh_post_restrictions'
		)
	);
	add_settings_field(
		'bio_word_count', '', 'lh_render_settings_field', 'lh_settings', 'lh_restrictions_section',
		array(
			'title' => __('Bio Word Count', 'lapor-hoax'),
			'desc'  => __('Required word count for author bio', 'lapor-hoax'),
			'id'    => 'bio_word_count',
			'type'  => 'multitext',
			'items' => array(
				'min_words_bio' => __('Minimum', 'lapor-hoax'),
				'max_words_bio' => __('Maximum', 'lapor-hoax')
			),
			'group' => 'lh_post_restrictions'
		)
	);
	add_settings_field(
		'tag_count', '', 'lh_render_settings_field', 'lh_settings', 'lh_restrictions_section',
		array(
			'title' => __('Tag Count', 'lapor-hoax'),
			'desc'  => __('Required number of tags', 'lapor-hoax'),
			'id'    => 'tag_count',
			'type'  => 'multitext',
			'items' => array(
				'min_tags' => __('Minimum', 'lapor-hoax'),
				'max_tags' => __('Maximum', 'lapor-hoax')
			),
			'group' => 'lh_post_restrictions'
		)
	);
	add_settings_field(
		'max_links', '', 'lh_render_settings_field', 'lh_settings', 'lh_restrictions_section',
		array(
			'title' => __('Maximum Links in Body', 'lapor-hoax'),
			'desc'  => '',
			'id'    => 'max_links',
			'type'  => 'text',
			'group' => 'lh_post_restrictions'
		)
	);
	add_settings_field(
		'max_links_bio', '', 'lh_render_settings_field', 'lh_settings', 'lh_restrictions_section',
		array(
			'title' => __('Maximum links in bio', 'lapor-hoax'),
			'desc'  => '',
			'id'    => 'max_links_bio',
			'type'  => 'text',
			'group' => 'lh_post_restrictions'
		)
	);
	add_settings_field(
		'thumbnail_required', '', 'lh_render_settings_field', 'lh_settings', 'lh_restrictions_section',
		array(
			'title' => __('Make featured image required', 'lapor-hoax'),
			'desc'  => '',
			'id'    => 'thumbnail_required',
			'type'  => 'checkbox',
			'group' => 'lh_post_restrictions'
		)
	);
	$user_roles = array(
		0                      => __('No one', 'lapor-hoax'),
		'update_core'          => __('Administrator', 'lapor-hoax'),
		'moderate_comments'    => __('Editor', 'lapor-hoax'),
		'edit_published_posts' => __('Author', 'lapor-hoax'),
		'edit_posts'           => __('Contributor', 'lapor-hoax'),
		'read'                 => __('Subscriber', 'lapor-hoax')
	);
	add_settings_field(
		'no_check', '', 'lh_render_settings_field', 'lh_settings', 'lh_role_section',
		array(
			'title'   => __('Disable checks for', 'lapor-hoax'),
			'desc'    => __('Submissions by users of this level and levels higher than this will not be checked', 'lapor-hoax'),
			'id'      => 'no_check',
			'type'    => 'select',
			'options' => $user_roles,
			'group'   => 'lh_role_settings'
		)
	);
	add_settings_field(
		'instantly_publish', '', 'lh_render_settings_field', 'lh_settings', 'lh_role_section',
		array(
			'title'   => __('Instantly publish posts by', 'lapor-hoax'),
			'desc'    => __('Submissions by users of this level and levels higher than this will be instantly published', 'lapor-hoax'),
			'id'      => 'instantly_publish',
			'type'    => 'select',
			'options' => $user_roles,
			'group'   => 'lh_role_settings'
		)
	);

	$media_roles = $user_roles;
	$media_roles[0] = __('Everybody', 'lapor-hoax');
	add_settings_field(
		'enable_media', '', 'lh_render_settings_field', 'lh_settings', 'lh_role_section',
		array(
			'title'   => __('Display media buttons to', 'lapor-hoax'),
			'desc'    => __('Users of this level and levels higher than this will see the media buttons', 'lapor-hoax'),
			'id'      => 'enable_media',
			'type'    => 'select',
			'options' => $media_roles,
			'group'   => 'lh_role_settings'
		)
	);

	add_settings_field(
		'before_author_bio', '', 'lh_render_settings_field', 'lh_settings', 'lh_misc_section',
		array(
			'title' => __('Display before bio', 'lapor-hoax'),
			'desc'  => __('The contents of this textarea will be placed before the author bio throughout the website (If author bios are visible)', 'lapor-hoax'),
			'id'    => 'before_author_bio',
			'type'  => 'textarea',
			'group' => 'lh_misc'
		)
	);

	add_settings_field(
		'disable_author_bio', '', 'lh_render_settings_field', 'lh_settings', 'lh_misc_section',
		array(
			'title' => __('Disable Author Bio', 'lapor-hoax'),
			'desc'  => __('Check to disable and hide the author bio field on the submission form. Author bios will still be visible on the website', 'lapor-hoax'),
			'id'    => 'disable_author_bio',
			'type'  => 'checkbox',
			'group' => 'lh_misc'
		)
	);
	add_settings_field(
		'remove_bios', '', 'lh_render_settings_field', 'lh_settings', 'lh_misc_section',
		array(
			'title' => __('Hide all Author Bios', 'lapor-hoax'),
			'desc'  => __('Check to hide author bios from the website', 'lapor-hoax'),
			'id'    => 'remove_bios',
			'type'  => 'checkbox',
			'group' => 'lh_misc'
		)
	);
	add_settings_field(
		'nofollow_body_links', '', 'lh_render_settings_field', 'lh_settings', 'lh_misc_section',
		array(
			'title' => __('Nofollow Body Links', 'lapor-hoax'),
			'desc'  => __('The nofollow attribute will be added to all links in article content', 'lapor-hoax'),
			'id'    => 'nofollow_body_links',
			'type'  => 'checkbox',
			'group' => 'lh_misc'
		)
	);
	add_settings_field(
		'nofollow_bio_links', '', 'lh_render_settings_field', 'lh_settings', 'lh_misc_section',
		array(
			'title' => __('Nofollow Bio Links', 'lapor-hoax'),
			'desc'  => __('The nofollow attribute will be added to all links in author bio'),
			'id'    => 'nofollow_bio_links',
			'type'  => 'checkbox',
			'group' => 'lh_misc'
		)
	);
	add_settings_field(
		'disable_login_redirection', '', 'lh_render_settings_field', 'lh_settings', 'lh_misc_section',
		array(
			'title' => __('Disable Redirection to Login Page', 'lapor-hoax'),
			'desc'  => __('Instead of being sent to the login page, users will be shown an error message', 'lapor-hoax'),
			'id'    => 'disable_login_redirection',
			'type'  => 'checkbox',
			'group' => 'lh_misc'
		)
	);
	add_settings_field(
		'posts_per_page', '', 'lh_render_settings_field', 'lh_settings', 'lh_misc_section',
		array(
			'title' => __('Posts Per Page', 'lapor-hoax'),
			'desc'  => __('Number of posts to display at a time on the interface created with the help of [lh_article_list]', 'lapor-hoax'),
			'id'    => 'posts_per_page',
			'type'  => 'text',
			'group' => 'lh_misc'
		)
	);
	// Finally, we register the fields with WordPress
	register_setting('lh_settings', 'lh_post_restrictions', 'lh_settings_validation');
	register_setting('lh_settings', 'lh_role_settings', 'lh_settings_validation');
	register_setting('lh_settings', 'lh_misc', 'lh_settings_validation');

} // end sandbox_initialize_theme_options 
add_action('admin_init', 'lh_create_options');

function lh_settings_validation($input)
{
	return $input;
}

function lh_add_meta_boxes()
{
	add_meta_box("lh_post_restrictions_metabox", __('Post Restrictions', 'lapor-hoax'), "lh_metaboxes_callback", "lh_metaboxes", 'advanced', 'default', array('settings_section' => 'lh_restrictions_section'));
	add_meta_box("lh_role_settings_metabox", __('Role Settings', 'lapor-hoax'), "lh_metaboxes_callback", "lh_metaboxes", 'advanced', 'default', array('settings_section' => 'lh_role_section'));
	add_meta_box("lh_misc_metabox", __('Misc Settings', 'lapor-hoax'), "lh_metaboxes_callback", "lh_metaboxes", 'advanced', 'default', array('settings_section' => 'lh_misc_section'));
}

add_action('admin_init', 'lh_add_meta_boxes');

function lh_metaboxes_callback($post, $args)
{
	do_settings_fields("lh_settings", $args['args']['settings_section']);
	submit_button(__('Save Changes', 'lapor-hoax'), 'secondary');
}

function lh_render_settings_field($args)
{
	$option_value = get_option($args['group']);
	?>
	<div class="row clearfix">
		<div class="col colone"><?php echo $args['title']; ?></div>
		<div class="col coltwo">
			<?php if ($args['type'] == 'text'): ?>
				<input type="text" id="<?php echo $args['id'] ?>"
					   name="<?php echo $args['group'] . '[' . $args['id'] . ']'; ?>"
					   value="<?php echo (isset($option_value[ $args['id'] ])) ? esc_attr($option_value[ $args['id'] ]) : ''; ?>">
			<?php elseif ($args['type'] == 'select'): ?>
				<select name="<?php echo $args['group'] . '[' . $args['id'] . ']'; ?>" id="<?php echo $args['id']; ?>">
					<?php foreach ($args['options'] as $key => $option) { ?>
						<option <?php if (isset($option_value[ $args['id'] ])) selected($option_value[ $args['id'] ], $key);
						echo 'value="' . $key . '"'; ?>><?php echo $option; ?></option><?php } ?>
				</select>
			<?php elseif ($args['type'] == 'checkbox'): ?>
				<input type="hidden" name="<?php echo $args['group'] . '[' . $args['id'] . ']'; ?>" value="0"/>
				<input type="checkbox" name="<?php echo $args['group'] . '[' . $args['id'] . ']'; ?>"
					   id="<?php echo $args['id']; ?>"
					   value="true" <?php if (isset($option_value[ $args['id'] ])) checked($option_value[ $args['id'] ], 'true'); ?> />
			<?php elseif ($args['type'] == 'textarea'): ?>
				<textarea name="<?php echo $args['group'] . '[' . $args['id'] . ']'; ?>"
						  type="<?php echo $args['type']; ?>" cols=""
						  rows=""><?php echo isset($option_value[ $args['id'] ]) ? stripslashes(esc_textarea($option_value[ $args['id'] ])) : ''; ?></textarea>
			<?php elseif ($args['type'] == 'multicheckbox'):
				foreach ($args['items'] as $key => $checkboxitem):
					?>
					<input type="hidden" name="<?php echo $args['group'] . '[' . $args['id'] . '][' . $key . ']'; ?>"
						   value="0"/>
					<label
						for="<?php echo $args['group'] . '[' . $args['id'] . '][' . $key . ']'; ?>"><?php echo $checkboxitem; ?></label>
					<input type="checkbox" name="<?php echo $args['group'] . '[' . $args['id'] . '][' . $key . ']'; ?>"
						   id="<?php echo $args['group'] . '[' . $args['id'] . '][' . $key . ']'; ?>" value="1"
						   <?php if ($key == 'reason'){ ?>checked="checked" disabled="disabled"<?php } else {
						checked($option_value[ $args['id'] ][ $key ]);
					} ?> />
				<?php endforeach; ?>
			<?php elseif ($args['type'] == 'multitext'):
				foreach ($args['items'] as $key => $textitem):
					?>
					<label for="<?php echo $args['group'] . '[' . $key . ']'; ?>"><?php echo $textitem; ?></label>
					<input type="text" id="<?php echo $args['group'] . '[' . $key . ']'; ?>" class="multitext"
						   name="<?php echo $args['group'] . '[' . $key . ']'; ?>"
						   value="<?php echo (isset($option_value[ $key ])) ? esc_attr($option_value[ $key ]) : ''; ?>">
				<?php endforeach; endif; ?>
		</div>
		<div class="col colthree">
			<small><?php echo $args['desc'] ?></small>
		</div>
	</div>
	<?php
}

?>