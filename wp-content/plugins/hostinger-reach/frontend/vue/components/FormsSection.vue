<script setup lang="ts">
import { computed } from 'vue';

import reachOverviewBannerBackground from '@/assets/images/backgrounds/overview-banner-background.png';
import Banner from '@/components/Banner.vue';
import PluginEntriesTable from '@/components/PluginEntriesTable.vue';
import { useModal } from '@/composables/useModal';
import { useIntegrationsStore } from '@/stores/integrationsStore';
import { ModalName } from '@/types/enums';
import type { Form } from '@/types/models';
import { translate } from '@/utils/translate';

const emit = defineEmits<{
	goToPlugin: [id: string];
	disconnectPlugin: [id: string];
	toggleFormStatus: [form: Form, status: boolean];
	viewForm: [form: Form];
	editForm: [form: Form];
	addForm: [id: string];
}>();

const { openModal } = useModal();
const integrationsStore = useIntegrationsStore();

const handleAddForm = () => {
	openModal(ModalName.ADD_FORM_MODAL, {}, { hasCloseButton: true });
};

const shouldShowTable = computed(
	() => integrationsStore?.activeIntegrations?.length > 1 || integrationsStore.hasAnyForms
);
const isBannerVisible = computed(() => !integrationsStore.isLoading && !shouldShowTable.value);
const isTableVisible = computed(() => integrationsStore.isLoading || shouldShowTable.value);
const isAddButtonVisible = computed(() => !integrationsStore.isLoading && shouldShowTable.value);
</script>

<template>
	<div class="forms-section">
		<div class="forms-section__header">
			<HText as="h1" variant="heading-1">
				{{ translate('hostinger_reach_forms_title') }}
			</HText>
			<span>
				<HButton
					v-if="isAddButtonVisible"
					variant="outline"
					color="neutral"
					size="small"
					icon-prepend="ic-plus-16"
					:is-loading="integrationsStore.isLoading"
					@click="handleAddForm"
				>
					{{ translate('hostinger_reach_forms_add_more_button_text') }}
				</HButton>
			</span>
		</div>

		<Banner
			v-if="isBannerVisible"
			:title="translate('hostinger_reach_forms_card_title')"
			:description="translate('hostinger_reach_forms_card_description')"
			:button-text="translate('hostinger_reach_forms_card_button_text')"
			:on-button-click="handleAddForm"
			:is-button-loading="integrationsStore.isLoading"
			:background-image="reachOverviewBannerBackground"
		/>

		<PluginEntriesTable
			v-if="isTableVisible"
			:integrations="integrationsStore.integrations"
			:is-loading="integrationsStore.isLoading"
			@go-to-plugin="emit('goToPlugin', $event)"
			@disconnect-plugin="emit('disconnectPlugin', $event)"
			@toggle-form-status="(form, status) => emit('toggleFormStatus', form, status)"
			@view-form="emit('viewForm', $event)"
			@edit-form="emit('editForm', $event)"
			@add-form="emit('addForm', $event)"
		/>
	</div>
</template>

<style scoped lang="scss">
.forms-section {
	display: flex;
	flex-direction: column;
	align-self: stretch;
	gap: 20px;

	&__header {
		display: flex;
		justify-content: space-between;
		align-items: center;
		align-self: stretch;
	}
}

@media (max-width: 1023px) {
	.forms-section {
		&__header {
			flex-direction: column;
			align-items: flex-start;
			gap: 12px;

			> :last-child {
				align-self: stretch;
				justify-content: flex-end;
			}
		}
	}
}
</style>
