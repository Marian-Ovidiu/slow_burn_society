<script setup lang="ts">
import { HSkeletonLoader } from '@hostinger/hcomponents';
import { computed } from 'vue';

import { translate } from '@/utils/translate';

interface MetricData {
	label: string;
	value: string | number;
	hasIcon?: boolean;
	tooltip?: string;
}

interface Props {
	title: string;
	layout: 'horizontal' | 'vertical';
	metrics: MetricData[];
	isLoading?: boolean;
}

const props = withDefaults(defineProps<Props>(), {
	isLoading: false
});

const isHorizontal = computed(() => props.layout === 'horizontal');
const isVertical = computed(() => props.layout === 'vertical');

const contentClasses = computed(() => ({
	'usage-card__content': true,
	'usage-card__content--horizontal': isHorizontal.value,
	'usage-card__content--vertical': isVertical.value
}));

const getMetricClasses = (isVerticalMetric: boolean) => ({
	'usage-card__metric': true,
	'usage-card__metric--vertical': isVerticalMetric
});

const SKELETON_CONFIG = {
	title: { height: '24px', width: '120px' },
	label: { height: '16px', width: isVertical.value ? '80px' : '60px' },
	value: { height: '32px', width: isVertical.value ? '50px' : '40px' }
} as const;
</script>

<template>
	<article
		class="usage-card"
		:class="{ 'usage-card--vertical': isVertical }"
		role="article"
		:aria-label="`${title} ${translate('hostinger_reach_ui_usage_statistics')}`"
	>
		<HCard class="usage-card__container" padding="20px 32px" border-radius="16px">
			<div class="usage-card__inner">
				<header class="usage-card__header">
					<HSkeletonLoader
						v-if="isLoading"
						:height="SKELETON_CONFIG.title.height"
						:width="SKELETON_CONFIG.title.width"
						rounded
					/>
					<HText v-else id="usage-card-title" as="h3" variant="heading-3" class="usage-card__title">
						{{ title }}
					</HText>
				</header>

				<div :class="contentClasses">
					<template v-for="(metric, index) in metrics" :key="index">
						<div :class="getMetricClasses(isVertical)">
							<div class="usage-card__metric-content">
								<div class="usage-card__metric-label-container">
									<HSkeletonLoader
										v-if="isLoading"
										:height="SKELETON_CONFIG.label.height"
										:width="SKELETON_CONFIG.label.width"
										rounded
									/>
									<HText v-else as="div" variant="body-3-secondary" class="usage-card__metric-label">
										{{ metric.label }}
									</HText>
									<span v-if="metric.hasIcon && !isLoading" v-tooltip="metric.tooltip" class="usage-card__metric-icon">
										<HIcon name="ic-info-circle-filled-16" color="neutral--300" aria-hidden="true" />
									</span>
								</div>

								<HSkeletonLoader
									v-if="isLoading"
									:height="SKELETON_CONFIG.value.height"
									:width="SKELETON_CONFIG.value.width"
									rounded
								/>
								<HText v-else as="div" variant="heading-1" class="usage-card__metric-value">
									{{ metric.value }}
								</HText>
							</div>
						</div>

						<div v-if="isHorizontal && index < metrics.length - 1" class="usage-card__divider" aria-hidden="true"></div>
					</template>
				</div>
			</div>
		</HCard>
	</article>
</template>

<style scoped lang="scss">
$mobile-breakpoint: 767px;

.usage-card {
	flex: 1;
	display: flex;
	flex-direction: column;

	&--vertical {
		max-width: none;
	}

	&__container {
		flex: 1;
		display: flex;
		flex-direction: column;
		border: 1px solid var(--neutral--200);
	}

	&__inner {
		display: flex;
		flex-direction: column;
		align-self: stretch;
	}
}

.usage-card__header {
	display: flex;
	align-items: center;
	align-self: stretch;
	gap: 4px;
	margin-bottom: 8px;
}

.usage-card__title {
	font-weight: 700;
	font-size: 16px;
	line-height: 1.5;
	color: var(--neutral--700);
}

.usage-card__content {
	display: flex;
	align-self: stretch;

	&--horizontal {
		flex-direction: row;
		align-items: center;
		gap: 40px;
	}

	&--vertical {
		flex-direction: column;
		justify-content: space-between;
		height: 200px;
	}
}

.usage-card__metric {
	display: flex;
	align-items: center;

	&:not(&--vertical) {
		flex-direction: row;
		gap: 40px;
	}

	&--vertical {
		flex-direction: column;
		align-self: stretch;
		gap: 16px;
		padding: 8px 0 4px;
	}

	&-content {
		display: flex;
		flex-direction: column;
		gap: 4px;

		.usage-card__metric--vertical & {
			align-self: stretch;
		}
	}

	&-label-container {
		display: flex;
		align-items: center;
		gap: 8px;
		margin-bottom: 4px;
	}

	&-icon {
		display: flex;
		align-items: center;
	}

	&-label {
		font-weight: 400;
		font-size: 12px;
		line-height: 1.67;
		color: var(--neutral--300);
	}

	&-value {
		font-weight: 700;
		font-size: 24px;
		line-height: 1.33;
		color: var(--neutral--600);
	}
}

.usage-card__divider {
	width: 1px;
	height: 20px;
	background-color: var(--neutral--200);
	flex-shrink: 0;
}

@media (max-width: $mobile-breakpoint) {
	.usage-card__content--horizontal {
		flex-direction: column;
		align-items: flex-start;
		gap: 16px;
	}

	.usage-card__metric:not(.usage-card__metric--vertical) {
		flex-direction: column;
		align-items: flex-start;
		gap: 8px;
		width: 100%;

		.usage-card__metric-content {
			width: 100%;
		}
	}

	.usage-card__divider {
		display: none;
	}
}
</style>
