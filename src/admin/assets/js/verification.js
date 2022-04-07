jQuery(document).ready(function ($) {
	$('#mojoauthloginformshortcodeeditor').click(function(){
		var input = document.getElementById('mojoauthloginformshortcodeeditor');
		  input.focus();
		  input.select();
		document.execCommand('copy');
		$('#mojoauthloginformshortcodemessage').remove();
		$("<div id='mojoauthloginformshortcodemessage'>Copied!</div>").insertAfter(input);
		setTimeout(function(){
			$('#mojoauthloginformshortcodemessage').remove();
		},5000);
	});
	$('#mojoauthloginformshortcodephp').click(function(){
		var input = document.getElementById('mojoauthloginformshortcodephp');
		  input.focus();
		  input.select();
		document.execCommand('copy');
		$('#mojoauthloginformshortcodemessage').remove();
		$("<div id='mojoauthloginformshortcodemessage'>Copied!</div>").insertAfter(input);
		setTimeout(function(){
			$('#mojoauthloginformshortcodemessage').remove();
		},5000);
	});
    $('.mojoauth_verification').click(function () {
        $(this).html('Loading');
        $('.mojoauth_verification').attr('disabled','disabled');
        var count = 1;
        var validationLoading = setInterval(() => {
            if(count%4 == 0){
                $(this).html('Loading');
            }
            $('.mojoauth_verification').append('.');
        }, 500);
        var data = {
            'action': 'mojoauth_verification',
            'mojoauth_apikey': $('#mojoauth_apikey').val()
        };
        $.post(mojoauthadminajax.ajax_url, data, function (wpresponse) {
            wpresponse = JSON.parse(JSON.parse(wpresponse).response);
            clearInterval(validationLoading);
            $('.mojoauth_verification').html('Get Public Certificate');
            $('.mojoauth_verification').removeAttr('disabled');
            if(wpresponse.data){
                $('#mojoauth_public_key').val(wpresponse.data);
            }else{
                $('#mojoauth_public_key').val('');
                $('.mojoauth_verification_message').html(wpresponse.description).show('slow');
                setTimeout(function(){
                    $('.mojoauth_verification_message').hide('slow');
                },20000);
            }
        });
    });
    if($('#mojoauth_apikey').val() != ""){
        $('.mojoauth_verification').removeAttr('disabled');
    }else{
        $('.mojoauth_verification').attr('disabled','disabled');
    }
    $('#mojoauth_apikey').on("keyup change", function(){
        if($(this).val() != ""){
            $('.mojoauth_verification').removeAttr('disabled');
        }else{
            $('.mojoauth_verification').attr('disabled','disabled');
        }
    });
});