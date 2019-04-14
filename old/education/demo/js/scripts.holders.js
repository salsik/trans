
// ============================================
// == Home Site News
// ============================================
function get_home_site_news($holder, $link) {
	// template: template_home_site_news

	_api('get', 'site_news_home', {limit: 3}, function(response){
		if(response.error) {
			return false;
		}
		temp = 'template_home_site_news';

		if(!response.data) {
			var msg = 'No News Available!';
			$('<div class="no-results" />').html(msg).appendTo( $('.listings', $holder) );
			return ;
		}

		$.each(response.data, function(i, v){
			ich[temp]( v ).appendTo( $('.listings', $holder) );
		});
		
		_html_links($holder);
	});
}

//============================================
//== Site News
//============================================
function get_site_news($holder, $link) {
	// template: template_site_news
	var vars = {};
	vars._load_cache = false;

	var obj = _jqueryObj( $link );
//	alert( typeof obj );
	if( obj ) {
		vars.news_cat_id = obj.data('cat_id');
	}
	_load_infinity_data($holder, 'site_news', 'template_site_news', vars, {noResult: 'No news available!'});
}
function get_site_news_details($holder, $link) {
	// template: template_site_news_details
	var obj = _jqueryObj( $link );
	if( obj ) {
		_load_details_data($holder, obj.data('id'), 'site_news_details', 'template_site_news_details', {}, {noResult: 'No news available!'});
	}
}

//============================================
//== Education News
//============================================

function get_education_news($holder, $link) {
	// template: template_education_news
	var vars = {};
	vars._load_cache = false;

	var obj = _jqueryObj( $link );
//	alert( typeof obj );
	if( obj ) {
		vars.news_cat_id = obj.data('cat_id');
	}
	
	_load_infinity_data($holder, 'education_news', 'template_education_news', vars, {noResult: 'No news available!'});
}
function get_education_news_details($holder, $link) {
	// template: template_education_news_details
	var obj = _jqueryObj( $link );
	if( obj ) {
		_load_details_data($holder, obj.data('id'), 'education_news_details', 'template_education_news_details', {}, {noResult: 'No news available!'});
	}
}

// ============================================
// == School News
// ============================================

function get_school_news($holder, $link) {
	// template: template_school_news
	var vars = {};
	vars._load_cache = false;

	var obj = _jqueryObj( $link );
//	alert( typeof obj );
	if( obj ) {
		vars.news_cat_id = obj.data('cat_id');
	}
	
	_load_infinity_data($holder, 'news', 'template_school_news', vars, {noResult: 'No news available!'});
}
function get_school_news_details($holder, $link) {
	// template: template_school_news_details
	var obj = _jqueryObj( $link );
	if( obj ) {
		_load_details_data($holder, obj.data('id'), 'news_details', 'template_school_news_details', {}, {noResult: 'No news available!'});
	}
}

//============================================
//== School Gallery
//============================================

function get_school_gallery($holder, $link) {
	// template: template_school_gallery
	var vars = {};
	vars._load_cache = false;

	var obj = _jqueryObj( $link );
//	alert( typeof obj );
	if( obj ) {
		vars.album_id = obj.data('cat_id');
	}
	
	_load_infinity_data($holder, 'gallery', 'template_school_gallery', vars, {noResult: 'No Photos available!'});
}
function get_school_gallery_details($holder, $link) {
	// template: template_school_gallery_details
	var obj = _jqueryObj( $link );
	if( obj ) {
		_load_details_data($holder, obj.data('id'), 'gallery_details', 'template_school_gallery_details', {}, {noResult: 'No Photo available!'});
	}
}

//============================================
//== School Videos
//============================================

function get_school_videos($holder, $link) {
	// template: template_school_news
	var vars = {};
	vars._load_cache = false;

	var obj = _jqueryObj( $link );
	
	_load_infinity_data($holder, 'videos', 'template_school_videos', vars, {noResult: 'No videos available!'});
}
function get_school_videos_details($holder, $link) {
	// template: template_school_videos_details
	var obj = _jqueryObj( $link );
	if( obj ) {
		_load_details_data($holder, obj.data('id'), 'videos_details', 'template_school_videos_details', {}, {noResult: 'No video available!'});
	}
}

//============================================
//== School Contact
//============================================

function get_school_contact($holder, $link) {
	// template: template_school_contact
	var vars = {};
	var obj = _jqueryObj( $link );
//	alert( typeof obj );
	if( obj ) {
		vars.cat_id = obj.data('cat_id');
		vars.sub_id = obj.data('sub_id');
	}
	_load_infinity_data($holder, 'questions', 'template_school_contact', vars, {
		noResult: 'No previous contacts available!',
		filterResults: function(data){
			if( !data.replies || data.replies < 1) {
				data.last_update = 'Never';
			} else {
				data.last_update = data.last_reply_date;
				data.last_update += ' ' + data.last_reply_time;
				if( data.last_reply_from=='school') {
					data.last_update += ' <b>By </b>' + $school.title;
				} else {
					data.last_update += ' <b>By </b>' + $account.full_name;
				}
	
				data.last_update += ' | <b>Replies:</b> ' + data.replies;
			}
	
			return data;
		}
	});
}
function get_school_contact_details($holder, $link) {
	// template: template_school_contact_details
	
	
	
	var obj = _jqueryObj( $link );
	if( obj ) {
		if( $holder.data('load_id') && $holder.data('load_id') == obj.data('id')) {
			return false;
		}

		$('.detailsBox', $holder).empty();
		$('.listings', $holder).empty();
		$('.reply', $holder).hide();

		_load_infinity_data($holder, 'questions_replies', 'template_school_contact_replies', {id: obj.data('id') },{
			noResult: 'No replies available!',
			loadData: function( response, $holder ){

				if(response.question) {
//					ich['template_school_contact_detailsBox']( response.question ).appendTo( $('.detailsBox', $holder) );
//					$holder.data('load_id', response.question.id);
					ich['template_school_contact_detailsBox']( response.question[0] ).appendTo( $('.detailsBox', $holder) );
					$holder.data('load_id', response.question[0].id);
				}
			}, 
			filterResults: function(data){
				if( data.from=='school') {
					data.reply_from = $school.title;
				} else {
					data.reply_from = $account.full_name;
				}

				return data;
			},
			afterAll: function(){
				$('.reply', $holder).show();
			}
		});
	}
}


function _school_contact_reply(form) {
	if($(form).hasClass('sending')) return false;
	$(form).addClass('sending');
	
	$holder = $(form).parents('.holders:first');

	_showLoader();

	$('.errorHolder', form).slideUp('fast');
	var vars = {
		question_id : $holder.data('load_id'),
		question_reply: $(form).find('.question_reply:first').val()
	};
	_api('post', 'question_reply', vars, function(response){
		$(form).removeClass('sending');
		_showLoader( false );
		if(response.error) {
			$('.errors', form).html(response.error);
			$('.errorHolder', form).slideDown('fast');
			return false;
		}
		_reset_inputs($holder);

		ich['template_school_contact_replies']( response.reply ).appendTo( $('.listings', $holder) );
	});
	return false;
}
function _school_contact_add(form) {
	if($(form).hasClass('sending')) return false;
	$(form).addClass('sending');
	
	$holder = $(form).parents('.holders:first');

	_showLoader();

	$('.errorHolder', form).slideUp('fast');
	var vars = {
		contact_name: $(form).find('.contact_name:first').val(),
		contact_email: $(form).find('.contact_email:first').val(),
		contact_phone: $(form).find('.contact_phone:first').val(),
		contact_address: $(form).find('.contact_address:first').val(),

		question_title: $(form).find('.question_title:first').val(),
		question_description: $(form).find('.question_description:first').val()
	};
	_api('post', 'question_add', vars, function(response){
		$(form).removeClass('sending');
		_showLoader( false );
		if(response.error) {
			$('.errors', form).html(response.error);
			$('.errorHolder', form).slideDown('fast');
			return false;
		}
		_reset_inputs($holder);
		
		_changeHolder('school-contact', '', function( holder ){

			holder.find('.listings').empty();
		});

	});
	return false;
}

//============================================
//== School Polls
//============================================

function get_school_polls($holder, $link) {
	// template: template_school_contact
	var vars = {};
	vars.school_id = $school.id;
	var obj = _jqueryObj( $link );
	_load_infinity_data($holder, 'polls', 'template_school_poll', vars, {
		noResult: 'No polls available!'
	}); 
}
function get_school_poll($holder, $link) {
	// template: template_school_poll
	$('.submitHolder', $holder).hide();

	$('.content2', $holder).hide();
	$('.content1', $holder).show();
	
	var _vars = {_load_once: true, _load_cache: false};
	_vars.poll_id = $($link).data('id');
	_vars.school_id = $school.id;
	
	_load_infinity_data($holder, 'poll', 'template_school_poll_options', _vars, {
		noResult: 'No poll available!',
		loadData: function( response, $holder ){

			if(response.poll) {
				$('.detailsBox', $holder).empty();
				ich['template_school_poll_detailsBox']( response.poll[0] ).appendTo( $('.detailsBox', $holder) );
				$holder.data('load_id', response.poll[0].id);
			}
			
			if( response.poll ) {
				$holder.data('poll_id', response.poll[0].id);
				if( response.poll[0].option_id > 0 ) {
					return {
						template: 'template_school_poll_options_text'
					};
				} else {
					$('.submitHolder', $holder).show();
				}
			}
			
		},
		filterResults: function( data ){
			if(data.selected) {
				data.className = 'selected';
				data.checked = 'checked="CHECKED"';
			}
			return data;
		}
	});
}
function _poll_reply(element) {

	var $holder = $(element).parents('.holders:first');

	var id = $(element).find('.option:checked').val();
	var poll_id = $holder.data('poll_id');
	
	_showLoader();
	
	$('.errorHolder', $holder).slideUp('fast');
	var _vars = {
		poll_id: poll_id,
		option_id: id,
		school_id: $school.id
	};
	_api('post', 'poll_option', _vars, function(response){
		_showLoader( false );

		if(response.error) {
			
			$('.errors', $holder).html(response.error);
			$('.errorHolder', $holder).slideDown('fast');
			return false;
		}

		$('.content1', $holder).hide();
		$('.content2', $holder).show();
	});
	return false;
}
//============================================
//== School Quiz
//============================================

function get_school_quiz($holder, $link) {
	// template: template_school_quiz
	$('.wall', $holder).hide();
	$('.submitHolder', $holder).hide();

	$('.content2', $holder).hide();
	$('.content1', $holder).show();
	
	$('.students', $holder).empty();

	_load_infinity_data($holder, 'competition', 'template_school_quiz_options', {_load_once: true, _load_cache: false}, {
		noResult: 'No quiz available!',
		loadData: function( response, $holder ){

			if(response.competition) {
				$('.detailsBox', $holder).empty();
//				ich['template_school_quiz_detailsBox']( response.competition ).appendTo( $('.detailsBox', $holder) );
//				$holder.data('load_id', response.competition.id);
				ich['template_school_quiz_detailsBox']( response.competition[0] ).appendTo( $('.detailsBox', $holder) );
				$holder.data('load_id', response.competition[0].id);
			} else if(response.no_competition) {
				ich['template_school_quiz_empty']({}).appendTo( $('.detailsBox', $holder) );
			}
			if(response.students && response.students.length) {
				$('.wall', $holder).show();
				$('.students', $holder).empty();
				$.each(response.students, function(i, v){
					ich['template_school_quiz_students']( v ).appendTo( $('.students', $holder) );
				});
			} 
			
			if( response.competition ) {
				$holder.data('competition_id', response.competition.id);
				if( response.competition.option_id > 0 ) {
					return {
						template: 'template_school_quiz_options_text'
					};
				} else {
					$('.submitHolder', $holder).show();
				}
			}
			
		}, 
		filterResults: function( data ){
			if(data.selected) {
				data.className = 'selected';
				data.checked = 'checked="CHECKED"';
			}
			return data;
		}
	});
}



function _quiz_reply(element) {

	var $holder = $(element).parents('.holders:first');

	var id = $(element).find('.option:checked').val();
	var quiz_id = $holder.data('competition_id');
	
	_showLoader();
	
	$('.errorHolder', $holder).slideUp('fast');
	vars = {
		competition_id: quiz_id,
		option_id: id
	};
	_api('post', 'competition_option', vars, function(response){
		_showLoader( false );

		if(response.error) {
			
			$('.errors', $holder).html(response.error);
			$('.errorHolder', $holder).slideDown('fast');
			return false;
		}

		$('.content1', $holder).hide();
		$('.content2', $holder).show();

		if(response.students && response.students.length) {
			$('.wall', $holder).show();
			$('.students', $holder).empty();
			$.each(response.students, function(i, v){
				ich['template_school_quiz_students']( v ).appendTo( $('.students', $holder) );
			});
		}
	});
	return false;
}

// ============================================
// == School Files
// ============================================

function get_school_files($holder, $link) {
	// template: template_school_files
	var vars = {};
	var obj = _jqueryObj( $link );
//	alert( typeof obj );
	if( obj ) {
		vars.document_cat_id = obj.data('cat_id');
	}
	_load_infinity_data($holder, 'documents', 'template_school_files', vars, {noResult: 'No files available!'});
}

//============================================
//== About School
//============================================
function about_school($holder, $link) {
	// template: template_about_school
	
	if( school_about_created ) {
		return false;
	}
	school_about_created = true;
	
//	$('.about', $holder).empty();

	var temp = 'template_sbout_school';

	$vars = {};
	$vars.school_id = $school.id;

	_showLoader( true );
	
	_api('get', 'about', $vars, function(response){
		_showLoader( false );
		if(response.error) {
			return false;
		}
		
		$('.about', $holder).html( response.data.description );
	});
}

//============================================
//== Settings
//============================================

function get_settings($holder, $link) {

	SelectRadioButtonByValue($("input[name='notifications']", $holder), $account.notifications, 0);

}

function _update_settings(element) {

	var $holder = $(element).parents('.holders:first');

	_showLoader();
	
	$('.errorHolder', $holder).slideUp('fast');

	var vars = {
		notifications: $(element).find("input[name='notifications']:checked").val()
	};
	
	_api('post', 'settings', vars, function(response){
		_showLoader( false );

		if(response.error) {
			$('.errors', $holder).html(response.error);
			$('.errorHolder', $holder).slideDown('fast');
			return false;
		}

		$account.notifications = vars.notifications;
		
		alert("Settings updated!");
	});
	return false;
}

function SelectRadioButtonByValue(Selector, value, def) {
	var selected = false;
	$(Selector).each(function(){
		if($(this).is('input') && $(this).val() == value) {
			selected = true;
			$(this).prop('checked', true);
		}
	});
	
	if( !selected && typeof def != 'undefined') {
		SelectRadioButtonByValue(Selector, def);
	}
}
//============================================
//== Change School
//============================================
function change_school($holder, $link) {
	// template: template_change_school
	
	if( school_list_created ) {
		return false;
	}
	school_list_created = true;

	temp = 'template_change_school';

	$.each($schools, function(i, v){
		ich[temp]( v ).appendTo( $('.listings', $holder) );
	});
	

//	_html_links($holder);
}

//============================================
//== Agenda
//============================================
function get_agenda_students($holder, $link) {
	// template: template_change_school
	
	if( students_list_created ) {
		return false;
	}
	students_list_created = true;

	temp = 'template_agenda_students';

	$.each($students, function(i, v){
		ich[temp]( v ).appendTo( $('.listings', $holder) );
	});
	_html_links($holder);
//	_html_links($holder);
}
function get_agenda_student($holder, $link) {
	// template: template_site_news
	var vars = {};
	vars._load_cache = false;

	var obj = _jqueryObj( $link );
//	alert( typeof obj );
	if( obj ) {
		vars.student_id = obj.data('id');
	}
	_load_infinity_data($holder, 'agenda', 'template_agenda_student', vars, {noResult: 'Agenda not available!'});
}

// ============================================
// == FUNCTIONS
// ============================================

function _load_infinity_data($holder, action, template, $vars, callbacks) {

	if(typeof $vars != 'object') {
		$vars = {};
	}
	if(typeof callbacks != 'object') {
		callbacks = {};
	}
	
	$vars.school_id = $school.id;
	$vars._load_cache = false;
	
	_load_cache = ($vars._load_cache!==false);
	
	if( _load_cache && $('.no-results', $holder).length > 0) {
		return false;
	}
	if( !_load_cache || $('.item', $holder).length < 1) {
		_showLoader();
		$('.listings', $holder).empty();
		if(!$global[action]) {
			$global[action] = {};
		}
		$global[action].page = 1;

		_api('get', action, $vars, function(response){
			_showLoader( false );
			if(response.error) {
				return false;
			}
			temp = template;
			if(typeof callbacks.loadData == 'function') {
				var dataLoaded = callbacks.loadData( response, $holder );
				if( typeof dataLoaded == 'object') {
					if( dataLoaded.template ) {
						temp = dataLoaded.template;
					}
				}
			}

			if(!response.data) {
				var msg = (callbacks.noResult ) ? callbacks.noResult : 'No Results!';
				$('<div class="no-results" />').html(msg).appendTo( $('.listings', $holder) );
				return ;
			}

			$.each(response.data, function(i, v){
				if(typeof callbacks.filterResults == 'function') {
					v = callbacks.filterResults(v);
				}
				ich[temp]( v ).appendTo( $('.listings', $holder) );
			});

			
			if( $('.listings', $holder).hasClass('listings_banners_private') ) {
				_buildListingsBanners( $('.listings', $holder), true );
			}
			else if( $('.listings', $holder).hasClass('listings_banners') ) {
				_buildListingsBanners( $('.listings', $holder), false );
			}

			_html_links($holder);

			if(typeof callbacks.afterAll == 'function') {
				callbacks.afterAll();
			}
			
			if(!$vars._load_once ) {
				$($('.listings', $holder).get(0)).infiniScroll({
					vars: $vars,
			        url: function () {
						$global[action].page++;
		
	//					var link = $api + '?action='+action+'&page='+$global[action].page;
						var link = _api('getlink', action, {page: $global[action].page});
						return link;
			        },
			        before: function () {
						$('.load_more', $holder).show();
			        },
			        after: function (response, options) {
	
						$('.load_more', $holder).hide();
	
						if(response.error || !response.data) {
							options.active = false;
			                return '';
						}
						
						$.each(response.data, function(i, v){
							if(typeof callbacks.filterResults == 'function') {
								v = callbacks.filterResults(v);
							}
							ich[template]( v ).appendTo( $('.listings', $holder) );
						});
						if( $('.listings', $holder).hasClass('listings_banners_private') ) {
							_buildListingsBanners( $('.listings', $holder), true );
						}
						else if( $('.listings', $holder).hasClass('listings_banners') ) {
							_buildListingsBanners( $('.listings', $holder), false );
						}
						_html_links($holder);
		                return '';
			        }
				});
	
			}
		});
	}
}

function _load_details_data($holder, id, action, template, $vars, callbacks) {

	id = parseInt( id );
	if( isNaN(id) || id < 1) {
		return false;
	}
	if(typeof callbacks != 'object') {
		callbacks = {};
	}
	if( $holder.data('load_id') && $holder.data('load_id') == id) {
		return false;
	}

	$('.detailsBox', $holder).empty();

	_showLoader();

	_api('get', action, {id: id}, function(response){
		_showLoader( false );
		if(response.error) {
			return false;
		}

		if(typeof callbacks.filterResults == 'function') {
			response.data = callbacks.filterResults(response.data);
		}

		if(!response.data) {
			var msg = (callbacks.noResult ) ? callbacks.noResult : 'No Results!';
			$('<div class="no-results" />').html( msg ).appendTo( $('.detailsBox', $holder) );
			return ;
		}

		ich[template]( response.data ).appendTo( $('.detailsBox', $holder) );

		_html_links($holder);

		if(typeof callbacks.afterAll == 'function') {
			callbacks.afterAll();
		}

		$holder.data('load_id', response.data.id);
	});
}

