<?php 
/**
 * Register Custom Post Types.
 */

 function cptui_register_my_cpts_client() {

	/**
	 * Post Type: Clients.
	 */
	$labels = [
		"name" => esc_html__( "Clients", "icarus" ),
		"singular_name" => esc_html__( "Client", "icarus" ),
		"menu_name" => esc_html__( "My Clients", "icarus" ),
		"all_items" => esc_html__( "All Clients", "icarus" ),
		"add_new" => esc_html__( "Add new", "icarus" ),
		"add_new_item" => esc_html__( "Add new Client", "icarus" ),
		"edit_item" => esc_html__( "Edit Client", "icarus" ),
		"new_item" => esc_html__( "New Client", "icarus" ),
		"view_item" => esc_html__( "View Client", "icarus" ),
		"view_items" => esc_html__( "View Clients", "icarus" ),
		"search_items" => esc_html__( "Search Clients", "icarus" ),
		"not_found" => esc_html__( "No Clients found", "icarus" ),
		"not_found_in_trash" => esc_html__( "No Clients found in trash", "icarus" ),
		"parent" => esc_html__( "Parent Client:", "icarus" ),
		"featured_image" => esc_html__( "Featured image for this Client", "icarus" ),
		"set_featured_image" => esc_html__( "Set featured image for this Client", "icarus" ),
		"remove_featured_image" => esc_html__( "Remove featured image for this Client", "icarus" ),
		"use_featured_image" => esc_html__( "Use as featured image for this Client", "icarus" ),
		"archives" => esc_html__( "Client archives", "icarus" ),
		"insert_into_item" => esc_html__( "Insert into Client", "icarus" ),
		"uploaded_to_this_item" => esc_html__( "Upload to this Client", "icarus" ),
		"filter_items_list" => esc_html__( "Filter Clients list", "icarus" ),
		"items_list_navigation" => esc_html__( "Clients list navigation", "icarus" ),
		"items_list" => esc_html__( "Clients list", "icarus" ),
		"attributes" => esc_html__( "Clients attributes", "icarus" ),
		"name_admin_bar" => esc_html__( "Client", "icarus" ),
		"item_published" => esc_html__( "Client published", "icarus" ),
		"item_published_privately" => esc_html__( "Client published privately.", "icarus" ),
		"item_reverted_to_draft" => esc_html__( "Client reverted to draft.", "icarus" ),
		"item_trashed" => esc_html__( "Client trashed.", "icarus" ),
		"item_scheduled" => esc_html__( "Client scheduled", "icarus" ),
		"item_updated" => esc_html__( "Client updated.", "icarus" ),
		"parent_item_colon" => esc_html__( "Parent Client:", "icarus" ),
	];
	
	$args = [
		"label" => esc_html__( "Clients", "icarus" ),
		"labels" => $labels,
		"description" => "",
		"public" => true,
		"publicly_queryable" => true,
		"show_ui" => true,
		"show_in_rest" => true,
		"rest_base" => "",
		"rest_controller_class" => "WP_REST_Posts_Controller",
		"rest_namespace" => "wp/v2",
		"has_archive" => false,
		"show_in_menu" => true,
		"show_in_nav_menus" => true,
		"delete_with_user" => false,
		"exclude_from_search" => false,
		"capability_type" => "post",
		"map_meta_cap" => true,
		"hierarchical" => false,
		"can_export" => false,
		"rewrite" => [ "slug" => "client", "with_front" => true ],
		"query_var" => true,
		"supports" => [ "title", "editor", "thumbnail" ],
		"taxonomies" => [ "technology" ],
		"show_in_graphql" => false,
	];
	
	register_post_type( "client", $args );
	}
	
add_action( 'init', 'cptui_register_my_cpts_client' );
	