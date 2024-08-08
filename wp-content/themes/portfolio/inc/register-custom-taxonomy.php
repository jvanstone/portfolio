<?php
/**
 * Register Custom Taxonomy 
 */
function cptui_register_my_taxes() {

	/**
	 * Taxonomy: Technology.
	 */

	$labels = [
		"name" => esc_html__( "Technology", "portfolio" ),
		"singular_name" => esc_html__( "Technology", "portfolio" ),
		"menu_name" => esc_html__( "Technology", "portfolio" ),
		"all_items" => esc_html__( "All Technology", "portfolio" ),
	];

	
	$args = [
		"label" => esc_html__( "Technology", "portfolio" ),
		"labels" => $labels,
		"public" => true,
		"publicly_queryable" => true,
		"hierarchical" => false,
		"show_ui" => true,
		"show_in_menu" => true,
		"show_in_nav_menus" => true,
		"query_var" => true,
		"rewrite" => [ 'slug' => 'technology', 'with_front' => true, ],
		"show_admin_column" => false,
		"show_in_rest" => true,
		"show_tagcloud" => false,
		"rest_base" => "technology",
		"rest_controller_class" => "WP_REST_Terms_Controller",
		"rest_namespace" => "wp/v2",
		"show_in_quick_edit" => false,
		"sort" => false,
		"show_in_graphql" => false,
	];
	register_taxonomy( "technology", [ "clients" ], $args );
}
add_action( 'init', 'cptui_register_my_taxes' );

function cptui_register_my_cpts_resume() {

	/**
	 * Post Type: Resumes.
	 */

	$labels = [
		"name" => esc_html__( "Resumes", "portfolio" ),
		"singular_name" => esc_html__( "Resume", "portfolio" ),
		"menu_name" => esc_html__( "Resumes", "portfolio" ),
		"all_items" => esc_html__( "All Resumes", "portfolio" ),
		"add_new" => esc_html__( "Add new", "portfolio" ),
		"add_new_item" => esc_html__( "Add new Resume", "portfolio" ),
		"edit_item" => esc_html__( "Edit Resume", "portfolio" ),
		"new_item" => esc_html__( "New Resume", "portfolio" ),
		"view_item" => esc_html__( "View Resume", "portfolio" ),
		"view_items" => esc_html__( "View Resumes", "portfolio" ),
		"search_items" => esc_html__( "Search Resumes", "portfolio" ),
		"not_found" => esc_html__( "No Resumes found", "portfolio" ),
		"not_found_in_trash" => esc_html__( "No Resumes found in trash", "portfolio" ),
		"parent" => esc_html__( "Parent Resume:", "portfolio" ),
		"featured_image" => esc_html__( "Featured image for this Resume", "portfolio" ),
		"set_featured_image" => esc_html__( "Set featured image for this Resume", "portfolio" ),
		"remove_featured_image" => esc_html__( "Remove featured image for this Resume", "portfolio" ),
		"use_featured_image" => esc_html__( "Use as featured image for this Resume", "portfolio" ),
		"archives" => esc_html__( "Resume archives", "portfolio" ),
		"insert_into_item" => esc_html__( "Insert into Resume", "portfolio" ),
		"uploaded_to_this_item" => esc_html__( "Upload to this Resume", "portfolio" ),
		"filter_items_list" => esc_html__( "Filter Resumes list", "portfolio" ),
		"items_list_navigation" => esc_html__( "Resumes list navigation", "portfolio" ),
		"items_list" => esc_html__( "Resumes list", "portfolio" ),
		"attributes" => esc_html__( "Resumes attributes", "portfolio" ),
		"name_admin_bar" => esc_html__( "Resume", "portfolio" ),
		"item_published" => esc_html__( "Resume published", "portfolio" ),
		"item_published_privately" => esc_html__( "Resume published privately.", "portfolio" ),
		"item_reverted_to_draft" => esc_html__( "Resume reverted to draft.", "portfolio" ),
		"item_trashed" => esc_html__( "Resume trashed.", "portfolio" ),
		"item_scheduled" => esc_html__( "Resume scheduled", "portfolio" ),
		"item_updated" => esc_html__( "Resume updated.", "portfolio" ),
		"parent_item_colon" => esc_html__( "Parent Resume:", "portfolio" ),
	];

	$args = [
		"label" => esc_html__( "Resumes", "portfolio" ),
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
		"can_export" => true,
		"rewrite" => [ "slug" => "resume", "with_front" => false ],
		"query_var" => true,
		"menu_icon" => "dashicons-media-document",
		"supports" => [ "title", "custom-fields" ],
		"show_in_graphql" => false,
	];

	register_post_type( "resume", $args );
}

add_action( 'init', 'cptui_register_my_cpts_resume' );
