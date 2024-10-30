<?php

class Contextual_Nav_Menu_Relation_Bulk_Post {
	
	// The Bulk Post URL
	public $bulk_post_url;

	// The Bulk Post Assets URL
	public $bulk_post_assets_url;
	
	// The Bulk Post Path
	public $bulk_post_path;

	/**
	 * Construct the Settings Page Object
	 */
	public function __construct() {
		
		$this->init();

	} // END public function __construct

	/**
	 * Init Settings Page Object
	 *
	 * @return  null
	 */
	public function init() {
		
		// Initializing Post properties
		$this->bulk_post_url        = plugins_url( trailingslashit( basename( __DIR__ ) ), dirname( __FILE__ ) );
		$this->bulk_post_assets_url = $this->bulk_post_url . 'assets/';
		$this->bulk_post_path       = plugin_dir_path( __FILE__ );
		
		// Add actions and filters
		$this->actions_filters();

	} // END public function init

	/**
	 * Register Actions and Filters
	 *
	 * @return  null
	 */
	public function actions_filters() {
		
		// Ajax Actions
		add_action( 'wp_ajax_get_contextual_nav_menu_relation_bulk_post_inner_custom_box', array( &$this, 'bulk_post_inner_custom_box' ) );
		add_action( 'wp_ajax_update_contextual_nav_menu_relation_bulk_post', array( &$this, 'save_metadatas' ) );
		
		// Enqueue some scripts in admin
		add_action( 'admin_enqueue_scripts', array( &$this, 'admin_enqueue_scripts' ) );
		
		// Add some content in the Edit page Footer
		add_action( 'admin_footer-edit.php', array( &$this, 'admin_footer' ) );

	} // END public function actions_filters

	/**
	 * Prints the box content.
	 *
	 * @return  null
	 */
	public function bulk_post_inner_custom_box() {

		// Render the bulk-post-custom-box template
		include( $this->bulk_post_path . 'views/bulk-post-custom-box.php' );

	} // END public function bulk_inner_custom_box

	/**
	 * When Bulk Update Posts, saves our custom data.
	 *
	 * @return  null
	 */
	public function save_metadatas() {
			
		$contextual_menu      = $_POST['contextual_menu'];
		$contextual_menu_item = $_POST['contextual_menu_item'];
		$count                = 0;

		foreach( $_POST['post_ids'] as $post_id ) {

			$post_id = ( int ) $post_id;
			
			if( $post_id != 0 ) {

				// Check the user's permissions.
				if ( 'page' == $_POST['post_type'] ) {

					if ( ! current_user_can( 'edit_page', $post_id ) )
						return $post_id;

				} else {

					if ( ! current_user_can( 'edit_post', $post_id ) )
						return $post_id;

				}
				if( $contextual_menu != 'none' ) {

					if( $contextual_menu_item != 'none' ) {

						// Update the meta field in the database.
						update_post_meta( $post_id, '_contextual_menu_item', $contextual_menu_item );
						update_post_meta( $post_id, '_contextual_menu', $contextual_menu );
						$count++;

					} else {

						// Update the meta field in the database.
						delete_post_meta( $post_id, '_contextual_menu_item' );
						delete_post_meta( $post_id, '_contextual_menu' );
						$count++;

					}
				} else {

					// Update the meta field in the database.
					delete_post_meta( $post_id, '_contextual_menu_item' );
					delete_post_meta( $post_id, '_contextual_menu' );
					$count++;

				}

			}

		}
		
		printf( __( '%1d posts updated', 'cnm' ), $count ); 
		
		die;
		
	} // END public function save_metadatas

	/**
	 * Enqueue some admin scripts
	 *
	 * @param   string  $hook_suffix  the hook suffix of the current page
	 *
	 * @return  null
	 */
	public function admin_enqueue_scripts( $hook_suhfix ) {

		global $post;
		
		// first check that $hook_suhfix is appropriate for your admin page
		if( 'edit.php' == $hook_suhfix ) {

			add_thickbox();
			
			$params = array(
				'adminUrl'   => admin_url(  'admin-ajax.php'  ),
				'postType'   => isset( $post->post_type ) ? $post->post_type : 'post',
				'optionText' => __( 'Add Parent Menu Item', 'cnm' ),
			);
			
			wp_enqueue_script( 'cnm-bulk-post', $this->bulk_post_assets_url . 'js/bulk-post.js', array( 'jquery', 'thickbox' ) );
			wp_localize_script( 'cnm-bulk-post', 'cnmBulkParams', $params );
			
			wp_enqueue_style( 'cnm-bulk', $this->bulk_post_assets_url . 'css/bulk-post.css' );

		}
	
	} // END public function admin_enqueue_scripts

	/**
	 * Add bulk action on edit.php page
	 *
	 * @return  null
	 */
	public function admin_footer() {

		// Render the bulk-post-custom-box template
		include( $this->bulk_post_path . 'views/bulk-post.php' );

	} // END public function admin_footer
	
} // END class Contextual_Nav_Menu_Relation_Bulk_Post  

$Contextual_Nav_Menu_Relation_Bulk_Post = new Contextual_Nav_Menu_Relation_Bulk_Post();
