<?php
/**
 * 为产品集合添加头部背景图片支持和缩略图支持
 */

function wprs_add_collection_meta_fields() {
	// 添加 WooCommerce 分类缩略图支持
	add_action('product_collection_add_form_fields', 'wprs_collection_add_thumbnail_field');
	add_action('product_collection_edit_form_fields', 'wprs_collection_edit_thumbnail_field', 10, 2);

	// 保存字段
	add_action('created_product_collection', 'wprs_save_collection_meta_fields', 10, 1);
	add_action('edited_product_collection', 'wprs_save_collection_meta_fields', 10, 1);
}
add_action('init', 'wprs_add_collection_meta_fields');

/**
 * 添加分类缩略图字段（新增页面）
 */
function wprs_collection_add_thumbnail_field() {
	if (!function_exists('wc_tax_meta_fields')) {
		?>
		<div class="form-field term-thumbnail-wrap">
			<label><?php esc_html_e('Thumbnail', 'woocommerce'); ?></label>
			<div id="product_collection_thumbnail" style="float: left; margin-right: 10px;">
				<img src="<?php echo esc_url(wc_placeholder_img_src()); ?>" width="60px" height="60px" />
			</div>
			<div style="line-height: 60px;">
				<input type="hidden" id="product_collection_thumbnail_id" name="product_collection_thumbnail_id" />
				<button type="button" class="upload_image_button button">
					<?php esc_html_e('Upload/Add image', 'woocommerce'); ?>
				</button>
				<button type="button" class="remove_image_button button">
					<?php esc_html_e('Remove image', 'woocommerce'); ?>
				</button>
			</div>
			<script type="text/javascript">
				// 仅当 WooCommerce 的缩略图脚本未加载时才添加
				if (typeof jQuery !== 'undefined' && typeof wc_add_tax_meta_fields === 'undefined') {
					jQuery(function($){
						var mediaFrame;

						$(document).on('click', '.upload_image_button', function(e){
							e.preventDefault();

							if (mediaFrame) {
								mediaFrame.open();
								return;
							}

							mediaFrame = wp.media({
								title: '<?php esc_html_e('Choose an image', 'woocommerce'); ?>',
								button: {
									text: '<?php esc_html_e('Use image', 'woocommerce'); ?>'
								},
								multiple: false
							});

							mediaFrame.on('select', function(){
								var attachment = mediaFrame.state().get('selection').first().toJSON();
								$('#product_collection_thumbnail_id').val(attachment.id);
								$('#product_collection_thumbnail img').attr('src', attachment.url);
								$('.remove_image_button').show();
							});

							mediaFrame.open();
						});

						$(document).on('click', '.remove_image_button', function(){
							$('#product_collection_thumbnail img').attr('src', '<?php echo esc_js(wc_placeholder_img_src()); ?>');
							$('#product_collection_thumbnail_id').val('');
							$('.remove_image_button').hide();
							return false;
						});
					});
				}
			</script>
			<div class="clear"></div>
		</div>
		<?php
	}
}

/**
 * 添加分类缩略图字段（编辑页面）
 */
function wprs_collection_edit_thumbnail_field($term, $taxonomy) {
	if (!function_exists('wc_tax_meta_fields')) {
		$thumbnail_id = get_term_meta($term->term_id, 'thumbnail_id', true);
		$image = $thumbnail_id ? wp_get_attachment_image_url($thumbnail_id, 'thumbnail') : wc_placeholder_img_src();
		?>
		<tr class="form-field term-thumbnail-wrap">
			<th scope="row" valign="top">
				<label><?php esc_html_e('Thumbnail', 'woocommerce'); ?></label>
			</th>
			<td>
				<div id="product_collection_thumbnail" style="float: left; margin-right: 10px;">
					<img src="<?php echo esc_url($image); ?>" width="60px" height="60px" />
				</div>
				<div style="line-height: 60px;">
					<input type="hidden" id="product_collection_thumbnail_id" name="product_collection_thumbnail_id" value="<?php echo esc_attr($thumbnail_id); ?>" />
					<button type="button" class="upload_image_button button">
						<?php esc_html_e('Upload/Add image', 'woocommerce'); ?>
					</button>
					<button type="button" class="remove_image_button button <?php echo $thumbnail_id ? '' : 'hidden'; ?>">
						<?php esc_html_e('Remove image', 'woocommerce'); ?>
					</button>
				</div>
				<script type="text/javascript">
					if (typeof jQuery !== 'undefined' && typeof wc_add_tax_meta_fields === 'undefined') {
						jQuery(function($){
							var mediaFrame;

							$(document).on('click', '.upload_image_button', function(e){
								e.preventDefault();

								if (mediaFrame) {
									mediaFrame.open();
									return;
								}

								mediaFrame = wp.media({
									title: '<?php esc_html_e('Choose an image', 'woocommerce'); ?>',
									button: {
										text: '<?php esc_html_e('Use image', 'woocommerce'); ?>'
									},
									multiple: false
								});

								mediaFrame.on('select', function(){
									var attachment = mediaFrame.state().get('selection').first().toJSON();
									$('#product_collection_thumbnail_id').val(attachment.id);
									$('#product_collection_thumbnail img').attr('src', attachment.url);
									$('.remove_image_button').show();
								});

								mediaFrame.open();
							});

							$(document).on('click', '.remove_image_button', function(){
								$('#product_collection_thumbnail img').attr('src', '<?php echo esc_js(wc_placeholder_img_src()); ?>');
								$('#product_collection_thumbnail_id').val('');
								$('.remove_image_button').hide();
								return false;
							});
						});
					}
				</script>
				<div class="clear"></div>
			</td>
		</tr>
		<?php
	}
}


/**
 * 添加背景图片上传所需的 JavaScript
 */
function wprs_add_header_bg_scripts() {
	?>
	<script>
		jQuery(document).ready(function($) {
			var frame;
			var imagePreview = $('.header-bg-preview img');
			var inputField = $('#collection_above_header_bg');
			var uploadButton = $('#upload_header_bg_button');
			var removeButton = $('#remove_header_bg_button');

			uploadButton.click(function(e) {
				e.preventDefault();

				if (frame) {
					frame.open();
					return;
				}

				frame = wp.media({
					title: '<?php _e("Select or Upload Header Background Image", "wenprise-products-by-tags"); ?>',
					button: {
						text: '<?php _e("Use this image", "wenprise-products-by-tags"); ?>'
					},
					multiple: false
				});

				frame.on('select', function() {
					var attachment = frame.state().get('selection').first().toJSON();
					inputField.val(attachment.id);
					imagePreview.attr('src', attachment.url).show();
					removeButton.removeClass('hidden');
				});

				frame.open();
			});

			removeButton.click(function(e) {
				e.preventDefault();
				inputField.val('');
				imagePreview.attr('src', '').hide();
				$(this).addClass('hidden');
			});
		});
	</script>
	<?php
}

/**
 * 保存所有字段值
 */
function wprs_save_collection_meta_fields($term_id) {
	// 保存分类缩略图
	if (isset($_POST['product_collection_thumbnail_id'])) {
		update_term_meta($term_id, 'thumbnail_id', absint($_POST['product_collection_thumbnail_id']));
	}
}
