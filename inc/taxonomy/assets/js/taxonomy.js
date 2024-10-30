( function( $ ) {
    $( function(  ) {
        $( '#cnm-new-taxonomy #contextual_menu, .form-field #contextual_menu' ).change( function() {
            $( '#cnm-new-taxonomy .cnm_container, .form-field .cnm_container' ).hide();
            
            var val = $( this).val();
            
            if( val == 'none' )
                $( '#cnm-new-taxonomy #contextual_menu_item, #cnm-new-taxonomy .cnm_container select, .form-field #contextual_menu_item, .form-field .cnm_container select' ).val( val );
            else
                $( '#cnm-new-taxonomy #cnm_container_' + val + ', .form-field #cnm_container_' + val ).show();
        } );
        $( '#cnm-new-taxonomy .cnm_container select, .form-field .cnm_container select' ).change( function() {
            $( '#cnm-new-taxonomy #contextual_menu_item, .form-field #contextual_menu_item' ).val( $( this ).val() ); 
        } );
    } );
} )( jQuery )