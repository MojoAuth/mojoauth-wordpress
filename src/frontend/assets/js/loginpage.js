var mjResponseHandler = function (response) {
    if (response.authenticated == true) {
        var data = {
            'action': 'mojoauth_login',
            'mojoauth_token': response.oauth.access_token,
            'mojoauth_identifier': response.user.identifier      // We pass php values differently!
        };
        // We can also pass the url value separately from ajaxurl for front end AJAX implementations
        mjAjaxRequest(mojoauthajax, data);
    }
};
var mjAjaxRequest = function (mojoauthajax, data) {
    jQuery.post(mojoauthajax.ajax_url, data)
            .done(function (wpresponse) {
                wpresponse = JSON.parse(wpresponse);
                if (wpresponse.status == "popup") {
                    //Show Popup and action 
                    mjEmailPopup(data['mojoauth_token'], data['mojoauth_identifier']);
                } else if (wpresponse.status == "error") {
                    document.getElementsByClassName('mj-form-error')[0].innerHTML = wpresponse.message;
                    document.getElementsByClassName('mj-form-error')[0].style.display = "block";
                    document.getElementsByClassName("mj-popup-input-button")[0].disabled = false;
                }else {
                    if(mojoauthajax.success_redirect == "@@samepage@@"){
                        let samePageURL = window.location.href;
                        let params = new URLSearchParams(new URL(samePageURL).search);
                        params.delete('state_id');
                        let newUrl =  samePageURL.slice(0,samePageURL.indexOf('?'));
                        if(params.toString()!==""){
                            newUrl+="?"+params.toString();
                        }
                        window.location.href = newUrl.replace("wp-login.php","");
                    }else{
                        window.location.href = mojoauthajax.success_redirect;
                    }
                }
            }).fail(function (xhr, textStatus, errorThrown) {
        mjAjaxRequest(mojoauthajax, data);
    });
};
var mjGetQueryParam = function (param) {
    var found;
    window.location.search.substr(1).split("&").forEach(function (item) {
        if (param == item.split("=")[0]) {
            found = decodeURIComponent(item.split("=")[1]);
        }
    });
    return found;
};
var mjSubmitForm = function(event){
    event.preventDefault();
    var data = {
        'action': 'mojoauth_login',
        'mojoauth_token': document.getElementById('mj-popup-input-token').value,
        'mojoauth_identifier': document.getElementById('mj-popup-input-identifier').value,     // We pass php values differently!
        'mojoauth_email': document.getElementById('mj-popup-input-email').value
    };
    if((data['mojoauth_token'] != "") && (data['mojoauth_identifier'] != "") && (data['mojoauth_email'] != "")){
        document.getElementsByClassName('mj-form-error')[0].innerHTML = "";
        document.getElementsByClassName('mj-form-error')[0].style.display = "none";
        document.getElementsByClassName("mj-popup-input-button")[0].disabled = true;
        mjAjaxRequest(mojoauthajax, data);
    }else{
        document.getElementsByClassName('mj-form-error')[0].innerHTML = "Email is not valid.";
        document.getElementsByClassName('mj-form-error')[0].style.display = "block";
        document.getElementsByClassName("mj-popup-input-button")[0].disabled = false;
    }
};
var mjEmailPopup = function(token, identifier){
    
    var mjEmailPopupouter = document.createElement('div');
    mjEmailPopupouter.className = "mj-popup";
    mjEmailPopupouter.innerHTML = '<style>    .mj-popup {  z-index: 9999999;    background-color: rgba(0, 0, 0, 0.36);      position: fixed;      inset: 0;      font-family: "Inter", sans-serif;    }    .mj-popup-inner {      border: 0 !important;      max-width: 480px;      margin: 0 auto;      left: 0 !important;      right: 0 !important;      top: 50% !important;      height: -webkit-fit-content;      height: -moz-fit-content;      height: fit-content;      -webkit-transform: translateY(-50%);      transform: translateY(-50%);      position: absolute;      background-color: #fff;      border-radius: 8px;    }    @media (max-width: 768px){      .mj-popup-inner {        margin: 0 16px;      }    }    .mj-popup form {      padding: 32px;    }    .mj-popup .title {      font-size: 20px;      margin: 0;    }    .mj-popup form label {      font-weight: normal;      width: 100%;      display: block;      margin-bottom: 4px;    }    .mj-popup form input[type="email"] {      width: 100%;      border: 1px solid #dde1e6;      margin-bottom: 16px;      padding: 12px 16px;      border-radius: 4px;      box-sizing: border-box;    }    .mj-popup form button {      min-width: 120px;      min-height: 46px;      border-radius: 8px;      padding: 0 32px;      font-weight: 500;      font-size: 18px;      line-height: 46px;      display: flex;      justify-content: center;      width: fit-content;      transition: all 300ms ease-in-out;      border: 1px solid transparent;      background-color: #6929c4;      color: #fff;      border-color: #6929c4;      width: 100%;    }    .popup-header {      display: flex;      align-items: center;      justify-content: space-between;      min-height: 56px;      border-bottom: 1px solid #d1d3d4;      padding: 0 24px;    }.mj-popup-input-button,.mj-popup-close{    cursor: pointer;}.mj-form-error{display:none;color: red;font-size: 12px;transform: translateY(-15px);}</style>';
    var mjEmailPopupInner = document.createElement('div');
    mjEmailPopupInner.className = "mj-popup-inner";

    var mjEmailPopupHeader = document.createElement('div');
    mjEmailPopupHeader.className = "popup-header";
    
    var mjEmailPopupHeaderH2 = document.createElement('h2');
    mjEmailPopupHeaderH2.className = "title";
    
    var mjEmailPopupHeaderClose = document.createElement('a');
    mjEmailPopupHeaderClose.className = 'mj-popup-close';
    mjEmailPopupHeaderClose.innerHTML = '<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-x"><line x1="18" y1="6" x2="6" y2="18"></line><line x1="6" y1="6" x2="18" y2="18"></line></svg>';
    mjEmailPopupHeaderClose.addEventListener('click',function(){
        mjEmailPopupouter.remove();
    });
    
    var mjEmailPopupForm = document.createElement('form');
        mjEmailPopupForm.setAttribute('onsubmit','mjSubmitForm(event)');
    
    var mjEmailPopupEmailControl = document.createElement('div');
    mjEmailPopupEmailControl.className = "mj-form-control";
    
    var mjEmailPopupEmailLabel = document.createElement('label');
    mjEmailPopupEmailLabel.setAttribute("for", "email");
    mjEmailPopupEmailLabel.innerHTML = "Email";
    
    var mjEmailPopupEmailInput = document.createElement('input');
    mjEmailPopupEmailInput.type = "email";
    mjEmailPopupEmailInput.name = "mj-popup-input-email";
    mjEmailPopupEmailInput.id = "mj-popup-input-email";
    
    var mjEmailPopupEmailError = document.createElement('div');
    mjEmailPopupEmailError.className = "mj-form-error";
    
    mjEmailPopupEmailControl.appendChild(mjEmailPopupEmailLabel);
    mjEmailPopupEmailControl.appendChild(mjEmailPopupEmailInput);
    mjEmailPopupEmailControl.appendChild(mjEmailPopupEmailError);
    
    
    var mjEmailPopupTokenInput = document.createElement('input');
    mjEmailPopupTokenInput.id = "mj-popup-input-token";
    mjEmailPopupTokenInput.type = "hidden";
    mjEmailPopupTokenInput.value = token;
    
    var mjEmailPopupIdentifierInput = document.createElement('input');
    mjEmailPopupIdentifierInput.id = "mj-popup-input-identifier";
    mjEmailPopupIdentifierInput.type = "hidden";
    mjEmailPopupIdentifierInput.value = identifier;
    
    var mjEmailPopupButtonControl = document.createElement('div');
    mjEmailPopupButtonControl.className = "mj-form-control";
    
    var mjEmailPopupEmailButton = document.createElement('button');
    mjEmailPopupEmailButton.className = "mj-popup-input-button";
    mjEmailPopupEmailButton.innerHTML = "Submit";
    mjEmailPopupEmailButton.type = "submit";
    
    mjEmailPopupButtonControl.appendChild(mjEmailPopupEmailButton);
    
    mjEmailPopupHeader.appendChild(mjEmailPopupHeaderH2); 
    mjEmailPopupHeader.appendChild(mjEmailPopupHeaderClose); 
    
    mjEmailPopupInner.appendChild(mjEmailPopupHeader);  
    mjEmailPopupForm.appendChild(mjEmailPopupEmailControl); 
    mjEmailPopupForm.appendChild(mjEmailPopupTokenInput); 
    mjEmailPopupForm.appendChild(mjEmailPopupIdentifierInput); 
    
    mjEmailPopupForm.appendChild(mjEmailPopupButtonControl); 
    mjEmailPopupInner.appendChild(mjEmailPopupForm);
    mjEmailPopupouter.appendChild(mjEmailPopupInner);
    document.body.appendChild(mjEmailPopupouter);
};

var mojoauthInterval = setInterval(function () {
    var login = document.getElementById('login');
    if (login != null) {
        clearInterval(mojoauthInterval);

        login.innerHTML = '<style type="text/css">#login{display:none;}#mojoauth-passwordless-form {margin: 0% auto;width: 405px;}</style>';
        login.insertAdjacentHTML("afterend", '<div id="mojoauth-passwordless-form"></div>');

        var x = document.getElementsByClassName("login");
        var i;
        for (i = 0; i < x.length; i++) {
            x[i].classList.remove("login");
        }
        var mojoauthOptions = {
            language: mojoauthajax.language,
            redirect_url: (mojoauthajax.success_redirect == "@@samepage@@")?window.location.href:mojoauthajax.redirect,
            source: []
        };
        if (mojoauthajax.integrate_method.sms == "sms") {
            mojoauthOptions.source.push({type: "phone", feature: "otp"});
        }
        if (mojoauthajax.integrate_method.email == 'otp') {
            mojoauthOptions.source.push({type: "email", feature: "otp"});
        } else if (mojoauthajax.integrate_method.email == 'magiclink') {
            mojoauthOptions.source.push({type: "email", feature: "magiclink"});
        }
        const mojoauth = new MojoAuth(mojoauthajax.apikey, mojoauthOptions);
        mojoauth.signIn().then(mjResponseHandler);
    }
}, 10);