(() => {
	const selector = '.single-product .quantity .plus-quantity, .single-product .quantity .minus-quantity';

	function labelQuantityButtons() {
		document.querySelectorAll(selector).forEach((button) => {
			button.setAttribute('aria-label', button.classList.contains('plus-quantity') ? 'افزایش تعداد' : 'کاهش تعداد');
		});
	}

	function fixGalleryRoles(gallery) {
		gallery.querySelectorAll('.woocommerce-product-gallery__image[role="group"]').forEach((slide) => slide.removeAttribute('role'));
	}

	function clearRelatedImageAlt() {
		document.querySelectorAll('.single-product .cloz-related .woocommerce-LoopProduct-link img').forEach((image) => image.setAttribute('alt', ''));
	}

	document.addEventListener('DOMContentLoaded', () => {
		labelQuantityButtons();
		clearRelatedImageAlt();

		const gallery = document.querySelector('.single-product .woocommerce-product-gallery');
		if (gallery) {
			fixGalleryRoles(gallery);
			new MutationObserver(() => fixGalleryRoles(gallery)).observe(gallery, { subtree: true, attributes: true, attributeFilter: ['role'] });
		}
	});
})();
