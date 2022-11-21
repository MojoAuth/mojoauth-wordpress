jQuery(document).ready(function ($) {
    $('#mojoauthloginformshortcodeeditor').click(function () {
        var input = document.getElementById('mojoauthloginformshortcodeeditor');
        input.focus();
        input.select();
        document.execCommand('copy');
        $('#mojoauthloginformshortcodemessage').remove();
        $("<div id='mojoauthloginformshortcodemessage'>Copied!</div>").insertAfter(input);
        setTimeout(function () {
            $('#mojoauthloginformshortcodemessage').remove();
        }, 5000);
    });
    $('#mojoauthloginformshortcodephp').click(function () {
        var input = document.getElementById('mojoauthloginformshortcodephp');
        input.focus();
        input.select();
        document.execCommand('copy');
        $('#mojoauthloginformshortcodemessage').remove();
        $("<div id='mojoauthloginformshortcodemessage'>Copied!</div>").insertAfter(input);
        setTimeout(function () {
            $('#mojoauthloginformshortcodemessage').remove();
        }, 5000);
    });
    $('.mojoauth_verification').click(function () {
        $(this).html('Verifying ');
        $('.mojoauth_verification').attr('disabled', 'disabled');
        var count = 1;
        var validationLoading = setInterval(() => {
            if (count % 5 == 0) {
                $(this).html('Verifying ');
            }
            $('.mojoauth_verification').append('.');
            count++;
        }, 500);
        var data = {
            'action': 'mojoauth_verification',
            'mojoauth_apikey': $('#mojoauth_apikey').val()
        };
        $.post(mojoauthadminajax.ajax_url, data, function (wpresponse) {
            wpresponse = JSON.parse(JSON.parse(wpresponse).response);
            if (wpresponse.data) {
                $('#mojoauth_public_key').val(wpresponse.data);
                $.post(mojoauthadminajax.ajax_url, {
                    'action': 'mojoauth_get_language',
                    'mojoauth_apikey': $('#mojoauth_apikey').val()
                }, function (lanresponse) {
                    lanresponse = JSON.parse(lanresponse);
                    var availableLanguages = JSON.parse(lanresponse.response).data.languages_available;
                    for (var key of Object.keys(availableLanguages)) {
                        for (var langCode of Object.keys(availableLanguages[key])) {
                            var langHTML = '<option value="' + langCode + '"';
                            if (lanresponse.lang == langCode) {
                                langHTML += 'selected="selected"';
                            }
                            langHTML += '>' + availableLanguages[key][langCode] + '</option>';
                            $('#mojoauth_language').append(langHTML);
                        }
                    }
                    $('.mojoauth_active').slideDown();
                    clearInterval(validationLoading);
                    $('.mojoauth_verification').html('Verify');
                    $('.mojoauth_verification').removeAttr('disabled');
                    if ($('#mojoauth_integrate_method_email').prop('checked')) {
                        $('#mojoauth_integrate_method_email_active').slideDown();
                    } else {
                        $('#mojoauth_integrate_method_email_active').slideUp();
                    }
                    if($('#mojoauth_login_redirection').val() == "@@other@@"){
                        $('#mojoauth_login_redirection_other').parent().show();
                    }else{
                        $('#mojoauth_login_redirection_other').parent().hide();
                    }
                });
            } else {
                clearInterval(validationLoading);
                $('.mojoauth_active').hide();
                $('#mojoauth_public_key').val('');
                $('.mojoauth_verification_message').html(wpresponse.description).slideDown();
                $('.mojoauth_verification').html('Verify');
                $('.mojoauth_verification').removeAttr('disabled');
                setTimeout(function () {
                    $('.mojoauth_verification_message').slideUp();
                }, 20000);
            }
        });
    });
    $('#mojoauth_apikey').on("keyup change", function () {
        if ($(this).val() != "") {
            $('.mojoauth_verification').removeAttr('disabled');
        } else {
            $('.mojoauth_verification').attr('disabled', 'disabled');
        }
    });
    if ($('#mojoauth_apikey').val() != "") {
        $('.mojoauth_verification').click();
    }
    $('#mojoauth_integrate_method_email').click(function () {
        if ($(this).prop('checked')) {
            $('#mojoauth_integrate_method_email_active').slideDown();
        } else {
            $('#mojoauth_integrate_method_email_active').slideUp();
        }
    });
    $('#mojoauth_login_redirection').on("change",function(){
        if($('#mojoauth_login_redirection').val() == "@@other@@"){
            $('#mojoauth_login_redirection_other').parent().show();
        }else{
            $('#mojoauth_login_redirection_other').parent().hide();
        }
    })
});