<?php
wp_enqueue_style('fep-style');
wp_enqueue_script('fep-script');
wp_enqueue_media();

$current_user = wp_get_current_user();
$post = false;
$post_id = -1;
$featured_img_html = '';
if (isset($_GET['fep_id']) && isset($_GET['fep_action']) && $_GET['fep_action'] == 'edit') {
	$post_id = $_GET['fep_id'];
	$p = get_post($post_id, 'ARRAY_A');
	if ($p['post_author'] != $current_user->ID) return __("You don't have permission to edit this post", 'frontend-publishing');
	$category = get_the_category($post_id);
	$tags = wp_get_post_tags($post_id, array('fields' => 'names'));
	$featured_img = get_post_thumbnail_id($post_id);
	$featured_img_html = (!empty($featured_img)) ? wp_get_attachment_image($featured_img, array(200, 200)) : '';
	$post = array(
		'title'            => $p['post_title'],
		'content'          => $p['post_content'],
		'about_the_author' => get_post_meta($post_id, 'about_the_author', true)
	);
	if (isset($category[0]) && is_array($category))
		$post['category'] = $category[0]->cat_ID;
	if (isset($tags) && is_array($tags))
		$post['tags'] = implode(', ', $tags);
}
?>
<noscript>
	<div id="no-js"
		 class="warning"><?php _e('This form needs JavaScript to function properly. Please turn on JavaScript and try again!', 'frontend-publishing'); ?></div>
</noscript>
<div id="fep-new-post">
	<div id="fep-message" class="alert alert-warning"></div>
	<form id="fep-submission-form">



		<ul class="nav nav-tabs" role="tablist">
			<li role="presentation" class="active"><a href="#report" role="tab" data-toggle="tab">Lapor</a></li>
			<li role="presentation"><a href="#clarification" role="tab" data-toggle="tab">Klarifikasi</a></li>
		</ul>

		<!-- Tab panes -->
		<div class="tab-content">
			<div role="tabpanel" class="tab-pane active" id="report">
				

				<div class="form-group">
					<label for="fep-source-url"><?php _e('URL Berita', 'frontend-publishing'); ?></label>
					<input class="form-control" type="text" name="source_url" id="fep-source-url" value="<?php echo (isset($post['source_url'])) ? $post['source_url'] : ''; ?>">
				</div>

				<div class="form-group">
					<label for="fep-post-title"><?php _e('Judul Berita', 'frontend-publishing'); ?></label>
					<input class="form-control" type="text" name="post_title" id="fep-post-title" value="<?php echo ($post) ? $post['title'] : ''; ?>">
				</div>

				<div class="form-group">
					<label for="fep-post-excerpt"><?php _e('Isi Berita', 'frontend-publishing'); ?></label>
					<textarea class="form-control" name="post_excerpt" id="fep-post-excerpt" rows="6"><?php echo (isset($post['excerpt'])) ? $post['excerpt'] : ''; ?></textarea>
				</div>

				<div class="form-group">
					<div id="fep-featured-image">
						<div id="fep-featured-image-container"><?php echo $featured_img_html; ?></div>
						<a id="fep-featured-image-link" class="btn btn-primary" href="#"><?php _e('Tambah Gambar', 'frontend-publishing'); ?></a>
						<input type="hidden" id="fep-featured-image-id" value="<?php echo (!empty($featured_img)) ? $featured_img : '-1'; ?>"/>
					</div>
				</div>

				<div class="form-group">
					<label for="fep-category"><?php _e('Kategori', 'frontend-publishing'); ?></label>
					<?php wp_dropdown_categories(array('id' => 'fep-category', 'class'=>'form-control', 'hide_empty' => 0, 'name' => 'post_category', 'orderby' => 'name', 'selected' => $post['category'], 'hierarchical' => true, 'show_option_none' => __('None', 'frontend-publishing'))); ?>	
				</div>

				<div class="form-group">
					<label for="fep-tags"><?php _e('Tags', 'frontend-publishing'); ?></label>
					<input class="form-control" type="text" name="post_tags" id="fep-tags" value="<?php echo ($post) ? $post['tags'] : ''; ?>">
				</div>

				<div class="form-group">
					<label for="fep-figure"><?php _e('Tokoh Terkait', 'frontend-publishing'); ?></label>
					<input class="form-control" type="text" name="figure" id="fep-figure" value="<?php echo (isset($post['figure'])) ? $post['figure'] : ''; ?>">
				</div>


			</div>
			<!-- tab -->

			<div role="tabpanel" class="tab-pane" id="clarification">

				<div class="form-group">
					<label for="fep-post-content"><?php _e('Klarifikasi', 'frontend-publishing'); ?></label>
					<?php
						$enable_media = (isset($fep_roles['enable_media']) && $fep_roles['enable_media']) ? current_user_can($fep_roles['enable_media']) : 1;
						wp_editor($post['content'], 'fep-post-content', $settings = array('textarea_name' => 'post_content', 'textarea_rows' => 7, 'media_buttons' => $enable_media));
						wp_nonce_field('fepnonce_action', 'fepnonce');
					?>	
					
				<!-- </div> not used there is already closing tag from wp editor -->

				<div class="form-group">
					<label for="fep-label"><?php _e('Kesimpulan', 'frontend-publishing'); ?></label>
					<?php wp_dropdown_categories(array('id' => 'fep-label', 'class'=>'form-control', 'hide_empty' => 0, 'name' => 'label', 'taxonomy'=>'label', 'orderby' => 'name', 'selected' => $post['label'], 'hierarchical' => true, 'show_option_none' => __('None', 'frontend-publishing'))); ?>	
				</div>

			</div>
			<!-- tab -->
		</div>
		<!-- tab-content -->




		<input type="hidden" name="about_the_author" id="fep-about" value="-1">
		<input type="hidden" name="post_id" id="fep-post-id" value="<?php echo $post_id ?>">

		<div class="form-group">
			<button class="btn btn-primary active-btn" type="button" id="fep-submit-post"><?php _e('Submit', 'frontend-publishing'); ?></button> 
			<img class="fep-loading-img" src="<?php echo plugins_url('static/img/ajax-loading.gif', dirname(__FILE__)); ?>"/>
		</div>

	</form>
</div>