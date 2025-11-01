import { defineStore } from 'pinia';
import { computed, ref } from 'vue';

import { STORE_PERSISTENT_KEYS } from '@/types/enums';
import type { ReachData } from '@/types/models';
import { snakeToCamelObj } from '@/utils/caseConversion';

export const useGeneralDataStore = defineStore(
	'generalDataStore',
	() => {
		const data = ref<ReachData>(snakeToCamelObj(hostinger_reach_reach_data) as ReachData);
		const totalFormPages = computed(() => data.value.totalFormPages);
		const siteUrl = computed(() => data.value.siteUrl);
		const isHostingerUser = computed(() => data.value.isHostingerUser);
		const ajaxUrl = computed(() => data.value.ajaxUrl);
		const nonce = computed(() => data.value.nonce);
		const pluginUrl = computed(() => data.value.pluginUrl);
		const translations = computed(() => data.value.translations);
		const isConnected = computed(() => data.value.isConnected);
		const isStaging = computed(() => data.value.isStaging);

		return {
			data,
			siteUrl,
			ajaxUrl,
			nonce,
			isConnected,
			isHostingerUser,
			totalFormPages,
			isStaging,
			pluginUrl,
			translations
		};
	},
	{
		persist: { key: STORE_PERSISTENT_KEYS.GENERAL_DATA_STORE }
	}
);
