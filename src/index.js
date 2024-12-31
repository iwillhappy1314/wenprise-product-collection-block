const { registerBlockType } = wp.blocks;
const { InspectorControls } = wp.blockEditor;
const { PanelBody, SelectControl, RangeControl } = wp.components;
const { useState, useEffect } = wp.element;
const { apiFetch } = wp;
const { __ } = wp.i18n; // 引入翻译函数
const ServerSideRender = wp.serverSideRender;

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
	},

	edit: function(props) {
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
					<PanelBody title={__('Block Settings', 'wenprise-product-collection')}>
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

					<PanelBody title={__('Layout Settings', 'wenprise-product-collection')} initialOpen={true}>
						<h3 className="components-base-control">{__('Desktop', 'wenprise-product-collection')} (≥1024px)</h3>
						<RangeControl
							label={__('Columns', 'wenprise-product-collection')}
							value={attributes.columns}
							onChange={(columns) => setAttributes({ columns })}
							min={1}
							max={6}
						/>
						<RangeControl
							label={__('Column Gap', 'wenprise-product-collection') + ' (px)'}
							value={attributes.columnGap}
							onChange={(columnGap) => setAttributes({ columnGap })}
							min={0}
							max={100}
						/>
						<RangeControl
							label={__('Row Gap', 'wenprise-product-collection') + ' (px)'}
							value={attributes.rowGap}
							onChange={(rowGap) => setAttributes({ rowGap })}
							min={0}
							max={100}
						/>

						<h3 className="components-base-control">{__('Tablet', 'wenprise-product-collection')} (≥768px)</h3>
						<RangeControl
							label={__('Columns', 'wenprise-product-collection')}
							value={attributes.tabletColumns}
							onChange={(tabletColumns) => setAttributes({ tabletColumns })}
							min={1}
							max={4}
						/>
						<RangeControl
							label={__('Column Gap', 'wenprise-product-collection') + ' (px)'}
							value={attributes.tabletColumnGap}
							onChange={(tabletColumnGap) => setAttributes({ tabletColumnGap })}
							min={0}
							max={80}
						/>
						<RangeControl
							label={__('Row Gap', 'wenprise-product-collection') + ' (px)'}
							value={attributes.tabletRowGap}
							onChange={(tabletRowGap) => setAttributes({ tabletRowGap })}
							min={0}
							max={80}
						/>

						<h3 className="components-base-control">{__('Mobile', 'wenprise-product-collection')} (&lt;768px)</h3>
						<RangeControl
							label={__('Columns', 'wenprise-product-collection')}
							value={attributes.mobileColumns}
							onChange={(mobileColumns) => setAttributes({ mobileColumns })}
							min={1}
							max={2}
						/>
						<RangeControl
							label={__('Column Gap', 'wenprise-product-collection') + ' (px)'}
							value={attributes.mobileColumnGap}
							onChange={(mobileColumnGap) => setAttributes({ mobileColumnGap })}
							min={0}
							max={50}
						/>
						<RangeControl
							label={__('Row Gap', 'wenprise-product-collection') + ' (px)'}
							value={attributes.mobileRowGap}
							onChange={(mobileRowGap) => setAttributes({ mobileRowGap })}
							min={0}
							max={50}
						/>

						<RangeControl
							label={__('Number of Products', 'wenprise-product-collection')}
							value={attributes.productsCount}
							onChange={(productsCount) => setAttributes({ productsCount })}
							min={1}
							max={48}
						/>
					</PanelBody>
				</InspectorControls>
				<div className="wc-block-product-collection">
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

	save: function() {
		return null;
	},
});
