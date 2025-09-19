<script lang="ts" setup>
import { HSkeletonLoader } from '@hostinger/hcomponents';
import { ref } from 'vue';

import BaseModal from '@/components/Modals/Base/BaseModal.vue';
import type { Page } from '@/types/models/pagesModels';
import { translate } from '@/utils/translate';

interface Props {
	title?: string;
	pages?: Page[];
	data?: Record<string, unknown>;
}

const props = defineProps<Props>();

const NEW_FORM_BUTTON_LINK = '/wp-admin/post-new.php?post_type=page&hostinger_reach_add_block=1';

const loadingPageId = ref<string | null>(null);
const isNewFormButtonLoading = ref(false);

const handlePageClick = (page: Page) => {
	if (loadingPageId.value) return;

	loadingPageId.value = page.id;

	window.location.href = page.link;
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
				<div v-if="pages && pages.length > 0" class="select-page-modal__pages">
					<div
						v-for="page in pages"
						:key="page.id"
						class="select-page-modal__page-item"
						:class="{
							'select-page-modal__page-item--selected': page.isAdded,
							'select-page-modal__page-item--loading': loadingPageId === page.id
						}"
						@click="handlePageClick(page)"
					>
						<div v-if="loadingPageId === page.id" class="select-page-modal__page-loading">
							<HSkeletonLoader width="60%" height="20px" border-radius="sm" />
						</div>
						<template v-else>
							<div class="select-page-modal__page-content">
								<HText variant="body-2-bold" as="span" class="select-page-modal__page-name">
									{{ page.name || translate('hostinger_reach_forms_no_title') }}
								</HText>
							</div>

							<div>
								<HIcon
									:name="page.isAdded ? 'ic-checkmark-circle-filled-24' : 'ic-circle-empty-24'"
									:color="page.isAdded ? 'primary--500' : 'neutral--200'"
								/>
							</div>
						</template>
					</div>
				</div>
				<div v-else class="select-page-modal__no-pages">
					<HText variant="body-2" as="p" class="select-page-modal__no-pages-text">
						{{ translate('hostinger_reach_forms_no_pages_available') }}
					</HText>
				</div>
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
		max-height: 400px;
		overflow-y: auto;
		padding-right: 4px;

		&::-webkit-scrollbar {
			width: 6px;
		}

		&::-webkit-scrollbar-track {
			background: var(--neutral--100);
			border-radius: 3px;
		}

		&::-webkit-scrollbar-thumb {
			background: var(--neutral--300);
			border-radius: 3px;

			&:hover {
				background: var(--neutral--400);
			}
		}

		scrollbar-width: thin;
		scrollbar-color: var(--neutral--300) var(--neutral--100);
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
		font-family: 'DM Sans', sans-serif;
		font-weight: 700;
		font-size: 14px;
		line-height: 20px;
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

	&__footer {
		display: flex;
		justify-content: flex-end;
		gap: 8px;
	}

	@media (max-width: 640px) {
		&__pages {
			max-height: 300px;
		}

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
			max-height: 250px;
		}

		&__page-item {
			padding: 14px;
		}
	}
}
</style>
