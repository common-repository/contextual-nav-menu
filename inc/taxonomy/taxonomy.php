<?php

class Contextual_Nav_Menu_Relation_Taxonomy {
	
	// The Taxonomy URL
	public $taxonomy_url;

	// The Taxonomy Assets URL
	public $taxonomy_assets_url;
	
	// The Taxonomy Path
	public $taxonomy_path;

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
		
		// Initializing Taxonomy properties
		$this->taxonomy_url        = plugins_url( trailingslashit( basename( __DIR__ ) ), dirname( __FILE__ ) );
		$this->taxonomy_assets_url = $this->taxonomy_url . 'assets/';
		$this->taxonomy_path       = plugin_dir_path( __FILE__ );
		
		// Add actions and filters
		$this->actions_filters();

	} // END public function init

	/**
	 * Register Actions and Filters
	 *
	 * @return  null
	 */
	public function actions_filters() {
		
		$taxonomies = array( 'category' => 'category', 'post_tag' => 'post_tag' );
		
		foreach( $taxonomies as $taxonomy => $value ) {
			
			// Add A Taxonomy Metabox
			add_action( $taxonomy . '_add_form_fields', array( &$this, 'add_form_fields' ) );
			
			// Add A Edit Taxonomy Metabox
			add_action( $taxonomy . '_edit_form_fields', array( &$this, 'edit_form_fields' ) );
			
			// Save Metadatas	
			add_action( 'created_' . $taxonomy, array( &$this, 'save_metadatas' ) );    
			add_action( 'edited_' . $taxonomy, array( &$this, 'save_metadatas' ) ); 

		}
		
		// Delete Metadatas
		add_action( 'delete_term', array( &$this, 'delete_metadatas' ), 10, 3 );
		
		// Enqueue some scripts in admin	
		add_action( 'admin_enqueue_scripts', array( &$this, 'admin_enqueue_scripts' ) );

	} // END public function actions_filters
	
	/**
	 * Adds a box to the main column on the Taxonomy add screen.
	 *
	 * @return  null
	 */
	public function add_form_fields() {

		// Render the taxonomy-custom-box template
		include( $this->taxonomy_path . 'views/new-taxonomy.php' );

	} // END public function add_form_fields
	
	/**
	 * Adds a box to the main column on the Taxonomy edit screen.
	 *
	 * @return  null
	 */
	public function edit_form_fields() {

		// Render the taxonomy-custom-box template
		include( $this->taxonomy_path . 'views/taxonomy.php' );

	} // END public function add_form_fields
 
	/**
	 * When the Taxonomy is saved, saves our custom datas.
	 *
	 * @param 	int 	$term_id 	The ID of the taxonomy being saved.
	 *
	 * @return  null
	 */
	public function save_metadatas( $term_id ) {

		/*
		* We need to verify this came from the our screen and with proper authorization,
		* because save_post can be triggered at other times.
		*/

		// Check if our nonce is set.
		if ( ! isset( $_POST['contextual_nav_menu_relation_inner_custom_box_nonce'] ) )
			return $term_id;

		$nonce = $_POST['contextual_nav_menu_relation_inner_custom_box_nonce'];

		// Verify that the nonce is valid.
		if ( ! wp_verify_nonce( $nonce, 'contextual_nav_menu_relation_inner_custom_box' ) )
			return $term_id;
		
		if ( ! current_user_can( 'manage_categories', $term_id ) )
			return $term_id;

		/* OK, its safe for us to save the data now. */

		$taxonomy                    = $_POST['taxonomy'];
		$contextual_menu             = $_POST['contextual_menu'];
		$contextual_menu_item        = $_POST['contextual_menu_item'];
		$count                       = 0;
		$contextual_menu_item_option = get_option( 'contextual_menu_item' );
		$tax_id                      = $taxonomy . '-' . $term_id;
		
		if( !isset( $contextual_menu_item_option ) )
			$contextual_menu_item_option = array();
		
		if( $contextual_menu != 'none' ) {

			if( $contextual_menu_item != 'none' ) {

				$contextual_menu_item_option[$tax_id] = array(
					'contextual_menu'      => $contextual_menu,
					'contextual_menu_item' => $contextual_menu_item,
				);

				update_option( 'contextual_menu_item', $contextual_menu_item_option );

			} else {

				unset( $contextual_menu_item_option[$tax_id] );
				update_option( 'contextual_menu_item', $contextual_menu_item_option );

			}

		} else {

			unset( $contextual_menu_item_option[$tax_id] );
			update_option( 'contextual_menu_item', $contextual_menu_item_option );

		}

	} // END public function save_metadatas
 
	/**
	 * When the Taxonomy is deleted, delete our custom datas.
	 *
	 * @param 	int 	$term_id 	The ID of the taxonomy being deleted.
	 *
	 * @return  null
	 */
	public function delete_metadatas( $term_id, $tt_id, $taxonomy ) {
		
		if ( ! current_user_can( 'manage_categories', $term_id ) )
			return $term_id;
		
		$contextual_menu_item = get_option( 'contextual_menu_item' );
		$tax_id               = $taxonomy . '-' . $term_id;
		
		if( !isset( $contextual_menu_item ) || !isset( $contextual_menu_item[ $tax_id ] ) )
			return $term_id;

		unset( $contextual_menu_item[ $tax_id ] );
		update_option( 'contextual_menu_item', $contextual_menu_item );
		
	} // END public function delete_metadatas

	/**
	 * Enqueue some admin scripts
	 *
	 * @param   string  $hook_suffix  the hook suffix of the current page
	 *
	 * @return  null
	 */
	public function admin_enqueue_scripts( $hook_suffix ) {

		// first check that $hook_suffix is appropriate for your admin page
		if( 'edit-tags.php' == $hook_suffix )
			wp_enqueue_script( 'cnm-taxonomy', $this->taxonomy_assets_url . 'js/taxonomy.js', array( 'jquery' ) );
	
	} // END public function admin_enqueue_scripts
	
} // END class Contextual_Nav_Menu_Relation_Taxonomy

$Contextual_Nav_Menu_Relation_Taxonomy = new Contextual_Nav_Menu_Relation_Taxonomy();
