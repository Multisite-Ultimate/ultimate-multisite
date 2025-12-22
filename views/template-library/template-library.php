<?php
/**
 * Template Library page.
 *
 * @since 2.5.0
 */

if (! defined('ABSPATH')) {
	exit; // Exit if accessed directly
}

?>

<div id="wp-ultimo-wrap" class="<?php wu_wrap_use_container(); ?> wrap wu-wrap <?php echo esc_attr($classes); ?>">

	<h1 class="wp-heading-inline">

	<?php echo esc_html($page->get_title()); ?> <span v-cloak v-if="count > 0" class="title-count theme-count" v-text="count"></span>

	<?php
	/**
	 * You can filter the get_title_link using wu_page_list_get_title_link
	 *
	 * @since 2.5.0
	 */
	foreach ($page->get_title_links() as $action_link) :
		$action_classes = isset($action_link['classes']) ? $action_link['classes'] : '';

		?>

		<a title="<?php echo esc_attr($action_link['label']); ?>" href="<?php echo esc_url($action_link['url']); ?>" class="page-title-action <?php echo esc_attr($action_classes); ?>">

		<?php if ($action_link['icon']) : ?>

			<span class="dashicons dashicons-<?php echo esc_attr($action_link['icon']); ?> wu-text-sm wu-align-middle wu-h-4 wu-w-4">
			&nbsp;
			</span>

		<?php endif; ?>

		<?php echo esc_html($action_link['label']); ?>

		</a>

	<?php endforeach; ?>

	<?php
	/**
	 * Allow plugin developers to add additional buttons to list pages
	 *
	 * @since 2.5.0
	 * @param WU_Page $page WP Ultimo Page instance
	 */
	do_action('wu_page_template_library_after_title', $page);
	?>

	</h1>

	<?php if (wu_request('updated')) : ?>

	<div id="message" class="updated notice wu-admin-notice notice-success is-dismissible below-h2">
		<p><?php esc_html_e('Settings successfully saved.', 'ultimate-multisite'); ?></p>
	</div>

	<?php endif; ?>
	<?php if ($user) : ?>
		<div class="notice wu-hidden wu-admin-notice wu-styling hover:wu-styling notice-success">
			<?php // translators: %1$s: the current user display name, %2$s: their password. ?>
			<p class="wu-py-2"><?php echo esc_html(sprintf(__('Connected to UltimateMultisite.com as %1$s (%2$s).', 'ultimate-multisite'), $user['display_name'], $user['user_email'])); ?> <a title="<?php esc_attr_e('Disconnect your site', 'ultimate-multisite'); ?>" href="<?php echo esc_attr($logout_url); ?>"><?php esc_html_e('Disconnect', 'ultimate-multisite'); ?></a></p>
		</div>
	<?php else : ?>
		<div class="notice wu-hidden wu-admin-notice wu-styling hover:wu-styling notice-warning">
			<p class="wu-py-2"><?php esc_html_e('Connect to UltimateMultisite.com to download premium templates.', 'ultimate-multisite'); ?></p>
			<div>
				<ul class="wu-m-0">
					<li class="">
						<a class="button-primary wu-font-bold wu-uppercase" title="<?php esc_attr_e('Connect your site', 'ultimate-multisite'); ?>" href="<?php echo esc_attr($oauth_url); ?>"><?php esc_html_e('Connect your site to UltimateMultisite.com', 'ultimate-multisite'); ?></a>
					</li>
				</ul>
			</div>
		</div>
	<?php endif; ?>

	<hr class="wp-header-end">

	<div class="wu-flex wu-items-center wu-justify-between wu-mb-6 wu-p-4 wu-bg-gray-50 wu-border wu-border-gray-200 wu-rounded-lg">
		<div class="wu-flex wu-items-center wu-space-x-4">
			<span class="wu-text-sm wu-text-gray-600">
				<span class="wu-font-semibold" v-text="count" v-cloak>0</span> <?php esc_html_e('templates', 'ultimate-multisite'); ?>
			</span>
		</div>

		<div class="wu-flex wu-items-center wu-space-x-2" id="templates-menu">
			<?php foreach ($sections as $section_name => $section) : ?>
				<a
					href="<?php echo esc_url($page->get_section_link($section_name)); ?>"
					class="wu-px-4 wu-py-2 wu-text-sm wu-mx-4 wu-border wu-transition-colors wu-no-underline"
					:class="category === '<?php echo esc_attr($section_name); ?>' ? 'wu-bg-gray-100 wu-text-gray-900 wu-border-blue-600 wu-border-solid' : 'wu-bg-white wu-text-gray-700 wu-border-gray-300 hover:wu-bg-gray-50'"
					@click.prevent="set_category('<?php echo esc_attr($section_name); ?>')"
					v-show="'<?php echo esc_attr($section_name); ?>' === 'all' || available_categories.some(cat => cat.slug === '<?php echo esc_attr($section_name); ?>')"
				>
					<span class="<?php echo esc_attr($section['icon']); ?> wu-mr-1"></span>
					<?php echo esc_html($section['title']); ?>
				</a>
			<?php endforeach; ?>
		</div>

		<div id="search-templates">
			<input
				type="search"
				class="wu-w-64 wu-px-3 wu-py-2 wu-text-sm wu-border wu-border-gray-300 wu-rounded-md focus:wu-outline-none focus:wu-ring-2 focus:wu-ring-blue-500 focus:wu-border-blue-500"
				placeholder="<?php esc_attr_e('Search templates...', 'ultimate-multisite'); ?>"
				v-model="search"
			/>
		</div>
	</div>

	<div id="wu-template-library">

		<div v-if="loading" class="wu-text-center wu-py-12">
			<div class="wu-inline-flex wu-items-center wu-px-4 wu-py-2 wu-text-sm wu-text-blue-600 wu-bg-blue-50 wu-border wu-border-blue-200 wu-rounded-lg">
				<svg class="wu-animate-spin wu-mr-2 wu-h-4 wu-w-4" fill="none" viewBox="0 0 24 24">
					<circle class="wu-opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
					<path class="wu-opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
				</svg>
				<?php esc_html_e('Loading templates...', 'ultimate-multisite'); ?>
			</div>
		</div>

		<div class="wu-grid wu-grid-cols-1 md:wu-grid-cols-2 lg:wu-grid-cols-3 wu-gap-6" v-cloak>
			<div
				v-for="template in templates_list"
				:key="template.slug"
				class="wu-bg-white wu-border wu-border-gray-200 wu-rounded-lg wu-shadow-sm wu-overflow-hidden wu-transition-shadow hover:wu-shadow-md"
				:data-slug="template.slug"
			>
				<!-- Template Thumbnail -->
				<div class="wu-relative wu-aspect-video wu-bg-gray-100">
					<img
						v-if="template.images && template.images.length > 0"
						:src="template.images[0].thumbnail || template.images[0].src"
						:alt="template.name"
						class="wu-w-full wu-h-full wu-object-cover"
					>
					<div v-else class="wu-flex wu-items-center wu-justify-center wu-w-full wu-h-full wu-text-4xl wu-text-gray-400">
						<span class="dashicons dashicons-layout"></span>
					</div>

					<!-- Status Badge -->
					<div v-if="template.installed" class="wu-absolute wu-top-3 wu-right-3 wu-px-2 wu-py-1 wu-text-xs wu-font-semibold wu-text-white wu-bg-green-600 wu-rounded">
						<?php esc_html_e('Installed', 'ultimate-multisite'); ?>
					</div>

					<!-- Version Badge -->
					<div v-if="template.template_version" class="wu-absolute wu-bottom-3 wu-left-3 wu-px-2 wu-py-1 wu-text-xs wu-font-medium wu-text-gray-700 wu-bg-white wu-bg-opacity-90 wu-rounded">
						v{{ template.template_version }}
					</div>

					<!-- Industry Type Badge -->
					<div v-if="template.industry_type" class="wu-absolute wu-top-3 wu-left-3 wu-px-2 wu-py-1 wu-text-xs wu-font-medium wu-text-white wu-bg-blue-600 wu-rounded">
						{{ template.industry_type }}
					</div>
				</div>

				<div class="wu-p-4">
					<!-- Template Name and Author -->
					<h3 class="wu-text-lg wu-font-semibold wu-text-gray-900 wu-mb-1">{{ template.name }}</h3>
					<p class="wu-text-sm wu-text-gray-600 wu-mb-3">
						<?php esc_html_e('By', 'ultimate-multisite'); ?> <span class="wu-font-medium">{{ template.author }}</span>
					</p>

					<!-- Short Description -->
					<div class="wu-text-sm wu-text-gray-600 wu-mb-3 wu-line-clamp-2" v-html="template.short_description"></div>

					<!-- Template Meta -->
					<div class="wu-flex wu-items-center wu-flex-wrap wu-gap-3 wu-text-xs wu-text-gray-500 wu-mb-3">
						<span v-if="template.page_count" class="wu-flex wu-items-center">
							<span class="dashicons dashicons-admin-page wu-mr-1 wu-text-gray-400" style="font-size: 14px; width: 14px; height: 14px;"></span>
							{{ template.page_count }} <?php esc_html_e('pages', 'ultimate-multisite'); ?>
						</span>
						<span v-if="template.included_plugins && template.included_plugins.length" class="wu-flex wu-items-center">
							<span class="dashicons dashicons-admin-plugins wu-mr-1 wu-text-gray-400" style="font-size: 14px; width: 14px; height: 14px;"></span>
							{{ template.included_plugins.length }} <?php esc_html_e('plugins', 'ultimate-multisite'); ?>
						</span>
						<span v-if="template.included_themes && template.included_themes.length" class="wu-flex wu-items-center">
							<span class="dashicons dashicons-admin-appearance wu-mr-1 wu-text-gray-400" style="font-size: 14px; width: 14px; height: 14px;"></span>
							{{ template.included_themes[0].name || template.included_themes[0] }}
						</span>
					</div>
				</div>

				<!-- Actions Footer -->
				<div class="wu-px-4 wu-py-4 wu-bg-gray-50 wu-border-t wu-border-gray-200">
					<div class="wu-flex wu-items-center wu-justify-between wu-space-x-3">
						<div>
							<span v-if="template.is_free" class="wu-inline-flex wu-items-center wu-px-2 wu-py-1 wu-text-xs wu-font-medium wu-text-green-800 wu-bg-green-100 wu-rounded">
								<?php esc_html_e('Free', 'ultimate-multisite'); ?>
							</span>
							<span v-else class="wu-inline-flex wu-items-center wu-px-2 wu-py-1 wu-text-xs wu-font-medium wu-text-blue-800 wu-bg-blue-100 wu-rounded">
								<span v-html="template.price_html"></span>
							</span>
						</div>

						<!-- Demo Button -->
						<a
							v-if="template.demo_url"
							:href="template.demo_url"
							target="_blank"
							class="wu-px-3 wu-py-2 wu-text-sm wu-font-medium wu-text-gray-600 wu-bg-white wu-border wu-border-gray-300 wu-rounded-md hover:wu-bg-gray-50 wu-transition-colors wu-no-underline"
							:aria-label="'<?php esc_attr_e('Preview', 'ultimate-multisite'); ?> ' + template.name"
						>
							<span class="dashicons dashicons-external wu-mr-1" style="font-size: 14px; width: 14px; height: 14px; line-height: 20px;"></span>
							<?php esc_html_e('Demo', 'ultimate-multisite'); ?>
						</a>

						<!-- Install Button -->
						<button
							v-if="template.installed"
							type="button"
							class="wu-px-4 wu-py-2 wu-text-sm wu-font-medium wu-text-gray-500 wu-bg-gray-100 wu-border wu-border-gray-300 wu-rounded-md wu-cursor-not-allowed"
							disabled
						>
							<span class="dashicons-wu-check wu-mr-1"></span>
							<?php esc_html_e('Installed', 'ultimate-multisite'); ?>
						</button>
						<a
							v-else-if="template.download_url"
							:href="'<?php echo esc_attr($more_info_url); ?>'.replace('TEMPLATE_SLUG', template.slug)"
							class="wubox button-primary wu-inline-flex wu-items-center wu-justify-center wu-px-4 wu-py-2 wu-text-sm wu-font-medium wu-text-white wu-bg-blue-600 wu-border wu-border-blue-600 wu-rounded-md hover:wu-bg-blue-700 wu-transition-colors wu-no-underline"
							:data-title="'Install ' + template.name"
						>
							<?php esc_html_e('Install', 'ultimate-multisite'); ?>
						</a>
						<a
							v-else-if="!template.is_free"
							:href="template.permalink"
							class="wu-inline-flex wu-items-center wu-justify-center wu-px-4 wu-py-2 wu-text-sm wu-font-medium wu-bg-gray-300 wu-border wu-border-green-600 wu-rounded-md hover:wu-bg-green-700 wu-transition-colors wu-no-underline"
							target="_blank"
						>
							<?php esc_html_e('Buy Now', 'ultimate-multisite'); ?>
						</a>

						<!-- Details Button -->
						<a
							:href="'<?php echo esc_attr($more_info_url); ?>'.replace('TEMPLATE_SLUG', template.slug)"
							class="wubox wu-px-3 wu-py-2 wu-text-sm wu-font-medium wu-text-blue-600 wu-bg-white wu-border wu-border-blue-600 wu-rounded-md hover:wu-bg-blue-50 wu-transition-colors wu-no-underline"
							:aria-label="'<?php esc_attr_e('More information about', 'ultimate-multisite'); ?> ' + template.name"
							:data-title="template.name"
						>
							<?php esc_html_e('Details', 'ultimate-multisite'); ?>
						</a>
					</div>

				</div>
			</div>
		</div>

		<div
			v-cloak
			v-if="!loading && templates_list.length === 0"
			class="wu-text-center wu-py-12"
		>
			<div class="wu-max-w-md wu-mx-auto">
				<div class="wu-text-6xl wu-text-gray-400 wu-mb-4">
					<span class="dashicons dashicons-search"></span>
				</div>
				<h3 class="wu-text-lg wu-font-medium wu-text-gray-900 wu-mb-2"><?php esc_html_e('No templates found...', 'ultimate-multisite'); ?></h3>
				<p class="wu-text-sm wu-text-gray-600"><?php esc_html_e('Check the search terms or navigate between categories to see what templates we have available.', 'ultimate-multisite'); ?></p>
			</div>
		</div>

	</div>

	<?php
	/**
	 * Allow plugin developers to add scripts to the bottom of the page
	 *
	 * @since 2.5.0
	 * @param WU_Page $page WP Ultimo Page instance
	 */
	do_action('wu_page_template_library_footer', $page);
	?>

</div>
