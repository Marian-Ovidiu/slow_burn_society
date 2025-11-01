<script lang="ts" setup>
import { HLabel } from '@hostinger/hcomponents';
import { computed, ref } from 'vue';

import BaseModal from '@/components/Modals/Base/BaseModal.vue';
import PluginIntegrationSkeleton from '@/components/skeletons/PluginIntegrationSkeleton.vue';
import PluginsSectionSkeleton from '@/components/skeletons/PluginsSectionSkeleton.vue';
import { useModal } from '@/composables/useModal';
import { getPluginInfo } from '@/data/pluginData';
import { useIntegrationsStore } from '@/stores/integrationsStore';
import { usePagesStore } from '@/stores/pagesStore';
import { ModalName } from '@/types/enums';
import { translate } from '@/utils/translate';

const { openModal } = useModal();

const integrationsStore = useIntegrationsStore();
const pagesStore = usePagesStore();
const showPlugins = ref(false);

const installedIntegrations = computed(() =>
	integrationsStore.availableIntegrations.filter((integration) => integration.isActive)
);

const availableIntegrations = computed(() =>
	integrationsStore.availableIntegrations.filter((integration) => !integration.isActive)
);

const handleCreateForm = () => {
	openModal(
		ModalName.SELECT_PAGE_MODAL,
		{
			title: translate('hostinger_reach_forms_modal_title'),
			data: {
				backButtonRedirectAction: () => {
					openModal(ModalName.ADD_FORM_MODAL, {}, { hasCloseButton: true });
				}
			},
			pages: pagesStore.pages
		},
		{
			hasCloseButton: true
		}
	);
};

const handleConnect = async (integrationId: string) => {
	await integrationsStore.toggleIntegrationStatus(integrationId, true);
};

const handleDisconnect = (id: string) => {
	openModal(ModalName.CONFIRM_DISCONNECT_MODAL, {
		data: {
			integration: id,
			backButtonRedirectAction: () => {
				openModal(ModalName.ADD_FORM_MODAL, {}, { hasCloseButton: true });
			}
		}
	});
};

const togglePluginsSection = () => {
	showPlugins.value = !showPlugins.value;
};

pagesStore.loadData();
</script>

<template>
	<BaseModal title-alignment="left" :title="translate('hostinger_reach_add_form_modal_title')">
		<div class="add-form-modal">
			<HSnackbar variant="info">
				{{ translate('hostinger_reach_forms_consent_notice') }}
			</HSnackbar>

			<div class="add-form-modal__main-card">
				<div class="add-form-modal__main-content">
					<div class="add-form-modal__main-icon">
						<HIcon name="ic-add-24" color="primary--500" />
					</div>
					<HText variant="body-1-bold" as="span">
						{{ translate('hostinger_reach_forms_new_contact_form') }}
					</HText>
				</div>
				<HButton
					:is-loading="pagesStore.isPagesLoading"
					variant="contain"
					color="primary"
					size="small"
					@click="handleCreateForm"
				>
					{{ translate('hostinger_reach_forms_create_form_button') }}
				</HButton>
			</div>

			<PluginsSectionSkeleton v-if="integrationsStore.isLoading" />

			<template v-else>
				<div v-if="installedIntegrations.length" class="add-form-modal__installed-section">
					<HText variant="body-3" as="h3" color="neutral--300">
						{{ translate('hostinger_reach_forms_installed_plugins') }}
					</HText>
					<div class="add-form-modal__plugins-list">
						<div v-for="integration in installedIntegrations" :key="integration.id" class="add-form-modal__plugin-card">
							<PluginIntegrationSkeleton v-if="integrationsStore.loadingIntegrations[integration.id]" />

							<div v-else class="add-form-modal__plugin-content">
								<div class="add-form-modal__plugin-info">
									<div class="add-form-modal__plugin-icon">
										<img
											:src="getPluginInfo(integration).icon"
											:alt="integration.title"
											class="add-form-modal__plugin-image"
											loading="lazy"
										/>
									</div>
									<div class="add-form-modal__plugin-details">
										<HText variant="body-2-bold" as="span">
											{{ integration.title }}
										</HText>

										<HLabel color="success" variant="outline">
											{{ translate('hostinger_reach_forms_active') }}
										</HLabel>
									</div>
								</div>
								<div class="add-form-modal__plugin-actions">
									<HButton
										variant="outline"
										color="danger"
										size="small"
										:is-disabled="integrationsStore.isIntegrationLoading(integration.id)"
										@click="handleDisconnect(integration.id)"
									>
										{{ translate('hostinger_reach_forms_disconnect') }}
									</HButton>
								</div>
							</div>
						</div>
					</div>
				</div>

				<div v-if="availableIntegrations.length" class="add-form-modal__plugins-section">
					<HButton
						variant="text"
						color="primary"
						size="small"
						:icon-append="showPlugins ? 'ic-chevron-up-16' : 'ic-chevron-down-16'"
						icon-color="primary--500"
						class="add-form-modal__plugins-toggle"
						:aria-expanded="showPlugins"
						@click="togglePluginsSection"
					>
						{{ translate('hostinger_reach_forms_view_supported_plugins') }}
					</HButton>

					<div v-if="showPlugins" class="add-form-modal__plugins-list">
						<div v-for="integration in availableIntegrations" :key="integration.id" class="add-form-modal__plugin-card">
							<PluginIntegrationSkeleton v-if="integrationsStore.isIntegrationLoading(integration.id)" />

							<div v-else class="add-form-modal__plugin-content">
								<div class="add-form-modal__plugin-info">
									<div class="add-form-modal__plugin-icon">
										<img
											:src="getPluginInfo(integration).icon"
											:alt="integration.title"
											class="add-form-modal__plugin-image"
											loading="lazy"
										/>
									</div>
									<div class="add-form-modal__plugin-details">
										<HText variant="body-2-bold" as="span">
											{{ integration.title }}
										</HText>
									</div>
								</div>
								<div class="add-form-modal__plugin-actions">
									<HButton
										variant="outline"
										color="neutral"
										size="small"
										:is-disabled="integrationsStore.isIntegrationLoading(integration.id)"
										@click="handleConnect(integration.id)"
									>
										{{
											integration.isPluginActive
												? translate('hostinger_reach_forms_connect')
												: translate('hostinger_reach_forms_install_and_connect')
										}}
									</HButton>
								</div>
							</div>
						</div>
					</div>
				</div>
			</template>
		</div>
	</BaseModal>
</template>

<style lang="scss" scoped>
.add-form-modal {
	display: flex;
	flex-direction: column;
	gap: 20px;
	margin-top: 24px;

	&__main-card {
		display: flex;
		align-items: center;
		justify-content: space-between;
		padding: 20px;
		background: var(--neutral--0);
		border: 1px solid var(--primary--500);
		border-radius: 12px;
		transition:
			border-color 0.2s ease,
			box-shadow 0.2s ease;

		&:hover {
			border-color: var(--primary--600);
			box-shadow: 0 0 0 3px rgba(124, 58, 237, 0.1);
		}
	}

	&__main-content {
		display: flex;
		align-items: center;
		gap: 12px;
	}

	&__main-icon {
		width: 32px;
		height: 32px;
		flex-shrink: 0;
		background: var(--primary--50);
		border-radius: 8px;
		display: flex;
		align-items: center;
		justify-content: center;
	}

	&__plugins-section {
		display: flex;
		flex-direction: column;
		gap: 16px;
	}

	&__plugins-toggle {
		align-self: center;
	}

	&__plugins-list {
		display: flex;
		flex-direction: column;
		gap: 12px;
	}

	&__plugin-card {
		display: flex;
		flex-direction: column;
		background: var(--neutral--0);
		border: 1px solid var(--neutral--200);
		border-radius: 12px;
		transition: border-color 0.2s ease;
		contain: layout style;

		&:hover {
			border-color: var(--neutral--300);
		}
	}

	&__plugin-content {
		display: flex;
		align-items: center;
		justify-content: space-between;
		padding: 16px;
		width: 100%;
	}

	&__plugin-info {
		display: flex;
		align-items: center;
		gap: 12px;
	}

	&__plugin-icon {
		width: 40px;
		height: 40px;
		border-radius: 8px;
		display: flex;
		align-items: center;
		justify-content: center;
		background: var(--neutral--50);
		flex-shrink: 0;
	}

	&__plugin-image {
		width: 32px;
		height: 32px;
		object-fit: contain;
	}

	&__plugin-details {
		display: flex;
		gap: 8px;
		align-items: center;
	}

	&__plugin-actions {
		display: flex;
		align-items: center;
		gap: 12px;
	}

	&__installed-section {
		display: flex;
		flex-direction: column;
		gap: 8px;
	}

	@media (max-width: 640px) {
		gap: 16px;

		&__main-card {
			padding: 16px;
			flex-direction: column;
			align-items: stretch;
			gap: 16px;
		}

		&__main-content {
			justify-content: center;
		}

		&__plugin-content {
			padding: 14px;
		}

		&__plugin-icon {
			width: 36px;
			height: 36px;
		}

		&__plugin-image {
			width: 28px;
			height: 28px;
		}
	}

	@media (max-width: 480px) {
		gap: 12px;

		&__main-card {
			padding: 14px;
		}

		&__plugins-list {
			gap: 8px;
		}

		&__plugin-content {
			padding: 12px;
		}
	}
}
</style>
