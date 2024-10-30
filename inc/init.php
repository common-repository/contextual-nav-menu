<?php

/**
 * Init Contextual Nav Menu
 */
class Contextual_Nav_Menu_Init {
	
	// The plugin URL
	public $plugin_url;
	
	// The plugin Assets URL
	public $plugin_assets_url;

	// The plugin Inc Path
	public $plugin_inc_path;
	
	// The plugin Relative Path
	public $plugin_rel_path;

	/**
	 * Construct the plugin object
	 */
	public function __construct() {
		
		$this->init();

	} // END public function __construct

	/**
	 * Init plugin
	 *
	 * @return  null
	 */
	public function init() {
		
		// Initializing plugin properties
		$this->plugin_url        = plugins_url( '/', dirname( __FILE__ ) );
		$this->plugin_assets_url = $this->plugin_url . 'assets/';
		$this->plugin_inc_path   = plugin_dir_path( __FILE__ );
		$this->plugin_rel_path   = trailingslashit( dirname( plugin_basename( dirname( __FILE__ ) ) ) );

		$this->includes();
		
		$this->actions_filters();

	} // END public function init

	/** 
	 * Includes
	 * 
	 * @return  null
	 */
	public function includes() {

		// Add some functions
		require_once( $this->plugin_inc_path . 'functions.php' );
		
		// the Walkers
		require_once( $this->plugin_inc_path . 'walkers.php' );
		
		// The Settings Page
		require_once( $this->plugin_inc_path . 'settings/settings.php' );
		
		// Post Tools
		require_once( $this->plugin_inc_path . 'post/post.php' );
		
		// Taxonomy Tools
		require_once( $this->plugin_inc_path . 'taxonomy/taxonomy.php' );
		
		// Bulk Post Tools
		require_once( $this->plugin_inc_path . 'bulk-post/bulk-post.php' );
		
		// Bulk Taxonomy Tools
		require_once( $this->plugin_inc_path . 'bulk-taxonomy/bulk-taxonomy.php' );
		
		// The Widget
		require_once( $this->plugin_inc_path . 'widget/widget.php' );

	} // END public function includes

	/**
	 * Register Actions and Filters
	 * 
	 * @return  null
	 */
	public function actions_filters() {

		// Init Text Domain
		add_action( 'plugins_loaded', array( &$this, 'init_textdomain' ) );
		
		// Enqueue Front Scripts and Styles
		add_action( 'wp_enqueue_scripts', array( &$this, 'scripts_styles' ) );

	} // END public function actions_filters

	/**
	 * Init textdomain
	 * 
	 * @return  null
	 */
	public function init_textdomain() {
		
		load_plugin_textdomain( 'cnm', false, $this->plugin_rel_path . 'lang' );
		
	} // END public function init_textdomain

	/**
	 * Add some front style 
	 * 
	 * @return  null
	 */
	public function scripts_styles() {

		// Add Genericons font, used in the main stylesheet.
		wp_enqueue_style( 'cnm', $this->plugin_assets_url . 'css/cnm.css', array(), '20140128' );
		
	} // END public function scripts_styles

} // END class Contextual_Nav_Menu_Init

// instantiate the Init class
$Contextual_Nav_Menu_Init = new Contextual_Nav_Menu_Init();
