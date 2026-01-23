<?php
/**
 * Magic Link URL Element Template
 *
 * Renders a magic link URL as a link, button, or plain URL text.
 *
 * @since 2.0.0
 *
 * @var string                   $magic_link_url The generated magic link URL.
 * @var \WP_Ultimo\Models\Site   $site           The site object.
 * @var string                   $display_as     How to display: 'anchor', 'button', or 'url'.
 * @var string                   $link_text      The text for link/button display.
 * @var int                      $open_in_new_tab Whether to open in new tab.
 * @var string                   $className      Additional CSS classes.
 */
defined('ABSPATH') || exit;

$target_attr = ! empty($open_in_new_tab) ? 'target="_blank" rel="noopener noreferrer"' : '';
?>
<?php if ('url' === $display_as) : ?>

	<?php echo esc_url($magic_link_url); ?>

<?php else : ?>

	<div class="wu-styling wu-magic-link-url-element <?php echo esc_attr($className ?? ''); // phpcs:ignore WordPress.NamingConventions.ValidVariableName.VariableNotSnakeCase ?>">

		<?php if ('button' === $display_as) : ?>

			<a
				href="<?php echo esc_url($magic_link_url); ?>"
				class="wu-magic-link-button button button-primary"
				<?php echo $target_attr; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
			>
				<?php echo esc_html($link_text); ?>
			</a>

		<?php else : ?>

			<a
				href="<?php echo esc_url($magic_link_url); ?>"
				class="wu-magic-link-anchor"
				<?php echo $target_attr; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
			>
				<?php echo esc_html($link_text); ?>
			</a>

		<?php endif; ?>

	</div>

<?php endif; ?>
