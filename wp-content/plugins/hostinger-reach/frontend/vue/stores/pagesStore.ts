import { defineStore } from 'pinia';
import { ref } from 'vue';

import { pagesRepo } from '@/data/repositories/pagesRepo';
import { STORE_PERSISTENT_KEYS } from '@/types/enums';
import type { WordPressPagesList } from '@/types/models/pagesModels';

export const usePagesStore = defineStore(
	'pagesStore',
	() => {
		const pages = ref<WordPressPagesList>([]);
		const isPagesLoading = ref(false);

		const loadPages = async () => {
			isPagesLoading.value = true;

			const [data, err] = await pagesRepo.getPagesWithSubscriptionBlock();
			isPagesLoading.value = false;

			if (err) {
				return;
			}

			pages.value = data ?? [];
		};

		return {
			pages,
			isPagesLoading,
			loadPages
		};
	},
	{
		persist: { key: STORE_PERSISTENT_KEYS.PAGES_STORE }
	}
);
