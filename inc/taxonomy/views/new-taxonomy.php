<?php       

// Add an nonce field so we can check for it later.
wp_nonce_field( 'contextual_nav_menu_relation_inner_custom_box', 'contextual_nav_menu_relation_inner_custom_box_nonce' );

global $menu_item_selected, $taxonomy;

$contextual_menu_item = get_option( 'contextual_menu_item' );

$menu_item_selected = null;
$menu_selected = null;

// Get menus
$menus = get_terms( 'nav_menu', array( 'hide_empty' => false ) );

// If no menus exists, direct the user to go and create some.
if ( !$menus ) {
    print '<p>'. sprintf( __( 'No menus have been created yet. <a href="%s">Create some</a>.' ), admin_url( 'nav-menus.php' ) ) .'</p>';
    return;
}

?>
<div class="form-field">
    <div id="cnm-new-taxonomy">
        <label for="contextual_menu"><?php _e( 'Select Menu', 'cnm' ); ?></label>
        <input type="hidden" name="contextual_menu_item" id="contextual_menu_item" value="" />
        <input type="hidden" name="taxonomy" id="taxonomy" value="<?php print $taxonomy ?>" />
        <select name="contextual_menu" id="contextual_menu">
            <option value="none"><?php _ex( 'None', 'menu' , 'cnm' ); ?></option>
            <?php foreach( $menus as $menu ) :?>
                <option <?php selected( $menu_selected, $menu->term_id, true ) ?> value="<?php print $menu->term_id; ?>"><?php print $menu->name; ?></option>
            <?php endforeach;?>
        </select>
        <?php
        foreach( $menus as $menu ) :
            ?>
            <div class="cnm_container" id="cnm_container_<?php print $menu->term_id; ?>" style="display: none;">
                <label for="contextual_menu_item_<?php print $menu->term_id; ?>"><?php _e( 'Select Menu Entry', 'cnm' ); ?></label> 
                <select name="contextual_menu_item_<?php print $menu->term_id; ?>" id="contextual_menu_item_<?php print $menu->term_id; ?>">
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
    </div>
    <p><?php _e( 'Define a parent menu item if necessary.', 'cnm' ); ?></p>
</div>
<?php
unset( $menu_item_selected );
