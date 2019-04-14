<?php 

if( !defined('_auto_complete.php')) {
	define('_auto_complete.php', true);

?>
	<link rel="stylesheet" type="text/css" href="css/jquery.autocomplete.css" />
	<link rel="stylesheet" type="text/css" href="css/thickbox.css" />
<!--<script src="http://ajax.googleapis.com/ajax/libs/jquery/1.7.1/jquery.js"></script>-->
	<script type='text/javascript' src='js/jquery.bgiframe.min.js'></script>
	<script type='text/javascript' src='js/jquery.ajaxQueue.js'></script>
	<script type='text/javascript' src='js/thickbox-compressed.js'></script>
	<script type='text/javascript' src='js/jquery.autocomplete.js'></script>


<script type="text/javascript">
<!--
$(document).ready(function(){

	function showAutoCompleteInput(Element) {
		$(Element).parents('.autocomplete:first').find(".name:first").hide();
		$(Element).parents('.autocomplete:first').find(".auto_name:first").css('display', 'inline');
	}
	$('.autocomplete .auto_name').keypress(function(e){
		if( e.which == 0 ) {
			if($(this).siblings(".name:first").html() ) {
				$(this).siblings(".name:first").css('display', 'inline');
				$(this).hide();
				return false; 
			}
		}
		if( e.which == 32 || e.which == 8 || e.which == 0) return true; // e.which == 13 ||
		if(48 <= e.which && e.which <= 57) return true;
		if(65 <= e.which && e.which <= 90) return true;
		if(97 <= e.which && e.which <= 122) return true;
		return false; 
	});
	$('.autocomplete .change').live('click', function(e){
		showAutoCompleteInput(this);
		return false; 
	});
	$('.autocomplete .remove').live('click', function(e){
		var $autocomplete = $(this).parents('.autocomplete:first');
		showAutoCompleteInput(this);
		$autocomplete.find(".auto_name:first").val('');
		$autocomplete.find(".auto_id:first").val('');

		if( $autocomplete.data('remove') ) {
			if( typeof window[ $autocomplete.data('remove') ] == 'function') {
				window[ $autocomplete.data('remove') ]($autocomplete);
			}
		}
		return false; 
	});


	$('.autocomplete').each(function(){
		$('<span style="display: inline" class="name"></span>').hide().prependTo(this);
		if( $(".auto_id", this).val() ) {
			setAutocompleteName($(this), $(".auto_id", this).val(), $(".auto_name", this).val());

			if( $(this).data('load') ) {
				if( typeof window[ $(this).data('load') ] == 'function') {
					window[ $(this).data('load') ]($(this));
				}
			}
		}
		ac_link = ($(this).data('link')) ? $(this).data('link') : "<?php echo _auto_complete_file; ?>";

		$(".auto_name", this).autocomplete( ac_link, {
			dynamicLink: function($input, url){
				var $autocomplete = $input.parents('.autocomplete:first');
			
				if( $autocomplete.data('get') ) {
					if( typeof window[ $autocomplete.data('get') ] == 'function') {
						return window[ $autocomplete.data('get') ]($autocomplete, url );
					}
				}
				return url;
			},
			width: 320,
			max: 10,
			highlight: false,
			scroll: true,
			scrollHeight: 300,
			formatItem: function(data, i, n, value) {
				return "<b>" + value +'</b>';
			},
			formatResult: function(data, value) {
				return value.split(".")[0];
			},
			selectFirst: false,
			onSelect: function( $input, data ) {
				var $autocomplete = $input.parents('.autocomplete:first');
				setAutocompleteName($autocomplete, data[1], data[0]);

				if( $autocomplete.data('select') ) {
					if( typeof window[ $autocomplete.data('select') ] == 'function') {
						window[ $autocomplete.data('select') ]($autocomplete, data);
					}
				}
			}
		});

	});

	$('.auto-complete').each(function(){

		if( !$(this).data('link')) {
			return false;
		}
		ac_link = $(this).data('link');
		var $that = ($(this).is('input') ) ? $(this) : $("input[type='text']:first", this);

//		$(".auto_name", this).autocomplete( ac_link, {
		$that.autocomplete( ac_link, {
			width: 320,
			max: 10,
			highlight: false,
			scroll: true,
			scrollHeight: 300,
			formatItem: function(data, i, n, value) {
				return "<b>" + value +'</b>';
			},
			formatResult: function(data, value) {
				return value.split(".")[0];
			},
			selectFirst: false,
			onSelect: function( $input, data ) {
				if( $input.hasClass('auto-complete') ) {
					var $autocomplete = $input;
				} else {
					var $autocomplete = $input.parents('.auto-complete:first');
				}

				if( $autocomplete.data('select') ) {
					if( typeof window[ $autocomplete.data('select') ] == 'function') {
						window[ $autocomplete.data('select') ]($autocomplete, data);
					}
				}
			}
		});
	});
});


function setAutocompleteName(Selector, id, name, changeName) {
	Selector.find(".auto_name:first").hide();
	Selector.find(".auto_id:first").val(id);
	if( changeName ) {
		Selector.find(".auto_name:first").val( name );
	}
	var Html = '';
	Html += name;
	Html += ' (<a href="#" class="change" onclick="return false;">Change</a>';
	Html += '-<a href="#" class="remove" onclick="return false;">Remove</a>)';
	Selector.find(".name:first").html( Html ).css('display', 'inline');
}

//-->
</script>
<?php
}