<?php
wp_enqueue_style('fep-style');
wp_enqueue_script('fep-script');
wp_enqueue_media();

$current_user = wp_get_current_user();
$status = isset($_GET['lh_type']) ? $_GET['lh_type'] : 'publish';
$paged = isset($_GET['lh_page']) ? $_GET['lh_page'] : 1;
$per_page = (isset($lh_misc['posts_per_page']) && is_numeric($lh_misc['posts_per_page'])) ? $lh_misc['posts_per_page'] : 10;
$author_posts = new WP_Query(array('posts_per_page' => $per_page, 'paged' => $paged, 'orderby' => 'DESC', 'author' => $current_user->ID, 'post_status' => $status));
$old_exist = ($paged * $per_page) < $author_posts->found_posts;
$new_exist = $paged > 1;
?>

<div id="fep-posts">
	<div id="fep-message" class="alert"></div>

	<ul class="nav nav-tabs">
		<li <?php echo ($status == 'publish') ? 'class="active"' : ''; ?>>
			<a href="?lh_type=publish"><?php _e('Live', 'lapor-hoax'); ?></a>
		</li>
		<li <?php echo ($status == 'pending') ? 'class="active"' : ''; ?>>
			<a href="?lh_type=pending"><?php _e('Pending', 'lapor-hoax'); ?></a>
		</li>
	</ul>
	<!-- tab header -->

	

	<div id="fep-post-table-container">

		<?php if (!$author_posts->have_posts()): ?>
			<p><?php _e('Nothing found.', 'lapor-hoax'); ?></p>
		<?php else: ?>
			<p><?php printf(__('%s article(s).', 'lapor-hoax'), $author_posts->found_posts); ?></p>
		<?php endif; ?>
		
		<table class="table table-striped" width="100%">
			<?php
			while ($author_posts->have_posts()) : $author_posts->the_post();
				$postid = get_the_ID();
				?>
				<tr id="fep-row-<?= $postid ?>" class="fep-row">
					<td><?php the_title(); ?></td>
					<td class="fep-fixed-td">
						<?php if ($status == 'publish'): ?>
							<a href="<?php the_permalink(); ?>" title="<?php _e('View Post', 'lapor-hoax'); ?>"><?php _e('View', 'lapor-hoax'); ?></a>
						<?php endif; ?>
						<a href="?lh_action=edit&lh_id=<?= $postid; ?><?= (isset($_SERVER['QUERY_STRING']) ? '&' . $_SERVER['QUERY_STRING'] : '') ?>"><?php _e('Edit', 'lapor-hoax'); ?></a>
						<span class="post-delete">
							<img id="fep-loading-img-<?= $postid ?>" class="fep-loading-img" src="<?php echo plugins_url('static/img/ajax-loading.gif', dirname(__FILE__)); ?>">
							<a href="#"><?php _e('Delete', 'lapor-hoax'); ?></a>
							<input type="hidden" class="post-id" value="<?= $postid ?>">
						</span>
					</td>
				</tr>
			<?php endwhile; ?>
		</table>

		<?php wp_nonce_field('fepnonce_delete_action', 'fepnonce_delete'); ?>

		<div class="fep-nav">
			<?php if ($new_exist): ?>
				<a class="fep-nav-link fep-nav-link-left" href="?lh_type=<?= $status ?>&lh_page=<?= ($paged - 1) ?>">
					&#10094; <?php _e('Newer Posts', 'lapor-hoax'); ?></a>
			<?php endif; ?>
			<?php if ($old_exist): ?>
				<a class="fep-nav-link fep-nav-link-right"
				   href="?lh_type=<?= $status ?>&lh_page=<?= ($paged + 1) ?>"><?php _e('Older Posts', 'lapor-hoax'); ?>
					&#10095;</a>
			<?php endif; ?>
			<div style="clear:both;"></div>
		</div>

		<?php 
			wp_reset_query();
			wp_reset_postdata(); 
		?>
	</div>
</div>