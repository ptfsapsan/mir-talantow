$(function (){
   $('.comment').on('blur', function (){
        var t = $(this);
        location.href = '?act=ch_text&id=' + t.data('id') + '&text=' + t.val();
   });
   
   $('[name=active]').on('change', function (){
      location.href = '?act=ch_active&id=' + $(this).data('id');
   });
});