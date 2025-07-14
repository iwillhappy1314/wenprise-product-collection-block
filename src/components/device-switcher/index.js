import { DEVICES } from '../../constants/device';

export const DeviceSwitcher = ({ selectedDevice, onChange }) => {
	const buttonStyle = {
		padding: 0,
		minWidth: '16px',
		height: '16px',
		background: 'transparent',
		border: 'none',
		cursor: 'pointer'
	};

	return (
		<div className="wprs-device-switcher" style={{ display: 'flex' }}>
			{DEVICES.map((device) => (
				<button
					key={device.name}
					style={buttonStyle}
					onClick={() => onChange(device.name)}
				>
          <span
						className={`dashicons ${device.icon}`}
						style={{
							minWidth: '16px',
							height: '16px',
							fontSize: '16px',
							color: selectedDevice === device.name ? '#007cba' : '#949494',
						}}
					/>
				</button>
			))}
		</div>
	);
};
