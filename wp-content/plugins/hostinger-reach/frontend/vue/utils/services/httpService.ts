import type { AxiosRequestConfig } from 'axios';
import axios from 'axios';

import { camelToSnakeObj, snakeToCamelObj } from '@/utils/caseConversion';
import { asyncCall } from '@/utils/helpers';

const TIMEOUT_TIME = 120_000;

export const axiosInstance = axios.create({
	timeout: TIMEOUT_TIME,
	withCredentials: true,
	headers: {
		Accept: 'application/json;charset=UTF-8',
		'Content-Type': 'application/json;charset=UTF-8'
	}
});

axiosInstance.interceptors.request.use((req) => {
	if ((req as unknown as { plain?: boolean }).plain) return req;

	if (req.data) {
		req.data = camelToSnakeObj(req.data);
	}

	if (req.params) {
		req.params = camelToSnakeObj(req.params);
	}

	return req;
});

axiosInstance.interceptors.response.use(
	(res) =>
		snakeToCamelObj({
			...res,
			data: res.data
		}),
	(error: Error) => Promise.reject(error)
);

const httpService = {
	get<T>(url: string, config?: AxiosRequestConfig) {
		return asyncCall<T>(axiosInstance.get(url, config));
	},
	post<T>(url: string, data?: unknown, config?: AxiosRequestConfig) {
		return asyncCall<T>(axiosInstance.post(url, data, config));
	},
	put<T>(url: string, data?: unknown, config?: AxiosRequestConfig) {
		return asyncCall<T>(axiosInstance.put(url, data, config));
	},
	patch<T>(url: string, data?: unknown, config?: AxiosRequestConfig) {
		return asyncCall<T>(axiosInstance.patch(url, data, config));
	},
	delete<T>(url: string, config?: AxiosRequestConfig) {
		return asyncCall<T>(axiosInstance.delete(url, config));
	},
	request<T>(config: AxiosRequestConfig) {
		return asyncCall<T>(axiosInstance(config));
	}
};

export default httpService;
