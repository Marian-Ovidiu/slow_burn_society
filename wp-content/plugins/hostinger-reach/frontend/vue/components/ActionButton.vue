<script setup lang="ts">
import { translate } from '@/utils/translate';

interface Props {
	icon: string;
	text: string;
	url?: string;
}

const { url = '', text, icon } = defineProps<Props>();

const handleClick = () => {
	if (url) {
		window.open(url, '_blank');
	}
};
</script>

<template>
	<HCard
		class="action-button"
		padding="16px 20px"
		border-radius="999px"
		role="button"
		tabindex="0"
		:aria-label="url ? `${text} (${translate('hostinger_reach_ui_opens_in_new_tab')})` : text"
		@click="handleClick"
		@keydown.enter="handleClick"
		@keydown.space.prevent="handleClick"
	>
		<div class="button-content">
			<HIcon :name="icon" class="button-icon" aria-hidden="true" />
			<span class="button-text">{{ text }}</span>
		</div>
		<HIcon name="ic-arrow-up-right-square-16" class="arrow-icon" aria-hidden="true" />
	</HCard>
</template>

<style scoped lang="scss">
.action-button {
	flex: 1;
	cursor: pointer;
	background-color: var(--neutral--0);
	border: 1px solid var(--neutral--200);
	transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
	display: flex;
	align-items: center;
	justify-content: space-between;
	gap: 12px;

	&:hover {
		background-color: var(--neutral--100);
		border-color: var(--neutral--200);
	}

	&:deep(.h-card) {
		padding: 16px 20px;
		border-radius: 999px;
		background-color: var(--neutral--0);
		border: 1px solid var(--neutral--200);
		display: flex;
		align-items: center;
		justify-content: space-between;
		gap: 12px;
	}
}

.button-content {
	display: flex;
	align-items: center;
	gap: 8px;
	flex: 1;
}

.button-icon,
.arrow-icon {
	width: 16px;
	height: 16px;
	color: var(--neutral--600);
	flex-shrink: 0;
}

.button-text {
	font-family: 'DM Sans', sans-serif;
	font-weight: 700;
	font-size: 12px;
	line-height: 1.667;
	color: var(--neutral--600);
	margin: 0;
	white-space: nowrap;
	flex: 1;
}

@media (max-width: 767px) {
	.action-button {
		&:deep(.h-card) {
			padding: 12px 16px;
		}
	}
}
</style>
