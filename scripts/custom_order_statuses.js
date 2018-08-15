(function( $ ) {
    $(function() {
        $('.custom-order-status input[type="color"]').on('change', function() {
            var param = $(this).attr('param');
            var property = $(this).attr('name');
            $('mark.status-' + param).css(property, $(this).val());
        });
    });
})( jQuery );
