<script setup lang="ts">
import { computed, onMounted } from 'vue';

import reachLogo from '@/assets/images/icons/reach-logo.svg';
import ActionButtonsSection from '@/components/ActionButtonsSection.vue';
import FormsSection from '@/components/FormsSection.vue';
import UsageCardsSection from '@/components/UsageCardsSection.vue';
import { useModal } from '@/composables';
import { useOverviewData } from '@/composables/useOverviewData';
import { useReachUrls } from '@/composables/useReachUrls';
import { useToast } from '@/composables/useToast';
import { formsRepo } from '@/data/repositories/formsRepo';
import { useIntegrationsStore } from '@/stores/integrationsStore';
import { ModalName } from '@/types';
import type { Form } from '@/types/models';
import { translate } from '@/utils/translate';

const { isLoading, usageCards, loadOverviewData } = useOverviewData();
const { reachUpgradeLink, reachYourPlanLink, reachCampaignsLink, reachTemplatesLink, reachSettingsLink } =
	useReachUrls();
const { showError } = useToast();

const { openModal } = useModal();
const integrationsStore = useIntegrationsStore();

const actionButtons = computed(() => [
	{
		icon: 'ic-graph-arrow-up-16',
		text: translate('hostinger_reach_overview_campaigns_text'),
		url: reachCampaignsLink.value
	},
	{
		icon: 'ic-sparkles-16',
		text: translate('hostinger_reach_overview_templates_text'),
		url: reachTemplatesLink.value
	},
	{
		icon: 'ic-gear-16',
		text: translate('hostinger_reach_overview_settings_text'),
		url: reachSettingsLink.value
	}
]);

const handlePluginGoTo = (id: string) => {
	const integration = integrationsStore.integrations.find((i) => i.id === id);
	if (!integration?.adminUrl) {
		return;
	}

	window.open(integration.adminUrl, '_blank');
};

const handlePluginDisconnect = (id: string) => {
	openModal(ModalName.CONFIRM_DISCONNECT_MODAL, {
		data: { integration: id }
	});
};

const handleFormToggleStatus = async (form: Form, status: boolean) => {
	if (form.isLoading) {
		return;
	}

	const integration = integrationsStore.integrations.find((i) => i.forms?.some((f) => f.formId === form.formId));
	if (!integration || !integration.forms) {
		return;
	}

	const formIndex = integration.forms.findIndex((f) => f.formId === form.formId);
	if (formIndex !== -1) {
		integration.forms[formIndex].isLoading = true;
	}

	const [, error] = await formsRepo.toggleFormStatus(form.formId, status);

	if (formIndex !== -1) {
		integration.forms[formIndex].isLoading = false;
	}

	if (error?.response?.data?.error) {
		showError(error?.response?.data?.error);

		return;
	}

	if (error) {
		showError(translate('hostinger_reach_error_message'));

		return;
	}

	if (formIndex !== -1) {
		integration.forms[formIndex] = {
			...integration.forms[formIndex],
			isActive: status
		};
	}
};

const handleViewForm = (form: Form) => {
	if (form.formId === 'ai-theme-footer-form') {
		window.open(hostinger_reach_reach_data.site_url, '_blank');
	}

	if (form.post?.guid) {
		window.open(form.post.guid, '_blank');
	}
};

const handleAddForm = (id: string) => {
	const integration = integrationsStore.integrations.find((i) => i.id === id);
	if (!integration?.addFormUrl) {
		return;
	}

	window.open(integration.addFormUrl, '_blank');
};

const handleEditForm = (form: Form) => {
	const integration = integrationsStore.integrations.find((i) => i.forms?.some((f) => f.formId === form.formId));

	if (!integration?.editUrl) {
		return;
	}

	let editUrl = integration.editUrl;

	if (editUrl.includes('{post_id}')) {
		editUrl = editUrl.replace('{post_id}', form.post?.ID.toString() ?? '');
	} else if (editUrl.includes('{form_id}')) {
		editUrl = editUrl.replace('{form_id}', form.formId);
	}

	if (form.formId === 'ai-theme-footer-form') {
		editUrl = 'site-editor.php?p=%2Fwp_template_part%2Fhostinger-ai-theme%2F%2Ffooter&canvas=edit';
	}

	if (!editUrl.startsWith('http') && !editUrl.startsWith('/')) {
		editUrl = `/wp-admin/${editUrl}`;
	}

	window.open(editUrl, '_blank');
};

onMounted(() => {
	loadOverviewData();
	integrationsStore.loadIntegrations();
});
</script>

<template>
	<div class="overview">
		<header class="overview__header">
			<div class="overview__header-content">
				<div class="overview__header-brand">
					<img :src="reachLogo" :alt="translate('hostinger_reach_header_logo_alt')" class="overview__header-logo" />
				</div>
			</div>
		</header>

		<div class="overview__content">
			<div class="overview__section">
				<div class="overview__title">
					<HText as="h1" variant="heading-1">
						{{ translate('hostinger_reach_overview_title') }}
					</HText>
					<div class="overview__title-buttons">
						<HButton
							variant="text"
							color="primary"
							size="small"
							icon-append="ic-arrow-up-right-square-16"
							:to="reachYourPlanLink"
							target="_blank"
							class="overview__your-plan-button"
						>
							{{ translate('hostinger_reach_overview_your_plan_button') }}
						</HButton>
						<HButton
							variant="outline"
							color="primary"
							size="small"
							icon-prepend="ic-lightning-16"
							:to="reachUpgradeLink"
							target="_blank"
							class="overview__upgrade-button"
						>
							{{ translate('hostinger_reach_overview_upgrade_button') }}
						</HButton>
					</div>
				</div>
				<div class="overview__section-content">
					<UsageCardsSection :usage-cards="usageCards" :is-loading="isLoading" />
					<ActionButtonsSection :buttons="actionButtons" />
				</div>
			</div>

			<FormsSection
				@go-to-plugin="handlePluginGoTo"
				@disconnect-plugin="handlePluginDisconnect"
				@toggle-form-status="handleFormToggleStatus"
				@view-form="handleViewForm"
				@edit-form="handleEditForm"
				@add-form="handleAddForm"
			/>
		</div>
	</div>
</template>

<style scoped lang="scss">
.overview {
	min-height: 100vh;
	background-color: var(--neutral--50);

	&__header {
		width: 100%;
		padding: 40px 0 20px 0;
		@media (max-width: 768px) {
			padding: 16px 12px;
		}

		@media (max-width: 480px) {
			padding: 12px 8px;
		}
	}

	&__header-content {
		display: flex;
		justify-content: flex-start;
		align-items: center;
		width: 860px;
		margin: 0 auto;

		@media (max-width: 1023px) {
			width: 100%;
		}
	}

	&__header-brand {
		display: flex;
		align-items: center;
		gap: 12px;

		@media (max-width: 480px) {
			gap: 8px;
		}
	}

	&__header-logo {
		height: 28px;
		width: auto;

		@media (max-width: 768px) {
			height: 24px;
		}

		@media (max-width: 480px) {
			height: 20px;
		}
	}

	&__content {
		display: flex;
		flex-direction: column;
		align-items: flex-end;
		gap: 32px;
		padding: 20px 0;
		width: 860px;
		margin: 0 auto;
	}

	&__section {
		display: flex;
		flex-direction: column;
		align-self: stretch;
		gap: 20px;
	}

	&__title {
		display: flex;
		justify-content: space-between;
		align-items: center;
		align-self: stretch;
	}

	&__title-buttons {
		display: flex;
		align-items: center;
		gap: 8px;
	}

	&__your-plan-button {
		margin-right: 0;
	}

	&__upgrade-button {
		background: var(--neutral--0);
		border: 1px solid transparent;
		background-image:
			linear-gradient(var(--neutral--0), var(--neutral--0)),
			linear-gradient(135deg, var(--primary--200) 0%, var(--primary--400) 47.45%, var(--primary--600) 100%);
		background-origin: border-box;
		background-clip: padding-box, border-box;
		color: var(--neutral--600);
	}

	&__section-content {
		display: flex;
		flex-direction: column;
		align-self: stretch;
		gap: 16px;
	}
}

@media (max-width: 1023px) {
	.overview {
		&__content {
			width: 100%;
			padding: 24px 16px;
		}

		&__title {
			flex-direction: column;
			align-items: flex-start;
			gap: 12px;
		}

		&__title-buttons {
			align-self: stretch;
			justify-content: flex-end;
		}
	}
}
</style>
