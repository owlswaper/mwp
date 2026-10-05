(() => {
	'use strict';
	const motion = window.matchMedia('(prefers-reduced-motion: reduce)');
	const hover = window.matchMedia('(hover: hover)');
	const controllers = new Map();

	const initialize = section => {
		if (controllers.has(section)) return;
		const rail = section.querySelector('.cloz-discovery-rail');
		const cards = Array.from(rail?.querySelectorAll('.cloz-discovery-card') || []);
		if (!rail || cards.length < 2) return;
		const actions = section.querySelector('.cloz-discovery-actions');
		const prev = section.querySelector('.cloz-discovery-prev');
		const next = section.querySelector('.cloz-discovery-next');
		const pause = section.querySelector('.cloz-discovery-pause');
		if (!actions || !prev || !next || !pause) return;
		const abort = new AbortController();
		const listen = (target, event, fn, options = {}) => target.addEventListener(event, fn, { ...options, signal: abort.signal });
		let timer = 0, frame = 0, direction = 1;
		let visible = false, overflow = false, paused = false, hovered = false, focused = false, touching = false;
		hovered = hover.matches && section.matches(':hover');
		focused = section.contains(document.activeElement);
		let resumeAt = 0;
		const rtl = getComputedStyle(rail).direction === 'rtl';

		const geometry = () => {
			const bounds = rail.getBoundingClientRect();
			const first = cards[0].getBoundingClientRect();
			const last = cards[cards.length - 1].getBoundingClientRect();
			return {
				bounds,
				start: rtl ? first.right <= bounds.right - 1 : first.left >= bounds.left + 1,
				end: rtl ? last.left >= bounds.left + 1 : last.right <= bounds.right - 1
			};
		};
		const stop = () => { clearTimeout(timer); timer = 0; };
		const canPlay = () => visible && overflow && !paused && !hovered && !focused && !touching && !motion.matches && !document.hidden;
		const schedule = () => {
			stop();
			if (!canPlay()) return;
			timer = setTimeout(() => {
				if (!canPlay()) return;
				const position = geometry();
				if (position.end) direction = -1;
				else if (position.start) direction = 1;
				move(direction, false);
				schedule();
			}, Math.max(3000, resumeAt - Date.now()));
		};
		const update = () => {
			overflow = rail.scrollWidth > rail.clientWidth + 2;
			actions.hidden = !overflow;
			const position = geometry();
			prev.disabled = position.start;
			next.disabled = position.end;
			pause.hidden = motion.matches;
			pause.setAttribute('aria-pressed', String(paused));
			pause.setAttribute('aria-label', paused ? 'ادامهٔ حرکت خودکار دسته‌ها' : 'توقف حرکت خودکار دسته‌ها');
			pause.querySelector('[data-discovery-pause-icon]').toggleAttribute('hidden', paused);
			pause.querySelector('[data-discovery-play-icon]').toggleAttribute('hidden', !paused);
		};
		const move = (step, manual = true) => {
			const bounds = rail.getBoundingClientRect();
			const edge = rtl ? bounds.right - 3 : bounds.left + 3;
			let index = 0, distance = Infinity;
			cards.forEach((card, i) => {
				const box = card.getBoundingClientRect();
				const delta = Math.abs((rtl ? box.right : box.left) - edge);
				if (delta < distance) { distance = delta; index = i; }
			});
			const target = cards[Math.max(0, Math.min(cards.length - 1, index + step))].getBoundingClientRect();
			rail.scrollBy({ left: (rtl ? target.right : target.left) - edge, behavior: motion.matches ? 'instant' : 'smooth' });
			if (manual) { resumeAt = Date.now() + 10000; schedule(); }
		};
		listen(prev, 'click', () => move(-1));
		listen(next, 'click', () => move(1));
		listen(pause, 'click', () => { paused = !paused; update(); schedule(); });
		listen(section, 'pointerenter', () => { if (hover.matches) { hovered = true; stop(); } });
		listen(section, 'pointerleave', () => { hovered = false; schedule(); });
		listen(section, 'focusin', () => { focused = true; stop(); });
		listen(section, 'focusout', event => { if (!section.contains(event.relatedTarget)) { focused = false; schedule(); } });
		listen(rail, 'pointerdown', () => { touching = true; resumeAt = Date.now() + 10000; stop(); }, { passive: true });
		const release = () => { if (touching) { touching = false; resumeAt = Date.now() + 10000; schedule(); } };
		listen(window, 'pointerup', release, { passive: true });
		listen(window, 'pointercancel', release, { passive: true });
		listen(rail, 'wheel', () => { resumeAt = Date.now() + 10000; schedule(); }, { passive: true });
		listen(rail, 'scroll', () => {
			if (frame) return;
			frame = requestAnimationFrame(() => { frame = 0; update(); });
		}, { passive: true });
		listen(rail, 'keydown', event => {
			if (event.target !== rail) return;
			if (!['ArrowLeft', 'ArrowRight'].includes(event.key)) return;
			event.preventDefault();
			move(event.key === (rtl ? 'ArrowLeft' : 'ArrowRight') ? 1 : -1);
		});
		listen(document, 'visibilitychange', schedule);
		listen(motion, 'change', () => { update(); schedule(); });
		const resize = 'ResizeObserver' in window ? new ResizeObserver(() => { update(); schedule(); }) : null;
		resize?.observe(rail);
		if (!resize) listen(window, 'resize', () => { update(); schedule(); }, { passive: true });
		const observer = 'IntersectionObserver' in window ? new IntersectionObserver(entries => {
			visible = entries.some(entry => entry.isIntersecting);
			schedule();
		}, { threshold: .25 }) : null;
		observer?.observe(section);
		// Without visibility detection the manual rail remains fully usable.
		update();
		controllers.set(section, () => { stop(); cancelAnimationFrame(frame); abort.abort(); resize?.disconnect(); observer?.disconnect(); });
	};
	const scan = () => {
		controllers.forEach((destroy, section) => { if (!section.isConnected) { destroy(); controllers.delete(section); } });
		document.querySelectorAll('.cloz-category-discovery').forEach(initialize);
	};
	if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', scan, { once: true });
	else scan();
	document.body.addEventListener('cloz_archive_updated', scan);
})();
