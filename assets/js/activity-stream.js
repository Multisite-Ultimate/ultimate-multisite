document.addEventListener('DOMContentLoaded', function() {
	Object.defineProperty(Vue.prototype, '$moment', {
		value: wu_moment
	});

	var wuActivityStream = new Vue({
		el: '#activity-stream-content',
		data: {
			count: 0,
			loading: true,
			page: 1,
			queried: [],
			error: false,
			errorMessage: "",
		},
		mounted: function() {
			this.pullQuery();
		},
		watch: {
			queried: function(value) {},
		},
		methods: {
			hasMore: function() {
				return this.queried.count > (this.page * 5)
			},
			refresh: function() {
				this.loading = true;
				this.pullQuery();
			},
			navigatePrev: function() {
				this.page = this.page <= 1 ? 1 : this.page - 1;
				this.loading = true;
				this.pullQuery();
			},
			navigateNext: function() {
				this.page = this.page + 1;
				this.loading = true;
				this.pullQuery();
			},
			pullQuery: function() {
				var that = this;
				jQuery.ajax({
					url: ajaxurl,
					data: {
						_ajax_nonce: wu_activity_stream_nonce,
						action: 'wu_fetch_activity',
						page: this.page,
					},
					success: function(data) {
						that.loading = false;
						Vue.set(wuActivityStream, 'loading', false);

						if (data.success) {
							Vue.set(wuActivityStream, 'queried', data.data);
						}
					},
				})
			},
			get_color_event: function(type) {},
		}
	});
});