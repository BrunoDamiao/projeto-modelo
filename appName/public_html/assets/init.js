$(document).ready(function() {

    $( ".showpass" ).click(function() {
        init_showpass();
    });

    $( "#getPrevew" ).change(function(){
        init_preview_image(this);
    });

    $( ".sts" ).click(function(){
        init_edit_stats_form(this);
        // alert('> '+this);
    });

});

/**
* SHOW PASSWORD FORM PERFIL
*/
function init_showpass() {

    var typePass = $('#user_password');
    var iconPass = $(".showpass i");

    if ( typePass.attr('type') == 'password' ) {

        typePass.attr('type', 'text');
        iconPass.attr('class', 'fa fa-eye-slash');

    } else {

        typePass.attr('type', 'password');
        iconPass.attr('class', 'fa fa-eye');

    }

}

/**
* PREVIEW IMAGES
*/
function init_preview_image(input) {
    if (input.files && input.files[0]) {
        var reader = new FileReader();

        reader.onload = function (e) {
            $('#avatar').attr('src', e.target.result);

                $('.btn-file > .path').remove();
                $('.btn-file').append("<div class='path'></div>");
                $('.btn-file > .path').text(e.target.result);
        }

        reader.readAsDataURL(input.files[0]);
    }
}


/**
* TOOGLE STATUS EDIT FORM
*/
function init_edit_stats_form(x) {

    $(x).each(function(){
        var id   = $(x).attr('data-id');
        var name = $(x).attr('data-name');
        var route= $(x).attr('data-model');
        // var msg  = '<div class="alert alert-success hidden-time alert-dismissable fade in" role="alert"> <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>Status do registro: <strong>'+name+'</strong>, foi editado com sucesso! </div>';
        // alert('rota '+route+'/'+id);
        console.log('route: '+ route);

        $.ajax({
            url:  route,
            data: {id},
            type: 'GET',
            dataType: 'json',

            success: function(obj) {

                $("."+obj.msg_alert).hide();
                $(".table-responsive").before( showMesg(obj.msg_alert, obj.msg_text) );
                $("."+obj.msg_alert).delay('2000').fadeOut("slow");

            }

            /*complete: function() {
                window.location.reload();
            }*/

        });
        return false;
    });
}



/**
* HELPERS
*/
function showMesg(alert, data) {
  return '<div class="alert '+alert+' alert-dismissible fade show" role="alert">'+data+'<button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span> </button></div>';
}