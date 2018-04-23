$(function (){
   $('.thumb_img').on('click', function (){
      var t = $(this),
         but = $('#load_but'),
         big_img = $('#big_img');
      switch(t.data('type')){
         case 'link':
            big_img.prop('src', '/image/html.png');
            but.html('Перейти').prop('href', t.data('link')).show();
            break;
         case 'file':
            big_img.prop('src', t.data('src'));
            but.html('Скачать').
            prop('href', '/load-file/' + t.data('id')).show();
            break;
         case 'empty':
            big_img.prop('src', t.prop('src'));
            but.hide();
            break;
      }

      $('.thumb_img').css({'border': '1px #c6c6c6 solid', 'boxShadow': 'none'});
      t.css({'border': '1px #00f solid',
         'boxShadow': '0 0 3px 3px rgba(0, 0, 0, 0.2)'});
   }).first().click();

});