<script lang="ts" setup>
import BaseModal from '@/components/Modals/Base/BaseModal.vue';
import { useModal } from '@/composables';
import { useIntegrationsStore } from '@/stores';
import { translate } from '@/utils/translate';

interface Props {
	data?: { integration: string; backButtonRedirectAction: (() => void) | undefined };
}

const { closeModal } = useModal();
const integrationsStore = useIntegrationsStore();
const props = defineProps<Props>();

const dismiss = () => {
	const backButtonAction = props.data?.backButtonRedirectAction;
	if (backButtonAction) {
		backButtonAction();
	} else {
		closeModal();
	}
};
const handleDisconnect = async (integrationId: string) => {
	await integrationsStore.toggleIntegrationStatus(integrationId, false);
	dismiss();
};
</script>

<template>
	<BaseModal title-alignment="left" :title="translate('hostinger_reach_confirm_disconnect_modal_title')">
		<template #title-icon>
			<HIcon class="confirm-disconnect-modal__icon" name="ic-warning-circle-filled-24" color="danger--600" />
		</template>

		<div class="confirm-disconnect-modal">
			<div class="confirm-disconnect-modal__content">
				<HText variant="body-2" as="p" class="confirm-disconnect-modal__text">
					{{ translate('hostinger_reach_confirm_disconnect_modal_text') }}
				</HText>
			</div>

			<div class="confirm-disconnect-modal__footer">
				<HButton variant="text" color="neutral" size="small" @click="dismiss">
					{{ translate('hostinger_reach_confirm_disconnect_modal_cancel') }}
				</HButton>
				<HButton
					color="danger"
					size="small"
					:is-disabled="integrationsStore.isIntegrationLoading(props.data?.integration)"
					@click="handleDisconnect(props.data?.integration)"
				>
					{{ translate('hostinger_reach_confirm_disconnect_modal_disconnect') }}
				</HButton>
			</div>
		</div>
	</BaseModal>
</template>

<style lang="scss" scoped>
.confirm-disconnect-modal {
	margin-top: 24px;

	&__icon {
		margin-right: 12px;
	}

	&__content {
		display: flex;
		flex-direction: column;
		gap: 20px;
		align-items: center;
		margin-bottom: 24px;
	}

	&__footer {
		display: flex;
		justify-content: flex-end;
		gap: 8px;
	}

	@media (max-width: 640px) {
		&__footer {
			flex-direction: column-reverse;
			gap: 12px;

			:deep(.h-button) {
				width: 100%;
			}
		}
	}
}
</style>
