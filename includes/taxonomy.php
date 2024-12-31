<?php

// 注册自定义分类方法
function wprs_register_product_collection_taxonomy() {
	$labels = array(
		'name'              => esc_html__('Product Collections', 'wenprise-product-collection'),
		'singular_name'     => esc_html__('Product Collection', 'wenprise-product-collection'),
		'search_items'      => esc_html__('Search Collections', 'wenprise-product-collection'),
		'all_items'         => esc_html__('All Collections', 'wenprise-product-collection'),
		'parent_item'       => esc_html__('Parent Collection', 'wenprise-product-collection'),
		'parent_item_colon' => esc_html__('Parent Collection:', 'wenprise-product-collection'),
		'edit_item'         => esc_html__('Edit Collection', 'wenprise-product-collection'),
		'update_item'       => esc_html__('Update Collection', 'wenprise-product-collection'),
		'add_new_item'      => esc_html__('Add New Collection', 'wenprise-product-collection'),
		'new_item_name'     => esc_html__('New Collection Name', 'wenprise-product-collection'),
		'menu_name'         => esc_html__('Collections', 'wenprise-product-collection'),
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
