import {
	PanelBody,
	RangeControl,
	ToggleControl,
	TextControl,
} from '@wordpress/components';
import { __ } from '@wordpress/i18n';
import {DeviceSwitcher} from '../device-switcher';
import {useResponsive} from '../../hooks/useResponsive';

export const CarouselSettings = ({ attributes, setAttributes }) => {
	const { device, handleDeviceChange } = useResponsive();
	const { perPage, perPageTablet, perPageMobile, gap, gapTablet, gapMobile, pagination, arrows } = attributes;

	return (
		<PanelBody title={__('轮播设置')} initialOpen={true}>

			<div style={{display: 'flex', justifyContent: 'space-between', alignItems: 'center', gap: '1rem', marginBottom: '0.5rem'}}>
				<label style={{minWidth: '100px'}}>{__('每页显示数量')}</label>
				<DeviceSwitcher selectedDevice={device} onChange={handleDeviceChange} />
			</div>

			{device === 'desktop' && (
				<RangeControl
					value={perPage}
					onChange={(value) => setAttributes({perPage: value})}
					min={1}
					max={8}
				/>
			)}

			{device === 'tablet' && (
				<RangeControl
					value={perPageTablet}
					onChange={(value) => setAttributes({perPageTablet: value})}
					min={1}
					max={6}
				/>
			)}

			{device === 'mobile' && (
				<RangeControl
					value={perPageMobile}
					onChange={(value) => setAttributes({perPageMobile: value})}
					min={1}
					max={4}
				/>
			)}

			<div style={{display: 'flex', justifyContent: 'space-between', alignItems: 'center', gap: '1rem', marginBottom: '0.5rem'}}>
				<label style={{minWidth: '100px'}}>{__('间距')}</label>
				<DeviceSwitcher selectedDevice={device} onChange={handleDeviceChange} />
			</div>

			{device === 'desktop' && (
				<TextControl
					value={gap}
					onChange={(value) => setAttributes({gap: value})}
					help={__('支持 rem、em、px 等单位，如：2rem')}
				/>
			)}

			{device === 'tablet' && (
				<TextControl
					value={gapTablet}
					onChange={(value) => setAttributes({gapTablet: value})}
					help={__('支持 rem、em、px 等单位，如：2rem')}
				/>
			)}

			{device === 'mobile' && (
				<TextControl
					value={gapMobile}
					onChange={(value) => setAttributes({gapMobile: value})}
					help={__('支持 rem、em、px 等单位，如：2rem')}
				/>
			)}

			<ToggleControl
				label={__('显示分页器')}
				checked={pagination}
				onChange={(value) => setAttributes({pagination: value})}
			/>

			<ToggleControl
				label={__('显示箭头')}
				checked={arrows}
				onChange={(value) => setAttributes({arrows: value})}
			/>
		</PanelBody>
	);
};
