<script lang="ts" setup>
import { HButton, HIcon, HSkeletonLoader, HText } from '@hostinger/hcomponents';
import { computed, onMounted, ref } from 'vue';

import BaseModal from '@/components/Modals/Base/BaseModal.vue';
import Pagination from '@/components/Pagination.vue';
import { usePagesStore } from '@/stores/pagesStore';
import type { Page, WordPressPage } from '@/types/models/pagesModels';
import { translate } from '@/utils/translate';

interface Props {
	title?: string;
	pages?: Page[];
	data?: Record<string, unknown>;
}

const props = defineProps<Props>();

const pagesStore = usePagesStore();

const NEW_FORM_BUTTON_LINK = '/wp-admin/post-new.php?post_type=page&hostinger_reach_add_block=1';

const loadingPageId = ref<string | null>(null);
const isNewFormButtonLoading = ref(false);

const currentPages = computed(() => {
	const storePages = pagesStore.items;

	return storePages && storePages.length > 0 ? storePages : props.pages || [];
});

const getPageDisplayName = (page: Page | WordPressPage): string => {
	if ('name' in page && page.name) {
		return page.name;
	}

	if ('title' in page && page.title?.rendered) {
		return page.title.rendered;
	}

	return translate('hostinger_reach_forms_no_title');
};

const isPageAdded = (page: Page | WordPressPage): boolean => ('isAdded' in page ? page.isAdded || false : false);

const isLoading = computed(() => pagesStore.isLoading);
const currentPage = computed(() => pagesStore.currentPage);
const totalItems = computed(() => pagesStore.totalItems);
const itemsPerPage = computed(() => pagesStore.itemsPerPage);

const handlePageChange = async (page: number) => {
	await pagesStore.goToPage(page);
};

const handlePageClick = (page: Page | WordPressPage) => {
	if (loadingPageId.value) return;

	loadingPageId.value = String(page.id);

	const pageUrl = page.link;
	if (pageUrl) {
		window.location.href = pageUrl;
	}
};

const handleNewFormClick = () => {
	if (isNewFormButtonLoading.value) return;

	isNewFormButtonLoading.value = true;

	window.location.href = NEW_FORM_BUTTON_LINK;
};

const handleBackClick = () => {
	const backButtonAction = props.data?.backButtonRedirectAction as (() => void) | undefined;
	if (backButtonAction) {
		backButtonAction();
	}
};

onMounted(async () => {
	await pagesStore.resetToFirstPage();
});
</script>

<template>
	<BaseModal title-alignment="centered" :title="title">
		<template v-if="data?.backButtonRedirectAction" #back-button>
			<button class="select-page-modal__back-button" type="button" @click="handleBackClick">
				<HIcon name="ic-chevron-left-16" color="neutral--600" />
			</button>
		</template>

		<div class="select-page-modal">
			<div class="select-page-modal__content">
				<div class="select-page-modal__pages">
					<template v-if="isLoading">
						<div
							v-for="n in itemsPerPage"
							:key="`skeleton-${n}`"
							class="select-page-modal__page-item select-page-modal__page-item--loading"
						>
							<div class="select-page-modal__page-loading">
								<HSkeletonLoader width="60%" height="20px" border-radius="sm" />
							</div>
						</div>
					</template>

					<template v-else-if="currentPages && currentPages.length > 0">
						<div
							v-for="page in currentPages"
							:key="page.id"
							class="select-page-modal__page-item"
							:class="{
								'select-page-modal__page-item--selected': isPageAdded(page),
								'select-page-modal__page-item--loading': loadingPageId === String(page.id)
							}"
							@click="handlePageClick(page)"
						>
							<div v-if="loadingPageId === String(page.id)" class="select-page-modal__page-loading">
								<HSkeletonLoader width="60%" height="20px" border-radius="sm" />
							</div>
							<template v-else>
								<div class="select-page-modal__page-content">
									<HText variant="body-2-bold" as="span" class="select-page-modal__page-name">
										{{ getPageDisplayName(page) }}
									</HText>
								</div>

								<div>
									<HIcon
										:name="isPageAdded(page) ? 'ic-checkmark-circle-filled-24' : 'ic-circle-empty-24'"
										:color="isPageAdded(page) ? 'primary--500' : 'neutral--200'"
									/>
								</div>
							</template>
						</div>
					</template>

					<template v-else-if="!isLoading">
						<div class="select-page-modal__no-pages">
							<HText variant="body-2" as="p" class="select-page-modal__no-pages-text">
								{{ translate('hostinger_reach_forms_no_pages_available') }}
							</HText>
						</div>
					</template>
				</div>
			</div>

			<div class="select-page-modal__pagination">
				<Pagination
					:current-page="currentPage"
					:total-items="totalItems"
					:items-per-page="itemsPerPage"
					:total-visible="5"
					:disabled="isLoading"
					@page-change="handlePageChange"
					@update:current-page="handlePageChange"
				/>
			</div>

			<div class="select-page-modal__footer">
				<HButton
					variant="outline"
					color="neutral"
					size="small"
					:icon-prepend="isNewFormButtonLoading ? undefined : 'ic-add-16'"
					:is-loading="isNewFormButtonLoading"
					@click="handleNewFormClick"
				>
					{{ translate('hostinger_reach_forms_new_page_text') }}
				</HButton>
			</div>
		</div>
	</BaseModal>
</template>

<style lang="scss" scoped>
.select-page-modal {
	margin-top: 24px;

	&__back-button {
		position: absolute;
		top: 0;
		left: 0;
		display: flex;
		align-items: center;
		justify-content: center;
		width: 32px;
		height: 32px;
		border: none;
		background: transparent;
		border-radius: 8px;
		cursor: pointer;
		transition: background-color 0.2s ease;

		&:hover {
			background-color: var(--neutral--100);
		}

		&:active {
			background-color: var(--neutral--200);
		}
	}

	&__content {
		display: flex;
		flex-direction: column;
		gap: 20px;
		align-items: center;
		margin-bottom: 24px;
	}

	&__pages {
		display: flex;
		flex-direction: column;
		gap: 8px;
		width: 100%;
		max-width: 100%;
	}

	&__page-item {
		display: flex;
		align-items: center;
		justify-content: space-between;
		height: 60px;
		gap: 16px;
		padding: 20px;
		border: 1px solid var(--neutral--200);
		border-radius: 16px;
		background: var(--neutral--0);
		cursor: pointer;
		transition:
			border-color 0.2s ease,
			opacity 0.2s ease;

		&:hover:not(&--loading) {
			border-color: var(--primary--500);
		}

		&--selected {
			border-color: var(--primary--500);
		}

		&--loading {
			cursor: not-allowed;
			opacity: 0.7;
			border-color: var(--neutral--300);
		}
	}

	&__page-loading {
		display: flex;
		align-items: center;
		width: 100%;
		gap: 12px;
	}

	&__page-content {
		display: flex;
		align-items: center;
		gap: 12px;
		flex: 1;
	}

	&__page-name {
		font-weight: 700;
	}

	&__checkmark {
		width: 20px;
		height: 20px;
		display: flex;
		align-items: center;
		justify-content: center;
		flex-shrink: 0;
	}

	&__no-pages {
		display: flex;
		flex-direction: column;
		align-items: center;
		justify-content: center;
		gap: 12px;
		padding: 40px 20px;
		text-align: center;
	}

	&__no-pages-icon {
		width: 32px;
		height: 32px;
		color: var(--neutral--400);
	}

	&__no-pages-text {
		color: var(--neutral--500);
		margin: 0;
	}

	&__pagination {
		display: flex;
		justify-content: center;
		align-items: center;
		padding: 16px 0;
		margin-bottom: 8px;
	}

	&__footer {
		display: flex;
		justify-content: flex-end;
		gap: 8px;
	}

	@media (max-width: 640px) {
		&__page-item {
			padding: 16px;
		}

		&__footer {
			flex-direction: column-reverse;
			gap: 12px;

			:deep(.h-button) {
				width: 100%;
			}
		}
	}

	@media (max-width: 480px) {
		&__pages {
			gap: 6px;
		}

		&__page-item {
			padding: 14px;
		}
	}
}
</style>
