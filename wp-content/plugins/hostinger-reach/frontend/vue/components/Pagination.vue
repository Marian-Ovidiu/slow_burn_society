<script setup lang="ts">
import { HButton, HIcon } from '@hostinger/hcomponents';
import { computed } from 'vue';

interface Props {
	currentPage: number;
	totalItems: number;
	itemsPerPage: number;
	totalVisible?: number;
	disabled?: boolean;
}

const props = withDefaults(defineProps<Props>(), {
	totalVisible: 5,
	disabled: false
});

const emit = defineEmits<{
	'page-change': [page: number];
	'update:current-page': [page: number];
}>();

const totalPages = computed(() => Math.ceil(props.totalItems / props.itemsPerPage));

const visiblePages = computed(() => {
	const pages: (number | string)[] = [];
	const current = props.currentPage;
	const total = totalPages.value;
	const maxVisible = props.totalVisible;

	if (total <= maxVisible) {
		for (let i = 1; i <= total; i++) {
			pages.push(i);
		}

		return pages;
	}

	pages.push(1);

	const sidePages = Math.floor((maxVisible - 3) / 2);
	const leftBound = Math.max(2, current - sidePages);
	const rightBound = Math.min(total - 1, current + sidePages);

	if (leftBound > 2) {
		pages.push('...');
	}

	for (let i = leftBound; i <= rightBound; i++) {
		if (i !== 1 && i !== total) {
			pages.push(i);
		}
	}

	if (rightBound < total - 1) {
		pages.push('...');
	}

	if (total > 1) {
		pages.push(total);
	}

	return pages;
});

const canGoPrevious = computed(() => props.currentPage > 1 && !props.disabled);
const canGoNext = computed(() => props.currentPage < totalPages.value && !props.disabled);

const goToPage = (page: number) => {
	if (props.disabled || page < 1 || page > totalPages.value || page === props.currentPage) {
		return;
	}

	emit('page-change', page);
	emit('update:current-page', page);
};

const goToPrevious = () => {
	if (canGoPrevious.value) {
		goToPage(props.currentPage - 1);
	}
};

const goToNext = () => {
	if (canGoNext.value) {
		goToPage(props.currentPage + 1);
	}
};

const handleKeydown = (event: KeyboardEvent, page: number) => {
	if (event.key === 'Enter' || event.key === ' ') {
		event.preventDefault();
		goToPage(page);
	}
};
</script>

<template>
	<nav
		v-if="totalPages > 1"
		class="pagination"
		role="navigation"
		:aria-label="`Pagination, page ${currentPage} of ${totalPages}`"
	>
		<HButton
			variant="text"
			color="neutral"
			size="small"
			:is-disabled="!canGoPrevious"
			:aria-label="`Go to previous page, page ${currentPage - 1}`"
			:tabindex="canGoPrevious ? 0 : -1"
			@click="goToPrevious"
		>
			<HIcon name="ic-chevron-small-left-16" />
		</HButton>

		<template v-for="(page, index) in visiblePages" :key="`page-${page}-${index}`">
			<span v-if="page === '...'" class="pagination-ellipsis" aria-hidden="true">...</span>
			<HButton
				v-else
				variant="text"
				size="small"
				:color="page === currentPage ? 'primary' : 'neutral'"
				:is-disabled="disabled"
				:aria-label="page === currentPage ? `Current page, page ${page}` : `Go to page ${page}`"
				:aria-current="page === currentPage ? 'page' : undefined"
				:tabindex="disabled ? -1 : 0"
				@click="goToPage(page as number)"
				@keydown="handleKeydown($event, page as number)"
			>
				{{ page }}
			</HButton>
		</template>

		<HButton
			variant="text"
			color="neutral"
			size="small"
			:is-disabled="!canGoNext"
			:aria-label="`Go to next page, page ${currentPage + 1}`"
			:tabindex="canGoNext ? 0 : -1"
			@click="goToNext"
		>
			<HIcon name="ic-chevron-small-right-16" />
		</HButton>
	</nav>
</template>

<style scoped>
.pagination {
	display: flex;
	align-items: center;
	gap: 8px;
}

.pagination-ellipsis {
	display: flex;
	align-items: center;
	justify-content: center;
	width: 48px;
	height: 48px;
	color: var(--neutral--700);
	font-size: 14px;
	font-weight: 500;
}
</style>
