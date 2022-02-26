<?php
	/**
		* Plugin Name:       Yelp Polls
		* Plugin URI:        https://github.com/allen-mckenzie/yelp-polls
		* Description:       Create custom polls using Yelp and Straw Polls
		* Version:           0.1.0
		* Requires at least: 5.5
		* Requires PHP:      7.0
		* Author:            Allen McKenzie
		* Contributers:		 allenmcnichols, amethystanswers
		* Author URI:        https://github.com/allen-mckenzie
		* License:           GPL v3 or later
		* License URI:       https://www.gnu.org/licenses/gpl-3.0.html
		* Update URI:        https://github.com/allen-mckenzie/yelp-polls/releases
		* Text Domain:       yelp-polls
		* Domain Path:       /languages
		*
		* @package         Yelp_Polls
	*/

	if( !defined( 'ABSPATH' ) ) return; // None shall pass
	
	/**
	 * yelp_polls_autoload_classes function
	 * Include our classes
	 * All Yelp Polls classes are prefixed with `Yelp_`
	 * 
	 * @since 0.1.0
	 * 
	 * @param type $class_name string containing file name.
	 */
	function yelp_polls_autoload_classes( $class_name ) {
		$yelpPolls = new Yelp_Polls();
		if ( 0 !== strpos( $class_name, 'Yelp_' ) ) {
			return;
		}
	
		$filename = strtolower(
			str_replace(
				'_',
				'-',
				substr( $class_name, strlen( 'Yelp_' ) )
			)
		);
		$yelpPolls->include_file( 'includes/classes/class-' . $filename );
	}

	/**
	 * spl_autoload_register
	 * Registers all of the classes found by the autoloader
	 * 
	 * @since 0.1.0
	 */
	spl_autoload_register( 'yelp_polls_autoload_classes' );

	/**
	 * Define the Yelp Polls Plugin Dir
	 * 
	 * @since 0.1.0
	 */
	define( 'YELP_POLLS__PLUGIN_DIR', plugin_dir_path( __FILE__ ) );

	/**
	 * Yelp_Polls Class definition
	 * This is the main class for the plugin
	 * 
	 * @since 0.1.0
	 */
	final class Yelp_Polls {

		/**
		 * Define the first instance of the class
		 */
		protected static $single_instance;

		/**
		 * include_file function
		 * Check our directory for any files ending with .php
		 * 
		 * @since 0.1.0
		 * 
		 * @param type $filename string containing the name of the file
		 * @return boolean True/False if file was found
		 */
		public static function include_file( $filename ) {
			$file = self::dir( $filename . '.php' );
			if ( ! is_file( $file ) ) {	// This check is necessary to find our php files containing our class definitions.
				return false;
			}
			include_once $file; // This issue continues to elude me. How do we includ or require a file without using one of these commands?
			return true;
		}

		/**
		 * dir function
		 * Create paths with trailing slashes based on the provide path
		 * 
		 * @since 0.1.0
		 * 
		 * @param type $path string with the name of the path
		 * @return string $dir . $path formatted directory path
		 */
		public static function dir( $path = '' ) {
			static $dir;
			$dir = $dir ? $dir : trailingslashit( __DIR__ );
			return $dir . $path;
		}

		/**
		 * hooks function
		 * Plugin intialization action, filters, and hooks go here
		 * 
		 * @since 0.1.0
		 */
		public function hooks() {
			add_action( 'admin_menu', array( $this, 'yp_menu' ) );
			add_action( 'init', array( $this, 'yelp_polls_cpt' ) );
			add_action( 'add_meta_boxes', array( $this, 'add_meta_box' ) );
			add_action( 'save_post',      array( $this, 'save'         ) );
			add_action( 'wp_enqueue_scripts', array( $this, 'yelp_polls_styles' ) );

			add_filter( 'generate_sidebar_layout', 'yelp_polls_sidebar_layout' );
			add_filter( 'the_content', array( $this , 'yelp_polls_content' ) );

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
		 * yelp_polls_styles function
		 * This function enqueues our custom styles.
		 * 
		 * @since 0.1.0
		 */
		public function yelp_polls_styles() {
			wp_enqueue_style( 'yelp-polls-style', plugin_dir_url(__FILE__).'includes/css/yelp-polls.css' );
		}

		/**
		 * yelp_polls_sidebar_layout function
		 * This function disables the sidebar if present on our custom post type pages.
		 * 
		 * @since 0.1.0
		 * 
		 * @param array $layout contains the global layout configuration for the site
		 * @return arry $layout with the sidebar disabled
		 */
		public function yelp_polls_sidebar_layout( $layout ) {
			$post_types = array( 'yelp-polls' );
		
			if ( in_array( get_post_type(), $post_types ) ) {
				return 'no-sidebar';
			}
		
			return $layout;
		}

		/**
		 * yelp_polls_cpt function
		 * This function sets up and creates the custom post type for the Yelp Polls.
		 * 
		 * @since 0.1.0
		 */
		public function yelp_polls_cpt() {
			$labels = array(
				'name'                  => _x( 'Yelp Polls', 'Post type general name', 'yelp-polls' ),
				'singular_name'         => _x( 'Yelp Poll', 'Post type singular name', 'yelp-polls' ),
				'menu_name'             => _x( 'Yelp Polls', 'Admin Menu text', 'yelp-polls' ),
				'name_admin_bar'        => _x( 'Yelp Poll', 'Add New on Toolbar', 'yelp-polls' ),
				'add_new'               => __( 'Add New', 'yelp-polls' ),
				'add_new_item'          => __( 'Add New Yelp Poll', 'yelp-polls' ),
				'new_item'              => __( 'New Yelp Poll', 'yelp-polls' ),
				'edit_item'             => __( 'Edit Yelp Poll', 'yelp-polls' ),
				'view_item'             => __( 'View Yelp Poll', 'yelp-polls' ),
				'all_items'             => __( 'All Polls', 'yelp-polls' ),
				'search_items'          => __( 'Search Yelp Polls', 'yelp-polls' ),
				'parent_item_colon'     => __( 'Parent Yelp Polls:', 'yelp-polls' ),
				'not_found'             => __( 'No Yelp Polls found.', 'yelp-polls' ),
				'not_found_in_trash'    => __( 'No Yelp Polls found in Trash.', 'yelp-polls' ),
				'featured_image'        => _x( 'Yelp Poll Cover Image', 'Overrides the “Featured Image” phrase for this post type. Added in 4.3', 'yelp-polls' ),
				'set_featured_image'    => _x( 'Set cover image', 'Overrides the “Set featured image” phrase for this post type. Added in 4.3', 'yelp-polls' ),
				'remove_featured_image' => _x( 'Remove cover image', 'Overrides the “Remove featured image” phrase for this post type. Added in 4.3', 'yelp-polls' ),
				'use_featured_image'    => _x( 'Use as cover image', 'Overrides the “Use as featured image” phrase for this post type. Added in 4.3', 'yelp-polls' ),
				'archives'              => _x( 'Yelp Poll archives', 'The post type archive label used in nav menus. Default “Post Archives”. Added in 4.4', 'yelp-polls' ),
				'insert_into_item'      => _x( 'Insert into Yelp Poll', 'Overrides the “Insert into post”/”Insert into page” phrase (used when inserting media into a post). Added in 4.4', 'yelp-polls' ),
				'uploaded_to_this_item' => _x( 'Uploaded to this Yelp Poll', 'Overrides the “Uploaded to this post”/”Uploaded to this page” phrase (used when viewing media attached to a post). Added in 4.4', 'yelp-polls' ),
				'filter_items_list'     => _x( 'Filter Yelp Polls list', 'Screen reader text for the filter links heading on the post type listing screen. Default “Filter posts list”/”Filter pages list”. Added in 4.4', 'yelp-polls' ),
				'items_list_navigation' => _x( 'Yelp Polls list navigation', 'Screen reader text for the pagination heading on the post type listing screen. Default “Posts list navigation”/”Pages list navigation”. Added in 4.4', 'yelp-polls' ),
				'items_list'            => _x( 'Yelp Polls list', 'Screen reader text for the items list heading on the post type listing screen. Default “Posts list”/”Pages list”. Added in 4.4', 'yelp-polls' ),
			);     
			$args = array(
				'labels'             => $labels,
				'description'        => 'Yelp Poll custom post type.',
				'public'             => true,
				'publicly_queryable' => true,
				'show_ui'            => true,
				'show_in_menu'       => true,
				'query_var'          => true,
				'rewrite'            => array( 'slug' => 'yelp-poll' ),
				'capability_type'    => 'post',
				'has_archive'        => true,
				'hierarchical'       => false,
				'menu_position'      => 20,
				'supports'           => array( 'title', 'custom-fields', 'thumbnail' ),
				'show_in_rest'       => true
			);
			register_post_type( 'yelp-polls', $args );
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
			$post_types = array( 'yelp-polls' );

			if ( in_array( $post_type, $post_types ) ) {
				add_meta_box(
					'Business Location',
					__( 'Business Location', 'yelp-polls' ),
					array( $this, 'render_meta_box_location_content' ),
					$post_type,
					'advanced',
					'high'
				);
			}

			if ( in_array( $post_type, $post_types ) ) {
				add_meta_box(
					'Business Type',
					__( 'Business Type', 'yelp-polls' ),
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
			$yelpAPI = new Yelp_API();
			if ( ! wp_verify_nonce( filter_input( INPUT_POST, 'yelp_polls_metabox_nonce'), 'yelp_polls_metabox' ) ) {
				return $post_id;
			}

			if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
				return $post_id;
			}

			if ( 'page' == filter_input( INPUT_POST, 'yelp-polls') ) {
				if ( ! current_user_can( 'edit_page', $post_id ) ) {
					return $post_id;
				}
			}

			$ypBizLoc = sanitize_text_field( filter_input( INPUT_POST, 'yelp-polls-business-location') );
			$yelp_polls_type = sanitize_text_field( filter_input( INPUT_POST, 'yelp-polls-type') );
			$postID = sanitize_text_field( filter_input( INPUT_POST, 'post_ID') );
			$has_poll = get_post_meta( $postID, '_yelp_polls_poll', true );
			if( null === $has_poll ) {
				return;
			}
			if( $has_poll === '' ) {
				$yelp_polls_poll = $yelpAPI->addPoll( $postID, $yelp_polls_type, $ypBizLoc );
				update_post_meta( $post_id, '_yelp_polls_poll', $yelp_polls_poll );
			}
			update_post_meta( $post_id, '_yelp_polls_business_location', $ypBizLoc );
			update_post_meta( $post_id, '_yelp_polls_type', $yelp_polls_type );
		}

		/**
		 * render_meeta_box_location_content function
		 * This function displays the custom metabox within the editor screen of the current post
		 * to allow the user to provide a location in a City, ST format.
		 * 
		 * @since 0.1.0
		 * 
		 * @param array $post is the current array item containing the data for the current post.
		 */
		public function render_meta_box_location_content( $post ) {

			wp_nonce_field( 'yelp_polls_metabox', 'yelp_polls_metabox_nonce' );

			$bizLoc = get_post_meta( $post->ID, '_yelp_polls_business_location', true );

			?>
			<label for="yelp-polls-business-location">
				<?php esc_html_e( 'City, State Abbreviate. Example: San Francisco, CA', 'yelp-polls' ); ?>
			</label>
				
			<input style="width:100%;" type="text" class="form-control" name="yelp-polls-business-location" value="<?php esc_html_e( $bizLoc, 'yelp-polls' ); ?>" /> 
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

			$type = get_post_meta( $post->ID, '_yelp_polls_type', true );
			// $bizLoc = get_post_meta( $post->ID, '_yelp_polls_business_location', true );

			?>
			<label for="yelp-polls-type">
				<?php esc_html_e( 'Entertainment, Restaurant, Fine Dining, Pizza, etc...', 'yelp-polls' ); ?>
			</label>

			<input style="width:100%;" type="text" class="form-control" name="yelp-polls-type" value="<?php esc_html_e( $type, 'yelp-polls' ); ?>" />

			<?php
			$has_poll = get_post_meta( $post->ID, '_yelp_polls_poll', true );
			?>
			<input style="width:100%;" type="hidden" class="form-control" name="yelp-polls-poll" value="<?php esc_html_e( $has_poll, 'yelp-polls' ); ?>" />
			<?php
		}

		/**
		 * yelp_polls_content function
		 * This function displays the gathered information from the Yelp API and Straw Poll API based on the 
		 * information provided in the custom metaboxes for the content of the Custom Post Type Front End.
		 * 
		 * @since 0.1.0
		 * 
		 * @param array $content is the array containing the page content for the given post.
		 * @return array $content for the custom post type after generating it from the data we retrieved.
		 */
		public function yelp_polls_content($content) {
			$yelp_polls_page = new Yelp_Polls_Page();
			global $post;
			$postID = $post->ID;
			$type = get_post_meta( $postID, '_yelp_polls_type', true );
			$bizLoc = get_post_meta( $postID, '_yelp_polls_business_location', true );
			$poll = get_post_meta( $postID, '_yelp_polls_poll', true );
			$bizLoc_array = explode(",",$bizLoc);
			$city = $bizLoc_array[0];
			$response_body = json_decode( get_post_meta( $postID, '_yelp_polls_yelp_results', true ), true );
			$pollitems = $yelp_polls_page->buildPollItems($response_body);
			$content = '<section id="yelp-polls">';
			if ($post->post_type == 'yelp-polls') {
				$content .= '
					<div id="yelp-polls-title">
						<h1> '.$type.' locations near '.$city.'</h1>
						<hr/>
					</div>
					<section id="yelp-polls-content">
						<div class="cards">
							<div class="card card-1">'.$yelp_polls_page->cardContent( $pollitems, 0 ).'</div>
							<div class="card card-2">'.$yelp_polls_page->cardContent( $pollitems, 1 ).'</div>
							<div class="card card-3">'.$yelp_polls_page->cardContent( $pollitems, 2 ).'</div>
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
		 * yp_menu function
		 * This function creates a new admin menu item to allow users to enter their Yelp and StrawPoll API keys.
		 * 
		 * @since 0.1.0
		 */
		public function yp_menu() {
			add_menu_page( 'Yelp Poll', 'Yelp Poll', 'manage_options', 'yelp-polls', array( $this, 'ypForm' ), 'dashicons-admin-generic', 0 );
			add_submenu_page( 'yelp-polls', 'Yelp Poll Settings', 'Settings', 'manage_options', 'yelp-polls', array( $this, 'ypForm' ) );
			add_action( 'admin_init', array( $this, 'settings' ) );
		}

		/**
		 * settings function
		 * This function creates custom setting fields that our new menu will use to store credentials.
		 * 
		 * @since 0.1.0
		 */
		public function settings() {
			add_settings_section( 'yelp_polls_settings_section', null, null, 'yelp-polls-options' );
			register_setting( 'yelp_polls_Fields', 'yelp_polls_Text' );
			add_settings_field( 'yelp_polls_text', 'Filtered Text', array( $this, 'yelp_fields'), 'yelp-polls-options', 'yelp_polls_settings_section' );
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
				<p><?php esc_html_e( 'Your API Key\'s were saved.', 'yelp-polls' ); ?></p>
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
				<p><?php esc_html_e( 'Uh uh uh... you didn\t say the magic word...', 'yelp-polls' ); ?></p>
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
			if ( wp_verify_nonce( filter_input( INPUT_POST, 'nonce'), 'yelp_polls' ) AND current_user_can( 'manage_options' ) ) {
				update_option( 'yelp_polls_yelp_api', sanitize_text_field( filter_input( INPUT_POST, 'yelp_api_key') ) );
				update_option( 'yelp_polls_straw_poll_api', sanitize_text_field( filter_input( INPUT_POST, 'straw_poll_api_key') ) );
				add_action( 'admin_notices', 'handleForm_admin_notice__success' );
			}
			if ( !wp_verify_nonce( filter_input( INPUT_POST, 'nonce'), 'yelp_polls' ) AND !current_user_can( 'manage_options' ) ) {
				add_action( 'admin_notices', 'handleForm_admin_notice__error' );
			}
		}

		/**
		 * ypForm function
		 * This function generates the form used in the settings menu to allow users to submit the credentials. That input is then
		 * passed to the handleForm function and stored into the options table to be used while making API calls to Yelp and StrawPoll.
		 * 
		 * @since 0.1.0
		 */
		public function ypForm() {
			?>
				<div class="wrap">
					<h1>Yelp Polls</h1>
					<?php
						if ( wp_verify_nonce( filter_input( INPUT_POST, 'nonce'), 'yelp_polls' ) AND current_user_can( 'manage_options' ) ) {
							if (filter_input( INPUT_POST, 'justsubmitted') == "true") $this->handleForm();
						}
					?>
					<form method="POST">
						<input type="hidden" name="justsubmitted" value="true">
						<?php wp_nonce_field( 'yelp_polls', 'nonce' ); ?>
						<label for="yelp_api_key"><p>Enter your Yelp API Key</p>
						<div class="yelp_polls__flex-container">    
							<input style="width:100%" name="yelp_api_key" id="yelp_api_key" placeholder="aFMUqcUXCqlUbIn9uPn3x_" value="<?php esc_html_e( esc_textarea( get_option( 'yelp_polls_yelp_api' ) ), 'yelp-polls' ); ?>" />
						</div>
						<label for="straw_poll_api_key"><p>Enter your Straw Poll API Key</p>
						<div class="yelp_polls__flex-container">
							<input style="width:100%" name="straw_poll_api_key" id="straw_poll_api_key" placeholder="aFMUqcUXCqlUbIn9uPn3x_" value="<?php esc_html_e( esc_textarea( get_option( 'yelp_polls_straw_poll_api' ) ), 'yelp-polls' ); ?>" />
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
					<h1>Yelp Polls Options</h1>
					<form action="options.php" method="POST">
						<?php
							settings_fields( 'yelp_polls_Fields' );
							do_settings_sections( 'yelp-polls-options' );
							submit_button();
						?>
					</form>
				</div>
			<?php
		}

	}

	/**
	 * yelpPolls function
	 * This function helps create and standup our class
	 * 
	 * @since 0.1.0
	 * 
	 * @return class Yelp_Polls instance
	 */
	function yelpPolls() {
		$yelpPolls = new Yelp_Polls();
		return $yelpPolls->get_instance();
	}
	add_action( 'plugins_loaded', array( yelpPolls(), 'hooks' ) );