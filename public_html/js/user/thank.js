$(function (){

   // выбор бланка диплома
   $('.blanks').on('click', function (){
      var t = $(this);
      $('#blank_hidd').val(t.data('id'));
      $('.blanks').removeClass('active');
      t.addClass('active');
   }).first().click();


   // проверка форм
   $('[name=executor_name], [name=email]').on('keyup', function(){
      var t = $(this),
         str = t.val();
      if(str.length > 100) t.val(str.substr(0, 100));
   });
   $('[name=organization_name], [name=organization_address], [name=work_title]')
      .on('keyup', function(){
         var t = $(this),
            str = t.val();
         if(str.length > 200) t.val(str.substr(0, 200));
      });

   // прокрутка
   $('body, html').animate({'scrollTop': 0}, 0);



});
