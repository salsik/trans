$(document).ready( function(){

	$('<img src="images/cross.png" />').load();
	$('.bValidator').bValidator({
		singleError: true,
		offset:              {x:-22, y:22},
		position:            {x:'right', y:'top'},
		showCloseIcon:       false,
		classNamePrefix:     'ex6_',
		errorValidateOn: 	 null,
		errorMessages: { en: {
			required: '<img src="images/cross.png" />',
			number: '<img src="images/cross.png" />',
			email: '<img src="images/cross.png" />'
		}}
	});

	$('#LoginForm').submit(function(){
		if($(this).hasClass('sending')) return false;
		$(this).addClass('sending');
		var Form = this;
		var Parent = $(Form).parents('.login:first');
		var Loader = Parent.find('.loader');

		Loader.stop(true, true).fadeIn( 600 );
		$('.errorHolder', Parent).slideUp('fast');
		$.post('login.ajax.php', $(Form).serialize(), function( respond ){
			if( respond.status == 'ok')
			{
				window.location.href = './';
				return false;
			}
			$('.errors', Parent).html( respond.error );
			Loader.stop(true, true).fadeOut('', function(){
				$(Form).removeClass('sending');
				$('.errorHolder', Parent).slideDown('fast');
			});
		}, 'json');
		return false;
	});
	$('#ContactUs').submit(function(){
		if($(this).hasClass('sending')) return false;
		$(this).addClass('sending');
		var Form = this;
		var Parent = $(Form).parents('.contactus');
		var Loader = $(Form).parents('.contactus').find('.loader');
		
		Loader.stop(true, true).fadeIn( 600 );
		$.post('contact.ajax.php', $(Form).serialize(), function( respond ){
			if( respond.status == 'sent')
			{
				$('.msg', Loader).hide().html(respond.msg).fadeIn('slow');
				$('.img', Loader).fadeOut('slow');
				$(Form).animate({'opacity':0}, 500);
				return false;
			}
			Loader.stop(true, true).fadeOut('', function(){
				$(Form).removeClass('sending');
			});
		}, 'json');
		return false;
	});

	$(".gallery").fancybox({
		'transitionIn'	: 'elastic',
		'transitionOut'	: 'elastic'
	});
});
