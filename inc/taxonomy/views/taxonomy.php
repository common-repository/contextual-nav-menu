<?php       

// Add an nonce field so we can check for it later.
wp_nonce_field( 'contextual_nav_menu_relation_inner_custom_box', 'contextual_nav_menu_relation_inner_custom_box_nonce' );

global $menu_item_selected, $tag_ID, $taxonomy;

$contextual_menu_item = get_option( 'contextual_menu_item' );

if( isset( $tag_ID ) && isset( $contextual_menu_item[ $taxonomy . '-' . $tag_ID ] ) ) {
    $menu_item_selected = $contextual_menu_item[ $taxonomy . '-' . $tag_ID ]['contextual_menu_item'];
    $menu_selected = $contextual_menu_item[ $taxonomy . '-' . $tag_ID ]['contextual_menu'];
}
else {
    $menu_item_selected = null;
    $menu_selected = null;
}

// Get menus
$menus = get_terms( 'nav_menu', array( 'hide_empty' => false ) );

// If no menus exists, direct the user to go and create some.
if ( !$menus ) {
    print '<p>'. sprintf( __( 'No menus have been created yet. <a href="%s">Create some</a>.' ), admin_url( 'nav-menus.php' ) ) .'</p>';
    return;
}

?>
<tr class="form-field">
    <th scope="row"><label for="contextual_menu"><?php _e( 'Select Menu', 'cnm' ); ?></label></th>
    <td>
        <input type="hidden" name="contextual_menu_item" id="contextual_menu_item" value="<?php print $menu_item_selected ?>" />
        <input type="hidden" name="taxonomy" id="taxonomy" value="<?php print $taxonomy ?>" />
        <select name="contextual_menu" id="contextual_menu">
            <option value="none"><?php _ex( 'None', 'menu' , 'cnm' ); ?></option>
            <?php foreach( $menus as $menu ) :?>
                <option <?php selected( $menu_selected, $menu->term_id, true ) ?> value="<?php print $menu->term_id; ?>"><?php print $menu->name; ?></option>
            <?php endforeach;?>
        </select>
        <span class="description"><?php _e( 'To delete relation, just select "none" in the "Select Menu" than click "Upadte".', 'cnm' ); ?></span>
    </td>
</tr>
<tr class="form-field">
    <th><label><?php _e( 'Select Menu Entry', 'cnm' ); ?></label></th>
    <td>
        <?php
        foreach( $menus as $menu ) :
            ?>
            <div class="cnm_container" id="cnm_container_<?php print $menu->term_id; ?>" <?php if( $menu_selected != $menu->term_id ) { print ' style="display: none;"'; } ?>>
                
                <select name="contextual_menu_item_<?php print $menu->term_id ?>" id="contextual_menu_item_<?php print $menu->term_id; ?>">
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
        ?>
    </td>
</tr>
<?php
unset( $menu_item_selected );
