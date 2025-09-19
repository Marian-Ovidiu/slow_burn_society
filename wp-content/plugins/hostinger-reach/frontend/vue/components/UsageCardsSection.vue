<script setup lang="ts">
import UsageCard from '@/components/UsageCard.vue';
import UsageCardsRow from '@/components/UsageCardsRow.vue';

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
	usageCards: UsageCardData[];
	isLoading?: boolean;
}

const props = withDefaults(defineProps<Props>(), {
	isLoading: false
});
</script>

<template>
	<div class="usage-cards-section">
		<UsageCardsRow :usage-cards="[props.usageCards[0], props.usageCards[1]]" :is-loading="props.isLoading" />
		<UsageCard
			:title="props.usageCards[2].title"
			:layout="props.usageCards[2].layout"
			:metrics="props.usageCards[2].metrics"
			:is-loading="props.isLoading"
		/>
	</div>
</template>

<style scoped lang="scss">
.usage-cards-section {
	display: flex;
	align-self: stretch;
	gap: 12px;
}

@media (max-width: 1023px) {
	.usage-cards-section {
		flex-direction: column;
	}
}
</style>
