$(function(){
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
      preview_styles: 'font-size color'
   });

});