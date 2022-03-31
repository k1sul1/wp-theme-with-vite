<?php
/**
 * Register custom post types and taxonomies here.
 */
namespace k1\CCT;

add_action('init', function () {
  /**
   * REMEMBER TO FLUSH PERMALINKS AND CONFIGURE POLYLANG SETTINGS AFTER ADDING NEW POST TYPES!
   *
   * Don't forget or else...
   */

  $args = [
		'label'  => esc_html__( 'Services', 'theme-base' ),
		'labels' => [
			'menu_name'          => esc_html__( 'Services', 'theme-base' ),
			'name_admin_bar'     => esc_html__( 'Service', 'theme-base' ),
			'add_new'            => esc_html__( 'Add service', 'theme-base' ),
			'add_new_item'       => esc_html__( 'Add new service', 'theme-base' ),
			'new_item'           => esc_html__( 'New service', 'theme-base' ),
			'edit_item'          => esc_html__( 'Edit service', 'theme-base' ),
			'view_item'          => esc_html__( 'View service', 'theme-base' ),
			'update_item'        => esc_html__( 'View service', 'theme-base' ),
			'all_items'          => esc_html__( 'All Services', 'theme-base' ),
			'search_items'       => esc_html__( 'Search Services', 'theme-base' ),
			'parent_item_colon'  => esc_html__( 'Parent service', 'theme-base' ),
			'not_found'          => esc_html__( 'No Services found', 'theme-base' ),
			'not_found_in_trash' => esc_html__( 'No Services found in Trash', 'theme-base' ),
			'name'               => esc_html__( 'Services', 'theme-base' ),
			'singular_name'      => esc_html__( 'Service', 'theme-base' ),
		],
		'public'              => true,
		'exclude_from_search' => false,
		'publicly_queryable'  => true,
		'show_ui'             => true,
		'show_in_nav_menus'   => true,
		'show_in_admin_bar'   => true,
		'show_in_rest'        => true,
		'capability_type'     => 'post',
		'hierarchical'        => false,
		'has_archive'         => true,
		'query_var'           => true,
		'can_export'          => true,
		'rewrite_no_front'    => false,
		'show_in_menu'        => true,
		'menu_icon'           => 'dashicons-chart-area',
		'supports' => [
			'title',
			'editor',
			'author',
			'thumbnail',
      'excerpt',
      'revisions',
		],
    'taxonomies' => ['category'],

		'rewrite' => true
	];

	register_post_type('service', $args);

  /**
   * REMEMBER TO FLUSH PERMALINKS AND CONFIGURE POLYLANG SETTINGS AFTER ADDING NEW POST TYPES!
   *
   * Don't forget or else...
   */
});
