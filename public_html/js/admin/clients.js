$(function (){
   $('#search_email').on('change', function (){
      location.href = '?act=search_email&email=' + $(this).val();
   });
});