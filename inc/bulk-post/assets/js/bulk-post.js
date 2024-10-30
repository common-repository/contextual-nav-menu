( function( $ ) {
    $( function() {
        $( '#contextual_menu' ).live( 'change', function() {
            $( '.cnm_container' ).hide();
            
            var val = $( this).val();
            
            if( val == 'none' )
                $( '#contextual_menu_item, .cnm_container select' ).val( val );
            else
                $( '#cnm_container_' + val ).show(); 
        } );
        $( '.cnm_container select' ).live( 'change', function() {
            $( '#contextual_menu_item' ).val( $( this ).val() ); 
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
        
        function build_cnm_form() {
            var post_ids = [];
            $( '[id^=cb-select-]:checked' ).each( function() {
                var val = $( this ).val();
                
                if( val != '' && val != 'on' )
                    post_ids.push( $( this ).val() );
            } ); 
            
            if(  post_ids.length > 0  ) {
                $.ajax( {
                    url: cnmBulkParams.adminUrl,
                    type: 'POST',
                    data: {
                        action: 'get_contextual_nav_menu_relation_bulk_post_inner_custom_box',
                        post_ids: post_ids
                    },
                    success: function( result ) {
                        $( '#cnm_form_container' ).html( result ).show();
                        $( '#cnm_form_container_launcher' ).click();
                    }
                } );

                $( '#cnm_form' ).unbind(  "submit"  ).live( 'submit', function( event ) {
                    event.preventDefault();
                    var contextual_menu = $( '#contextual_menu' ).val();
                    var contextual_menu_item;
                    if( contextual_menu != 'none' ) {
                        contextual_menu_item = $( '#cnm_' + contextual_menu ).val();
                    } else {
                        contextual_menu_item = 'none';
                    }
                    $.ajax( {
                        url: cnmBulkParams.adminUrl,
                        type: 'POST',
                        data: {
                            action: 'update_contextual_nav_menu_relation_bulk_post',
                            post_ids: post_ids,
                            contextual_menu: contextual_menu,
                            contextual_menu_item: contextual_menu_item,
                            post_type: cnmBulkParams.postType
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
