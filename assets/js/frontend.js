(() => {
	'use strict';

	const PREFIX = '[SOPSR News Slider]';

	function log(debug, ...args) {
		if (debug && window.console && typeof window.console.info === 'function') {
			window.console.info(PREFIX, ...args);
		}
	}

	function fail(root, message, error) {
		root.classList.add('sopsr-news-slider--error');
		if (window.console && typeof window.console.error === 'function') {
			window.console.error(PREFIX, message, error || '');
		}
	}

	function initSlider(root) {
		if (!root || root.dataset.sopsrInitialized === '1') {
			return;
		}

		const configNode = root.querySelector('.sopsr-news-slider__config');
		if (!configNode) {
			fail(root, 'Missing JSON configuration.');
			return;
		}

		let config;
		try {
			config = JSON.parse(configNode.textContent || '{}');
		} catch (error) {
			fail(root, 'Invalid JSON configuration.', error);
			return;
		}

		const debug = Boolean(config.__sopsrDebug);
		delete config.__sopsrDebug;

		if (debug) {
			root.addEventListener('focusin', (event) => {
				const target = event.target;
				if (!(target instanceof Element)) {
					return;
				}

				log(true, 'Focus', {
					id: root.id,
					element: target.tagName.toLowerCase(),
					className: target.className || '',
					href: target.getAttribute('href') || '',
				});
			});
		}

		if (typeof window.Splide !== 'function') {
			fail(root, 'Splide library is not available. The first slide remains visible as a fallback.');
			return;
		}

		try {
			const splide = new window.Splide(root, config);

			splide.on('mounted', () => {
				root.dataset.sopsrInitialized = '1';
				log(debug, 'Mounted', {
					id: root.id,
					options: config,
					length: splide.length,
				});
			});

			splide.on('move', (newIndex, oldIndex, destIndex) => {
				log(debug, 'Move', { id: root.id, newIndex, oldIndex, destIndex });
			});

			splide.on('autoplay:play', () => log(debug, 'Autoplay started', { id: root.id }));
			splide.on('autoplay:pause', () => log(debug, 'Autoplay paused', { id: root.id }));
			splide.on('lazyload:loaded', (img) => log(debug, 'Lazy image loaded', { id: root.id, src: img.currentSrc || img.src || '' }));

			splide.mount();
			root._sopsrSplide = splide;
		} catch (error) {
			fail(root, 'Splide mount failed. The first slide remains visible as a fallback.', error);
		}
	}

	function initAll() {
		document.querySelectorAll('.sopsr-news-slider').forEach(initSlider);
	}

	if (document.readyState === 'loading') {
		document.addEventListener('DOMContentLoaded', initAll, { once: true });
	} else {
		initAll();
	}
})();
