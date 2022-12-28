function swal_fire_error(error_msg){
    Swal.fire({
        icon: 'error',
        title: 'Oops...',
        html: error_msg,
    });
}

function swal_fire_success(message){
    Swal.fire({
        position: 'top-end',
        icon: 'success',
        title: message,
        showConfirmButton: false,
        timer: 5000
    });
}