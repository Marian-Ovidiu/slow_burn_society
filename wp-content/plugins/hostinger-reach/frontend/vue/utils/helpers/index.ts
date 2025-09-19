import { AxiosResponse } from 'axios';

interface BaseApiResponse<T> {
	success?: boolean;
	data?: T;
	error?: Error | string | null;
}

export const generateCorrelationId = (): string => `${Date.now()}-${Math.random().toString(36).substr(2, 9)}`;

export const asyncCall = async <T>(
	promise: Promise<AxiosResponse<BaseApiResponse<T>>>
): Promise<[T | null, Error | null]> => {
	try {
		const response = await promise;

		if (!response.data.error || (Array.isArray(response.data.error) && !response.data.error.length)) {
			const responseData = response.data.data || response.data;

			return [responseData as T, null];
		}

		return [null, response.data.error as Error];
	} catch (error) {
		return [null, error as Error];
	}
};
