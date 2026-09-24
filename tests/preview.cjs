// Lightweight DOM harness for live updates without installing browser dependencies.
const assert = require('node:assert/strict');
const fs = require('node:fs');
const vm = require('node:vm');
const element = () => ({
	style: { setProperty(key, value) { this[key] = value; } },
	dataset: {}, listeners: {}, value: '', checked: false,
	addEventListener(type, callback) { this.listeners[type] = callback; }
});
const fields = {};
const parts = {};
for (const name of ['image', 'overlay', 'content', 'inner', 'title', 'cta']) {
	parts['.sopsr-preview-' + name] = element();
}
parts['.sopsr-news-slider__arrows'] = element();
parts['.splide__pagination'] = element();
const preview = element();
preview.querySelector = selector => parts[selector];
const stage = element();
stage.dataset.device = 'desktop';
const spacingTabs = ['desktop', 'tablet', 'mobile'].map(device => {
	const tab = element();
	tab.dataset.titleSpacingDevice = device;
	tab.classList = { toggle(name, active) { tab.active = active; } };
	tab.setAttribute = (name, value) => { tab[name] = value; };
	return tab;
});
const spacingPanels = ['desktop', 'tablet', 'mobile'].map(device => {
	const panel = element();
	panel.dataset.titleSpacingPanel = device;
	panel.hidden = device !== 'desktop';
	return panel;
});
const sections = [element(), element(), element()];
sections.forEach((section, index) => {
	section.id = 'sopsr-section-' + (index + 1);
	section.open = index === 0;
});
const form = element();
form.querySelectorAll = () => [];
const nestedFields = new Map();
form.querySelector = selector => {
	const match = selector.match(/^\[name="(.+)"\]$/);
	return match ? nestedFields.get(match[1]) || null : null;
};
const customStyle = element();
const document = {
	readyState: 'complete',
	getElementById(id) {
		return ({ 'sopsr-slider-settings-form': form, 'sopsr-slider-preview': preview, 'sopsr-preview-custom-css': customStyle })[id] || fields[id] || null;
	},
	querySelector: () => stage,
	querySelectorAll: selector => selector === '.sopsr-settings-section' ? sections : selector === '[data-title-spacing-device]' ? spacingTabs : selector === '[data-title-spacing-panel]' ? spacingPanels : []
};
const jquery = () => ({ wpColorPicker() {} });
const storage = initial => {
	const values = new Map(Object.entries(initial));
	return {
		getItem: key => values.has(key) ? values.get(key) : null,
		setItem: (key, value) => values.set(key, String(value)),
		removeItem: key => values.delete(key)
	};
};
const localStorage = storage({
	'sopsr-news-slider-accordion-state': JSON.stringify({
		'sopsr-section-1': false,
		'sopsr-section-2': true,
		'sopsr-section-3': false
	})
});
const sessionStorage = storage({ 'sopsr-news-slider-save-scroll': '735' });
let restoredScroll = null;
const window = {
	SOPSRSliderAdmin: {}, localStorage, sessionStorage, scrollY: 812,
	requestAnimationFrame: callback => callback(),
	scrollTo: options => { restoredScroll = options; },
	addEventListener() {},
	setTimeout: callback => callback()
};
const set = (key, value) => { fields['sopsr-' + key] = { value: String(value), checked: Boolean(value) }; };
const setNested = (device, key, value, checked = Boolean(value)) => {
	const name = `sopsr_news_slider_settings[responsive][${device}][${key}]`;
	nestedFields.set(name, { value: String(value), checked });
};
set('arrows', true);
set('pagination', true);
set('cta_enabled', true);
set('title_font_mode', 'clamp');
set('title_clamp_min', 1.5);
set('title_clamp_min_unit', 'rem');
set('title_clamp_max', 3);
set('title_clamp_max_unit', 'em');
set('title_clamp_fluid', 3);
set('cta_font_size', 1.25);
set('cta_font_size_unit', 'rem');
set('controls_color', '#123456');
set('controls_bg_color', '#abcdef');
set('controls_bg_opacity', 50);
set('controls_size', 60);
set('controls_size_unit', 'px');
set('content_max_width', 70);
set('content_max_width_unit', 'rem');
set('content_padding_x', 2);
set('content_padding_x_unit', 'rem');
set('content_padding_y', 1.5);
set('content_padding_y_unit', 'em');
set('title_max_width', 50);
set('title_max_width_unit', 'rem');
set('title_padding', 1);
set('title_padding_unit', 'rem');
set('title_margin', .5);
set('title_margin_unit', 'em');
set('title_padding_top', 4);
set('title_padding_top_unit', 'px');
set('title_padding_right', .5);
set('title_padding_right_unit', 'rem');
set('title_padding_bottom', 2);
set('title_padding_bottom_unit', 'em');
set('title_padding_left', 1);
set('title_padding_left_unit', 'px');
set('title_weight', 700);
set('title_line_height', 1.15);
set('title_text_shadow', true);
set('cta_border_width', 1);
set('cta_border_width_unit', 'px');
set('cta_border_radius', 4);
set('cta_border_radius_unit', 'px');
set('cta_font_weight', 600);
set('cta_padding_y', 12);
set('cta_padding_y_unit', 'px');
set('cta_padding_x', 22);
set('cta_padding_x_unit', 'px');
setNested('tablet', 'content_halign', 'left');
setNested('tablet', 'content_valign', 'bottom');
setNested('tablet', 'content_max_width', 42);
setNested('tablet', 'content_max_width_unit', 'rem');
setNested('tablet', 'content_padding_x', 3);
setNested('tablet', 'content_padding_x_unit', 'rem');
setNested('tablet', 'content_padding_y', 2);
setNested('tablet', 'content_padding_y_unit', 'em');
setNested('tablet', 'title_weight', 300);
setNested('tablet', 'title_line_height', 1.25);
setNested('tablet', 'title_max_width', 38);
setNested('tablet', 'title_max_width_unit', 'rem');
setNested('tablet', 'title_padding', 1.25);
setNested('tablet', 'title_padding_unit', 'em');
setNested('tablet', 'title_margin', .5);
setNested('tablet', 'title_margin_unit', 'rem');
for (const side of ['top', 'right', 'bottom', 'left']) {
	setNested('tablet', 'title_padding_' + side, 1.25);
	setNested('tablet', 'title_padding_' + side + '_unit', 'em');
}
setNested('tablet', 'title_text_shadow', 0, false);
setNested('tablet', 'cta_border_width', .1);
setNested('tablet', 'cta_border_width_unit', 'rem');
setNested('tablet', 'cta_border_radius', 1);
setNested('tablet', 'cta_border_radius_unit', 'em');
setNested('tablet', 'cta_font_size', 1.1);
setNested('tablet', 'cta_font_size_unit', 'rem');
setNested('tablet', 'cta_font_weight', 400);
setNested('tablet', 'cta_padding_y', .8);
setNested('tablet', 'cta_padding_y_unit', 'em');
setNested('tablet', 'cta_padding_x', 1.5);
setNested('tablet', 'cta_padding_x_unit', 'rem');
setNested('tablet', 'controls_size', 3);
setNested('tablet', 'controls_size_unit', 'rem');
setNested('mobile', 'content_halign', 'right');
setNested('mobile', 'content_valign', 'top');
setNested('mobile', 'content_max_width', 30);
setNested('mobile', 'content_max_width_unit', 'em');
setNested('mobile', 'content_padding_x', .5);
setNested('mobile', 'content_padding_x_unit', 'rem');
setNested('mobile', 'content_padding_y', 1);
setNested('mobile', 'content_padding_y_unit', 'em');
setNested('mobile', 'title_weight', 900);
setNested('mobile', 'title_line_height', 1.4);
setNested('mobile', 'title_max_width', 25);
setNested('mobile', 'title_max_width_unit', 'em');
setNested('mobile', 'title_padding', 8);
setNested('mobile', 'title_padding_unit', 'px');
setNested('mobile', 'title_margin', .25);
setNested('mobile', 'title_margin_unit', 'em');
for (const side of ['top', 'right', 'bottom', 'left']) {
	setNested('mobile', 'title_padding_' + side, 8);
	setNested('mobile', 'title_padding_' + side + '_unit', 'px');
}
setNested('mobile', 'title_text_shadow', 1, true);
setNested('mobile', 'cta_border_width', 2);
setNested('mobile', 'cta_border_width_unit', 'px');
setNested('mobile', 'cta_border_radius', .5);
setNested('mobile', 'cta_border_radius_unit', 'rem');
setNested('mobile', 'cta_font_size', .9);
setNested('mobile', 'cta_font_size_unit', 'em');
setNested('mobile', 'cta_font_weight', 800);
setNested('mobile', 'cta_padding_y', .5);
setNested('mobile', 'cta_padding_y_unit', 'rem');
setNested('mobile', 'cta_padding_x', .75);
setNested('mobile', 'cta_padding_x_unit', 'em');
setNested('mobile', 'controls_size', 2.5);
setNested('mobile', 'controls_size_unit', 'em');
set('cta_focus_text_color', '#fedcba');
set('cta_focus_bg_color', '#456789');
set('pagination_active_color', '#112233');
set('pagination_color', '#445566');
set('focus_color', '#ffcc00');
vm.runInNewContext(fs.readFileSync('assets/js/admin.js', 'utf8'), {
	document, jQuery: jquery, CSS: { escape: value => value },
	window
});
assert.equal(parts['.sopsr-preview-title'].style.padding, '4px 0.5rem 2em 1px');
assert.deepEqual(sections.map(section => section.open), [false, true, false]);
assert.equal(restoredScroll.top, 735);
assert.equal(sessionStorage.getItem('sopsr-news-slider-save-scroll'), null, 'Restored scroll is consumed once.');
sections[2].open = true;
sections[2].listeners.toggle();
assert.deepEqual(JSON.parse(localStorage.getItem('sopsr-news-slider-accordion-state')), {
	'sopsr-section-1': false,
	'sopsr-section-2': true,
	'sopsr-section-3': true
});
form.listeners.submit();
assert.equal(sessionStorage.getItem('sopsr-news-slider-save-scroll'), '812');
assert.equal(parts['.sopsr-preview-title'].style.fontSize, 'clamp(1.5rem,33px,3em)');
assert.equal(parts['.sopsr-preview-cta'].style.fontSize, '1.25rem');
assert.equal(preview.style['--sopsr-control-bg'], 'rgba(171,205,239,0.5)');
assert.equal(preview.style['--sopsr-control-size'], '60px');
assert.equal(preview.style['--sopsr-control-color'], '#123456');
assert.equal(preview.style['--sopsr-cta-focus-text'], '#fedcba');
assert.equal(preview.style['--sopsr-cta-focus-bg'], '#456789');
assert.equal(preview.style['--sopsr-page-active'], '#112233');
assert.equal(preview.style['--sopsr-page'], '#445566');
assert.equal(preview.style['--sopsr-focus'], '#ffcc00');
for (const [device, fluid] of [['tablet', 21.6], ['mobile', 11.25]]) {
	stage.dataset.device = device;
	form.listeners.change();
	assert.equal(parts['.sopsr-preview-title'].style.fontSize, `clamp(1.5rem,${fluid}px,3em)`);
}
stage.dataset.device = 'tablet';
form.listeners.change();
assert.equal(parts['.sopsr-preview-content'].style.justifyItems, 'start');
assert.equal(parts['.sopsr-preview-content'].style.alignItems, 'end');
assert.equal(parts['.sopsr-preview-content'].style.padding, '2em 3rem');
assert.equal(parts['.sopsr-preview-inner'].style.width, 'min(100%,42rem)');
assert.equal(parts['.sopsr-preview-title'].style.fontWeight, '300');
assert.equal(parts['.sopsr-preview-title'].style.lineHeight, '1.25');
assert.equal(parts['.sopsr-preview-title'].style.maxWidth, '38rem');
assert.equal(parts['.sopsr-preview-title'].style.padding, '1.25em 1.25em 1.25em 1.25em');
assert.equal(parts['.sopsr-preview-title'].style.margin, '0.5rem 0.5rem 0.5rem 0.5rem');
assert.equal(parts['.sopsr-preview-title'].style.textShadow, 'none');
assert.equal(parts['.sopsr-preview-cta'].style.borderWidth, '0.1rem');
assert.equal(parts['.sopsr-preview-cta'].style.borderRadius, '1em');
assert.equal(parts['.sopsr-preview-cta'].style.fontSize, '1.1rem');
assert.equal(parts['.sopsr-preview-cta'].style.fontWeight, '400');
assert.equal(parts['.sopsr-preview-cta'].style.padding, '0.8em 1.5rem');
assert.equal(preview.style['--sopsr-control-size'], '3rem');
stage.dataset.device = 'mobile';
form.listeners.change();
assert.equal(parts['.sopsr-preview-content'].style.justifyItems, 'end');
assert.equal(parts['.sopsr-preview-content'].style.alignItems, 'start');
assert.equal(parts['.sopsr-preview-content'].style.padding, '1em 0.5rem');
assert.equal(parts['.sopsr-preview-inner'].style.width, 'min(100%,30em)');
assert.equal(parts['.sopsr-preview-title'].style.fontWeight, '900');
assert.equal(parts['.sopsr-preview-title'].style.lineHeight, '1.4');
assert.equal(parts['.sopsr-preview-title'].style.maxWidth, '25em');
assert.equal(parts['.sopsr-preview-title'].style.padding, '8px 8px 8px 8px');
assert.equal(parts['.sopsr-preview-title'].style.margin, '0.25em 0.25em 0.25em 0.25em');
setNested('mobile', 'title_margin_top', 1);
setNested('mobile', 'title_margin_top_unit', 'rem');
setNested('mobile', 'title_margin_right', 2);
setNested('mobile', 'title_margin_right_unit', 'px');
setNested('mobile', 'title_margin_bottom', 3);
setNested('mobile', 'title_margin_bottom_unit', 'em');
setNested('mobile', 'title_margin_left', 4);
setNested('mobile', 'title_margin_left_unit', 'px');
form.listeners.change();
assert.equal(parts['.sopsr-preview-title'].style.margin, '1rem 2px 3em 4px');
assert.equal(parts['.sopsr-preview-title'].style.textShadow, '0 2px 8px rgba(0,0,0,.45)');
assert.equal(parts['.sopsr-preview-cta'].style.borderWidth, '2px');
assert.equal(parts['.sopsr-preview-cta'].style.borderRadius, '0.5rem');
assert.equal(parts['.sopsr-preview-cta'].style.fontSize, '0.9em');
assert.equal(parts['.sopsr-preview-cta'].style.fontWeight, '800');
assert.equal(parts['.sopsr-preview-cta'].style.padding, '0.5rem 0.75em');
assert.equal(preview.style['--sopsr-control-size'], '2.5em');
set('arrows', false);
set('pagination', false);
form.listeners.change();
assert.equal(parts['.sopsr-news-slider__arrows'].hidden, true);
assert.equal(parts['.splide__pagination'].hidden, true);
set('title_font_mode', 'responsive');
set('title_mobile', 2.5);
set('title_mobile_unit', 'em');
stage.dataset.device = 'mobile';
form.listeners.change();
assert.equal(parts['.sopsr-preview-title'].style.fontSize, '2.5em');
set('title_mobile_unit', 'invalid');
form.listeners.change();
assert.equal(parts['.sopsr-preview-title'].style.fontSize, '2.5px');
assert.equal(parts['.sopsr-preview-cta'].style.color, undefined, 'No inline color may override focus CSS.');
assert.equal(parts['.sopsr-preview-cta'].style.backgroundColor, undefined);
spacingTabs[1].listeners.click();
assert.equal(stage.dataset.device, 'tablet');
assert.deepEqual(spacingPanels.map(panel => panel.hidden), [true, false, true]);
assert.equal(spacingTabs[1]['aria-pressed'], 'true');
assert.match(
	fs.readFileSync('includes/class-sopsr-news-slider-admin.php', 'utf8'),
	/\$open = 'content' === \$id \? ' open' : '';/,
	'Only the first content accordion is server-rendered open by default.'
);
console.log('PASS: Desktop/Tablet/Mobile preview styles, units, accordion state and save scroll restoration.');
