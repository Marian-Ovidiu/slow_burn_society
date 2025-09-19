<script lang="ts" setup>
import { HPopoverGlobalBackdrop } from '@hostinger/hcomponents';

import Modals from '@/components/Modals/Modals.vue';
</script>

<template>
	<div id="hostinger-reach-app">
		<RouterView />
		<Modals />
		<HToast />
		<HPopoverGlobalBackdrop />
	</div>
</template>
