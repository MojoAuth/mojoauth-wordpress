var mojoauthInterval = setInterval(function () {
    var login = document.getElementById('login');
    if (login != null) {
        clearInterval(mojoauthInterval);

        login.innerHTML = "";
        login.insertAdjacentHTML("afterend", '<div id="mojoauth-passwordless-form"></div>');
     
        var x = document.getElementsByClassName("login");
        var i;
        for (i = 0; i < x.length; i++) {
            x[i].classList.remove("login");
        }

        const mojoauth = new MojoAuth(mojoauthajax.apikey,{
			language: mojoauthajax.language,
			redirect_url: mojoauthajax.redirect,
		});
		if(mojoauthajax.integrate_method == 'otp'){
			mojoauth.signInWithEmailOTP().then(responseHandler)
		}else{
			mojoauth.signInWithMagicLink().then(responseHandler);
		}
		function responseHandler(response){
			if (response.authenticated == true) {
				var data = {
					'action': 'mojoauth_login',
					'mojoauth_token': response.oauth.access_token,
					'mojoauth_email': response.user.identifier      // We pass php values differently!
				};
				// We can also pass the url value separately from ajaxurl for front end AJAX implementations
				mojoAuthAjaxRequest(mojoauthajax, data);
			}
		}
		var mojoAuthAjaxRequest = function(mojoauthajax, data) {
			jQuery.post(mojoauthajax.ajax_url, data)
			.done(function (wpresponse) {
				if(typeof(mjGetQueryParam('redirect_to')) != "undefined"){
					window.location.href = mjGetQueryParam('redirect_to');
				}else{
					window.location.href = mojoauthajax.redirect;
				}
			}).fail( function(xhr, textStatus, errorThrown) {
				mojoAuthAjaxRequest(mojoauthajax,data);
			});
		}
		var mjGetQueryParam = function(param) {
			var found;
			window.location.search.substr(1).split("&").forEach(function(item) {
				if (param ==  item.split("=")[0]) {
					found = decodeURIComponent(item.split("=")[1]);
				}
			});
			return found;
		};
    }
}, 2000)