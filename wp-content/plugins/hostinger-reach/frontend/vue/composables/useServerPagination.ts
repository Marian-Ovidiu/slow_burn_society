import { computed, ref } from 'vue';

import type { PaginatedResponse } from '@/data/repositories/pagesRepo';

export interface ServerPaginationOptions {
	itemsPerPage?: number;
	initialPage?: number;
	autoLoad?: boolean;
}

export interface ServerPaginationState {
	currentPage: number;
	totalPages: number;
	totalItems: number;
	itemsPerPage: number;
	isLoading: boolean;
	hasError: boolean;
	error: unknown;
}

export const useServerPagination = <T>(
	fetchFunction: (page: number, perPage: number) => Promise<[PaginatedResponse<T[]> | null, unknown]>,
	options: ServerPaginationOptions = {}
) => {
	const { itemsPerPage = 5, initialPage = 1, autoLoad = true } = options;

	const currentPage = ref(initialPage);
	const totalPages = ref(0);
	const totalItems = ref(0);
	const items = ref<T[]>([]);
	const isLoading = ref(false);
	const hasError = ref(false);
	const error = ref<unknown>(null);

	const hasPrevious = computed(() => currentPage.value > 1);
	const hasNext = computed(() => currentPage.value < totalPages.value);
	const isEmpty = computed(() => !isLoading.value && items.value.length === 0);
	const startIndex = computed(() => (currentPage.value - 1) * itemsPerPage + 1);
	const endIndex = computed(() => Math.min(currentPage.value * itemsPerPage, totalItems.value));

	const pageRange = computed(() => ({
		from: startIndex.value,
		to: endIndex.value,
		total: totalItems.value
	}));

	const loadData = async (page: number = currentPage.value) => {
		isLoading.value = true;
		hasError.value = false;
		error.value = null;

		try {
			const [result, err] = await fetchFunction(page, itemsPerPage);

			if (err) {
				hasError.value = true;
				error.value = err;
				items.value = [];

				return false;
			}

			if (!result) {
				return false;
			}

			items.value = result.data;
			currentPage.value = result.pagination.currentPage;
			totalPages.value = result.pagination.totalPages;
			totalItems.value = result.pagination.totalItems;

			return true;
		} catch (err) {
			hasError.value = true;
			error.value = err;
			items.value = [];

			return false;
		} finally {
			isLoading.value = false;
		}
	};

	const goToPage = async (page: number) => {
		if (page >= 1 && page <= totalPages.value && page !== currentPage.value && !isLoading.value) {
			await loadData(page);
		}
	};

	const goToFirstPage = async () => {
		await goToPage(1);
	};

	const goToLastPage = async () => {
		await goToPage(totalPages.value);
	};

	const goToPreviousPage = async () => {
		if (hasPrevious.value) {
			await goToPage(currentPage.value - 1);
		}
	};

	const goToNextPage = async () => {
		if (hasNext.value) {
			await goToPage(currentPage.value + 1);
		}
	};

	const setPage = (page: number) => goToPage(page);
	const nextPage = () => goToNextPage();
	const prevPage = () => goToPreviousPage();

	const refresh = async () => {
		await loadData(currentPage.value);
	};

	if (autoLoad) {
		loadData(initialPage);
	}

	return {
		currentPage,
		totalPages,
		totalItems,
		itemsPerPage,
		items,
		isLoading,
		hasError,
		error,
		hasPrevious,
		hasNext,
		isEmpty,
		startIndex,
		endIndex,
		pageRange,
		loadData,
		goToPage,
		setPage,
		nextPage,
		prevPage,
		goToFirstPage,
		goToLastPage,
		goToPreviousPage,
		goToNextPage,
		refresh
	};
};
