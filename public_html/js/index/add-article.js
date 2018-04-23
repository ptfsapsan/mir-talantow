$(function (){
   $('select[name=article_theme]').on('change', function (){
      location.href = $(this).val();
   });


   $('[name=original]').on('change', function (){
      if($(this).is(':checked')) $('#orig-text').show();
      else $('#orig-text').hide();
   }).change();

   if($('textarea[name=text]').length){
      tinymce.init({
         selector: 'textarea[name=text]',
         height: 500,
         menubar: false,
         plugins: [
            'advlist lists charmap preview searchreplace visualblocks code' +
            ' fullscreen media table contextmenu paste textcolor colorpicker table'
         ],
         toolbar: 'undo redo | bold italic underline | ' +
         ' alignleft aligncenter alignright alignjustify | visualblocks ' +
         ' copy paste cut searchreplace | forecolor backcolor | ' +
         'bullist numlist outdent indent | media | charmap' +
         ' | table | preview fullscreen',
         content_css: [
            '/css/tinymce/codepen.min.css',
            '/css/tinymce/fonts.css'
         ],
         language: 'ru',
         preview_styles: 'font-size color'//,
         // moxiemanager_image_settings: {
         //    /* Scope to different folder, show thumbnails of selected extensions */
         //    title : 'Images',
         //    extensions : 'jpg,png,gif',
         //    rootpath : '/testfiles/testfolder',
         //    view : 'thumbs'
         // }
      });
   }

   $('.blanks').on('click', function (){
      var t = $(this);
      t.addClass('active').siblings().removeClass('active');
      $('[name=certificate_id]').val(t.data('id'));
   }).first().click();

});

