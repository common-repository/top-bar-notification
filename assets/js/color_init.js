jQuery(document).ready(function ($) {
    $("#custom").spectrum({
        color: jQuery("#color").val(),
        change:function (color) {
            jQuery("#color").val(color.toHexString());
        }
    });
});
