<?php
/**
 * Placeholders
 *
 * @since 2.0.0
 */
?>
<div id="wu-template-placeholders" class="<?php wu_wrap_use_container(); ?> wrap wp-ultimo">

	<h1 class="wp-heading-inline">

		<?php esc_html_e('Template Placeholders', 'multisite-ultimate'); ?>

	</h1>

	<p class="description"></p>

	<hr class="wp-header-end">

	<div class="wu-advanced-filters">

		<form id="posts-filter" method="get">

			<div class="tablenav">

				<div class="tablenav-pages one-page">

			<span v-cloak class="displaying-num">

			{{data.placeholders.length}} <?php esc_html_e('item(s)', 'multisite-ultimate'); ?>

			</span>

				</div>

				<br class="clear">
			</div>

			<table class="wp-list-table widefat fixed striped">

				<thead>

				<tr>

					<th id="cb" class="manage-column column-cb" style="width: 50px;">

						<label class="screen-reader-text" for="wu-select-2">
							<?php esc_attr_e('Select All', 'multisite-ultimate'); ?>
						</label>

						<input v-bind:disabled="!data.placeholders" v-model="toggle" v-on:click="select_all" id="wu-select-2"
								type="checkbox">

					</th>

					<?php foreach ($columns as $key => $label) : ?>

					<th scope="col" id="<?php echo esc_attr($key); ?>" class="manage-column sortable asc column-<?php echo esc_attr($key); ?>">
						<?php echo esc_html($label); ?>
					</th>

					<?php endforeach; ?>

				</tr>

				</thead>

				<tbody id="the-list">

				<tr v-if="loading && !data.placeholders.length" class="wu-text-center">

					<td colspan="<?php echo count($columns) + 1; ?>">

						<div class="wu-p-4">

							<?php esc_html_e('Loading Template Placeholders...', 'multisite-ultimate'); ?>

						</div>

					</td>

				</tr>

				<tr v-cloak v-if="!loading && !data.placeholders.length" class="wu-text-center">

					<td colspan="<?php echo count($columns) + 1; ?>">

						<div class="wu-p-4">

							<?php esc_html_e('No items to display', 'multisite-ultimate'); ?>

						</div>

					</td>

				</tr>

				<tr v-if="data" v-cloak v-for="item in data.placeholders" :id="'tax-rate' + item.id" v-bind:class="{selected: item.selected}">

					<th scope="row" class="check-column">

						<label class="screen-reader-text" for="wu-select-1">

							<?php esc_html_e('Select', 'multisite-ultimate'); ?> {{item.title}}

						</label>

						<input type="checkbox" v-model="item.selected">

					</th>

					<?php foreach ($columns as $key => $label) : ?>

					<td class="date column-<?php echo esc_attr($key); ?>" data-colname="<?php echo esc_attr($key); ?>">

						<?php

						/**
						 * Switch for some of the fields
						 */
						switch ($key) :
							case 'compound':
								?>

								<input type="checkbox" v-model="item.compound">

								<?php
								break;
							case 'placeholder':
								?>

								<input
								class="wu-bg-transparent wu-p-4 wu-border-none wu-w-full hover:wu-bg-gray-200 hover:wu-border hover:wu-border-solid hover:wu-border-gray-400 hover:wu-cursor-pointer"
								name="" placeholder="e.g. placeholder" v-on:input="item.<?php echo esc_attr($key); ?> = $event.target.value.toLowerCase().replace(/[^a-z0-9-_]+/g, '')" v-bind:value= "item.<?php echo esc_attr($key); ?>">

								<?php
								break;
							case 'content':
								?>

								<textarea
								class="wu-bg-transparent wu-p-4 wu-m-0 wu-border-none wu-w-full wu-float-left hover:wu-bg-gray-200 hover:wu-border hover:wu-border-solid hover:wu-border-gray-400 hover:wu-cursor-pointer"
								name="" placeholder="e.g. Content" v-model="item.<?php echo esc_attr($key); ?>" rows="1"></textarea>

								<?php
								break;
							default:
								?>

								<input
										class="wu-bg-transparent wu-p-4 wu-border-none wu-w-full hover:wu-bg-gray-200 hover:wu-border hover:wu-border-solid hover:wu-border-gray-400 hover:wu-cursor-pointer"
										name="" placeholder="*" v-model="item.<?php echo esc_attr($key); ?>">

								<?php
								break;
							endswitch;
						?>

						</td>

					<?php endforeach; ?>

				</tr>

				</tbody>

				<tfoot>

				<tr>

					<th id="cb" class="manage-column column-cb">

						<label class="screen-reader-text" for="wu-select">


							<?php esc_html_e('Select All', 'multisite-ultimate'); ?>

						</label>

						<input v-bind:disabled="!data.placeholders.length" v-model="toggle" v-on:click="select_all" id="wu-select"
								type="checkbox">

					</th>

					<?php foreach ($columns as $key => $label) : ?>

						<th scope="col" id="<?php echo esc_attr($key); ?>" class="manage-column sortable asc column-<?php echo esc_attr($key); ?>">

							<?php echo esc_html($label); ?>

						</th>

					<?php endforeach; ?>

				</tr>

				</tfoot>

			</table>
	</div>

	<div class="tablenav bottom wu-bg-gray-100 wu-p-4" v-cloak v-show="!creating">

		<div class="alignleft actions">

			<button v-on:click.prevent="add_row" class="button">

				<?php esc_html_e('Add new Row', 'multisite-ultimate'); ?>

			</button>

			<button v-on:click.prevent="delete_rows" class="button">

				<?php esc_html_e('Delete Selected Rows', 'multisite-ultimate'); ?>

			</button>

		</div>

		<div class="alignleft actions">

			<?php

			/**
			 * Let developers print additional buttons to this screen
			 * Our very on EU VAT functions hook on this to display our VAT helper button
			 *
			 * @since 2.0.0
			 */
			do_action('wu_edit_placeholders_screen_additional_actions');

			?>

		</div>

		<div class="alignright actions">

		<span v-if="changed && !saveMessage && !saving" class="description"
				style="display: inline-block; line-height: 28px; margin-right: 10px;">
		<?php esc_html_e('Save your changes!', 'multisite-ultimate'); ?>
		</span>

			<span v-if="saving" class="description" style="display: inline-block; line-height: 28px; margin-right: 10px;">
		<?php esc_html_e('Saving...', 'multisite-ultimate'); ?>
		</span>

			<span v-if="saveMessage" class="description"
					style="display: inline-block; line-height: 28px; margin-right: 10px;">
		{{saveMessage}}
		</span>

			<button v-on:click.prevent="save" v-bind:disabled="saving" class="button button-primary">
				<?php esc_html_e('Save Template Placeholders', 'multisite-ultimate'); ?>
			</button>
		</div>

		<br class="clear">

		</form>

	</div>

	<form id="nonce_form">

		<?php wp_nonce_field('wu_edit_placeholders_editing'); ?>

	</form>

	<br class="clear">

</div>
