jQuery(document).ready(function (){
   var $j = jQuery.noConflict();

   $j('[name=type]').on('click', function (){
      location.href = '?act=ch_session&k=type&v=' + $j(this).val();
   })



});
