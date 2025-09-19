export interface ContactList {
	id: number;
	name: string;
}

export interface Form {
	id?: number;
	formId: string;
	postId?: number;
	contactListId: number;
	type: string;
	isActive: boolean;
	isLoading?: boolean;
	isViewFormHidden?: boolean;
	isEditFormHidden?: boolean;
	submissions: number;
	post?: {
		ID: number;
		postAuthor: string;
		postDate: string;
		postDateGmt: string;
		postContent: string;
		postTitle: string;
		postExcerpt: string;
		postStatus: string;
		commentStatus: string;
		pingStatus: string;
		postPassword: string;
		postName: string;
		toPing: string;
		pinged: string;
		postModified: string;
		postModifiedGmt: string;
		postContentFiltered: string;
		postParent: number;
		guid: string;
		menuOrder: number;
		postType: string;
		postMimeType: string;
		commentCount: string;
		filter: string;
		ancestors: unknown[];
		pageTemplate: string;
		postCategory: number[];
		tagsInput: unknown[];
		id?: number;
		title?: string;
		url?: string;
	};
}

export interface FormsFilter {
	contactListId?: number;
	type?: string;
	limit?: number;
	offset?: number;
}

export interface FormSubmissionData {
	formId: string;
	contactListId?: number;
	postId?: number;
	type: string;
	isActive?: boolean;
}

export interface FormUpdateData {
	formId: string;
	contactListId?: number;
	isActive?: boolean;
}

export interface Integration {
	id: string;
	isActive: boolean;
	title: string;
	url: string;
	adminUrl: string;
	addFormUrl: string;
	isPluginActive: boolean;
	editUrl?: string;
	forms?: Form[];
}

export interface IntegrationsResponse {
	[key: string]: {
		isActive: boolean;
		title: string;
		url: string;
		adminUrl: string;
		addFormUrl: string;
		editUrl: string;
		isAvailable: boolean;
		isPluginActive: boolean;
	};
}
