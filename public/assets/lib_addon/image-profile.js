// PROFILE UPLOAD
function FileImage() {
    var fileData = new FormData();
    fileData.append('FILE_IMAGE', $('#FILE').get(0).files[0]);

    $.ajax({
        url: '/User/UploadImage', //@Url.Action("UploadImage", "Pengaturan")',
        type: "POST",
        contentType: false,
        processData: false,
        data: fileData,
        success: function (result) {
            //var d = new Date();
            //$('#iProfile').attr('src', result + '?dummy=' + d.getTime());
            ProfilePiture();
        },
        error: function (err) {
            alert('Gagal...!!!');
        }
    });
}

function ProfilePiture() {
    $.get('/User/GetBase64Image', null, function (result) {
        var imag = "<img src='data:image/jpg;base64," + result.base64imgage + "' class='img-circle m-b' alt='logo'/>";

        $('#iProfile').html(imag);
    });
}