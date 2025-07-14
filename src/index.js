const { registerBlockType } = wp.blocks;
const { InspectorControls } = wp.blockEditor;
const { PanelBody, SelectControl } = wp.components;
const { useState, useEffect } = wp.element;
const { apiFetch } = wp;
const { __ } = wp.i18n; // 引入翻译函数
const ServerSideRender = wp.serverSideRender;

// 导入轮播设置组件
import { CarouselSettings, LayoutSettings } from 'wenprise-wp-components';

registerBlockType('wenprise/product-collection', {
	title: __('Product Collection', 'wenprise-product-collection'),
	icon: 'grid-view',
	category: 'wenprise-blocks',
	attributes: {
		taxonomyType: {
			type: 'string',
			default: 'product_tag',
		},
		tagId: {
			type: 'string',
			default: '',
		},
		displayStyle: {
			type: 'string',
			default: 'grid',
		},
		columns: {
			type: 'number',
			default: 4,
		},
		tabletColumns: {
			type: 'number',
			default: 3,
		},
		mobileColumns: {
			type: 'number',
			default: 2,
		},
		columnGap: {
			type: 'number',
			default: 32,
		},
		tabletColumnGap: {
			type: 'number',
			default: 24,
		},
		mobileColumnGap: {
			type: 'number',
			default: 16,
		},
		rowGap: {
			type: 'number',
			default: 32,
		},
		tabletRowGap: {
			type: 'number',
			default: 24,
		},
		mobileRowGap: {
			type: 'number',
			default: 16,
		},
		productsCount: {
			type: 'number',
			default: 12,
		},
		// 轮播设置属性
		perPage: {
			type: 'number',
			default: 4,
		},
		perPageTablet: {
			type: 'number',
			default: 2,
		},
		perPageMobile: {
			type: 'number',
			default: 1,
		},
		gap: {
			type: 'string',
			default: '2rem',
		},
		gapTablet: {
			type: 'string',
			default: '1.5rem',
		},
		gapMobile: {
			type: 'string',
			default: '1rem',
		},
		pagination: {
			type: 'boolean',
			default: true,
		},
		arrows: {
			type: 'boolean',
			default: true,
		},
	},

	edit: function (props) {
		const { attributes, setAttributes } = props;
		const [taxonomyTerms, setTaxonomyTerms] = useState([]);
		const [loading, setLoading] = useState(false);

		// 可用的分类方式
		const taxonomyOptions = [
			{ label: __('Product Tags', 'wenprise-product-collection'), value: 'product_tag' },
			{ label: __('Product Categories', 'wenprise-product-collection'), value: 'product_cat' },
			{ label: __('Product Collections', 'wenprise-product-collection'), value: 'product_collection' },
		];

		// 分类方式的显示名称映射
		const taxonomyLabels = {
			'product_tag': __('Tag', 'wenprise-product-collection'),
			'product_cat': __('Category', 'wenprise-product-collection'),
			'product_collection': __('Collection', 'wenprise-product-collection')
		};

		useEffect(() => {
			if (attributes.taxonomyType) {
				loadTaxonomyTerms(attributes.taxonomyType);
			}
		}, [attributes.taxonomyType]);

		// 加载指定分类方式的所有项
		const loadTaxonomyTerms = async (taxonomy) => {
			setLoading(true);
			try {
				const terms = await apiFetch({
					path: `/wp/v2/${taxonomy}?per_page=100`
				});
				const formattedTerms = terms.map((term) => ({
					label: term.name,
					value: term.id.toString(),
				}));
				setTaxonomyTerms(formattedTerms);
			} catch (error) {
				console.error('Error loading taxonomy terms:', error);
				setTaxonomyTerms([]);
			}
			setLoading(false);
		};

		return (
			<>
				<InspectorControls>
					<PanelBody title={__('区块设置', 'wenprise-product-collection')}>
						<SelectControl
							label={__('Select Taxonomy', 'wenprise-product-collection')}
							value={attributes.taxonomyType}
							options={taxonomyOptions}
							onChange={(taxonomyType) => {
								setAttributes({
									taxonomyType,
									tagId: ''
								});
							}}
						/>
						<SelectControl
							label={__('Select', 'wenprise-product-collection') + ' ' + taxonomyLabels[attributes.taxonomyType]}
							value={attributes.tagId}
							options={[
								{ label: __('Please select', 'wenprise-product-collection'), value: '' },
								...taxonomyTerms,
							]}
							onChange={(tagId) => setAttributes({ tagId })}
						/>
					</PanelBody>

					<PanelBody title={__('显示样式', 'wenprise-product-collection')}>
						<SelectControl
							label={__('显示样式', 'wenprise-product-collection')}
							value={attributes.displayStyle}
							options={[
								{ value: 'grid', label: __('网格', 'wenprise-product-collection') },
								{ value: 'list', label: __('列表', 'wenprise-product-collection') },
								{ value: 'carousel', label: __('轮播', 'wenprise-product-collection') },
							]}
							onChange={(displayStyle) => setAttributes({ displayStyle })}
						/>
					</PanelBody>

					{attributes.displayStyle === 'carousel' && (
						<CarouselSettings attributes={attributes} setAttributes={setAttributes} />
					)}

					{(attributes.displayStyle === 'grid' || attributes.displayStyle === 'list') && (
						<LayoutSettings attributes={attributes} setAttributes={setAttributes} />
					)}
				</InspectorControls>
				<div className={`wc-block-product-collection wc-block-product-collection--${attributes.displayStyle}`}>
					{loading ? (
						<p>{__('Loading...', 'wenprise-product-collection')}</p>
					) : !attributes.tagId ? (
						<p>{__('Please select a term in the sidebar settings', 'wenprise-product-collection')}</p>
					) : (
						<ServerSideRender
							block="wenprise/product-collection"
							attributes={attributes}
						/>
					)}
				</div>
			</>
		);
	},

	save: function () {
		return null;
	},
});
