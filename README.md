# ŠOP SR News Slider

Custom WordPress hero/news slider for ŠOP SR.

## Requirements

- WordPress 6.x
- PHP 8.1+ (tested syntactically with PHP available in build environment; target server uses PHP 8.4)
- Optional: Polylang
- Splide 4.1.4 (bundled when vendor files are present; otherwise local-cache/CDN fallback)

## Installation

1. Upload the plugin ZIP in **Plugins → Add New → Upload Plugin**.
2. Activate **ŠOP SR News Slider**.
3. Open **ŠOP SR Slider** in the WordPress admin.
4. Configure the slider.
5. Place the shortcode:

```text
[sopsr_news_slider]
```

on the homepage.

## Splide loading

The plugin supports three sources, in this order:

1. bundled files in `assets/vendor/splide/` if present,
2. a local cached copy in WordPress uploads,
3. jsDelivr CDN fallback.

On the settings page, Diagnostics shows the active source. If CDN is active, use **Stiahnuť Splide lokálne** to download Splide 4.1.4 into WordPress uploads. The frontend then switches to the local copy automatically. Source-map comments are removed from the locally cached minified files to avoid unnecessary `.map` 404 warnings in DevTools.

This makes the ZIP immediately testable while still allowing a fully local production setup.

## Content query

- published WordPress posts,
- current Polylang language when Polylang is active,
- configurable post count,
- include categories (optional),
- exclude multiple categories,
- translated Polylang category IDs are mapped automatically,
- pinned post IDs can be displayed first (Polylang translations are resolved when available),
- configurable behavior when featured image is missing.

## Responsive images and layout

Desktop / Tablet / Mobile each have:

- width and unit,
- height mode:
  - fixed,
  - auto,
  - aspect ratio,
  - clamp,
  - viewport,
- image `object-fit`,
- image X/Y position.

The first hero image is loaded eagerly with high fetch priority. Other images can use native WordPress lazy loading or Splide lazy loading.

## Accessibility

The plugin deliberately keeps these behaviors enabled:

- `pauseOnHover`,
- `pauseOnFocus`,
- pagination keyboard support,
- Splide live region,
- `prefers-reduced-motion`,
- visible focus styles,
- accessible arrow / pagination / autoplay labels,
- Pause/Play control whenever autoplay is enabled.

The CTA visible text and its screen-reader prefix are configurable per language. The accessible label is generated as:

```text
{configured prefix} {post title}
```

The optional `title` attribute uses the same value, but accessibility does not rely on `title`.

### Keyboard test before production

Test at minimum:

- Tab / Shift+Tab,
- Enter,
- Space,
- arrow keys while focus is inside the slider,
- pagination keyboard controls,
- first/last slide with loop OFF,
- loop ON,
- autoplay Pause/Play,
- `prefers-reduced-motion`.

## Logging / diagnostics

Debug logging is enabled by default for testing.

The plugin writes a private technical log under WordPress uploads and also captures fatal PHP errors originating inside this plugin:

```text
wp-content/uploads/sopsr-news-slider-private/sopsr-news-slider.log
```

The directory receives an Apache `.htaccess` deny rule and an `index.php`.

The admin Diagnostics panel shows the latest lines and has a Clear Log button.

Frontend debug output can also be enabled/disabled in settings and appears in the browser console with prefix:

```text
[SOPSR News Slider]
```

No passwords or form data are intentionally logged.

## Admin live preview

The settings page contains a lightweight live preview with Desktop / Tablet / Mobile switches. It previews:

- responsive size,
- object-fit / object-position,
- overlay and gradient,
- title size / color / weight / line-height,
- CTA normal / hover / focus styling,
- navigation arrows and pagination colors,
- content alignment,
- custom CSS using `{{slider}}`.

The preview uses representative Desktop (1100px), Tablet (720px) and Mobile (375px) widths for fluid typography and approximates dimensions to fit the sidebar. Arrow/pagination visibility and control size are global settings; dot size and spacing are fixed frontend styles. Date/excerpt font sizes are not configurable.

The preview is not a full Splide instance. The public frontend uses real Splide.

## Custom CSS

Use the token:

```css
{{slider}} .sopsr-news-slider__title {
    letter-spacing: .01em;
}
```

At render time `{{slider}}` is replaced by the current slider root ID.

The first slider uses:

```text
#sopsr-news-slider
```

Additional instances receive numbered IDs.

## Important notes

- `fade` does not use Splide `loop` type; if Loop is enabled with Fade, the plugin uses `rewind` so the experience still wraps.
- With only one returned post, autoplay, arrows, drag and pagination are automatically disabled.
- If JavaScript or Splide fails, CSS leaves the first slide visible as a graceful fallback.
- Wheel navigation defaults to OFF because it can interfere with normal page scrolling.
- Risky/internal Splide settings such as `clones`, `classes` and `focusableNodes` are intentionally not exposed.

## Files

```text
sopsr-news-slider.php
includes/
  class-sopsr-news-slider-settings.php
  class-sopsr-news-slider-logger.php
  class-sopsr-news-slider-assets.php
  class-sopsr-news-slider-render.php
  class-sopsr-news-slider-admin.php
assets/
  css/frontend.css
  css/admin.css
  js/frontend.js
  js/admin.js
  vendor/splide/README.txt
docs/
  SPEC.md
```

## Version

Developer checks (no additional dependencies): `php tests/regression.php` and `node tests/preview.cjs`.
These cover settings/CSS generation and preview updates; browser and WordPress integration still require the keyboard/manual checks above.

### 0.1.3

- The settings page remembers which accordion sections are open or closed in the current browser.
- After saving from either Save button, the settings page returns to the previous scroll position and restores the accordion state.
- On the first visit, before a browser state exists, only the first settings section is expanded.

### 0.1.2

- Added “Uložiť zmeny” in the sticky Live Preview card, submitting the existing settings form.
- Added CTA focus text/background color pickers; focus overrides hover and keeps the visible focus ring. Older settings inherit their normal CTA colors until saved.
- Added px/rem/em selectors and decimal values for CTA font size, responsive title sizes and clamp minimum/maximum; missing units remain px and the fluid value remains vw.
- Added rotating accordion chevrons and visible keyboard focus; section resets retain their existing behavior.
- Live Preview now shows decorative navigation arrows and pagination dots using frontend CSS, including configured icon/background/opacity/size and active/inactive dot colors.
- Desktop/Tablet/Mobile preview updates typography and controls immediately; the preview CTA can receive keyboard focus to test its colors.
- Preserved the 0.1.1 frontend keyboard focus order, semantic CTA links and Splide accessibility behavior.

### 0.1.1

Keyboard-focus update:

- autoplay Pause/Play control is before the carousel track in DOM order,
- Prev/Next arrows are before the carousel track in DOM order,
- active slide links/CTA therefore follow the controls in the Tab sequence,
- with debug console enabled, `focusin` events are logged as `[SOPSR News Slider] Focus`,
- CTA remains a semantic `<a>` because it navigates to the article.

Expected focus order with autoplay: `Pause/Play → Previous → Next → active slide CTA → pagination`.
Without autoplay: `Previous → Next → active slide CTA → pagination`. Disabled arrows are skipped by the browser.

### 0.1.0

Initial test build.
