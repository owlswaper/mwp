(() => {
	const trigger = document.querySelector('.single-product .bijan-show-price-chart');
	if (!trigger || !window.clozPriceHistoryAssets) return;

	let ready;
	let activated = false;

	function load(source) {
		return new Promise((resolve, reject) => {
			const script = document.createElement('script');
			script.src = source;
			script.onload = resolve;
			script.onerror = reject;
			document.head.appendChild(script);
		});
	}

	function prepare() {
		return ready || (ready = load(clozPriceHistoryAssets.chart).then(() => load(clozPriceHistoryAssets.history)));
	}

	function open(event) {
		if (activated) return;
		event.preventDefault();
		event.stopImmediatePropagation();
		activated = true;
		prepare().then(() => setTimeout(() => trigger.click(), 0));
	}

	trigger.addEventListener('pointerenter', prepare, { once: true });
	trigger.addEventListener('touchstart', prepare, { once: true, passive: true });
	trigger.addEventListener('click', open);
	trigger.dataset.clozPriceReady = 'true';
})();
