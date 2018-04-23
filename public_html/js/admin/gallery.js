$(function (){
   $('[name=type]').on('change', function (){
      location.href = '?act=ch_type&type=' + $(this).val();
   })
});
