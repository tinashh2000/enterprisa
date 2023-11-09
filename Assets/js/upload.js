function gebid(el) {
    return document.getElementById(el);
}

function uploadFile() {
    var file = gebid("file1").files[0];
    // alert(file.name+" | "+file.size+" | "+file.type);
    var formdata = new FormData();
    formdata.append("file1", file);
    var ajax = new XMLHttpRequest();
    ajax.upload.addEventListener("progress", progressHandler, false);
    ajax.addEventListener("load", completeHandler, false);
    ajax.addEventListener("error", errorHandler, false);
    ajax.addEventListener("abort", abortHandler, false);
    ajax.open("POST", "file_upload_parser.php");
    ajax.send(formdata);
}

function progressHandler(event) {
    gebid("loaded_n_total").innerHTML = "Uploaded " + event.loaded + " bytes of " + event.total;
    var percent = (event.loaded / event.total) * 100;
    gebid("progressBar").value = Math.round(percent);
    gebid("status").innerHTML = Math.round(percent) + "% uploaded... please wait";
}

function completeHandler(event) {
    gebid("status").innerHTML = event.target.responseText;
    gebid("progressBar").value = 0;
}

function errorHandler(event) {
    gebid("status").innerHTML = "Upload Failed";
}

function abortHandler(event) {
    gebid("status").innerHTML = "Upload Aborted";
}
