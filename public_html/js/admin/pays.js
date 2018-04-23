jQuery(document).ready(function (){
   var $j = jQuery.noConflict(),
      from = Calendar.setup({
         dateFormat: "%d.%m.%Y %H:%M",
         trigger: "date",
         inputField: "date",
         showTime: true,
         minuteStep: 1,
         onSelect: function (){
            this.hide();
         }
      });

   $j('[name=order_id]').on('change', function (){
      $j('#order_price').val($j(this).find('option:selected').data('price'));
   }).change();


});