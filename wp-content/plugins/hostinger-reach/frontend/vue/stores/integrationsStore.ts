import { defineStore } from 'pinia';
import { computed, ref } from 'vue';

import { useToast } from '@/composables/useToast';
import { DEFAULT_PLUGIN_DATA, INTEGRATION_TO_FORM_TYPE_MAP } from '@/data/pluginData';
import { formsRepo } from '@/data/repositories/formsRepo';
import { STORE_PERSISTENT_KEYS } from '@/types/enums';
import type { Form, Integration } from '@/types/models';
import { toKebabCase } from '@/utils/caseConversion';
import { translate } from '@/utils/translate';

export const useIntegrationsStore = defineStore(
	'integrationsStore',
	() => {
		const { showSuccess } = useToast();

		const integrations = ref<Integration[]>([]);
		const isLoading = ref(false);
		const error = ref<string | null>(null);
		const loadingIntegrations = ref<Record<string, boolean>>({});

		const activeIntegrations = computed(() => integrations.value.filter((integration) => integration.isActive));

		const availableIntegrations = computed(() =>
			integrations.value.filter(({ id }) => id !== DEFAULT_PLUGIN_DATA.hostingerReach.id)
		);

		const hasAnyForms = computed(() =>
			integrations.value.some((integration) => integration.forms && integration.forms.length > 0)
		);

		const isIntegrationLoading = (integrationId: string) => loadingIntegrations.value[integrationId] || false;

		const mapAndFilterForms = (formsData: Form[], integrationId: string) =>
			formsData
				?.filter((form) => INTEGRATION_TO_FORM_TYPE_MAP[integrationId] === form.type)
				.map((form) => ({
					...form,
					isViewFormHidden: DEFAULT_PLUGIN_DATA[integrationId].isViewFormHidden,
					isEditFormHidden: DEFAULT_PLUGIN_DATA[integrationId].isEditFormHidden
				})) || [];

		const loadIntegrations = async () => {
			isLoading.value = true;
			error.value = null;

			const [integrationsData, integrationsError] = await formsRepo.getIntegrations();
			const [formsData] = await formsRepo.getForms();

			if (integrationsError) {
				error.value = integrationsError.message || 'Failed to load integrations';
				isLoading.value = false;

				return;
			}

			if (integrationsData) {
				integrations.value = Object.entries(integrationsData).map(([id, integration]) => ({
					id,
					isActive: integration.isActive,
					title: integration.title,
					url: integration.url,
					adminUrl: integration.adminUrl,
					addFormUrl: integration.addFormUrl,
					isPluginActive: integration.isPluginActive,
					editUrl: integration.editUrl,
					forms: mapAndFilterForms(formsData || [], id)
				}));
			}

			isLoading.value = false;
		};

		const toggleIntegrationStatus = async (integrationId: string, isActive: boolean) => {
			loadingIntegrations.value[integrationId] = true;

			const [, error] = await formsRepo.toggleIntegrationStatus(toKebabCase(integrationId), isActive);

			if (error) {
				loadingIntegrations.value[integrationId] = false;

				return;
			}

			const integration = integrations.value.find((i) => i.id === integrationId);

			if (integration) {
				integration.isActive = isActive;
			}

			await loadIntegrations();

			showSuccess(
				translate(
					isActive
						? 'hostinger_reach_forms_plugin_connected_success'
						: 'hostinger_reach_forms_plugin_disconnected_success'
				)
			);

			loadingIntegrations.value[integrationId] = false;
		};

		return {
			integrations,
			isLoading,
			error,
			loadingIntegrations,
			activeIntegrations,
			availableIntegrations,
			hasAnyForms,
			isIntegrationLoading,
			loadIntegrations,
			toggleIntegrationStatus
		};
	},
	{
		persist: { key: STORE_PERSISTENT_KEYS.INTEGRATIONS_STORE }
	}
);
