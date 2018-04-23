jQuery(document).ready(function (){
   var $j = jQuery.noConflict(),
      from = Calendar.setup({
         dateFormat: "%d.%m.%Y",
         trigger: "from",
         inputField: "from",
         onSelect: function (){
            this.hide();
            location.href = '?act=ch_session&p=from&v=' + $j('#from').val();
         }
      }),
      to = Calendar.setup({
         dateFormat: "%d.%m.%Y",
         trigger: "to",
         inputField: "to",
         onSelect: function (){
            this.hide();
            location.href = '?act=ch_session&p=to&v=' + $j('#to').val();
         }
      });

   $j('#type, #kind, #status, #search, #nomination, #theme')
      .on('change', function (){
      var t = $j(this);
      location.href = '?act=ch_session&p=' + t.prop('id') + '&v=' + t.val();
   });
   $j('#paid, #unpaid').on('click', function (){
      var t = $j(this),
         v = t.is(':checked')? '1': '';
      location.href = '?act=ch_session_checkbox&p=' + t.prop('id') + '&v=' + v;
   });

   // просмотр картинок
   if(!$j('body').is('#modal_fon'))
      $j('body').prepend('<div id="modal_fon"></div>' +
         '<div id="modal_win"><img src=""></div>');
   $j('.imgs').on('click', function (){
      var m = $j('#modal_fon, #modal_win');
      m.hide();
      $j('#modal_win img').prop('src', $j(this).data('src'))
         .on('load', function (){
            m.show().on('click', function (){
               m.hide();
            });
            sizeWin();
         });
   });

   // смена статуса заявки
   $j('.ch_stat').on('change', function (){
      var t = $j(this);
      location.href = '?act=ch_status&id=' + t.data('id') +
         '&status=' + t.val();
   });

   // все дипломанты
    $j('#all_dip').on('click', function () {
        location.href = '?act=all_dip';
    });

});