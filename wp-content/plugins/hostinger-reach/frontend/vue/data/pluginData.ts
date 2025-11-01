import type { Integration } from '@/types/models';

export const HOSTINGER_REACH_ID = 'hostinger-reach';

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

export const getPluginInfo = (integration: Integration): PluginInfo => ({
	id: integration.id,
	icon: integration?.icon || '',
	isViewFormHidden: integration?.isViewFormHidden || true,
	isEditFormHidden: integration?.isEditFormHidden || false
});
