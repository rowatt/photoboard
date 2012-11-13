jQuery(function($) {

	//hide addressbar in mobile safari
	window.scrollTo(0, 1);

/* --------------------------------------------------------------
/* Display sorry notice if old browser
/* -------------------------------------------------------------- */

	//Mozilla v 2 is Firefox 4.0
	//Safari 530 is Safari 4.0

	if( (  $.browser.msie && parseInt($.browser.version) < 8 ||
			$.browser.mozilla && parseInt($.browser.version) < 2 ||
			$.browser.safari && parseInt($.browser.version) < 530 )
		) {
		//unsupported browser
		$('#sorry').show();
		$('#primary').css('visibility','hidden');
	} else {
		//supported or unknown browser
		$('#sorry').hide();
		$('#primary').css('visibility','visible');
		$('#show-filter').show();
		$('#full-wrapper').fadeIn();
	}

/* --------------------------------------------------------------
/* Ajax loaders
/* -------------------------------------------------------------- */

	// load full sized image & info when thumb clicked
	$('#photoboard-thumbs').on( 'click', '.person-thumb', function() {
		myID = $(this).attr('id').substr(2);
		$.ajax({
			type: 'POST',
			url: photoboard_params['ajaxurl'],
			data: {
				"action": "photofull",
				"id": myID
			}
		}).success(function(data, textStatus, XMLHttpRequest) {
				$('#ajax-full').html(data);
				$('#help-full').hide();
		});
	});

	// load thumbs
	$.ajax({
		type: 'POST',
		url: photoboard_params['ajaxurl'],
		data: {
			"action": "photothumbs"
		}
	}).success(function(data, textStatus, XMLHttpRequest) {
		$('#photoboard-thumbs').html(data).hide().fadeIn();
		resize_thumb_area();
	});


/* --------------------------------------------------------------
/* Resize thumbs area width so things fit well, & adjust other bits accordingly
/* -------------------------------------------------------------- */

	$(window).resize( function() {
		resize_thumb_area();
	});

	function resize_thumb_area() {
		//calculations
		var thumb_width = $('.person-thumb').first().width();
		var full_width = $('#full-wrapper').width();
		var page_width = $('#page').width();
		var new_width = thumb_width * Math.floor( (page_width - full_width - 20)/thumb_width );

		//set width of thumbs
		$('#photoboard-thumbs').width(new_width);

		//set position of full sized image
		$('#full-wrapper').css( 'margin-left', new_width+20);

		//adjust float right menus
		$('.navbar-inner .pull-right').css( 'margin-right', page_width - new_width - full_width - 15 );
	}


/* --------------------------------------------------------------
/* Show/hide thumbs based on filters
/* -------------------------------------------------------------- */

	$('.filter-checkbox input').change( function(){
		filter_thumbs();
	});

	function filter_thumbs() {
		var state_current = $('#filter-current').prop('checked');
		var state_not_current = $('#filter-not-current').prop('checked');

		var state_ff_member = $('#filter-ff_member').prop('checked');
		var state_ff_other = $('#filter-ff_other').prop('checked');
		var state_ff_guest = $('#filter-ff_guest').prop('checked');
		var state_ff_volunteer = $('#filter-ff_volunteer').prop('checked');
		var state_nfa_staff = $('#filter-nfa_staff').prop('checked');
		var state_nfa_member = $('#filter-nfa_member').prop('checked');
		var state_other_affiliation = $('#filter-other_affiliation').prop('checked');

		var state_park = $('#filter-park').prop('checked');
		var state_cluny = $('#filter-cluny').prop('checked');
		var state_findhorn = $('#filter-findhorn').prop('checked');
		var state_kinloss = $('#filter-kinloss').prop('checked');
		var state_forres = $('#filter-forres').prop('checked');
		var state_other_location = $('#filter-other_location').prop('checked');

		$('.person-thumb').hide().filter( function() {
			return  (
				($(this).hasClass('current') && state_current) ||
					($(this).hasClass('not-current') && state_not_current)
				)
				&&
				(
					($(this).hasClass('ff_member') && state_ff_member) ||
						($(this).hasClass('ff_other') && state_ff_other) ||
						($(this).hasClass('ff_guest') && state_ff_guest) ||
						($(this).hasClass('ff_volunteer') && state_ff_volunteer) ||
						($(this).hasClass('nfa_member') && state_nfa_member) ||
						($(this).hasClass('nfa_staff') && state_nfa_staff) ||
						($(this).hasClass('other_affiliation') && state_other_affiliation)
					)
				&&
				(
					($(this).hasClass('location-park') && state_park) ||
						($(this).hasClass('location-cluny') && state_cluny) ||
						($(this).hasClass('location-findhorn') && state_findhorn) ||
						($(this).hasClass('location-kinloss') && state_kinloss) ||
						($(this).hasClass('location-forres') && state_forres) ||
						($(this).hasClass('location-other') && state_other_location)
					)
				;
		}).show();
	}

	$('#show-filter').click(function(e) {
		e.preventDefault();
		try{
			var scroll_to = $(window).scrollTop() + 40;
		} catch(e) {
			var scroll_to = window.pageYOffset;
		}

		$('#filters').css('top', scroll_to );
		$('#filters').toggle();
		return false;
	});

	$('.check-buttons .label').click( function() {
		$(this).parent().next('ul.filter-list').find('input').prop('checked', $(this).hasClass('check-all'));
		filter_thumbs();
	});

/* --------------------------------------------------------------
/* Other
/* -------------------------------------------------------------- */

	//add bootstrap style to current menu item
	$('.current-menu-item').addClass('active');

	$('.close').click( function() {
		$(this).parent().hide();
	});

	$('#full-wrapper').click( function() {
		var elem = $(this);
		elem.toggleClass('is-hovered');

		if( ! elem.hasClass('is-hovered') )
			elem.addClass('not-hovered');
		else
			elem.removeClass('not-hovered');
	});

	$('#full-wrapper').mouseleave( function() {
		$(this).removeClass('not-hovered');
	});

});