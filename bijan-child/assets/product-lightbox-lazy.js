(() => {
	if (typeof window.checkSliders === 'function') {
		document.removeEventListener('DOMContentLoaded', window.checkSliders);
		document.addEventListener('DOMContentLoaded', () => {
			requestAnimationFrame(() => requestAnimationFrame(window.checkSliders));
		});
	}

	if (window.clozLightboxEnabled !== '1' || !window.clozLightboxAssets) return;

	let ready;
	const loadStyle = (source) => new Promise((resolve, reject) => {
		const link = document.createElement('link');
		link.rel = 'stylesheet';
		link.href = source;
		link.onload = resolve;
		link.onerror = reject;
		document.head.appendChild(link);
	});
	const loadScript = (source) => new Promise((resolve, reject) => {
		const script = document.createElement('script');
		script.src = source;
		script.onload = resolve;
		script.onerror = reject;
		document.head.appendChild(script);
	});
	const prepare = () => ready || (ready = Promise.all(clozLightboxAssets.styles.map(loadStyle))
		.then(() => clozLightboxAssets.scripts.reduce(
			(chain, source) => chain.then(() => loadScript(source)),
			Promise.resolve()
		)));

	document.querySelectorAll('.product-main-slider').forEach((slider) => {
		let gallery;
		const init = () => {
			if (gallery) return gallery;
			const settings = {
				zoomFromOrigin: true,
				preload: 0,
				selector: 'a',
				plugins: [lgZoom, lgVideo],
				videojs: true,
				download: bijanWCSingle.lightboxDownload === '1',
				thumbnail: bijanWCSingle.lightboxThumb === '1',
				fullScreen: bijanWCSingle.lightboxFullscreen === '1',
				rotateLeft: bijanWCSingle.lightboxRotate === '1',
				rotateRight: bijanWCSingle.lightboxRotate === '1',
			};

			if (settings.thumbnail) settings.plugins.push(lgThumbnail);
			if (settings.fullScreen) settings.plugins.push(lgFullscreen);
			if (settings.rotateLeft) settings.plugins.push(lgRotate);
			gallery = lightGallery(slider, settings);
			return gallery;
		};

		slider.addEventListener('pointerdown', prepare, { once: true, passive: true });
		slider.addEventListener('click', (event) => {
			const link = event.target.closest('a');
			if (gallery || !link) return;
			event.preventDefault();
			prepare().then(init).then(() => {
				requestAnimationFrame(() => link.click());
			});
		}, true);
		slider.dataset.clozLightboxReady = 'true';
	});
})();
