<script lang="ts" setup>
import { computed, defineAsyncComponent } from 'vue';

import { useModal } from '@/composables/useModal';
import { useModalStore } from '@/stores/modalStore';

const { closeModal } = useModal();
const modalStore = useModalStore();
const activeModal = computed(() => modalStore.activeModal);

const modalComponent = computed(() => {
	if (!activeModal.value) return null;

	return defineAsyncComponent(() => import(`@/components/Modals/${activeModal.value?.name}.vue`));
});
</script>

<template>
	<Transition name="fade">
		<div v-if="activeModal" class="base-modal__wrapper">
			<div
				class="base-modal__container"
				:class="{
					'base-modal__container--xxl': activeModal.settings?.isXXL,
					'base-modal__container--xl': activeModal.settings?.isXL,
					'base-modal__container--lg': activeModal.settings?.isLG
				}"
			>
				<button
					v-if="activeModal.settings?.hasCloseButton"
					type="button"
					class="base-modal__close-button"
					@click="closeModal"
				>
					<HIcon name="ic-close-24" color="gray" />
				</button>
				<div
					class="base-modal__content-container"
					:class="{
						'base-modal__content-container--no-content-padding': activeModal.settings?.noContentPadding,
						'base-modal__content-container--no-border': activeModal.settings?.noBorder
					}"
				>
					<Component :is="modalComponent" v-bind="activeModal?.data || {}" />
				</div>
			</div>
		</div>
	</Transition>
</template>

<style lang="scss" scoped>
$modal-z-index: 9999;

.base-modal {
	&__wrapper {
		--modal-backdrop: rgba(0, 0, 0, 0.3);

		overflow: auto;
		position: fixed;
		z-index: $modal-z-index;
		height: 100%;
		inset: 0;
		background-color: var(--modal-backdrop);
		display: flex;
		align-items: center;
		justify-content: center;

		&.is-above-intercom {
			z-index: var(--z-index-intercom-2);
		}
	}

	&__close-button {
		position: absolute;
		top: 20px;
		right: 20px;
		z-index: 10;
		background: transparent;
		border: none;
		cursor: pointer;
		display: flex;
		align-items: center;
		justify-content: center;
		width: 32px;
		height: 32px;
		border-radius: 8px;
		transition: background-color 0.2s ease;

		&:hover {
			background-color: var(--neutral--100);
		}

		&:active {
			background-color: var(--neutral--200);
		}

		&:focus {
			outline: 2px solid var(--primary--500);
			outline-offset: 2px;
		}
	}

	&__container {
		position: relative;
		max-height: calc(100vh - 6rem);
		width: 100%;
		max-width: 500px;

		&--auto-width {
			max-width: none !important;
			width: auto;
		}

		&--lg {
			max-width: 600px;
		}

		&--xl {
			max-width: 800px;
		}

		&--xxl {
			max-width: 964px;
		}
	}

	&__content-container {
		border: 1px solid var(--neutral--200);
		border-radius: 16px;
		background-color: var(--neutral--0);
		padding: 24px;

		&--no-content-padding {
			padding: 0;
		}

		&--no-border {
			border: none;
		}
	}
}
</style>
