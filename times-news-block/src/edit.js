import { __ } from '@wordpress/i18n';
import { useBlockProps, InspectorControls } from '@wordpress/block-editor';
import {
	PanelBody,
	RangeControl,
	SelectControl,
	ToggleControl,
	TextareaControl,
	Placeholder,
	Spinner,
} from '@wordpress/components';
import { useEffect, useState } from '@wordpress/element';
import apiFetch from '@wordpress/api-fetch';
import './editor.scss';

export default function Edit({ attributes, setAttributes }) {
	const { newsCount, category, useAI, userCriteria, layout } = attributes;
	const [newsArticles, setNewsArticles] = useState([]);
	const [availableFeeds, setAvailableFeeds] = useState([]);
	const [isLoading, setIsLoading] = useState(false);
	const [error, setError] = useState(null);

	const blockProps = useBlockProps({
		className: `times-news-layout-${layout}`,
	});

	// Fetch available feeds on mount
	useEffect(() => {
		fetchAvailableFeeds();
	}, []);

	// Fetch news articles when attributes change
	useEffect(() => {
		fetchNews();
	}, [newsCount, category, useAI, userCriteria]);

	const fetchAvailableFeeds = async () => {
		try {
			const response = await apiFetch({
				path: '/times-news-block/v1/feeds',
			});
			setAvailableFeeds(response);
		} catch (err) {
			console.error('Error fetching feeds:', err);
			// Fallback to default feeds
			setAvailableFeeds([
				{ label: __('All News', 'times-news-block'), value: 'all' },
				{ label: __('World', 'times-news-block'), value: 'world' },
				{ label: __('Business', 'times-news-block'), value: 'business' },
				{ label: __('Sport', 'times-news-block'), value: 'sport' },
				{ label: __('Culture', 'times-news-block'), value: 'culture' },
			]);
		}
	};

	const fetchNews = async () => {
		setIsLoading(true);
		setError(null);

		try {
			const params = new URLSearchParams({
				count: newsCount,
				category: category,
				useAI: useAI ? '1' : '0',
				criteria: userCriteria,
			});

			const response = await apiFetch({
				path: `/times-news-block/v1/news?${params.toString()}`,
			});

			setNewsArticles(response);
		} catch (err) {
			setError(err.message || __('Failed to fetch news', 'times-news-block'));
			console.error('Error fetching news:', err);
		} finally {
			setIsLoading(false);
		}
	};

	return (
		<>
			<InspectorControls>
				<PanelBody title={__('Settings', 'times-news-block')} initialOpen={true}>
					<SelectControl
						label={__('Layout Style', 'times-news-block')}
						value={layout}
						options={[
							{ label: __('Grid (3 columns)', 'times-news-block'), value: 'grid' },
							{ label: __('List (full width)', 'times-news-block'), value: 'list' },
							{ label: __('Cards (2 columns)', 'times-news-block'), value: 'cards' },
							{ label: __('Compact (minimal)', 'times-news-block'), value: 'compact' },
							{ label: __('Featured (large images)', 'times-news-block'), value: 'featured' },
						]}
						onChange={(value) => setAttributes({ layout: value })}
						help={__('Choose how to display the news articles', 'times-news-block')}
					/>

					<RangeControl
						label={__('Number of Articles', 'times-news-block')}
						value={newsCount}
						onChange={(value) => setAttributes({ newsCount: value })}
						min={1}
						max={20}
						help={__('Select how many articles to display', 'times-news-block')}
					/>

					<SelectControl
						label={__('Category', 'times-news-block')}
						value={category}
						options={availableFeeds.length > 0 ? availableFeeds : [
							{ label: __('Loading...', 'times-news-block'), value: 'all' },
						]}
						onChange={(value) => setAttributes({ category: value })}
						help={__('Choose a news category (configured in Settings → Times News Block)', 'times-news-block')}
					/>
				</PanelBody>

				<PanelBody
					title={__('AI Filtering', 'times-news-block')}
					initialOpen={false}
				>
					<ToggleControl
						label={__('Enable AI-Powered Filtering', 'times-news-block')}
						checked={useAI}
						onChange={(value) => setAttributes({ useAI: value })}
						help={__(
							'Use OpenAI to filter and rank news based on your criteria',
							'times-news-block'
						)}
					/>

					{useAI && (
						<TextareaControl
							label={__('Filtering Criteria', 'times-news-block')}
							value={userCriteria}
							onChange={(value) => setAttributes({ userCriteria: value })}
							help={__(
								'Describe what kind of news you want (e.g., "technology and innovation", "environmental news", "economic analysis")',
								'times-news-block'
							)}
							placeholder={__(
								'Enter your criteria...',
								'times-news-block'
							)}
							rows={4}
						/>
					)}
				</PanelBody>
			</InspectorControls>

			<div {...blockProps}>
				{isLoading && (
					<Placeholder
						icon="rss"
						label={__('Loading Times News...', 'times-news-block')}
					>
						<Spinner />
					</Placeholder>
				)}

				{!isLoading && error && (
					<Placeholder
						icon="warning"
						label={__('Error Loading News', 'times-news-block')}
					>
						<p>{error}</p>
					</Placeholder>
				)}

				{!isLoading && !error && newsArticles.length === 0 && (
					<Placeholder
						icon="rss"
						label={__('Times News Block', 'times-news-block')}
					>
						<p>
							{__(
								'No news articles found. Check your settings.',
								'times-news-block'
							)}
						</p>
					</Placeholder>
				)}

				{!isLoading && !error && newsArticles.length > 0 && (
					<div className={`times-news-container times-news-${layout}`}>
						{newsArticles.map((article, index) => (
							<article key={index} className="times-news-item">
								{article.image && (
									<div className="times-news-image">
										<img
											src={article.image}
											alt={article.title}
											loading="lazy"
										/>
									</div>
								)}
								<div className="times-news-content">
									<h3 className="times-news-title">
										<a
											href={article.link}
											target="_blank"
											rel="noopener noreferrer"
										>
											{article.title}
										</a>
									</h3>
									{article.description && (
										<p className="times-news-description">
											{article.description}
										</p>
									)}
									{article.pubDate && (
										<time className="times-news-date">
											{new Date(article.pubDate).toLocaleDateString()}
										</time>
									)}
								</div>
							</article>
						))}
					</div>
				)}
			</div>
		</>
	);
}
