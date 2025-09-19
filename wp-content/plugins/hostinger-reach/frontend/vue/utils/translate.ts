interface WindowWithHostingerData extends Window {
	hostinger_reach_reach_data?: {
		translations?: Record<string, string>;
	};
}

export const translate = (text: string): string => {
	const translations = (window as WindowWithHostingerData)?.hostinger_reach_reach_data?.translations || {};

	if (!text) return '';

	return translations[text] || text;
};

export const t = translate;
