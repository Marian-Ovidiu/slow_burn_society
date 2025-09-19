import { useGeneralDataStore } from '@/stores/generalDataStore';
import { Header } from '@/types/enums';
import type { AuthorizeRequestHeaders, ContactList, Form, FormsFilter, IntegrationsResponse } from '@/types/models';
import { generateCorrelationId } from '@/utils/helpers';
import httpService from '@/utils/services/httpService';

const URL = `${hostinger_reach_reach_data.rest_base_url}hostinger-reach/v1`;

export const formsRepo = {
	getForms: (filters: FormsFilter = {}, headers?: AuthorizeRequestHeaders) => {
		const { nonce } = useGeneralDataStore();
		const params = new URLSearchParams();

		if (filters.contactListId) {
			params.append('contact_list_id', filters.contactListId.toString());
		}

		if (filters.type) {
			params.append('type', filters.type);
		}

		if (filters.limit) {
			params.append('limit', filters.limit.toString());
		}

		if (filters.offset) {
			params.append('offset', filters.offset.toString());
		}

		const queryString = params.toString() ? `?${params.toString()}` : '';
		const config = {
			headers: {
				[Header.CORRELATION_ID]: headers?.[Header.CORRELATION_ID] || generateCorrelationId(),
				[Header.WP_NONCE]: nonce
			}
		};

		return httpService.get<Form[]>(`${URL}/forms${queryString}`, config);
	},

	getContactLists: (headers?: AuthorizeRequestHeaders) => {
		const { nonce } = useGeneralDataStore();

		const config = {
			headers: {
				[Header.CORRELATION_ID]: headers?.[Header.CORRELATION_ID] || generateCorrelationId(),
				[Header.WP_NONCE]: nonce
			}
		};

		return httpService.get<ContactList[]>(`${URL}/contact-lists`, config);
	},

	toggleFormStatus: (formId: string, isActive: boolean, headers?: AuthorizeRequestHeaders) => {
		const { nonce } = useGeneralDataStore();

		const config = {
			headers: {
				[Header.CORRELATION_ID]: headers?.[Header.CORRELATION_ID] || generateCorrelationId(),
				[Header.WP_NONCE]: nonce
			}
		};

		const data = {
			formId,
			isActive
		};

		return httpService.post<{ success: boolean }>(`${URL}/forms`, data, config);
	},

	getIntegrations: (headers?: AuthorizeRequestHeaders) => {
		const { nonce } = useGeneralDataStore();

		const config = {
			headers: {
				[Header.CORRELATION_ID]: headers?.[Header.CORRELATION_ID] || generateCorrelationId(),
				[Header.WP_NONCE]: nonce
			}
		};

		return httpService.get<IntegrationsResponse>(`${URL}/integrations`, config);
	},

	getIntegrationForms: (integrationType: string, headers?: AuthorizeRequestHeaders) => {
		const { nonce } = useGeneralDataStore();
		const params = new URLSearchParams();
		params.append('type', integrationType);

		const config = {
			plain: true,
			headers: {
				[Header.CORRELATION_ID]: headers?.[Header.CORRELATION_ID] || generateCorrelationId(),
				[Header.WP_NONCE]: nonce
			}
		};

		return httpService.get<Form[]>(`${URL}/forms?${params.toString()}`, config);
	},

	toggleIntegrationStatus: (integrationId: string, isActive: boolean, headers?: AuthorizeRequestHeaders) => {
		const { nonce } = useGeneralDataStore();

		const config = {
			headers: {
				[Header.CORRELATION_ID]: headers?.[Header.CORRELATION_ID] || generateCorrelationId(),
				[Header.WP_NONCE]: nonce
			}
		};

		const data = {
			integration: integrationId,
			isActive
		};

		return httpService.post<{ success: boolean }>(`${URL}/integrations`, data, config);
	}
};
