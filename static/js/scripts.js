jQuery(document).ready(function ($) {

	// Display a warning alert when the user has unsaved changes and tries to navigate away
	var has_changed = false;

	function confirm_exit() {
		var mce = typeof(tinyMCE) != 'undefined' ? tinyMCE.activeEditor : false;
		if (has_changed || (mce && !mce.isHidden() && mce.isDirty() )){
			return fep_messages.unsaved_changes_warning;
		}
	}

	window.onbeforeunload = confirm_exit;

	var submission_form = $('#fep-submission-form');
	$("input, textarea, #fep-post-content", submission_form).keydown(function () {
		has_changed = true;
	});
	$("select", submission_form).change(function () {
		has_changed = true;
	});

	// Validation functions
	function substr_count(string, sub_string) {
		var regex = new RegExp(sub_string, 'g');
		if (!string.match(regex) || !string || !sub_string)
			return 0;
		var count = string.match(regex);
		return count.length;
	}

	function str_word_count(string) {
		if (!string.length)
			return 0;
		string = string.replace(/(^\s*)|(\s*$)/gi, "");
		string = string.replace(/[ ]{2,}/gi, " ");
		string = string.replace(/\n /, "\n");
		return string.split(' ').length;
	}

	function count_tags(string) {
		if (!string.length)
			return 0;
		return string.split(',').length;
	}

	function get_post_errors(title, content, bio, category, tags, featured_image) {
		// var error_string = '';
		// if (fep_rules.check_required == false)
		// 	return false;

		// if ((fep_rules.min_words_title != 0 && title === '') || (fep_rules.min_words_content != 0 && content === '') || (fep_rules.min_words_bio != 0 && bio === '') || (fep_rules.min_tags != 0 && tags === ''))
		// 	error_string = fep_messages.required_field_error + '<br/>';

		// var stripped_content = content.replace(/(<([^>]+)>)/ig, "");
		// var stripped_bio = bio.replace(/(<([^>]+)>)/ig, "");

		// if (title != '' && str_word_count(title) < fep_rules.min_words_title)
		// 	error_string += fep_messages.title_short_error + '<br/>';

		// if (content != '' && str_word_count(title) > fep_rules.max_words_title)
		// 	error_string += fep_messages.title_long_error + '<br/>';

		// if (content != '' && str_word_count(stripped_content) < fep_rules.min_words_content)
		// 	error_string += fep_messages.article_short_error + '<br/>';

		// if (str_word_count(stripped_content) > fep_rules.max_words_content)
		// 	error_string += fep_messages.article_long_error + '<br/>';

		// if (bio != -1 && bio != '' && str_word_count(stripped_bio) < fep_rules.min_words_bio)
		// 	error_string += fep_messages.bio_short_error + '<br/>';

		// if (bio != -1 && str_word_count(stripped_bio) > fep_rules.max_words_bio)
		// 	error_string += fep_messages.bio_long_error + '<br/>';

		// if (substr_count(content, '</a>') > fep_rules.max_links)
		// 	error_string += fep_messages.too_many_article_links_error + '<br/>';

		// if (substr_count(bio, '</a>') > fep_rules.max_links_bio)
		// 	error_string += fep_messages.too_many_bio_links_error + '<br/>';

		// if (tags != '' && count_tags(tags) < fep_rules.min_tags)
		// 	error_string += fep_messages.too_few_tags_error + '<br/>';

		// if (count_tags(tags) > fep_rules.max_tags)
		// 	error_string += fep_messages.too_many_tags_error + '<br/>';

		// if (fep_rules.thumbnail_required && fep_rules.thumbnail_required == 'true' && featured_image == -1)
		// 	error_string += fep_messages.featured_image_error + '<br/>';

		// if (error_string == '')
		// 	return false;
		// else
		// 	return '<strong>' + fep_messages.general_form_error + '</strong><br/>' + error_string;
	}

	// Delete a post
	$("td.post-delete a").click(function (event) {
		var id = $(this).siblings('.post-id').first().val(),
			nonce = $('#fepnonce_delete').val(),
			loading_image = $(this).siblings('.fep-loading-img').first(),
			row = $(this).closest('.fep-row'),
			message_box = $('#fep-message'),
			post_count = $('.count', $('#fep-posts')),
			confirmation = confirm(fep_messages.confirmation_message);

		if (!confirmation)
			return;

		$(this).hide();
		loading_image.show().css({'float': 'none', 'box-shadow': 'none'});
		$.ajax({
			type: 'POST',
			url: fepajaxhandler.ajaxurl,
			data: {
				action: 'fep_delete_posts',
				post_id: id,
				delete_nonce: nonce
			},
			success: function (data) {
				var arr = $.parseJSON(data);
				message_box.html('');
				if (arr.success) {
					row.hide();
					message_box.show().addClass('success').append(arr.message);
					post_count.html(Number(post_count.html()) - 1);
				}
				else {
					message_box.show().addClass('warning').append(arr.message);
				}
				if (message_box.offset().top < $(window).scrollTop()) {
					$('html, body').animate({scrollTop: message_box.offset().top - 10}, 'slow');
				}
			},
			error: function (MLHttpRequest, textStatus, errorThrown) {
				alert(errorThrown);
			}
		});
		event.preventDefault();
	});

	$("#fep-submit-post.active-btn").on('click', function () {
		tinyMCE.triggerSave();

		var title = $("#fep-post-title").val(),
			content = $("#fep-post-content").val(),
			bio = $("#fep-about").val(),
			category = $("#fep-category").val(),
			tags = $("#fep-tags").val(),
			post_id_input = $("#fep-post-id"),
			post_id = post_id_input.val(),
			featured_image = $("#fep-featured-image-id").val(),
			nonce = $("#fepnonce").val(),
			message_box = $('#fep-message'),
			form_container = $('#fep-new-post'),
			submit_btn = $('#fep-submit-post'),
			load_img = $("img.fep-loading-img"),
			submission_form = $('#fep-submission-form');
			// errors = get_post_errors(title, content, bio, category, tags, featured_image);

		// if (errors) {
		// 	if (form_container.offset().top < $(window).scrollTop()) {
		// 		$('html, body').animate({scrollTop: form_container.offset().top - 10}, 'slow');
		// 	}
		// 	message_box.removeClass('success').addClass('warning').html('').show().append(errors);
		// 	return;
		// }

		var data = submission_form.serializeArray();
		var post_data = {};
		$.each(data,
		function(i, v) {
			post_data[v.name] = v.value;
		});

		post_data['action'] = 'fep_process_form_input';
		post_data['post_nonce'] = nonce;
		post_data['post_id'] = post_id;

		// {
		// 	action: 'fep_process_form_input',
		// 	post_title: title,
		// 	post_content: content,
		// 	about_the_author: bio,
		// 	post_category: category,
		// 	post_tags: tags,
		// 	post_id: post_id,
		// 	featured_img: featured_image,
		// 	post_nonce: nonce
		// }

		load_img.show();
		submit_btn.attr("disabled", true).removeClass('active-btn').addClass('passive-btn');
		$.ajaxSetup({cache: false});
		$.ajax({
			type: 'POST',
			url: fepajaxhandler.ajaxurl,
			data: post_data,
			success: function (data) {
				has_changed = false;
				var arr = $.parseJSON(data);
				if (arr.success) {
					submission_form.hide();
					post_id_input.val(arr.post_id);
					message_box.removeClass('alert-warning').addClass('alert-success');
				}
				else{
					message_box.removeClass('alert-success').addClass('alert-warning');
				}

				message_box.html('').append(arr.message).show();
				if (form_container.offset().top < $(window).scrollTop()) {
					$('html, body').animate({scrollTop: form_container.offset().top - 10}, 'slow');
				}
				load_img.hide();
				submit_btn.attr("disabled", false).removeClass('passive-btn').addClass('active-btn');
			},
			error: function (MLHttpRequest, textStatus, errorThrown) {
				alert(errorThrown);
			}
		});
	});

	$('body').on('click', '#fep-continue-editing', function (e) {
		$('#fep-message').hide();
		$('#fep-submission-form').show();
		e.preventDefault();
	});

	$('a#fep-featured-image-link', $('#fep-featured-image')).click(function (e) {
		e.preventDefault();
		custom_uploader = wp.media.frames.file_frame = wp.media({
			title: fep_messages.media_lib_string,
			button: {
				text: fep_messages.media_lib_string
			},
			multiple: false
		});
		custom_uploader.on('select', function () {
			attachment = custom_uploader.state().get('selection').first().toJSON();
			jQuery('input#fep-featured-image-id', $('#fep-featured-image')).val(attachment.id);
			$.ajax({
				type: 'POST',
				url: fepajaxhandler.ajaxurl,
				data: {
					action: 'fep_fetch_featured_image',
					img: attachment.id
				},
				success: function (data) {
					$('#fep-featured-image-container').html(data);
					has_changed = true;
				},
				error: function (MLHttpRequest, textStatus, errorThrown) {
					alert(errorThrown);
				}
			});
		});
		custom_uploader.open();
	});
});