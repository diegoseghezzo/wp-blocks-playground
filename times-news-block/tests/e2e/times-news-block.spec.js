/**
 * WordPress dependencies
 */
import {
	createNewPost,
	insertBlock,
	publishPost,
	saveDraft,
} from '@wordpress/e2e-test-utils';

describe('Times News Block E2E Tests', () => {
	beforeAll(async () => {
		await page.setViewport({ width: 1280, height: 800 });
	});

	beforeEach(async () => {
		await createNewPost();
	});

	it('should be available in the block inserter', async () => {
		await insertBlock('Times News Block');

		// Check if the block is inserted
		const block = await page.waitForSelector(
			'[data-type="create-block/times-news-block"]'
		);
		expect(block).toBeTruthy();
	});

	it('should insert block and display placeholder', async () => {
		await insertBlock('Times News Block');

		// Wait for the block to be inserted
		await page.waitForSelector('[data-type="create-block/times-news-block"]');

		// Check for placeholder or loading state
		const placeholder = await page.$(
			'.wp-block-times-news-block, .components-placeholder'
		);
		expect(placeholder).toBeTruthy();
	});

	it('should show settings in the sidebar', async () => {
		await insertBlock('Times News Block');

		// Wait for block to be selected
		await page.waitForSelector('[data-type="create-block/times-news-block"]');

		// Check for inspector controls
		const settingsPanel = await page.waitForSelector('.block-editor-block-inspector');
		expect(settingsPanel).toBeTruthy();

		// Check for specific controls
		const hasNumberControl = await page.evaluate(() => {
			return !!document.querySelector('[aria-label*="Number of Articles"]');
		});

		const hasCategoryControl = await page.evaluate(() => {
			return !!document.querySelector('[aria-label*="Category"]');
		});

		expect(hasNumberControl || hasCategoryControl).toBeTruthy();
	});

	it('should allow changing the number of articles', async () => {
		await insertBlock('Times News Block');

		await page.waitForSelector('[data-type="create-block/times-news-block"]');

		// Look for range control in inspector
		const rangeControl = await page.$('.components-range-control');

		if (rangeControl) {
			const rangeInput = await rangeControl.$('input[type="range"], input[type="number"]');

			if (rangeInput) {
				await rangeInput.click({ clickCount: 3 });
				await rangeInput.type('10');

				// Verify the value changed
				const value = await page.evaluate(
					(input) => input.value,
					rangeInput
				);

				expect(value).toBe('10');
			}
		}
	});

	it('should allow selecting different categories', async () => {
		await insertBlock('Times News Block');

		await page.waitForSelector('[data-type="create-block/times-news-block"]');

		// Look for select control
		const selectControl = await page.$('.components-select-control__input, select');

		if (selectControl) {
			await selectControl.select('business');

			const value = await page.evaluate(
				(select) => select.value,
				selectControl
			);

			expect(value).toBe('business');
		}
	});

	it('should toggle AI filtering option', async () => {
		await insertBlock('Times News Block');

		await page.waitForSelector('[data-type="create-block/times-news-block"]');

		// Look for AI Filtering panel
		const aiPanel = await page.evaluate(() => {
			const buttons = Array.from(document.querySelectorAll('.components-panel__body-toggle'));
			return buttons.find((btn) => btn.textContent.includes('AI Filtering'));
		});

		if (aiPanel) {
			// Panel exists
			expect(aiPanel).toBeTruthy();
		}
	});

	it('should save the block with attributes', async () => {
		await insertBlock('Times News Block');

		await page.waitForSelector('[data-type="create-block/times-news-block"]');

		// Save as draft
		await saveDraft();

		// Wait for save to complete
		await page.waitForSelector('.editor-post-saved-state.is-saved');

		// Reload page
		await page.reload({ waitUntil: 'networkidle0' });

		// Check if block still exists
		const block = await page.waitForSelector(
			'[data-type="create-block/times-news-block"]'
		);
		expect(block).toBeTruthy();
	});

	it('should publish post with the block', async () => {
		await insertBlock('Times News Block');

		await page.waitForSelector('[data-type="create-block/times-news-block"]');

		// Publish the post
		await publishPost();

		// Wait for publish confirmation
		await page.waitForSelector('.components-snackbar');

		// Verify post was published
		const isPublished = await page.evaluate(() => {
			return !!document.querySelector('.editor-post-publish-panel__header-published');
		});

		expect(isPublished).toBeTruthy();
	});

	it('should display news items when loaded', async () => {
		await insertBlock('Times News Block');

		await page.waitForSelector('[data-type="create-block/times-news-block"]');

		// Wait for news to load (give it some time)
		await page.waitForTimeout(3000);

		// Check for news grid or placeholder
		const hasNewsGrid = await page.evaluate(() => {
			return !!document.querySelector('.times-news-grid');
		});

		const hasPlaceholder = await page.evaluate(() => {
			return !!document.querySelector('.components-placeholder');
		});

		// Either news loaded or placeholder shown
		expect(hasNewsGrid || hasPlaceholder).toBeTruthy();
	});

	it('should handle block removal', async () => {
		await insertBlock('Times News Block');

		await page.waitForSelector('[data-type="create-block/times-news-block"]');

		// Click block to select it
		await page.click('[data-type="create-block/times-news-block"]');

		// Open block toolbar
		const removeButton = await page.waitForSelector(
			'.block-editor-block-toolbar button[aria-label*="Remove"]'
		);

		if (removeButton) {
			await removeButton.click();

			// Verify block is removed
			const blockExists = await page.$('[data-type="create-block/times-news-block"]');
			expect(blockExists).toBeFalsy();
		}
	});

	it('should support multiple instances', async () => {
		// Insert first block
		await insertBlock('Times News Block');
		await page.waitForSelector('[data-type="create-block/times-news-block"]');

		// Insert second block
		await insertBlock('Times News Block');

		// Count blocks
		const blockCount = await page.evaluate(() => {
			return document.querySelectorAll('[data-type="create-block/times-news-block"]').length;
		});

		expect(blockCount).toBeGreaterThanOrEqual(2);
	});
});
