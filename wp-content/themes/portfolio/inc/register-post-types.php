<?php 
/**
 * Register Custom Post Types.
 */
function cptui_register_my_cpts_projects() {

	/**
	 * Post Type: Past Projects.
	 */

	$labels = [
		"name" => esc_html__( "Past Projects", "icarus" ),
		"singular_name" => esc_html__( "Project", "icarus" ),
		"menu_name" => esc_html__( "Projects", "icarus" ),
		"all_items" => esc_html__( "All Projects", "icarus" ),
		"add_new" => esc_html__( "Add new", "icarus" ),
		"add_new_item" => esc_html__( "Add new Project", "icarus" ),
		"edit_item" => esc_html__( "Edit Project", "icarus" ),
		"new_item" => esc_html__( "New Project", "icarus" ),
		"view_item" => esc_html__( "View Project", "icarus" ),
		"view_items" => esc_html__( "View Projects", "icarus" ),
		"search_items" => esc_html__( "Search Projects", "icarus" ),
		"not_found" => esc_html__( "No Projects found", "icarus" ),
		"not_found_in_trash" => esc_html__( "No Projects found in trash", "icarus" ),
		"parent" => esc_html__( "Parent Project:", "icarus" ),
		"featured_image" => esc_html__( "Featured image for this Project", "icarus" ),
		"set_featured_image" => esc_html__( "Set featured image for this Project", "icarus" ),
		"remove_featured_image" => esc_html__( "Remove featured image for this Project", "icarus" ),
		"use_featured_image" => esc_html__( "Use as featured image for this Project", "icarus" ),
		"archives" => esc_html__( "Project archives", "icarus" ),
		"insert_into_item" => esc_html__( "Insert into Project", "icarus" ),
		"uploaded_to_this_item" => esc_html__( "Upload to this Project", "icarus" ),
		"filter_items_list" => esc_html__( "Filter Projects", "icarus" ),
		"items_list_navigation" => esc_html__( "Projects list navigation", "icarus" ),
		"items_list" => esc_html__( "Projects list", "icarus" ),
		"attributes" => esc_html__( "Projects attributes", "icarus" ),
		"name_admin_bar" => esc_html__( "Project", "icarus" ),
		"item_published" => esc_html__( "Project published", "icarus" ),
		"item_published_privately" => esc_html__( "Project published privately.", "icarus" ),
		"item_reverted_to_draft" => esc_html__( "Project reverted to draft.", "icarus" ),
		"item_trashed" => esc_html__( "Project trashed.", "icarus" ),
		"item_scheduled" => esc_html__( "Project scheduled", "icarus" ),
		"item_updated" => esc_html__( "Project updated.", "icarus" ),
		"parent_item_colon" => esc_html__( "Parent Project:", "icarus" ),
	];

	$args = [
		"label" => esc_html__( "Past Projects", "icarus" ),
		"labels" => $labels,
		"description" => "",
		"public" => true,
		"publicly_queryable" => true,
		"show_ui" => true,
		"show_in_rest" => true,
		"rest_base" => "",
		"rest_controller_class" => "WP_REST_Posts_Controller",
		"rest_namespace" => "wp/v2",
		"has_archive" => true,
		"show_in_menu" => true,
		"show_in_nav_menus" => true,
		"delete_with_user" => false,
		"exclude_from_search" => false,
		"capability_type" => "post",
		"map_meta_cap" => true,
		"hierarchical" => false,
		"can_export" => true,
		"rewrite" => [ "slug" => "projects", "with_front" => false ],
		"query_var" => true,
		"menu_icon" => "dashicons-products",
		"supports" => [ "title", "editor", "thumbnail" ],
		"taxonomies" => [ "technology" ],
		"show_in_graphql" => false,
	];

	register_post_type( "projects", $args );
}

add_action( 'init', 'cptui_register_my_cpts_projects' );
