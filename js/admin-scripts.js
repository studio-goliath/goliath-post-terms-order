jQuery( function( $ ) {

    // Select 2 pour le choix ou la creation de term
    var $sortableSelect = $(".sortable-tax-select");

    var select2Options = {
        tags: true
    };

    $sortableSelect.select2( select2Options );

    $sortableSelect.on({
        "select2:select":  function (e) {

            console.log( e.target );
            var $currentSelect = $( e.target );
            var selectedOption = e.params.data;

            var $ulSortable = $currentSelect.nextAll('.terms-draggable') ;

            $ulSortable.append( "<li>" +
                "<input type='hidden' name='sg-terms-sort[" + e.target.getAttribute( 'id' ) + "][]' value='" + selectedOption.id + "' />" +
                "<span class='dashicons dashicons-sort'></span>" +
                "<span class='text'>" + selectedOption.text + "</span>" +
                "<span class='dashicons dashicons-dismiss'></span>" +
                "</li>" );


            // Disabled the selected option
            $('option[value="'+ selectedOption.id + '"]', $currentSelect).attr( 'disabled', 'disabled' );
            $currentSelect.val( null ).select2( select2Options );

        }
    });

    // Ordonancement des terms choisie
    $('.terms-draggable').sortable().disableSelection();

    // Supression d'un term
    $('.terms-draggable .dashicons-dismiss').on('click', function () {

        var $parent = $(this).parent();
        var term_id = $('input', $parent ).val();

        var $select = $parent.parent().prevAll('.sortable-tax-select');
        console.log( term_id, $select );
        $('option[value="'+ term_id + '"]', $select).removeAttr( 'disabled' );
        $select.select2( select2Options );
        $parent.remove();
    });
});