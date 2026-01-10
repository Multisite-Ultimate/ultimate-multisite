module.exports = {
	root: true,
	extends: ['plugin:@wordpress/eslint-plugin/recommended-with-formatting'],
	env: {
		browser: true,
		jquery: true,
	},
	globals: {
		// WordPress globals
		wp: 'readonly',
		ajaxurl: 'readonly',
		pagenow: 'readonly',
		typenow: 'readonly',
		adminpage: 'readonly',
		// Vue.js
		Vue: 'readonly',
		// Ultimate Multisite globals
		wu_moment: 'readonly',
		wu_activity_stream_nonce: 'readonly',
		wu_checkout: 'readonly',
		wu_checkout_form: 'readonly',
		wu_block_ui: 'readonly',
		wu_block_ui_legacy: 'readonly',
		wu_format_money: 'readonly',
		wu_template_previewer: 'readonly',
		wu_fields: 'readonly',
		wu_settings: 'readonly',
		wu_addons: 'readonly',
		wubox: 'readonly',
		Swal: 'readonly',
		ClipboardJS: 'readonly',
		Shepherd: 'readonly',
		ApexCharts: 'readonly',
		tippy: 'readonly',
	},
	rules: {
		// Allow tabs for indentation (matches PHP coding standards)
		indent: ['error', 'tab', { SwitchCase: 1 }],
		// Disable prettier - too strict for legacy code
		'prettier/prettier': 'off',
		// Relax some rules for legacy code compatibility
		'no-unused-vars': ['warn', { vars: 'all', args: 'none', ignoreRestSiblings: true }],
		// Allow console for development
		'no-console': 'warn',
		// Allow var for legacy code
		'no-var': 'warn',
		// Prefer const but don't enforce strictly for legacy code
		'prefer-const': 'warn',
		// Object shorthand is nice but not required for legacy code
		'object-shorthand': 'warn',
		// Allow snake_case for WP compatibility
		camelcase: 'off',
		// Allow redeclaring globals (we define them above)
		'no-redeclare': 'off',
		// Disable strict formatting rules for legacy code
		'space-in-parens': 'off',
		'comma-dangle': 'off',
		quotes: 'off',
		semi: 'off',
		'padded-blocks': 'off',
		'eol-last': 'off',
		'space-before-function-paren': 'off',
		'space-before-blocks': 'off',
	},
	ignorePatterns: [
		'**/*.min.js',
		'node_modules/',
		'vendor/',
		'lib/',
		'assets/js/lib/',
	],
};
