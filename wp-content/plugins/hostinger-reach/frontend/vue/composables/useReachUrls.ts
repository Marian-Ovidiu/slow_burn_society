import { computed } from 'vue';

import { useGeneralDataStore } from '@/stores/generalDataStore';

export const useReachUrls = () => {
	const generalStore = useGeneralDataStore();

	const reachBaseDomain = computed(() => (generalStore.isStaging ? 'reach.hostinger.dev' : 'reach.hostinger.com'));
	const hpanelBaseDomain = computed(() => (generalStore.isStaging ? 'hpanel.hostinger.dev' : 'hpanel.hostinger.com'));

	return {
		reachUpgradeLink: computed(() => `https://${hpanelBaseDomain.value}/reach`),
		reachYourPlanLink: computed(() => `https://${reachBaseDomain.value}/settings?tab=your-plan`),
		reachCampaignsLink: computed(() => `https://${reachBaseDomain.value}/campaigns`),
		reachTemplatesLink: computed(() => `https://${reachBaseDomain.value}/templates`),
		reachSettingsLink: computed(() => `https://${reachBaseDomain.value}/settings`),
		reachBaseDomain,
		hpanelBaseDomain
	};
};
