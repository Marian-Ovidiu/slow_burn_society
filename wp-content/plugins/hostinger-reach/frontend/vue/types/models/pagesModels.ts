export interface Page {
	id: string;
	link: string;
	name: string;
	isAdded?: boolean;
}

export interface RenderedContent {
	rendered: string;
	protected?: boolean;
}

export interface Guid {
	rendered: string;
}

export interface Meta {
	footnotes: string;
}

export interface LinkItem {
	href: string;
	targetHints?: {
		allow: string[];
	};
	embeddable?: boolean;
	count?: number;
	templated?: boolean;
}

export interface Links {
	self: LinkItem[];
	collection: LinkItem[];
	about: LinkItem[];
	author: LinkItem[];
	replies: LinkItem[];
	versionHistory: LinkItem[];
	wpAttachment: LinkItem[];
	curies: LinkItem[];
}

export interface WordPressPage {
	id: number;
	date: string;
	dateGmt: string;
	guid: Guid;
	modified: string;
	modifiedGmt: string;
	slug: string;
	status: string;
	type: string;
	link: string;
	title: RenderedContent;
	content: RenderedContent;
	excerpt: RenderedContent;
	author: number;
	featuredMedia: number;
	parent: number;
	menuOrder: number;
	commentStatus: string;
	pingStatus: string;
	template: string;
	meta: Meta;
	classList: string[];
	HostingerReachPluginHasSubscriptionBlock: boolean;
	HostingerReachPluginIsElementor: boolean;
	links: Links;
}

export type WordPressPagesList = WordPressPage[];
