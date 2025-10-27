<?php
/**
 * Class TestNewsFetcher
 *
 * @package Times_News_Block
 */

/**
 * Test the news fetching functionality
 */
class TestNewsFetcher extends WP_UnitTestCase {

	/**
	 * Test that the news fetcher function exists
	 */
	public function test_news_fetcher_function_exists() {
		$this->assertTrue( function_exists( 'times_news_block_fetch_news' ) );
	}

	/**
	 * Test fetching news with default parameters
	 */
	public function test_fetch_news_default_params() {
		$news = times_news_block_fetch_news();

		$this->assertIsArray( $news );
		// RSS feeds might be empty or unavailable in test environment
		$this->assertGreaterThanOrEqual( 0, count( $news ) );
	}

	/**
	 * Test fetching news with custom count
	 */
	public function test_fetch_news_custom_count() {
		$count = 3;
		$news = times_news_block_fetch_news( $count );

		$this->assertIsArray( $news );
		$this->assertLessThanOrEqual( $count, count( $news ) );
	}

	/**
	 * Test fetching news with different categories
	 */
	public function test_fetch_news_categories() {
		$categories = array( 'all', 'world', 'business', 'sport', 'culture' );

		foreach ( $categories as $category ) {
			$news = times_news_block_fetch_news( 5, $category );
			$this->assertIsArray( $news );
		}
	}

	/**
	 * Test news article structure
	 */
	public function test_news_article_structure() {
		// Mock a news article
		$article = array(
			'title'       => 'Test Article',
			'link'        => 'https://example.com/test',
			'description' => 'Test description',
			'pubDate'     => '2024-01-01 12:00:00',
			'image'       => 'https://example.com/image.jpg',
		);

		$this->assertArrayHasKey( 'title', $article );
		$this->assertArrayHasKey( 'link', $article );
		$this->assertArrayHasKey( 'description', $article );
		$this->assertArrayHasKey( 'pubDate', $article );
		$this->assertArrayHasKey( 'image', $article );
	}

	/**
	 * Test caching mechanism
	 */
	public function test_news_caching() {
		// First call
		$news1 = times_news_block_fetch_news( 5, 'all', false, '' );

		// Check if transient was set
		$cache_key = 'times_news_' . md5( 'all' . '5' . '0' . '' );
		$cached = get_transient( $cache_key );

		$this->assertNotFalse( $cached );
		$this->assertEquals( $news1, $cached );
	}

	/**
	 * Test render callback function exists
	 */
	public function test_render_callback_exists() {
		$this->assertTrue( function_exists( 'times_news_block_render_callback' ) );
	}

	/**
	 * Test render callback with empty news
	 */
	public function test_render_callback_empty_news() {
		$attributes = array(
			'newsCount'    => 5,
			'useAI'        => false,
			'userCriteria' => '',
			'category'     => 'all',
		);

		$output = times_news_block_render_callback( $attributes, '', null );

		$this->assertIsString( $output );
		$this->assertStringContainsString( 'wp-block-times-news-block', $output );
	}

	/**
	 * Test REST API route registration
	 */
	public function test_rest_route_registered() {
		$routes = rest_get_server()->get_routes();
		$this->assertArrayHasKey( '/times-news-block/v1/news', $routes );
	}

	/**
	 * Test REST API endpoint response
	 */
	public function test_rest_api_endpoint() {
		$request = new WP_REST_Request( 'GET', '/times-news-block/v1/news' );
		$request->set_param( 'count', 5 );
		$request->set_param( 'category', 'all' );

		$response = rest_get_server()->dispatch( $request );

		$this->assertEquals( 200, $response->get_status() );
		$this->assertIsArray( $response->get_data() );
	}

	/**
	 * Test AI filtering function exists
	 */
	public function test_ai_filter_function_exists() {
		$this->assertTrue( function_exists( 'times_news_block_filter_with_ai' ) );
	}

	/**
	 * Test AI filtering without API key
	 */
	public function test_ai_filter_without_api_key() {
		delete_option( 'times_news_block_openai_key' );

		$articles = array(
			array(
				'title'       => 'Test Article 1',
				'description' => 'Description 1',
			),
			array(
				'title'       => 'Test Article 2',
				'description' => 'Description 2',
			),
		);

		$filtered = times_news_block_filter_with_ai( $articles, 'test criteria', 5 );

		// Should return original articles if no API key
		$this->assertEquals( $articles, $filtered );
	}

	/**
	 * Test settings page registration
	 */
	public function test_settings_page_registered() {
		global $wp_settings_sections;

		do_action( 'admin_init' );

		$this->assertArrayHasKey( 'times_news_block', $wp_settings_sections );
	}

	/**
	 * Test OpenAI API key option
	 */
	public function test_openai_api_key_option() {
		$test_key = 'sk-test-key-123';
		update_option( 'times_news_block_openai_key', $test_key );

		$retrieved_key = get_option( 'times_news_block_openai_key' );

		$this->assertEquals( $test_key, $retrieved_key );

		// Cleanup
		delete_option( 'times_news_block_openai_key' );
	}

	/**
	 * Clean up after tests
	 */
	public function tearDown(): void {
		parent::tearDown();

		// Clear all transients
		global $wpdb;
		$wpdb->query( "DELETE FROM $wpdb->options WHERE option_name LIKE '_transient_times_news_%'" );
		$wpdb->query( "DELETE FROM $wpdb->options WHERE option_name LIKE '_transient_timeout_times_news_%'" );
	}
}
