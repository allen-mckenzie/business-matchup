<?php
	/**
		* Plugin Name:       Business Matchup
		* Plugin URI:        https://github.com/allen-mckenzie/business-matchup
		* Description:       Create custom polls using Yelp and Straw Polls to let your followers vote for which business they think is the best.
		* Version:           0.1.3
		* Requires at least: 5.5
		* Requires PHP:      7.0
		* Author:            Allen McKenzie
		* Contributers:		 allenmcnichols, amethystanswers
		* Author URI:        https://github.com/allen-mckenzie
		* License:           GPL v3 or later
		* License URI:       https://www.gnu.org/licenses/gpl-3.0.html
		* Update URI:        https://github.com/allen-mckenzie/business-matchup/releases
		* Text Domain:       business-matchups
		* Domain Path:       /languages
		*
		* @package         Business_Matchup
	*/

	if( !defined( 'ABSPATH' ) ) return; // None shall pass

	/**
	 * Register the Autoloader
	 * 
	 * @since 0.1.3
	 */
	spl_autoload_register('business_matchup_autoloader');

	/**
	 * business_matchup_autoloader function
	 * Include our classes
	 * All Business Matchup classes are prefixed with `Business_Matchup`
	 * 
	 * @since 0.1.3
	 * 
	 * @param type $class string containing file name.
	 */
	function business_matchup_autoloader( $class ) {

		$namespace = 'BusinessMatchup';

		if( strpos( $class, $namespace ) !== 0 ) {
			return;
		}

		$class = str_replace( $namespace, '', $class );
		$class = str_replace( '\\', '',$class );
		$class = str_replace( '\\', DIRECTORY_SEPARATOR, 'class-' . strtolower( $class ) );
		$class = str_replace( '_', '-',$class );

		$path = dirname(__FILE__) .  DIRECTORY_SEPARATOR . 'includes/classes' . DIRECTORY_SEPARATOR . $class . '.php';
	 
		if ( file_exists( $path ) ) {
			require_once $path;
		}

	}

	/**
	 * Define the Business Matchup Polls Plugin Dir
	 * 
	 * @since 0.1.0
	 */
	define( 'BUSINESS_MATCHUP_POLLS__PLUGIN_DIR', plugin_dir_path( __FILE__ ) );

	/**
	 * Business_Matchup Class definition
	 * This is the main class for the plugin
	 * 
	 * @since 0.1.0
	 */
	final class Business_Matchup {

		/**
		 * Define the first instance of the class
		 */
		protected static $single_instance;

		// public function __construct() {
		// 	$BusinessMatchupAPI = new Business_Matchup_API();
		// 	$BusinessMatchupPollsPage = new Business_Matchup_Polls_Page();
		// }
		/**
		 * hooks function
		 * Plugin intialization action, filters, and hooks go here
		 * 
		 * @since 0.1.0
		 */
		public function hooks() {
			add_action( 'admin_menu', array( $this, 'bm_menu' ) );
			add_action( 'init', array( $this, 'business_matchup_cpt' ) );
			add_action( 'add_meta_boxes', array( $this, 'add_meta_box' ) );
			add_action( 'save_post',      array( $this, 'save'         ) );
			add_action( 'wp_enqueue_scripts', array( $this, 'business_matchup_styles' ) );

			add_filter( 'generate_sidebar_layout', 'business_matchup_sidebar_layout' );
			add_filter( 'the_content', array( $this , 'business_matchup_content' ) );

			register_activation_hook( __FILE__, [ $this, 'activate' ] );

		}
		
		/**
		 * activate function
		 * This function runs upon plugin activation to update the permalinks option
		 * to force WordPress to refresh the permalinks in preparation of creating
		 * our custom post type.
		 * 
		 * @since 0.1.0
		 */
		public function activate() {
			update_option('plugin_permalinks_flushed', 0);
		}

		/**
		 * get_instance function
		 * This helps set up the single instance of our new class
		 * 
		 * @since 0.1.0
		 * 
		 * @return self::$single_instance of the class
		 */
		public static function get_instance() {
			if ( null === self::$single_instance ) {
				self::$single_instance = new self();
			}
	
			return self::$single_instance;
		}

		/**
		 * business_matchup_styles function
		 * This function enqueues our custom styles.
		 * 
		 * @since 0.1.0
		 */
		public function business_matchup_styles() {
			wp_enqueue_style( 'business-matchups-style', plugin_dir_url(__FILE__).'includes/css/business-matchups.css' );
		}

		/**
		 * business_matchup_sidebar_layout function
		 * This function disables the sidebar if present on our custom post type pages.
		 * 
		 * @since 0.1.0
		 * 
		 * @param array $layout contains the global layout configuration for the site
		 * @return arry $layout with the sidebar disabled
		 */
		public function business_matchup_sidebar_layout( $layout ) {
			$post_types = array( 'business-matchups' );
		
			if ( in_array( get_post_type(), $post_types ) ) {
				return 'no-sidebar';
			}
		
			return $layout;
		}

		/**
		 * business_matchup_cpt function
		 * This function sets up and creates the custom post type for the Business Matchup Polls.
		 * 
		 * @since 0.1.0
		 */
		public function business_matchup_cpt() {
			$labels = array(
				'name'                  => _x( 'Business Matchups', 'Post type general name', 'business-matchups' ),
				'singular_name'         => _x( 'Business Matchups', 'Post type singular name', 'business-matchups' ),
				'menu_name'             => _x( 'Business Matchups', 'Admin Menu text', 'business-matchups' ),
				'name_admin_bar'        => _x( 'Business Matchups', 'Add New on Toolbar', 'business-matchups' ),
				'add_new'               => __( 'Add New', 'business-matchups' ),
				'add_new_item'          => __( 'Add New Business Matchup', 'business-matchups' ),
				'new_item'              => __( 'New Business Matchup', 'business-matchups' ),
				'edit_item'             => __( 'Edit Business Matchup', 'business-matchups' ),
				'view_item'             => __( 'View Business Matchup', 'business-matchups' ),
				'all_items'             => __( 'All Polls', 'business-matchups' ),
				'search_items'          => __( 'Search Business Matchup', 'business-matchups' ),
				'parent_item_colon'     => __( 'Parent Business Matchup:', 'business-matchups' ),
				'not_found'             => __( 'No Business Matchup found.', 'business-matchups' ),
				'not_found_in_trash'    => __( 'No Business Matchup found in Trash.', 'business-matchups' ),
				'featured_image'        => _x( 'Business Matchup Cover Image', 'Overrides the “Featured Image” phrase for this post type. Added in 4.3', 'business-matchups' ),
				'set_featured_image'    => _x( 'Set cover image', 'Overrides the “Set featured image” phrase for this post type. Added in 4.3', 'business-matchups' ),
				'remove_featured_image' => _x( 'Remove cover image', 'Overrides the “Remove featured image” phrase for this post type. Added in 4.3', 'business-matchups' ),
				'use_featured_image'    => _x( 'Use as cover image', 'Overrides the “Use as featured image” phrase for this post type. Added in 4.3', 'business-matchups' ),
				'archives'              => _x( 'Business Matchup archives', 'The post type archive label used in nav menus. Default “Post Archives”. Added in 4.4', 'business-matchups' ),
				'insert_into_item'      => _x( 'Insert into Business Matchup', 'Overrides the “Insert into post”/”Insert into page” phrase (used when inserting media into a post). Added in 4.4', 'business-matchups' ),
				'uploaded_to_this_item' => _x( 'Uploaded to this Business Matchup', 'Overrides the “Uploaded to this post”/”Uploaded to this page” phrase (used when viewing media attached to a post). Added in 4.4', 'business-matchups' ),
				'filter_items_list'     => _x( 'Filter Business Matchup list', 'Screen reader text for the filter links heading on the post type listing screen. Default “Filter posts list”/”Filter pages list”. Added in 4.4', 'business-matchups' ),
				'items_list_navigation' => _x( 'Business Matchup list navigation', 'Screen reader text for the pagination heading on the post type listing screen. Default “Posts list navigation”/”Pages list navigation”. Added in 4.4', 'business-matchups' ),
				'items_list'            => _x( 'Business Matchup list', 'Screen reader text for the items list heading on the post type listing screen. Default “Posts list”/”Pages list”. Added in 4.4', 'business-matchups' ),
			);     
			$args = array(
				'labels'             => $labels,
				'description'        => 'Business Matchup Poll custom post type.',
				'public'             => true,
				'publicly_queryable' => true,
				'show_ui'            => true,
				'show_in_menu'       => true,
				'query_var'          => true,
				'rewrite'            => array( 'slug' => 'business-matchup' ),
				'capability_type'    => 'post',
				'has_archive'        => true,
				'hierarchical'       => false,
				'menu_position'      => 20,
				'supports'           => array( 'title', 'custom-fields', 'thumbnail', 'publicize' ),
				'show_in_rest'       => true,
				'menu_icon'          => 'dashicons-awards'
			);
			register_post_type( 'business-matchups', $args );
		}
		
		/**
		 * add_meta_box function
		 * Creates custom metaboxes for our custom post type so we can allow users to enter the location and type of business
		 * 
		 * @since 0.1.0
		 * 
		 * @param string $post_type string the contains the post type
		 */
		public function add_meta_box( $post_type ) {
			$post_types = array( 'business-matchups' );

			if ( in_array( $post_type, $post_types ) ) {
				add_meta_box(
					'Business Location',
					__( 'Business Location', 'business-matchups' ),
					array( $this, 'render_meta_box_location_content' ),
					$post_type,
					'advanced',
					'high'
				);
			}

			if ( in_array( $post_type, $post_types ) ) {
				add_meta_box(
					'Business Type',
					__( 'Business Type', 'business-matchups' ),
					array( $this, 'render_meta_box_type_content' ),
					$post_type,
					'advanced',
					'high'
				);
			}

		}

		/**
		 * save function
		 * This function takes the submitted form information from the custom post type editor screen
		 * and verifies the entered data before saving them as postmeta entries in the database for the current post.
		 * 
		 * @since 0.1.0
		 * 
		 * @param integer $post_id contains the id number of the current post
		 * @return integer $post_id if performing verification fails or if performing an autosave
		 */
		public function save( $post_id ) {
			$businessMatchupYelpAPI = new \BusinessMatchup\Business_Matchup_API();
			if ( ! wp_verify_nonce( filter_input( INPUT_POST, 'business_matchup_metabox_nonce'), 'business_matchup_metabox' ) ) {
				return $post_id;
			}

			if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
				return $post_id;
			}

			if ( 'page' == filter_input( INPUT_POST, 'business-matchups') ) {
				if ( ! current_user_can( 'edit_page', $post_id ) ) {
					return $post_id;
				}
			}

			$bmBizLoc = sanitize_text_field( filter_input( INPUT_POST, 'business-matchups-business-location') );
			$business_matchup_type = sanitize_text_field( filter_input( INPUT_POST, 'business-matchups-type') );
			$postID = sanitize_text_field( filter_input( INPUT_POST, 'post_ID') );
			$has_poll = get_post_meta( $postID, '_business_matchup_poll', true );
			if( null === $has_poll ) {
				return;
			}
			if( $has_poll === '' ) {
				$business_matchup_poll = $businessMatchupYelpAPI->addPoll( $postID, $business_matchup_type, $bmBizLoc );
				update_post_meta( $post_id, '_business_matchup_poll', $business_matchup_poll );
			}
			update_post_meta( $post_id, '_business_matchup_business_location', $bmBizLoc );
			update_post_meta( $post_id, '_business_matchup_type', $business_matchup_type );
		}

		/**
		 * render_meta_box_location_content function
		 * This function displays the custom metabox within the editor screen of the current post
		 * to allow the user to provide a location in a City, ST format.
		 * 
		 * @since 0.1.0
		 * 
		 * @param array $post is the current array item containing the data for the current post.
		 */
		public function render_meta_box_location_content( $post ) {

			wp_nonce_field( 'business_matchup_metabox', 'business_matchup_metabox_nonce' );

			$bizLoc = get_post_meta( $post->ID, '_business_matchup_business_location', true );

			?>
			<label for="business-matchups-business-location">
				<?php esc_html_e( 'City, State Abbreviate. Example: San Francisco, CA', 'business-matchups' ); ?>
			</label>
				
			<input style="width:100%;" type="text" class="form-control" name="business-matchups-business-location" value="<?php esc_html_e( $bizLoc, 'business-matchups' ); ?>" /> 
			<?php
		}

		/**
		 * render_meta_box_type_content function
		 * This function displays the custom metabox within the editor screen of the current post
		 * to allow the user to provide a type of business. ie: Diner, Entertainment, etc...
		 * 
		 * @since 0.1.0
		 * 
		 * @param array $post is the current array item containing the data for the current post
		 */
		public function render_meta_box_type_content( $post ) {

			$type = get_post_meta( $post->ID, '_business_matchup_type', true );
			// $bizLoc = get_post_meta( $post->ID, '_business_matchup_business_location', true );

			?>
			<label for="business-matchups-type">
				<?php esc_html_e( 'Entertainment, Restaurant, Fine Dining, Pizza, etc...', 'business-matchups' ); ?>
			</label>

			<input style="width:100%;" type="text" class="form-control" name="business-matchups-type" value="<?php esc_html_e( $type, 'business-matchups' ); ?>" />

			<?php
			$has_poll = get_post_meta( $post->ID, '_business_matchup_poll', true );
			?>
			<input style="width:100%;" type="hidden" class="form-control" name="business-matchups-poll" value="<?php esc_html_e( $has_poll, 'business-matchups' ); ?>" />
			<?php
		}

		/**
		 * business_matchup_content function
		 * This function displays the gathered information from the Yelp API and Straw Poll API based on the 
		 * information provided in the custom metaboxes for the content of the Custom Post Type Front End.
		 * 
		 * @since 0.1.0
		 * 
		 * @param array $content is the array containing the page content for the given post.
		 * @return array $content for the custom post type after generating it from the data we retrieved.
		 */
		public function business_matchup_content($content) {
			$business_matchup_page = new \BusinessMatchup\Business_Matchup_Polls_Page();
			global $post;
			$postID = $post->ID;
			$type = get_post_meta( $postID, '_business_matchup_type', true );
			$bizLoc = get_post_meta( $postID, '_business_matchup_business_location', true );
			$poll = get_post_meta( $postID, '_business_matchup_poll', true );
			$bizLoc_array = explode(",",$bizLoc);
			$city = $bizLoc_array[0];
			$response_body = json_decode( get_post_meta( $postID, '_business_matchup_yelp_results', true ), true );
			$pollitems = $business_matchup_page->buildPollItems($response_body);
			$content = '<section id="business-matchups">';
			if ($post->post_type == 'business-matchups') {
				$content .= '
					<div id="business-matchups-title">
						<h1> '.$type.' locations near '.$city.'</h1>
						<hr/>
					</div>
					<section id="business-matchups-content">
						<div class="cards">
							<div class="card card-1">'.$business_matchup_page->cardContent( $pollitems, 0 ).'</div>
							<div class="card card-2">'.$business_matchup_page->cardContent( $pollitems, 1 ).'</div>
							<div class="card card-3">'.$business_matchup_page->cardContent( $pollitems, 2 ).'</div>
						</div>
					</section>
				';
				$content .= '<div id="strawpolls-content">';
				$content .= $poll;
				$content .= '</div>';
				$content .= '</section>';
			}
			return $content;
		}

		/**
		 * bm_menu function
		 * This function creates a new admin menu item to allow users to enter their Yelp and StrawPoll API keys.
		 * 
		 * @since 0.1.0
		 */
		public function bm_menu() {
			add_menu_page( 'Business Matchup Settings', 'Business Matchup Settings', 'manage_options', 'business-matchups', array( $this, 'bmForm' ), 'dashicons-admin-generic', 0 );
			add_submenu_page( 'business-matchups', 'Business Matchup Settings', 'Settings', 'manage_options', 'business-matchups', array( $this, 'bmForm' ) );
			add_action( 'admin_init', array( $this, 'settings' ) );
		}

		/**
		 * settings function
		 * This function creates custom setting fields that our new menu will use to store credentials.
		 * 
		 * @since 0.1.0
		 */
		public function settings() {
			add_settings_section( 'business_matchup_settings_section', null, null, 'business-matchups-options' );
			register_setting( 'business_matchup_Fields', 'business_matchup_Text' );
			add_settings_field( 'business_matchup_text', 'Filtered Text', array( $this, 'business_matchup_yelp_fields'), 'business-matchups-options', 'business_matchup_settings_section' );
		}
		/**
		 * handleForm_admin_notice__success function
		 * This function handles the output and display of custom admin notices upon submitting the credentials in the settings menu.
		 * 
		 * @since 0.1.0
		 */
		public function handleForm_admin_notice__success() {
			?>
			<div class="notice notice-success is-dismissible">
				<p><?php esc_html_e( 'Your API Key\'s were saved.', 'business-matchups' ); ?></p>
			</div>
			<?php
		}
		
		/**
		 * handleForm_admin_notice__error function
		 * This function handles the output and display of custom admin notices upon submitting the credentials in the settings menu
		 * without passing a valid nonce.
		 * 
		 * @since 0.1.0
		 */
		public function handleForm_admin_notice__error() {
			?>
			<div class="notice notice-error">
				<p><?php esc_html_e( 'Uh uh uh... you didn\t say the magic word...', 'business-matchups' ); ?></p>
			</div>
			<?php
		}

		/**
		 * handleForm function
		 * This function processes and validates the information submitted in our new settings menu
		 * 
		 * @since 0.1.0
		 */
		public function handleForm() {
			if ( wp_verify_nonce( filter_input( INPUT_POST, 'nonce'), 'business_matchup' ) AND current_user_can( 'manage_options' ) ) {
				update_option( 'business_matchup_yelp_api', sanitize_text_field( filter_input( INPUT_POST, 'yelp_api_key') ) );
				update_option( 'business_matchup_straw_poll_api', sanitize_text_field( filter_input( INPUT_POST, 'straw_poll_api_key') ) );
				add_action( 'admin_notices', 'handleForm_admin_notice__success' );
			}
			if ( !wp_verify_nonce( filter_input( INPUT_POST, 'nonce'), 'business_matchup' ) AND !current_user_can( 'manage_options' ) ) {
				add_action( 'admin_notices', 'handleForm_admin_notice__error' );
			}
		}

		/**
		 * bmForm function
		 * This function generates the form used in the settings menu to allow users to submit the credentials. That input is then
		 * passed to the handleForm function and stored into the options table to be used while making API calls to Yelp and StrawPoll.
		 * 
		 * @since 0.1.0
		 */
		public function bmForm() {
			?>
				<div class="wrap">
					<h1>Business Matchup Polls</h1>
					<?php
						if ( wp_verify_nonce( filter_input( INPUT_POST, 'nonce'), 'business_matchup' ) AND current_user_can( 'manage_options' ) ) {
							if (filter_input( INPUT_POST, 'justsubmitted') == "true") $this->handleForm();
						}
					?>
					<form method="POST">
						<input type="hidden" name="justsubmitted" value="true">
						<?php wp_nonce_field( 'business_matchup', 'nonce' ); ?>
						<label for="yelp_api_key"><p>Enter your Yelp API Key</p>
						<div class="business_matchup__flex-container">    
							<input style="width:100%" name="yelp_api_key" id="yelp_api_key" placeholder="aFMUqcUXCqlUbIn9uPn3x_" value="<?php esc_html_e( esc_textarea( get_option( 'business_matchup_yelp_api' ) ), 'business-matchups' ); ?>" />
						</div>
						<label for="straw_poll_api_key"><p>Enter your Straw Poll API Key</p>
						<div class="business_matchup__flex-container">
							<input style="width:100%" name="straw_poll_api_key" id="straw_poll_api_key" placeholder="aFMUqcUXCqlUbIn9uPn3x_" value="<?php esc_html_e( esc_textarea( get_option( 'business_matchup_straw_poll_api' ) ), 'business-matchups' ); ?>" />
						</div>
						<br style="padding-bottom: 20px;"/>
						<input type="submit" name="submit" id="submit" class="button button-primary" value="Save Changes">
					</form>
				</div>
			<?php
		}

		/**
		 * optionsSubPage function
		 * This function help define our new settings page in our new custom menu item to help users easily find where to enter their credentials.
		 * 
		 * @since 0.1.0
		 */
		public function optionsSubPage() {
			?>
				<div class="wrap">
					<h1>Business Matchup Polls Options</h1>
					<form action="options.php" method="POST">
						<?php
							settings_fields( 'business_matchup_Fields' );
							do_settings_sections( 'business-matchups-options' );
							submit_button();
						?>
					</form>
				</div>
			<?php
		}

	}

	/**
	 * businessMatchup function
	 * This function helps create and standup our class
	 * 
	 * @since 0.1.0
	 * 
	 * @return class Business_Matchup instance
	 */
	function businessMatchup() {
		$businessMatchup = new Business_Matchup();
		return $businessMatchup->get_instance();
	}
	add_action( 'plugins_loaded', array( businessMatchup(), 'hooks' ) );
