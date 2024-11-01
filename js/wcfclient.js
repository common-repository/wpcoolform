




var exform = document.getElementById('wcf_submit');
if (exform != null) {
    exform.one('click', function () {
        submitForm();
    });
}

function resetForm() {
    var myform = document.getElementById("wcf_user_form");
    myform.reset();
    return false;
}

var theResponse;
function submitForm() {
    var myform = document.getElementById("wcf_user_form");
    var fd = new FormData(myform);
    jQuery.ajax({
        url: WCFAjax.ajaxurl,
        data: fd,
        cache: false,
        processData: false,
        contentType: false,
        type: 'POST',
        success: function (response) {
            obj = JSON.parse(response);
            redirect = obj.redirect;
            if (redirect == 'false') {
                newCaptcha = obj.wcf_captcha;
                newSession = obj.wcf_session;
                message = obj.message;
                var capy = document.getElementById("wcf_captcha");
                capy.innerHTML = newCaptcha;
                var sess = document.getElementById("wcf_session");
                sess.value = newSession;
                var cm = document.getElementById("wcf_captcha_message");
                cm.innerHTML = message;
            } else if (validateURL(redirect)) {
                window.location = redirect;
            }
        }
    });
    return false;
}

function validateURL(str) {
  var regex = /(http|https):\/\/(\w+:{0,1}\w*)?(\S+)(:[0-9]+)?(\/|\/([\w#!:.?+=&%!\-\/]))?/;
  if(!regex .test(str)) {
    return false;
  } else {
    return true;
  }
}