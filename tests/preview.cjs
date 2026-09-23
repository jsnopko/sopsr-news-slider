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
for (const name of ['image', 'overlay', 'content', 'title', 'cta']) {
	parts['.sopsr-preview-' + name] = element();
}
parts['.sopsr-news-slider__arrows'] = element();
parts['.splide__pagination'] = element();
const preview = element();
preview.querySelector = selector => parts[selector];
const stage = element();
stage.dataset.device = 'desktop';
const sections = [element(), element(), element()];
sections.forEach((section, index) => {
	section.id = 'sopsr-section-' + (index + 1);
	section.open = index === 0;
});
const form = element();
form.querySelectorAll = () => [];
form.querySelector = () => null;
const customStyle = element();
const document = {
	readyState: 'complete',
	getElementById(id) {
		return ({ 'sopsr-slider-settings-form': form, 'sopsr-slider-preview': preview, 'sopsr-preview-custom-css': customStyle })[id] || fields[id] || null;
	},
	querySelector: () => stage,
	querySelectorAll: selector => selector === '.sopsr-settings-section' ? sections : []
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
set('cta_focus_text_color', '#fedcba');
set('cta_focus_bg_color', '#456789');
set('pagination_active_color', '#112233');
set('pagination_color', '#445566');
set('focus_color', '#ffcc00');
vm.runInNewContext(fs.readFileSync('assets/js/admin.js', 'utf8'), {
	document, jQuery: jquery, CSS: { escape: value => value },
	window
});
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
set('arrows', false);
set('pagination', false);
form.listeners.change();
assert.equal(parts['.sopsr-news-slider__arrows'].hidden, true);
assert.equal(parts['.splide__pagination'].hidden, true);
set('title_font_mode', 'responsive');
set('title_mobile', 2.5);
set('title_mobile_unit', 'em');
form.listeners.change();
assert.equal(parts['.sopsr-preview-title'].style.fontSize, '2.5em');
set('title_mobile_unit', 'invalid');
form.listeners.change();
assert.equal(parts['.sopsr-preview-title'].style.fontSize, '2.5px');
assert.equal(parts['.sopsr-preview-cta'].style.color, undefined, 'No inline color may override focus CSS.');
assert.equal(parts['.sopsr-preview-cta'].style.backgroundColor, undefined);
assert.match(
	fs.readFileSync('includes/class-sopsr-news-slider-admin.php', 'utf8'),
	/\$open = 'content' === \$id \? ' open' : '';/,
	'Only the first content accordion is server-rendered open by default.'
);
console.log('PASS: preview updates, device modes, font units, accordion state and save scroll restoration.');
