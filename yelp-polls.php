<?php
	/**
		* Plugin Name:       Yelp Polls
		* Plugin URI:        https://github.com/allen-mckenzie/yelp-polls
		* Description:       Create custom polls using Yelp and Straw Polls
		* Version:           0.0.1
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

	if( !defined( 'ABSPATH' ) ) exit; // None shall pass

	if ( !class_exists( 'Yelp_Polls_Plugin' ) ) {
		class Yelp_Polls {

			function __construct() {
				add_action( 'init', array( $this, 'yelp_polls_cpt' ) );
				add_action( 'add_meta_boxes', array( $this, 'add_meta_box' ) );
				add_action( 'save_post',      array( $this, 'save'         ) );
			}

			function yelp_polls_init() {
				register_activation_hook( __FILE__, 'yelp_polls_activate' );
				register_deactivation_hook( __FILE__, 'yelp_polls_deactivate' );
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
				register_post_type( 'Yelp Poll', $args );
			}
		
			public function add_meta_box( $post_type ) {
				$post_types = array( 'yelppoll' );

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

			public function save( $post_id ) {

				if ( ! isset( $_POST['yelp_polls_metabox_nonce'] ) ) {
					return $post_id;
				}

				$nonce = $_POST['yelp_polls_metabox_nonce'];

				if ( ! wp_verify_nonce( $nonce, 'yelp_polls_metabox' ) ) {
					return $post_id;
				}

				if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
					return $post_id;
				}

				if ( 'page' == $_POST['yelppoll'] ) {
					if ( ! current_user_can( 'edit_page', $post_id ) ) {
						return $post_id;
					}
				} else {
					if ( ! current_user_can( 'edit_post', $post_id ) ) {
						return $post_id;
					}
				}

				$yelp_polls_location = sanitize_text_field( $_POST['yelp-polls-location'] );
				$yelp_polls_type = sanitize_text_field( $_POST['yelp-polls-type'] );

				update_post_meta( $post_id, '_yelp_polls_location', $yelp_polls_location );
				update_post_meta( $post_id, '_yelp_polls_type', $yelp_polls_type );
			}

			public function render_meta_box_location_content( $post ) {

				wp_nonce_field( 'yelp_polls_metabox', 'yelp_polls_metabox_nonce' );

				$location = get_post_meta( $post->ID, '_yelp_polls_location', true );

				?>
				<label for="yelp-polls-location">
					<?php _e( 'City, State Abbreviate. Example: San Francisco, CA', 'yelp-polls' ); ?>
				</label>
					
				<input style="width:100%;" type="text" class="form-control" name="yelp-polls-location" value="<?php echo esc_attr( $location ); ?>" /> 
				<?php
			}

			public function render_meta_box_type_content( $post ) {

				$type = get_post_meta( $post->ID, '_yelp_polls_type', true );

				?>
				<label for="yelp-polls-type">
					<?php _e( 'Entertainment, Restaurant, Fine Dining, Pizza, etc...', 'yelp-polls' ); ?>
				</label>

				<input style="width:100%;" type="text" class="form-control" name="yelp-polls-type" value="<?php echo esc_attr( $type ); ?>" />

				<?php
			}
			
			function yelp_polls_activate() {
				Yelp_Polls::yelp_polls_cpt();
			}

			function yelp_polls_deactivate() {

			}
		}

		$yelpPolls = new Yelp_Polls;
	}
