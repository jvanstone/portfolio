<?php
/**
 * Register Custom Taxonomy 
 */
function cptui_register_my_taxes() {

	/**
	 * Taxonomy: Technology.
	 */

	$labels = [
		"name" => esc_html__( "Technology", "icarus" ),
		"singular_name" => esc_html__( "Technology", "icarus" ),
		"menu_name" => esc_html__( "Technology", "icarus" ),
		"all_items" => esc_html__( "All Technology", "icarus" ),
	];

	
	$args = [
		"label" => esc_html__( "Technology", "icarus" ),
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