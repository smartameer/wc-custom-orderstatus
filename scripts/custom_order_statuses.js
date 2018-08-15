(function( $ ) {
    $(function() {
        $('form[name="order-status-form"] input[type="color"]').on('change', function() {
            var property = $(this).attr('name');
            $('mark.form-order-status').css(property, $(this).val());
        });
        $('form[name="order-status-form"] input[name="name"]').on('keyup', function() {
            $('mark.form-order-status > span').text($(this).val());
        });
    });
})( jQuery );
