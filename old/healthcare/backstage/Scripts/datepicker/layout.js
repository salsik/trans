(function($){
	var initLayout = function() {
		var hash = window.location.hash.replace('#', '');
		var currentTab = $('ul.navigationTabs a')
							.bind('click', showTab)
							.filter('a[rel=' + hash + ']');
		if (currentTab.size() == 0) {
			currentTab = $('ul.navigationTabs a:first');
		}
		showTab.apply(currentTab.get(0));
		$('#date').DatePicker({
			flat: true,
			date: '2008-07-31',
			current: '2008-07-31',
			calendars: 1,
			starts: 1,
			view: 'years'
		});
		var now = new Date();
		now.addDays(-10);
		var now2 = new Date();
		now2.addDays(-5);
		now2.setHours(0,0,0,0);


		$('.inputDate').DatePicker({
			format:'Y-m-d',
			date: $('#inputDate').val(),
			current: $('#inputDate').val(),
			starts: 1,
			position: 'right',
			onBeforeShow: function(){
				$('#inputDate').DatePickerSetDate($('#inputDate').val(), true);
			},
			onChange: function(formated, dates){
				$('#inputDate').val(formated);$('#inputDate').DatePickerHide();
			}
		});
		var now3 = new Date();
		now3.addDays(-4);
		var now4 = new Date()
		$('#widgetCalendar').DatePicker({});
		var state = false;
		$('#widgetField>a').bind('click', function(){});
		$('#widgetCalendar div.datepicker').css('position', 'absolute');
	};
	
	var showTab = function(e) {};
	
	EYE.register(initLayout, 'init');
})(jQuery)