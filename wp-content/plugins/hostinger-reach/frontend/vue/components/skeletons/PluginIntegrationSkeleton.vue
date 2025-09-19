<script lang="ts" setup>
import { HSkeletonLoader } from '@hostinger/hcomponents';
</script>

<template>
	<div class="plugin-integration-skeleton">
		<div class="plugin-integration-skeleton__info">
			<div class="plugin-integration-skeleton__icon">
				<HSkeletonLoader width="40px" height="40px" border-radius="sm" />
			</div>
			<div class="plugin-integration-skeleton__details">
				<HSkeletonLoader width="120px" height="16px" border-radius="sm" />
			</div>
		</div>
		<div class="plugin-integration-skeleton__actions">
			<HSkeletonLoader width="60px" height="24px" border-radius="lg" />
			<HSkeletonLoader width="80px" height="32px" border-radius="sm" />
		</div>
	</div>
</template>

<style lang="scss" scoped>
.plugin-integration-skeleton {
	display: flex;
	align-items: center;
	justify-content: space-between;
	padding: 16px;
	width: 100%;

	&__info {
		display: flex;
		align-items: center;
		gap: 12px;
	}

	&__icon {
		flex-shrink: 0;
	}

	&__details {
		display: flex;
		flex-direction: column;
		gap: 4px;
	}

	&__actions {
		display: flex;
		align-items: center;
		gap: 12px;
		flex-shrink: 0;
	}
}
</style>
