<script lang="ts" setup>
import { HSkeletonLoader } from '@hostinger/hcomponents';
</script>

<template>
	<div class="plugin-entry-skeleton">
		<div class="plugin-entry-skeleton__cell plugin-entry-skeleton__cell--plugin">
			<div class="plugin-entry-skeleton__plugin-content">
				<div class="plugin-entry-skeleton__plugin-info">
					<div class="plugin-entry-skeleton__plugin-icon">
						<HSkeletonLoader width="28px" height="28px" border-radius="sm" />
					</div>
					<HSkeletonLoader width="120px" height="16px" border-radius="sm" />
				</div>
			</div>
		</div>
		<div class="plugin-entry-skeleton__cell plugin-entry-skeleton__cell--entries">
			<HSkeletonLoader width="20px" height="16px" border-radius="sm" />
		</div>
		<div class="plugin-entry-skeleton__cell plugin-entry-skeleton__cell--status">
			<HSkeletonLoader width="60px" height="24px" border-radius="lg" />
		</div>
		<div class="plugin-entry-skeleton__cell plugin-entry-skeleton__cell--actions">
			<HSkeletonLoader width="32px" height="32px" border-radius="sm" />
		</div>
	</div>
</template>

<style scoped lang="scss">
.plugin-entry-skeleton {
	display: flex;
	padding: 16px 0px;

	&__cell {
		display: flex;
		align-items: center;

		&--plugin {
			width: 50%;
			order: 1;
		}

		&--entries {
			width: 21%;
			order: 2;
		}

		&--status {
			width: 19%;
			order: 3;
		}

		&--actions {
			width: 10%;
			display: flex;
			justify-content: flex-end;
			padding-right: 16px;
			order: 4;
		}
	}

	&__plugin-content {
		display: flex;
		align-items: center;
		gap: 8px;
		width: 100%;
	}

	&__plugin-info {
		display: flex;
		align-items: center;
		gap: 12px;
	}

	&__plugin-icon {
		width: 28px;
		height: 28px;
		border-radius: 7px;
		overflow: hidden;
		display: flex;
		align-items: center;
		justify-content: center;
	}

	@media (max-width: 1023px) {
		flex-direction: column;
		gap: 12px;

		&__cell--plugin,
		&__cell--entries,
		&__cell--status,
		&__cell--actions {
			width: 100%;
			justify-content: center;
		}

		&__cell--actions {
			padding-right: 0;
		}
	}
}
</style>
