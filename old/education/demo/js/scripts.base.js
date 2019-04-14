
var school_about_created = false;
var school_list_created = false;
var students_list_created = false;

var $bannerworking = false;
var $global = {};
var throttleTimeout;

$(document).ready(function(){
	_reloadScroolPanel();
});

$(document).ready(function(){
	_showAsLoggedin();
	_changeHolder('home');
//	if( window.location.hash ) {
//		var hash = window.location.hash.substring(1);
//		if( hash.match(/^[a-zA-Z0-9_-]+$/) ) {
//			_changeHolder(hash);
//		}
//	}
	
	_html_links('body');
});

function _html_links(Selector){
	$(Selector).find('a').each(function(){
		var link = this;
		
		if( $(link).attr('target') == '_blank' ) {
			return;
		}

		if( !$(link).attr('href') ) {
			return;
		}
		$(link).off('click');

		if( $(link).attr('href') == '#' ) {
			if( $(link).data('menu') ) {
				$(link).off('click', function(){
					var menu = $('#'+$(this).data('menu'));
					if( menu.length > 0 ) {
						if( menu.is(':visible') ) {
							menu.stop(true, true).slideUp('fast', function(){
								_reloadScroolPanel();
							});
						}
						else {
							menu.stop(true, true).slideDown('fast', function(){
								_reloadScroolPanel();
							});
						}
					}
				});
			}
			return;
		}

		$(link).on('click', function(){

			var thisLink = this;

			if( $(thisLink).attr('href').indexOf('#') !== 0 ) {
				return false;
			}

			var $goto = $(thisLink).attr('href').substring(1);
			if( ! $goto.match(/^[a-zA-Z0-9_-]+$/) ) {
				return false;
			}

			if($goto == 'logout') {
				_logout();
			} else {
				_changeHolder( $goto, thisLink);
			}
			return false;
		});
	});
}
function _changeHolder(holder, link, callback) {
//	_logs("_changeHolder", "Holder: " + holder);

	var Holder = $('#' + holder +'.holders');
	if( Holder.length < 1 ) {
		return false;
	}

//	_logs("_changeHolder", "Holder.length: " + Holder.length);

	$('.notification-list').removeClass('active');
	_showLoader( false );
	_loadBanners( Holder );
	$('.holders').hide();
	Holder.show();
	_menu._resetMenu();

	if( typeof callback == 'function') {
		callback(Holder);
	}

	var $callback = Holder.data('callback');
	if( $callback ) {
		if( typeof window[ $callback ] == 'function') {
			window[ $callback ](Holder, link);
		}
	}
}
function _loadBanners( holder, $class, $empty ) {
	
	var banners = [];
	var bannerTypes = {};
	var $i = 0;
	
	$class = ($class) ? $class : 'banner';
	holder.find('.' + $class).each(function(){
		if( $(this).data('banner') && ( !$empty || $(this).html() == '') ) { // && 
			banners['banner'+$i] = this;
			bannerTypes['banner'+$i] = $(this).data('banner');
			
			$i++;
		}
	});
	
	if($i < 1) {
		return ;
	}

	_api('get', 'banners', {types: bannerTypes}, function(response){
		if(response.error || !response.banners) {
			return false;
		}

		$.each(response.banners, function(i, v){
			if(banners[i]) {
				$(banners[i]).html(v.banner);

				banners['banner'+$i] = this;
				bannerTypes['banner'+$i] = $(this).data('banner');
			}
		});
	});
}

function _buildListingsBanners( Listings, isPrivate) {
	
	if( $bannerworking ) {
		return false;
	}
	$bannerworking = true;
	if( isPrivate ) {
		var bannerDom = '<div class="listings_banner listings_banner_private banner center" data-banner="listings_banner" ></div>';
	}
	else {
		var bannerDom = '<div class="listings_banner banner center" data-banner="listings_banner" ></div>';
	}

	var bannerEach = 3;
	var x = 0;

	Listings.children('div').each(function(){
		if( $(this).hasClass('item')) {
			x++;
			if( x > 0 && !(x%bannerEach) ) {
				$(bannerDom).insertBefore(this);
			}
		}
	});
	_loadBanners( Listings, 'listings_banner', true );
	$bannerworking = false;
}


function _showLoader( show, callback ) {
	if( show === false ) {
		$('#loader').stop(true, true).fadeOut( 600, function(){
			if(typeof callback == 'function') {
				callback();
			}
		});
	} else {
		$('#loader').stop(true, true).fadeIn( 600, function(){
			if(typeof callback == 'function') {
				callback();
			}
		});
	}
}

function _showAsLoggedin( callback ) {
	if( $loginKey ) {
		$('.isLoggedin').show();
		$('.isNotLoggedin').hide();
		$('#app_name').html( $account.full_name );
	} else {
		$('.isLoggedin').hide();
		$('.isNotLoggedin').show();
		$('#app_name').html('Sections');
	}
	if(typeof callback == 'function') {
		callback();
	}
}

function _jqueryObj(obj) {
	if(typeof obj == 'object') {
		if(typeof obj.nodeType != 'undefined') {
			return $(obj);
		} else if(typeof obj.jquery != 'undefined') {
			return obj;
		}
	}
	return false;
}

function is_object(obj) {
	if(typeof obj == 'object') {
		return true;
	}
	return false;
}


function _api(method, action, vars, callback) {

	if(typeof callback != 'function') {
		callback = $.noop;
	}
//alert( typeof vars );
	if(typeof vars == 'object') {
		if( typeof vars.school_id == 'undefined') {
			if( typeof $school.id != 'undefined') {
				vars.school_id = $school.id;
			}
		}
	}

	var link = $api + '?key='+$loginKey+'&action='+action;
	

	if(method == 'getlink') {
		return link+'&'+$.param(vars);
	} else if(method == 'get') {
		$.get(link, vars, callback, 'json');
	} else if(method == 'post') {
		$.post(link, vars, callback, 'json');
	}
}

function _reset_inputs(holder) {
	var obj = _jqueryObj( holder );
	if( obj ) {
		obj.find('input').each(function(){
			if(this.type == 'text') {
				this.value='';
			} else if(this.type == 'email') {
				this.value='';
			} else if(this.type == 'number') {
				this.value='';
			} else if(this.type == 'password') {
				this.value='';
			}
		});
		obj.find('textarea').each(function(){
			this.value='';
		});
	}
}



function _do_login(response) {
	
	
	var loggedin = {};
	if( typeof response.loggedin != 'undefined' && response.loggedin ) {
		loggedin = response;
	}

//	_log("_do_login", response);
//	_log("_do_login", loggedin);
	
	$loginKey = loggedin.key;
	$.cookie('loginKey', $loginKey, { expires : 10 });

	$categories_news = (typeof response.categories_news == 'object') ? response.categories_news : {};

//_log("_do_login", 'One');
	$categories_documents = (typeof loggedin.categories_documents == 'object') ? loggedin.categories_documents : {};
	$categories_gallery = (typeof loggedin.categories_gallery == 'object') ? loggedin.categories_gallery : {};

	$school = {};
	$account = (typeof loggedin.account == 'object') ? loggedin.account : {};
	$schools = (typeof loggedin.schools == 'object') ? loggedin.schools : {};
	$students = (typeof loggedin.students == 'object') ? loggedin.students : {};
	
	_select_school();

	_load_categories($categories_news, '#menu_education_news_menu', '#education_news_cat');
	_load_categories($categories_news, '#menu_school_news_menu', '#school_news_cat');
	_load_categories($categories_documents, '#menu_school_documents_menu', '#school_documents_cat');
	_load_categories($categories_gallery, '#menu_school_gallery_menu', '#school_gallery_cat');

	_html_links('#mp-menu');

	_showAsLoggedin();
}

function _select_school( school_id ) {

	$school = {};
	$.each($schools, function(i, v){
//		alert( $school.id );
//		alert( typeof school_id == 'undefined' );
		if( typeof school_id == 'undefined' && !$school.id) {
			$school = v;
		}
		else if( v.id == school_id) {
			$school = v;
		}
	});

	if( $school.id ) {
		$('.rname').html( $school.title );
	}
	else {
		$('.rname').html( 'School' );
	}
}

function _change_school_to( school_id ) {
	_select_school( school_id );
	
	school_about_created = false;
	
	_changeHolder('home');
}


function _load_categories(cats, holder, item) {

	var ul = $(holder).find('ul:first');
	ul.empty();
	$.each(cats, function(i, v){
		var li = $(item).clone();
		var li_a = li.find('a:first');

		li_a.data('cat_id', v.id);
		li_a.text(v.title);

		li.appendTo( ul );
	});
}
function _login(form) {
	return _post_form(form, 'login', function( response ){

//		_log("login", response);

		if( !response.loggedin ) {
			return _show_form_error(form, "Failed to login!");
		}

		_do_login(response);
		_changeHolder('home');
	});
}

function _pwd(form) {
	return _post_form(form, 'pwd', function( response ){

		alert('SMS message will sent to your mobile phone with your password!');
		_changeHolder('home');
	});
}
function _register(form) {
	return _post_form(form, 'register', function( response ){

		alert('SMS message will sent to your mobile phone with your password!');
		_changeHolder('login');
	});
}
function _post_form(form, action, callback) {
	if($(form).hasClass('sending')) return false;
	$(form).addClass('sending');

	_showLoader();

	$('.errorHolder', form).slideUp('fast');
	_api('post', action, $(form).serialize(), function(response){
//		_log("_post_form", typeof response);
//		_log("_post_form", response);
		$(form).removeClass('sending');
		_showLoader( false );
//		_log("_post_form", response);
//		_log("_post_form", response.error);

		if(typeof response.error != 'undefined' && response.error) {
			return _show_form_error(form, response.error);
		}
//		_log("_post_form", response);
		
		callback( response );
	});
	return false;
}

function _show_form_error(form, error) {

	$('.errors', form).html(error);
	$('.errorHolder', form).slideDown('fast');
	
	return false;
}



function _logout() {
	var loginKey = $loginKey;

	$('.rname').html( 'School' );

	_changeHolder('home');

	_api('post', 'logout');

	$loginKey = false;
	$.cookie('loginKey', '', { expires : 30 });
	$account = {};
	$schools = {};
	$students = {};
//	$categories_news = {}; // we need this categories for site news
	$categories_documents = {};
	$categories_gallery = {};

	_showAsLoggedin();
}


function DropDown(el) {
    this.dd = el;
    this.placeholder = this.dd.children('span');
    this.opts = this.dd.find('ul.dropdown > li');
    this.val = '';
    this.index = -1;
    this.initEvents();
}

DropDown.prototype = {
    initEvents : function() {
        var obj = this;
 
        obj.dd.on('click', function(event){
            $(this).toggleClass('active');
            return false;
        });

        obj.opts.on('click',function(){
            return false;
        });
    },
    getValue : function() {
        return this.val;
    },
    getIndex : function() {
        return this.index;
    }
}
$(function() {
	var dd = new DropDown( $('#Notifications') );
	$(document).click(function() {
		// all dropdowns
		$('.notification-list').removeClass('active');
	});
});

$(document).ready(function(){
	$('.flexslider').flexslider({
		  animation: "slide",
		  controlsContainer: ".flexslider-container"
	  });
});

function _reloadScroolPanel() {

	$("#scroll").css({'height' : $(window).height() - $('#app_name').outerHeight()}) -10;
	$("#scroll").jScrollPane({
	    mouseWheelSpeed: 50
	});

	menuList = $("#scroll").data('jsp');
	menuList.reinitialise();
}

function _logs(tag, details) {
	console.log('Tag:'+tag);
	console.log(details);
}

function _log(tag, details) {
	return _logs(tag, details);
}