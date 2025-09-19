<script setup lang="ts">
import ActionButton from '@/components/ActionButton.vue';

interface ActionButtonData {
	icon: string;
	text: string;
	url: string;
}

interface Props {
	buttons: ActionButtonData[];
}

const props = defineProps<Props>();
</script>

<template>
	<div class="action-buttons-section">
		<ActionButton
			v-for="button in props.buttons"
			:key="button.text"
			:icon="button.icon"
			:text="button.text"
			:url="button.url"
		/>
	</div>
</template>

<style scoped lang="scss">
.action-buttons-section {
	display: flex;
	justify-content: stretch;
	align-items: stretch;
	align-self: stretch;
	gap: 8px;
}

@media (max-width: 1023px) {
	.action-buttons-section {
		flex-direction: column;
	}
}
</style>
