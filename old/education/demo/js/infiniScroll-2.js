(function($) {

	$.fn.infiniScroll = function(method, opt) {

		var $this = this;
		var section = {};
		var section_current = '';
		var settings = {
			'vars': {},
			'url' : false,
			'before' : $.noop,
			'after' : $.noop
		};

		settings.active = false;

		// Method calling logic
		if (method == 'section') {
			section_current = opt;
		} else if (typeof opt === 'object' || !opt) {
			section_current = method;
			section[section_current] = $.extend({}, settings, method);
			section[section_current].active = true;
			section[section_current].busy = false;

			if(typeof section[section_current].vars != 'object') {
				section[section_current].vars = {};
			}
			interval = setInterval(function(){
				if (section[section_current] && section[section_current].active && !section[section_current].busy ) {
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
				if (typeof section[section_current].url == 'function') {
					url = section[section_current].url(this, section[section_current]);
				} else if (typeof section[section_current].url == 'string') {
					url = section[section_current].url;
				}
				if (url) {

					if (typeof section[section_current].before == 'function') {
						section[section_current].before(section[section_current]);
					}

					section[section_current].busy = true;
					$.get(url, section[section_current].vars, function(data) {
						if (typeof section[section_current].after == 'function') {
							section[section_current].after(data, section[section_current]);
						}

						section[section_current].busy = false;
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
