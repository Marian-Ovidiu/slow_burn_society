<script setup lang="ts">
import { computed } from 'vue';

import UsageCard from '@/components/UsageCard.vue';

interface UsageCardData {
	title: string;
	layout: 'horizontal' | 'vertical';
	metrics: Array<{
		label: string;
		value: string | number;
		hasIcon?: boolean;
		tooltip?: string;
	}>;
}

interface Props {
	usageCards: (UsageCardData | undefined)[];
	isLoading?: boolean;
}

const props = withDefaults(defineProps<Props>(), {
	isLoading: false
});

const validCards = computed(() => props.usageCards.filter(Boolean) as UsageCardData[]);
</script>

<template>
	<div class="usage-cards-row">
		<UsageCard
			v-for="card in validCards"
			:key="card.title"
			:title="card.title"
			:layout="card.layout"
			:metrics="card.metrics"
			:is-loading="props.isLoading"
		/>
	</div>
</template>

<style scoped lang="scss">
.usage-cards-row {
	display: flex;
	flex-direction: column;
	align-self: stretch;
	gap: 12px;
	flex: 1;
}

@media (max-width: 1023px) {
	.usage-cards-row {
		flex-direction: column;
	}
}
</style>
