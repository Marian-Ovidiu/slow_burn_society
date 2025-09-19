<script setup lang="ts">
import { translate } from '@/utils/translate';

interface Props {
	title: string;
	description: string;
	buttonText: string;
	buttonIcon?: string;
	backgroundImage?: string;
	isButtonDisabled?: boolean;
	isButtonLoading?: boolean;
	onButtonClick?: () => void;
}

withDefaults(defineProps<Props>(), {
	buttonIcon: 'ic-plus-16',
	backgroundImage: '',
	onButtonClick: () => {}
});
</script>

<template>
	<section class="banner-section" role="banner" aria-labelledby="banner-title">
		<HCard padding="0" border-radius="20px">
			<div class="banner">
				<div class="banner__content">
					<div class="content-wrapper">
						<div class="title-section">
							<HText id="banner-title" as="h3" variant="heading-3">
								{{ title }}
							</HText>
						</div>
						<div class="description-section">
							<HText as="p" variant="body-2" aria-describedby="banner-title">
								{{ description }}
							</HText>
						</div>
					</div>
					<HButton
						size="small"
						color="primary"
						:icon-prepend="buttonIcon"
						class="banner-button"
						:is-disabled="isButtonDisabled"
						:is-loading="isButtonLoading"
						:aria-label="`${buttonText} - ${title}`"
						@click="onButtonClick"
					>
						{{ buttonText }}
					</HButton>
				</div>
				<div
					v-if="backgroundImage"
					class="banner__image"
					role="img"
					:aria-label="`${translate('hostinger_reach_ui_banner_background_image')} ${title}`"
				>
					<img
						class="banner__background-image"
						:src="backgroundImage"
						:alt="`${translate('hostinger_reach_ui_background_image_for')} ${title}`"
						role="presentation"
					/>
				</div>
			</div>
		</HCard>
	</section>
</template>

<style scoped lang="scss">
.banner-section {
	display: block;
	width: 100%;
}

.banner {
	display: grid;
	grid-template-columns: 1fr auto;
	gap: 24px;
	align-items: center;
	min-height: 160px;
	position: relative;
	border-radius: 20px;
	overflow: hidden;

	@media (max-width: 992px) {
		display: flex;
		flex-direction: column;
		text-align: left;
		gap: 0;
		min-height: auto;
		background: linear-gradient(
			135deg,
			var(--neutral--0) 0%,
			var(--neutral--50) 30%,
			var(--primary--50) 60%,
			var(--primary--100) 80%,
			var(--primary--200) 100%
		);
	}

	&__content {
		z-index: 2;
		padding: 24px 28px;
		border-radius: 20px 0 0 20px;
		display: flex;
		flex-direction: column;
		gap: 24px;

		@media (max-width: 992px) {
			padding: 16px 24px 24px 24px;
			width: 100%;
			order: 2;
			border-radius: 20px;
			gap: 20px;
		}
	}

	&__image {
		position: absolute;
		top: 0;
		right: 0;
		bottom: 0;
		width: auto;
		z-index: 1;

		@media (max-width: 992px) {
			display: none;
		}

		@media (max-width: 480px) {
			display: none;
		}
	}

	&__background-image {
		height: 100%;
		width: auto;
		object-fit: cover;
		object-position: center;
		border-radius: 0 20px 20px 0;

		@media (max-width: 992px) {
			display: none;
		}
	}
}

.content-wrapper {
	display: flex;
	width: 60%;
	flex-direction: column;
	gap: 8px;
}

.title-section {
	display: flex;
	align-items: center;
	gap: 16px;
}

.description-section {
	display: flex;
	flex-direction: column;
	gap: 4px;
}

.banner-button {
	align-self: flex-start;
	border-radius: 8px;

	&:deep(.h-button-v2) {
		padding: 6px 16px;
		border-radius: 8px;
		gap: 8px;

		&:hover {
			transform: translateY(-1px);
		}

		&:active {
			transform: translateY(0);
		}
	}

	@media (max-width: 768px) {
		align-self: stretch;
		width: 100%;

		&:deep(.h-button-v2) {
			padding: 12px 16px;
			width: 100%;
		}
	}
}
</style>
