/**
 * Credipost Public JavaScript
 */

(function($) {
	'use strict';

	$(document).ready(function() {
		var lastRemovedBlock = null;
		var lastRemovedIndex = -1;
		var currentSourceUrl = '';

		function showNotice(msg, isSuccess) {
			var $box = $('.credipost-notice-box');
			$box.css({
				'background': isSuccess ? '#e7f7ed' : '#fcf0f0',
				'border-left-color': isSuccess ? '#00a32a' : '#d63638'
			}).find('p').html(msg);
			$box.slideDown(200);

			if (isSuccess) {
				setTimeout(function() { $box.slideUp(300); }, 5000);
			}
		}

		function hideNotice() {
			$('.credipost-notice-box').slideUp(150);
		}

		// Fetch Article
		$('#credipost-pub-btn-fetch').on('click', function(e) {
			e.preventDefault();
			hideNotice();

			var url = $.trim($('#credipost-pub-url').val());
			if (!url) {
				showNotice(credipostPublic.i18n.emptyUrl, false);
				return;
			}

			if (!/^https?:\/\//i.test(url)) {
				url = 'https://' + url;
				$('#credipost-pub-url').val(url);
			}

			currentSourceUrl = url;
			var $btn = $(this);
			$btn.prop('disabled', true);

			$.ajax({
				url: credipostPublic.ajaxUrl,
				type: 'POST',
				dataType: 'json',
				data: {
					action: 'credipost_scrape_url',
					security: credipostPublic.nonce,
					url: url
				}
			})
			.done(function(res) {
				if (res.success && res.data) {
					$('#credipost-pub-title').val(res.data.title || '');
					renderBlocks(res.data.content || '');
					$('#credipost-pub-workspace').slideDown(300);
				} else {
					var msg = (res.data && res.data.message) ? res.data.message : credipostPublic.i18n.genericError;
					showNotice(msg, false);
				}
			})
			.fail(function() {
				showNotice(credipostPublic.i18n.genericError, false);
			})
			.always(function() {
				$btn.prop('disabled', false);
			});
		});

		// Video Embed
		$('#credipost-pub-btn-embed').on('click', function(e) {
			e.preventDefault();
			hideNotice();

			var url = $.trim($('#credipost-pub-url').val());
			if (!url) {
				showNotice(credipostPublic.i18n.emptyUrl, false);
				return;
			}

			if (!/^https?:\/\//i.test(url)) {
				url = 'https://' + url;
				$('#credipost-pub-url').val(url);
			}

			currentSourceUrl = url;
			var $btn = $(this);
			$btn.prop('disabled', true);

			$.ajax({
				url: credipostPublic.ajaxUrl,
				type: 'POST',
				dataType: 'json',
				data: {
					action: 'credipost_get_embed',
					security: credipostPublic.nonce,
					url: url
				}
			})
			.done(function(res) {
				if (res.success && res.data) {
					$('#credipost-pub-title').val('Video Embed');
					renderBlocks(res.data.embed_html || '');
					$('#credipost-pub-workspace').slideDown(300);
				} else {
					var msg = (res.data && res.data.message) ? res.data.message : credipostPublic.i18n.embedNotFound;
					showNotice(msg, false);
				}
			})
			.fail(function() {
				showNotice(credipostPublic.i18n.genericError, false);
			})
			.always(function() {
				$btn.prop('disabled', false);
			});
		});

		function renderBlocks(html) {
			var $c = $('#credipost-pub-rendered-content');
			$c.empty();
			var $temp = $('<div>').html(html);

			$temp.children().each(function() {
				var $b = $('<div class="credipost-block"></div>');
				$b.append('<button type="button" class="credipost-remove-btn">&times;</button>');
				$b.append($(this));
				$c.append($b);
			});

			if ($c.children().length === 0) {
				var $single = $('<div class="credipost-block"></div>');
				$single.append('<button type="button" class="credipost-remove-btn">&times;</button>');
				$single.append($temp.contents());
				$c.append($single);
			}
		}

		$(document).on('click', '.credipost-public-wrap .credipost-remove-btn', function(e) {
			e.preventDefault();
			var $b = $(this).closest('.credipost-block');
			lastRemovedIndex = $b.index();
			lastRemovedBlock = $b.detach();
			$('#credipost-pub-undo-bar').slideDown(150);
		});

		$('#credipost-pub-btn-undo').on('click', function(e) {
			e.preventDefault();
			if (lastRemovedBlock) {
				var $c = $('#credipost-pub-rendered-content');
				var $children = $c.children('.credipost-block');
				if (lastRemovedIndex >= 0 && lastRemovedIndex < $children.length) {
					$children.eq(lastRemovedIndex).before(lastRemovedBlock);
				} else {
					$c.append(lastRemovedBlock);
				}
				lastRemovedBlock = null;
				lastRemovedIndex = -1;
				$('#credipost-pub-undo-bar').slideUp(150);
			}
		});

		// Save Post
		$('#credipost-pub-btn-save').on('click', function(e) {
			e.preventDefault();
			hideNotice();

			var $clone = $('#credipost-pub-rendered-content').clone();
			$clone.find('.credipost-remove-btn').remove();
			$clone.find('.credipost-block').each(function() {
				$(this).replaceWith($(this).contents());
			});

			var clean = $.trim($clone.html());
			if (!clean) {
				showNotice(credipostPublic.i18n.noContent, false);
				return;
			}

			var $btn = $(this);
			$btn.prop('disabled', true);

			$.ajax({
				url: credipostPublic.ajaxUrl,
				type: 'POST',
				dataType: 'json',
				data: {
					action: 'credipost_save_post',
					security: credipostPublic.nonce,
					title: $.trim($('#credipost-pub-title').val()),
					content: clean,
					source_url: currentSourceUrl,
					status: $('#credipost-pub-status').val() || 'draft',
					category_id: $('#credipost-pub-category').val() || 0,
					site_id: $('#credipost-pub-site').length ? ($('#credipost-pub-site').val() || 0) : 0
				}
			})
			.done(function(res) {
				if (res.success && res.data) {
					showNotice(res.data.message, true);
				} else {
					var msg = (res.data && res.data.message) ? res.data.message : credipostPublic.i18n.genericError;
					showNotice(msg, false);
				}
			})
			.fail(function() {
				showNotice(credipostPublic.i18n.genericError, false);
			})
			.always(function() {
				$btn.prop('disabled', false);
			});
		});

		$('#credipost-pub-btn-clear').on('click', function(e) {
			e.preventDefault();
			$('#credipost-pub-url').val('');
			$('#credipost-pub-title').val('');
			$('#credipost-pub-rendered-content').empty();
			$('#credipost-pub-workspace').slideUp(200);
			$('#credipost-pub-undo-bar').hide();
			hideNotice();
			currentSourceUrl = '';
			lastRemovedBlock = null;
		});

	});

})(jQuery);
