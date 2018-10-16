$(document).ready(function() {

    $( ".showpass" ).click(function() {
        init_showpass();
    });

    $( ".btn_str" ).click(function() {
        init_text_area_form();
    });

    $( "#getPrevew" ).change(function(){
        init_preview_image(this);
    }); 

    $( ".sts" ).click(function(){
        init_edit_stats_form(this);
        // alert('> '+this);
    }); 

    $( ".btnJslist" ).click(function(){
        init_modal_list();
    });

    $( ".btnJsSave" ).click(function(e){
        var e = e.preventDefault();
        init_modal_save(e);
    });

    $(".myModal").on("hidden.bs.modal", function () {
        // console.log('clicou');
        window.location.reload();
    });

    
    init_codemirror();

    init_dropzone();

});



/**
* CodeMirror - forms code
*/
function init_codemirror() {
    
    var code = document.querySelector(".jscodemirror");

    if ( code ) {
        console.log('is code '+code);

        CodeMirror.fromTextArea(code, {
            lineNumbers: true,
            styleActiveLine: true,
            matchBrackets: true,
            theme: "material",
        });

    }else{
        console.log('null code ');
    }

    

};


/**
* DATA INSERT FORM
* -> https://stackoverflow.com/questions/5004233/jquery-ajax-post-example-with-php
* -> https://stackoverflow.com/questions/20769364/insert-data-through-ajax-into-mysql-database/20769461
*/
function init_modal_list() {

    var modal = $(".myModal");
    var data  = modal.find(".datamodel").attr('data-id');
    var route = modal.find(".datamodel").attr('data-routelist');
    // console.log('id: '+data);
    // console.log('route: '+route);

    $.ajax({
        url:  route,
        data: data,
        type: 'POST',
        dataType: 'json',

        success: function(obj) {

            var table = modal.find(".modal-body");
            var tbody = modal.find(".table tbody");
            // console.log(obj);
            
            tbody.empty();
            table.show();
            
            if ( obj == '' ) {
                tbody.append("<tr colspan='4'><td> <center>Sem Registro</center> </td> </tr>");
            }
            
            $.each(obj, function(index, val) {                    
                // if ( val.level_name == '--' ) {   
                    // tbody.append("<tr><td>"+val.level_id+"</td><td>"+val.level_category+"</td> </tr>");
                // }else{
                tbody.append("<tr><td>"+val.level_id+"</td><td>"+val.level_category+"</td> <td>"+val.level_name+"</td> </tr>");
                // }
            });

        },

        error: function(rs) {
           console.log(rs);
        }

    });

}

function init_modal_save() {
    
    var modal = $(".myModal");
    var data  = modal.find("form").find("input, select, button, textarea");
    var route = modal.find(".datamodel").attr('data-routesave');
    // console.log(data);

    $.ajax({
        url : route,
        data: data,
        type: 'POST',
        dataType: 'json',

        success: function(obj) {
            // console.log(obj);
            // console.log(obj.data);
            // console.log( jQuery.isEmptyObject(obj.data) );
            
            if ( !$.isEmptyObject(obj.data) ) {                
                /*var tbody = modal.find(".table tbody");
                    tbody.empty();
                    $.each(obj.data, function(index, val) {                    
                        tbody.append("<tr><td>"+val.level_id+"</td><td>"+val.level_category+"</td><td>"+val.level_name+"</td> </tr>");
                    });*/
                
                init_modal_list();
                modal.find("form").find('input, textarea').val("");
            }


            $("."+obj.msg_alert).hide();
            $(".table-responsive").before( showMesg(obj.msg_alert, obj.msg_text) );
            $("."+obj.msg_alert).delay('8000').fadeOut("slow");

        },

        error: function(obj) {
           console.log(obj);
        }

    });
    return false;

}


/**
* UPLOAD DE IMAGES
*/
function init_dropzone() {
    
    try {
        Dropzone.autoDiscover = false;
        
        var myUrl      = $('.dropzBR').attr('data-url');
        var myDropzone = new Dropzone(".dropzBR", {
            url: myUrl,
            paramName: "gb_thumb",
            maxFilesize: 1, // MB
            dictResponseError: "Erro ao fazer o upload !",
            maxFiles: 10,
            acceptedFiles: 'image/*',
            // addRemoveLinks: true,
            dictDefaultMessage: //MENSAGEM PADRÃO
                '<span class="bigger-150 bolder"><i class="ace-icon fa fa-caret-right red"></i>  Mova seus arquivos </span> para fazer upload \
                 <span class="smaller-80 grey">(ou clique)</span> <br /> \
                 <i class="upload-icon ace-icon fa fa-cloud-upload blue fa-3x"></i>',
            dictResponseError: 'Error while uploading file!'
            // clickable: ".fileinput-button",
            // autoProcessQueue: false
        });

        // myDropzone.on("queuecomplete", function() {
        myDropzone.on("success", function() {
            //Redirect URL
            window.location.reload();
            // window.location.href = 'http://.....';
            // window.location.href = "/admin/capelania/muploads/"+id_post;
        });

        $(document).one('ajaxloadstart.page', function (e) {
            try {
                alert('foi destroy');
               myDropzone.destroy();
            } catch (e) { }
         });

    } catch (e) {
         // alert('Dropzone.js does not support older browsers!');
    }

}


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
* TEXT AREAS FORMS
*/
function init_text_area_form() {
    $('.form_str').val($('#editor-one').cleanHtml(true));
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
    return '<div class="alert '+alert+' hidden-time alert-dismissable fade in" role="alert"> <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button> '+data+' </div>';
}