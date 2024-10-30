<?php

class Contextual_Nav_Menu_Settings {
	
	// The Settings URL
	public $settings_url;

	// The Settings Assets URL
	public $settings_assets_url;
	
	// The Settings Path
	public $settings_path;
	
	// The settings page Suffix
	public $settings_page_suffix;

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
		
		// Initializing settings properties
		$this->settings_url        = plugins_url( trailingslashit( basename( __DIR__ ) ), dirname( __FILE__ ) );
		$this->settings_assets_url = $this->settings_url . 'assets/';
		$this->settings_path       = plugin_dir_path( __FILE__ );
		
		// Add actions and filters
		$this->actions_filters();

	} // END public function init

	/**
	 * Register Actions and Filters
	 *
	 * @return  null
	 */
	public function actions_filters() {

		// Add Settings Menu Entry
		add_action( 'admin_menu', array( &$this, 'add_menu' ) );
		
		// Register Settings
		add_action( 'admin_init', array( &$this, 'register_settings' ) );
		
		// Register Translations (PolyLang Integration)
		add_action( 'admin_init', array( &$this, 'register_translations' ) );

		// Add some scripts and styles to the settings page
		add_action( 'admin_enqueue_scripts', array( &$this, 'admin_enqueue_scripts' ) );

	} // END public function actions_filters

	/**
	 * Add an "Advanced Breadcrumbs Settings" page
	 * 
	 * @return  null
	 */
	public function add_menu() {
		
		$this->settings_page_suffix = add_options_page( 
			__( 'Contextual Nav Menu Breadcrumb Settings', 'cnm' ), 
			__( 'Contextual Nav Menu Breadcrumb', 'cnm' ), 
			'administrator', 
			'cnm', 
			array( &$this, 'settings_page' ) 
		);

	} // END public function add_menu

	/**
	 * Register Settings
	 *
	 * @return  null
	 */
	public function register_settings() {
		
		global $polylang;
		
		register_setting( 'contextual_nav_menu_breadcrumb_settings-group', 'contextual_nav_menu_breadcrumb_show_on_home' );
		register_setting( 'contextual_nav_menu_breadcrumb_settings-group', 'contextual_nav_menu_breadcrumb_home_text' );
		register_setting( 'contextual_nav_menu_breadcrumb_settings-group', 'contextual_nav_menu_breadcrumb_home_link_text' );
		register_setting( 'contextual_nav_menu_breadcrumb_settings-group', 'contextual_nav_menu_breadcrumb_home_image' );
		register_setting( 'contextual_nav_menu_breadcrumb_settings-group', 'contextual_nav_menu_breadcrumb_menu_order_list' );    
		register_setting( 'contextual_nav_menu_breadcrumb_settings-group', 'contextual_nav_menu_breadcrumb_error404_text' );    
		register_setting( 'contextual_nav_menu_breadcrumb_settings-group', 'contextual_nav_menu_breadcrumb_search_text' );
		register_setting( 'contextual_nav_menu_breadcrumb_settings-group', 'contextual_nav_menu_breadcrumb_tag_text' );
		register_setting( 'contextual_nav_menu_breadcrumb_settings-group', 'contextual_nav_menu_breadcrumb_author_text' );
		
		$contextual_nav_menu_list = array();
		$contextual_nav_menu = get_terms( 'nav_menu', array( 'hide_empty' => true ) );
		
		for( $i = 0; $i < count( $contextual_nav_menu ); $i++ ) {
			
			register_setting( 'contextual_nav_menu_breadcrumb_settings-group', 'contextual_nav_menu_breadcrumb_menu_order_' . $i );
			
			if( isset( $polylang ) )
				register_setting( 'contextual_nav_menu_breadcrumb_settings-group', 'contextual_nav_menu_breadcrumb_menu_language_' . $i );

		}

	} // END public function register_settings

	/**
	 * Register Translations
	 *
	 * @return  null
	 */
	public function register_translations() {
		
		global $polylang;
		
		if( isset( $polylang ) ) {
			
			pll_register_string( 'Breadcrumb Home Text', get_option( 'contextual_nav_menu_breadcrumb_home_text', __( 'Home Page', 'cnm' ) ), 'CNM' );
			pll_register_string( 'Breadcrumb Home Link Text', get_option( 'contextual_nav_menu_breadcrumb_home_link_text', __( 'Home', 'cnm' ) ), 'CNM' );
			pll_register_string( 'Breadcrumb Error Text', get_option( 'contextual_nav_menu_breadcrumb_error404_text', __( 'Not Found', 'cnm' ) ), 'CNM' );
			pll_register_string( 'Breadcrumb Search Text', get_option( 'contextual_nav_menu_breadcrumb_search_text', __( 'Search', 'cnm' ) ), 'CNM' );
			pll_register_string( 'Breadcrumb Tag Text', get_option( 'contextual_nav_menu_breadcrumb_tag_text', __( 'Tag', 'cnm' ) ), 'CNM' );
			pll_register_string( 'Breadcrumb Author Text', get_option( 'contextual_nav_menu_breadcrumb_author_text', __( 'Author', 'cnm' ) ), 'CNM' );

		}        
		
	} // END public function register_translations

	/**
	 * Display the Settings page
	 *
	 * @return  null
	 */
	public function settings_page() {

		// Render the Settings template
		include( $this->settings_path . 'views/settings.php' );
		
	} // END public function settings_page
	
	/**
	 * Enqueue Settings Page Style
	 *
	 * @param   string  $hook_suffix  the hook suffix of the current page
	 *
	 * @return  null
	 */
	public function admin_enqueue_scripts( $hook_suffix ) {

		if( $this->settings_page_suffix != $hook_suffix )
			return;
		
		wp_enqueue_media();
		wp_enqueue_script( 'cnm-settings', $this->settings_assets_url . 'js/settings.js' );
			
		$params = array(
			'imageBouton' => __( 'Choose Home Image', 'cnm' ),
		);

		wp_localize_script( 'cnm-settings', 'cnmParams', $params );

	} // END public function admin_enqueue_scripts

} // END class Contextual_Nav_Menu_Settings

// instantiate the Settings class
$Contextual_Nav_Menu_Settings = new Contextual_Nav_Menu_Settings();
