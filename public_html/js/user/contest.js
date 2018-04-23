$(function (){
   function getTempFiles(){
      $('#files').load('/ajax/get-temp-files', function (data){
         // удаление
         $('.del_file').on('click', function (){
            $.post('/ajax/delete-temp-file', {id: $(this).data('id')}, getTempFiles);
         });
         // чекбокс для галереи
         if(data == '') $('#agree-div').hide();
         else $('#agree-div').show();
         // картинки
         $('.tmp_img').on('click', function (){
            $('body').append('<div id="modal_fon"></div>' +
               '<div id="modal_win"><img src=""></div>');
            var m = $('#modal_fon, #modal_win');
            m.hide();
            $('#modal_win').find('img').prop('src', $(this).data('src'))
               .on('load', function (){
                  m.show().on('click', function (){
                     m.remove();
                  });
                  sizeWin();
               });
         })
      });
   }

   // бегунок
   var kind = getCookie('kind');
   if(kind == null) kind = 1;

   $('.kind_item').on('click', function(){
      var t = $(this),
         id = t.data('id');
      t.addClass('active').siblings().removeClass('active');
      $('#slider_img').css('top', 11 + (id - 1) * 58);
      $('#kind_hidd').val(id == 1? 'monthly': (id == 2? 'fast': 'urgent'));
      //сохраняем к кукисах
      var d = new Date;
      d.setDate(d.getDate() + 365);
      document.cookie = 'kind=' + id + '; path=/; expires=' + d.toString();
   }).siblings('[data-id=' + kind + ']').click();

   // добавить линк
   $('#add-link').on('click', function (){
      var t = $(this),
         last = t.prev('input'),
         n = last.data('n');
      n++;
      if(last.val() != '')
         last.after(
            '<input type="url" data-n="' + n + '" name="link[]">'
         );
   });

   // выбор бланка диплома
   $('.blanks').on('click', function (){
      var t = $(this);
      $('#blank_hidd').val(t.data('id'));
      $('.blanks').removeClass('active');
      t.addClass('active');
   }).first().click();

   // загрузка файла
   $('#load_button').uploadifive({
      auto: true,
      buttonText: 'Загрузить файл',
      buttonClass: 'button',
      width: 200,
      //multi: true,
      fileType: false,
      //dnd: true,
      fileObjName: 'file',
      queueID: "queue",
      fileSizeLimit: '8000',
      uploadScript: '/ajax/upload-temp-files',
      onSelect: function (){

      },
      onUploadComplete: function (file, data){
         var d = eval('(' + data + ')');
         if(d.result == 'error'){
            //showMessages(d.message, 'error');
         }
         else{
            //showMessages('Файл благополучно загружен', 'info');
            getTempFiles();

         }
      },
      onQueueComplete: function (){
         $('#queue').html('');
      }
   });

   getTempFiles();

   // оригинал диплома
   var orig = $('[name=original]');
   orig.on('change', function (){
      if($(this).is(':checked')) $('#orig-text').show();
      else $('#orig-text').hide();
   });
   if(orig.is(':checked')) orig.click();

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
