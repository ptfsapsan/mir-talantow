$(function (){
   $('.can-toggle input[type=checkbox]').on('click', function (){
      var t = $(this);
      $.post('/ajax/set-active-blog-article',
         {id: t.data('id'), active: t.is(':checked')? 'yes': 'no'});
   })
});