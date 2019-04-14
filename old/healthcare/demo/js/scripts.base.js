
var $bannerworking = false;
var $global = {};

//$(document).ready(function(){
//	$("#scroll").jScrollPane();
//	
//	$("#scroll").each(function(){
//		var api = $(this).data('jsp');
//		var throttleTimeout;
//		$(window).bind('resize', function() {
//			if (!throttleTimeout) {
//				throttleTimeout = setTimeout( function() {
//					api.reinitialise();
//					throttleTimeout = null;
//				}, 50 );
//			}
//		});
//	});
//});

$(document).ready(function () {
    _showAsLoggedin();
    _changeHolder('home');
//	if( window.location.hash ) {
//		var hash = window.location.hash.substring(1);
//		if( hash.match(/^[a-zA-Z0-9_-]+$/) ) {
//			_changeHolder(hash);
//		}
//	}
    _html_links('body');
    get_notifications();
});

function _html_links(Selector) {
    $(Selector).find('a').each(function () {
        var link = this;

        if ($(link).hasClass('_linked')) {
            return;
        }
        $(link).addClass('_linked');

        if ($(link).attr('target') == '_blank') {
            return;
        }

        if (!$(link).attr('href') || $(link).attr('href') == '#') {
            return;
        }

        $(link).on('click', function () {

            var thisLink = this;

            if ($(thisLink).attr('href').indexOf('#') !== 0) {
                return false;
            }

            var $goto = $(thisLink).attr('href').substring(1);
            if (!$goto.match(/^[a-zA-Z0-9_-]+$/)) {
                return false;
            }

            if ($goto == 'logout') {
                _logout();
            } else {
                _changeHolder($goto, thisLink);
            }
            return false;
        });
    });
}
function _changeHolder(holder, link, callback) {

    var Holder = $('#' + holder + '.holders');
    if (Holder.length < 1) {
        return false;
    }

    $('.notification-list').removeClass('active');
    _showLoader(false);
    _loadBanners(Holder);
    $('.holders').hide();
    Holder.show();
//	window.location.hash = holder;
//	$('body').trigger('click');
//	$('body').trigger('touchstart');
    _menu._resetMenu();

    if (typeof callback == 'function') {
        callback(Holder);
    }

    var $callback = Holder.data('callback');
    if ($callback) {
        if (typeof window[ $callback ] == 'function') {
            window[ $callback ](Holder, link);
        }
    }
}
function _loadBanners(holder, $class, $empty) {

    var banners = [];
    var bannerTypes = {};
    var $i = 0;

    $class = ($class) ? $class : 'banner';
    holder.find('.' + $class).each(function () {
        if ($(this).data('banner') && (!$empty || $(this).html() == '')) { // && 
            banners['banner' + $i] = this;
            bannerTypes['banner' + $i] = $(this).data('banner');

            $i++;
        }
    });

    if ($i < 1) {
        return;
    }

    _api('get', 'banners', {types: bannerTypes}, function (response) {
        if (response.error || !response.banners) {
            return false;
        }

        $.each(response.banners, function (i, v) {
            if (banners[i]) {
                $(banners[i]).html(v.banner);

                banners['banner' + $i] = this;
                bannerTypes['banner' + $i] = $(this).data('banner');
            }
        });
    });
}

function _buildListingsBanners(Listings, isPrivate) {

    if ($bannerworking) {
        return false;
    }
    $bannerworking = true;
    if (isPrivate) {
        var bannerDom = '<div class="listings_banner listings_banner_private banner center" data-banner="listings_banner" ></div>';
    } else {
        var bannerDom = '<div class="listings_banner banner center" data-banner="listings_banner" ></div>';
    }

    var bannerEach = 3;
    var x = 0;

    Listings.children('div').each(function () {
        if ($(this).hasClass('item')) {
            x++;
            if (x > 0 && !(x % bannerEach)) {
                $(bannerDom).insertBefore(this);
            }
        }
    });
    if (isPrivate) {
        _loadBanners(Listings, 'listings_banner_private', true);
    } else {
        _loadBanners(Listings, 'listings_banner', true);
    }

    $bannerworking = false;


//	_api('get', 'banners', {types: bannerTypes}, function(response){
//		if(response.error || !response.banners) {
//			return false;
//		}
//
//		$.each(response.banners, function(i, v){
//			if(banners[i]) {
//				$(banners[i]).html(v.banner);
//			}
//		});
//	});
}


function _showLoader(show, callback) {
    if (show === false) {
        $('#loader').stop(true, true).fadeOut(600, function () {
            if (typeof callback == 'function') {
                callback();
            }
        });
    } else {
        $('#loader').stop(true, true).fadeIn(600, function () {
            if (typeof callback == 'function') {
                callback();
            }
        });
    }
}
function _showAsLoggedin(callback) {
    if ($loginKey) {
        $('.isLoggedin').show();
        $('.isNotLoggedin').hide();
        $('#app_name').html($account.full_name);
    } else {
        $('.isLoggedin').hide();
        $('.isNotLoggedin').show();
        $('#app_name').html('Sections');
    }
    if (typeof callback == 'function') {
        callback();
    }
}

function _jqueryObj(obj) {
    if (typeof obj == 'object') {
        if (typeof obj.nodeType != 'undefined') {
            return $(obj);
        } else if (typeof obj.jquery != 'undefined') {
            return obj;
        }
    }
    return false;
}


function _api(method, action, vars, callback) {

    if (typeof callback != 'function') {
        callback = $.noop;
    }

    var link = $api + '?key=' + $loginKey + '&action=' + action;

    if (method == 'getlink') {
        return link + '&' + $.param(vars);
    } else if (method == 'get') {
        $.get(link, vars, callback, 'json');
    } else if (method == 'post') {
        $.post(link, vars, callback, 'json');
    }
}

function _reset_inputs(holder) {
    var obj = _jqueryObj(holder);
    if (obj) {
        obj.find('input').each(function () {
            if (this.type == 'text') {
                this.value = '';
            } else if (this.type == 'email') {
                this.value = '';
            } else if (this.type == 'password') {
                this.value = '';
            }
        });
        obj.find('textarea').each(function () {
            this.value = '';
        });
    }
}

function get_notifications() {
    if ($loginKey) {
        _api('post', 'notifications', {}, function (response) {
            if (response.notifications) {
                update_notifications(response.notifications);
            }
        });
    }
}
function _do_login(response) {

    $loginKey = response.key;
    $.cookie('loginKey', $loginKey, {expires: 10});
    $account = response.account;
    $reseller = response.reseller;
    $categories = response.categories;
    $('.rname').html($reseller.title);
    _showAsLoggedin();
}
function _login(form) {
    if ($(form).hasClass('sending'))
        return false;
    $(form).addClass('sending');

    _showLoader();

    $('.errorHolder', form).slideUp('fast');
    _api('post', 'login', $(form).serialize(), function (response) {
        $(form).removeClass('sending');
        if (response.error) {
            _showLoader(false);

            $('.errors', form).html(response.error);
            $('.errorHolder', form).slideDown('fast');
            return false;
        }
        _do_login(response);

// _buildCategoriesList();
        _changeHolder('home');
    });
    return false;
}

function _logout() {
    var loginKey = $loginKey;

    $('.rname').html('Reseller');

    _changeHolder('home');

    _api('post', 'logout');

    $loginKey = false;
    $.cookie('loginKey', '', {expires: 30});
    $account = {};
    $reseller = {};
    $categories = {};

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
    initEvents: function () {
        var obj = this;

        obj.dd.on('click', function (event) {
            $(this).toggleClass('active');
            return false;
        });

        obj.opts.on('click', function () {
            return false;
        });
    },
    getValue: function () {
        return this.val;
    },
    getIndex: function () {
        return this.index;
    }
}
$(function () {
    var dd = new DropDown($('#Notifications'));
    $(document).click(function () {
        // all dropdowns
        $('.notification-list').removeClass('active');
    });
});




/* 31-10-2013 */
$(document).ready(function () {
    $('.flexslider').flexslider({
        animation: "slide",
        controlsContainer: ".flexslider-container"
    });
});
