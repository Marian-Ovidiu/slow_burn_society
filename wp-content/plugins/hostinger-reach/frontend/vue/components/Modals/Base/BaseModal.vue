<script lang="ts" setup>
import { onMounted, onUnmounted } from 'vue';

import { useScrollLock } from '@/composables/useScrollLock';

interface Props {
	title?: string;
	subtitle?: string;
	titleAlignment?: 'centered' | 'left';
	disableScroll?: boolean;
}

const props = withDefaults(defineProps<Props>(), {
	titleAlignment: 'left',
	disableScroll: true
});

const { lockScroll, unlockScroll } = useScrollLock();

onMounted(() => {
	if (props.disableScroll) {
		lockScroll();
	}
});

onUnmounted(() => {
	if (props.disableScroll) {
		unlockScroll();
	}
});
</script>

<template>
	<div class="base-modal">
		<div class="base-modal__header">
			<slot name="back-button"></slot>
			<span
				class="base-modal__title-container"
				:class="{
					'base-modal__title-container--centered': titleAlignment === 'centered'
				}"
			>
				<slot name="title-icon"></slot>
				<h2 v-if="title" class="base-modal__title">{{ title }}</h2>
			</span>
		</div>
		<p
			v-if="subtitle"
			class="base-modal__subtitle"
			:class="{
				'base-modal__subtitle--centered': titleAlignment === 'centered'
			}"
		>
			{{ subtitle }}
		</p>
		<slot></slot>
	</div>
</template>

<style lang="scss" scoped>
.base-modal {
	&__header {
		position: relative;
		margin-bottom: 8px;
		margin-top: -4px;
	}

	&__title-container {
		display: flex;
		align-items: center;
		justify-content: flex-start;

		&--centered {
			justify-content: center;
		}
	}

	&__title {
		font-size: 20px;
		color: var(--neutral--700);
		margin: 0;
		font-weight: 700;
	}

	&__subtitle {
		font-size: 14px;
		margin-top: 4px;
		margin-bottom: 24px;
		color: var(--neutral--500);

		&--centered {
			text-align: center;
		}
	}
}
</style>
