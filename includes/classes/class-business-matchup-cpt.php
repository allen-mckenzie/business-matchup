<?php
    namespace BusinessMatchup;

	/**
	 * Business_Matchup_CPT class
	 * This class contains the functions needed to create our custom post type.
	 * 
	 * @since 0.1.3
	 */
    class Business_Matchup_CPT {

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


    }