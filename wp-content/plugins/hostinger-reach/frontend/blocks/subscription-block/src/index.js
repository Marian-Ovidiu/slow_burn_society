import {registerBlockType} from '@wordpress/blocks';
import {__} from '@wordpress/i18n';
import Edit from './edit';
import './styles.scss'

registerBlockType('hostinger-reach/subscription',
	{
		name: "hostinger-reach/subscription",
		title: __("Reach Subscription Form", "hostinger-reach"),
		icon:
			<svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
				<path
					d="M13.925 7.35306L18.0634 3.21429L20.7856 5.9365L18.5036 8.21843H15.8008L15.7823 8.20957L10.0747 5.67124V1.5H13.925V7.35306Z"
					fill="#1D1E20"/>
				<path
					d="M5.49586 15.7815L8.21638 15.7895L13.9252 18.3287V22.4999H10.0749L10.0736 16.649L5.93656 20.7856L3.21436 18.0634L5.49586 15.7815Z"
					fill="#1D1E20"/>
				<path
					d="M22.5003 10.0757H18.3282L15.7827 15.7997V18.505L18.0638 20.7861L20.786 18.0635L16.6472 13.9251L22.5003 13.9243V10.0757Z"
					fill="#1D1E20"/>
				<path
					d="M5.9365 3.21436L8.21632 5.49418V8.20415L5.67208 13.9244H1.5V10.0758H7.35306L3.21429 5.93741L5.9365 3.21436Z"
					fill="#1D1E20"/>
			</svg>,
		category: "common",
		textdomain: "hostinger-reach",
		attributes: {
			formId: {
				type: 'string',
				default: '',
			},
			showName: {
				type: 'boolean',
				default: false,
			},
			showSurname: {
				type: 'boolean',
				default: false,
			},
			contactList: {
				type: 'string',
				default: '',
			},
			layout: {
				type: 'string',
				default: 'default',
				enum: ['default', 'inline']
			}
		},
		edit: Edit,
		save: () => {
			return null;
		}
	}
);


