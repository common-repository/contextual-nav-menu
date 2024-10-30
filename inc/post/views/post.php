<?php

// Add an nonce field so we can check for it later.
wp_nonce_field( 'contextual_nav_menu_relation_inner_custom_box', 'contextual_nav_menu_relation_inner_custom_box_nonce' );

global $menu_item_selected, $post;
$menu_item_selected = get_post_meta( $post->ID, '_contextual_menu_item', true );
$menu_selected = get_post_meta( $post->ID, '_contextual_menu', true );

// Get menus
$menus = get_terms( 'nav_menu', array( 'hide_empty' => false ) );

// If no menus exists, direct the user to go and create some.
if ( !$menus ) {
    print '<p>'. sprintf( __( 'No menus have been created yet. <a href="%s">Create some</a>.' ), admin_url( 'nav-menus.php' ) ) .'</p>';
    return;
}


?>
<input type="hidden" name="contextual_menu_item" id="contextual_menu_item" value="<?php print $menu_item_selected; ?>" />
<label for="contextual_menu"><?php _e( 'Select Menu', 'cnm' ); ?></label><br />
<select name="contextual_menu" id="contextual_menu">
    <option value="none"><?php _ex( 'None', 'menu' , 'cnm' ); ?></option>
    <?php foreach( $menus as $menu ) :?>
        <option <?php selected( $menu_selected, $menu->term_id, true ) ?> value="<?php print $menu->term_id; ?>"><?php print $menu->name; ?></option>
    <?php endforeach;?>
</select>
<?php
foreach( $menus as $menu ) :
    ?>
    <div class="cnm_container" id="cnm_container_<?php print $menu->term_id; ?>" <?php if( $menu_selected != $menu->term_id ) { print ' style="display: none;"'; } ?>>
        <label for="cnm_<?php print $menu->term_id; ?>"><?php _e( 'Select Menu Entry', 'cnm' ); ?></label>
        <select name="cnm_<?php print $menu->term_id; ?>" id="cnm_<?php print $menu->term_id; ?>">
            <option value="none"><?php _ex( 'None', 'menu-item', 'cnm' ); ?></option>
            <?php
                contextual_nav_menu(    
                    array( 
                        'show_description' => false,
                        'items_wrap'     => '%3$s',
                        'container' => false,
                        'walker'  => new Contextual_Nav_Menu_Dropdown_Walker(),
                        'menu' => $menu->term_id,
                    )
                );
            ?>
        </select>            
    </div>
    <?php
endforeach;

?><p class="howto"><?php _e( 'To delete relation, just select "none" in the "Select Menu" than click "Upadte".', 'cnm' ); ?></p><?php

unset( $menu_item_selected );
