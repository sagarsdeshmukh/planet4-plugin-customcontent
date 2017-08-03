<?php
/*
Plugin Name: Planet4 Custom Content
Plugin URI: https://greenpeace.org/
Description: Declares a plugin that will create a custom post type displaying challenges.
Version: 0.1
Author: Greenpeace Tech Team
Author URI: https://greenpeace.org/
License: GPLv2
*/

class planet4_customcontent {

	//Adding actions here
	function planet4_customcontent() {
		add_action('init',array($this,'create_post_type'));
		add_action('init',array($this,'create_taxonomies'));
		add_action('manage_p4_content_posts_columns',array($this,'columns'),10,2);
		add_action('manage_p4_content_posts_custom_column',array($this,'column_data'),11,2);
		add_filter('posts_join',array($this,'join'),10,1);
		add_filter('posts_orderby',array($this,'set_default_sort'),20,2);
	}

	function create_post_type() {
		$labels = array(
			'name'               => 'Challenge',
			'singular_name'      => 'Challenge',
			'menu_name'          => 'Challenge',
			'name_admin_bar'     => 'Challenge',
			'add_new'            => 'Add New',
			'add_new_item'       => 'Add New Challenge',
			'new_item'           => 'New Challenge',
			'edit_item'          => 'Edit Challenge',
			'view_item'          => 'View Challenge',
			'all_items'          => 'All Challenge',
			'search_items'       => 'Search Challenge',
			'parent_item_colon'  => 'Parent Challenges',
			'not_found'          => 'No Challenge Found',
			'not_found_in_trash' => 'No Challenge Found in Trash'
		);

		$args = array(
			'labels'              => $labels,
			'public'              => true,
			'exclude_from_search' => false,
			'publicly_queryable'  => true,
			'show_ui'             => true,
			'show_in_nav_menus'   => true,
			'show_in_menu'        => true,
			'show_in_admin_bar'   => true,
			'menu_position'       => 5,
			'menu_icon'           => 'dashicons-admin-appearance',
			'capability_type'     => 'post',
			'hierarchical'        => false,
			'supports'            => array( 'title', 'editor', 'author', 'thumbnail', 'excerpt', 'comments', 'custom-fields' ),
			'has_archive'         => true,
			'rewrite'             => array( 'slug' => 'projects' ),
			'query_var'           => true
		);

		register_post_type( 'p4_content', $args );
	}

	function create_taxonomies() {

		// Add new taxonomy, make it hierarchical (like categories)
		$labels = array(
			'name'              => 'Types',
			'singular_name'     => 'Type',
			'search_items'      => 'Search Types',
			'all_items'         => 'All Types',
			'parent_item'       => 'Parent Type',
			'parent_item_colon' => 'Parent Type:',
			'edit_item'         => 'Edit Type',
			'update_item'       => 'Update Type',
			'add_new_item'      => 'Add New Type',
			'new_item_name'     => 'New Type Name',
			'menu_name'         => 'Types',
		);

		$args = array(
			'hierarchical'      => true,
			'labels'            => $labels,
			'show_ui'           => true,
			'show_admin_column' => true,
			'query_var'         => true,
			'rewrite'           => array( 'slug' => 'type' ),
		);

		register_taxonomy('p4_content_type',array('p4_content'),$args);

		// Add new taxonomy, NOT hierarchical (like tags)
		$labels = array(
			'name'                       => 'Attributes',
			'singular_name'              => 'Attribute',
			'search_items'               => 'Attributes',
			'popular_items'              => 'Popular Attributes',
			'all_items'                  => 'All Attributes',
			'parent_item'                => null,
			'parent_item_colon'          => null,
			'edit_item'                  => 'Edit Attribute',
			'update_item'                => 'Update Attribute',
			'add_new_item'               => 'Add New Attribute',
			'new_item_name'              => 'New Attribute Name',
			'separate_items_with_commas' => 'Separate Attributes with commas',
			'add_or_remove_items'        => 'Add or remove Attributes',
			'choose_from_most_used'      => 'Choose from most used Attributes',
			'not_found'                  => 'No Attributes found',
			'menu_name'                  => 'Attributes',
		);

		$args = array(
			'hierarchical'          => false,
			'labels'                => $labels,
			'show_ui'               => true,
			'show_admin_column'     => true,
			'update_count_callback' => '_update_post_term_count',
			'query_var'             => true,
			'rewrite'               => array( 'slug' => 'attribute' ),
		);

		register_taxonomy('p4_content_attribute','p4_content',$args);
	}

	function columns($columns) {
		unset($columns['date']);
		unset($columns['taxonomy-p4_content_attribute']);
		unset($columns['comments']);
		unset($columns['author']);
		return array_merge(
			$columns,
			array(
				'p4_awards' => 'Awards',
				'p4_timeframe' => 'Timeframe'
			));
	}

	function column_data($column,$post_id) {
		switch($column) {
			case 'p4_awards' :
				echo get_post_meta($post_id,'awards',1);
				break;
			case 'p4_timeframe' :
				echo get_post_meta($post_id,'timeframe',1);
				break;
		}
	}

	function join($wp_join) {
		global $wpdb;
		if(get_query_var('post_type') == 'p4_content') {
			$wp_join .= " LEFT JOIN (
					SELECT post_id, meta_value AS awards
					FROM $wpdb->postmeta
					WHERE meta_key = 'awards' ) AS meta
					ON $wpdb->posts.ID = meta.post_id ";
		}
		return ($wp_join);
	}

	function set_default_sort($orderby,&$query) {
		global $wpdb;
		if(get_query_var('post_type') == 'p4_content') {
			return "meta.awards DESC";
		}
	 	return $orderby;
	}
}

new planet4_customcontent();

?>