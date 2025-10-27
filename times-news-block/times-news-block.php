<?php
/**
 * Plugin Name:       Times News Block
 * Description:       Display The Times news with AI-powered filtering and customizable criteria
 * Requires at least: 6.1
 * Requires PHP:      7.4
 * Version:           1.0.0
 * Author:            Diego Seghezzo
 * License:           GPL-2.0-or-later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       times-news-block
 *
 * @package           times-news-block
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

// Define plugin constants
define( 'TIMES_NEWS_BLOCK_VERSION', '1.0.0' );
define( 'TIMES_NEWS_BLOCK_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
define( 'TIMES_NEWS_BLOCK_PLUGIN_URL', plugin_dir_url( __FILE__ ) );

/**
 * Registers the block using the metadata loaded from the `block.json` file.
 * Behind the scenes, it registers also all assets so they can be enqueued
 * through the block editor in the corresponding context.
 *
 * @see https://developer.wordpress.org/reference/functions/register_block_type/
 */
function times_news_block_init() {
	register_block_type( __DIR__, array(
		'render_callback' => 'times_news_block_render_callback',
	) );
}
add_action( 'init', 'times_news_block_init' );

/**
 * Render callback for the Times News Block
 *
 * @param array    $attributes Block attributes.
 * @param string   $content    Block default content.
 * @param WP_Block $block      Block instance.
 * @return string Rendered block HTML.
 */
function times_news_block_render_callback( $attributes, $content, $block ) {
	// Get block attributes with defaults
	$news_count = isset( $attributes['newsCount'] ) ? absint( $attributes['newsCount'] ) : 5;
	$use_ai = isset( $attributes['useAI'] ) ? (bool) $attributes['useAI'] : false;
	$user_criteria = isset( $attributes['userCriteria'] ) ? sanitize_text_field( $attributes['userCriteria'] ) : '';
	$category = isset( $attributes['category'] ) ? sanitize_text_field( $attributes['category'] ) : 'all';
	$layout = isset( $attributes['layout'] ) ? sanitize_text_field( $attributes['layout'] ) : 'grid';

	// Fetch news articles
	$news_articles = times_news_block_fetch_news( $news_count, $category, $use_ai, $user_criteria );

	// If no articles, return message
	if ( empty( $news_articles ) ) {
		return '<div class="wp-block-times-news-block"><p>' . esc_html__( 'No news articles found.', 'times-news-block' ) . '</p></div>';
	}

	// Build HTML output
	ob_start();
	?>
	<div class="wp-block-times-news-block times-news-layout-<?php echo esc_attr( $layout ); ?>">
		<div class="times-news-container times-news-<?php echo esc_attr( $layout ); ?>">
			<?php foreach ( $news_articles as $article ) : ?>
				<article class="times-news-item">
					<?php if ( ! empty( $article['image'] ) ) : ?>
						<div class="times-news-image">
							<img src="<?php echo esc_url( $article['image'] ); ?>"
							     alt="<?php echo esc_attr( $article['title'] ); ?>"
							     loading="lazy" />
						</div>
					<?php endif; ?>
					<div class="times-news-content">
						<h3 class="times-news-title">
							<a href="<?php echo esc_url( $article['link'] ); ?>"
							   target="_blank"
							   rel="noopener noreferrer">
								<?php echo esc_html( $article['title'] ); ?>
							</a>
						</h3>
						<?php if ( ! empty( $article['description'] ) ) : ?>
							<p class="times-news-description">
								<?php echo esc_html( $article['description'] ); ?>
							</p>
						<?php endif; ?>
						<?php if ( ! empty( $article['pubDate'] ) ) : ?>
							<time class="times-news-date" datetime="<?php echo esc_attr( $article['pubDate'] ); ?>">
								<?php echo esc_html( human_time_diff( strtotime( $article['pubDate'] ), current_time( 'timestamp' ) ) ); ?> ago
							</time>
						<?php endif; ?>
					</div>
				</article>
			<?php endforeach; ?>
		</div>
	</div>
	<?php
	return ob_get_clean();
}

/**
 * Fetch news articles from The Times RSS feed
 *
 * @param int    $count         Number of articles to fetch.
 * @param string $category      News category.
 * @param bool   $use_ai        Whether to use AI filtering.
 * @param string $user_criteria User-defined criteria for AI filtering.
 * @return array Array of news articles.
 */
function times_news_block_fetch_news( $count = 5, $category = 'all', $use_ai = false, $user_criteria = '' ) {
	// VIP OPTIMIZATION: Multi-layer caching strategy
	// Layer 1: Object cache (Memcached/Redis on VIP)
	// Layer 2: Transients (database fallback)
	$cache_key = 'times_news_' . md5( $category . $count . $use_ai . $user_criteria );
	$cache_group = 'times_news_block';

	// Try object cache first (VIP uses Memcached)
	$cached_news = wp_cache_get( $cache_key, $cache_group );
	if ( false !== $cached_news ) {
		return $cached_news;
	}

	// Fallback to transients
	$cached_news = get_transient( $cache_key );
	if ( false !== $cached_news ) {
		// Populate object cache for next request
		wp_cache_set( $cache_key, $cached_news, $cache_group, 15 * MINUTE_IN_SECONDS );
		return $cached_news;
	}

	// Get configured RSS feeds from settings
	$configured_feeds = get_option( 'times_news_block_rss_feeds', times_news_block_get_default_feeds() );

	// Build feed URLs array from configured feeds (only enabled ones)
	$feed_urls = array();
	foreach ( $configured_feeds as $feed ) {
		if ( ! empty( $feed['enabled'] ) && ! empty( $feed['id'] ) && ! empty( $feed['url'] ) ) {
			$feed_urls[ $feed['id'] ] = $feed['url'];
		}
	}

	// Fallback to default if no feeds configured
	if ( empty( $feed_urls ) ) {
		$default_feeds = times_news_block_get_default_feeds();
		foreach ( $default_feeds as $feed ) {
			$feed_urls[ $feed['id'] ] = $feed['url'];
		}
	}

	// Get the feed URL for requested category, fallback to first available
	$feed_url = isset( $feed_urls[ $category ] ) ? $feed_urls[ $category ] : reset( $feed_urls );

	// VIP OPTIMIZATION: Performance tracking for slow operations
	$start_time = microtime( true );

	// Fetch RSS feed
	$rss = fetch_feed( $feed_url );

	// Log slow RSS fetches (VIP monitors these)
	$fetch_duration = microtime( true ) - $start_time;
	if ( $fetch_duration > 2.0 ) {
		error_log( sprintf(
			'Times News Block: Slow RSS fetch detected (%.2fs) for category: %s, URL: %s',
			$fetch_duration,
			$category,
			$feed_url
		) );
	}

	if ( is_wp_error( $rss ) ) {
		error_log( 'Times News Block RSS Error: ' . $rss->get_error_message() );
		// Return demo data for development/testing
		return times_news_block_get_demo_data( $count, $category );
	}

	// Get items from feed
	$maxitems = $use_ai ? $count * 3 : $count; // Fetch more if using AI filtering
	$rss_items = $rss->get_items( 0, $maxitems );

	$news_articles = array();

	foreach ( $rss_items as $item ) {
		// Extract image from multiple possible sources
		$image_url = '';

		// Method 1: Try enclosure (most common for podcasts and media)
		if ( $enclosure = $item->get_enclosure() ) {
			if ( $enclosure->get_thumbnail() ) {
				$image_url = $enclosure->get_thumbnail();
			} elseif ( $enclosure->get_link() ) {
				$image_url = $enclosure->get_link();
			}
		}

		// Method 2: Try media:thumbnail (common in news feeds)
		if ( empty( $image_url ) ) {
			$thumbnail = $item->get_item_tags( 'http://search.yahoo.com/mrss/', 'thumbnail' );
			if ( ! empty( $thumbnail[0]['attribs']['']['url'] ) ) {
				$image_url = $thumbnail[0]['attribs']['']['url'];
			}
		}

		// Method 3: Try media:content
		if ( empty( $image_url ) ) {
			$media_content = $item->get_item_tags( 'http://search.yahoo.com/mrss/', 'content' );
			if ( ! empty( $media_content[0]['attribs']['']['url'] ) ) {
				$media_type = $media_content[0]['attribs']['']['type'] ?? '';
				if ( strpos( $media_type, 'image' ) !== false ) {
					$image_url = $media_content[0]['attribs']['']['url'];
				}
			}
		}

		// Method 4: Extract from content/description
		if ( empty( $image_url ) ) {
			$content = $item->get_content();
			if ( empty( $content ) ) {
				$content = $item->get_description();
			}

			// Try multiple image patterns
			$patterns = array(
				'/<img[^>]+src=["\']([^"\']+)["\'][^>]*>/i',
				'/<img[^>]+src=([^\s>]+)/i',
			);

			foreach ( $patterns as $pattern ) {
				if ( preg_match( $pattern, $content, $matches ) ) {
					$image_url = trim( $matches[1], '\'"' );
					break;
				}
			}
		}

		// Method 5: Look for og:image in description
		if ( empty( $image_url ) ) {
			$description = $item->get_description();
			if ( preg_match( '/og:image["\']?\s+content=["\']([^"\']+)["\']/', $description, $matches ) ) {
				$image_url = $matches[1];
			}
		}

		$article = array(
			'title'       => html_entity_decode( $item->get_title(), ENT_QUOTES | ENT_HTML5, 'UTF-8' ),
			'link'        => $item->get_permalink(),
			'description' => html_entity_decode( wp_trim_words( strip_tags( $item->get_description() ), 30 ), ENT_QUOTES | ENT_HTML5, 'UTF-8' ),
			'pubDate'     => $item->get_date( 'Y-m-d H:i:s' ),
			'image'       => $image_url,
		);

		$news_articles[] = $article;
	}

	// Apply AI filtering if enabled
	if ( $use_ai && ! empty( $user_criteria ) && ! empty( $news_articles ) ) {
		$news_articles = times_news_block_filter_with_ai( $news_articles, $user_criteria, $count );
	} else {
		$news_articles = array_slice( $news_articles, 0, $count );
	}

	// VIP OPTIMIZATION: Cache in both layers for optimal performance
	// Object cache (fast, in-memory)
	wp_cache_set( $cache_key, $news_articles, $cache_group, 15 * MINUTE_IN_SECONDS );
	// Transient cache (persistent, database backup)
	set_transient( $cache_key, $news_articles, 15 * MINUTE_IN_SECONDS );

	return $news_articles;
}

/**
 * Filter news articles using OpenAI
 *
 * @param array  $articles      Array of news articles.
 * @param string $user_criteria User-defined filtering criteria.
 * @param int    $count         Number of articles to return.
 * @return array Filtered and ranked articles.
 */
function times_news_block_filter_with_ai( $articles, $user_criteria, $count ) {
	// VIP OPTIMIZATION: Rate limiting for external API calls
	// Limit: 100 AI calls per hour per user (prevents abuse and controls costs)
	$user_id = get_current_user_id();
	$rate_limit_key = 'times_news_ai_calls_' . ( $user_id ? $user_id : 'guest_' . times_news_block_get_visitor_ip_hash() );
	$calls_count = (int) get_transient( $rate_limit_key );

	if ( $calls_count >= 100 ) {
		error_log( sprintf( 'Times News Block: AI rate limit exceeded for user %s', $user_id ? $user_id : 'guest' ) );
		// Return non-AI filtered results as graceful degradation
		return array_slice( $articles, 0, $count );
	}

	// Get OpenAI API key from settings (or environment variable - VIP best practice)
	$api_key = times_news_block_get_api_key();

	if ( empty( $api_key ) ) {
		error_log( 'Times News Block: OpenAI API key not configured' );
		return array_slice( $articles, 0, $count );
	}

	// Prepare articles for AI analysis
	$articles_text = '';
	foreach ( $articles as $index => $article ) {
		$articles_text .= sprintf(
			"[%d] Title: %s\nDescription: %s\n\n",
			$index,
			$article['title'],
			$article['description']
		);
	}

	// Call OpenAI API
	$prompt = sprintf(
		"Based on the user criteria: '%s'\n\nRank the following news articles by relevance (0-10 scale). Return ONLY a JSON array of objects with 'index' and 'score' properties, ordered by score (highest first). Limit to top %d articles.\n\nArticles:\n%s",
		$user_criteria,
		$count,
		$articles_text
	);

	// VIP OPTIMIZATION: Track AI API call performance
	$ai_start_time = microtime( true );

	$response = wp_remote_post(
		'https://api.openai.com/v1/chat/completions',
		array(
			'headers' => array(
				'Authorization' => 'Bearer ' . $api_key,
				'Content-Type'  => 'application/json',
			),
			'body'    => json_encode( array(
				'model'       => 'gpt-3.5-turbo',
				'messages'    => array(
					array(
						'role'    => 'user',
						'content' => $prompt,
					),
				),
				'temperature' => 0.3,
			) ),
			'timeout' => 30,
		)
	);

	if ( is_wp_error( $response ) ) {
		error_log( 'Times News Block OpenAI Error: ' . $response->get_error_message() );
		return array_slice( $articles, 0, $count );
	}

	$body = json_decode( wp_remote_retrieve_body( $response ), true );

	if ( empty( $body['choices'][0]['message']['content'] ) ) {
		error_log( 'Times News Block: Invalid OpenAI response' );
		return array_slice( $articles, 0, $count );
	}

	// VIP OPTIMIZATION: Track and log AI performance
	$ai_duration = microtime( true ) - $ai_start_time;
	if ( $ai_duration > 5.0 ) {
		error_log( sprintf(
			'Times News Block: Slow AI filtering detected (%.2fs) for criteria: %s',
			$ai_duration,
			substr( $user_criteria, 0, 50 )
		) );
	}

	// VIP OPTIMIZATION: Increment rate limit counter after successful API call
	set_transient( $rate_limit_key, $calls_count + 1, HOUR_IN_SECONDS );

	// Parse AI response
	$ai_content = $body['choices'][0]['message']['content'];

	// Try to extract JSON from the response (in case there's extra text)
	preg_match( '/\[.*\]/s', $ai_content, $json_matches );
	if ( empty( $json_matches[0] ) ) {
		error_log( 'Times News Block: Could not parse AI response' );
		return array_slice( $articles, 0, $count );
	}

	$rankings = json_decode( $json_matches[0], true );

	if ( ! is_array( $rankings ) ) {
		error_log( 'Times News Block: Invalid AI rankings format' );
		return array_slice( $articles, 0, $count );
	}

	// Filter and sort articles based on AI rankings
	$filtered_articles = array();
	foreach ( $rankings as $ranking ) {
		if ( isset( $ranking['index'] ) && isset( $articles[ $ranking['index'] ] ) ) {
			$filtered_articles[] = $articles[ $ranking['index'] ];
		}
	}

	return array_slice( $filtered_articles, 0, $count );
}

/**
 * Register REST API endpoint for fetching news
 */
function times_news_block_register_rest_routes() {
	register_rest_route(
		'times-news-block/v1',
		'/news',
		array(
			'methods'             => 'GET',
			'callback'            => 'times_news_block_rest_get_news',
			'permission_callback' => '__return_true',
			'args'                => array(
				'count'    => array(
					'default'  => 5,
					'sanitize_callback' => 'absint',
				),
				'category' => array(
					'default'  => 'all',
					'sanitize_callback' => 'sanitize_text_field',
				),
				'useAI'    => array(
					'default'  => false,
					'sanitize_callback' => 'rest_sanitize_boolean',
				),
				'criteria' => array(
					'default'  => '',
					'sanitize_callback' => 'sanitize_text_field',
				),
			),
		)
	);

	// Endpoint to get available feed categories
	register_rest_route(
		'times-news-block/v1',
		'/feeds',
		array(
			'methods'             => 'GET',
			'callback'            => 'times_news_block_rest_get_feeds',
			'permission_callback' => '__return_true',
		)
	);
}
add_action( 'rest_api_init', 'times_news_block_register_rest_routes' );

/**
 * REST API callback for fetching news
 *
 * @param WP_REST_Request $request Request object.
 * @return WP_REST_Response Response object.
 */
function times_news_block_rest_get_news( $request ) {
	$count = $request->get_param( 'count' );
	$category = $request->get_param( 'category' );
	$use_ai = $request->get_param( 'useAI' );
	$criteria = $request->get_param( 'criteria' );

	$news = times_news_block_fetch_news( $count, $category, $use_ai, $criteria );

	return new WP_REST_Response( $news, 200 );
}

/**
 * REST API callback for getting available feeds
 *
 * @return WP_REST_Response Response object.
 */
function times_news_block_rest_get_feeds() {
	$configured_feeds = get_option( 'times_news_block_rss_feeds', times_news_block_get_default_feeds() );

	// Format for block editor dropdown
	$feeds = array();
	foreach ( $configured_feeds as $feed ) {
		if ( ! empty( $feed['enabled'] ) && ! empty( $feed['id'] ) ) {
			$feeds[] = array(
				'value' => $feed['id'],
				'label' => $feed['label'] ?? $feed['id'],
			);
		}
	}

	return new WP_REST_Response( $feeds, 200 );
}

/**
 * VIP OPTIMIZATION: Get API key from environment variable (VIP best practice)
 * Fallback to database if not available
 *
 * @return string API key.
 */
function times_news_block_get_api_key() {
	// Priority 1: Environment variable (VIP recommended)
	if ( defined( 'TIMES_NEWS_OPENAI_KEY' ) ) {
		return TIMES_NEWS_OPENAI_KEY;
	}

	// Priority 2: VIP private files directory
	if ( defined( 'WPCOM_VIP_PRIVATE_DIR' ) ) {
		$key_file = WPCOM_VIP_PRIVATE_DIR . '/times-news-openai-key.txt';
		if ( file_exists( $key_file ) ) {
			return trim( file_get_contents( $key_file ) );
		}
	}

	// Priority 3: Database (less secure, but works for non-VIP)
	return get_option( 'times_news_block_openai_key', '' );
}

/**
 * VIP OPTIMIZATION: Get visitor IP hash for rate limiting guests
 *
 * @return string Hashed IP address.
 */
function times_news_block_get_visitor_ip_hash() {
	$ip = '';

	// Try various methods to get real IP
	if ( ! empty( $_SERVER['HTTP_X_FORWARDED_FOR'] ) ) {
		$ip = sanitize_text_field( wp_unslash( $_SERVER['HTTP_X_FORWARDED_FOR'] ) );
	} elseif ( ! empty( $_SERVER['HTTP_X_REAL_IP'] ) ) {
		$ip = sanitize_text_field( wp_unslash( $_SERVER['HTTP_X_REAL_IP'] ) );
	} elseif ( ! empty( $_SERVER['REMOTE_ADDR'] ) ) {
		$ip = sanitize_text_field( wp_unslash( $_SERVER['REMOTE_ADDR'] ) );
	}

	// Return hashed IP for privacy
	return md5( $ip . wp_salt() );
}

/**
 * VIP OPTIMIZATION: Enqueue admin assets (CSP-compliant)
 */
function times_news_block_enqueue_admin_assets( $hook ) {
	// Only load on our settings page
	if ( 'settings_page_times-news-block' !== $hook ) {
		return;
	}

	// Enqueue CSS (VIP requirement: no inline styles)
	wp_enqueue_style(
		'times-news-block-admin',
		TIMES_NEWS_BLOCK_PLUGIN_URL . 'admin/css/settings.css',
		array(),
		TIMES_NEWS_BLOCK_VERSION
	);

	// Enqueue JavaScript (VIP requirement: no inline scripts)
	wp_enqueue_script(
		'times-news-block-admin',
		TIMES_NEWS_BLOCK_PLUGIN_URL . 'admin/js/settings.js',
		array( 'jquery' ),
		TIMES_NEWS_BLOCK_VERSION,
		true
	);

	// Localize script for translations and data
	wp_localize_script(
		'times-news-block-admin',
		'timesNewsSettings',
		array(
			'feedCount' => count( get_option( 'times_news_block_rss_feeds', times_news_block_get_default_feeds() ) ),
			'i18n'      => array(
				'idLabel'        => __( 'ID (slug)', 'times-news-block' ),
				'idPlaceholder'  => __( 'e.g., tech', 'times-news-block' ),
				'labelLabel'     => __( 'Label', 'times-news-block' ),
				'labelPlaceholder' => __( 'e.g., Technology', 'times-news-block' ),
				'urlLabel'       => __( 'RSS Feed URL', 'times-news-block' ),
				'enabledLabel'   => __( 'Enabled', 'times-news-block' ),
				'active'         => __( 'Active', 'times-news-block' ),
				'remove'         => __( 'Remove', 'times-news-block' ),
				'confirmRemove'  => __( 'Are you sure you want to remove this feed?', 'times-news-block' ),
			),
		)
	);
}
add_action( 'admin_enqueue_scripts', 'times_news_block_enqueue_admin_assets' );

/**
 * Add settings page for API configuration
 */
function times_news_block_add_admin_menu() {
	add_options_page(
		__( 'Times News Block Settings', 'times-news-block' ),
		__( 'Times News Block', 'times-news-block' ),
		'manage_options',
		'times-news-block',
		'times_news_block_settings_page'
	);
}
add_action( 'admin_menu', 'times_news_block_add_admin_menu' );

/**
 * Register settings
 */
function times_news_block_settings_init() {
	register_setting( 'times_news_block', 'times_news_block_openai_key' );
	register_setting( 'times_news_block', 'times_news_block_rss_feeds', array(
		'type'              => 'array',
		'sanitize_callback' => 'times_news_block_sanitize_feeds',
		'default'           => times_news_block_get_default_feeds(),
	) );

	// API Configuration Section
	add_settings_section(
		'times_news_block_section',
		__( 'API Configuration', 'times-news-block' ),
		'times_news_block_settings_section_callback',
		'times_news_block'
	);

	add_settings_field(
		'times_news_block_openai_key',
		__( 'OpenAI API Key', 'times-news-block' ),
		'times_news_block_openai_key_render',
		'times_news_block',
		'times_news_block_section'
	);

	// RSS Feeds Section
	add_settings_section(
		'times_news_block_feeds_section',
		__( 'RSS Feed Sources', 'times-news-block' ),
		'times_news_block_feeds_section_callback',
		'times_news_block'
	);

	add_settings_field(
		'times_news_block_rss_feeds',
		__( 'Configure Feeds', 'times-news-block' ),
		'times_news_block_rss_feeds_render',
		'times_news_block',
		'times_news_block_feeds_section'
	);
}
add_action( 'admin_init', 'times_news_block_settings_init' );

/**
 * Get default RSS feeds
 */
function times_news_block_get_default_feeds() {
	return array(
		array(
			'id'       => 'all',
			'label'    => 'All News',
			'url'      => 'http://feeds.bbci.co.uk/news/rss.xml',
			'enabled'  => true,
		),
		array(
			'id'       => 'world',
			'label'    => 'World',
			'url'      => 'http://feeds.bbci.co.uk/news/world/rss.xml',
			'enabled'  => true,
		),
		array(
			'id'       => 'business',
			'label'    => 'Business',
			'url'      => 'http://feeds.bbci.co.uk/news/business/rss.xml',
			'enabled'  => true,
		),
		array(
			'id'       => 'sport',
			'label'    => 'Sport',
			'url'      => 'http://feeds.bbci.co.uk/news/sport/rss.xml',
			'enabled'  => true,
		),
		array(
			'id'       => 'culture',
			'label'    => 'Culture',
			'url'      => 'http://feeds.bbci.co.uk/news/entertainment_and_arts/rss.xml',
			'enabled'  => true,
		),
	);
}

/**
 * Sanitize RSS feeds array
 */
function times_news_block_sanitize_feeds( $feeds ) {
	if ( ! is_array( $feeds ) ) {
		return times_news_block_get_default_feeds();
	}

	$sanitized = array();
	foreach ( $feeds as $feed ) {
		if ( ! empty( $feed['url'] ) ) {
			$sanitized[] = array(
				'id'      => sanitize_key( $feed['id'] ?? '' ),
				'label'   => sanitize_text_field( $feed['label'] ?? '' ),
				'url'     => esc_url_raw( $feed['url'] ),
				'enabled' => ! empty( $feed['enabled'] ),
			);
		}
	}

	return ! empty( $sanitized ) ? $sanitized : times_news_block_get_default_feeds();
}

/**
 * Settings section callback
 */
function times_news_block_settings_section_callback() {
	echo '<p>' . esc_html__( 'Configure API keys for enhanced functionality.', 'times-news-block' ) . '</p>';
}

/**
 * Feeds section callback
 */
function times_news_block_feeds_section_callback() {
	echo '<p>' . esc_html__( 'Manage RSS feed sources for different news categories. Add, edit, or remove feeds as needed.', 'times-news-block' ) . '</p>';
}

/**
 * RSS feeds field render
 */
function times_news_block_rss_feeds_render() {
	$feeds = get_option( 'times_news_block_rss_feeds', times_news_block_get_default_feeds() );
	?>
	<div id="times-news-feeds-manager">
		<!-- VIP COMPLIANCE: Styles moved to admin/css/settings.css -->
		<div id="times-news-feeds-container">
			<?php foreach ( $feeds as $index => $feed ) : ?>
				<div class="times-news-feed-row" data-index="<?php echo esc_attr( $index ); ?>">
					<div class="times-news-feed-controls">
						<div>
							<label><?php esc_html_e( 'ID (slug)', 'times-news-block' ); ?></label>
							<input type="text"
							       name="times_news_block_rss_feeds[<?php echo esc_attr( $index ); ?>][id]"
							       value="<?php echo esc_attr( $feed['id'] ?? '' ); ?>"
							       class="regular-text"
							       placeholder="e.g., world"
							       required />
						</div>
						<div>
							<label><?php esc_html_e( 'Label', 'times-news-block' ); ?></label>
							<input type="text"
							       name="times_news_block_rss_feeds[<?php echo esc_attr( $index ); ?>][label]"
							       value="<?php echo esc_attr( $feed['label'] ?? '' ); ?>"
							       class="regular-text"
							       placeholder="e.g., World News"
							       required />
						</div>
						<div>
							<label><?php esc_html_e( 'RSS Feed URL', 'times-news-block' ); ?></label>
							<input type="url"
							       name="times_news_block_rss_feeds[<?php echo esc_attr( $index ); ?>][url]"
							       value="<?php echo esc_url( $feed['url'] ?? '' ); ?>"
							       class="large-text"
							       placeholder="https://example.com/feed.xml"
							       required />
						</div>
						<div>
							<label><?php esc_html_e( 'Enabled', 'times-news-block' ); ?></label>
							<label>
								<input type="checkbox"
								       name="times_news_block_rss_feeds[<?php echo esc_attr( $index ); ?>][enabled]"
								       value="1"
								       <?php checked( ! empty( $feed['enabled'] ) ); ?> />
								<?php esc_html_e( 'Active', 'times-news-block' ); ?>
							</label>
						</div>
						<div>
							<label>&nbsp;</label>
							<a href="#" class="times-news-remove-feed" data-index="<?php echo esc_attr( $index ); ?>">
								<?php esc_html_e( 'Remove', 'times-news-block' ); ?>
							</a>
						</div>
					</div>
				</div>
			<?php endforeach; ?>
		</div>

		<button type="button" class="button times-news-add-feed">
			<?php esc_html_e( '+ Add New Feed', 'times-news-block' ); ?>
		</button>

		<p class="description" style="margin-top: 15px;">
			<?php esc_html_e( 'Add multiple RSS feeds for different categories. The ID will be used in the block dropdown.', 'times-news-block' ); ?>
		</p>

		<!-- VIP COMPLIANCE: JavaScript moved to admin/js/settings.js -->
	</div>
	<?php
}

/**
 * OpenAI API key field render
 */
function times_news_block_openai_key_render() {
	$value = get_option( 'times_news_block_openai_key', '' );
	?>
	<input type="password"
	       name="times_news_block_openai_key"
	       value="<?php echo esc_attr( $value ); ?>"
	       class="regular-text" />
	<p class="description">
		<?php esc_html_e( 'Enter your OpenAI API key to enable AI-powered news filtering.', 'times-news-block' ); ?>
		<a href="https://platform.openai.com/api-keys" target="_blank" rel="noopener">
			<?php esc_html_e( 'Get API Key', 'times-news-block' ); ?>
		</a>
	</p>
	<?php
}

/**
 * Settings page HTML
 */
function times_news_block_settings_page() {
	?>
	<div class="wrap">
		<h1><?php echo esc_html( get_admin_page_title() ); ?></h1>
		<form action="options.php" method="post">
			<?php
			settings_fields( 'times_news_block' );
			do_settings_sections( 'times_news_block' );
			submit_button();
			?>
		</form>
	</div>
	<?php
}

/**
 * Get demo/sample news data for development and testing
 *
 * @param int    $count    Number of articles to return.
 * @param string $category News category.
 * @return array Array of demo news articles.
 */
function times_news_block_get_demo_data( $count = 5, $category = 'all' ) {
	$demo_articles = array(
		array(
			'title'       => 'Breaking: Major Technology Breakthrough Announced',
			'link'        => 'https://www.thetimes.co.uk/article/technology-breakthrough',
			'description' => 'Scientists have made a significant breakthrough in quantum computing technology, promising to revolutionize data processing and encryption methods worldwide.',
			'pubDate'     => gmdate( 'Y-m-d H:i:s', strtotime( '-2 hours' ) ),
			'image'       => 'https://picsum.photos/seed/tech1/800/450',
		),
		array(
			'title'       => 'Global Climate Summit Reaches Historic Agreement',
			'link'        => 'https://www.thetimes.co.uk/article/climate-summit',
			'description' => 'World leaders have signed a landmark agreement on climate action, committing to ambitious carbon reduction targets by 2030.',
			'pubDate'     => gmdate( 'Y-m-d H:i:s', strtotime( '-4 hours' ) ),
			'image'       => 'https://picsum.photos/seed/climate1/800/450',
		),
		array(
			'title'       => 'Economic Growth Surpasses Expectations in Latest Quarter',
			'link'        => 'https://www.thetimes.co.uk/article/economic-growth',
			'description' => 'The economy showed remarkable resilience with growth figures exceeding analyst predictions, driven by strong consumer spending and business investment.',
			'pubDate'     => gmdate( 'Y-m-d H:i:s', strtotime( '-6 hours' ) ),
			'image'       => 'https://picsum.photos/seed/economy1/800/450',
		),
		array(
			'title'       => 'Championship Team Secures Dramatic Victory',
			'link'        => 'https://www.thetimes.co.uk/article/sports-victory',
			'description' => 'In a thrilling finale, the home team secured victory in the final minutes, delighting fans and securing their place in history.',
			'pubDate'     => gmdate( 'Y-m-d H:i:s', strtotime( '-8 hours' ) ),
			'image'       => 'https://picsum.photos/seed/sports1/800/450',
		),
		array(
			'title'       => 'New Art Exhibition Draws Record Crowds',
			'link'        => 'https://www.thetimes.co.uk/article/art-exhibition',
			'description' => 'The highly anticipated contemporary art exhibition has broken attendance records, showcasing works from emerging and established artists alike.',
			'pubDate'     => gmdate( 'Y-m-d H:i:s', strtotime( '-10 hours' ) ),
			'image'       => 'https://picsum.photos/seed/art1/800/450',
		),
		array(
			'title'       => 'Healthcare Innovation Promises Better Patient Outcomes',
			'link'        => 'https://www.thetimes.co.uk/article/healthcare-innovation',
			'description' => 'A new medical technology has shown promising results in clinical trials, offering hope for improved treatment of chronic conditions.',
			'pubDate'     => gmdate( 'Y-m-d H:i:s', strtotime( '-12 hours' ) ),
			'image'       => 'https://picsum.photos/seed/health1/800/450',
		),
		array(
			'title'       => 'Education Reform Proposals Unveiled',
			'link'        => 'https://www.thetimes.co.uk/article/education-reform',
			'description' => 'Comprehensive education reforms have been proposed, focusing on digital literacy and preparing students for future workforce demands.',
			'pubDate'     => gmdate( 'Y-m-d H:i:s', strtotime( '-14 hours' ) ),
			'image'       => 'https://picsum.photos/seed/education1/800/450',
		),
		array(
			'title'       => 'Space Exploration Mission Achieves Milestone',
			'link'        => 'https://www.thetimes.co.uk/article/space-mission',
			'description' => 'The latest space mission has successfully achieved a critical milestone, bringing humanity closer to establishing a permanent presence beyond Earth.',
			'pubDate'     => gmdate( 'Y-m-d H:i:s', strtotime( '-16 hours' ) ),
			'image'       => 'https://picsum.photos/seed/space1/800/450',
		),
		array(
			'title'       => 'Sustainability Initiative Launches in Major Cities',
			'link'        => 'https://www.thetimes.co.uk/article/sustainability',
			'description' => 'Urban centers worldwide are implementing innovative sustainability programs, focusing on renewable energy and waste reduction.',
			'pubDate'     => gmdate( 'Y-m-d H:i:s', strtotime( '-18 hours' ) ),
			'image'       => 'https://picsum.photos/seed/sustain1/800/450',
		),
		array(
			'title'       => 'Cultural Festival Celebrates Diversity and Unity',
			'link'        => 'https://www.thetimes.co.uk/article/cultural-festival',
			'description' => 'Communities come together for an annual celebration of cultural heritage, featuring music, food, and traditions from around the world.',
			'pubDate'     => gmdate( 'Y-m-d H:i:s', strtotime( '-20 hours' ) ),
			'image'       => 'https://picsum.photos/seed/culture1/800/450',
		),
	);

	// Filter by category if not 'all'
	if ( 'all' !== $category ) {
		// In a real scenario, we'd filter by category
		// For demo purposes, we'll just use all articles
	}

	// Return requested number of articles
	return array_slice( $demo_articles, 0, $count );
}
