import contactForm7PluginIcon from '@/assets/images/icons/contact-form-7-plugin.svg';
import elementorPluginIcon from '@/assets/images/icons/elementor-plugin.svg';
import emailReachPluginIcon from '@/assets/images/icons/email-reach-plugin.svg';
import wooCommercePluginIcon from '@/assets/images/icons/woocommerce-plugin.svg';
import wpFormsLitePluginIcon from '@/assets/images/icons/wp-forms-lite-plugin.svg';
import type { Integration } from '@/types/models';

export const PLUGIN_IDS = {
	HOSTINGER_REACH: 'hostinger-reach',
	CONTACT_FORM_7: 'contact-form-7',
	WP_FORMS_LITE: 'wp-forms-lite',
	ELEMENTOR: 'elementor',
	WOOCOMMERCE: 'woocommerce'
} as const;

export const INTEGRATION_TO_FORM_TYPE_MAP: Record<string, string> = {
	hostingerReach: 'hostinger-reach',
	'contactForm-7': 'contact-form-7',
	wpformsLite: 'wpforms-lite',
	elementor: 'elementor',
	woocommerce: 'woocommerce'
} as const;

export const PLUGIN_STATUSES = {
	ACTIVE: 'active',
	INACTIVE: 'inactive'
} as const;

export type PluginStatus = (typeof PLUGIN_STATUSES)[keyof typeof PLUGIN_STATUSES];

export interface PluginInfo {
	id: string;
	icon: string;
	isViewFormHidden: boolean;
	isEditFormHidden: boolean;
}

export const DEFAULT_PLUGIN_DATA: Record<string, PluginInfo> = {
	hostingerReach: {
		id: 'hostingerReach',
		icon: emailReachPluginIcon,
		isViewFormHidden: false,
		isEditFormHidden: false
	},
	'contactForm-7': {
		id: 'contactForm-7',
		icon: contactForm7PluginIcon,
		isViewFormHidden: true,
		isEditFormHidden: false
	},
	wpformsLite: {
		id: 'wpformsLite',
		icon: wpFormsLitePluginIcon,
		isViewFormHidden: true,
		isEditFormHidden: false
	},
	elementor: {
		id: 'elementor',
		icon: elementorPluginIcon,
		isViewFormHidden: false,
		isEditFormHidden: false
	},
	woocommerce: {
		id: 'woocommerce',
		icon: wooCommercePluginIcon,
		isViewFormHidden: true,
		isEditFormHidden: true
	}
} as const;

export const PLUGIN_DATA = DEFAULT_PLUGIN_DATA;

export const getPluginInfo = (integration: Integration): PluginInfo => {
	const defaultInfo = DEFAULT_PLUGIN_DATA[integration.id];

	return {
		id: integration.id,
		icon: defaultInfo?.icon || '',
		isViewFormHidden: defaultInfo?.isViewFormHidden || true,
		isEditFormHidden: defaultInfo?.isEditFormHidden || false
	};
};
