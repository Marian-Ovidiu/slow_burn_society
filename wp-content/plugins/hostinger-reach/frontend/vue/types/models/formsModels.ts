export interface ContactList {
	id: number;
	name: string;
}

export interface Form {
	id?: number;
	formId: string;
	formTitle?: string;
	postId?: number;
	contactListId: number;
	type: string;
	isActive: boolean;
	isLoading?: boolean;
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

export interface Integration {
	id: string;
	icon: string;
	isActive: boolean;
	title: string;
	url: string;
	adminUrl: string;
	addFormUrl: string;
	isPluginActive: boolean;
	canDeactivate: boolean;
	isGoToPluginVisible: boolean;
	isViewFormHidden: boolean;
	isEditFormHidden: boolean;
	canToggleForms: boolean;
	editUrl?: string;
	forms?: Form[];
}

export interface IntegrationsResponse {
	[key: string]: Integration;
}
