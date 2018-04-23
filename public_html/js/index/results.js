$(function (){
   function loadResult(){
      $.post('/ajax/get-results', {
         type: type,
         kind: kind,
         nomination: nomination,
         theme: theme,
         page: page
      }, function (data){
         $('#loading').hide();
         if(data == 'end') return;

         results_field.append(data);
         page++;
         scrollFlag = true;
         $('#results_field tr').off('click').on('click', viewDiploma);
      });
   }

   function viewDiploma(){
      var alert = $('#view_dip'),
         fon = $('#messages_fon'),
         close = fon.find('img'),
         t = $(this),
         color = t.css('background-color'),
         btn = $('#view_dip_but');
      fon.show();
      var win = $(window),
         w_win = win.width(),
         h_win = win.height(),
         w_al = alert.width(),
         h_al = alert.height();
      alert.css({width: w_al, height: h_al,
         top: (h_win - h_al) / 2, left: (w_win - w_al) / 2 - 20});
      fon.css({width: w_win, height: h_win});
      close.css({
         top: (h_win - h_al) / 2 + 2,
         left: (w_win + w_al) / 2 + 2
      });
      t.css('background-color', '#8fe573');

      close.off('click').on('click', function(){
         fon.hide();
         t.css('background-color', color);
      });

      btn.off('click').on('click', function (){
         $.post('/ajax/to-diploma',
            {id: t.data('id'), email: btn.prev().val()}, function (data){
               if(data == 'empty'){
                  close.click();
               }
               else{
                  location.href = '/diplom/' + data;
               }
            });
      })
   }
   //$(window).on('resize', viewDiploma);


   var type = vars.type,
      kind = vars.kind,
      nomination = 0,
      theme = 0,
      page = 1,
      results_field = $('#results_field'),
      scrollFlag = true;
   
   loadResult();


   $('[name=nomination]').on('change', function (){
      nomination = $(this).val();
      page = 1;
      results_field.html('');
      loadResult();
   });

   $('[name=theme]').on('change', function (){
      theme = $(this).val();
      page = 1;
      results_field.html('');
      loadResult();
   });

   $('#messages_fon').prepend('<div id="view_dip">' +
      '<div>Для перехода на страницу диплома введите электронный адрес,' +
      ' указанный в заявке</div>' +
      '<input type="email">' +
      ' <input type="button" value="Перейти к диплому" id="view_dip_but">' +
      '</div>').hide();

   $(document).scroll(function(){
      if($(document).height() - $(window).height() - $(window).scrollTop() <=  150){
         if(scrollFlag){
            scrollFlag = false;
            $('#loading').show();
            loadResult();
         }
      }
   });

});