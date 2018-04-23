$(function (){
   $('select[name=article_theme]').on('change', function (){
      location.href = $(this).val();
   });


});

