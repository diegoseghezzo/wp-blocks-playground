// Jest setup file
import '@testing-library/jest-dom';

// Mock WordPress global
global.wp = {
	i18n: {
		__: (text) => text,
		_x: (text) => text,
		_n: (single, plural, number) => (number === 1 ? single : plural),
	},
};

// Suppress console warnings in tests
global.console = {
	...console,
	warn: jest.fn(),
	error: jest.fn(),
};
