var base_dir = window.location.pathname.split('/').slice(0, -1).join('/');

// jQuery plugin to prevent double submission of forms
jQuery.fn.preventDoubleSubmission = function () {
	$(this).on('submit', function (e) {
		var $form = $(this);

		if ($form.data('submitted') === true) {
			// Previously submitted - don't submit again
			e.preventDefault();
		} else {
			// Mark it so that the next submit can be ignored
			$form.data('submitted', true);
		}
	});

	// Keep chainability
	return this;
};

// chat scrolling
function scrollHeight() { return $(document).height() - $(window).height() }
function isScrolledToBottom() { return $('html')[0].scrollTop == scrollHeight() }
function scrollToBottom() { $('html')[0].scrollTop = scrollHeight() }

// message listing
function listMessages(scrollFlag) { $('#message_log').load(base_dir + '/?location=/chat/list', function () { if (scrollFlag) scrollToBottom() }) }
function periodicallyListMessagesCallback() { var scrollFlag = isScrolledToBottom(); listMessages(scrollFlag) }

// on ready
$(function () {

	// stay updated
	setInterval(periodicallyListMessagesCallback, 1000) // get messages periodically

	// allow message sending
	$('form#send_message_form').preventDoubleSubmission();
	$('form#send_message_form').submit(function (e) {
		e.preventDefault() // prevent page reload

		var input = $('input[name=message_text]');
		if (input.val() == '') return false // no blank message allowed
		var serialized_form = $('form#send_message_form').serialize();
		input.prop('disabled', true) // disable input on submit

		// send
		$.post(base_dir + '/?location=/chat/send', serialized_form) // send message
			.done(function () {
				input.val('')
			})
			.fail(function () { // error handling
				alert('Error! (when sending a message)')
			})
			.always(function () {
				input.prop('disabled', false) // restore input after submit
				input.focus()
			})
		return false
	})
})
