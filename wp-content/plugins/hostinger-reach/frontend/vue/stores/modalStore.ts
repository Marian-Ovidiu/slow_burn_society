import { defineStore } from 'pinia';
import { ref } from 'vue';

import type { ModalName } from '@/types/enums';
import type { ModalContent, ModalSettings } from '@/types/models';

export const useModalStore = defineStore('modalStore', () => {
	interface Modal {
		name: ModalName;
		data?: ModalContent;
		settings?: ModalSettings;
	}

	const activeModal = ref<Modal | null>(null);

	const openModal = (name: ModalName, data?: ModalContent, settings?: ModalSettings) => {
		activeModal.value = { name, data, settings };
	};

	const closeModal = () => (activeModal.value = null);

	return { activeModal, openModal, closeModal };
});
