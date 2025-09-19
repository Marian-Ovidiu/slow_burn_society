import { ref } from 'vue';

const scrollLockCount = ref(0);
const originalBodyStyles = ref<{
	overflow: string;
	paddingRight: string;
} | null>(null);

export const useScrollLock = () => {
	const lockScroll = () => {
		scrollLockCount.value++;

		if (scrollLockCount.value === 1) {
			originalBodyStyles.value = {
				overflow: document.body.style.overflow,
				paddingRight: document.body.style.paddingRight
			};

			const scrollbarWidth = window.innerWidth - document.documentElement.clientWidth;

			document.body.style.overflow = 'hidden';
			if (scrollbarWidth > 0) {
				document.body.style.paddingRight = `${scrollbarWidth}px`;
			}
		}
	};

	const unlockScroll = () => {
		scrollLockCount.value = Math.max(0, scrollLockCount.value - 1);

		if (scrollLockCount.value === 0 && originalBodyStyles.value) {
			document.body.style.overflow = originalBodyStyles.value.overflow;
			document.body.style.paddingRight = originalBodyStyles.value.paddingRight;
			originalBodyStyles.value = null;
		}
	};

	return {
		lockScroll,
		unlockScroll,
		isLocked: () => scrollLockCount.value > 0
	};
};
