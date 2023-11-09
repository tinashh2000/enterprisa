function MessageBox(msg, error) {
    //swAlert.fire({type: error ? 'error' : 'success', title: msg});

    error ? toastr.error(msg) : toastr.success(msg);

}

// const swAlert = Swal.mixin({
//     toast: true,
//     position: 'top-end',
//     showConfirmButton: false,
//     timer: 6000
// });


function removeTags(str) {
    if ((str===null) || (str===''))
        return false;
    else
        str = str.toString();
    return str.replace( /(<([^>]+)>)/ig, '');
}