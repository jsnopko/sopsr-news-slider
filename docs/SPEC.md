# ŠOP SR News Slider – návrh a základná dokumentácia

**Stav:** implementovaný test build 0.1.3
**Projekt:** www.sopsr.sk / WordPress + Kadence + Polylang  
**Knižnica:** Splide.js 4.1.4; plugin podporuje bundled/local cache a CDN fallback  
**Licencia Splide:** MIT  
**Cieľ:** vlastný, rýchly, prístupný hero/news slider pre hlavnú stránku bez závislosti od externého slider pluginu.

## 1. Základná architektúra

Slider bude samostatný WordPress plugin, napr.:

```text
wp-content/plugins/sopsr-news-slider/
├── sopsr-news-slider.php
├── includes/
│   ├── class-admin.php
│   ├── class-settings.php
│   ├── class-render.php
│   └── class-query.php
├── assets/
│   ├── vendor/splide/
│   │   ├── splide.min.js
│   │   └── splide-core.min.css
│   ├── css/
│   │   ├── frontend.css
│   │   └── admin.css
│   └── js/
│       ├── frontend.js
│       └── admin-preview.js
└── README.md
```

Splide sa preferenčne načíta z bundled súborov alebo lokálnej cache vo WordPress uploads; ak lokálna kópia nie je dostupná, test build má jsDelivr fallback. Frontend obsah sa vyrenderuje server-side cez PHP a Splide pridá iba carousel správanie. Pri zlyhaní JavaScriptu musí zostať aspoň obsah prvého slidu použiteľný.

V prvej verzii bude jeden hlavný profil slidera pre homepage. Renderovanie bude od začiatku používať unikátne HTML ID, aby bolo možné neskôr podporiť viac sliderov bez prepisovania frontendu.

## 2. Vkladanie slidera

Primárne shortcode:

```text
[sopsr_news_slider]
```

Neskôr je možné pridať Gutenberg blok alebo viac profilov.

## 3. Zdroj obsahu

Predvolený režim:

- WordPress post type `post`
- iba publikované príspevky
- aktuálny jazyk podľa Polylang
- zoradenie podľa dátumu od najnovších
- nastaviteľný počet príspevkov
- multi-select pre vylúčené kategórie
- pri Polylang sa vylúčená kategória preloží na zodpovedajúci term aktuálneho jazyka
- manuálne pripnuté články podľa ID (pri Polylang sa preložený post ID mapuje automaticky)

### Chýbajúci featured image

Nastaviteľné správanie:

1. preskočiť článok,
2. použiť globálny fallback obrázok,
3. použiť farebné pozadie bez obrázka.

Ak filtrovanie alebo chýbajúce obrázky znížia počet výsledkov, query má načítať dostatok kandidátov, aby sa pokúsila naplniť požadovaný počet slidov.

## 4. Obsah jedného slidu

Predvolený slide:

- featured image,
- overlay,
- názov aktuality,
- voliteľne dátum,
- voliteľne excerpt,
- CTA odkaz/tlačidlo.

Nadpis bude semantický heading (`h2`/`h3` podľa kontextu stránky).

Celý slide nebude obrovský vnorený link, ak zároveň obsahuje CTA. Predvolený spôsob odkazu bude samostatný CTA link; alternatívne môže byť klikateľný nadpis.

## 5. Rozmery a layout

Nastavenia:

- content width,
- full width,
- vlastná max-width,
- viewport/fullscreen hero,
- samostatná šírka a výška pre desktop/tablet/mobile,
- `auto`, px, %, CSS hodnota podľa typu poľa,
- pomer strán / aspect ratio,
- voliteľný responsive `clamp()`.

### Breakpointy

Predvolené hodnoty budú upraviteľné:

- Desktop: nad tablet breakpointom
- Tablet: stredný rozsah
- Mobile: pod mobile breakpointom

Pre každý breakpoint:

- width,
- max-width,
- height,
- aspect ratio alebo fixed/clamp režim,
- image fit,
- image position,
- prípadne zapnutie/vypnutie arrows/pagination podľa potreby.

## 6. Obrázok

Použije sa normálny WordPress `<img>`, nie konverzia na CSS background cez Splide `cover`.

Dôvody:

- `srcset`,
- `sizes`,
- `width`/`height`,
- WordPress image sizes,
- kontrola lazy/eager loadingu,
- lepšia práca s alternatívnym textom a výkonom.

### Object fit

Per breakpoint:

- `cover`,
- `contain`,
- `fill`,
- `none`,
- `scale-down`.

### Object position

Per breakpoint:

- presets: center, top, bottom, left, right, rohy,
- vlastné X/Y percentá.

Pre typické fotografie 1920 × 800 sa musí dať zvoliť zachovanie celého obrazu cez `contain`, alebo hero crop cez `cover`.

### Accessibility obrázka

Ak fotografia iba vizuálne dopĺňa názov článku a neobsahuje samostatnú informačnú hodnotu, bude možné použiť prázdny `alt=""`, aby screen reader neopakoval rovnakú informáciu. Ak attachment obsahuje zmysluplný alt text a obrázok nesie informáciu, plugin ho môže použiť podľa zvoleného režimu.

## 7. Overlay

Nastavenia:

- WordPress color picker,
- opacity 0–100 %,
- none / solid / gradient top→bottom / gradient bottom→top,
- live admin preview.

Farba a opacity sa budú ukladať oddelene, nie ako ručne písané RGBA.

## 8. Nadpis

Nastavenia:

- farba,
- font weight,
- line-height,
- max-width,
- zarovnanie,
- text shadow zap/vyp,
- veľkosť cez `clamp()`.

Príklad:

```css
font-size: clamp(24px, 2.4vw, 48px);
```

Admin bude zadávať minimum / fluid / maximum, nie celý CSS výraz.

Ideálne samostatné hodnoty alebo override pre desktop/tablet/mobile.

## 9. CTA

Nastavenia:

- CTA zap/vyp,
- viditeľný text,
- farba textu,
- background,
- hover background/text,
- border,
- border radius,
- padding,
- font size/weight,
- focus štýl,
- zarovnanie.

### Accessibility názov CTA

Viditeľný text môže byť napr.:

```text
Viac informácií
```

Accessible name bude obsahovať aj názov článku, napr.:

```text
Viac informácií o aktualite: Názov článku
```

Použije sa primárne `aria-label`. Voliteľne sa môže generovať aj `title`, ale WCAG správanie nebude závisieť od `title`.

Prefix bude lokalizovateľný per Polylang jazyk, napr.:

- SK: `Viac informácií o aktualite:`
- EN: `Read more about:`
- DE/PL/HU: vlastná hodnota v nastavení.

Názov článku sa doplní automaticky.

## 10. Splide – bežné nastavenia

V administrácii budú minimálne:

- transition: slide / fade,
- loop,
- rewind,
- rewindByDrag,
- speed,
- rewindSpeed,
- autoplay,
- interval,
- resetProgress,
- arrows,
- pagination,
- paginationKeyboard,
- drag / swipe,
- keyboard,
- waitForTransition,
- easing,
- start,
- perPage,
- perMove,
- gap,
- padding,
- lazyLoad,
- preloadPages,
- pauseOnHover,
- pauseOnFocus.

`wheel` bude v Advanced a predvolene vypnutý.

Poznámka: Splide `rewind` nefunguje v `loop` type. Pri fade režime sa nekonečné pokračovanie rieši cez `rewind`.

Nebudú bežne sprístupnené interné/rizikové možnosti ako `clones`, `classes`, `focusableNodes`, interné role alebo low-level i18n štruktúra, ak nemajú jasný administrátorský význam.

## 11. Autoplay a progress

Autoplay:

- zap/vyp,
- interval,
- speed,
- pause on hover,
- pause on focus.

Ak je autoplay aktívny, slider bude mať viditeľné Pause/Play ovládanie.

Voliteľný autoplay progress:

- none,
- progress bar.

## 12. Accessibility – povinné pravidlá

Tieto veci sa nesmú dať administrátorom omylom vypnúť:

- zmysluplný accessible name slidera,
- viditeľný `:focus-visible`,
- keyboard dostupnosť,
- správne ovládanie focusu,
- `pauseOnFocus`,
- `pauseOnHover` pri autoplay,
- Pause/Play pri autoplay,
- rešpektovanie `prefers-reduced-motion`,
- správne screen-reader labely arrows/pagination/CTA,
- žiadne skryté focusovateľné prvky v neaktívnych slidoch,
- logické DOM poradie,
- bez focus trapu.

Treba testovať aj `loop` režim, pretože vytvára klony slidov. Splide rieši focusable prvky v neviditeľných slidoch, ale výsledok sa musí manuálne overiť.

### Keyboard test

Minimálne:

- Tab,
- Shift+Tab,
- Enter,
- Space,
- šípky tam, kde ich Splide podporuje,
- Home/End na pagination podľa Splide behavior.

Testovať aj prvý/posledný slide pri `loop = false`.

## 13. Admin rozhranie

Navrhované sekcie:

1. Obsah
2. Layout a rozmery
3. Responsive
4. Obrázok
5. Overlay
6. Nadpis
7. CTA
8. Slider / ovládanie
9. Autoplay
10. Accessibility
11. Advanced Splide
12. Custom CSS
13. Preview

Použiť WordPress admin komponenty a color picker. Nastavenia rozdeliť do prehľadných kariet/accordionov, nie jednu dlhú stenu polí.

## 14. Admin preview

Preview jedného reprezentatívneho slidu bez reloadu stránky.

Prepínače:

```text
Desktop | Tablet | Mobile
```

Live preview má reagovať minimálne na:

- rozmery,
- image fit,
- image position,
- overlay,
- gradient,
- title clamp/weight/line-height/alignment,
- CTA text/farby/radius,
- Custom CSS, ak je bezpečne aplikovateľné do preview.

Preview nemusí inicializovať celý Splide carousel. Má byť rýchly a slúžiť primárne na vizuálne ladenie.

## 15. Custom CSS

Samostatné pole v administrácii.

CSS sa aplikuje iba na wrapper pluginu, napr.:

```text
#sopsr-news-slider
```

alebo na unikátne ID konkrétnej inštancie.

Pri výstupe sa CSS pridá cez WordPress enqueue/inline style mechanizmus. Pole bude dostupné iba oprávnenému administrátorovi.

## 16. Polylang

Plugin musí:

- rešpektovať aktuálny jazyk query,
- správne mapovať exclude kategórie na preklady,
- mať preložiteľné CTA a accessibility texty,
- nepoužívať hardcoded slovenské texty na frontende,
- fungovať aj bez Polylang (fallback na default texty).

## 17. Výkon

- Splide JS/CSS načítať iba na stránkach, kde sa slider renderuje.
- Prvý viditeľný obrázok: eager / vyššia fetch priority podľa výsledného testu.
- Ostatné obrázky: lazy loading.
- Použiť vhodný WordPress image size, `srcset` a `sizes`.
- Predchádzať CLS explicitnými rozmermi/aspect ratio.
- Žiadny externý CDN dependency.
- Pri jednom slide automaticky vypnúť autoplay, arrows a pagination, ak nedávajú zmysel.

Voliteľne neskôr cache query výsledkov s invalidáciou pri publikovaní/úprave postu alebo zmene nastavení. Pri 5–10 postoch to nie je nutnosť pre v1.

## 18. Bezpečnosť a WordPress štandardy

- nastavenia iba pre používateľov s vhodnou capability (`manage_options` alebo vlastná),
- nonce pri ukladaní,
- sanitizácia každého poľa podľa typu,
- escaping výstupu,
- žiadne heslá/secret hodnoty,
- Custom CSS iba pre oprávneného admina,
- všetky pluginové option names s vlastným prefixom,
- uninstall nesmie automaticky mazať dáta bez explicitného rozhodnutia.

## 19. Edge cases

Plugin musí rozumne zvládnuť:

- 0 dostupných článkov,
- 1 dostupný článok,
- menej článkov než nastavený počet,
- chýbajúci featured image,
- veľmi dlhý title,
- chýbajúci excerpt,
- deaktivovaný Polylang,
- JS error / Splide sa nenačíta,
- autoplay + reduced motion,
- loop on/off,
- fade + rewind,
- mobil s úzkou šírkou,
- veľmi široký monitor.

## 20. Reset a defaulty

- reset jednotlivých sekcií,
- reset celého slidera,
- potvrdenie pred resetom,
- rozumné defaulty vhodné pre homepage ŠOP SR.

Predpokladané defaulty:

```text
posts: 5
transition: fade alebo slide podľa finálneho vizuálu
loop: true
autoplay: true
interval: 6000 ms
speed: 700 ms
arrows: true
pagination: true
drag: true
keyboard: true
overlay: #000000 / cca 40–45 %
CTA: true
```

## 21. Užitočné rozšírenia do budúcna

Nie sú nutné pre prvú implementáciu, ale architektúra ich nemá blokovať:

- manuálne pripnuté články,
- manuálny výber a poradie slidov,
- viac slider profilov,
- Gutenberg blok,
- per-post focal point,
- per-post override overlay/title farby,
- samostatný slide z custom obsahu,
- plánovanie publikovania slider položiek.

## 22. Testovací checklist pred nasadením

### Funkčnosť
- latest posts,
- exclude jednej aj viacerých kategórií,
- počet postov,
- Polylang,
- loop on/off,
- fade/slide,
- autoplay,
- responsive breakpointy,
- cover/contain,
- CTA.

### Accessibility
- iba klávesnica,
- NVDA alebo iný screen reader,
- zoom 200 %,
- reduced motion,
- focus viditeľný,
- Pause/Play,
- správne labely,
- žiadne focus pasce.

### Výkon
- Lighthouse,
- CLS,
- LCP hlavného obrázka,
- lazy loading ďalších obrázkov,
- žiadne 404 assets/source maps.

### Browser/device
- Chrome,
- Firefox,
- Edge,
- mobil Android/iOS podľa možností.

## 23. Referencie

- Splide docs: https://splidejs.com/documents/
- Splide options: https://splidejs.com/guides/options/
- Splide accessibility: https://splidejs.com/guides/accessibility/
- Splide getting started: https://splidejs.com/guides/getting-started/
- npm package: https://www.npmjs.com/package/@splidejs/splide


## Implementačná poznámka pre verziu 0.1.0

Test build používa jsDelivr ako fallback, ak Splide nie je pribalený alebo lokálne stiahnutý. V administrácii je tlačidlo na stiahnutie Splide 4.1.4 do WordPress uploads; po úspechu sa frontend automaticky prepne na lokálnu kópiu.


## Implementačná poznámka pre verziu 0.1.1

Keyboard focus order bol upravený tak, aby autoplay toggle a Prev/Next šípky boli v DOM pred `splide__track`. CTA zostáva semantický odkaz `<a>`. Pri zmene slidu pomocou Next/Previous zostáva focus na ovládacom prvku a ďalší Tab smeruje na focusovateľný obsah aktuálneho slidu. Debug režim zaznamenáva `focusin` udalosti do konzoly.


## Implementačná poznámka pre verziu 0.1.2

Nadpis (Desktop/Tablet/Mobile aj clamp min/max) a CTA majú číselnú veľkosť s jednotkou px/rem/em. Chýbajúca jednotka je px; clamp fluid zostáva vw. Jednotky sú striktne validované, hodnoty podporujú desatinné čísla. Dátum a excerpt nemajú nastaviteľnú veľkosť písma.

CTA má samostatné farby textu a pozadia pre :focus aj :focus-visible; focus má prednosť pred hover a zachováva outline. Staršie nastavenia preberajú normálne CTA farby bez zápisu do databázy pri načítaní.

Live Preview obsahuje ďalšie Uložiť zmeny v tom istom formulári, dekoratívne šípky a päť bodiek so spoločným frontend CSS. Šípky a pagination nemajú Tab stop. CTA je dostupné klávesnicou na kontrolu focus farieb. Viditeľnosť šípok/bodiek aj veľkosť ovládania sú globálne; bodky majú existujúcu pevnú veľkosť a odstup. Náhľad používa reprezentatívne šírky 1100/720/375px a približné rozmery prispôsobené panelu.

Sekcie zachovávajú natívne details/summary, reset a získavajú otočnú šípku aj viditeľný focus. Frontendové DOM poradie a klávesnicové správanie 0.1.1 zostávajú zachované.


## Implementačná poznámka pre verziu 0.1.3

Administrácia ukladá otvorený/zatvorený stav každej `details` sekcie do lokálneho úložiska prehliadača. Ak stav ešte neexistuje, otvorená je iba prvá sekcia. Pri odoslaní pôvodného settings formulára sa do session úložiska uloží aktuálna vertikálna pozícia a po návrate z WordPress `options.php` sa jednorazovo obnoví. Platí to pre obe tlačidlá na uloženie a nemení to nonce, capability kontrolu ani sanitizáciu nastavení.
