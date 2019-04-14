(function($) {


	$.fn.infiniScroll = function(method) {

		var $this = $(this).get(0);
		var busy = false;
		var options = {};
		var settings = {
			'vars': {},
			'url' : false,
			'before' : $.noop,
			'after' : $.noop
		};

		settings.active = false;

		// Method calling logic
		if (typeof method === 'object' || !method) {
			options = $.extend({}, settings, method);
			options.active = true;

			if(typeof options.vars != 'object') {
				options.vars = {};
			}
//			_checkLevel();
//			$(window).scroll(function(){
//				console.log('ss');
//				if (options.active && !busy ) {
//					_checkLevel();
//				}
//			});
			interval = setInterval(function(){
				if (options.active && !busy && $($this).is(':visible') ) {
					_checkLevel();
				}
			}, 1000);
		} else {
			var Msg = 'Method ' + method
			+ ' does not exist on jQuery.infiniScroll';
			$.error(Msg);
			console.log(Msg);
		}


		function _checkLevel() {
			// if it's low enough, grab latest data
			if (_levelReached()) {

				var url = '';
				if (typeof options.url == 'function') {
					url = options.url(this, options);
				} else if (typeof options.url == 'string') {
					url = options.url;
				}
				if (url) {

					if (typeof options.before == 'function') {
						options.before(options);
					}

					busy = true;
					$.get(url, options.vars, function(data) {
						if (typeof options.after == 'function') {
							options.after(data, options);
						}

						busy = false;
					}, 'json');
				}
			}
		};

		function _levelReached() {
			// is it low enough to add elements to bottom?

			var endOfElement = $($this).outerHeight() + $($this).offset().top;

			var viewportHeight = window.innerHeight
					|| document.documentElement.clientHeight
					|| document.body.clientHeight || 0;

//			console.log( (endOfElement - viewportHeight) );
			return endOfElement - viewportHeight < 30;
		};

	}

})(jQuery);
