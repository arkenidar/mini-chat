// jQuery plugin to prevent double submission of forms
jQuery.fn.preventDoubleSubmission = function() {
  $(this).on('submit',function(e){
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
function scrollHeight(){ return $(document).height()-$(window).height() }
function isScrolledToBottom(){ var h = scrollHeight(); return $("body")[0].scrollTop==h }
function scrollToBottom(){ $("body")[0].scrollTop = scrollHeight() }

// message listing
function listMessages(scrollFlag){ $("#message_log").load("/?location=/chat/list", function(){ if(scrollFlag) scrollToBottom() } ) }
function periodicallyListMessagesCallback(){ var scrollFlag = isScrolledToBottom(); listMessages(scrollFlag) }

// on ready
$(function(){
	
	// stay updated
	setInterval(periodicallyListMessagesCallback, 1000) // get messages periodically

	// allow message sending
	$("form#send_message_form").preventDoubleSubmission();
	$("form#send_message_form").submit(function(e){
		e.preventDefault() // prevent page reload

		var input = $("input[name=message_text]");
		if(input.val()=="") return false // no blank message allowed
		var serialized_form = $("form#send_message_form").serialize();
		input.prop("disabled", true) // disable input on submit

		// send
		$.post("/?location=/chat/send", serialized_form) // send message
		.done(function(){ 
			input.val("")
		})
		.fail(function(){ // error handling
			alert("network error, retry...")
		})
		.always(function(){
			input.prop("disabled", false) // restore input after submit
			input.focus()
		})
		return false
	})
}) 
