<?php
	/**
		* Plugin Name:       Yelp Polls
		* Plugin URI:        https://github.com/allen-mckenzie/yelp-polls
		* Description:       Create custom polls using Yelp and Straw Polls
		* Version:           0.0.3
		* Requires at least: 5.5
		* Requires PHP:      7.4
		* Author:            Allen McKenzie
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
	 * Include our classes
	 */
	//use includes\classes\Yelp_API;

	define( 'YELP_POLLS__PLUGIN_DIR', plugin_dir_path( __FILE__ ) );

	if ( !class_exists( 'Yelp_Polls_Plugin' ) ) {
		class Yelp_Polls {
			function __construct() {
				require( YELP_POLLS__PLUGIN_DIR . 'includes/classes/class-api.php' );
				add_action( 'admin_menu', array( $this, 'yp_menu' ) );
				register_activation_hook( __FILE__, [ $this, 'activate' ] );
				add_action( 'init', array( $this, 'yelp_polls_cpt' ) );
				add_action( 'add_meta_boxes', array( $this, 'add_meta_box' ) );
				add_action( 'save_post',      array( $this, 'save'         ) );
				add_filter( 'generate_sidebar_layout', 'yelp_polls_sidebar_layout' );
				add_filter('the_content', array( $this, 'yelp_polls_content' ) );
				add_action( 'wp_enqueue_scripts', array( $this, 'yelp_polls_styles' ) );
			}

			function activate() {
				flush_rewrite_rules();
			}

			function yelp_polls_styles() {
				wp_enqueue_style( 'my-movie-style', plugin_dir_url(__FILE__).'includes/css/yelp-polls.css' );
			}

			function yelp_polls_sidebar_layout( $layout ) {
				$post_types = array( 'yelp-polls' );
			
				if ( in_array( get_post_type(), $post_types ) ) {
					return 'no-sidebar';
				}
			
				return $layout;
			}

			function yelp_polls_cpt() {
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
		
			function add_meta_box( $post_type ) {
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

			function save( $post_id ) {

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
					$yelp_polls_poll = $this->addPoll( $postID, $yelp_polls_type, $ypBizLoc );
					update_post_meta( $post_id, '_yelp_polls_poll', $yelp_polls_poll );
				}
				update_post_meta( $post_id, '_yelp_polls_business_location', $ypBizLoc );
				update_post_meta( $post_id, '_yelp_polls_type', $yelp_polls_type );
			}

			function render_meta_box_location_content( $post ) {

				wp_nonce_field( 'yelp_polls_metabox', 'yelp_polls_metabox_nonce' );

				$bizLoc = get_post_meta( $post->ID, '_yelp_polls_business_location', true );

				?>
				<label for="yelp-polls-business-location">
					<?php esc_html_e( 'City, State Abbreviate. Example: San Francisco, CA', 'yelp-polls' ); ?>
				</label>
					
				<input style="width:100%;" type="text" class="form-control" name="yelp-polls-business-location" value="<?php esc_html_e( $bizLoc, 'yelp-polls' ); ?>" /> 
				<?php
			}

			function render_meta_box_type_content( $post ) {

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

			function createPoll( $poll_json ) {
				$url = 'https://strawpoll.com/api/poll';
				$post_data = $poll_json;
				$headers = array(
					'user-agent' => '',
					'Content-Type' => 'application/json',
					'X-API-KEY' => STRAWPOLL_API_KEY
				);
				$args = array(
					'method' => 'POST',
					'headers' => $headers,
					'blocking'    => true,
					'body' => $post_data,
					'data_format' => 'body'
				);
				$response = wp_remote_request( $url, $args );
				$response_body = wp_remote_retrieve_body( $response );
				$response_body = json_decode( $response_body, true);
				$pollID = null;
				if( isset( $response_body['content_id'] ) ) {
					$pollID = $response_body['content_id'];
				}
				$html = '<iframe src="https://strawpoll.com/embed/' . $pollID . '" style="width: 75%;height: 100%;margin: 0 16.5% !important;padding-top: 20px;min-height: 640px;" frameborder="0" allowfullscreen></iframe>';
				return $html;
			}

			function addPoll( $postID, $term, $bizLoc ) {
				$yelpAPI = new Yelp_API();
				$response = $yelpAPI->search( $term, $bizLoc );
				if( null === $response) {
					return;
				}
				$response_body = wp_remote_retrieve_body( $response );
				update_post_meta( $postID, '_yelp_polls_yelp_results', $response_body );
				$response_body = json_decode( get_post_meta( $postID, '_yelp_polls_yelp_results', true ), true );
				$pollitems = $yelpAPI->buildPollItems($response_body);
				$bizLoc_array = explode(",",$bizLoc);
				$city = $bizLoc_array[0];
				$answers = array();
				foreach($pollitems as $answer) {
					$answers[] = $answer['name'];
				}
				$poll_array = array( 
					"poll" => array(
						"title" => "Which of these $term locations near $city do you think is the best?",
						"answers" => $answers,
						"priv" => false,
						"ma" => false,
						"mip" => true,
						"enter_name" => true,
						"only_reg" => false
					)
				);
				$poll_json = json_encode( $poll_array );
				$pollHTML = $this->createPoll( $poll_json );
				return $pollHTML;
			}

			function yelp_polls_content($content) {
				$yelpAPI = new Yelp_API();
				global $post;
				$postID = $post->ID;
				$type = get_post_meta( $postID, '_yelp_polls_type', true );
				$bizLoc = get_post_meta( $postID, '_yelp_polls_business_location', true );
				$poll = get_post_meta( $postID, '_yelp_polls_poll', true );
				$bizLoc_array = explode(",",$bizLoc);
				$city = $bizLoc_array[0];
				$response_body = json_decode( get_post_meta( $postID, '_yelp_polls_yelp_results', true ), true );
				$pollitems = $yelpAPI->buildPollItems($response_body);
				if ($post->post_type == 'yelp-polls') {
					$content = '
						<h1> '.$type.' locations near '.$city.'</h1>
						<hr/>
						<section class="yelp-polls-content">
							<div class="cards">
								<div class="card">'.$yelpAPI->cardContent( $pollitems, 0 ).'</div>
								<div class="card">'.$yelpAPI->cardContent( $pollitems, 1 ).'</div>
								<div class="card">'.$yelpAPI->cardContent( $pollitems, 2 ).'</div>
							</div>
						</section>
					';
					$content .= $poll;
				}
				return $content;
			}

			function yp_menu() {
				add_menu_page( 'Yelp Poll', 'Yelp Poll', 'manage_options', 'yelp-polls', array( $this, 'ypForm' ), 'dashicons-admin-generic', 0 );
				add_submenu_page( 'yelp-polls', 'Yelp Poll Settings', 'Settings', 'manage_options', 'yelp-polls', array( $this, 'ypForm' ) );
				add_action( 'admin_init', array( $this, 'settings' ) );
			}
	
			function settings() {
				add_settings_section( 'yelp_polls_settings_section', null, null, 'yelp-polls-options' );
				register_setting( 'yelp_polls_Fields', 'yelp_polls_Text' );
				add_settings_field( 'yelp_polls_text', 'Filtered Text', array( $this, 'yelp_fields'), 'yelp-polls-options', 'yelp_polls_settings_section' );
			}

			function handleForm() {
				$message = '
				<div class="error">
					<p>
						Uh uh uh, you didn\'t say the magic word
					</p>
				</div>
				';
				if ( wp_verify_nonce( filter_input( INPUT_POST, 'nonce'), 'yelp_polls' ) AND current_user_can( 'manage_options' ) ) {
					update_option( 'yelp_polls_yelp_api', sanitize_text_field( filter_input( INPUT_POST, 'yelp_api_key') ) );
					update_option( 'yelp_polls_straw_poll_api', sanitize_text_field( filter_input( INPUT_POST, 'straw_poll_api_key') ) );
					$message = '
						<div class="updated">
							<p>Your API Key\'s were saved.</p>
						</div>
					';
				}
				echo $message;
			}
	
			function ypForm() {
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
								<input name="yelp_api_key" id="yelp_api_key" placeholder="aFMUqcUXCqlUbIn9uPn3x_" value="<?php esc_html_e( esc_textarea( get_option( 'yelp_polls_yelp_api' ) ) ); ?>" />
							</div>
							<label for="straw_poll_api_key"><p>Enter your Straw Poll API Key</p>
							<div class="yelp_polls__flex-container">
								<input name="straw_poll_api_key" id="straw_poll_api_key" placeholder="aFMUqcUXCqlUbIn9uPn3x_" value="<?php esc_html_e( esc_textarea( get_option( 'yelp_polls_straw_poll_api' ) ) ); ?>" />
							</div>
							<input type="submit" name="submit" id="submit" class="button button-primary" value="Save Changes">
						</form>
					</div>
				<?php
			}
	
			function optionsSubPage() {
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
		$yelpPolls = new Yelp_Polls;
	}