<?php
	/**
		* Plugin Name:       Business Matchup
		* Plugin URI:        https://wordpress.org/plugins/business-matchup/
		* Description:       Create custom polls using Yelp and Straw Polls to let your followers vote for which business they think is the best.
		* Version:           1.0.0
		* Requires at least: 5.5
		* Requires PHP:      7.0
		* Author:            Allen McKenzie
		* Contributers:		 allenmcnichols, amethystanswers
		* Author URI:        https://github.com/allen-mckenzie
		* License:           GPL v3 or later
		* License URI:       https://www.gnu.org/licenses/gpl-3.0.html
		* Update URI:        https://wordpress.org/plugins/business-matchup
		* Text Domain:       business-matchups
		* Domain Path:       /languages/
		*
		* @package         Business_Matchup
	*/

	if( !defined( 'ABSPATH' ) ) return; // None shall pass

	require realpath( plugin_dir_path( __FILE__ ) . 'vendor/autoload.php' ); // Require the composer autoloader

	/**
	 * Define the Business Matchup Polls Plugin Dir
	 * 
	 * @since 1.0.0
	 */
	define( 'BUSINESS_MATCHUP_POLLS__PLUGIN_DIR', plugin_dir_path( __FILE__ ) );

	/**
	 * Business_Matchup Class definition
	 * This is the main class for the plugin
	 * 
	 * @since 1.0.0
	 */
	final class Business_Matchup {

		/**
		 * Define the first instance of the class
		 */
		protected static $single_instance;

		/**
		 * hooks function
		 * Plugin intialization action, filters, and hooks go here
		 * 
		 * @since 1.0.0
		 */
		public function hooks() {
			$businessMatchupSettings = new \BusinessMatchup\Business_Matchup_Settings();
			add_action( 'admin_menu', array( $businessMatchupSettings, 'bm_menu' ) );
			add_action( 'save_post',      array( $businessMatchupSettings, 'save'         ) );

			$businessMatchupCPT = new \BusinessMatchup\Business_Matchup_CPT();
			add_action( 'init', array( $businessMatchupCPT, 'business_matchup_cpt' ) );

			$businessMatchupPollsPage = new \BusinessMatchup\Business_Matchup_Polls_Page();
			add_action( 'wp_enqueue_scripts', array( $businessMatchupPollsPage, 'business_matchup_styles' ) );
			add_action( 'add_meta_boxes', array( $businessMatchupPollsPage, 'add_meta_box' ) );
			add_filter( 'the_content', array( $businessMatchupPollsPage, 'business_matchup_content' ) );
			add_filter( 'generate_sidebar_layout', array( $businessMatchupPollsPage, 'business_matchup_sidebar_layout' ) );

			register_activation_hook( __FILE__, [ $this, 'activate' ] );

		}
		
		/**
		 * activate function
		 * This function runs upon plugin activation to update the permalinks option
		 * to force WordPress to refresh the permalinks in preparation of creating
		 * our custom post type.
		 * 
		 * @since 1.0.0
		 */
		public function activate() {
			update_option('plugin_permalinks_flushed', 0);
		}

		/**
		 * get_instance function
		 * This helps set up the single instance of our new class
		 * 
		 * @since 1.0.0
		 * 
		 * @return self::$single_instance of the class
		 */
		public static function get_instance() {
			if ( null === self::$single_instance ) {
				self::$single_instance = new self();
			}
	
			return self::$single_instance;
		}

	}

	/**
	 * businessMatchup function
	 * This function helps create and standup our class
	 * 
	 * @since 1.0.0
	 * 
	 * @return class Business_Matchup instance
	 */
	function businessMatchup() {
		$businessMatchup = new Business_Matchup();
		return $businessMatchup->get_instance();
	}
	add_action( 'plugins_loaded', array( businessMatchup(), 'hooks' ) );
