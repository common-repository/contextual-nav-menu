<?php

global $menu_item_selected;

$menu_item_selected = null;

// Get menus
$menus = get_terms( 'nav_menu', array( 'hide_empty' => false ) );

?>
<div class="cnm_bulk_container">
    <h2><?php _e( 'Selected Posts', 'cnm' ); ?></h2>
    <ol>
    <?php
    foreach( $_POST['post_ids'] as $post_id ) {
        $post = get_post( $post_id );
        ?>
        <li><?php print $post->post_title; ?></li>    
        <?php    
    }
    ?>
    </ol>
</div>
<div class="cnm_bulk_container">
    <?php
    // If no menus exists, direct the user to go and create some.
    if ( !$menus ) {
        print '<p>'. sprintf( __( 'No menus have been created yet. <a href="%s">Create some</a>.' ), admin_url( 'nav-menus.php' ) ) .'</p>';
        return;
    }
    else {
    ?>
        <div id="cnm_result"></div>
        <form id="cnm_form">
            <label for="contextual_menu"><?php _e( 'Select Menu', 'cnm' ); ?></label>
            <div>
                <select name="contextual_menu" id="contextual_menu">
                    <option value="none"><?php _ex( 'None', 'menu' , 'cnm' ); ?></option>
                    <?php foreach( $menus as $menu ) :?>
                        <option value="<?php print $menu->term_id; ?>"><?php print $menu->name; ?></option>
                    <?php endforeach;?>
                </select>
            </div>
            <?php
            foreach( $menus as $menu ) :
                ?>
                <div class="cnm_container" id="cnm_container_<?php print $menu->term_id; ?>" style="display: none;">
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
                                    'menu' => $menu->term_id
                                )
                            );
                        ?>
                    </select>            
                </div>
                <?php
            endforeach;
            ?>
            <br /><input class="button" type="submit" value="<?php _e( 'Update', 'cnm' ); ?>" />
        </form>
        <p class="howto"><?php _e( 'To delete relation, just select "none" in the "Select Menu" than click "Upadte".', 'cnm' ); ?></p>
        <?php
    }
    ?>
</div>
<?php

unset($menu_item_selected);
die;
