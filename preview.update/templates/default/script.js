$(function () {
    $('#preview_update_form').on('submit', function () {
        $.ajax({
            url: $(this).attr('action'),
            type: 'POST',
            data: $(this).serialize(),
            success: function(html){
                $('.status_block').html(html);
            },
            error: function( jqXHR, status, errorThrown ){
                alert('Ошибка запроса');
            }
        });
        return false;
    })
})