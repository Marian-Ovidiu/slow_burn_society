import { defineStore } from 'pinia';

import { useServerPagination } from '@/composables/useServerPagination';
import { pagesRepo } from '@/data/repositories/pagesRepo';
import { STORE_PERSISTENT_KEYS } from '@/types/enums';
import type { WordPressPage } from '@/types/models/pagesModels';

export const usePagesStore = defineStore(
	'pagesStore',
	() => {
		const serverPagination = useServerPagination<WordPressPage>(
			async (page: number, perPage: number) =>
				await pagesRepo.getPagesWithSubscriptionBlock({ page, perPage }, undefined),
			{
				itemsPerPage: 10,
				initialPage: 1,
				autoLoad: false
			}
		);

		const resetToFirstPage = async () => {
			await serverPagination.goToPage(1);
		};

		return {
			pages: serverPagination.items,
			isPagesLoading: serverPagination.isLoading,
			resetToFirstPage,
			...serverPagination
		};
	},
	{
		persist: { key: STORE_PERSISTENT_KEYS.PAGES_STORE }
	}
);
