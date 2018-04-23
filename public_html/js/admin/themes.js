$(function (){
   $('.tt_input, .tt_textarea').on('change', function (){
      var t = $(this),
          p = t.parent().parent('tr'),
          id = p.data('id');
      location.href = '?act=change&id=' + id + '&title=' + p.find('.tt_input').val() +
         '&text=' + p.find('.tt_textarea').val();
   });
});