function getSiteImages(type){
   $('#file_' + type).load('/ajax/get-site-images', {type: type});
}




$(function(){

   // загрузка логотипа
   $('#load_button_logo').uploadifive({
      auto: true,
      buttonText: 'Загрузить логотип',
      buttonClass: 'button',
      width: 200,
      fileType: false,
      fileObjName: 'file',
      queueID: "queue_logo",
      fileSizeLimit: '8000',
      uploadScript: '/ajax/upload-site-image?type=logo',
      onUploadComplete: function (file, data){
         var d = eval('(' + data + ')');
         if(d.result == 'error'){
            $('#messages_fon').prepend('<div class="alert alert-danger">' +
               d.message + '</div>');
            showAlert();
         }
         else{
            $('#messages_fon').prepend('<div class="alert alert-info">' +
               'Файл благополучно загружен</div>');
            showAlert();
            getSiteImages('logo');

         }
      },
      onQueueComplete: function (){
         $('#queue_logo').html('');
      }
   });

   // загрузка логотипа
   $('#load_button_head').uploadifive({
      auto: true,
      buttonText: 'Загрузить в шапку',
      buttonClass: 'button',
      width: 200,
      fileType: false,
      fileObjName: 'file',
      queueID: "queue_head",
      fileSizeLimit: '8000',
      uploadScript: '/ajax/upload-site-image?type=head',
      onUploadComplete: function (file, data){
         var d = eval('(' + data + ')');
         if(d.result == 'error'){
            $('#messages_fon').prepend('<div class="alert alert-danger">' +
               d.message + '</div>');
            showAlert();
         }
         else{
            $('#messages_fon').prepend('<div class="alert alert-info">' +
               'Файл благополучно загружен</div>');
            showAlert();
            getSiteImages('head');

         }
      },
      onQueueComplete: function (){
         $('#queue_head').html('');
      }
   });

   getSiteImages('logo');
   getSiteImages('head');

});
