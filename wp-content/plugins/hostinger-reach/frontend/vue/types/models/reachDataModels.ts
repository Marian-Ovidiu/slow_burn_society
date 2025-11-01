export interface ReachData {
	siteUrl: string;
	ajaxUrl: string;
	nonce: string;
	pluginUrl: string;
	translations: Record<string, string>;
	isConnected: boolean;
	totalFormPages: number;
	isHostingerUser: boolean;
	isStaging: boolean;
}

export interface HstReachDataRaw {
	site_url: string;
	rest_base_url: string;
	ajax_url: string;
	nonce: string;
	plugin_url: string;
	translations: Record<string, string>;
	is_connected: boolean;
	is_hostinger_user: boolean;
	is_staging: boolean;
	total_form_pages: number;
}

export interface OverviewData {
	totalEmailsSentThisMonth: number;
	remainingEmailsQuota: number;
	totalCampaignsSentThisMonth: number;
	averageClickToOpenRate: number;
	totalSubscribed: number;
	totalSubscribedThisMonth: number;
	totalUnsubscribedThisMonth: number;
}
