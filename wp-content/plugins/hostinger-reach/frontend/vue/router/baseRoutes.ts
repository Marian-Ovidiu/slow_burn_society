import { Route } from '@/types/enums/routeEnum';
import OverviewView from '@/views/OverviewView.vue';
import WelcomeView from '@/views/WelcomeView.vue';

import { connectionGuard } from './guards/connectionGuard';

export default [
	{
		path: '/',
		name: 'root',
		beforeEnter: connectionGuard,
		redirect: '/home'
	},
	{
		path: '/home',
		name: Route.Base.WELCOME,
		beforeEnter: connectionGuard,
		component: WelcomeView
	},
	{
		path: '/overview',
		name: Route.Base.OVERVIEW,
		beforeEnter: connectionGuard,
		component: OverviewView
	}
];
