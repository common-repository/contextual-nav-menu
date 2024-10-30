<?php 

class Contextual_Nav_Menu_Widget_Title_Walker extends Walker_Nav_Menu {    
    
    var $current_element_markers = array( 'current-menu-item', 'current-menu-ancestor' );

    /**
     * Traverse elements to create list from elements.
     *
     * Display one element if the element doesn't have any children otherwise,
     * display the element and its children. Will only traverse up to the max
     * depth and no ignore elements under that depth. It is possible to set the
     * max depth to include all depths, see walk() method.
     *
     * This method should not be called directly, use the walk() method instead.
     *
     * @since 2.5.0
     *
     * @param object $element           Data object.
     * @param array  $children_elements List of elements to continue traversing.
     * @param int    $max_depth         Max depth to traverse.
     * @param int    $depth             Depth of current element.
     * @param array  $args              An array of arguments.
     * @param string $output            Passed by reference. Used to append additional content.
     * 
     * @return null Null on failure with no changes to parameters.
     */
    function display_element( $element, &$children_elements, $max_depth, $depth, $args, &$output ) {

        if ( !$element )
            return;
        
        $id_field = $this->db_fields['id'];
        $id       = $element->$id_field;

        $ancestor_test = array_intersect( $this->current_element_markers, $element->classes );
        
        if( !empty( $ancestor_test ) )
            if( $depth == $args[0]->start_depth )
                $output .= $element->title;
            elseif ( ( $max_depth == 0 || $max_depth > $depth + 1 ) && isset( $children_elements[$id] ) )
                foreach( $children_elements[ $id ] as $child )
                    $this->display_element( $child, $children_elements, $max_depth, $depth + 1, $args, $output );
        
        unset( $children_elements[ $id ] );
        
    } // END function display_element

} // END class Contextual_Nav_Menu_Widget_Title_Walker extends Walker_Nav_Menu

class Contextual_Nav_Menu_Widget_Walker extends Walker_Nav_Menu {    
    
    var $current_element_markers = array( 'current-menu-item', 'current-menu-ancestor' );

    /**
     * Start the empty element output.
     *
     * @param string $output Passed by reference. Used to append additional content.
     */
    function start_empty_el( &$output ) {
        
        $output .= '<li class="empty-item">';

    } // END function start_empty_el

    /**
     * Ends the empty element output.
     *
     * @param string $output Passed by reference. Used to append additional content.
     */
    function end_empty_el( &$output ) {

        $output .= '</li>';

    } // END function end_empty_el
    
    /**
     * Traverse elements to create list from elements.
     *
     * Display one element if the element doesn't have any children otherwise,
     * display the element and its children. Will only traverse up to the max
     * depth and no ignore elements under that depth. It is possible to set the
     * max depth to include all depths, see walk() method.
     *
     * This method should not be called directly, use the walk() method instead.
     *
     * @since 2.5.0
     *
     * @param object $element           Data object.
     * @param array  $children_elements List of elements to continue traversing.
     * @param int    $max_depth         Max depth to traverse.
     * @param int    $depth             Depth of current element.
     * @param array  $args              An array of arguments.
     * @param string $output            Passed by reference. Used to append additional content.
     * @return null Null on failure with no changes to parameters.
     */
    function display_element( $element, &$children_elements, $max_depth, $depth, $args, &$output ) {

        if ( !$element )
            return;

        $id_field      = $this->db_fields['id'];    
        $id            = $element->$id_field;
        $ancestor_test = array_intersect( $this->current_element_markers, $element->classes );
        
        //display this element
        if ( isset( $args[0] ) && is_array( $args[0] ) )
            $args[0]['has_children'] = ! empty( $children_elements[$id] );
        

        if( $depth <= $args[0]->start_depth ) {
            if( empty( $ancestor_test ) ) {
                
                unset( $children_elements[ $id ] );
                return;

            }
            else {

                call_user_func_array( array( $this, 'start_empty_el' ), array( &$output ) );

            }
        }
        elseif( $depth > $args[0]->start_depth ) {
            
            $cb_args = array_merge( array( &$output, $element, $depth ), $args );
            call_user_func_array( array( $this, 'start_el' ), $cb_args );

        }

        // descend only when the depth is right and there are childrens for this element
        if ( ( $max_depth == 0 || $max_depth > $depth+1 ) && isset( $children_elements[$id] ) ) {

            foreach( $children_elements[ $id ] as $child ){

                if ( !isset( $newlevel ) ) {

                    $newlevel = true;
                    //start the child delimiter
                    $cb_args = array_merge( array( &$output, $depth ), $args );
                    call_user_func_array( array( $this, 'start_lvl' ), $cb_args );

                }

                $this->display_element( $child, $children_elements, $max_depth, $depth + 1, $args, $output );

            }

            unset( $children_elements[ $id ] );
        }

        if ( isset( $newlevel ) && $newlevel ){
            
            //end the child delimiter
            $cb_args = array_merge( array( &$output, $depth ), $args );
            call_user_func_array( array( $this, 'end_lvl' ), $cb_args );

        }

        //end this element
        if( $depth <= $args[0]->start_depth ) {
            
            call_user_func_array( array( $this, 'end_empty_el' ), array( &$output ) );

        }
        elseif( $depth > $args[0]->start_depth ) {
            
            $cb_args = array_merge( array( &$output, $element, $depth ), $args );
            call_user_func_array( array( $this, 'end_el' ), $cb_args );

        }
        
    } // END function display_element

} // END class Contextual_Nav_Menu_Widget_Walker extends Walker_Nav_Menu

class Contextual_Nav_Menu_Breadcrumb_Walker extends Walker_Nav_Menu {	
    
    var $current_ancestors_element_markers = array( 'current-menu-ancestor' );
	var	$current_element_markers = array( 'current-menu-item' ); 

    /**
     * Start the element output.
     *
     * @see Walker::start_el()
     *
     * @since 3.0.0
     *
     * @param string $output Passed by reference. Used to append additional content.
     * @param object $item   Menu item data object.
     * @param int    $depth  Depth of menu item. Used for padding.
     * @param array  $args   An array of arguments. @see wp_nav_menu()
     * @param int    $id     Current item ID.
     */
    function start_last_el( &$output, $item, $depth = 0, $args = array(), $id = 0 ) {
        global $cnm_bc_is_closed;
        
        $cnm_bc_is_closed = true;
        $class_names      = $value = '';
        
        $classes   = empty( $item->classes ) ? array() : ( array ) $item->classes;
        $classes[] = 'menu-item-' . $item->ID;

        /**
         * Filter the CSS class( es ) applied to a menu item's <li>.
         *
         * @since 3.0.0
         *
         * @param array  $classes The CSS classes that are applied to the menu item's <li>.
         * @param object $item    The current menu item.
         * @param array  $args    An array of arguments. @see wp_nav_menu()
         */
        $class_names = join( ' ', apply_filters( 'nav_menu_css_class', array_filter( $classes ), $item, $args ) );
        $class_names = $class_names ? ' class="' . esc_attr( $class_names ) . '"' : '';

        /**
         * Filter the ID applied to a menu item's <li>.
         *
         * @since 3.0.1
         *
         * @param string The ID that is applied to the menu item's <li>.
         * @param object $item The current menu item.
         * @param array $args An array of arguments. @see wp_nav_menu()
         */
        $id = apply_filters( 'nav_menu_item_id', 'menu-item-'. $item->ID, $item, $args );
        $id = $id ? ' id="' . esc_attr( $id ) . '"' : '';

        $output .= '<li' . $id . $value . $class_names .'>';
        
        $item_output = $args->before;
        /** This filter is documented in wp-includes/post-template.php */
        $item_output .= apply_filters( 'the_title', $item->title, $item->ID );
        $item_output .= $args->after;

        $output .= $item_output;

    } // END function start_last_el

    /**
     * Traverse elements to create list from elements.
     *
     * Display one element if the element doesn't have any children otherwise,
     * display the element and its children. Will only traverse up to the max
     * depth and no ignore elements under that depth. It is possible to set the
     * max depth to include all depths, see walk() method.
     *
     * This method should not be called directly, use the walk() method instead.
     *
     * @since 2.5.0
     *
     * @param object $element           Data object.
     * @param array  $children_elements List of elements to continue traversing.
     * @param int    $max_depth         Max depth to traverse.
     * @param int    $depth             Depth of current element.
     * @param array  $args              An array of arguments.
     * @param string $output            Passed by reference. Used to append additional content.
     * @return null Null on failure with no changes to parameters.
     */
    function display_element( $element, &$children_elements, $max_depth, $depth, $args, &$output ) {

        if ( !$element )
            return;

        $id_field      = $this->db_fields['id'];
        $id            = $element->$id_field;
        $ancestor_test = array_intersect( $this->current_ancestors_element_markers, $element->classes );
        $current_test  = array_intersect( $this->current_element_markers, $element->classes );
        
        //display this element
        if ( isset( $args[0] ) && is_array( $args[0] ) )
            $args[0]['has_children'] = ! empty( $children_elements[ $id ] );

        if( empty( $ancestor_test ) && empty( $current_test ) ) {
            
            unset( $children_elements[ $id ] );
            return;  

        }
        
        if( !empty( $current_test ) ) {
            
            $cb_args = array_merge( array( &$output, $element, $depth ), $args );
            call_user_func_array( array( $this, 'start_last_el' ), $cb_args );
            unset( $children_elements[ $id ] );

        } 
        else {
           
            $cb_args = array_merge( array( &$output, $element, $depth ), $args );
            call_user_func_array( array( $this, 'start_el' ), $cb_args );        
            
            // descend only when the depth is right and there are childrens for this element
            if ( ( $max_depth == 0 || $max_depth > $depth+1 ) && isset( $children_elements[ $id ] ) ) {
                
                foreach( $children_elements[ $id ] as $child ){
                    
                    $this->display_element( $child, $children_elements, $max_depth, $depth + 1, $args, $output );
             
                }

                unset( $children_elements[ $id ] );
            }

        }
        
        $cb_args = array_merge( array( &$output, $element, $depth ), $args );
        //end this element
        call_user_func_array( array( $this, 'end_el' ), $cb_args );
        
    } // END function display_element

} // END class Contextual_Nav_Menu_Breadcrumb_Walker extends Walker_Nav_Menu

class Contextual_Nav_Menu_Dropdown_Walker extends Walker_Nav_Menu {

    /**
     * Starts the list before the elements are added.
     *
     * The $args parameter holds additional values that may be used with the child
     * class methods. This method is called at the start of the output list.
     *
     * @since 2.1.0
     * @abstract
     *
     * @param string $output Passed by reference. Used to append additional content.
     * @param int    $depth  Depth of the item.
     * @param array  $args   An array of additional arguments.
     */
    function start_lvl( &$output, $depth = 0, $args = array() ){} // END function start_lvl

    /**
     * Ends the list of after the elements are added.
     *
     * The $args parameter holds additional values that may be used with the child
     * class methods. This method finishes the list at the end of output of the elements.
     *
     * @since 2.1.0
     * @abstract
     *
     * @param string $output Passed by reference. Used to append additional content.
     * @param int    $depth  Depth of the item.
     * @param array  $args   An array of additional arguments.
     */
    function end_lvl( &$output, $depth = 0, $args = array() ){} // END function end_lvl

    /**
     * Start the element output.
     *
     * The $args parameter holds additional values that may be used with the child
     * class methods. Includes the element output also.
     *
     * @since 2.1.0
     * @abstract
     *
     * @param string $output            Passed by reference. Used to append additional content.
     * @param item $object            The data object.
     * @param int    $depth             Depth of the item.
     * @param array  $args              An array of additional arguments.
     * @param int    $current_object_id ID of the current item.
     */
    function start_el( &$output, $item, $depth = 0, $args = array(), $current_object_id = 0 ){
        
        global $menu_item_selected;

        // add spacing to the title based on the depth
        $item->title = str_repeat( '&nbsp;', $depth * 8 ) . $item->title;

        $attributes  = '';  
        $attributes .= ! empty( $item->ID ) ? ' value="' . esc_attr( $item->ID ) . '"' : '';  
        $attributes .= ! empty( $menu_item_selected ) && $item->ID == $menu_item_selected ? ' selected="selected"' : '';
        
        $item_output  = '';  
        $item_output .= '<option' . $attributes . '>';  
        $item_output .= $item->title;  

        $output .= $item_output;

    } // END function start_el

    /**
     * Ends the element output, if needed.
     *
     * The $args parameter holds additional values that may be used with the child class methods.
     *
     * @since 2.1.0
     * @abstract
     *
     * @param string $output Passed by reference. Used to append additional content.
     * @param item $object The data object.
     * @param int    $depth  Depth of the item.
     * @param array  $args   An array of additional arguments.
     */
    function end_el( &$output, $item, $depth = 0, $args = array() ){
        
        $output .= '</option>';

    } // END function end_el
    
} // END class Contextual_Nav_Menu_Dropdown_Walker extends Walker_Nav_Menu
