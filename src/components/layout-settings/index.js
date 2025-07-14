import {
	PanelBody,
	RangeControl,
} from '@wordpress/components';
import { __ } from '@wordpress/i18n';
import {DeviceSwitcher} from '../device-switcher';
import {useResponsive} from '../../hooks/useResponsive';

export const LayoutSettings = ({ attributes, setAttributes }) => {
	const { device, handleDeviceChange } = useResponsive();
	const { 
		columns, tabletColumns, mobileColumns, 
		columnGap, tabletColumnGap, mobileColumnGap, 
		rowGap, tabletRowGap, mobileRowGap,
		productsCount
	} = attributes;

	return (
		<PanelBody title={__('布局设置', 'wenprise-product-collection')} initialOpen={attributes.displayStyle !== 'carousel'}>
			<div style={{display: 'flex', justifyContent: 'space-between', alignItems: 'center', gap: '1rem', marginBottom: '0.5rem'}}>
				<label style={{minWidth: '100px'}}>{__('设备', 'wenprise-product-collection')}</label>
				<DeviceSwitcher selectedDevice={device} onChange={handleDeviceChange} />
			</div>

			{device === 'desktop' && (
				<>
					<RangeControl
						label={__('列数', 'wenprise-product-collection')}
						value={columns}
						onChange={(value) => setAttributes({columns: value})}
						min={1}
						max={6}
					/>
					<RangeControl
						label={__('列间距', 'wenprise-product-collection') + ' (px)'}
						value={columnGap}
						onChange={(value) => setAttributes({columnGap: value})}
						min={0}
						max={100}
					/>
					<RangeControl
						label={__('行间距', 'wenprise-product-collection') + ' (px)'}
						value={rowGap}
						onChange={(value) => setAttributes({rowGap: value})}
						min={0}
						max={100}
					/>
				</>
			)}

			{device === 'tablet' && (
				<>
					<RangeControl
						label={__('列数', 'wenprise-product-collection')}
						value={tabletColumns}
						onChange={(value) => setAttributes({tabletColumns: value})}
						min={1}
						max={4}
					/>
					<RangeControl
						label={__('列间距', 'wenprise-product-collection') + ' (px)'}
						value={tabletColumnGap}
						onChange={(value) => setAttributes({tabletColumnGap: value})}
						min={0}
						max={80}
					/>
					<RangeControl
						label={__('行间距', 'wenprise-product-collection') + ' (px)'}
						value={tabletRowGap}
						onChange={(value) => setAttributes({tabletRowGap: value})}
						min={0}
						max={80}
					/>
				</>
			)}

			{device === 'mobile' && (
				<>
					<RangeControl
						label={__('列数', 'wenprise-product-collection')}
						value={mobileColumns}
						onChange={(value) => setAttributes({mobileColumns: value})}
						min={1}
						max={2}
					/>
					<RangeControl
						label={__('列间距', 'wenprise-product-collection') + ' (px)'}
						value={mobileColumnGap}
						onChange={(value) => setAttributes({mobileColumnGap: value})}
						min={0}
						max={50}
					/>
					<RangeControl
						label={__('行间距', 'wenprise-product-collection') + ' (px)'}
						value={mobileRowGap}
						onChange={(value) => setAttributes({mobileRowGap: value})}
						min={0}
						max={50}
					/>
				</>
			)}

			<RangeControl
				label={__('产品数量', 'wenprise-product-collection')}
				value={productsCount}
				onChange={(value) => setAttributes({productsCount: value})}
				min={1}
				max={48}
			/>
		</PanelBody>
	);
};
