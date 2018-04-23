function RecaptchaInit(){
   switch(vars.type){
      case 'registration':
         grecaptcha.render($('#recaptcha1')[0], {sitekey: vars.captcha_key});
         break;
      case 'forgot':
         grecaptcha.render($('#recaptcha2')[0], {sitekey: vars.captcha_key});
         break;
   }
}
