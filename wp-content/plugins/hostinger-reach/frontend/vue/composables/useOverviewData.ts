import { computed, ref } from 'vue';

import { reachRepo } from '@/data/repositories/reachRepo';
import type { OverviewData } from '@/types/models/reachDataModels';
import { translate } from '@/utils/translate';

export const useOverviewData = () => {
	const isLoading = ref(true);
	const error = ref<string | null>(null);
	const overviewData = ref<OverviewData | null>(null);

	const usageCards = computed(() => [
		{
			title: translate('hostinger_reach_overview_emails_title'),
			layout: 'horizontal' as const,
			metrics: [
				{
					label: translate('hostinger_reach_overview_emails_sent_label'),
					value: overviewData.value?.totalEmailsSentThisMonth || 0
				},
				{
					label: translate('hostinger_reach_overview_emails_remaining_label'),
					value: overviewData.value?.remainingEmailsQuota || 0
				}
			]
		},
		{
			title: translate('hostinger_reach_overview_campaigns_title'),
			layout: 'horizontal' as const,
			metrics: [
				{
					label: translate('hostinger_reach_overview_campaigns_sent_label'),
					value: overviewData.value?.totalCampaignsSentThisMonth || 0
				},
				{
					label: translate('hostinger_reach_overview_campaigns_ctor_label'),
					value: `${overviewData.value?.averageClickToOpenRate || 0}%`,
					hasIcon: true,
					tooltip: translate('hostinger_reach_ui_tooltip_ctor_info')
				}
			]
		},
		{
			title: translate('hostinger_reach_overview_subscribers_title'),
			layout: 'vertical' as const,
			metrics: [
				{
					label: translate('hostinger_reach_overview_subscribers_new_label'),
					value: overviewData.value?.totalSubscribedThisMonth || 0
				},
				{
					label: translate('hostinger_reach_overview_subscribers_unsubscribes_label'),
					value: overviewData.value?.totalUnsubscribedThisMonth || 0
				},
				{
					label: translate('hostinger_reach_overview_subscribers_total_label'),
					value: overviewData.value?.totalSubscribed || 0
				}
			]
		}
	]);

	const loadOverviewData = async () => {
		isLoading.value = true;
		error.value = null;

		const [data, err] = await reachRepo.getOverview();

		isLoading.value = false;

		if (err) {
			error.value = err.message || translate('hostinger_reach_error_message');

			return;
		}

		if (data) {
			overviewData.value = data;
		}
	};

	return {
		isLoading,
		error,
		overviewData,
		usageCards,
		loadOverviewData
	};
};
