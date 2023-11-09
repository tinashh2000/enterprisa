function dragDropZone(elId, backend, handlers=null, limit=10000000) {
  $(elId).dmUploader({ //
    url: backend,
    maxFileSize: limit,
    extraData: function () {
      if (handlers != null && typeof handlers.getExtraData == "function") {
        return handlers.getExtraData();
      }
      return {}
    },
    onDragEnter: function(){
      // Happens when dragging something over the DnD area
      this.addClass('active');
    },
    onDragLeave: function(){
      // Happens when dragging something OUT of the DnD area
      this.removeClass('active');
    },
    onInit: function(){
      // Plugin is ready to use
      // ui_add_log('Penguin initialized :)', 'info');
    },
    onComplete: function(){
      if (handlers != null && typeof handlers.onComplete == "function") {
        handlers.onComplete();
      }
      // All files in the queue are processed (success or error)
      // ui_add_log('All pending tranfers finished');
    },
    onNewFile: function(id, file){
      // $(elId).dmUploader({});
      if (handlers != null && typeof handlers.onNewFile == "function") {
        if (!handlers.onNewFile()) return false;
      }
      // When a new file is added using the file selector or the DnD area
      // ui_add_log('New file added #' + id);
      ui_multi_add_file(id, file);
      return true;
    },
    onBeforeUpload: function(id){
      ui_multi_update_file_status(id, 'uploading', 'Uploading...');
      ui_multi_update_file_progress(id, 0, '', true);
    },
    onUploadCanceled: function(id) {
      if (handlers != null && typeof handlers.onUploadCancelled == "function") {
        handlers.onUploadCancelled();
      }
      // Happens when a file is directly canceled by the user.
      ui_multi_update_file_status(id, 'warning', 'Canceled by User');
      ui_multi_update_file_progress(id, 0, 'warning', false);
    },
    onUploadProgress: function(id, percent){
      // Updating file progress
      ui_multi_update_file_progress(id, percent);
    },
    onUploadSuccess: function(id, data){
      // A file was successfully uploaded
      ui_multi_update_file_status(id, 'success', 'Upload Complete');
      ui_multi_update_file_progress(id, 100, 'success', false);
    },
    onUploadError: function(id, xhr, status, message){

      if (handlers != null && typeof handlers.onUploadError == "function") {
        handlers.onComplete();
      }

      ui_multi_update_file_status(id, 'danger', typeof xhr.responseJSON != "undefined" && xhr.responseJSON.message != "undefined"  ? xhr.responseJSON.message : message);
      ui_multi_update_file_progress(id, 0, 'danger', false);  
    },
    onFallbackMode: function(){
      // When the browser doesn't support this plugin :(
    },
    onFileSizeError: function(file){
    }
  });
}