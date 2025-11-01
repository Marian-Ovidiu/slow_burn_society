<script lang="ts" setup>
import { HSkeletonLoader } from '@hostinger/hcomponents';

const SKELETON_COUNT = 3;
</script>

<template>
	<div class="plugins-section-skeleton">
		<div class="plugins-section-skeleton__toggle">
			<HSkeletonLoader width="160px" height="32px" border-radius="sm" />
		</div>

		<div class="plugins-section-skeleton__list">
			<div v-for="i in SKELETON_COUNT" :key="`skeleton-${i}`" class="plugins-section-skeleton__item">
				<div class="plugins-section-skeleton__item-info">
					<div class="plugins-section-skeleton__item-icon">
						<HSkeletonLoader width="40px" height="40px" border-radius="sm" />
					</div>
					<div class="plugins-section-skeleton__item-details">
						<HSkeletonLoader width="120px" height="16px" border-radius="sm" />
						<HSkeletonLoader width="80px" height="14px" border-radius="sm" />
					</div>
				</div>
				<div class="plugins-section-skeleton__item-actions">
					<HSkeletonLoader width="60px" height="24px" border-radius="lg" />
					<HSkeletonLoader width="80px" height="32px" border-radius="sm" />
				</div>
			</div>
		</div>
	</div>
</template>

<style lang="scss" scoped>
.plugins-section-skeleton {
	display: flex;
	flex-direction: column;
	gap: 16px;

	&__toggle {
		display: flex;
		justify-content: center;
	}

	&__list {
		display: flex;
		flex-direction: column;
		gap: 12px;
	}

	&__item {
		display: flex;
		align-items: center;
		justify-content: space-between;
		padding: 16px;
		background: var(--neutral--0);
		border: 1px solid var(--neutral--200);
		border-radius: 12px;
	}

	&__item-info {
		display: flex;
		align-items: center;
		gap: 12px;
	}

	&__item-icon {
		flex-shrink: 0;
	}

	&__item-details {
		display: flex;
		flex-direction: column;
		gap: 4px;
	}

	&__item-actions {
		display: flex;
		align-items: center;
		gap: 12px;
		flex-shrink: 0;
	}
}
</style>
