import type { DirectiveBinding } from 'vue';

import { translate } from '@/utils/translate';

const TOOLTIP_ATTR = 'tooltip';

export interface ComplexTooltip {
	content: string;
	autoWidth?: boolean;
}

type TooltipBinding = DirectiveBinding<string> | DirectiveBinding<ComplexTooltip>;

const isString = (value: unknown) => typeof value === 'string';

const getCurrentRotation = (el: HTMLElement) => {
	const computedStyles = window.getComputedStyle(el, null);
	const transformProp =
		computedStyles.getPropertyValue('-webkit-transform') ||
		computedStyles.getPropertyValue('-moz-transform') ||
		computedStyles.getPropertyValue('-ms-transform') ||
		computedStyles.getPropertyValue('-o-transform') ||
		computedStyles.getPropertyValue('transform') ||
		'none';
	if (transformProp !== 'none') {
		const values = transformProp.split('(')[1].split(')')[0].split(',');

		const angle = Math.round(Math.atan2(Number(values[1]), Number(values[0])) * (180 / Math.PI));

		return angle < 0 ? angle + 360 : angle;
	}

	return 0;
};

const getTooltipClass = ({ modifiers }: DirectiveBinding, isRotated: boolean) => {
	const position = Object.keys(modifiers)[0];

	return `has-tooltip--${position || 'bottom'}${isRotated ? '-rotated' : ''}`;
};

const getTooltipContent = (binding: TooltipBinding) => {
	if (!binding.value) return null;

	const content = isString(binding.value) ? binding.value : (binding.value as ComplexTooltip).content;

	return translate(content);
};

const addTooltip = (el: HTMLElement, binding: TooltipBinding) => {
	const content = getTooltipContent(binding);

	if (!content) return removeTooltip(el, binding);

	const rotation = getCurrentRotation(el);

	el.setAttribute(TOOLTIP_ATTR, content as string);

	document.documentElement.style.setProperty('--tooltip-rotation', `-${rotation}deg`);

	if (!isString(binding.value) && (binding.value as ComplexTooltip).autoWidth) {
		document.documentElement.style.setProperty('--tooltip-width', 'auto');
	}

	el.style.transition = '0s';

	const zIndex = getComputedStyle(document.documentElement).getPropertyValue('--z-index-child-2');
	el.style.zIndex = zIndex;
	el.style.position = 'relative';

	el.classList.add(getTooltipClass(binding, rotation === 180));
};

const removeTooltip = (el: HTMLElement, binding: TooltipBinding) => {
	el.removeAttribute(TOOLTIP_ATTR);

	el.style.zIndex = '';
	el.style.position = '';

	el.classList.remove(getTooltipClass(binding, true));
	el.classList.remove(getTooltipClass(binding, false));
};

const unbind = (el: HTMLElement, binding: TooltipBinding) => {
	el.removeEventListener('mouseover', () => addTooltip(el, binding));
	el.removeEventListener('mouseleave', () => removeTooltip(el, binding));
	el.removeEventListener('click', () => removeTooltip(el, binding));
};

const bind = (el: HTMLElement, binding: TooltipBinding) => {
	el.addEventListener('mouseover', () => addTooltip(el, binding));
	el.addEventListener('mouseleave', () => removeTooltip(el, binding));
	el.addEventListener('click', () => removeTooltip(el, binding));
};

export const vTooltip = {
	mounted: (el: HTMLElement, binding: TooltipBinding) => bind(el, binding),
	updated: (el: HTMLElement, binding: TooltipBinding) => bind(el, binding),
	beforeUnmount: (el: HTMLElement, binding: TooltipBinding) => unbind(el, binding)
};
