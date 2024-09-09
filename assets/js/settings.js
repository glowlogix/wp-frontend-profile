document.addEventListener('DOMContentLoaded', function() {
    var recaptchaLogin = document.getElementById('wpfep_general[enable_captcha_login]');
    var recaptchaRegistration = document.getElementById('wpfep_general[enable_captcha_registration]');
    var hcaptchaLogin = document.getElementById('wpfep_general[enable_hcaptcha_login]');
    var hcaptchaRegistration = document.getElementById('wpfep_general[enable_hcaptcha_registration]');

    

    function handleRecaptchaChange() {
        if (recaptchaLogin.checked || recaptchaRegistration.checked) {
            if (hcaptchaLogin.checked || hcaptchaRegistration.checked) {
                alert('You are using hCAPTCHA. Please disable hCAPTCHA first.');
                recaptchaLogin.checked = false;
                recaptchaRegistration.checked = false;
            }
        }
        alertCheckboxState();
    }

    function handleHcaptchaChange() {
        if (hcaptchaLogin.checked || hcaptchaRegistration.checked) {
            if (recaptchaLogin.checked || recaptchaRegistration.checked) {
                alert('You are using reCAPTCHA. Please disable reCAPTCHA first.');
                hcaptchaLogin.checked = false;
                hcaptchaRegistration.checked = false;
            }
        }
    }

    recaptchaLogin.addEventListener('change', handleRecaptchaChange);
    recaptchaRegistration.addEventListener('change', handleRecaptchaChange);
    hcaptchaLogin.addEventListener('change', handleHcaptchaChange);
    hcaptchaRegistration.addEventListener('change', handleHcaptchaChange);
});