import { translate } from '@/utils/translate';

export interface FAQItem {
	id: string;
	question: string;
	answer: string;
}

export const faqData: FAQItem[] = [
	{
		id: 'what-is-reach',
		question: translate('hostinger_reach_faq_what_is_reach_question'),
		answer: translate('hostinger_reach_faq_what_is_reach_answer')
	},
	{
		id: 'how-different',
		question: translate('hostinger_reach_faq_how_different_question'),
		answer: translate('hostinger_reach_faq_how_different_answer')
	},
	{
		id: 'how-much-cost',
		question: translate('hostinger_reach_faq_how_much_cost_question'),
		answer: translate('hostinger_reach_faq_how_much_cost_answer')
	}
];
