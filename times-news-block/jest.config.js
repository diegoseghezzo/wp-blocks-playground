module.exports = {
	...require('@wordpress/scripts/config/jest-unit.config'),
	testMatch: [
		'**/src/**/*.test.[jt]s?(x)',
		'**/__tests__/**/*.[jt]s?(x)',
	],
	collectCoverageFrom: [
		'src/**/*.{js,jsx}',
		'!src/**/*.test.{js,jsx}',
		'!src/index.js',
	],
	coverageThreshold: {
		global: {
			branches: 50,
			functions: 50,
			lines: 50,
			statements: 50,
		},
	},
	setupFilesAfterEnv: ['<rootDir>/jest.setup.js'],
};
