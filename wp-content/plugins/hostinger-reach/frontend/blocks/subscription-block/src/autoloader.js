wp.domReady(() => {
	whenEditorIsReady().then(() => {
		let block = wp.blocks.createBlock('hostinger-reach/subscription', {});
		setTimeout(() => {
			wp.data.dispatch('core/block-editor').insertBlocks(block);
		}, 0);

	});

	function whenEditorIsReady() {
		return new Promise((resolve) => {
			const unsubscribe = wp.data.subscribe(() => {
				if (wp.data.select('core/editor').isCleanNewPost() || wp.data.select('core/block-editor').getBlockCount() > 0) {
					unsubscribe()
					resolve()
				}
			})
		})
	}
});



