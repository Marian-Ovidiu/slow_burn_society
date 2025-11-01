import { useGeneralDataStore } from '@/stores/generalDataStore';
import { Header } from '@/types/enums';
import type { AuthorizeRequestHeaders } from '@/types/models';
import type { OverviewData } from '@/types/models/reachDataModels';
import { generateCorrelationId } from '@/utils/helpers';
import httpService from '@/utils/services/httpService';

const URL = `${hostinger_reach_reach_data.rest_base_url}hostinger-reach/v1`;

export const reachRepo = {
	generateAuthUrl: (headers?: AuthorizeRequestHeaders) => {
		const { nonce } = useGeneralDataStore();

		const config = {
			headers: {
				[Header.CORRELATION_ID]: headers?.[Header.CORRELATION_ID] || generateCorrelationId(),
				[Header.WP_NONCE]: nonce
			}
		};

		return httpService.post<{ authUrl: string; success: boolean }>(`${URL}/generate-auth-url`, {}, config);
	},

	getOverview: (headers?: AuthorizeRequestHeaders) => {
		const { nonce } = useGeneralDataStore();

		const config = {
			headers: {
				[Header.CORRELATION_ID]: headers?.[Header.CORRELATION_ID] || generateCorrelationId(),
				[Header.WP_NONCE]: nonce
			}
		};

		return httpService.get<OverviewData>(`${URL}/overview`, config);
	}
};
