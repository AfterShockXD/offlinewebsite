(function($) {

	$.fn.setValidationError = function( strMessage )
	{
		var element = $(this);
		element.removeClass('success').addClass('error').text(strMessage);
	};

	$.fn.setValidationSuccess = function( strMessage )
	{
		var element = $(this);
		element.removeClass('error').addClass('success').text(strMessage);
	};

	$.fn.tagName = function()
	{
		if ( typeof this[0] == 'undefined' ) { return ''; }
		return this[0].tagName.toLowerCase();
	};

	$.fn.clearInfo = function( strMessage )
	{
		$(this).next('span.info').removeClass('loading success error').show().text('');
	};

	$.fn.validatorError = function( strMessage )
	{
		console.log('validatorError():', this.parent(), strMessage);
		$(this).addClass('validate-error');
		$(this).next('span.info').removeClass('loading success').show().setValidationError(strMessage);
	};

	$.fn.validatorSuccess = function( strMessage )
	{
		$(this).removeClass('validate-error');
		$(this).next('span.info').removeClass('loading error').show().setValidationSuccess(strMessage);
	};

	$.fn.validatorInfo = function( strMessage )
	{
		$(this).removeClass('validate-error');
		$(this).next('span.info').removeClass('loading error').show().html(strMessage);
	};

	$.fn.validatorIndicator = function( strMessage )
	{
		$(this).removeClass('validate-error');
		$(this).next('span.info').removeClass('loading error').hide();
		$(this).parent().find('span.indicator').show().html(strMessage);
	};

	$.fn.validatorLoader = function( strMessage )
	{
		$(this).removeClass('validate-error');
		$(this).next('span.info').removeClass('success error').show().setValidationSuccess(strMessage);
	};

	var validate = {

		ajaxQueue: {},
		ajaxQueueCounter: 0,

		email: function()
		{
			var element = this;
			var expression = /^([a-z0-9\-\_\.]{1,100})([a-z0-9]+)\@([a-z0-9]+)([a-z0-9\-\.]*)([a-z0-9]+)\.([a-z]{2,6})$/;
			//var expression = /^([a-z]+)([a-z0-9\-\_\.]{1,100})([a-z0-9]+)\@([a-z0-9]+)([a-z0-9\-\.]*)([a-z0-9]+)\.([a-z]{2,6})$/;

		
			if ( !element.val().match(expression) || element.val().length == 0 )
			{
				element.validatorError('Invalid email address');
				return false;
			}

			else element.validatorSuccess('');
			return true;
		},

		numeric: function( charlength ) {
			var element = this, expression = /^[0-9]+$/;

			if ( !element.val().match(expression) )
			{
				element.validatorError('Only numbers allowed');
				return false;
			}

			else if ( parseInt(charlength) > 0 && element.val().length != parseInt(charlength) )
			{
				element.validatorError('Only ' + parseInt(charlength) + ' characters allowed');
				return false;
			}

			else element.validatorSuccess('');
		},

		alphaonly: function()
		{
			var element = this, expression = /[a-zA-Z]$/;
			if ( !element.val().match(expression) )
			{
				element.validatorError('Only characters allowed');
				return false;
			}

			else element.validatorSuccess('');
			return true;
		},

		alphanumeric: function()
		{
			return this.val().length;
		},
		checked: function()
		{
			var element = this;
			//console.log ()
			if (!element.is(':checked'))
			{
				element.validatorError('required field');
				return false;
			}

			else element.validatorSuccess('');
			return true;
		},

		empty: function()
		{
			var element = this;

			if ( element.val().length == 0 )
			{
				element.validatorError('Required field');
				return false;
			}

			else element.validatorSuccess('  ');
			return true;
		},

		select: function()
		{
			// Match the default values to the following
			var element = this, defaults = [ 0, 'null' ], isdef = false;

			// Loop through the defaults and compare the selected value
			$(defaults).each(function() {
				// Check for match
				if ( element.val() == this )
				{
					isdef = true;
					return false;
				}
			});

			// If a default value has been selected then return
			if ( isdef )
			{
				element.validatorError('Please make your selection');
				return false;
			}

			else element.validatorSuccess('');
			return true;
		},

		match: function( elementId )
		{
			var element = this, target = $(element).closest('form').find('[name="' + elementId + '"]');

			// Ignore empty values
			if ( element.val().length == 0 ) return;

			// We won't get to this point if they have specified an invalid email address
			if ( target.val() != element.val() )
			{
				element.validatorError('Password fields do not match');
				return false;
			}

			else element.validatorSuccess('');
			return true;
		},

		ajax: function( method )
		{
			var element = this, destination = window.location.toString();

			// Exit the function if no value is specified
			if ( element.val().length <= 1 ) return false;

			// Housekeeping
			element.validatorLoader('Validating field...');
			validate.ajaxQueue[ element.attr('name') ] = false;
			validate.ajaxQueueCounter++;
			//alert('ajaxQueueCounter++: ' + validate.ajaxQueueCounter);

			// Attempt to call a php function in the active file
			$.post(destination, { ajax: true, method: method, value: element.val() }, function( response )
			{
				// Removing the loading class from the element
				element.removeClass('loading');
				validate.ajaxQueue[ element.attr('name') ] = true;
				validate.ajaxQueueCounter--;
				//alert('ajaxQueueCounter--: ' + validate.ajaxQueueCounter);

				// If an error occured, update the element and exit
				if ( !response.success )
				{
					element.validatorError( response.message ? response.message : 'Unknown Error' );
					return false;
				}

				// Append the response message
				element.validatorSuccess( response.message ? response.message : 'Correct' );
			}, 'json');

			return false;
		},

		phone: function()
		{
			var element = this;

			// Exit if there is no value entered
			if ( element.val().length <= 1 ) return;

			var expression = /^([0-9]( |-)?)?(\(?[0-9]{3}\)?|[0-9]{2,3})( |-)?([0-9]{3}( |-)?[0-9]{4}|[a-zA-Z0-9]{7})$/;

			if ( !element.val().match(expression) )
				element.validatorError('Invalid telephone number');
			else element.validatorSuccess('Correct');
		},

		password: function()
		{
			var element = this, points = 0, strlen = element.val().length
			var scale = 35, rangeindex = -1, percentage;

			var ranges = [
				{ range: 20,	text: 'Very Weak',	color: '#CC0000' },
				{ range: 30,	text: 'Weak',		color: 'red' },
				{ range: 50,	text: 'Good',		color: 'orange' },
				{ range: 65,	text: 'Very Good',	color: 'green' },
				{ range: 100,	text: 'Excellent',	color: '#006600' }
			];

			$.each( element.val(), function( index, character )
			{
				if ( (/^[a-z]+$/).test(character) ) points += 1;
				else points += 2;
			});

			points += strlen;
			percentage = Math.round( (points / scale) * 100 );
			if ( percentage > 100 ) percentage = 100;

			$.each( ranges, function( index, range )
			{
				if ( percentage <= range.range )
				{
					rangeindex = index;
					return false;
				}
			});

			var range = ranges[ rangeindex ];
			var loadbar = '<div style="width:171px; border:1px solid #cccccc; padding:1px; background-color:#ffffff; height:5px;"><div style="height:5px; background-color:' + range.color + '; width:' + percentage + '%;"></div></div>'
			element.validatorIndicator('<div style="color:' + range.color + '; margin-bottom:2px;">Password strength: ' + range.text + '</div>' + loadbar);
		}

	};

	$.fn.validator = function( options ) {

		// Default validation settings
		var theform = this;

		var settings = {
			scrollToElement:	theform,
			scrollToError:		true,
			alertMessage:		false,
			autoFocus:			true
		};

		// Extend the settings with the specified options (if any)
		if ( $(options).size() > 0 ) $.extend(settings, options);
		if ( typeof settings.scrollToElement == 'string' )
			settings.scrollToElement = $( (settings.scrollToElement.substr(1, 1) != '#' ? '#' : null) + settings.scrollToElement );

		// If an element other than a form has been specified
		var elements = ( theform.tagName() == 'form' ) ? $(theform).find('input[validate], textarea[validate], select[validate]') : $(theform);
		//if ( theform.tagName() != 'form' ) alert('validator element: ' + theform.tagName());

		var changeFunction = function( event )
		{
			// Retrieve the specified methods
			var element = $(this), methods = $(this).attr('validate').split(','), stopBubble = false;

			if ( event.type == 'keypress' && $.inArray('password', methods) == -1 ) return;

			// If the current element happens to be a selection box
			if ( element.tagName() == 'select' )
			{
				validate.select.call(element);
				return;
			}

			// Loop through each method
			$(methods).each(function()
			{
				// Exit if delegation has been stopped
				if ( stopBubble ) return false;

				// Grab the specified method
				var method = this.toString().toLowerCase();

				// Grab the properties if there are any
				if ( method.indexOf(':') >= 0 )
					var property = method.split(':')[1], method = method.split(':')[0];

				// Exit if no validator was found
				if ( typeof validate[ method ] == 'undefined' ) return;

				// Clear all information bars
				$(element).clearInfo();

				// Execute the validator
				var validationResult = ( typeof property == 'undefined' )
					? validate[ method ].call(element)
					: validate[ method ].call(element, property);

				// Should we call the delgation stop procedure?
				if ( !validationResult ) stopBubble = true;
			});
		};

		// Append all blur events to fields
		elements.change(changeFunction);
		elements.blur(changeFunction);
		elements.keypress(changeFunction);

		// Append the submission event
		$(theform).submit(function()
		{
			//alert('onSubmit: ' + theform.tagName());
			// Do not allow the form submission if there are any validation errors
			if ( !$(theform).validate() )
			{
				// Has an alert message been set?
				if ( typeof settings.alertMessage == 'string' ) alert(settings.alertMessage);
				$('html, body').animate({
					scrollTop: settings.scrollToError
						? $(theform).find('.validate-error:first').position()['top']
						: settings.scrollToElement.position()['top']
				}, 'slow',
					// Callback
					function() {
						// Auto element focus
						if ( settings.autoFocus ) $(theform).find('.validate-error:first').trigger('focus');
					}
				);

				return false;
			}

			return false;
		});

		$(theform).find('input[type="button"]').click(function( event )
		{
			$(theform).validate();
			event.preventDefault();
		});

	};

	$.fn.validate = function()
	{
		//$(this).find('input.validate-error:not([validate*="ajax:"]), select.validate-error:not([validate*="ajax:"])').removeClass('validate-error');
		$(this).find('span.info').clearInfo();

		var element = $(this);
		var elements = ( element.tagName() == 'form' )
			? $(this).find('input[validate], textarea[validate], select[validate]')
			: $(this);

		elements.each(function()
		{
			// Retrieve the specified methods
			var element = $(this), methods = $(this).attr('validate').split(',');

			// If the current element happens to be a selection box
			if ( element.tagName() == 'select' )
			{
				validate.select.call(element);
				return;
			}

			// Loop through each method
			$(methods).each(function()
			{
				// Grab the specified method
				var method = this.toString().toLowerCase();

				// Grab the properties if there are any
				if ( method.indexOf(':') >= 0 )
					var property = method.split(':')[1], method = method.split(':')[0];

				// Exit if no validator was found
				if ( typeof validate[ method ] == 'undefined' ) return;

				// Execute the validator
				var result = ( typeof property == 'undefined' )
					? validate[ method ].call(element)
					: validate[ method ].call(element, property);

				// Remove the error from the element if it was successfull
				if ( result ) element.removeClass('validate-error');
			});
		});

		var search = 'input.validate-error, select.validate-error';
		//alert('Error Fields (' + element.tagName() + '): ' + element.find(search).size() + ' (AJAX Queue: ' + validate.ajaxQueueCounter + ')' );
		return ( element.find(search).size() == 0 && validate.ajaxQueueCounter == 0 ) ? true : false;
	};

})(jQuery);

$(document).ready(function() {
	$('span.info:empty, span.info[value="&nbsp;"]').hide();
});