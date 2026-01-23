/* global wu_template_switching_params, Vue, wu_create_cookie, wu_listen_to_cookie_change */
(function($, hooks) {

	/*
 * Sets up the cookie listener for template selection.
 */
	hooks.addAction('wu_checkout_loaded', 'nextpress/wp-ultimo', function() {

		/*
     * Resets the template selection cookie.
     */
		wu_create_cookie('wu_template', false);

		/*
     * Listens for changes and set the template if one is detected.
     */
		wu_listen_to_cookie_change('wu_template', function(value) {

			window.wu_template_switching.template_id = value;

		});

	});

	$(document).ready(function() {

		const dynamic = {
			functional: true,
			template: '#dynamic',
			props: [ 'template' ],
			render(h, context) {

				const template = context.props.template;

				const component = template ? { template } : '<div>nbsp;</div>';

				return h(component);

			},
		};

		hooks.doAction('wu_checkout_loaded');

		// eslint-disable-next-line no-unused-vars
		window.wu_template_switching = new Vue({
			el: '#wp-ultimo-form-wu-template-switching-form',
			data() {

				return {
					template_id: 0,
					original_template_id: -1,
					template_category: '',
					stored_templates: {},
					confirm_switch: 0,
					ready: false,
				};

			},
			directives: {
				init: {
					bind(el, binding, vnode) {

						vnode.context[ binding.arg ] = binding.value;

					},
				},
			},
			components: {
				dynamic,
			},
			watch: {
				ready() {

					const that = this;

					if (that.ready !== false) {

						that.switch_template();

					} // end if;

				},
			},
			methods: {
				get_template(template, data) {

					if (typeof data.id === 'undefined') {

						data.id = 'default';

					} // end if;

					const template_name = template + '/' + data.id;

					if (typeof this.stored_templates[ template_name ] !== 'undefined') {

						return this.stored_templates[ template_name ];

					} // end if;

					const template_data = {
						duration: this.duration,
						duration_unit: this.duration_unit,
						products: this.products,
						...data,
					};

					this.fetch_template(template, template_data);

					return '<div class="wu-p-4 wu-bg-gray-100 wu-text-center wu-rounded">Loading</div>';

				},
				fetch_template(template, data) {

					const that = this;

					if (typeof data.id === 'undefined') {

						data.id = 'default';

					} // end if;

					this.request('wu_render_field_template', {
						template,
						attributes: data,
					}, function(results) {

						const template_name = template + '/' + data.id;

						if (results.success) {

							Vue.set(that.stored_templates, template_name, results.data.html);

						} else {

							Vue.set(that.stored_templates, template_name, '<div>' + results.data[ 0 ].message + '</div>');

						} // end if;

					});

				},
				switch_template() {

					const that = this;

					that.block();

					this.request('wu_switch_template', {
						template_id: that.template_id,
					}, function(results) {

						/*
             * Handle error responses.
             */
						if (results.success === false) {

							that.unblock();

							that.confirm_switch = false;

							that.ready = false;

							let errorMessage = 'An error occurred while switching templates.';

							if (results.data && results.data.message) {

								errorMessage = results.data.message;

							} else if (results.data && Array.isArray(results.data) && results.data[ 0 ] && results.data[ 0 ].message) {

								errorMessage = results.data[ 0 ].message;

							}

							// eslint-disable-next-line no-alert
							alert(errorMessage);

							return;

						}

						/*
             * Redirect of we get a redirect URL back.
             */
						if (typeof results.data.redirect_url === 'string') {

							window.location.href = results.data.redirect_url;

						} // end if;

					}, function() {

						/*
             * Handle network errors.
             */
						that.unblock();

						that.confirm_switch = false;

						that.ready = false;

						// eslint-disable-next-line no-alert
						alert('A network error occurred. Please try again.');

					});

				},
				block() {

					/*
           * Get the first bg color from a parent.
           */
					const bg_color = jQuery(this.$el).parents().filter(function() {

						return $(this).css('backgroundColor') !== 'rgba(0, 0, 0, 0)';

					}).first().css('backgroundColor');

					jQuery(this.$el).wu_block({
						message: '<div class="spinner is-active wu-float-none" style="float: none !important;"></div>',
						overlayCSS: {
							backgroundColor: bg_color ? bg_color : '#ffffff',
							opacity: 0.6,
						},
						css: {
							padding: 0,
							margin: 0,
							width: '50%',
							fontSize: '14px !important',
							top: '40%',
							left: '35%',
							textAlign: 'center',
							color: '#000',
							border: 'none',
							backgroundColor: 'none',
							cursor: 'wait',
						},
					});

				},
				unblock() {

					jQuery(this.$el).wu_unblock();

				},
				request(action, data, success_handler, error_handler) {

					jQuery.ajax({
						method: 'POST',
						url: wu_template_switching_params.ajaxurl + '&action=' + action,
						data,
						success: success_handler,
						error: error_handler,
					});

				},
			},
		});

	});

}(jQuery, wp.hooks));
