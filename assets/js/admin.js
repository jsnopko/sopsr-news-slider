(function ($) {
	'use strict';

	const cfg = window.SOPSRSliderAdmin || {};
	const form = document.getElementById('sopsr-slider-settings-form');
	const preview = document.getElementById('sopsr-slider-preview');
	const stage = document.querySelector('.sopsr-preview-stage');

	if (!form) {
		return;
	}

	function byId(key) {
		return document.getElementById('sopsr-' + key);
	}

	function nested(path) {
		const name = 'sopsr_news_slider_settings' + path.map(part => '[' + part + ']').join('');
		return form.querySelector('[name="' + CSS.escape(name) + '"]');
	}

	function val(key, fallback) {
		const el = byId(key);
		return el ? el.value : fallback;
	}

	function checked(key) {
		const el = byId(key);
		return Boolean(el && el.checked);
	}

	function num(value, fallback) {
		const n = Number.parseFloat(value);
		return Number.isFinite(n) ? n : fallback;
	}

	function fontSize(key, fallback) {
		const unit = val(key + '_unit', 'px');
		return Math.max(.01, num(val(key, fallback), fallback)) + (['px', 'rem', 'em'].includes(unit) ? unit : 'px');
	}

	function hexToRgba(hex, alpha) {
		let value = String(hex || '#000000').replace('#', '').trim();
		if (value.length === 3) {
			value = value.split('').map(char => char + char).join('');
		}
		if (!/^[0-9a-f]{6}$/i.test(value)) {
			value = '000000';
		}
		const r = parseInt(value.slice(0, 2), 16);
		const g = parseInt(value.slice(2, 4), 16);
		const b = parseInt(value.slice(4, 6), 16);
		return 'rgba(' + r + ',' + g + ',' + b + ',' + Math.max(0, Math.min(1, alpha)) + ')';
	}

	function activeDevice() {
		return stage ? stage.dataset.device || 'desktop' : 'desktop';
	}

	function nestedValue(device, key, fallback) {
		const el = nested(['responsive', device, key]);
		return el ? el.value : fallback;
	}

	function updateRangeOutputs() {
		form.querySelectorAll('.sopsr-range input[type="range"]').forEach(input => {
			const output = input.closest('.sopsr-range')?.querySelector('output');
			if (output) {
				const suffix = output.textContent.replace(/[-\d.]/g, '');
				output.textContent = input.value + suffix;
			}
		});
	}

	function updatePreview() {
		if (!preview || !stage) {
			return;
		}

		const device = activeDevice();
		const representativeWidth = device === 'mobile' ? 375 : device === 'tablet' ? 720 : 1100;
		// Share frontend control and CTA rules; only the variable values change here.
		const colors = {
			'cta-text': 'cta_text_color', 'cta-bg': 'cta_bg_color',
			'cta-hover-text': 'cta_hover_text_color', 'cta-hover-bg': 'cta_hover_bg_color',
			'cta-focus-text': 'cta_focus_text_color', 'cta-focus-bg': 'cta_focus_bg_color',
			'control-color': 'controls_color', 'page-active': 'pagination_active_color',
			'page': 'pagination_color', 'focus': 'focus_color'
		};
		Object.entries(colors).forEach(([variable, key]) => {
			preview.style.setProperty('--sopsr-' + variable, val(key, cfg.defaults?.[key]));
		});
		preview.style.setProperty('--sopsr-control-bg', hexToRgba(val('controls_bg_color', '#000000'), num(val('controls_bg_opacity', 35), 35) / 100));
		preview.style.setProperty('--sopsr-control-size', num(val('controls_size', 46), 46) + 'px');
		preview.querySelector('.sopsr-news-slider__arrows').hidden = !checked('arrows');
		preview.querySelector('.splide__pagination').hidden = !checked('pagination');
		const image = preview.querySelector('.sopsr-preview-image');
		const overlay = preview.querySelector('.sopsr-preview-overlay');
		const content = preview.querySelector('.sopsr-preview-content');
		const title = preview.querySelector('.sopsr-preview-title');
		const cta = preview.querySelector('.sopsr-preview-cta');

		const widthValue = num(nestedValue(device, 'width_value', 100), 100);
		const widthUnit = nestedValue(device, 'width_unit', '%');
		if (widthUnit === '%') {
			preview.style.width = Math.min(100, widthValue) + '%';
		} else if (widthUnit === 'vw') {
			preview.style.width = Math.min(100, widthValue) + '%';
		} else {
			preview.style.width = Math.min(100, Math.max(20, widthValue / representativeWidth * 100)) + '%';
		}

		const mode = nestedValue(device, 'height_mode', 'fixed');
		preview.style.aspectRatio = 'auto';
		if (mode === 'aspect') {
			const w = Math.max(.1, num(nestedValue(device, 'aspect_w', 12), 12));
			const h = Math.max(.1, num(nestedValue(device, 'aspect_h', 5), 5));
			preview.style.height = 'auto';
			preview.style.aspectRatio = w + ' / ' + h;
		} else if (mode === 'auto') {
			preview.style.height = device === 'mobile' ? '260px' : '300px';
		} else if (mode === 'clamp') {
			const min = num(nestedValue(device, 'clamp_min', 320), 320);
			const fluid = num(nestedValue(device, 'clamp_fluid', 50), 50);
			const max = num(nestedValue(device, 'clamp_max', 620), 620);
			const px = Math.max(min, Math.min(max, representativeWidth * fluid / 100));
			preview.style.height = Math.max(180, Math.min(420, px * .62)) + 'px';
		} else if (mode === 'viewport') {
			const vh = Math.max(1, Math.min(100, num(nestedValue(device, 'height_value', 80), 80)));
			preview.style.height = Math.max(180, 420 * vh / 100) + 'px';
		} else {
			const value = num(nestedValue(device, 'height_value', 620), 620);
			const unit = nestedValue(device, 'height_unit', 'px');
			const px = unit === 'vw' ? representativeWidth * value / 100 : unit === 'vh' ? 800 * value / 100 : value;
			preview.style.height = Math.max(180, Math.min(420, px * .62)) + 'px';
		}

		if (image) {
			image.style.objectFit = nestedValue(device, 'fit', 'cover');
			image.style.objectPosition =
				num(nestedValue(device, 'position_x', 50), 50) + '% ' +
				num(nestedValue(device, 'position_y', 50), 50) + '%';
			image.style.backgroundColor = val('image_background_color', '#111111');
		}

		if (overlay) {
			const color = val('overlay_color', '#000000');
			const opacity = num(val('overlay_opacity', 42), 42) / 100;
			const type = val('overlay_type', 'solid');
			const solid = hexToRgba(color, opacity);
			const light = hexToRgba(color, opacity * .2);
			if (type === 'none') {
				overlay.style.background = 'transparent';
			} else if (type === 'gradient-top-bottom') {
				overlay.style.background = 'linear-gradient(to bottom,' + light + ',' + solid + ')';
			} else if (type === 'gradient-bottom-top') {
				overlay.style.background = 'linear-gradient(to top,' + light + ',' + solid + ')';
			} else {
				overlay.style.background = solid;
			}
		}

		if (content) {
			const h = val('content_halign', 'center');
			const v = val('content_valign', 'center');
			content.style.justifyItems = h === 'left' ? 'start' : h === 'right' ? 'end' : 'center';
			content.style.alignItems = v === 'top' ? 'start' : v === 'bottom' ? 'end' : 'center';
			content.style.textAlign = h;
			content.style.padding =
				Math.max(0, num(val('content_padding_y', 32), 32)) + 'px ' +
				Math.max(0, num(val('content_padding_x', 32), 32)) + 'px';
		}

		if (title) {
			title.style.color = val('title_color', '#ffffff');
			title.style.fontWeight = val('title_weight', '700');
			title.style.lineHeight = val('title_line_height', '1.15');
			title.style.maxWidth = num(val('title_max_width', 900), 900) + 'px';
			title.style.textShadow = checked('title_text_shadow') ? '0 2px 8px rgba(0,0,0,.45)' : 'none';

			if (val('title_font_mode', 'clamp') === 'clamp') {
				title.style.fontSize =
					'clamp(' + fontSize('title_clamp_min', 26) + ',' +
					(representativeWidth * num(val('title_clamp_fluid', 3.1), 3.1) / 100) + 'px,' +
					fontSize('title_clamp_max', 52) + ')';
			} else {
				const key = device === 'mobile' ? 'title_mobile' : device === 'tablet' ? 'title_tablet' : 'title_desktop';
				title.style.fontSize = fontSize(key, 42);
			}
		}

		if (cta) {
			const enabled = checked('cta_enabled');
			cta.style.display = enabled ? 'inline-flex' : 'none';
			cta.style.borderColor = val('cta_border_color', '#2f6f3e');
			cta.style.borderWidth = num(val('cta_border_width', 1), 1) + 'px';
			cta.style.borderRadius = num(val('cta_border_radius', 4), 4) + 'px';
			cta.style.fontSize = fontSize('cta_font_size', 16);
			cta.style.fontWeight = val('cta_font_weight', '600');
			cta.style.padding =
				num(val('cta_padding_y', 12), 12) + 'px ' +
				num(val('cta_padding_x', 22), 22) + 'px';

			const skCta = nested(['translations', 'sk', 'cta_text']);
			const anyCta = form.querySelector('[name*="[translations]"][name$="[cta_text]"]');
			cta.textContent = (skCta || anyCta)?.value || 'Viac informácií';
		}

		const customCss = val('custom_css', '');
		let style = document.getElementById('sopsr-preview-custom-css');
		if (!style) {
			style = document.createElement('style');
			style.id = 'sopsr-preview-custom-css';
			document.head.appendChild(style);
		}
		style.textContent = String(customCss || '').replaceAll('{{slider}}', '#sopsr-slider-preview').replaceAll('{{id}}', '#sopsr-slider-preview');
	}

	function restoreElement(el) {
		if (!el || !el.hasAttribute('data-default')) {
			return;
		}
		const raw = el.getAttribute('data-default');

		if (el.matches('input[type="checkbox"]')) {
			el.checked = raw === '1' || raw === 'true';
		} else if (el.matches('select[multiple]')) {
			let defaults = [];
			try {
				defaults = JSON.parse(raw || '[]').map(String);
			} catch (e) {
				defaults = [];
			}
			Array.from(el.options).forEach(option => {
				option.selected = defaults.includes(String(option.value));
			});
		} else {
			el.value = raw;
		}

		if (el.classList.contains('sopsr-color-picker') && $.fn.wpColorPicker) {
			try {
				$(el).wpColorPicker('color', raw);
			} catch (e) {
				// The normal value above is enough if the picker is not initialized yet.
			}
		}

		el.dispatchEvent(new Event('change', { bubbles: true }));
		el.dispatchEvent(new Event('input', { bubbles: true }));
	}

	function resetContainer(container) {
		container.querySelectorAll('[data-default]').forEach(restoreElement);
		updateRangeOutputs();
		updatePreview();
	}

	$('.sopsr-color-picker').wpColorPicker({
		change: function () {
			window.setTimeout(updatePreview, 0);
		},
		clear: function () {
			window.setTimeout(updatePreview, 0);
		}
	});

	form.addEventListener('input', () => {
		updateRangeOutputs();
		updatePreview();
	});
	form.addEventListener('change', updatePreview);
	preview?.querySelector('.sopsr-preview-cta')?.addEventListener('click', event => event.preventDefault());

	document.querySelectorAll('.sopsr-preview-devices [data-preview-device]').forEach(button => {
		button.addEventListener('click', () => {
			document.querySelectorAll('.sopsr-preview-devices [data-preview-device]').forEach(item => item.classList.remove('is-active'));
			button.classList.add('is-active');
			stage.dataset.device = button.dataset.previewDevice;
			updatePreview();
		});
	});

	document.querySelectorAll('.sopsr-reset-section').forEach(button => {
		button.addEventListener('click', event => {
			event.preventDefault();
			event.stopPropagation();
			const section = button.closest('.sopsr-settings-section');
			if (section && window.confirm(cfg.confirmReset || 'Reset section?')) {
				resetContainer(section);
			}
		});
	});

	document.getElementById('sopsr-reset-all')?.addEventListener('click', () => {
		if (window.confirm(cfg.confirmAll || 'Reset all settings?')) {
			resetContainer(form);
		}
	});

	document.querySelectorAll('.sopsr-media-select').forEach(button => {
		button.addEventListener('click', event => {
			event.preventDefault();
			const field = button.closest('.sopsr-media-field');
			const input = field?.closest('.sopsr-field__control')?.querySelector('input[type="hidden"][id^="sopsr-"]');
			if (!input || !window.wp || !wp.media) {
				return;
			}
			const frame = wp.media({
				title: cfg.mediaTitle || 'Select image',
				button: { text: cfg.mediaButton || 'Use image' },
				multiple: false,
				library: { type: 'image' }
			});
			frame.on('select', () => {
				const attachment = frame.state().get('selection').first().toJSON();
				input.value = attachment.id || 0;
				const previewBox = field.querySelector('.sopsr-media-preview');
				const url = attachment.sizes?.medium?.url || attachment.sizes?.large?.url || attachment.url;
				if (previewBox) {
					previewBox.innerHTML = url ? '<img src="' + String(url).replace(/"/g, '&quot;') + '" alt="">' : '';
				}
				input.dispatchEvent(new Event('change', { bubbles: true }));
			});
			frame.open();
		});
	});

	document.querySelectorAll('.sopsr-media-remove').forEach(button => {
		button.addEventListener('click', event => {
			event.preventDefault();
			const field = button.closest('.sopsr-media-field');
			const input = field?.closest('.sopsr-field__control')?.querySelector('input[type="hidden"][id^="sopsr-"]');
			if (input) {
				input.value = '0';
				field.querySelector('.sopsr-media-preview').innerHTML = '';
				input.dispatchEvent(new Event('change', { bubbles: true }));
			}
		});
	});

	updateRangeOutputs();
	updatePreview();
})(jQuery);
