( function( $ ) {
    $( function(  ) {
        $( '#contextual_menu' ).change( function() {
            $( '.cnm_container' ).hide();
            
            var val = $( this).val();
            
            if( val == 'none' )
                $( '#contextual_menu_item, .cnm_container select' ).val( val );
            else
                $( '#cnm_container_' + val ).show(); 
        } );
        $( '.cnm_container select' ).change( function() {
            $( '#contextual_menu_item' ).val( $( this ).val() ); 
        } );
    } );
} )( jQuery )