<?php
/**
 * Plugin Name:       Wenprise Product Collection Block
 * Description:       Add product collection taxonomy for WooCommerce, and display products by collection
 * Requires at least: 6.1
 * Requires PHP:      7.0
 * Version:           0.1.0
 * Author:           The WordPress Contributors
 * License:          GPL-2.0-or-later
 * License URI:      https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:      wenprise-product-collection
 *
 * @package CreateBlock
 */

 define('WENPRISE_PRODUCT_COLLECTION_PATH', plugin_dir_path(__FILE__));

 require_once(WENPRISE_PRODUCT_COLLECTION_PATH . 'includes/taxonomy.php');
 require_once(WENPRISE_PRODUCT_COLLECTION_PATH . 'includes/taxonomy-meta.php');

 add_filter('block_categories_all', function ($categories)
{
	return array_merge(
		[
			[
				'slug'  => 'wenprise-blocks',
				'title' => __('Wenprise Blocks', 'flashfox'),
			],
		],
		$categories
	);
}, 10, 1);


function wprs_register_products_by_terms() {
	// 加载翻译文件
	load_plugin_textdomain('wenprise-product-collection', false, dirname(plugin_basename(__FILE__)) . '/languages');

	// 注册区块脚本
	wp_register_script(
		'wenprise-product-collection',
		plugins_url('build/index.js', __FILE__),
		array('wp-blocks', 'wp-element', 'wp-editor', 'wp-components', 'wp-server-side-render', 'wp-i18n')
	);

	// 为 JavaScript 添加翻译支持
	if (function_exists('wp_set_script_translations')) {
		wp_set_script_translations(
			'wenprise-product-collection',
			'wenprise-product-collection',
			plugin_dir_path(__FILE__) . 'languages'
		);
	}

	register_block_type('wenprise/product-collection', array(
		'editor_script' => 'wenprise-product-collection',
		'attributes' => array(
			'taxonomyType' => array(
				'type' => 'string',
				'default' => 'product_tag'
			),
			'tagId' => array(
				'type' => 'string',
				'default' => ''
			),
			'displayStyle' => array(
				'type' => 'string',
				'default' => 'grid'
			),
			'columns' => array(
				'type' => 'number',
				'default' => 4
			),
			'tabletColumns' => array(
				'type' => 'number',
				'default' => 3
			),
			'mobileColumns' => array(
				'type' => 'number',
				'default' => 2
			),
			'columnGap' => array(
				'type' => 'number',
				'default' => 32
			),
			'tabletColumnGap' => array(
				'type' => 'number',
				'default' => 24
			),
			'mobileColumnGap' => array(
				'type' => 'number',
				'default' => 16
			),
			'rowGap' => array(
				'type' => 'number',
				'default' => 32
			),
			'tabletRowGap' => array(
				'type' => 'number',
				'default' => 24
			),
			'mobileRowGap' => array(
				'type' => 'number',
				'default' => 16
			),
			'productsCount' => array(
				'type' => 'number',
				'default' => 12
			),
			// 轮播设置属性
			'perPage' => array(
				'type' => 'number',
				'default' => 4
			),
			'perPageTablet' => array(
				'type' => 'number',
				'default' => 2
			),
			'perPageMobile' => array(
				'type' => 'number',
				'default' => 1
			),
			'gap' => array(
				'type' => 'string',
				'default' => '2rem'
			),
			'gapTablet' => array(
				'type' => 'string',
				'default' => '1.5rem'
			),
			'gapMobile' => array(
				'type' => 'string',
				'default' => '1rem'
			),
			'pagination' => array(
				'type' => 'boolean',
				'default' => true
			),
			'arrows' => array(
				'type' => 'boolean',
				'default' => true
			)
		),
		'render_callback' => 'wprs_render_products_by_terms'
	));
}
add_action('init', 'wprs_register_products_by_terms');

function wprs_render_products_by_terms($attributes) {
	// 获取属性
	$taxonomy_type = $attributes['taxonomyType'] ?? 'product_tag';
	$term_id = $attributes['tagId'];
	$display_style = $attributes['displayStyle'] ?? 'grid';
	$desktop_columns = $attributes['columns'];
	$tablet_columns = $attributes['tabletColumns'];
	$mobile_columns = $attributes['mobileColumns'];
	$desktop_column_gap = $attributes['columnGap'];
	$tablet_column_gap = $attributes['tabletColumnGap'];
	$mobile_column_gap = $attributes['mobileColumnGap'];
	$desktop_row_gap = $attributes['rowGap'];
	$tablet_row_gap = $attributes['tabletRowGap'];
	$mobile_row_gap = $attributes['mobileRowGap'];
	$products_count = $attributes['productsCount'];

	// 轮播设置属性
	$per_page = $attributes['perPage'] ?? 4;
	$per_page_tablet = $attributes['perPageTablet'] ?? 2;
	$per_page_mobile = $attributes['perPageMobile'] ?? 1;
	$gap = $attributes['gap'] ?? '2rem';
	$gap_tablet = $attributes['gapTablet'] ?? '1.5rem';
	$gap_mobile = $attributes['gapMobile'] ?? '1rem';
	$pagination = $attributes['pagination'] ?? true;
	$arrows = $attributes['arrows'] ?? true;

	// 如果没有选择分类项，返回提示信息
	if (empty($term_id)) {
		return '<p>' . esc_html__('Please select a term in the block settings', 'wenprise-product-collection') . '</p>';
	}

	// 获取分类名称显示
	$taxonomy_labels = array(
		'product_tag' => esc_html__('Tag', 'wenprise-product-collection'),
		'product_cat' => esc_html__('Category', 'wenprise-product-collection'),
		'product_collection' => esc_html__('Collection', 'wenprise-product-collection')
	);
	$taxonomy_label = $taxonomy_labels[$taxonomy_type] ?? esc_html__('Term', 'wenprise-product-collection');

	// 设置查询参数
	$args = array(
		'post_type' => 'product',
		'posts_per_page' => $products_count,
		'tax_query' => array(
			array(
				'taxonomy' => $taxonomy_type,
				'field' => 'term_id',
				'terms' => $term_id
			)
		),
		'post_status' => 'publish',
	);

	// 查询产品
	$products = new WP_Query($args);

	ob_start();

	if ($products->have_posts()) {
		// 生成唯一的类名
		$unique_class = 'wprs-products-' . $display_style . '-' . wp_unique_id();

		// 根据显示样式添加不同的 CSS
		if ($display_style === 'grid' || $display_style === 'list') {
			// 网格或列表样式的 CSS
			$responsive_css = sprintf('
				<style>
					.%1$s.wc-block-grid.has-%2$d-columns .wc-block-grid__products {
						grid-template-columns: repeat(%2$d, 1fr);
						gap: %3$dpx %4$dpx;
					}
					@media (max-width: 1023px) {
						.%1$s.wc-block-grid.has-%2$d-columns .wc-block-grid__products {
							grid-template-columns: repeat(%5$d, 1fr);
							gap: %6$dpx %7$dpx;
						}
					}
					@media (max-width: 767px) {
						.%1$s.wc-block-grid.has-%2$d-columns .wc-block-grid__products {
							grid-template-columns: repeat(%8$d, 1fr);
							gap: %9$dpx %10$dpx;
						}
					}
					' . ($display_style === 'list' ? '
					.%1$s .wc-block-grid__product {
						display: flex;
						align-items: center;
						text-align: left;
					}
					.%1$s .wc-block-grid__product-image {
						max-width: 30%%;
						margin-right: 1rem;
						flex: 0 0 auto;
					}
					.%1$s .wc-block-grid__product-info {
						flex: 1;
					}
					' : '') . '
				</style>
			',
				esc_attr($unique_class),
				$desktop_columns,
				$desktop_row_gap,
				$desktop_column_gap,
				$tablet_columns,
				$tablet_row_gap,
				$tablet_column_gap,
				$mobile_columns,
				$mobile_row_gap,
				$mobile_column_gap
			);

			echo wp_kses($responsive_css, array(
				'style' => array()
			));

			echo '<div class="wc-block-grid wc-block-product-collection--' . esc_attr($display_style) . ' has-' . esc_attr($desktop_columns) . '-columns ' . esc_attr($unique_class) . '">';
			echo '<ul class="wc-block-grid__products">';
		} else if ($display_style === 'carousel') {
			// 轮播样式的 CSS
			$carousel_css = sprintf('
				<style>
					.%1$s {
						position: relative;
					}
					.%1$s .swiper-container {
						width: 100%%;
						overflow: hidden;
					}
					.%1$s .swiper-wrapper {
						display: flex;
					}
					.%1$s .swiper-slide {
						flex-shrink: 0;
					}
					.%1$s .swiper-pagination {
						position: relative;
						margin-top: 20px;
						text-align: center;
					}
					.%1$s .swiper-pagination-bullet {
						display: inline-block;
						width: 8px;
						height: 8px;
						margin: 0 4px;
						border-radius: 50%%;
						background: #ccc;
						cursor: pointer;
					}
					.%1$s .swiper-pagination-bullet-active {
						background: #000;
					}
					.%1$s .swiper-button-prev,
					.%1$s .swiper-button-next {
						position: absolute;
						top: 50%%;
						transform: translateY(-50%%);
						width: 40px;
						height: 40px;
						background: rgba(255, 255, 255, 0.8);
						border-radius: 50%%;
						display: flex;
						align-items: center;
						justify-content: center;
						cursor: pointer;
						z-index: 10;
					}
					.%1$s .swiper-button-prev {
						left: 10px;
					}
					.%1$s .swiper-button-next {
						right: 10px;
					}
					.%1$s .swiper-button-prev:after,
					.%1$s .swiper-button-next:after {
						content: "";
						width: 10px;
						height: 10px;
						border-style: solid;
						border-width: 2px 2px 0 0;
					}
					.%1$s .swiper-button-prev:after {
						transform: rotate(-135deg);
						margin-left: 3px;
					}
					.%1$s .swiper-button-next:after {
						transform: rotate(45deg);
						margin-right: 3px;
					}
				</style>
			',
				esc_attr($unique_class)
			);

			echo wp_kses($carousel_css, array(
				'style' => array()
			));

			// 引入 Swiper JS 和 CSS
			wp_enqueue_style('swiper-css', 'https://unpkg.com/swiper/swiper-bundle.min.css');
			wp_enqueue_script('swiper-js', 'https://unpkg.com/swiper/swiper-bundle.min.js', array(), null, true);

			// 初始化轮播的 JavaScript
			$carousel_js = sprintf('
				<script>
					document.addEventListener("DOMContentLoaded", function() {
						var swiper = new Swiper(".%1$s .swiper-container", {
							slidesPerView: %2$d,
							spaceBetween: %3$s,
							pagination: {
								el: ".%1$s .swiper-pagination",
								clickable: true,
								enabled: %4$s,
							},
							navigation: {
								nextEl: ".%1$s .swiper-button-next",
								prevEl: ".%1$s .swiper-button-prev",
								enabled: %5$s,
							},
							breakpoints: {
								768: {
									slidesPerView: %6$d,
									spaceBetween: %7$s,
								},
								1024: {
									slidesPerView: %8$d,
									spaceBetween: %9$s,
								},
							},
						});
					});
				</script>
			',
				esc_attr($unique_class),
				intval($per_page_mobile),
				esc_attr($gap_mobile),
				$pagination ? 'true' : 'false',
				$arrows ? 'true' : 'false',
				intval($per_page_tablet),
				esc_attr($gap_tablet),
				intval($per_page),
				esc_attr($gap)
			);

			echo '<div class="wc-block-product-collection wc-block-product-collection--carousel ' . esc_attr($unique_class) . '">';
			echo '<div class="swiper-container">';
			echo '<div class="swiper-wrapper">';
		}

		while ($products->have_posts()) {
			$products->the_post();
			global $product;

			// 确保 $product 是有效的 WooCommerce 产品对象
			if (!is_a($product, 'WC_Product')) {
				continue;
			}

			// 根据显示样式使用不同的容器
			if ($display_style === 'carousel') {
				echo '<div class="swiper-slide">';
				echo '<div class="wc-block-grid__product">';
			} else {
				echo '<li class="wc-block-grid__product">';
			}

			echo '<a href="' . esc_url(get_permalink()) . '" class="wc-block-grid__product-link">';

			// 产品图片
			if (has_post_thumbnail()) {
				echo '<div class="wc-block-grid__product-image">';
				echo woocommerce_get_product_thumbnail();
				echo '</div>';
			} else {
				echo '<div class="wc-block-grid__product-image">';
				echo wc_placeholder_img();
				echo '</div>';
			}

			// 列表样式时使用内容容器
			if ($display_style === 'list') {
				echo '<div class="wc-block-grid__product-info">';
			}

			// 产品标题
			echo '<h2 class="wc-block-grid__product-title">' . esc_html(get_the_title()) . '</h2>';
			echo '</a>'; // 关闭产品链接

			// 产品价格
			echo '<div class="wc-block-grid__product-price price">' . $product->get_price_html() . '</div>';

			// 添加到购物车按钮
			echo woocommerce_template_loop_add_to_cart();

			// 列表样式时关闭内容容器
			if ($display_style === 'list') {
				echo '</div>';
			}

			// 根据显示样式关闭容器
			if ($display_style === 'carousel') {
				echo '</div>';
				echo '</div>';
			} else {
				echo '</li>';
			}
		}

		// 根据显示样式关闭容器
		if ($display_style === 'carousel') {
			echo '</div>'; // 关闭 swiper-wrapper

			// 添加分页和箭头
			if ($pagination) {
				echo '<div class="swiper-pagination"></div>';
			}

			if ($arrows) {
				echo '<div class="swiper-button-prev"></div>';
				echo '<div class="swiper-button-next"></div>';
			}

			echo '</div>'; // 关闭 swiper-container

			// 添加轮播初始化脚本
			echo wp_kses($carousel_js, array(
				'script' => array()
			));

			echo '</div>'; // 关闭 wc-block-product-collection
		} else {
			echo '</ul>'; // 关闭 wc-block-grid__products
			echo '</div>'; // 关闭 wc-block-grid
		}
	} else {
		printf(
			'<p>%s</p>',
			sprintf(
			/* translators: %s: taxonomy label */
				esc_html__('No products found in this %s', 'wenprise-product-collection'),
				esc_html($taxonomy_label)
			)
		);
	}

	wp_reset_postdata();

	return ob_get_clean();
}
