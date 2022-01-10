var mojoauthInterval = setInterval(function () {
    var login = document.getElementById('login');
    if (login != null) {
        clearInterval(mojoauthInterval);

        login.innerHTML = "";
        login.insertAdjacentHTML("afterend", '<div id="mojoauth-passwordless-form"></div>');
        //login.style.display = "block";


        var x = document.getElementsByClassName("login");
        var i;
        for (i = 0; i < x.length; i++) {
            x[i].classList.remove("login");
        }


        const mojoauth = new MojoAuth(mojoauthajax.apikey,{
			language: mojoauthajax.language,
			redirect_url: mojoauthajax.redirect,
		});
        mojoauth.signInWithMagicLink().then(response => {
			if (response.authenticated == true) {
                    var data = {
                        'action': 'mojoauth_login',
                        'mojoauth_token': response.oauth.access_token,
                        'mojoauth_email': response.user.identifier      // We pass php values differently!
                    };
                    // We can also pass the url value separately from ajaxurl for front end AJAX implementations
					setInterval(function(){
						jQuery.post(mojoauthajax.ajax_url, data, function (wpresponse) {
							window.location.href = mojoauthajax.redirect
						});
					},2000);
                }
		});
    }
}, 200)