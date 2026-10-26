/**
 * Credipost Admin JavaScript
 *
 * Handles AJAX content scraping, oEmbed retrieval, visual block pruning,
 * undo history, and secure post publishing.
 */

(function($) {
	'use strict';

	$(document).ready(function() {
		var lastRemovedBlock = null;
		var lastRemovedIndex = -1;
		var currentSourceUrl = '';

		// Utility: Show Admin Notice
		function showNotice(message, type) {
			type = type || 'error';
			var $notice = $('#credipost-notice');
			$notice
				.removeClass('notice-error notice-success notice-warning notice-info')
				.addClass('notice-' + type)
				.find('p')
				.html(message);
			$notice.slideDown(200);

			if (type === 'success') {
				setTimeout(function() {
					$notice.slideUp(300);
				}, 6000);
			}
		}

		function hideNotice() {
			$('#credipost-notice').slideUp(150);
		}

		// Trigger fetch on Enter key inside URL input
		$('#credipost-url-input').on('keypress', function(e) {
			if (e.which === 13) {
				e.preventDefault();
				$('#credipost-btn-fetch').click();
			}
		});

		// 1. Fetch Article Content
		$('#credipost-btn-fetch').on('click', function(e) {
			e.preventDefault();
			hideNotice();

			var url = $.trim($('#credipost-url-input').val());
			if (!url) {
				showNotice(credipostAdmin.i18n.emptyUrl, 'error');
				return;
			}

			if (!/^https?:\/\//i.test(url)) {
				url = 'https://' + url;
				$('#credipost-url-input').val(url);
			}

			currentSourceUrl = url;
			var $btn = $(this);
			var $spinner = $('#credipost-spinner');

			$btn.prop('disabled', true);
			$spinner.addClass('is-active');

			$.ajax({
				url: credipostAdmin.ajaxUrl,
				type: 'POST',
				dataType: 'json',
				data: {
					action: 'credipost_scrape_url',
					security: credipostAdmin.nonce,
					url: url
				}
			})
			.done(function(response) {
				if (response.success && response.data) {
					var data = response.data;
					$('#credipost-post-title').val(data.title || '');
					$('#credipost-source-link')
						.attr('href', data.source_url || url)
						.text(data.source_url || url);

					renderContent(data.content || '');
					$('#credipost-workspace').slideDown(300);
				} else {
					var msg = (response.data && response.data.message) ? response.data.message : credipostAdmin.i18n.genericError;
					showNotice(msg, 'error');
				}
			})
			.fail(function(jqXHR, textStatus, errorThrown) {
				showNotice(credipostAdmin.i18n.genericError + ' (' + textStatus + ')', 'error');
			})
			.always(function() {
				$btn.prop('disabled', false);
				$spinner.removeClass('is-active');
			});
		});

		// 2. Fetch Video Embed via Core oEmbed
		$('#credipost-btn-embed').on('click', function(e) {
			e.preventDefault();
			hideNotice();

			var url = $.trim($('#credipost-url-input').val());
			if (!url) {
				showNotice(credipostAdmin.i18n.emptyUrl, 'error');
				return;
			}

			if (!/^https?:\/\//i.test(url)) {
				url = 'https://' + url;
				$('#credipost-url-input').val(url);
			}

			currentSourceUrl = url;
			var $btn = $(this);
			var $spinner = $('#credipost-spinner');

			$btn.prop('disabled', true);
			$spinner.addClass('is-active');

			$.ajax({
				url: credipostAdmin.ajaxUrl,
				type: 'POST',
				dataType: 'json',
				data: {
					action: 'credipost_get_embed',
					security: credipostAdmin.nonce,
					url: url
				}
			})
			.done(function(response) {
				if (response.success && response.data) {
					var data = response.data;
					var titleGuess = 'Video from ' + (new URL(url).hostname);
					$('#credipost-post-title').val(titleGuess);
					$('#credipost-source-link')
						.attr('href', data.source_url || url)
						.text(data.source_url || url);

					renderContent(data.embed_html || '');
					$('#credipost-workspace').slideDown(300);
				} else {
					var msg = (response.data && response.data.message) ? response.data.message : credipostAdmin.i18n.embedNotFound;
					showNotice(msg, 'error');
				}
			})
			.fail(function(jqXHR, textStatus) {
				showNotice(credipostAdmin.i18n.genericError, 'error');
			})
			.always(function() {
				$btn.prop('disabled', false);
				$spinner.removeClass('is-active');
			});
		});

		// 3. Render content blocks with interactive pruning handles
		function renderContent(htmlContent) {
			var $container = $('#credipost-rendered-content');
			$container.empty();

			if (!htmlContent) {
				$container.html('<p><em>' + credipostAdmin.i18n.noContent + '</em></p>');
				return;
			}

			// Wrap HTML in a temporary element
			var $temp = $('<div>').html(htmlContent);

			// Wrap each top-level element in a prunable block container
			$temp.children().each(function() {
				var $child = $(this);
				var $block = $('<div class="credipost-block"></div>');
				var $removeBtn = $('<button type="button" class="credipost-remove-btn" title="' + credipostAdmin.i18n.removeBlock + '">&times;</button>');

				$block.append($removeBtn);
				$block.append($child);
				$container.append($block);
			});

			// If no top-level blocks were found (plain text), wrap everything
			if ($container.children().length === 0) {
				var $singleBlock = $('<div class="credipost-block"></div>');
				$singleBlock.append('<button type="button" class="credipost-remove-btn" title="' + credipostAdmin.i18n.removeBlock + '">&times;</button>');
				$singleBlock.append($temp.contents());
				$container.append($singleBlock);
			}
		}

		// 4. Handle Block Removal (Pruning)
		$(document).on('click', '.credipost-remove-btn', function(e) {
			e.preventDefault();
			var $block = $(this).closest('.credipost-block');
			lastRemovedIndex = $block.index();
			lastRemovedBlock = $block.detach();

			$('#credipost-undo-bar').slideDown(150);
		});

		// 5. Handle Undo
		$('#credipost-btn-undo').on('click', function(e) {
			e.preventDefault();
			if (lastRemovedBlock) {
				var $container = $('#credipost-rendered-content');
				var $children = $container.children('.credipost-block');

				if (lastRemovedIndex >= 0 && lastRemovedIndex < $children.length) {
					$children.eq(lastRemovedIndex).before(lastRemovedBlock);
				} else {
					$container.append(lastRemovedBlock);
				}

				lastRemovedBlock = null;
				lastRemovedIndex = -1;
				$('#credipost-undo-bar').slideUp(150);
			}
		});

		// 6. Save as Post
		$('#credipost-btn-save').on('click', function(e) {
			e.preventDefault();
			hideNotice();

			var $container = $('#credipost-rendered-content');
			// Clone container and clean remove buttons to extract pure post HTML
			var $clone = $container.clone();
			$clone.find('.credipost-remove-btn').remove();

			// Unwrap blocks so only native semantic tags remain
			$clone.find('.credipost-block').each(function() {
				$(this).replaceWith($(this).contents());
			});

			var cleanHtml = $.trim($clone.html());
			if (!cleanHtml) {
				showNotice(credipostAdmin.i18n.noContent, 'warning');
				return;
			}

			var title = $.trim($('#credipost-post-title').val());
			var status = $('#credipost-post-status').val() || 'draft';
			var categoryId = $('#credipost-category-select').val() || 0;
			var siteId = $('#credipost-site-select').length ? ($('#credipost-site-select').val() || 0) : 0;

			var $btn = $(this);
			var $spinner = $('#credipost-save-spinner');

			$btn.prop('disabled', true);
			$spinner.addClass('is-active');

			$.ajax({
				url: credipostAdmin.ajaxUrl,
				type: 'POST',
				dataType: 'json',
				data: {
					action: 'credipost_save_post',
					security: credipostAdmin.nonce,
					title: title,
					content: cleanHtml,
					source_url: currentSourceUrl,
					status: status,
					category_id: categoryId,
					site_id: siteId
				}
			})
			.done(function(response) {
				if (response.success && response.data) {
					var editLink = response.data.edit_link
						? ' <a href="' + response.data.edit_link + '" class="button button-small" style="margin-left: 8px;">' + credipostAdmin.i18n.viewEditPost + '</a>'
						: '';
					showNotice(response.data.message + editLink, 'success');
				} else {
					var msg = (response.data && response.data.message) ? response.data.message : credipostAdmin.i18n.genericError;
					showNotice(msg, 'error');
				}
			})
			.fail(function() {
				showNotice(credipostAdmin.i18n.genericError, 'error');
			})
			.always(function() {
				$btn.prop('disabled', false);
				$spinner.removeClass('is-active');
			});
		});

		// 7. Print Article
		$('#credipost-btn-print').on('click', function(e) {
			e.preventDefault();
			window.print();
		});

		// 8. Clear & Reset
		$('#credipost-btn-clear').on('click', function(e) {
			e.preventDefault();
			if (confirm(credipostAdmin.i18n.confirmClear)) {
				$('#credipost-url-input').val('');
				$('#credipost-post-title').val('');
				$('#credipost-rendered-content').empty();
				$('#credipost-workspace').slideUp(200);
				$('#credipost-undo-bar').hide();
				hideNotice();
				currentSourceUrl = '';
				lastRemovedBlock = null;
			}
		});

	});

})(jQuery);
