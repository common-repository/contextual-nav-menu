<?php

/**
 * Displays a contextual navigation menu.
 *
 * Optional $args contents:
 *
 * menu - The menu that is desired. Accepts ( matching in order ) id, slug, name. Defaults to blank.
 * menu_class - CSS class to use for the ul element which forms the menu. Defaults to 'menu'.
 * menu_id - The ID that is applied to the ul element which forms the menu. Defaults to the menu slug, incremented.
 * container - Whether to wrap the ul, and what to wrap it with. Defaults to 'div'.
 * container_class - the class that is applied to the container. Defaults to 'menu-{menu slug}-container'.
 * container_id - The ID that is applied to the container. Defaults to blank.
 * fallback_cb - If the menu doesn't exists, a callback function will fire. Defaults to 'wp_page_menu'. Set to false for no fallback.
 * before - Text before the link text.
 * after - Text after the link text.
 * link_before - Text before the link.
 * link_after - Text after the link.
 * echo - Whether to echo the menu or return it. Defaults to echo.
 * depth - how many levels of the hierarchy are to be included. 0 means all. Defaults to 0.
 * walker - allows a custom walker to be specified.
 * theme_location - the location in the theme to be used. Must be registered with register_nav_menu() in order to be selectable by the user.
 * items_wrap - How the list items should be wrapped. Defaults to a ul with an id and class. Uses printf() format with numbered placeholders.
 *
 * @param array $args Arguments
 *
 * @return  string/null
 */
function contextual_nav_menu( $args = array() ) {
	
	static $menu_id_slugs = array();

	$defaults = array( 
		'menu'            => '', 
		'container'       => 'div', 
		'container_class' => '', 
		'container_id'    => '', 
		'menu_class'      => 'menu', 
		'menu_id'         => '',
		'echo'            => true, 
		'fallback_cb'     => 'wp_page_menu', 
		'before'          => '', 
		'after'           => '', 
		'link_before'     => '', 
		'link_after'      => '', 
		'items_wrap'      => '<ul id="%1$s" class="%2$s">%3$s</ul>',
		'depth'           => 0, 
		'walker'          => '', 
		'theme_location'  => ''
	);

	$args = wp_parse_args( $args, $defaults );

	/**
	 * Filter the arguments used to display a navigation menu.
	 *
	 * @param array $args Arguments from {@see contextual_nav_menu()}.
	 */
	$args = apply_filters( 'contextual_nav_menu_args', $args );
	$args = ( object ) $args;

	// Get the nav menu based on the requested menu
	$menu = wp_get_nav_menu_object( $args->menu );

	// Get the nav menu based on the theme_location
	if ( ! $menu && $args->theme_location && ( $locations = get_nav_menu_locations() ) && isset( $locations[ $args->theme_location ] ) )
		$menu = wp_get_nav_menu_object( $locations[ $args->theme_location ] );

	// get the first menu that has items if we still can't find a menu
	if ( ! $menu && !$args->theme_location ) {

		$menus = wp_get_nav_menus();

		foreach ( $menus as $menu_maybe ) {

			if ( $menu_items = wp_get_nav_menu_items( $menu_maybe->term_id, array( 'update_post_term_cache' => false ) ) ) {
				
				$menu = $menu_maybe;
				break;

			}

		}

	}

	// If the menu exists, get its items.
	if ( $menu && ! is_wp_error( $menu ) && !isset( $menu_items ) )
		$menu_items = wp_get_nav_menu_items( $menu->term_id, array( 'update_post_term_cache' => false ) );

	/*
	 * If no menu was found:
	 *  - Fall back ( if one was specified ), or bail.
	 *
	 * If no menu items were found:
	 *  - Fall back, but only if no theme location was specified.
	 *  - Otherwise, bail.
	 */
	if ( ( !$menu || is_wp_error( $menu ) || ( isset( $menu_items ) && empty( $menu_items ) && !$args->theme_location ) )
		&& $args->fallback_cb && is_callable( $args->fallback_cb ) )
			return call_user_func( $args->fallback_cb, ( array ) $args );

	if ( ! $menu || is_wp_error( $menu ) )
		return false;

	$nav_menu = $items = '';

	$show_container = false;
	
	if ( $args->container ) {
		
		/**
		 * Filter the list of HTML tags that are valid for use as menu containers.
		 *
		 * @param array The acceptable HTML tags for use as menu containers, defaults as 'div' and 'nav'.
		 */
		$allowed_tags = apply_filters( 'contextual_nav_menu_container_allowedtags', array( 'div', 'nav' ) );
		
		if ( in_array( $args->container, $allowed_tags ) ) {
			
			$show_container = true;
			$class = $args->container_class ? ' class="' . esc_attr( $args->container_class ) . '"' : ' class="menu-'. $menu->slug .'-container"';
			$id = $args->container_id ? ' id="' . esc_attr( $args->container_id ) . '"' : '';
			$nav_menu .= '<'. $args->container . $id . $class . '>';
		
		}

	}

	// Set up the $menu_item variables
	_contextual_menu_item_classes_by_context( $menu_items );

	$sorted_menu_items = $menu_items_with_children = array();
	
	foreach ( ( array ) $menu_items as $menu_item ) {
		
		$sorted_menu_items[ $menu_item->menu_order ] = $menu_item;
		
		if ( $menu_item->menu_item_parent )
			$menu_items_with_children[ $menu_item->menu_item_parent ] = true;
	
	}

	// Add the menu-item-has-children class where applicable
	if ( $menu_items_with_children ) {
		
		foreach ( $sorted_menu_items as &$menu_item ) {
			
			if ( isset( $menu_items_with_children[ $menu_item->ID ] ) )
				$menu_item->classes[] = 'menu-item-has-children';
		
		}
	
	}

	unset( $menu_items, $menu_item );

	/**
	 * Filter the sorted list of menu item objects before generating the menu's HTML.
	 *
	 * @param array $sorted_menu_items The menu items, sorted by each menu item's menu order.
	 */
	$sorted_menu_items = apply_filters( 'contextual_nav_menu_objects', $sorted_menu_items, $args );
	$items             .= walk_nav_menu_tree( $sorted_menu_items, $args->depth, $args );
	
	unset( $sorted_menu_items );

	// Attributes
	if ( ! empty( $args->menu_id ) ) {
		
		$wrap_id = $args->menu_id;

	} else {

		$wrap_id = 'menu-' . $menu->slug;
		
		while ( in_array( $wrap_id, $menu_id_slugs ) ) {
			
			if ( preg_match( '#-( \d+ )$#', $wrap_id, $matches ) )
				$wrap_id = preg_replace( '#-( \d+ )$#', '-' . ++$matches[1], $wrap_id );
			else
				$wrap_id = $wrap_id . '-1';

		}

	}

	$menu_id_slugs[] = $wrap_id;
	$wrap_class      = $args->menu_class ? $args->menu_class : '';

	/**
	 * Filter the HTML list content for navigation menus.
	 *
	 * @param string $items The HTML list content for the menu items.
	 * @param array $args Arguments from {@see contextual_nav_menu()}.
	 */
	$items = apply_filters( 'contextual_nav_menu_items', $items, $args );
	/**
	 * Filter the HTML list content for a specific navigation menu.
	 *
	 * @param string $items The HTML list content for the menu items.
	 * @param array $args Arguments from {@see contextual_nav_menu()}.
	 */
	$items = apply_filters( "contextual_nav_menu_{$menu->slug}_items", $items, $args );

	// Don't print any markup if there are no items at this point.
	if ( empty( $items ) )
		return false;

	$nav_menu .= sprintf( $args->items_wrap, esc_attr( $wrap_id ), esc_attr( $wrap_class ), $items );
	unset( $items );

	if ( $show_container )
		$nav_menu .= '</' . $args->container . '>';

	/**
	 * Filter the HTML content for navigation menus.
	 *
	 * @param string $nav_menu The HTML content for the navigation menu.
	 * @param array $args Arguments from {@see contextual_nav_menu()}.
	 */
	$nav_menu = apply_filters( 'contextual_nav_menu', $nav_menu, $args );

	if ( $args->echo )
		echo $nav_menu;
	else
		return $nav_menu;

}

/**
 * Displays a contextual breadcrumb navigation menu.
 *
 * Optional $args contents:
 *
 * menu_class - CSS class to use for the ol element which forms the menu. Defaults to 'breadcrumb'.
 * container - Whether to wrap the ol, and what to wrap it with. Defaults to 'nav'.
 * container_role - The role of the container.
 * container_class - the class that is applied to the container. Defaults to 'nav-menu-breadcrumb'.
 * container_id - The ID that is applied to the container. Defaults to blank.
 * fallback_cb - If the menu doesn't exists, a callback function will fire. Defaults to 'wp_nav_menu'. Set to false for no fallback.
 * before - Text before the link text.
 * after - Text after the link text.
 * link_before - Text before the link.
 * link_after - Text after the link.
 * echo - Whether to echo the menu or return it. Defaults to echo.
 * depth - how many levels of the hierarchy are to be included. 0 means all. Defaults to 0.
 *
 * @param array $args Arguments
 *
 * @return  null
 */
function contextual_nav_menu_breadcrumb( $args = array() ) {
	
	global $cnm_bc_is_closed, $polylang;
	static $_menu_id = 0;
	
	$cnm_bc_is_closed = false;
	
	$defaults = array( 
		'home_link_class' => 'nav-menu-breadcrumb-home-link', 
		'container'       => 'nav', 
		'container_role'  => 'navigation', 
		'container_class' => 'nav-menu-breadcrumb', 
		'fallback_cb'     => 'wp_nav_menu', 
		'menu_class'      => 'breadcrumb', 
		'echo'            => 1, 
	);

	$args = wp_parse_args( $args, $defaults );
	
	$container       = $args[ 'container' ];
	$container_class = $args[ 'container_class' ];
	$home_link_class = $args[ 'home_link_class' ];
	$menu_class      = $args[ 'menu_class' ];
	$echo            = $args[ 'echo' ];
	$container_role  = $args[ 'container_role' ];
	$menu_id         = 'breacrumb-menu-' . $_menu_id++;
	
	$args[ 'echo' ]            = false;
	$args[ 'container_class' ] = '';
	$args[ 'menu_class' ]      = '';
	$args[ 'container' ]       = '';
	$args[ 'items_wrap' ]      = '%3$s';
	$args[ 'walker' ]          = new Contextual_Nav_Menu_Breadcrumb_Walker();
	
	$show_on_home = get_option( 'contextual_nav_menu_breadcrumb_show_on_home', true );
	$breadcrumb   = '';
	
	if( !is_front_page() ) {
		
		$menu_order_numbers = get_option( 'contextual_nav_menu_breadcrumb_menu_order_list' );
		$nav_menus_list     = array();
		
		if( $menu_order_numbers == '' ) {

			$nav_menus = get_terms( 'nav_menu', array( 'hide_empty' => true ) );
			
			foreach( $nav_menus as $menu )
				array_push( $nav_menus_list, $menu->term_id );

		}
		else {

			$nav_menus = explode( ',', $menu_order_numbers );
			
			$index = 0;

			foreach( $nav_menus as $nav_menu_id ) {

				$nav_menu_id = (int) $nav_menu_id;
				
				if( $nav_menu_id ) {
					
					if( isset( $polylang ) ) {
						
						$language = pll_current_language();
						$menu_language = get_option( 'contextual_nav_menu_breadcrumb_menu_language_' . $index, true );
						
						if( empty( $menu_language ) || $menu_language == $language )
							array_push( $nav_menus_list, $nav_menu_id );

					}
					else
						array_push( $nav_menus_list, $nav_menu_id );
					
					$index++;

				}
				
			}

		}
		
		foreach( $nav_menus_list as $nav_menu_id ) {
			
			$args[ 'menu' ] = $nav_menu_id;
			
			$breadcrumb .= contextual_nav_menu( $args );
			
			if( !empty( $breadcrumb ) )
				break;

		}
		
		if( empty( $breadcrumb ) )
			_alternative_contextual_nav_menu_breadcrumb( $breadcrumb );

	}
	
	if( !empty( $breadcrumb ) || ( $show_on_home && is_front_page() ) ) {
		cnm_ob_start();
		
		?>

			<<?php print $container; ?> class="<?php print $container_class; ?>" role="<?php print $container_role; ?>">
				
				<ol id="<?php print $menu_id; ?>" class="<?php print $menu_class; ?>">
					
					<?php
						
						$blog_description = get_bloginfo( 'description' );
						$blog_name        = get_bloginfo( 'name' );
						$home_link_text   = cnm__( get_option( 'contextual_nav_menu_breadcrumb_home_link_text', __( 'Home', 'cnm' ) ) );
						$home_title       = !empty( $blog_description ) ? sprintf( __( '%1s, %2s - %3s', 'cnm' ), $blog_name, $blog_description, $home_link_text ) : sprintf( __( '%1s - %2s', 'cnm' ), $blog_name, $home_link_text );
						
						$home_image = get_option( 'contextual_nav_menu_breadcrumb_home_image', null );
						$home_text  = cnm__( get_option( 'contextual_nav_menu_breadcrumb_home_link_text', __( 'Home', 'cnm' ) ) );
						
						if( !empty( $home_image ) ) {

							$image_src = wp_get_attachment_image_src( $home_image, 'full' );
							$image_src = $image_src[0];
							
							$home_html = '<img alt="' . esc_attr( $home_text ) . '" src="' . $image_src . '" />';

						} else {

							$home_html = $home_text;

						}

					?>

					<li class="home-menu-item"><a class="<?php print $home_link_class; ?>" title="<?php print esc_attr( $home_title ); ?>" href="<?php bloginfo( 'url' ); ?>"><span><?php print $home_html; ?></span></a></li>
					
					<?php print $breadcrumb; ?>
					
					<?php 
						
						if( !$cnm_bc_is_closed ) {
							
							?><li class="current-menu-item"><span><?php _contextual_nav_menu_breadcrumb_title(); ?></span></li><?php

						}

					?>

				</ol>

			</<?php print $container; ?>>

		<?php
		
		$breadcrumb = cnm_ob_get_clean();
	}
	
	if ( $echo )
		echo $breadcrumb;
	else
		return $breadcrumb;

}

/**
 * Add the class property classes for the current context, if applicable.
 *
 * @access private
 *
 * @param array $menu_items The current menu item objects to which to add the class property information.
 */
function _contextual_menu_item_classes_by_context( &$menu_items ) {
	
	global $wp_query, $wp_rewrite;

	$queried_object    = $wp_query->get_queried_object();
	$queried_object_id = ( int ) $wp_query->queried_object_id;

	$active_object               = '';
	$active_ancestor_item_ids    = array();
	$active_parent_item_ids      = array();
	$active_parent_object_ids    = array();
	$possible_taxonomy_ancestors = array();
	$possible_object_parents     = array();
	$home_page_id                = ( int ) get_option( 'page_for_posts' );

	if ( $wp_query->is_singular && ! empty( $queried_object->post_type ) && ! is_post_type_hierarchical( $queried_object->post_type ) ) {
		
		foreach ( ( array ) get_object_taxonomies( $queried_object->post_type ) as $taxonomy ) {
			
			if ( is_taxonomy_hierarchical( $taxonomy ) ) {
				
				$term_hierarchy = _get_term_hierarchy( $taxonomy );
				$terms          = wp_get_object_terms( $queried_object_id, $taxonomy, array( 'fields' => 'ids' ) );
				
				if ( is_array( $terms ) ) {

					$possible_object_parents = array_merge( $possible_object_parents, $terms );
					$term_to_ancestor        = array();

					foreach ( ( array ) $term_hierarchy as $anc => $descs ) {
						
						foreach ( ( array ) $descs as $desc )
							$term_to_ancestor[ $desc ] = $anc;

					}

					foreach ( $terms as $desc ) {
						
						do {

							$possible_taxonomy_ancestors[ $taxonomy ][] = $desc;
							
							if ( isset( $term_to_ancestor[ $desc ] ) ) {

								$_desc = $term_to_ancestor[ $desc ];
								unset( $term_to_ancestor[ $desc ] );
								$desc = $_desc;

							} else {

								$desc = 0;

							}

						} while ( ! empty( $desc ) );

					}

				}

			}

		}

	} elseif ( ! empty( $queried_object->taxonomy ) && is_taxonomy_hierarchical( $queried_object->taxonomy ) ) {
		
		$term_hierarchy   = _get_term_hierarchy( $queried_object->taxonomy );
		$term_to_ancestor = array();

		foreach ( ( array ) $term_hierarchy as $anc => $descs ) {
			
			foreach ( ( array ) $descs as $desc )
				$term_to_ancestor[ $desc ] = $anc;

		}

		$desc = $queried_object->term_id;
		
		do {
			
			$possible_taxonomy_ancestors[ $queried_object->taxonomy ][] = $desc;
			
			if ( isset( $term_to_ancestor[ $desc ] ) ) {

				$_desc = $term_to_ancestor[ $desc ];
				unset( $term_to_ancestor[ $desc ] );
				$desc = $_desc;

			} else {

				$desc = 0;

			}

		} while ( ! empty( $desc ) );

	}

	$possible_object_parents = array_filter( $possible_object_parents );
	$front_page_url          = home_url();
	
	foreach ( ( array ) $menu_items as $key => $menu_item ) {

		$menu_items[$key]->current = false;
			
		if( empty( $menu_item->attr_title ) )
			$menu_items[$key]->attr_title = $menu_item->title;

		$classes   = ( array ) $menu_item->classes;
		$classes[] = 'menu-item';
		$classes[] = 'menu-item-type-' . $menu_item->type;
		$classes[] = 'menu-item-object-' . $menu_item->object;

		// if the menu item corresponds to a taxonomy term for the currently-queried non-hierarchical post object
		if ( $wp_query->is_singular && 'taxonomy' == $menu_item->type && in_array( $menu_item->object_id, $possible_object_parents ) ) {
			
			$active_parent_object_ids[] = ( int ) $menu_item->object_id;
			$active_parent_item_ids[]   = ( int ) $menu_item->db_id;
			$active_object              = $queried_object->post_type;

		// if the menu item corresponds to the currently-queried post or taxonomy object
		} elseif ( 
			$menu_item->object_id == $queried_object_id &&
			( 
				( ! empty( $home_page_id ) && 'post_type' == $menu_item->type && $wp_query->is_home && $home_page_id == $menu_item->object_id ) ||
				( 'post_type' == $menu_item->type && $wp_query->is_singular ) ||
				( 'taxonomy' == $menu_item->type && ( $wp_query->is_category || $wp_query->is_tag || $wp_query->is_tax ) && $queried_object->taxonomy == $menu_item->object )
			)
		) {

			$classes[]                    = 'current-menu-item';
			$menu_items[$key]->current    = true;
			$_anc_id                      = ( int ) $menu_item->db_id;
			
			$menu_items[$key]->attr_title = sprintf( __( '%1s - current page', 'cnm' ), $menu_items[$key]->attr_title );

			while( 
				( $_anc_id = get_post_meta( $_anc_id, '_menu_item_menu_item_parent', true ) ) &&
				! in_array( $_anc_id, $active_ancestor_item_ids )
			) {

				$active_ancestor_item_ids[] = $_anc_id;

			}

			if ( 'post_type' == $menu_item->type && 'page' == $menu_item->object ) {
				
				// Back compat classes for pages to match wp_page_menu()
				$classes[] = 'page_item';
				$classes[] = 'page-item-' . $menu_item->object_id;
				$classes[] = 'current_page_item';

			}

			$active_parent_item_ids[]   = ( int ) $menu_item->menu_item_parent;
			$active_parent_object_ids[] = ( int ) $menu_item->post_parent;
			$active_object              = $menu_item->object;

		// if the menu item corresponds to the currently-requested URL
		} elseif ( 'custom' == $menu_item->object ) {

			$_root_relative_current = untrailingslashit( $_SERVER['REQUEST_URI'] );
			$current_url            = set_url_scheme( 'http://' . $_SERVER['HTTP_HOST'] . $_root_relative_current );
			$raw_item_url           = strpos( $menu_item->url, '#' ) ? substr( $menu_item->url, 0, strpos( $menu_item->url, '#' ) ) : $menu_item->url;
			$item_url               = untrailingslashit( $raw_item_url );
			$_indexless_current     = untrailingslashit( preg_replace( '/' . preg_quote( $wp_rewrite->index, '/' ) . '$/', '', $current_url ) );

			if ( $raw_item_url && in_array( $item_url, array( $current_url, $_indexless_current, $_root_relative_current ) ) ) {
				
				$classes[]                 = 'current-menu-item';
				$menu_items[$key]->current = true;
				$_anc_id                   = ( int ) $menu_item->db_id;

				while( 
					( $_anc_id = get_post_meta( $_anc_id, '_menu_item_menu_item_parent', true ) ) &&
					! in_array( $_anc_id, $active_ancestor_item_ids )
				) {

					$active_ancestor_item_ids[] = $_anc_id;

				}

				if ( in_array( home_url(), array( untrailingslashit( $current_url ), untrailingslashit( $_indexless_current ) ) ) ) {
					
					// Back compat for home link to match wp_page_menu()
					$classes[] = 'current_page_item';

				}

				$active_parent_item_ids[] = ( int ) $menu_item->menu_item_parent;
				$active_parent_object_ids[] = ( int ) $menu_item->post_parent;
				$active_object = $menu_item->object;

			// give front page item current-menu-item class when extra query arguments involved
			} elseif ( $item_url == $front_page_url && is_front_page() ) {
				
				$classes[] = 'current-menu-item';

			}

			if ( untrailingslashit( $item_url ) == home_url() )
				$classes[] = 'menu-item-home';

		}

		// back-compat with wp_page_menu: add "current_page_parent" to static home page link for any non-page query
		if ( ! empty( $home_page_id ) && 'post_type' == $menu_item->type && empty( $wp_query->is_page ) && $home_page_id == $menu_item->object_id )
			$classes[] = 'current_page_parent';

		$menu_items[$key]->classes = array_unique( $classes );

	}

	$active_ancestor_item_ids = array_filter( array_unique( $active_ancestor_item_ids ) );
	$active_parent_item_ids   = array_filter( array_unique( $active_parent_item_ids ) );
	$active_parent_object_ids = array_filter( array_unique( $active_parent_object_ids ) );
					 
	$contextual_menu_item = '';

	if( $wp_query->is_single ) {
		
		$contextual_menu_item = get_post_meta( $queried_object->ID, '_contextual_menu_item', true );    
	
	} 
	elseif( $wp_query->is_category ) {
		
		$contextual_menu_item = get_option( 'contextual_menu_item' );    
		$contextual_menu_item = isset( $contextual_menu_item[ 'category-' . $queried_object->term_id ] ) && isset( $contextual_menu_item[ 'category-' . $queried_object->term_id ][ 'contextual_menu_item' ] ) ? $contextual_menu_item[ 'category-' . $queried_object->term_id ][ 'contextual_menu_item' ] : '';    
	
	}
	elseif( $wp_query->is_tag ) {

		$contextual_menu_item = get_option( 'contextual_menu_item' );    
		$contextual_menu_item = isset( $contextual_menu_item[ 'post_tag-' . $queried_object->term_id ] ) && isset( $contextual_menu_item[ 'post_tag-' . $queried_object->term_id ][ 'contextual_menu_item' ] ) ? $contextual_menu_item[ 'post_tag-' . $queried_object->term_id ][ 'contextual_menu_item' ] : '';    
	
	}

	$menu_items = array_reverse( $menu_items );
	
	// set parent's class
	foreach ( ( array ) $menu_items as $key => $parent_item ) {
		
		$classes                                 = ( array ) $parent_item->classes;
		$menu_items[$key]->current_item_ancestor = false;
		$menu_items[$key]->current_item_parent   = false;
		
		if( !empty( $contextual_menu_item ) && $contextual_menu_item == $parent_item->db_id ) {
			
			$active_ancestor_item_ids[] = ( int ) $parent_item->db_id;

		}
		
		if ( 
			isset( $parent_item->type ) &&
			( 
				// ancestral post object
				( 
					'post_type' == $parent_item->type &&
					! empty( $queried_object->post_type ) &&
					is_post_type_hierarchical( $queried_object->post_type ) &&
					in_array( $parent_item->object_id, $queried_object->ancestors ) &&
					$parent_item->object != $queried_object->ID
				) ||

				// ancestral term
				( 
					'taxonomy' == $parent_item->type &&
					isset( $possible_taxonomy_ancestors[ $parent_item->object ] ) &&
					in_array( $parent_item->object_id, $possible_taxonomy_ancestors[ $parent_item->object ] ) &&
					( 
						! isset( $queried_object->term_id ) ||
						$parent_item->object_id != $queried_object->term_id
					)
				)
			)
		) {

			$classes[]                  = empty( $queried_object->taxonomy ) ? 'current-' . $queried_object->post_type . '-ancestor' : 'current-' . $queried_object->taxonomy . '-ancestor';
			$active_ancestor_item_ids[] = ( int ) $parent_item->db_id;
		
		}       

		if ( in_array(  intval( $parent_item->db_id ), $active_ancestor_item_ids ) ) {
			
			$classes[]                               = 'current-menu-ancestor';
			$menu_items[$key]->current_item_ancestor = true;
			
			if( $parent_item->menu_item_parent ) {
				
				$active_ancestor_item_ids[] = ( int ) $parent_item->menu_item_parent;

			}

		}
		if ( in_array( $parent_item->db_id, $active_parent_item_ids ) ) {
			
			$classes[]                             = 'current-menu-parent';
			$menu_items[$key]->current_item_parent = true;

		}
		if ( in_array( $parent_item->object_id, $active_parent_object_ids ) )
			$classes[] = 'current-' . $active_object . '-parent';

		if ( 'post_type' == $parent_item->type && 'page' == $parent_item->object ) {
			
			// Back compat classes for pages to match wp_page_menu()
			if ( in_array( 'current-menu-parent', $classes ) )
				$classes[] = 'current_page_parent';

			if ( in_array( 'current-menu-ancestor', $classes ) )
				$classes[] = 'current_page_ancestor';

		}

		$menu_items[$key]->classes = array_unique( $classes );
	}
	
	$menu_items = array_reverse( $menu_items );

}

/**
 * Build an alternative breacrumb for page not related to any menu
 *
 * @access private
 *
 * @param string $breadcrumb the breadcrumb.
 *
 * @return  null
 */
function _alternative_contextual_nav_menu_breadcrumb( &$breadcrumb ) {
	
	global $cnm_bc_is_closed, $post, $cat;
	
	$cnm_bc_is_closed       = true;
	$current_item_class     = 'current-menu-item';
	$current_ancestor_class = 'current-menu-ancestor';

	if( is_404() ) {

		$breadcrumb .= '<li class="' . $current_item_class . '"><span>' . cnm__( get_option( 'contextual_nav_menu_breadcrumb_error404_text', __( 'Not Found', 'cnm' ) ) ) . '</span></li>';
	
	}
	elseif( is_search() ) {

		$breadcrumb .= '<li class="' . $current_item_class . '"><span>' . sprintf( cnm__( get_option( 'contextual_nav_menu_breadcrumb_search_text', __( 'Search for terms', 'cnm' ) ) ) .  ' "%1s"', get_search_query() ) . '</span></li>';
	
	}
	elseif( is_day() ) {

		$breadcrumb .= '<li class="' . $current_ancestor_class . '"><a href="' . get_year_link( get_the_time( 'Y' ) ) . '"><span>' . get_the_time( 'Y' ) . '</span></a></li>';
		$breadcrumb .= '<li class="' . $current_ancestor_class . '"><a href="' . get_month_link( get_the_time( 'Y' ), get_the_time( 'm' ) ) . '"><span>' . get_the_time( 'F' ) . '</span></a></li>';
		$breadcrumb .= '<li class="' . $current_item_class . '"><span>' . get_the_time( 'd' ) . '</span></li>';
	
	}
	elseif( is_month() ) {

		$breadcrumb .= '<li class="' . $current_ancestor_class . '"><a href="' . get_year_link( get_the_time( 'Y' ) ) . '"><span>' . get_the_time( 'Y' ) . '</span></a></li>';
		$breadcrumb .= '<li class="' . $current_item_class . '"><span>' . get_the_time( 'F' ) . '</span></li>';
	
	}
	elseif( is_year() ) {

		$breadcrumb .= '<li class="' . $current_item_class . '"><span>' . get_the_time( 'Y' ) . '</span></li>';
	
	}
	elseif( is_attachment() ) {

		$attachmentParent = get_post( $post->post_parent );
		$parent           = get_post( $attachmentParent->ID );
		$breadcrumb       .= '<li class="' . $current_ancestor_class . '"><a href="' . get_permalink( $attachmentParent ) . '"><span>' . $parent->post_title . '</span></a></li>';
		$breadcrumb       .= '<li class="' . $current_item_class . '"><span>' . get_the_title() . '</span></li>';
	
	}
	elseif( is_tag() ) {

		$breadcrumb .= '<li class="' . $current_item_class . '"><span>' . sprintf( cnm__( get_option( 'contextual_nav_menu_breadcrumb_tag_text', __( 'Tag', 'cnm' ) ) ) .  ' "%1s"', single_tag_title( '', false ) ) . '</span></li>';
	
	}
	elseif( is_author() ) {

		global $author;
		$userinfo   = get_userdata( $author );
		$breadcrumb .= '<li class="' . $current_item_class . '"><span>' . sprintf( cnm__( get_option( 'contextual_nav_menu_breadcrumb_author_text', __( 'Author', 'cnm' ) ) ) .  ' "%1s"', $userinfo->display_name ) . '</span></li>';
	
	}
	elseif( is_single() ) {
		
		$cat = get_the_category();
		
		if( isset( $cat[0] ) ) {

			$category = get_category( $cat[0]->term_id, false );
			
			if( $category->parent != 0 ) {

				$categories = get_category_parents( $cat[0]->term_id, false, ', ' );
				$categories = explode( ', ', $categories );
				
				foreach( $categories as $_cat ) {
					
					if( get_cat_ID( $_cat ) != 0 ) {
						
						$cat_name = get_category( get_cat_ID( $_cat ) )->name;
						$breadcrumb .= '<li class="' . $current_ancestor_class . '"><a href="' . get_category_link( get_cat_ID( $_cat ) ) . '"><span>' . $cat_name . '</span></a></li>';
					
					}

				}

				$breadcrumb .= '<li class="' . $current_item_class . '"><span>' . get_the_title() . '</span></li>';
			}
			else {

				$cat        = get_the_category();
				$breadcrumb .= '<li class="' . $current_ancestor_class . '"><a href="' . get_category_link( $cat[0]->term_id ) . '"><span>' . $cat[0]->name . '</span></a></li>';
				$breadcrumb .= '<li class="' . $current_item_class . '"><span>' . get_the_title() . '</span></li>';
			
			}

		}

	}
	elseif( is_category() ) { 

		$category = get_category( $cat, false );

		if ( $category->parent != 0 ) {
			
			$categories    = get_category_parents( $cat, false, ', ' );
			$categories    = explode( ', ', $categories );
			$categorycount = 0;

			foreach( $categories as $_cat ) {
				
				$categorycount++;

				if( $categorycount == count( $categories )-1 ) {
					
					if( get_cat_ID( $_cat ) != 0 ) {
						
						$cat_name   = get_category( get_cat_ID( $_cat ) )->name;
						$breadcrumb .= '<li class="' . $current_item_class . '"><span>' . $cat_name . '</span></li>';
					
					}

				}
				else {
					
					if( get_cat_ID( $_cat ) != 0 ) {
						
						$cat_name   = get_category( get_cat_ID( $_cat ) )->name;
						$breadcrumb .= '<li class="' . $current_ancestor_class . '"><a href="' . get_category_link( get_cat_ID( $_cat ) ) . '"><span>' . $cat_name . '</span></a></li>';
					
					}

				}

			}

		}
		else {
			
			$cat_name   = get_category( get_query_var( 'cat' ) )->name;
			$breadcrumb .= '<li class="' . $current_item_class . '"><span>' . $cat_name . '</span></li>';

		}

	}
	elseif( is_page() ) {

		$breadcrumb .= '<li class="' . $current_item_class . '"><span>' . get_the_title() . '</span></li>';
	
	}

}

/**
 * Display the breacrumb title
 *
 * @access private
 *
 * @return  null
 */
function _contextual_nav_menu_breadcrumb_title() {
	
	$title = '';
	
	if( is_single() || is_page() || is_attachment() ) {

		$title .= get_the_title();

	}
	elseif( is_tag() ) {

		$title .= sprintf( cnm__( get_option( 'contextual_nav_menu_breadcrumb_tag_text', __( 'Tag', 'cnm' ) ) ) .  ' "%1s"', single_tag_title( '', false ) );
	
	}
	elseif( is_category() ) {

		$cat_name = get_category( get_query_var( 'cat' ) )->name;
		$title    .= $cat_name;
	
	}
	elseif( is_front_page() ) {                                                                
		
		$title .= cnm__( get_option( 'contextual_nav_menu_breadcrumb_home_text', __( 'Home Page', 'cnm' ) ) );
	
	}
	
	print $title;

}

/**
 * Translation function
 * Works with PolyLang
 * Return the stranlated text
 *
 * @param   string  $txt  
 *
 * @return  string
 */
function cnm__( $txt ) {

	global $polylang;
	
	if( isset( $polylang ) ) {

		return pll__( $txt );

	} else {

		return $txt;

	}

}

/**
 * Translation function
 * Works with PolyLang
 * Prnt translated text
 *
 * @param   string  $txt
 *
 * @return  null
 */
function cnm_e( $txt ) {
	
	global $polylang;
	if( isset( $polylang ) ) {

		pll_e( $txt );

	} else {

		print $txt;

	}

}

/**
* Output buffering functions
*/
global $cnm_ob_stack;
$cnm_ob_stack = array();

/**
 * Output Buffering Handler
 *
 * @param   string  $str  
 *
 * @return  string       
 */
function cnm_ob_handler( $str ) {
	
	global $cnm_ob_stack;

	end( $cnm_ob_stack );
	$cnm_ob_stack[key( $cnm_ob_stack )] .= $str;

	return '';

}

/**
 * Output buffering Strat
 *
 * @return  null
 */
function cnm_ob_start() {
	
	global $cnm_ob_stack;

	array_push( $cnm_ob_stack, '' );
	ob_start( 'cnm_ob_handler' );

}

/**
 * Output buffering Clean
 *
 * @return  string  the content
 */
function cnm_ob_get_clean() {
	
	global $cnm_ob_stack;

	ob_end_flush();

	return array_pop( $cnm_ob_stack );

}
