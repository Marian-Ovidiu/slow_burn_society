export const IntegrationStatus = {
	NOT_AVAILABLE: "not_available",
	PLUGIN_DEACTIVATED: "plugin_deactivated",
	READY_TO_CONNECT: "ready_to_connect",
	CONNECTED: "connected",
} as const;

export type IntegrationStatus =
	(typeof IntegrationStatus)[keyof typeof IntegrationStatus];

export const IntegrationAction = {
	INSTALL: "install",
	CONNECT: "connect",
	DISCONNECT: "disconnect",
	NONE: "none",
} as const;

export type IntegrationAction =
	(typeof IntegrationAction)[keyof typeof IntegrationAction];

export const StatusColor = {
	PRIMARY: "primary",
	SUCCESS: "success",
	DANGER: "danger",
	NEUTRAL: "neutral",
} as const;

export type StatusColor = (typeof StatusColor)[keyof typeof StatusColor];

export type ButtonVariant = "contain" | "outline" | "outlineStrong" | "text";
