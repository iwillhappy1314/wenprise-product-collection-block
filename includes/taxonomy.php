<?php

// 注册自定义分类方法
function wprs_register_product_collection_taxonomy() {
	$labels = array(
		'name'              => esc_html__('Product Collections', 'wenprise-products-by-tags'),
		'singular_name'     => esc_html__('Product Collection', 'wenprise-products-by-tags'),
		'search_items'      => esc_html__('Search Collections', 'wenprise-products-by-tags'),
		'all_items'         => esc_html__('All Collections', 'wenprise-products-by-tags'),
		'parent_item'       => esc_html__('Parent Collection', 'wenprise-products-by-tags'),
		'parent_item_colon' => esc_html__('Parent Collection:', 'wenprise-products-by-tags'),
		'edit_item'         => esc_html__('Edit Collection', 'wenprise-products-by-tags'),
		'update_item'       => esc_html__('Update Collection', 'wenprise-products-by-tags'),
		'add_new_item'      => esc_html__('Add New Collection', 'wenprise-products-by-tags'),
		'new_item_name'     => esc_html__('New Collection Name', 'wenprise-products-by-tags'),
		'menu_name'         => esc_html__('Collections', 'wenprise-products-by-tags'),
	);

	$args = array(
		'hierarchical'      => true,
		'labels'            => $labels,
		'show_ui'           => true,
		'show_admin_column' => true,
		'query_var'         => true,
		'rewrite'           => array('slug' => 'collection'),
		'show_in_rest'      => true,
	);

	register_taxonomy('product_collection', array('product'), $args);
}
add_action('init', 'wprs_register_product_collection_taxonomy', 0);
