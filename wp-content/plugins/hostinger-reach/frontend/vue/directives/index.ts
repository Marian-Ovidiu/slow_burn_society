import type { App } from 'vue';

import { vTooltip } from '@/directives/tooltipDirective';

export const setDirectives = (app: App) => {
	app.directive('tooltip', vTooltip);
};
