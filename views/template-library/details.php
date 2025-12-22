<?php
/**
 * Template details modal.
 *
 * @since 2.5.0
 */

if (! defined('ABSPATH')) {
	exit; // Exit if accessed directly
}

?>

<div id="plugin-information" style="position: static">

	<div id="plugin-information-scrollable">

		<div id="plugin-information-title" class="with-banner"
		<?php
		if (isset($template->images[0]['thumbnail'])) :
			?>
style="background-image:url(<?php echo esc_url($template->images[0]['thumbnail']); ?>);background-position:center;"<?php endif; ?>>
			<div class="vignette"></div>
			<h2><?php echo esc_html($template->name); ?></h2>
		</div>

		<div id="plugin-information-tabs" class="with-banner">

			<a name="description" href="#" class="current">

				<?php esc_html_e('Description', 'ultimate-multisite'); ?>

			</a>

		</div>

		<div id="plugin-information-content" class="with-banner">

			<div class="fyi">

				<ul>
					<li>
						<strong><?php esc_html_e('Author:', 'ultimate-multisite'); ?></strong>
						<?php echo esc_html($template->author ?? 'Ultimate Multisite Team'); ?>
					</li>

					<?php if (! empty($template->template_version)) : ?>
					<li>
						<strong><?php esc_html_e('Version:', 'ultimate-multisite'); ?></strong>
						<?php echo esc_html($template->template_version); ?>
					</li>
					<?php endif; ?>

					<?php if (! empty($template->industry_type)) : ?>
					<li>
						<strong><?php esc_html_e('Industry:', 'ultimate-multisite'); ?></strong>
						<?php echo esc_html($template->industry_type); ?>
					</li>
					<?php endif; ?>

					<?php if (! empty($template->page_count)) : ?>
					<li>
						<strong><?php esc_html_e('Pages:', 'ultimate-multisite'); ?></strong>
						<?php echo esc_html($template->page_count); ?>
					</li>
					<?php endif; ?>

					<?php if (! empty($template->included_plugins) && is_array($template->included_plugins)) : ?>
					<li>
						<strong><?php esc_html_e('Plugins Included:', 'ultimate-multisite'); ?></strong>
						<ul class="wu-ml-4 wu-mt-1">
							<?php foreach ($template->included_plugins as $plugin) : ?>
								<li class="wu-text-sm">
									<?php
									$plugin_name     = is_array($plugin) ? ($plugin['name'] ?? $plugin['slug'] ?? '') : $plugin;
									$plugin_required = is_array($plugin) && ! empty($plugin['required']);
									echo esc_html($plugin_name);
									if ($plugin_required) :
										?>
										<span class="wu-text-xs wu-text-red-600"><?php esc_html_e('(Required)', 'ultimate-multisite'); ?></span>
									<?php endif; ?>
								</li>
							<?php endforeach; ?>
						</ul>
					</li>
					<?php endif; ?>

					<?php if (! empty($template->included_themes) && is_array($template->included_themes)) : ?>
					<li>
						<strong><?php esc_html_e('Theme:', 'ultimate-multisite'); ?></strong>
						<?php
						$theme = $template->included_themes[0] ?? [];
						echo esc_html(is_array($theme) ? ($theme['name'] ?? '') : $theme);
						?>
					</li>
					<?php endif; ?>

					<?php if (! empty($template->compatibility['wu_version'])) : ?>
					<li>
						<strong><?php esc_html_e('Requires Ultimate Multisite:', 'ultimate-multisite'); ?></strong>
						<?php
						// translators: %s minimum required version number.
						printf(esc_html__('%s or higher', 'ultimate-multisite'), esc_html($template->compatibility['wu_version']));
						?>
					</li>
					<?php endif; ?>

					<?php if (! empty($template->compatibility['wp_version'])) : ?>
					<li>
						<strong><?php esc_html_e('Requires WordPress:', 'ultimate-multisite'); ?></strong>
						<?php
						// translators: %s minimum required version number.
						printf(esc_html__('%s or higher', 'ultimate-multisite'), esc_html($template->compatibility['wp_version']));
						?>
					</li>
					<?php endif; ?>

					<?php if (! empty($template->demo_url)) : ?>
					<li>
						<a class="wu-no-underline" target="_blank" href="<?php echo esc_url($template->demo_url); ?>">
							<?php esc_html_e('View Live Demo »', 'ultimate-multisite'); ?>
						</a>
					</li>
					<?php endif; ?>

					<?php if (! empty($template->permalink)) : ?>
					<li>
						<a class="wu-no-underline" target="_blank" href="<?php echo esc_url($template->permalink); ?>">
							<?php esc_html_e('See on the Official Site »', 'ultimate-multisite'); ?>
						</a>
					</li>
					<?php endif; ?>

				</ul>
			</div>
			<div id="section-holder">

				<!-- Description Section -->
				<div id="section-description" class="section" style="display: block; min-height: 200px;">

					<?php echo wp_kses_post($template->description); ?>

				</div>

			</div>

		</div>

	</div>

	<div id="plugin-information-footer" style="height: auto !important;">

		<?php if (! empty($template->is_free) && $template->is_free) : ?>

		<span class="wu-text-green-800 wu-inline-block wu-py-1">

			<?php esc_html_e('This is a Free Template.', 'ultimate-multisite'); ?>

		</span>

		<?php elseif (! empty($template->prices['price']) && $template->prices['price'] > 0) : ?>

		<span class="wu-text-blue-800 wu-inline-block wu-py-1">

			<?php esc_html_e('This is a Premium Template.', 'ultimate-multisite'); ?>

		</span>

		<?php endif; ?>

		<?php if (! empty($template->installed) && $template->installed) : ?>

			<button
			disabled="disabled"
			data-slug="<?php echo esc_attr($template_slug); ?>"
			class="button button-disabled right"
			>
			<?php esc_html_e('Already Installed', 'ultimate-multisite'); ?>
			</button>

		<?php else : ?>

			<?php if (! empty($template->download_url)) : ?>

			<button
			type="submit"
			name="install"
			data-slug="<?php echo esc_attr($template_slug); ?>"
			class="button button-primary right"
			>
				<?php esc_html_e('Install Now', 'ultimate-multisite'); ?>
			</button>

			<?php elseif (! empty($template->permalink)) : ?>

			<a
			href="<?php echo esc_url($template->permalink); ?>"
			target="_blank"
			class="button button-primary right"
			>
				<?php esc_html_e('Get Template', 'ultimate-multisite'); ?>
			</a>

			<?php endif; ?>

			<input type="hidden" name="action" value="wu_form_handler">

			<input type="hidden" name="template" value="<?php echo esc_attr($template_slug); ?>">

			<?php wp_nonce_field('wu_form_template_more_info'); ?>

		<?php endif; ?>

	</div>

</div>
