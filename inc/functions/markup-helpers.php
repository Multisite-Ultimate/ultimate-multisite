<?php
/**
 * Markup Helper Functions
 *
 * @package WP_Ultimo\Functions
 * @since   2.0.0
 */

// Exit if accessed directly
defined('ABSPATH') || exit;

/**
 * Converts an array to a vue data-state parameter.
 *
 * @since 2.0.0
 *
 * @param array $state_array The array to convert.
 * @return string
 */
function wu_convert_to_state($state_array = []) {

	$object = (object) $state_array; // Force object to prevent issues with Vue.

	return wp_json_encode($object);
}

/**
 * Clean up p tags around block elements.
 *
 * @since 2.0.0
 *
 * @param string $content The content.
 * @return string
 */
function wu_remove_empty_p($content): ?string {

	$content = preg_replace(
		[
			'#<p>\s*<(div|aside|section|article|header|footer)#',
			'#</(div|aside|section|article|header|footer)>\s*</p>#',
			'#</(div|aside|section|article|header|footer)>\s*<br ?/?>#',
			'#<(div|aside|section|article|header|footer)(.*?)>\s*</p>#',
			'#<p>\s*</(div|aside|section|article|header|footer)#',
		],
		[
			'<$1',
			'</$1>',
			'</$1>',
			'<$1$2>',
			'</$1',
		],
		$content
	);

	return preg_replace('#<p>(\s|&nbsp;)*+(<br\s*/*>)*(\s|&nbsp;)*</p>#i', '', $content);
}

/**
 * Generates a string containing html attributes to be used inside html tags.
 *
 * This function takes an array of attributes => value and returns
 * a string of concatenated html attributes ready to be echoed inside
 * a HTML element.
 *
 * Example input:
 * array(
 *   'id'    => 'my-element-id',
 *   'class' => 'my-class my-class-2',
 * );
 *
 * Output: id="my-element-id" class="my-class my-class-2"
 *
 * @since 2.0.7
 *
 * @param array $attributes The list of attributes.
 */
function wu_array_to_html_attrs($attributes = []): string {

	$attributes = array_map(fn($key, $value) => $key . '="' . htmlspecialchars((string) $value) . '"', array_keys($attributes), $attributes);

	return implode(' ', $attributes);
}

/**
 * Adds a tooltip icon.
 *
 * @since 2.0.0
 *
 * @param string $tooltip Message to display.
 * @param string $icon Dashicon to display as the icon.
 * @return string
 */
function wu_tooltip($tooltip, $icon = 'dashicons-editor-help') {

	if (empty($tooltip)) {
		return '';
	}

	$markup = sprintf('<span class="wu-styling" role="tooltip" aria-label="%s">', esc_attr($tooltip));

	if ( ! is_admin()) {
		$markup .= '<svg style="width:11px" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" version="1.1" id="Layer_1" x="0px" y="0px" viewBox="0 0 122.88 122.88" xml:space="preserve"><g><path class="st0" d="M122.88,61.44C122.88,27.51,95.37,0,61.44,0C27.51,0,0,27.51,0,61.44c0,33.93,27.51,61.44,61.44,61.44 C95.37,122.88,122.88,95.37,122.88,61.44L122.88,61.44z M68.79,74.58H51.3v-1.75c0-2.97,0.32-5.39,1-7.25 c0.68-1.87,1.68-3.55,3.01-5.1c1.34-1.54,4.35-4.23,9.01-8.11c2.48-2.03,3.73-3.88,3.73-5.56c0-1.71-0.51-3.01-1.5-3.95 c-1-0.93-2.51-1.4-4.54-1.4c-2.19,0-3.98,0.73-5.4,2.16c-1.43,1.44-2.34,3.97-2.74,7.56l-17.88-2.22c0.61-6.57,3-11.86,7.15-15.85 c4.17-4.02,10.55-6.01,19.14-6.01c6.7,0,12.1,1.4,16.21,4.19c5.6,3.78,8.38,8.82,8.38,15.1c0,2.62-0.73,5.14-2.16,7.56 c-1.44,2.44-4.39,5.39-8.85,8.88c-3.09,2.48-5.05,4.44-5.86,5.93C69.19,70.24,68.79,72.19,68.79,74.58L68.79,74.58z M50.68,79.25 h18.76v16.53H50.68V79.25L50.68,79.25z"></path></g></svg>';
	} else {
		$markup .= sprintf('<span class="dashicons wu-text-xs wu-w-auto wu-h-auto wu-align-text-bottom %s"></span>', esc_attr($icon));
	}

	$markup .= '</span>';

	return $markup;
}

/**
 * Adds a tooltip to a HTML element. Needs to be echo'ed.
 *
 * @since 2.0.0
 *
 * @param string $tooltip Message to display.
 */
function wu_tooltip_text($tooltip): string {

	return sprintf('role="tooltip" aria-label="%s"', esc_attr($tooltip));
}

/**
 * Adds a preview tag that displays the image passed on hover.
 *
 * @since 2.0.0
 *
 * @param string  $image_url The image URL.
 * @param boolean $label The label for the preview tag. Defaults to Preview.
 */
function wu_preview_image($image_url, $label = false): string {

	if (empty($label)) {
		$label = __('Preview', 'multisite-ultimate');
	}

	return sprintf(' <span class="wu-image-preview wu-text-gray-600 wu-bg-gray-200 wu-p-1 wu-px-2 wu-ml-1 wu-inline-block wu-text-2xs wu-uppercase wu-font-bold wu-rounded wu-cursor-pointer wu-border-gray-300 wu-border wu-border-solid" data-image="%s">%s %s</span>', $image_url, "<span class='dashicons-wu-image wu-align-middle wu-mr-1'></span>", $label);
}

/**
 * Returns the list of available icons. To add more icons you need use the filter
 * wu_icons_list, and new array using the Key as the optgroup label and the value
 * as the array with all the icons you want to make available.
 *
 * Don't forget to add the css as well.
 *
 * @since 2.0.0
 *
 * @return array With all available icons.
 */
function wu_get_icons_list() {

	$all_icons = [];

	$all_icons['WP Ultimo Icons'] = [
		'dashicons-wu-add_task',
		'dashicons-wu-address',
		'dashicons-wu-add-to-list',
		'dashicons-wu-add-user',
		'dashicons-wu-adjust',
		'dashicons-wu-air',
		'dashicons-wu-aircraft',
		'dashicons-wu-aircraft-landing',
		'dashicons-wu-aircraft-take-off',
		'dashicons-wu-align-bottom',
		'dashicons-wu-align-horizontal-middle',
		'dashicons-wu-align-left',
		'dashicons-wu-align-right',
		'dashicons-wu-align-top',
		'dashicons-wu-align-vertical-middle',
		'dashicons-wu-archive',
		'dashicons-wu-area-graph',
		'dashicons-wu-arrow-bold-down',
		'dashicons-wu-arrow-bold-left',
		'dashicons-wu-arrow-bold-right',
		'dashicons-wu-arrow-bold-up',
		'dashicons-wu-arrow-down',
		'dashicons-wu-arrow-left',
		'dashicons-wu-arrow-long-down',
		'dashicons-wu-arrow-long-left',
		'dashicons-wu-arrow-long-right',
		'dashicons-wu-arrow-long-up',
		'dashicons-wu-arrow-right',
		'dashicons-wu-arrow-up',
		'dashicons-wu-arrow-with-circle-down',
		'dashicons-wu-arrow-with-circle-left',
		'dashicons-wu-arrow-with-circle-right',
		'dashicons-wu-arrow-with-circle-up',
		'dashicons-wu-attachment',
		'dashicons-wu-awareness-ribbon',
		'dashicons-wu-back',
		'dashicons-wu-back-in-time',
		'dashicons-wu-bar-graph',
		'dashicons-wu-battery',
		'dashicons-wu-beamed-note',
		'dashicons-wu-bell',
		'dashicons-wu-blackboard',
		'dashicons-wu-block',
		'dashicons-wu-book',
		'dashicons-wu-bookmark',
		'dashicons-wu-bookmarks',
		'dashicons-wu-bowl',
		'dashicons-wu-box',
		'dashicons-wu-briefcase',
		'dashicons-wu-browser',
		'dashicons-wu-brush',
		'dashicons-wu-bucket',
		'dashicons-wu-cake',
		'dashicons-wu-calculator',
		'dashicons-wu-calendar',
		'dashicons-wu-camera',
		'dashicons-wu-ccw',
		'dashicons-wu-chat',
		'dashicons-wu-check',
		'dashicons-wu-checkbox-checked',
		'dashicons-wu-checkbox-unchecked',
		'dashicons-wu-chevron-down',
		'dashicons-wu-chevron-left',
		'dashicons-wu-chevron-right',
		'dashicons-wu-chevron-small-down',
		'dashicons-wu-chevron-small-left',
		'dashicons-wu-chevron-small-right',
		'dashicons-wu-chevron-small-up',
		'dashicons-wu-chevron-thin-down',
		'dashicons-wu-chevron-thin-left',
		'dashicons-wu-chevron-thin-right',
		'dashicons-wu-chevron-thin-up',
		'dashicons-wu-chevron-up',
		'dashicons-wu-chevron-with-circle-down',
		'dashicons-wu-chevron-with-circle-left',
		'dashicons-wu-chevron-with-circle-right',
		'dashicons-wu-chevron-with-circle-up',
		'dashicons-wu-circle',
		'dashicons-wu-circle-with-cross',
		'dashicons-wu-circle-with-minus',
		'dashicons-wu-circle-with-plus',
		'dashicons-wu-circular-graph',
		'dashicons-wu-clapperboard',
		'dashicons-wu-classic-computer',
		'dashicons-wu-clipboard',
		'dashicons-wu-clock',
		'dashicons-wu-cloud',
		'dashicons-wu-code',
		'dashicons-wu-cog',
		'dashicons-wu-coin-dollar',
		'dashicons-wu-coin-euro',
		'dashicons-wu-coin-pound',
		'dashicons-wu-coin-yen',
		'dashicons-wu-colours',
		'dashicons-wu-compass',
		'dashicons-wu-controller-fast-forward',
		'dashicons-wu-controller-jump-to-start',
		'dashicons-wu-controller-next',
		'dashicons-wu-controller-paus',
		'dashicons-wu-controller-play',
		'dashicons-wu-controller-record',
		'dashicons-wu-controller-stop',
		'dashicons-wu-controller-volume',
		'dashicons-wu-copy',
		'dashicons-wu-credit',
		'dashicons-wu-credit-card',
		'dashicons-wu-credit-card1',
		'dashicons-wu-cross',
		'dashicons-wu-cup',
		'dashicons-wu-cw',
		'dashicons-wu-cycle',
		'dashicons-wu-database',
		'dashicons-wu-dial-pad',
		'dashicons-wu-direction',
		'dashicons-wu-document',
		'dashicons-wu-document-landscape',
		'dashicons-wu-documents',
		'dashicons-wu-done',
		'dashicons-wu-done_all',
		'dashicons-wu-dot-single',
		'dashicons-wu-dots-three-horizontal',
		'dashicons-wu-dots-three-vertical',
		'dashicons-wu-dots-two-horizontal',
		'dashicons-wu-dots-two-vertical',
		'dashicons-wu-download',
		'dashicons-wu-drink',
		'dashicons-wu-drive',
		'dashicons-wu-drop',
		'dashicons-wu-edit',
		'dashicons-wu-email',
		'dashicons-wu-emoji-flirt',
		'dashicons-wu-emoji-happy',
		'dashicons-wu-emoji-neutral',
		'dashicons-wu-emoji-sad',
		'dashicons-wu-erase',
		'dashicons-wu-eraser',
		'dashicons-wu-export',
		'dashicons-wu-eye',
		'dashicons-wu-feather',
		'dashicons-wu-filter_1',
		'dashicons-wu-filter_2',
		'dashicons-wu-filter_3',
		'dashicons-wu-filter_4',
		'dashicons-wu-filter_5',
		'dashicons-wu-filter_6',
		'dashicons-wu-filter_7',
		'dashicons-wu-filter_8',
		'dashicons-wu-filter_9',
		'dashicons-wu-filter_9_plus',
		'dashicons-wu-flag',
		'dashicons-wu-flash',
		'dashicons-wu-flashlight',
		'dashicons-wu-flat-brush',
		'dashicons-wu-flow-branch',
		'dashicons-wu-flow-cascade',
		'dashicons-wu-flow-line',
		'dashicons-wu-flow-parallel',
		'dashicons-wu-flow-tree',
		'dashicons-wu-folder',
		'dashicons-wu-folder-images',
		'dashicons-wu-folder-music',
		'dashicons-wu-folder-video',
		'dashicons-wu-forward',
		'dashicons-wu-funnel',
		'dashicons-wu-game-controller',
		'dashicons-wu-gauge',
		'dashicons-wu-globe',
		'dashicons-wu-graduation-cap',
		'dashicons-wu-grid',
		'dashicons-wu-hair-cross',
		'dashicons-wu-hand',
		'dashicons-wu-hash',
		'dashicons-wu-hashtag',
		'dashicons-wu-heart',
		'dashicons-wu-heart-outlined',
		'dashicons-wu-help',
		'dashicons-wu-help-with-circle',
		'dashicons-wu-home',
		'dashicons-wu-hour-glass',
		'dashicons-wu-image',
		'dashicons-wu-image-inverted',
		'dashicons-wu-images',
		'dashicons-wu-inbox',
		'dashicons-wu-infinity',
		'dashicons-wu-info',
		'dashicons-wu-info-with-circle',
		'dashicons-wu-install',
		'dashicons-wu-key',
		'dashicons-wu-keyboard',
		'dashicons-wu-lab-flask',
		'dashicons-wu-landline',
		'dashicons-wu-language',
		'dashicons-wu-laptop',
		'dashicons-wu-layers',
		'dashicons-wu-leaf',
		'dashicons-wu-level-down',
		'dashicons-wu-level-up',
		'dashicons-wu-lifebuoy',
		'dashicons-wu-light-bulb',
		'dashicons-wu-light-down',
		'dashicons-wu-light-up',
		'dashicons-wu-line-graph',
		'dashicons-wu-link',
		'dashicons-wu-list',
		'dashicons-wu-location',
		'dashicons-wu-location-pin',
		'dashicons-wu-lock',
		'dashicons-wu-lock-open',
		'dashicons-wu-login',
		'dashicons-wu-log-out',
		'dashicons-wu-loop',
		'dashicons-wu-magnet',
		'dashicons-wu-magnifying-glass',
		'dashicons-wu-mail',
		'dashicons-wu-man',
		'dashicons-wu-map',
		'dashicons-wu-mask',
		'dashicons-wu-medal',
		'dashicons-wu-megaphone',
		'dashicons-wu-menu',
		'dashicons-wu-message',
		'dashicons-wu-mic',
		'dashicons-wu-minus',
		'dashicons-wu-mobile',
		'dashicons-wu-modern-mic',
		'dashicons-wu-moon',
		'dashicons-wu-mouse',
		'dashicons-wu-music',
		'dashicons-wu-new',
		'dashicons-wu-new-message',
		'dashicons-wu-news',
		'dashicons-wu-note',
		'dashicons-wu-notification',
		'dashicons-wu-number',
		'dashicons-wu-old-mobile',
		'dashicons-wu-old-phone',
		'dashicons-wu-open-book',
		'dashicons-wu-palette',
		'dashicons-wu-paper-plane',
		'dashicons-wu-pencil',
		'dashicons-wu-pencil2',
		'dashicons-wu-phone',
		'dashicons-wu-pie-chart',
		'dashicons-wu-pin',
		'dashicons-wu-plus',
		'dashicons-wu-popup',
		'dashicons-wu-power-cord',
		'dashicons-wu-power-plug',
		'dashicons-wu-price-ribbon',
		'dashicons-wu-price-tag',
		'dashicons-wu-print',
		'dashicons-wu-progress-empty',
		'dashicons-wu-progress-full',
		'dashicons-wu-progress-one',
		'dashicons-wu-progress-two',
		'dashicons-wu-publish',
		'dashicons-wu-qrcode',
		'dashicons-wu-quote',
		'dashicons-wu-radio',
		'dashicons-wu-remove-user',
		'dashicons-wu-reply',
		'dashicons-wu-reply-all',
		'dashicons-wu-resize-100',
		'dashicons-wu-resize-full-screen',
		'dashicons-wu-retweet',
		'dashicons-wu-rocket',
		'dashicons-wu-round-brush',
		'dashicons-wu-rss',
		'dashicons-wu-ruler',
		'dashicons-wu-save',
		'dashicons-wu-scissors',
		'dashicons-wu-select-arrows',
		'dashicons-wu-share',
		'dashicons-wu-shareable',
		'dashicons-wu-share-alternitive',
		'dashicons-wu-shield',
		'dashicons-wu-shop',
		'dashicons-wu-shopping-bag',
		'dashicons-wu-shopping-basket',
		'dashicons-wu-shopping-cart',
		'dashicons-wu-shuffle',
		'dashicons-wu-signal',
		'dashicons-wu-sound',
		'dashicons-wu-sound-mix',
		'dashicons-wu-sound-mute',
		'dashicons-wu-sports-club',
		'dashicons-wu-spreadsheet',
		'dashicons-wu-squared-cross',
		'dashicons-wu-squared-minus',
		'dashicons-wu-squared-plus',
		'dashicons-wu-star',
		'dashicons-wu-star-outlined',
		'dashicons-wu-stopwatch',
		'dashicons-wu-suitcase',
		'dashicons-wu-swap',
		'dashicons-wu-sweden',
		'dashicons-wu-switch',
		'dashicons-wu-tablet',
		'dashicons-wu-tag',
		'dashicons-wu-text',
		'dashicons-wu-text-document',
		'dashicons-wu-text-document-inverted',
		'dashicons-wu-thermometer',
		'dashicons-wu-thumbs-down',
		'dashicons-wu-thumbs-up',
		'dashicons-wu-thunder-cloud',
		'dashicons-wu-ticket',
		'dashicons-wu-ticket1',
		'dashicons-wu-time-slot',
		'dashicons-wu-toggle_on',
		'dashicons-wu-tools',
		'dashicons-wu-traffic-cone',
		'dashicons-wu-trash',
		'dashicons-wu-tree',
		'dashicons-wu-triangle-down',
		'dashicons-wu-triangle-left',
		'dashicons-wu-triangle-right',
		'dashicons-wu-triangle-up',
		'dashicons-wu-trophy',
		'dashicons-wu-tv',
		'dashicons-wu-typing',
		'dashicons-wu-uninstall',
		'dashicons-wu-unread',
		'dashicons-wu-untag',
		'dashicons-wu-upload',
		'dashicons-wu-upload-to-cloud',
		'dashicons-wu-user',
		'dashicons-wu-users',
		'dashicons-wu-v-card',
		'dashicons-wu-verified',
		'dashicons-wu-video',
		'dashicons-wu-vinyl',
		'dashicons-wu-voicemail',
		'dashicons-wu-wallet',
		'dashicons-wu-warning',
		'dashicons-wu-wp-ultimo',
	];

	$all_icons['Dashicons'] = [
		'dashicons-before dashicons-admin-appearance',
		'dashicons-before dashicons-admin-collapse',
		'dashicons-before dashicons-admin-comments',
		'dashicons-before dashicons-admin-customizer',
		'dashicons-before dashicons-admin-generic',
		'dashicons-before dashicons-admin-home',
		'dashicons-before dashicons-admin-links',
		'dashicons-before dashicons-admin-media',
		'dashicons-before dashicons-admin-multisite',
		'dashicons-before dashicons-admin-network',
		'dashicons-before dashicons-admin-page',
		'dashicons-before dashicons-admin-plugins',
		'dashicons-before dashicons-admin-post',
		'dashicons-before dashicons-admin-settings',
		// 'dashicons-before dashicons-admin-site-alt',
		// 'dashicons-before dashicons-admin-site-alt2',
		// 'dashicons-before dashicons-admin-site-alt3',
		'dashicons-before dashicons-admin-site',
		'dashicons-before dashicons-admin-tools',
		'dashicons-before dashicons-admin-users',
		'dashicons-before dashicons-album',
		'dashicons-before dashicons-align-center',
		'dashicons-before dashicons-align-left',
		'dashicons-before dashicons-align-none',
		'dashicons-before dashicons-align-right',
		'dashicons-before dashicons-analytics',
		'dashicons-before dashicons-archive',
		'dashicons-before dashicons-arrow-down-alt',
		'dashicons-before dashicons-arrow-down-alt2',
		'dashicons-before dashicons-arrow-down',
		'dashicons-before dashicons-arrow-left-alt',
		'dashicons-before dashicons-arrow-left-alt2',
		'dashicons-before dashicons-arrow-left',
		'dashicons-before dashicons-arrow-right-alt',
		'dashicons-before dashicons-arrow-right-alt2',
		'dashicons-before dashicons-arrow-right',
		'dashicons-before dashicons-arrow-up-alt',
		'dashicons-before dashicons-arrow-up-alt2',
		'dashicons-before dashicons-arrow-up',
		'dashicons-before dashicons-art',
		'dashicons-before dashicons-awards',
		'dashicons-before dashicons-backup',
		'dashicons-before dashicons-book-alt',
		'dashicons-before dashicons-book',
		'dashicons-before dashicons-buddicons-activity',
		'dashicons-before dashicons-buddicons-bbpress-logo',
		'dashicons-before dashicons-buddicons-buddypress-logo',
		'dashicons-before dashicons-buddicons-community',
		'dashicons-before dashicons-buddicons-forums',
		'dashicons-before dashicons-buddicons-friends',
		'dashicons-before dashicons-buddicons-groups',
		'dashicons-before dashicons-buddicons-pm',
		'dashicons-before dashicons-buddicons-replies',
		'dashicons-before dashicons-buddicons-topics',
		'dashicons-before dashicons-buddicons-tracking',
		'dashicons-before dashicons-building',
		'dashicons-before dashicons-businessman',
		'dashicons-before dashicons-calendar-alt',
		'dashicons-before dashicons-calendar',
		'dashicons-before dashicons-camera',
		'dashicons-before dashicons-carrot',
		'dashicons-before dashicons-cart',
		'dashicons-before dashicons-category',
		'dashicons-before dashicons-chart-area',
		'dashicons-before dashicons-chart-bar',
		'dashicons-before dashicons-chart-line',
		'dashicons-before dashicons-chart-pie',
		'dashicons-before dashicons-clipboard',
		'dashicons-before dashicons-clock',
		'dashicons-before dashicons-cloud',
		'dashicons-before dashicons-controls-back',
		'dashicons-before dashicons-controls-forward',
		'dashicons-before dashicons-controls-pause',
		'dashicons-before dashicons-controls-play',
		'dashicons-before dashicons-controls-repeat',
		'dashicons-before dashicons-controls-skipback',
		'dashicons-before dashicons-controls-skipforward',
		'dashicons-before dashicons-controls-volumeoff',
		'dashicons-before dashicons-controls-volumeon',
		'dashicons-before dashicons-dashboard',
		'dashicons-before dashicons-desktop',
		'dashicons-before dashicons-dismiss',
		'dashicons-before dashicons-download',
		'dashicons-before dashicons-edit',
		'dashicons-before dashicons-editor-aligncenter',
		'dashicons-before dashicons-editor-alignleft',
		'dashicons-before dashicons-editor-alignright',
		'dashicons-before dashicons-editor-bold',
		'dashicons-before dashicons-editor-break',
		'dashicons-before dashicons-editor-code',
		'dashicons-before dashicons-editor-contract',
		'dashicons-before dashicons-editor-customchar',
		'dashicons-before dashicons-editor-expand',
		'dashicons-before dashicons-editor-help',
		'dashicons-before dashicons-editor-indent',
		'dashicons-before dashicons-editor-insertmore',
		'dashicons-before dashicons-editor-italic',
		'dashicons-before dashicons-editor-justify',
		'dashicons-before dashicons-editor-kitchensink',
		'dashicons-before dashicons-editor-ltr',
		'dashicons-before dashicons-editor-ol',
		'dashicons-before dashicons-editor-outdent',
		'dashicons-before dashicons-editor-paragraph',
		'dashicons-before dashicons-editor-paste-text',
		'dashicons-before dashicons-editor-paste-word',
		'dashicons-before dashicons-editor-quote',
		'dashicons-before dashicons-editor-removeformatting',
		'dashicons-before dashicons-editor-rtl',
		'dashicons-before dashicons-editor-spellcheck',
		'dashicons-before dashicons-editor-strikethrough',
		'dashicons-before dashicons-editor-table',
		'dashicons-before dashicons-editor-textcolor',
		'dashicons-before dashicons-editor-ul',
		'dashicons-before dashicons-editor-underline',
		'dashicons-before dashicons-editor-unlink',
		'dashicons-before dashicons-editor-video',
		'dashicons-before dashicons-email-alt',
		// 'dashicons-before dashicons-email-alt2',
		'dashicons-before dashicons-email',
		'dashicons-before dashicons-excerpt-view',
		'dashicons-before dashicons-external',
		'dashicons-before dashicons-facebook-alt',
		'dashicons-before dashicons-facebook',
		'dashicons-before dashicons-feedback',
		'dashicons-before dashicons-filter',
		'dashicons-before dashicons-flag',
		'dashicons-before dashicons-format-aside',
		'dashicons-before dashicons-format-audio',
		'dashicons-before dashicons-format-chat',
		'dashicons-before dashicons-format-gallery',
		'dashicons-before dashicons-format-image',
		'dashicons-before dashicons-format-quote',
		'dashicons-before dashicons-format-status',
		'dashicons-before dashicons-format-video',
		'dashicons-before dashicons-forms',
		'dashicons-before dashicons-googleplus',
		'dashicons-before dashicons-grid-view',
		'dashicons-before dashicons-groups',
		'dashicons-before dashicons-hammer',
		'dashicons-before dashicons-heart',
		'dashicons-before dashicons-hidden',
		'dashicons-before dashicons-id-alt',
		'dashicons-before dashicons-id',
		'dashicons-before dashicons-image-crop',
		'dashicons-before dashicons-image-filter',
		'dashicons-before dashicons-image-flip-horizontal',
		'dashicons-before dashicons-image-flip-vertical',
		'dashicons-before dashicons-image-rotate-left',
		'dashicons-before dashicons-image-rotate-right',
		'dashicons-before dashicons-image-rotate',
		'dashicons-before dashicons-images-alt',
		'dashicons-before dashicons-images-alt2',
		'dashicons-before dashicons-index-card',
		'dashicons-before dashicons-info',
		'dashicons-before dashicons-laptop',
		'dashicons-before dashicons-layout',
		'dashicons-before dashicons-leftright',
		'dashicons-before dashicons-lightbulb',
		'dashicons-before dashicons-list-view',
		'dashicons-before dashicons-location-alt',
		'dashicons-before dashicons-location',
		'dashicons-before dashicons-lock',
		'dashicons-before dashicons-marker',
		'dashicons-before dashicons-media-archive',
		'dashicons-before dashicons-media-audio',
		'dashicons-before dashicons-media-code',
		'dashicons-before dashicons-media-default',
		'dashicons-before dashicons-media-document',
		'dashicons-before dashicons-media-interactive',
		'dashicons-before dashicons-media-spreadsheet',
		'dashicons-before dashicons-media-text',
		'dashicons-before dashicons-media-video',
		'dashicons-before dashicons-megaphone',
		// 'dashicons-before dashicons-menu-alt',
		'dashicons-before dashicons-menu',
		'dashicons-before dashicons-microphone',
		'dashicons-before dashicons-migrate',
		'dashicons-before dashicons-minus',
		'dashicons-before dashicons-money',
		'dashicons-before dashicons-move',
		'dashicons-before dashicons-nametag',
		'dashicons-before dashicons-networking',
		'dashicons-before dashicons-no-alt',
		'dashicons-before dashicons-no',
		'dashicons-before dashicons-palmtree',
		'dashicons-before dashicons-paperclip',
		'dashicons-before dashicons-performance',
		'dashicons-before dashicons-phone',
		'dashicons-before dashicons-playlist-audio',
		'dashicons-before dashicons-playlist-video',
		'dashicons-before dashicons-plus-alt',
		'dashicons-before dashicons-plus-light',
		'dashicons-before dashicons-plus',
		'dashicons-before dashicons-portfolio',
		'dashicons-before dashicons-post-status',
		'dashicons-before dashicons-pressthis',
		'dashicons-before dashicons-products',
		'dashicons-before dashicons-randomize',
		'dashicons-before dashicons-redo',
		// 'dashicons-before dashicons-rest-api',
		'dashicons-before dashicons-rss',
		'dashicons-before dashicons-schedule',
		'dashicons-before dashicons-screenoptions',
		'dashicons-before dashicons-search',
		'dashicons-before dashicons-share-alt',
		'dashicons-before dashicons-share-alt2',
		'dashicons-before dashicons-share',
		'dashicons-before dashicons-shield-alt',
		'dashicons-before dashicons-shield',
		'dashicons-before dashicons-slides',
		'dashicons-before dashicons-smartphone',
		'dashicons-before dashicons-smiley',
		'dashicons-before dashicons-sort',
		'dashicons-before dashicons-sos',
		'dashicons-before dashicons-star-empty',
		'dashicons-before dashicons-star-filled',
		'dashicons-before dashicons-star-half',
		'dashicons-before dashicons-sticky',
		'dashicons-before dashicons-store',
		'dashicons-before dashicons-tablet',
		'dashicons-before dashicons-tag',
		'dashicons-before dashicons-tagcloud',
		'dashicons-before dashicons-testimonial',
		'dashicons-before dashicons-text',
		'dashicons-before dashicons-thumbs-down',
		'dashicons-before dashicons-thumbs-up',
		'dashicons-before dashicons-tickets-alt',
		'dashicons-before dashicons-tickets',
		// 'dashicons-before dashicons-tide',
		'dashicons-before dashicons-translation',
		'dashicons-before dashicons-trash',
		'dashicons-before dashicons-twitter',
		'dashicons-before dashicons-undo',
		'dashicons-before dashicons-universal-access-alt',
		'dashicons-before dashicons-universal-access',
		'dashicons-before dashicons-unlock',
		'dashicons-before dashicons-update',
		'dashicons-before dashicons-upload',
		'dashicons-before dashicons-vault',
		'dashicons-before dashicons-video-alt',
		'dashicons-before dashicons-video-alt2',
		'dashicons-before dashicons-video-alt3',
		'dashicons-before dashicons-visibility',
		'dashicons-before dashicons-warning',
		'dashicons-before dashicons-welcome-add-page',
		'dashicons-before dashicons-welcome-comments',
		'dashicons-before dashicons-welcome-learn-more',
		'dashicons-before dashicons-welcome-view-site',
		'dashicons-before dashicons-welcome-widgets-menus',
		'dashicons-before dashicons-welcome-write-blog',
		'dashicons-before dashicons-wordpress-alt',
		'dashicons-before dashicons-wordpress',
		'dashicons-before dashicons-yes-alt',
		'dashicons-before dashicons-yes',
	];

	return apply_filters('wu_icons_list', $all_icons);
}

/**
 * Checks if the current theme is a block theme.
 *
 * @since 2.0.11
 * @return boolean
 */
function wu_is_block_theme() {

	if (function_exists('wp_is_block_theme')) {
		return wp_is_block_theme();
	}

	return false;
}

/**
 * Generate flag emoji from a two-character country code.
 *
 * @param string $country_code The two-letter uppercase country code (e.g., "US", "GB").
 * @return string The flag emoji or empty string if invalid input.
 */
function wu_get_flag_emoji(string $country_code): string {
	// Ensure the country code is uppercase.
	$country_code = strtoupper($country_code);

	// Validate that the input is exactly two letters.
	if (strlen($country_code) !== 2 || ! ctype_alpha($country_code)) {
		return ''; // Return an empty string for invalid input.
	}

	// Convert the country code to regional indicator symbols.
	$emoji = '';
	foreach (str_split($country_code) as $char) {
		$emoji .= mb_chr(0x1F1E6 - ord('A') + ord($char), 'UTF-8');
	}

	return $emoji;
}
