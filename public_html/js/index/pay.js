$(function (){
   // проверка заявки
   $('#ver_order').on('click', function(){
      $('#ver_order').hide();
      $('#ver_result').html('');
      $.post('/ajax/verify-order', {
         id: $('#order_id_ver').val(),
         email: $('#email_ver').val()
      }, function (data){
         $('#ver_result').html(data);
         $('#ver_order').show();
      })
   });


   // работа с Webmoney
   if(vars.debt != '0') $('.wmbtn').show();
   $('#wm_order_id, #wm_email').on('change', function (){
      $.post('/ajax/get-order-debt', {
         id: $('#wm_order_id').val(),
         email: $('#wm_email').val()
      }, function (data){
         var is_ok = data != 'error';
         $('.wmwidget-sum span').html(is_ok? data + ' рублей': '');
         $('[name=LMI_PAYMENT_AMOUNT]').val(is_ok? data: '');
         if(is_ok) $('.wmbtn').show();
         else $('.wmbtn').hide();
      });
   })


});