<?php

class Contextual_Nav_Menu_Relation_Bulk_Taxonomy {
	
	// The Bulk Taxonomy URL
	public $bulk_taxonomy_url;

	// The Bulk Taxonomy Assets URL
	public $bulk_taxonomy_assets_url;
	
	// The Bulk Taxonomy Path
	public $bulk_taxonomy_path;

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
		$this->bulk_taxonomy_url        = plugins_url( trailingslashit( basename( __DIR__ ) ), dirname( __FILE__ ) );
		$this->bulk_taxonomy_assets_url = $this->bulk_taxonomy_url . 'assets/';
		$this->bulk_taxonomy_path       = plugin_dir_path( __FILE__ );
		
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
		add_action( 'wp_ajax_get_contextual_nav_menu_relation_bulk_taxonomies_inner_custom_box', array( &$this, 'inner_custom_box' ) );
		add_action( 'wp_ajax_update_contextual_nav_menu_relation_bulk_taxonomies', array( &$this, 'save_metadatas' ) );
		
		// Enqueue some scripts in admin
		add_action( 'admin_enqueue_scripts', array( &$this, 'admin_enqueue_scripts' ) );
		
		// Add some content in the Edit Tag page Footer
		add_action( 'admin_footer-edit-tags.php', array( &$this, 'admin_footer' ) );

	} // END public function actions_filters

	/**
	 * Prints the box content.
	 *
	 * @return  null
	 */
	public function inner_custom_box() {

		// Render the bulk-post-custom-box template
		include( $this->bulk_taxonomy_path . 'views/bulk-taxonomy-custom-box.php' );

	} // END public function inner_custom_box

	/**
	 * When Bulk Update Taxonomies, saves our custom data.
	 *
	 * @return  null
	 */
	public function save_metadatas() {

		$taxonomy             = $_POST['taxonomy'];
		$contextual_menu      = $_POST['contextual_menu'];
		$contextual_menu_item = $_POST['contextual_menu_item'];
		$count                = 0;
		$contextual_menu_item = get_option( 'contextual_menu_item' );
		
		if( !isset( $contextual_menu_item ) )
			$contextual_menu_item = array();

		foreach( $_POST['cat_ids'] as $tax_id ) {

			$tax_id = (int) $tax_id;
			
			if( $tax_id != 0 ) {

				// Check the user's permissions.
				if ( ! current_user_can( 'manage_categories', $tax_id ) )
					die;                
				
				$tax_id = $taxonomy . '-' . $tax_id;
				
				if( $contextual_menu != 'none' ) {

					if( $contextual_menu_item != 'none' ) {

						$contextual_menu_item[$tax_id] = array(
							'contextual_menu'      => $contextual_menu,
							'contextual_menu_item' => $contextual_menu_item,
						);

						update_option( 'contextual_menu_item', $contextual_menu_item );
						
						$count++;

					} else {

						unset( $contextual_menu_item[$tax_id] );
						update_option( 'contextual_menu_item', $contextual_menu_item );
						
						$count++;

					}
				} else {

					unset( $contextual_menu_item[$tax_id] );
					update_option( 'contextual_menu_item', $contextual_menu_item );
					
					$count++;

				}

			}
			
		}
		
		printf( __( '%1d taxonomy updated', 'cnm' ), $count ); 
		
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
		
		global $taxonomy;
		
		// first check that $hook_suhfix is appropriate for your admin page
		if( 'edit-tags.php' == $hook_suhfix ) {

			add_thickbox();
			
			$params = array(
				'adminUrl'   => admin_url(  'admin-ajax.php'  ),
				'optionText' => __( 'Add Parent Menu Item', 'cnm' ),
				'taxonomy'   => $taxonomy,
			);
			
			wp_enqueue_script( 'cnm-bulk-taxonomy', $this->bulk_taxonomy_assets_url . 'js/bulk-taxonomy.js', array( 'jquery', 'thickbox' ) );
			wp_localize_script( 'cnm-bulk-taxonomy', 'cnmBulkParams', $params );
			
			wp_enqueue_style( 'cnm-bulk', $this->bulk_taxonomy_assets_url . 'css/bulk-taxonomy.css' );

		}
	
	} // END public function admin_enqueue_scripts

	/**
	 * Add bulk action on taxonomy page
	 *
	 * @return  null
	 */
	public function admin_footer() {

		// Render the bulk-post-custom-box template
		include( $this->bulk_taxonomy_path . 'views/bulk-taxonomy.php' );

	} // END public function admin_footer

} // END class Contextual_Nav_Menu_Relation_Bulk_Taxonomy

$Contextual_Nav_Menu_Relation_Bulk_Taxonomy = new Contextual_Nav_Menu_Relation_Bulk_Taxonomy();
