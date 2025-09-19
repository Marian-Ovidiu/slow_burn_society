document.addEventListener('DOMContentLoaded', function() {
	const translations = hostinger_reach_subscription_block_data.translations;
	const formSelector = '.hostinger-reach-block-subscription-form';
	const forms = document.querySelectorAll(formSelector);
	forms.forEach(form => {
		const messageEl = form.querySelector('.reach-subscription-message');
		form.addEventListener('submit', function(e) {
			e.preventDefault();

			const submitBtn = form.querySelector('button[type="submit"]');
			const formData = new FormData(form);
			const data = {};

			formData.forEach((value, key) => {
				if (key.includes('.')) {
					const [ mainKey, subkey ] = key.split('.');

					if ( ! data[mainKey] ) {
						data[mainKey] = {};
					}

					data[mainKey][subkey] = value;
				} else {
					data[key] = value;
				}
			});

			submitBtn.disabled = true;

			fetch(hostinger_reach_subscription_block_data.endpoint, {
				method: 'POST',
				headers: {
					'Content-Type': 'application/json',
					'X-WP-Nonce': hostinger_reach_subscription_block_data.nonce,
				},
				body: JSON.stringify(data)
			})
				.then(response => {
					messageEl.style.display = 'block';
					form.reset();

					if ( response.ok ) {
						messageEl.textContent = translations.thanks;
						const submitBtn = form.querySelector('.hostinger-reach-block-submit');
						submitBtn.style.display = 'none';
					} else {
						throw new Error(response.statusText);
					}

				})
				.catch(error => {
					messageEl.textContent = translations.error;
					messageEl.style.display = 'block';
					submitBtn.disabled = false;
				});
		});
	});
});
