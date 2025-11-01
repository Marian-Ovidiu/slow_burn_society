import {__} from '@wordpress/i18n';
import { useEffect } from '@wordpress/element';
import './Dialog.scss'
import reachLogo from '../reach-logo.svg';

const SIDEBAR_SELECTORS = [
	'.interface-navigable-region.interface-interface-skeleton__secondary-sidebar',
	'.spectra-ee-quick-access-container .spectra-ee-quick-access__sidebar'
];

const Dialog = ( { onClose } ) => {

	useEffect(() => {
		const sidebars = SIDEBAR_SELECTORS
			.map(selector => document.querySelector(selector))
			.filter(Boolean);

		sidebars.forEach(sidebar => {
			sidebar.style.zIndex = '0';
		});

		const timeoutId = setTimeout(() => {
			onClose();
		}, 10000);

		return () => {
			sidebars.forEach(sidebar => {
				sidebar.style.zIndex = '';
			});

			clearTimeout(timeoutId);
		};
	}, []);

	return <div id="hostinger-reach-block-dialog" className="hostinger-reach-block-dialog">
		<div className="hostinger-reach-block-dialog__close" onClick={onClose}></div>
		<div className="hostinger-reach-block-dialog__logo">
			<img src={reachLogo} alt={__('Hostinger Reach Logo', 'hostinger-reach')} />
		</div>
		<p>{ __('Your form is ready. Go to Reach to complete setup and create templates.', 'hostinger-reach') }</p>
		<div className="hostinger-reach-block-dialog__actions">
			<Button link="https://reach.hostinger.com/" label={__('Go to Reach', 'hostinger-reach')} />
		</div>
	</div>
}

const Button = ({ link, label }) => {
	return <div className="hostinger-reach-block-dialog__button">
		<a target="_blank" href={link}>{ label }</a>
		<div className="hostinger-reach-block-dialog__button_indicator">
			<svg width="16" height="16" viewBox="0 0 16 16" fill="none" xmlns="http://www.w3.org/2000/svg">
				<path d="M2.5 5.24792C2.5 3.72914 3.73122 2.49792 5.25 2.49792H6C6.41421 2.49792 6.75 2.83371 6.75 3.24792C6.75 3.66214 6.41421 3.99792 6 3.99792H5.25C4.55964 3.99792 4 4.55757 4 5.24792V10.686C4 11.3764 4.55964 11.936 5.25 11.936H10.7508C11.4411 11.936 12.0008 11.3764 12.0008 10.686V10.0001C12.0008 9.58591 12.3366 9.25012 12.7508 9.25012C13.165 9.25012 13.5008 9.58591 13.5008 10.0001V10.686C13.5008 12.2048 12.2696 13.436 10.7508 13.436H5.25C3.73122 13.436 2.5 12.2048 2.5 10.686V5.24792Z" fill="#1D1E20"/>
				<path d="M12 5.06089L8.03033 9.03056C7.73744 9.32345 7.26256 9.32345 6.96967 9.03056C6.67678 8.73766 6.67678 8.26279 6.96967 7.9699L10.9393 4.00023H9C8.58579 4.00023 8.25 3.66444 8.25 3.25023C8.25 2.83601 8.58579 2.50023 9 2.50023L12.25 2.50023C12.9404 2.50023 13.5 3.05987 13.5 3.75023V7.00023C13.5 7.41444 13.1642 7.75023 12.75 7.75023C12.3358 7.75023 12 7.41444 12 7.00023V5.06089Z" fill="#1D1E20"/>
			</svg>
		</div>
	</div>
}


export default Dialog;
