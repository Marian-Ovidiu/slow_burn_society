import { Header } from "@/types/enums";

export interface AuthorizeRequestHeaders {
	[Header.X_HPANEL_ORDER_TOKEN]: string;
	[Header.CORRELATION_ID]: string;
}
