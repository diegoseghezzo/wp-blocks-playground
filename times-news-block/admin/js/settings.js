/**
 * Times News Block - Admin Settings JavaScript
 * VIP COMPLIANCE: External JS file for Content Security Policy
 */

(function($) {
	'use strict';

	$(document).ready(function() {
		let feedIndex = parseInt(timesNewsSettings.feedCount, 10);

		// Add new feed
		$('.times-news-add-feed').on('click', function() {
			const newFeed = `
				<div class="times-news-feed-row" data-index="${feedIndex}">
					<div class="times-news-feed-controls">
						<div>
							<label>${timesNewsSettings.i18n.idLabel}</label>
							<input type="text"
							       name="times_news_block_rss_feeds[${feedIndex}][id]"
							       class="regular-text"
							       placeholder="${timesNewsSettings.i18n.idPlaceholder}"
							       required />
						</div>
						<div>
							<label>${timesNewsSettings.i18n.labelLabel}</label>
							<input type="text"
							       name="times_news_block_rss_feeds[${feedIndex}][label]"
							       class="regular-text"
							       placeholder="${timesNewsSettings.i18n.labelPlaceholder}"
							       required />
						</div>
						<div>
							<label>${timesNewsSettings.i18n.urlLabel}</label>
							<input type="url"
							       name="times_news_block_rss_feeds[${feedIndex}][url]"
							       class="large-text"
							       placeholder="https://example.com/feed.xml"
							       required />
						</div>
						<div>
							<label>${timesNewsSettings.i18n.enabledLabel}</label>
							<label>
								<input type="checkbox"
								       name="times_news_block_rss_feeds[${feedIndex}][enabled]"
								       value="1"
								       checked />
								${timesNewsSettings.i18n.active}
							</label>
						</div>
						<div>
							<label>&nbsp;</label>
							<a href="#" class="times-news-remove-feed" data-index="${feedIndex}">
								${timesNewsSettings.i18n.remove}
							</a>
						</div>
					</div>
				</div>
			`;
			$('#times-news-feeds-container').append(newFeed);
			feedIndex++;
		});

		// Remove feed
		$(document).on('click', '.times-news-remove-feed', function(e) {
			e.preventDefault();
			if (confirm(timesNewsSettings.i18n.confirmRemove)) {
				$(this).closest('.times-news-feed-row').remove();
			}
		});
	});

})(jQuery);
