import { useState, useEffect } from '@wordpress/element';
import { useViewportMatch } from '@wordpress/compose';
import { dispatch, subscribe, select } from '@wordpress/data';
import { DEVICE_MAP } from '../constants/device';

export const useResponsive = () => {
	const [device, setDevice] = useState('desktop');
	const isTablet = useViewportMatch('medium', '<');
	const isMobile = useViewportMatch('small', '<');

	useEffect(() => {
		if (isMobile) setDevice('mobile');
		else if (isTablet) setDevice('tablet');
		else setDevice('desktop');
	}, [isTablet, isMobile]);

	useEffect(() => {
		const unsubscribe = subscribe(() => {
			try {
				const editPost = select('core/edit-post');
				if (editPost && typeof editPost.__experimentalGetPreviewDeviceType === 'function') {
					const currentDeviceType = editPost.__experimentalGetPreviewDeviceType();
					if (currentDeviceType && DEVICE_MAP[currentDeviceType]) {
						setDevice(DEVICE_MAP[currentDeviceType]);
					}
				}
			} catch (error) {
				console.error('预览设备类型获取失败:', error);
			}
		});
		return () => unsubscribe();
	}, []);

	const handleDeviceChange = (newDevice) => {
		setDevice(newDevice);
		try {
			const deviceMap = { desktop: 'Desktop', tablet: 'Tablet', mobile: 'Mobile' };
			const editPost = dispatch('core/edit-post');
			if (editPost && typeof editPost.__experimentalSetPreviewDeviceType === 'function') {
				editPost.__experimentalSetPreviewDeviceType(deviceMap[newDevice]);
			}
		} catch (error) {
			console.error('设置预览设备类型失败:', error);
		}
	};

	return { device, handleDeviceChange };
};
