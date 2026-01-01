module.exports = {
	extends: ['@wordpress/stylelint-config'],
	rules: {
		// Allow !important for WordPress admin overrides
		'declaration-no-important': null,
		// Relax selector specificity for admin styling
		'selector-max-id': null,
		// Allow vendor prefixes (handled by build tools if needed)
		'property-no-vendor-prefix': null,
		'value-no-vendor-prefix': null,
		// Relax rules for legacy CSS files
		'rule-empty-line-before': null,
		'comment-empty-line-before': null,
		'no-duplicate-selectors': null,
		'no-descending-specificity': null,
		'color-hex-length': null,
		'font-weight-notation': null,
		'selector-pseudo-element-colon-notation': null,
		// Allow various class naming patterns (legacy code uses camelCase, etc.)
		'selector-class-pattern': null,
		// Allow 0px instead of 0 for legacy compatibility
		'length-zero-no-unit': null,
		// Relax more rules for legacy code
		'value-keyword-case': null,
		'at-rule-empty-line-before': null,
		'selector-attribute-quotes': null,
		'block-no-empty': null,
		// Allow different ID naming patterns
		'selector-id-pattern': null,
		// Allow font-family without generic fallback (WordPress icons)
		'font-family-no-missing-generic-family-keyword': null,
		// Allow quotes around font-family names
		'font-family-name-quotes': null,
		// Allow duplicate properties (sometimes intentional for fallbacks)
		'declaration-block-no-duplicate-properties': null,
	},
	ignoreFiles: [
		'**/*.min.css',
		'node_modules/**',
		'vendor/**',
		'lib/**',
		'assets/css/lib/**',
	],
};
