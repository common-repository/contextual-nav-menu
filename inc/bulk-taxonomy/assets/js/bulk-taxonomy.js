( function( $ ) {
    $( function(  ) {
        $( '.cnm_bulk_container #contextual_menu' ).live( 'change', function() {
            $( '.cnm_bulk_container .cnm_container' ).hide();
            $( '.cnm_bulk_container #cnm_container_' + $(this).val() ).show(); 
        } );
        $( '.cnm_bulk_container .cnm_container select' ).live( 'change', function() {
            $( '.cnm_bulk_container #contextual_menu_item' ).val( $( this ).val() ); 
        } );
        
        $( '<option>' ).val( 'add_menu_item_parent' ).text( cnmBulkParams.optionText ).appendTo( "select[name='action']" );
        $( '<option>' ).val( 'add_menu_item_parent' ).text( cnmBulkParams.optionText ).appendTo( "select[name='action2']" );
        
        $( '#doaction' ).click( function( event ) {
            if( $( 'select[name="action"]' ).val() == 'add_menu_item_parent' ) {
                 event.preventDefault();
                 build_cnm_form();
                 return false;
            }
        } );
        
        $( '#doaction2' ).click( function( event ) {
            if( $( 'select[name="action2"]' ).val() == 'add_menu_item_parent' ) {
                 event.preventDefault();
                 build_cnm_form();
                 return false;
            }
        } );
        
        function build_cnm_form(  ) {
            var cat_ids = [];
            $( '[id^=cb-select-]:checked' ).each( function(  ) {
                cat_ids.push( $( this ).val(  ) );
            } ); 
            
            if(  cat_ids.length > 0  ) {
                $.ajax( {
                    url: cnmBulkParams.adminUrl,
                    type: 'POST',
                    data: {
                        action: 'get_contextual_nav_menu_relation_bulk_taxonomies_inner_custom_box',
                        cat_ids: cat_ids,
                        taxonomy: cnmBulkParams.taxonomy
                    },
                    success: function( result ) {
                        $( '#cnm_form_container' ).html( result ).show(  );
                        $( '#cnm_form_container_launcher' ).click();
                    }
                } );

                $( '#cnm_form' ).unbind(  "submit"  ).live( 'submit', function( event ) {
                    event.preventDefault();
                    var contextual_menu = $( '.cnm_bulk_container #contextual_menu' ).val();
                    var contextual_menu_item;
                    if( contextual_menu != 'none' ) {
                        contextual_menu_item = $( '.cnm_bulk_container #cnm_' + contextual_menu ).val();
                    } else {
                        contextual_menu_item = 'none';
                    }
                    $.ajax( {
                        url: cnmBulkParams.adminUrl,
                        type: 'POST',
                        data: {
                            action: 'update_contextual_nav_menu_relation_bulk_taxonomies',
                            cat_ids: cat_ids,
                            contextual_menu: contextual_menu,
                            contextual_menu_item: contextual_menu_item,
                            taxonomy: cnmBulkParams.taxonomy
                        },
                        success: function( result ) {
                            $( '#cnm_result' ).html( result );
                        }
                    } );                                            
                    return false;
                } );    
            }
        }
    } );
} )( jQuery )