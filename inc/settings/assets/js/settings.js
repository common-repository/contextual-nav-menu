
( function( $ ) {

    $( function() {

        var custom_uploader;
        
        $( '#upload_image_button' ).click( function( e ) {
     
            e.preventDefault();
     
            //If the uploader object has already been created, reopen the dialog
            if ( custom_uploader ) {
                custom_uploader.open();
                return;
            }
     
            //Extend the wp.media object
            custom_uploader = wp.media.frames.file_frame = wp.media( {
                title: cnmParams.imageBouton,
                button: {
                    text: cnmParams.imageBouton
                },
                multiple: false
            } );
     
            //When a file is selected, grab the URL and set it as the text field's value
            custom_uploader.on( 'select', function() {
                
                attachment = custom_uploader.state().get( 'selection' ).first().toJSON();
                $( '#contextual_nav_menu_breadcrumb_home_image' ).val( attachment.id );
                $( '#contextual_nav_menu_breadcrumb_home_image_preview' ).attr( 'src', attachment.sizes.full.url );

            } );
     
            //Open the uploader dialog
            custom_uploader.open();
     
        });
        
        $( '#delete_home_image' ).click( function( e ) {

            e.preventDefault();
            $( '#contextual_nav_menu_breadcrumb_home_image' ).val( '' );
            $( '#contextual_nav_menu_breadcrumb_home_image_preview' ).attr( 'src', '' );

        });
        
        $( '.menu_order_number' ).change( function() {

            var menu_order = '';

            $( '#contextual_nav_menu_breadcrumb_menu_order_list' ).val( '' );

            $( '.menu_order_number option:selected' ).each( function() {

                if( $( this ).val() != '' )
                    menu_order += $( this ).val() + ',';

            } );

            $( '#contextual_nav_menu_breadcrumb_menu_order_list' ).val( menu_order );

        } );

    } );

} )( jQuery );