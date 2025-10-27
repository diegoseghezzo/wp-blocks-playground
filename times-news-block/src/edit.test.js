import { render, screen, waitFor } from '@testing-library/react';
import '@testing-library/jest-dom';
import Edit from './edit';

// Mock WordPress dependencies
jest.mock('@wordpress/i18n', () => ({
	__: (text) => text,
}));

jest.mock('@wordpress/block-editor', () => ({
	useBlockProps: () => ({ className: 'wp-block-times-news-block' }),
	InspectorControls: ({ children }) => <div data-testid="inspector-controls">{children}</div>,
}));

jest.mock('@wordpress/components', () => ({
	PanelBody: ({ title, children }) => (
		<div data-testid="panel-body" data-title={title}>
			{children}
		</div>
	),
	RangeControl: ({ label, value, onChange }) => (
		<div data-testid="range-control">
			<label>{label}</label>
			<input
				type="range"
				value={value}
				onChange={(e) => onChange(Number(e.target.value))}
			/>
		</div>
	),
	SelectControl: ({ label, value, options, onChange }) => (
		<div data-testid="select-control">
			<label>{label}</label>
			<select value={value} onChange={(e) => onChange(e.target.value)}>
				{options.map((opt) => (
					<option key={opt.value} value={opt.value}>
						{opt.label}
					</option>
				))}
			</select>
		</div>
	),
	ToggleControl: ({ label, checked, onChange }) => (
		<div data-testid="toggle-control">
			<label>{label}</label>
			<input
				type="checkbox"
				checked={checked}
				onChange={(e) => onChange(e.target.checked)}
			/>
		</div>
	),
	TextareaControl: ({ label, value, onChange, placeholder }) => (
		<div data-testid="textarea-control">
			<label>{label}</label>
			<textarea
				value={value}
				onChange={(e) => onChange(e.target.value)}
				placeholder={placeholder}
			/>
		</div>
	),
	Placeholder: ({ icon, label, children }) => (
		<div data-testid="placeholder" data-icon={icon}>
			<div>{label}</div>
			{children}
		</div>
	),
	Spinner: () => <div data-testid="spinner">Loading...</div>,
}));

jest.mock('@wordpress/element', () => ({
	...jest.requireActual('react'),
	useEffect: jest.requireActual('react').useEffect,
	useState: jest.requireActual('react').useState,
}));

// Mock API fetch - use factory function to avoid hoisting issues
jest.mock('@wordpress/api-fetch', () => jest.fn());

// Get the mocked function after the mock is set up
import apiFetch from '@wordpress/api-fetch';
const mockApiFetch = apiFetch;

describe('Edit Component', () => {
	const defaultAttributes = {
		newsCount: 5,
		category: 'all',
		useAI: false,
		userCriteria: '',
	};

	const mockSetAttributes = jest.fn();

	beforeEach(() => {
		jest.clearAllMocks();
		mockApiFetch.mockResolvedValue([
			{
				title: 'Test Article 1',
				link: 'https://example.com/1',
				description: 'Description 1',
				pubDate: '2024-01-01',
				image: 'https://example.com/image1.jpg',
			},
			{
				title: 'Test Article 2',
				link: 'https://example.com/2',
				description: 'Description 2',
				pubDate: '2024-01-02',
				image: 'https://example.com/image2.jpg',
			},
		]);
	});

	test('renders without crashing', () => {
		render(
			<Edit attributes={defaultAttributes} setAttributes={mockSetAttributes} />
		);
	});

	test('displays loading state initially', () => {
		render(
			<Edit attributes={defaultAttributes} setAttributes={mockSetAttributes} />
		);

		expect(screen.getByTestId('spinner')).toBeInTheDocument();
	});

	test('fetches and displays news articles', async () => {
		render(
			<Edit attributes={defaultAttributes} setAttributes={mockSetAttributes} />
		);

		await waitFor(() => {
			expect(mockApiFetch).toHaveBeenCalled();
		});

		await waitFor(() => {
			expect(screen.getByText('Test Article 1')).toBeInTheDocument();
			expect(screen.getByText('Test Article 2')).toBeInTheDocument();
		});
	});

	test('renders inspector controls with correct labels', async () => {
		render(
			<Edit attributes={defaultAttributes} setAttributes={mockSetAttributes} />
		);

		await waitFor(() => {
			expect(screen.getByText('Number of Articles')).toBeInTheDocument();
			expect(screen.getByText('Category')).toBeInTheDocument();
			expect(screen.getByText('Enable AI-Powered Filtering')).toBeInTheDocument();
		});
	});

	test('displays AI criteria textarea when AI is enabled', async () => {
		const attributesWithAI = {
			...defaultAttributes,
			useAI: true,
		};

		render(
			<Edit attributes={attributesWithAI} setAttributes={mockSetAttributes} />
		);

		await waitFor(() => {
			expect(screen.getByText('Filtering Criteria')).toBeInTheDocument();
		});
	});

	test('handles API fetch errors gracefully', async () => {
		mockApiFetch.mockRejectedValue(new Error('Failed to fetch'));

		render(
			<Edit attributes={defaultAttributes} setAttributes={mockSetAttributes} />
		);

		await waitFor(() => {
			expect(screen.getByText('Error Loading News')).toBeInTheDocument();
		});
	});

	test('displays placeholder when no articles found', async () => {
		mockApiFetch.mockResolvedValue([]);

		render(
			<Edit attributes={defaultAttributes} setAttributes={mockSetAttributes} />
		);

		await waitFor(() => {
			expect(
				screen.getByText('No news articles found. Check your settings.')
			).toBeInTheDocument();
		});
	});

	test('renders article images when available', async () => {
		render(
			<Edit attributes={defaultAttributes} setAttributes={mockSetAttributes} />
		);

		await waitFor(() => {
			const images = screen.getAllByRole('img');
			expect(images).toHaveLength(2);
			expect(images[0]).toHaveAttribute('src', 'https://example.com/image1.jpg');
		});
	});

	test('makes API call with correct parameters', async () => {
		const customAttributes = {
			newsCount: 10,
			category: 'business',
			useAI: true,
			userCriteria: 'technology news',
		};

		render(
			<Edit attributes={customAttributes} setAttributes={mockSetAttributes} />
		);

		await waitFor(() => {
			expect(mockApiFetch).toHaveBeenCalledWith(
				expect.objectContaining({
					path: expect.stringContaining('count=10'),
				})
			);
		});

		await waitFor(() => {
			expect(mockApiFetch).toHaveBeenCalledWith(
				expect.objectContaining({
					path: expect.stringContaining('category=business'),
				})
			);
		});
	});

	test('article links have correct attributes', async () => {
		render(
			<Edit attributes={defaultAttributes} setAttributes={mockSetAttributes} />
		);

		await waitFor(() => {
			const links = screen.getAllByRole('link');
			expect(links[0]).toHaveAttribute('target', '_blank');
			expect(links[0]).toHaveAttribute('rel', 'noopener noreferrer');
		});
	});
});
