<?php 
/**
 * Register Custom Post Types.
 */
function cptui_register_my_cpts_projects() {

	/**
	 * Post Type: Past Projects.
	 */

	$labels = [
		"name" => esc_html__( "Past Projects", "portfolio" ),
		"singular_name" => esc_html__( "Project", "portfolio" ),
		"menu_name" => esc_html__( "Projects", "portfolio" ),
		"all_items" => esc_html__( "All Projects", "portfolio" ),
		"add_new" => esc_html__( "Add new", "portfolio" ),
		"add_new_item" => esc_html__( "Add new Project", "portfolio" ),
		"edit_item" => esc_html__( "Edit Project", "portfolio" ),
		"new_item" => esc_html__( "New Project", "portfolio" ),
		"view_item" => esc_html__( "View Project", "portfolio" ),
		"view_items" => esc_html__( "View Projects", "portfolio" ),
		"search_items" => esc_html__( "Search Projects", "portfolio" ),
		"not_found" => esc_html__( "No Projects found", "portfolio" ),
		"not_found_in_trash" => esc_html__( "No Projects found in trash", "portfolio" ),
		"parent" => esc_html__( "Parent Project:", "portfolio" ),
		"featured_image" => esc_html__( "Featured image for this Project", "portfolio" ),
		"set_featured_image" => esc_html__( "Set featured image for this Project", "portfolio" ),
		"remove_featured_image" => esc_html__( "Remove featured image for this Project", "portfolio" ),
		"use_featured_image" => esc_html__( "Use as featured image for this Project", "portfolio" ),
		"archives" => esc_html__( "Project archives", "portfolio" ),
		"insert_into_item" => esc_html__( "Insert into Project", "portfolio" ),
		"uploaded_to_this_item" => esc_html__( "Upload to this Project", "portfolio" ),
		"filter_items_list" => esc_html__( "Filter Projects", "portfolio" ),
		"items_list_navigation" => esc_html__( "Projects list navigation", "portfolio" ),
		"items_list" => esc_html__( "Projects list", "portfolio" ),
		"attributes" => esc_html__( "Projects attributes", "portfolio" ),
		"name_admin_bar" => esc_html__( "Project", "portfolio" ),
		"item_published" => esc_html__( "Project published", "portfolio" ),
		"item_published_privately" => esc_html__( "Project published privately.", "portfolio" ),
		"item_reverted_to_draft" => esc_html__( "Project reverted to draft.", "portfolio" ),
		"item_trashed" => esc_html__( "Project trashed.", "portfolio" ),
		"item_scheduled" => esc_html__( "Project scheduled", "portfolio" ),
		"item_updated" => esc_html__( "Project updated.", "portfolio" ),
		"parent_item_colon" => esc_html__( "Parent Project:", "portfolio" ),
	];

	$args = [
		"label" => esc_html__( "Past Projects", "portfolio" ),
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
