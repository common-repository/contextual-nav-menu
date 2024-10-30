<?php 

class Contextual_Nav_Menu_Widget extends WP_Widget {

	// The widget Path
	public $widget_path;

	/**
	 * Construct the Widget object
	 */
	function Contextual_Nav_Menu_Widget() {

		$this->widget_path = plugin_dir_path( __FILE__ );

		$widget_ops = array( 
			'classname'   => 'widget_contextual_nav_menu', 
			'description' => __( 'Display a Simple Contextual Nav Submenu', 'cnm' ), 
		);

		parent::__construct( 'contextual_nav_menu', __( 'Contextual Nav Submenu', 'cnm' ), $widget_ops, null );

	}

	/**
	 * Function that render widget
	 * 
	 * @param 	mixed 	$args
	 * @param 	mixed 	$instance
	 *
	 * @return  null
	 */
	public function widget( $args, $instance ) {
		
		static $_wrap_id = 0;
		
		$instance = wp_parse_args( $instance, $this->get_defaults() );
		extract( $args );

		$items_wrap                              = '<ul id="%1$s" class="%2$s">%3$s</ul>';
		$contextual_nav_menu_widget_title_walker = new Contextual_Nav_Menu_Widget_Title_Walker();
		$contextual_nav_menu_widget_walker       = new Contextual_Nav_Menu_Widget_Walker();
		$depth                                   = $instance['depth'] ? $instance['depth'] : 0;        
		$start_depth                             = !empty( $instance['start_depth'] ) ? absint( $instance['start_depth'] ) : 0;
		$wrap_class                              = 'cnm-menu-widget';
		$container_class                         = 'cnm-widget-container';
		$container                               = 'nav';
		$wrap_id                                 = 'cnm-widget-menu-';

		// Get menu
		$menu = wp_get_nav_menu_object( $instance['nav_menu'] );
		
		if ( !$menu )
			return;

		$contextual_nav_menu_widget_title = contextual_nav_menu( 

			array( 
				'echo'            => false, 
				'items_wrap'      => '%3$s',
				'fallback_cb'     => '', 
				'menu'            => $menu,
				'container'       => '',
				'container_class' => '', 
				'walker'          => $contextual_nav_menu_widget_title_walker, 
				'depth'           => $start_depth + 1, 
				'start_depth'     => $start_depth, 
			) 
			
		);

		$contextual_nav_menu_widget = contextual_nav_menu( 

			array( 
				'echo'            => false, 
				'items_wrap'      => '%3$s',
				'fallback_cb'     => '', 
				'container'       => '',
				'container_class' => '', 
				'menu'            => $menu, 
				'walker'          => $contextual_nav_menu_widget_walker, 
				'depth'           => $depth, 
				'start_depth'     => $start_depth, 
			) 

		);

		if ( '' == preg_replace( '/\s+/', '', strip_tags( $contextual_nav_menu_widget ) ) || empty( $contextual_nav_menu_widget_title ) )
			return;

		$widget_title = $contextual_nav_menu_widget_title;

		$nav_menu = '';

		$class    = ' class="' . $container_class . ' menu-'. $menu->slug .'-container"';
		$role     = ' role="navigation"';
		$nav_menu .= '<'. $container . $role . $class . '>';

		$wrap_id = $wrap_id . $menu->slug . '-' . $_wrap_id++;

		$nav_menu .= sprintf( $items_wrap, esc_attr( $wrap_id ), esc_attr( $wrap_class ), $contextual_nav_menu_widget );

		$nav_menu .= '</'. $container . '>';        
		
		/* render output */
		include( $this->widget_path . 'views/widget.php' );

	} // END public function widget

	/**
	 * Sanitize widget form values as they are saved.
	 *
	 * @see 	WP_Widget::update()
	 *
	 * @param 	array 					$new_instance Values just sent to be saved.
	 * @param 	array 					$old_instance Previously saved values from database.
	 *
	 * @return 	array 					Updated safe values to be saved.
	 */
	public function update( $new_instance, $old_instance ) {

		$defaults = $this->get_defaults();
		
		$new_instance['nav_menu']    = ( ! empty( $new_instance['nav_menu'] ) ) ? ( int )$new_instance['nav_menu'] : $defaults['nav_menu'];
		$new_instance['depth']       = ( ! empty( $new_instance['depth'] ) ) ? ( int )$new_instance['depth'] : $defaults['depth'];
		$new_instance['start_depth'] = ( ! empty( $new_instance['start_depth'] ) ) ? ( int )$new_instance['start_depth'] : $defaults['start_depth'];

		return $new_instance;

	} // END public function update

	/**
	 * Function that render form in Admin Widgets
	 * 
	 * @param 	mixed 		$instance
	 *
	 * @return  null
	 */
	public function form( $instance ) {
				
		// Get menus
		$menus = get_terms( 'nav_menu', array( 'hide_empty' => false ) );

		// If no menus exists, direct the user to go and create some.
		if ( !$menus ) {

			print '<p>'. sprintf( __( 'No menus have been created yet. <a href="%s">Create some</a>.' ), admin_url( 'nav-menus.php' ) ) .'</p>';
			return;

		}

		$instance = wp_parse_args( $instance, $this->get_defaults() );
		include( $this->widget_path . 'views/widget-form.php' );

	} // END public function form

	/**
	 * Default widget options
	 *
	 * @return 	array
	 */
	public function get_defaults() {

		return array( 
			'title'       => '',
			'nav_menu'    => 0,
			'depth'       => 0,
			'start_depth' => 0,
		);

	} // END public function get_defaults

	/**
	 * Auto register function
	 *
	 * @return  null
	 */
	static function register() {

		register_widget( 'Contextual_Nav_Menu_Widget' );

	} // END public function register

} // END class Contextual_Nav_Menu_Widget extends WP_Widget

add_action( 'widgets_init', array( 'Contextual_Nav_Menu_Widget', 'register' ) );
