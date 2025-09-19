import { useHToast } from '@hostinger/hcomponents';

let globalToastService: ReturnType<typeof useHToast> | null = null;

export const useToast = () => {
	if (!globalToastService) {
		globalToastService = useHToast();
	}

	const showSuccess = (message: string, sticky = false) => {
		globalToastService?.add({
			message,
			severity: 'success',
			sticky
		});
	};

	const showError = (message: string, sticky = false) => {
		globalToastService?.add({
			message,
			severity: 'failure',
			sticky
		});
	};

	const showInfo = (message: string, sticky = false) => {
		globalToastService?.add({
			message,
			severity: 'info',
			sticky
		});
	};

	const showWarning = (message: string, sticky = false) => {
		globalToastService?.add({
			message,
			severity: 'warning',
			sticky
		});
	};

	return {
		showSuccess,
		showError,
		showInfo,
		showWarning,
		toastService: globalToastService
	};
};
