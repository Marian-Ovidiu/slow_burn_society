export const Header = {
	WP_NONCE: "X-WP-Nonce",
	CORRELATION_ID: "X-Correlation-ID",
	X_HPANEL_ORDER_TOKEN: "X-Hpanel-Order-Token",
} as const;

export type HeaderType = (typeof Header)[keyof typeof Header];
