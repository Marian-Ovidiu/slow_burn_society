import { useGeneralDataStore } from '@/stores/generalDataStore';
import { Header } from '@/types/enums';
import { AuthorizeRequestHeaders } from '@/types/models';
import type { WordPressPagesList } from '@/types/models/pagesModels';
import { generateCorrelationId } from '@/utils/helpers';
import httpService from '@/utils/services/httpService';

const URL = `${hostinger_reach_reach_data.rest_base_url}wp/v2`;

export const pagesRepo = {
	getPagesWithSubscriptionBlock: (headers?: AuthorizeRequestHeaders) => {
		const { nonce } = useGeneralDataStore();

		const config = {
			headers: {
				[Header.CORRELATION_ID]: headers?.[Header.CORRELATION_ID] || generateCorrelationId(),
				[Header.WP_NONCE]: nonce
			}
		};

		return httpService.get<WordPressPagesList>(`${URL}/pages?hostinger_reach_page_query=1`, config);
	}
};
