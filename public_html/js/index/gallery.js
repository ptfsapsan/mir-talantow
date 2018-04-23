$(function (){
   var t;
   function loadGallery(){
      $.post('/ajax/get-gallery', {
         type: type,
         nomination: nomination,
         theme: theme,
         page: page
      }, function (data){
         $('#loading').hide();
         if(data == 'end') return;

         gallery_field.append(data);
         page++;
         scrollFlag = true;

         // открываем картинку
         $('.img_preview').on('click', function (){
            t = $(this);
            $('body').append('<div id="modal_fon"></div>' +
               '<div id="modal_win"><img src=""></div>');
            var m = $('#modal_fon, #modal_win');
            m.hide();
            $('#modal_win').find('img').prop('src', t.data('src'))
               .on('load', function (){
                  m.show().on('click', function (){
                     m.remove();
                  });
                  sizeWin();
               });
            t.siblings('p').find('span').load('/ajax/increment-view-img',
               {id: t.data('id')});
         });

         // открываем файл
         $('.file_con').on('click', function (){
            t = $(this);
            location.href = '/file-content/' + t.data('id');
         })

      });
   }


   var type = vars.type,
      nomination = 0,
      theme = 0,
      page = 1,
      gallery_field = $('#gallery_field'),
      scrollFlag = true;

   loadGallery();
   $('[name=nomination]').on('change', function (){
      nomination = $(this).val();
      page = 1;
      gallery_field.html('');
      loadGallery();
   });
   $('[name=theme]').on('change', function (){
      theme = $(this).val();
      page = 1;
      gallery_field.html('');
      loadGallery();
   });

   $(document).scroll(function (){
      if($(document).height() - $(window).height() - $(window).scrollTop() <= 150){
         if(scrollFlag){
            scrollFlag = false;
            $('#loading').show();
            loadGallery();
         }
      }
   });


   $(window).on('resize', sizeWin);

});