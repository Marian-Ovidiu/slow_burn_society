import type { Page } from "./pagesModels";

export interface ModalContent {
	title?: string;
	subtitle?: string;
	pages?: Page[];
	data?: Record<string, unknown>;
	onSuccess?: () => void;
	onClose?: () => void;
}

export interface ModalSettings {
	isXL?: boolean;
	isXXL?: boolean;
	isLG?: boolean;
	hasCloseButton?: boolean;
	noContentPadding?: boolean;
	noBorder?: boolean;
}
