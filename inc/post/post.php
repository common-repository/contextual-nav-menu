<?php

class Contextual_Nav_Menu_Relation_Post {
	
	// The Post URL
	public $post_url;

	// The Post Assets URL
	public $post_assets_url;
	
	// The Post Path
	public $post_path;

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
		$this->post_url        = plugins_url( trailingslashit( basename( __DIR__ ) ), dirname( __FILE__ ) );
		$this->post_assets_url = $this->post_url . 'assets/';
		$this->post_path       = plugin_dir_path( __FILE__ );
		
		// Add actions and filters
		$this->actions_filters();

	} // END public function init

	/**
	 * Register Actions and Filters
	 *
	 * @return  null
	 */
	public function actions_filters() {
		
		// Add a Post Metabox
		add_action( 'add_meta_boxes', array( &$this, 'meta_box' ) );
		
		// Save Metadatas
		add_action( 'save_post', array( &$this, 'save_metadatas' ) );
		
		// Enqueue some scripts in admin
		add_action( 'admin_enqueue_scripts', array( &$this, 'admin_enqueue_scripts' ) );

	} // END public function actions_filters

	/**
	 * Adds a box to the main column on the Post and Page edit screens.
	 *
	 * @return  null
	 */
	public function meta_box() {

		$post_types = get_post_types( array( 'public' => true ), 'object' );
		
		// Do not list any attachements
		unset( $post_types['attachment'] );

		foreach ( $post_types as $key => $post_type ) {
			add_meta_box( 
				'contextual_nav_menu_relation',
				__( 'Add Parent Menu Item', 'cnm' ),
				array( &$this, 'inner_custom_box' ),
				$key,
				'side',
				'low'
			);
		}
		
	} // END public function meta_box

	/**
	 * When the post is saved, saves our custom data.
	 *
	 * @param 	int 	$post_id The ID of the post being saved.
	 *
	 * @return  null
	 */
	public function save_metadatas( $post_id ) {

		  /*
		* We need to verify this came from the our screen and with proper authorization,
		* because save_post can be triggered at other times.
		*/

		// Check if our nonce is set.
		if ( ! isset( $_POST['contextual_nav_menu_relation_inner_custom_box_nonce'] ) )
			return $post_id;

		$nonce = $_POST['contextual_nav_menu_relation_inner_custom_box_nonce'];

		// Verify that the nonce is valid.
		if ( ! wp_verify_nonce( $nonce, 'contextual_nav_menu_relation_inner_custom_box' ) )
			return $post_id;

		// If this is an autosave, our form has not been submitted, so we don't want to do anything.
		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) 
			return $post_id;

		// Check the user's permissions.
		if ( 'page' == $_POST['post_type'] ) {

			if ( ! current_user_can( 'edit_page', $post_id ) )
				return $post_id;

		} else {

			if ( ! current_user_can( 'edit_post', $post_id ) )
				return $post_id;

		}

		/* OK, its safe for us to save the data now. */

		$contextual_menu = $_POST['contextual_menu'];

		if( $contextual_menu == 'none' ) {

			// Delete the meta field in the database.
			delete_post_meta( $post_id, '_contextual_menu_item' );
			delete_post_meta( $post_id, '_contextual_menu' );

		} else {

			$contextual_menu_item = $_POST['contextual_menu_item'];
					 
			if( $contextual_menu_item != 'none' ) {

				// Update the meta field in the database.
				update_post_meta( $post_id, '_contextual_menu_item', $contextual_menu_item );
				update_post_meta( $post_id, '_contextual_menu', $contextual_menu );

			}
			else {

				// Delete the meta field in the database.
				delete_post_meta( $post_id, '_contextual_menu_item' );
				delete_post_meta( $post_id, '_contextual_menu' );

			}

		}

	} // END public function save_metadatas

	/**
	 * Prints the box content.
	 * 
	 * @param 	WP_Post 	$post The object for the current post/page.
	 *
	 * @return  null
	 */
	public function inner_custom_box( $post ) {

		// Render the post template
		include( $this->post_path . 'views/post.php' );

	} // END public function inner_custom_box

	/**
	 * Enqueue some admin scripts
	 *
	 * @param   string  $hook_suffix  the hook suffix of the current page
	 *
	 * @return  null
	 */
	public function admin_enqueue_scripts( $hook_suffix ) {

		// first check that $hook_suhfix is appropriate for your admin page
		if( 'post.php' == $hook_suffix || 'post-new.php' == $hook_suffix )
			wp_enqueue_script( 'cnm-post', $this->post_assets_url . 'js/post.js', array( 'jquery' ) );
	
	} // END public function admin_enqueue_scripts

} // END class Contextual_Nav_Menu_Relation_Post  

$Contextual_Nav_Menu_Relation_Post = new Contextual_Nav_Menu_Relation_Post();
