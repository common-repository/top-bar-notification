jQuery(document).ready(function ($) {
    $('form').submit( function(event) {
        var data = {};
        const message = jQuery('.wp-editor-area').val();
        if (typeof tinymce == 'undefined') {
			data['message'] = message
        }else{
            tinyMCE.triggerSave();
        }
        event.preventDefault();
        $.each( $(this).serializeArray(), function ( i, field ) {
            data[field.name] = field.value;
        });
        if ( $('.activate').is(':checked')== true && data['message'] == "" ){
            alert( "Plese complete the field message" );
            return;
        }
        data['action'] = 'tbn_ajax_add';
        $.ajax({
            url: window.location.origin  + '/wp-admin/admin-ajax.php',
            method: "POST",
            data: data,
            success: function( result ) {
                if (data['id']){
                }else{
                    location.reload();
                }
            },
            error: function(err) {
                console.log(err);
            }
        });
    });
});
