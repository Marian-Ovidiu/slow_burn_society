import type { NavigationGuardNext, RouteLocationNormalized } from 'vue-router';

import { Route } from '@/types/enums/routeEnum';

const getTargetRoute = (isConnected: boolean) => (isConnected ? Route.Base.OVERVIEW : Route.Base.WELCOME);

const shouldRedirect = (to: RouteLocationNormalized, isConnected: boolean) => {
	if (to.path === '/') {
		return getTargetRoute(isConnected);
	}

	const targetRoute = getTargetRoute(isConnected);
	if (to.name !== targetRoute) {
		return targetRoute;
	}

	return null;
};

export const connectionGuard = (to: RouteLocationNormalized, _: RouteLocationNormalized, next: NavigationGuardNext) => {
	const isConnected = hostinger_reach_reach_data?.is_connected;
	const redirectRoute = shouldRedirect(to, isConnected);

	if (redirectRoute) {
		next({ name: redirectRoute });

		return;
	}

	next();
};
