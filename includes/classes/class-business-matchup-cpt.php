<?php
    namespace BusinessMatchup;

	/**
	 * Business_Matchup_CPT class
	 * This class contains the functions needed to create our custom post type.
	 * 
	 * @since 1.0.0
	 */
    class Business_Matchup_CPT {

        /**
		 * business_matchup_cpt function
		 * This function sets up and creates the custom post type for the Business Matchup Polls.
		 * 
		 * @since 1.0.0
		 */
		public function business_matchup_cpt() {
			$labels = array(
				'name'                  => _x( 'Business Matchups', 'Post type general name', 'business-matchup-polls' ),
				'singular_name'         => _x( 'Business Matchups', 'Post type singular name', 'business-matchup-polls' ),
				'menu_name'             => _x( 'Business Matchups', 'Admin Menu text', 'business-matchup-polls' ),
				'name_admin_bar'        => _x( 'Business Matchups', 'Add New on Toolbar', 'business-matchup-polls' ),
				'add_new'               => __( 'Add New', 'business-matchup-polls' ),
				'add_new_item'          => __( 'Add New Business Matchup', 'business-matchup-polls' ),
				'new_item'              => __( 'New Business Matchup', 'business-matchup-polls' ),
				'edit_item'             => __( 'Edit Business Matchup', 'business-matchup-polls' ),
				'view_item'             => __( 'View Business Matchup', 'business-matchup-polls' ),
				'all_items'             => __( 'All Polls', 'business-matchup-polls' ),
				'search_items'          => __( 'Search Business Matchup', 'business-matchup-polls' ),
				'parent_item_colon'     => __( 'Parent Business Matchup:', 'business-matchup-polls' ),
				'not_found'             => __( 'No Business Matchup found.', 'business-matchup-polls' ),
				'not_found_in_trash'    => __( 'No Business Matchup found in Trash.', 'business-matchup-polls' ),
				'featured_image'        => _x( 'Business Matchup Cover Image', 'Overrides the “Featured Image” phrase for this post type. Added in 4.3', 'business-matchup-polls' ),
				'set_featured_image'    => _x( 'Set cover image', 'Overrides the “Set featured image” phrase for this post type. Added in 4.3', 'business-matchup-polls' ),
				'remove_featured_image' => _x( 'Remove cover image', 'Overrides the “Remove featured image” phrase for this post type. Added in 4.3', 'business-matchup-polls' ),
				'use_featured_image'    => _x( 'Use as cover image', 'Overrides the “Use as featured image” phrase for this post type. Added in 4.3', 'business-matchup-polls' ),
				'archives'              => _x( 'Business Matchup archives', 'The post type archive label used in nav menus. Default “Post Archives”. Added in 4.4', 'business-matchup-polls' ),
				'insert_into_item'      => _x( 'Insert into Business Matchup', 'Overrides the “Insert into post”/”Insert into page” phrase (used when inserting media into a post). Added in 4.4', 'business-matchup-polls' ),
				'uploaded_to_this_item' => _x( 'Uploaded to this Business Matchup', 'Overrides the “Uploaded to this post”/”Uploaded to this page” phrase (used when viewing media attached to a post). Added in 4.4', 'business-matchup-polls' ),
				'filter_items_list'     => _x( 'Filter Business Matchup list', 'Screen reader text for the filter links heading on the post type listing screen. Default “Filter posts list”/”Filter pages list”. Added in 4.4', 'business-matchup-polls' ),
				'items_list_navigation' => _x( 'Business Matchup list navigation', 'Screen reader text for the pagination heading on the post type listing screen. Default “Posts list navigation”/”Pages list navigation”. Added in 4.4', 'business-matchup-polls' ),
				'items_list'            => _x( 'Business Matchup list', 'Screen reader text for the items list heading on the post type listing screen. Default “Posts list”/”Pages list”. Added in 4.4', 'business-matchup-polls' ),
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


    }