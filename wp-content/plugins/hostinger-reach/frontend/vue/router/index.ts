import { createRouter, createWebHashHistory } from 'vue-router';

import baseRoutes from './baseRoutes';

const router = createRouter({
	history: createWebHashHistory(),
	routes: baseRoutes
});

export default router;
