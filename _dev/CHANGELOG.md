### [Unreleased]: 2026-07-21

* Fix a `_load_textdomain_just_in_time` notice (and the "headers already sent" warning it triggers, since the notice output starts sending the response early) firing on every request: `inc/hooks.php` `require`d `inc/acf-fields.php` immediately, as plain top-level code gated only by an `acf_add_local_field_group` `function_exists()` check rather than a WordPress hook - so every ACF field group file's inline `__( '...', 'air-light' )` calls (labels, instructions, choices) ran the instant `functions.php` was parsed, well before the `init` action WP 6.7+ requires translations to wait for. Wrap the requires in a new `register_acf_field_groups()` function and hook it to `acf/init` instead (ACF's own documented timing for `acf_add_local_field_group()`, already used correctly for ACF blocks two lines above this in the same file)
* Fix the shop filter dropdowns (Tamaño/Capacidad/Precio) rendering completely clipped/invisible instead of showing their checkbox panel: `.shop-filter-bar-filters` set `overflow-x: auto` without an explicit `overflow-y`, which per the CSS overflow computed-value rule forces the other axis to `auto` too - turning the pill row into a scroll container on both axes and trapping each dropdown's absolutely-positioned panel (which extends below the row) inside its own tiny scrollable height instead of letting it float freely. Use `flex-wrap: wrap` instead - there are only three short pills, so wrapping is a safe fallback on narrow viewports without needing a scroll container at all
* Rework the mobile off-canvas menu into a full-height drawer: it now covers the entire viewport top-to-bottom (`position: fixed; top: 0` minus `--admin-bar-offset` so it starts below the WP admin bar instead of being hidden under it, `z-index` above the header's own content but below the toggle button so the close/X stays clickable) and slides in from the left with a real animated transition (previously it started below the header at a JS-calculated offset with no `transform` transition, so opening/closing just snapped instantly instead of sliding) - same capped width (`min(80vw, 25rem)`). Since the drawer no longer depends on the header's height at all, this also removes the now-dead `calculate-burger-menu-position.js` and its JS-computed `top`/`height`, along with the old `.logged-in.admin-bar .menu-items-wrapper { margin-top: 46px }` patch it needed (which only covered WP's own ≤782px admin-bar breakpoint, not the theme's wider 1030px mobile-nav one). Also adds a dimmed backdrop (`#menu-items-overlay`, same tone as the cart drawer's own `::backdrop`) behind the drawer while it's open, covering the page and the sliver of header the drawer itself doesn't - clicking it closes the menu, same as the toggle
* Add the CANUT logo and a "Contáctanos" CTA (linking to `/contacto/`) to the mobile drawer - logo at the top, navigation in the middle, contact button pinned to the bottom (`template-parts/header/navigation.php`, `_nav-mobile.scss`); both are drawer-only content, hidden on desktop (`_nav-desktop.scss`) since the desktop header already has its own logo and a "Contacto" nav link. The logo block's height matches the real header's own rendered height (`--site-header-height`, already tracked by `modules/sticky-header.js` for the product gallery) instead of an arbitrary padding value, so it lands exactly where the real header's logo did and reads as the drawer sliding in "over" the header rather than starting fresh below it
* Fix the hamburger/X toggle turning invisible against dark backgrounds: the closed hamburger is now white specifically on the transparent/dark-green home header (`.home .site-header .hamburger`), and the opened X is always white (`--hamburger-color-active`) since the header itself now always goes to the same dark green while the drawer is open, on every page, not just home (`_site-header.scss`, `_nav-mobile.scss`)
* Turn the shop grid's quick add-to-cart button into a "Comprar ahora" CTA that opens the cart drawer on success instead of navigating away: reuses WooCommerce's own `added_to_cart`/`.loading`/`.added` add-to-cart.js lifecycle (`modules/cart-drawer.js` now also listens for it) rather than adding a parallel AJAX call, plays a brief scale/color pulse on success, and relabels itself to "Ver carrito" afterwards - including on a fresh page load if the product is already in the cart (`WC()->cart->find_product_in_cart()` in `woocommerce/content-product.php`), so a second click reopens the drawer instead of adding another unit. WooCommerce's own auto-inserted "Ver carrito" text link is hidden (`_card-product-canut.scss`) since the button now covers that itself
* Fix the shop grid's product image rendering as a thin, uncropped strip instead of a cropped square: `.card-product-canut-media` is an `<a>` (inline by default), so its `aspect-ratio` had no effect; add `display: block`. Separately, the Air-Light base theme's own CLS-safe `img[width][height]:not([data-object-fit='cover']) { height: auto }` rule (`formatting/_img.scss`) was overriding the intended `object-fit: cover` since it ties in specificity with a plain `img` descendant selector and loads after - opt in with `data-object-fit="cover"` on the product image instead of fighting it with more specificity. Also fixes the product title rendering as an underlined link (missing `text-decoration: none` on its wrapping anchor)
* Wire the shop filter bar (Tamaño/Capacidad/Precio) up to real WooCommerce filtering instead of decorative buttons: register global `pa_tamano`/`pa_capacidad` product attributes on `init` if missing (`register_shop_filter_attributes()` in `inc/hooks/woocommerce.php`) since WooCommerce's native `filter_{attribute}`/`query_type_{attribute}` query vars only match taxonomy-based attributes, not the custom per-product ones already used for the spec table; each dropdown is a `<details>` disclosure of checkboxes (`woocommerce/archive-product.php`, `views/_shop.scss`) that joins checked terms into one comma-separated hidden input on submit (`modules/shop-filters.js`, since WooCommerce only reads that query var as a single string, never an array) - Precio uses the native `min_price`/`max_price` query vars directly, no plugin needed
* Restyle the "Productos" nav dropdown from a narrow black box with underlined links to a much wider white card with green text and no underline (`_nav-desktop.scss`); sub-menu items linking to a WooCommerce product now show its featured image, full-width with rounded corners, instead of plain text - the title wraps under it instead of stretching the card (`Nav_Walker::start_el()` in `inc/includes/nav-walker.php`), while the mobile off-canvas drawer keeps plain text links (`_nav-mobile.scss`); also fixes the dropdown's chevron rendering browser-default black instead of following the header's white text over the transparent home hero (buttons don't inherit `color` like other inline elements)
* Swap the hover-reveal arrow on every `button-canut-base` (Comprar ahora, etc.) from `caret-right-fill` to `caret-left-fill` (`_button-canut.scss`), keeping the same currentColor mask technique
* Add the "Garantía" page from Figma as a new `acf/garantia-canut` block (`template-parts/blocks/garantia-canut.php`, sections in `template-parts/garantia/`, fields in `inc/acf-fields/garantia-canut.php`): hero over a photo, a legal article card (Garantía Legal / Derecho de Retracto highlighted callout with a bullet list / Avería en Transporte), a full-width "Exclusiones de Garantía" band and a closing trust-signals + WhatsApp CTA section - same one-block-holds-the-whole-page pattern as Nosotros/Contacto. Every repeatable part (article sections with their own optional bullet list, exclusion cards, trust-signal cards) is an ACF repeater so editors can add/remove/reorder rows without touching code; every field falls back to the original Figma copy when empty. Ships its own `page-garantia.php` (same reasoning as `page-nosotros.php`/`page-contacto.php`) and populates the existing "Garantía" page with this content via WP-CLI
* Add the "Contacto" page from Figma as a new `acf/contacto-canut` block (`template-parts/blocks/contacto-canut.php`, sections in `template-parts/contacto/`, fields in `inc/acf-fields/contacto-canut.php`): hero, a message form (name/email/phone/message) and an info column (WhatsApp channels, email/business hours, embeddable Google Maps card with a "view on Maps" button) - same one-block-holds-the-whole-page pattern as Nosotros/homepage, every field falls back to the original design copy when left empty. No form plugin (Gravity Forms, etc.) is active on this site, so the form posts natively to `admin-post.php` and is handled by `contact_form_submit()` in `inc/hooks/forms.php` (nonce + honeypot, `wp_mail()` to the ACF-editable recipient address, redirects back with a `?contacto=exito|error` status banner reusing `banner-canut`, which also gets a new `is-error` variant here); ships its own `page-contacto.php` (same reasoning as `page-nosotros.php`) and populates the existing "Contacto" page with this content. Adds `icon-envelope.svg`, `icon-phone.svg`, `icon-headset.svg` and `icon-map-pin.svg` (Phosphor fill)
* Add the "Soporte" (community support) page from Figma: a new `Pregunta_Soporte` post type backs the "Crea tu pregunta" form (`page-soporte.php`, WP Page slug `soporte`, `template-parts/soporte/question-form.php`) - visitors submit their name, email, a `categoria_ayuda` "Tema" and their question via a plain `admin-post.php` submit (`soporte_form_submit()` in `inc/hooks/soporte.php`, same no-form-plugin convention as the Contacto page), creating a `pending` post with the contact details on a new ACF field group (`inc/acf-fields/pregunta-soporte.php`). Approval is the native WordPress editorial flow: an editor writes the answer in the post's own editor and clicking "Publicar" is the only actual approve step - `maybe_sync_pregunta_soporte_to_recurso_ayuda()` mirrors the now-published question into a new `Recurso_Ayuda` post (same `categoria_ayuda` term), so it starts appearing on the existing Centro de ayuda FAQ listing without any extra action. The page also shows a live "Últimas preguntas" sidebar (latest 3 mirrored questions, `template-parts/soporte/recent-questions.php`), a bridge band linking to Centro de ayuda, and a static "Preguntas frecuentes" accordion (reuses `accordion-canut`) for the general store FAQs from the Figma copy. Adds `icon-arrow-square-out.svg` (Phosphor fill)
* Fix the "Familia CANUT" gallery strip on the product page silently showing a blank slot for any `.mov` clip most browsers can't decode (iPhone `.mov` exports are commonly HEVC-in-QuickTime, which only Safari plays back in a `<video>` element): pass the file's real `mime_type` through as the `<source type>` attribute so unsupported formats are rejected immediately instead of hanging, and add `modules/gallery-strip-video-fallback.js` to swap any video that fails to load for its own poster image instead of leaving it empty
* Reduce the default site header's vertical padding site-wide (`2.5rem` desktop/`1.25rem` mobile down to a flat `space-sm`, matching the front page's already-reduced header) so every page using the normal white header - Nosotros, Garantía, Contacto, Shop, single product, etc. - gets the same compact height instead of the front page standing out as the only tight one (`_site-header.scss`); the single product page's own slightly-reduced override is now redundant and removed since the new default is already smaller
* Add the "Centro de ayuda" (help center) page from Figma: a new `Recurso_Ayuda` post type (title = pregunta, editor WYSIWYG = respuesta) grouped by a new `Categoria_Ayuda` taxonomy (icon picker + editorial order + WhatsApp CTA copy per category via `inc/acf-fields/categoria-ayuda.php`), rendered on `page-centro-de-ayuda.php` (applies to the WP Page with slug `centro-de-ayuda`) as an expandable FAQ accordion per category (reuses `accordion-canut`) with a sidebar category nav (scrollspy) and a WhatsApp CTA band; each resource also keeps its own permalink (`single-recurso_ayuda.php`) for direct links/SEO. The hero search field is a real backend search, not a client-side text filter: it debounces into a new `canut_help_search` AJAX action (`inc/hooks/help-center.php`) that runs a `WP_Query` across title/content plus a `palabras_clave` ACF field (`inc/acf-fields/recurso-ayuda.php`) and the resource's category name, then `modules/help-center-canut.js` shows/hides the matching already-rendered accordion items - falls back to native WordPress search (`?s=...&post_type=recurso_ayuda`) if JS fails. Adds `icon-magnifying-glass.svg` and `icon-paw-print.svg` (Phosphor fill)
* Add a reusable "Página informativa" page template (`template-pagina-informativa.php`, selectable from Page Attributes) for legal/support pages like Términos de Servicio, Política de Privacidad, Shipping Policy or Warranty: editors add any number of sections via a `secciones` ACF repeater (title + WYSIWYG), and the template auto-numbers them and builds a sticky table of contents from that same repeater - no hardcoded section list or manual numbering; the current section is highlighted in the TOC while scrolling (`modules/pagina-informativa.js`, `IntersectionObserver`); full-width layout on the same `wrap-canut` container as the rest of the site (title/breadcrumb/TOC/content all share its left edge), grey page background (`--wp--preset--color--light`) with the numbered sections floating in a white card flush to the wrap's right edge
* Add the "Nosotros" (about) page from Figma as a new `acf/nosotros-canut` block (`template-parts/blocks/nosotros-canut.php`, sections in `template-parts/nosotros/`, fields in `inc/acf-fields/nosotros-canut.php`): hero manifesto, origin story with pull-quote, a 4-card bento grid of brand pillars (light/dark/accent card variants, optional image/stat/tags/link per card) and a closing CTA - same one-block-holds-the-whole-page pattern as the homepage, every field falls back to the original design copy when left empty; adds `icon-pencil-ruler.svg`, `icon-medal.svg` and `icon-brain.svg` (Phosphor fill) to the icon set. Ships with its own `page-nosotros.php` (like `page-sistema-de-diseno.php`, applies to the WP Page with slug `nosotros`) instead of the default `page.php`: the latter's `<main>` carries `has-global-padding is-layout-constrained`, which caps every child to the ~800px text content width, squashing this page's full-bleed hero/mint-tinted sections down to a narrow boxed column - same reasoning `front-page.php` already skips those classes for. Also fixes the bento grid's `grid-column` spans (row 1 needs an 8/4 split, not two 6/6 cards, or the second card wraps to its own row) and the first card's image collapsing to a sliver (`flex-basis: auto` let the media column shrink to content instead of splitting evenly with the text)
* Fix a jump at the very end of every accordion's open/close animation (`modules/accordion-canut.js`): the animated target height was computed by summing `summary.offsetHeight + panel.offsetHeight`, which silently drops the panel `<p>`'s own margin, so the animation always finished a bit short/long of the true size and then snapped the remainder the instant it completed; measure the real natural height directly instead (temporarily lifting the pinned `height`), and do it *before* applying `overflow: hidden` - that establishes a new block formatting context which changes how the panel's margin collapses and would otherwise skew the very measurement it's meant to fix
* Give the header on the single product page slightly less vertical padding (`space-md`/`space-sm` instead of the default `2.5rem`/`1.25rem`) so it leaves more viewport space for the sticky gallery (`.single-product .site-header-inner` in `_site-header.scss`)
* Fix the site header rendering flush with no vertical breathing room (`.site-header-inner` had `padding-bottom: 0` while `padding-top` was set) and fix the sticky/fixed header overlapping the WP admin bar while scrolled: add a `--admin-bar-offset` custom property (`0` by default, `2rem`/`2.875rem` under `.admin-bar`, matching the toolbar's own 32px/46px breakpoint) and use it as the header's sticky/fixed `top` instead of a hardcoded `0` (`_site-header.scss`)
* Restructure the mobile hamburger menu: opening it no longer scrolls the page to the top (`window.scrollTo(0, 0)` removed from `modules/navigation.js`, the menu already stays correctly positioned below the header via its own JS-calculated `top`/`height`), and it now opens as a white off-canvas drawer (~80% viewport width, capped at `25rem`) sliding in from the right instead of a full-screen dark overlay - background, text and separator colors in `_nav-mobile.scss` switch from the previous white-on-black scheme to match the rest of the site (white background, black text, `--wp--preset--color--border` separators), the hamburger's active-state `X` color switches from white to black to stay visible, and a `hover` shadow is added to the open drawer for separation from the page behind it
* Animate every `accordion-canut-item` open/close (height + panel fade) instead of the native instant snap, via a new `modules/accordion-canut.js` using the Web Animations API; also cross-fade the extended FAQ's swapped image instead of snapping the `src` change
* Fix a position jump at the end of the extended FAQ's accordion animation: the native `<details name="...">` grouping used for mutual exclusivity closes other members synchronously and instantly the moment one opens, bypassing the new animation entirely; replace it with a `data-accordion-group` attribute and manage exclusivity in `modules/accordion-canut.js` itself, so the previously open item now animates shut in sync with the one opening
* Fix the product gallery sticking under the sticky site header on desktop: `.product-gallery`'s sticky `top` now offsets by the header's real rendered height (`--site-header-height`, set by `modules/sticky-header.js`) instead of a fixed spacing value
* Show the thumbnail rail on mobile too, as a horizontal scroll strip below the main image (previously desktop-only, mobile only had dot indicators), so it's clearer there are more photos; clicking a thumbnail now scrolls the mobile carousel to that slide like the dots already did
* Restyle the WooCommerce Checkout block to match the CANUT design from Figma, without replacing its engine: keeps the block's own skeleton loaders/Store API speed (`views/_checkout-canut.scss` targets the block's own `wc-block-*` classes - numbered steps via its native `showFormStepNumbers` attribute restyled into circular badges, payment methods restyled into selectable cards, sticky order summary sidebar)
* Add a minimal, distraction-free header/footer (centered logo only, no nav/cart/menus) used only on the checkout page (`is_checkout()` in `header.php`/`footer.php`)
* Add a `billing_neighborhood` ("Barrio") field to checkout via the Additional Checkout Fields API (`woocommerce_register_additional_checkout_field()`), saved as real order meta
* Reorder payment gateways so "Contraentrega" (COD) always renders first, and apply an automatic 8% fee discount to the cart when "Pago en línea" (Wompi) is the selected gateway
* Add `form-canut` (label, input, select with chevron, textarea, hover/focus tooltip), `payment-option-canut` (a native radio input styled as a full selectable card, `:has(:checked)` for the selected state, no JS) and `banner-canut` (tinted info callouts via `color-mix()`) design-system components, plus matching "Formularios"/"Método de pago"/"Banners" sections on `/sistema-de-diseno`
* Replace the standalone Cart page with a cart drawer (`template-parts/cart/drawer.php`, `modules/cart-drawer.js`): a `<dialog>` slide-in panel from the header cart icon with a quantity stepper/remove per item (custom `canut_cart_drawer_update` AJAX action, no full page reload), sticky "Pagar de forma segura" CTA straight to checkout, and a full-width popup on mobile; the Cart page itself is unpublished
* Add `icon-info.svg`, `icon-credit-card.svg`, `icon-minus-circle.svg`, `icon-plus-circle.svg` and `icon-trash.svg` (Phosphor fill, matching the theme's existing icon set)
* Fix the extended FAQ accordion ("Preguntas Frecuentes") on the product page not closing other questions when one opens, and add a per-question image that swaps in as each one is opened: native `<details name="product-faq-extended">` grouping handles the mutual exclusivity, `modules/faq-image-sync.js` handles the image swap on `toggle`; add an optional per-row image field to the `extended_faq_items` ACF repeater (falls back to the section's default image), and hide the image entirely below the tablet-landscape breakpoint since the section is single-column there
* Fix the product page "Familia CANUT" gallery section losing its side margins: it carried both `.wrap-canut` and `.product-gallery-strip` on the same element, and `.product-gallery-strip`'s `padding: X 0` shorthand reset the `padding-left`/`padding-right` `.wrap-canut` was providing; use longhand `padding-top`/`padding-bottom` instead
* Rebuild the product gallery as a real swipeable carousel on mobile (native horizontal scroll-snap, dot indicators below synced to scroll position) and a thumbnail-driven swap gallery on desktop, both with a fixed height (not aspect-ratio) and `object-fit: cover`; clicking any image opens it in a click-to-zoom lightbox overlay (`modules/product-gallery.js`, new `.product-gallery-lightbox` markup)
* Turn "Lo que incluye" and "Especificaciones" into bordered mini cards (`.tabs-canut-chip`) that always lay out in 2 columns, each with its own icon instead of a repeated checkmark: "Lo que incluye" gets an icon picker in the ACF field group (`box_contents` repeater), "Especificaciones" picks an icon automatically from the WooCommerce attribute name (capacidad/conectividad/material/peso/batería keywords, falling back to a generic check)
* Fix the footer payment row actually showing broken/mismatched images (`payment-visa.jpg` etc. under `assets/images/homepage/` were leftover dashboard screenshots, not payment logos): replace with small uppercase text pills for PayPal/Visa/Mastercard/Efectivo (`.site-footer-canut-payments li`) and delete the unused jpgs
* Fix every `--wp--preset--spacing--space-2xl` reference silently resolving to nothing (`_site-footer.scss`, `_front-page.scss`, `_design-system.scss`): WordPress sanitizes the `space-2xl` slug from `theme.json` into the custom property `--wp--preset--spacing--space-2-xl` (hyphen between the digit and the letters), so every usage of the unhyphenated name was an invalid `var()` reference computing to the property's initial value instead of the intended 128px; this is what made the footer's bottom-bar divider sit right under the last column of links instead of leaving proper breathing room above it
* Make the homepage "Producto destacado" section pull its image, title, description and price straight from a real WooCommerce product instead of manually entered ACF text/image fields: add a `featured_product` post object field (`inc/acf-fields/homepage-canut.php`) to pick the product, falling back to the store's only published product when left empty (`template-parts/blocks/homepage-canut.php`); "Lo quiero ya" now links straight to checkout with the product added and "Especificaciones" to the product's own page
* Fix the product page gallery not sticking while scrolling: `.site` had a blanket `overflow: hidden` (originally there to contain the mobile nav dropdown, which is actually `position: fixed` and clips itself independently) and any ancestor `overflow` other than `visible` breaks `position: sticky` for every descendant; remove it from `.site` (`_general.scss`)
* Fix the FAQ accordion chevron rendering oversized/unstyled on the product page: `.accordion-canut-icon` was a CSS class never applied to any markup (raw SVGs from `assets/svg/` carry no size of their own and `require`-ing one doesn't add a class to it), so target `svg` as a descendant of `.accordion-canut-trigger` instead, same pattern already used by `.trust-badge-canut svg`/`.button-canut-base svg`
* Fix homepage trust band showing a visible duplicated second row on desktop: the `aria-hidden` copy of the list exists only to feed the mobile marquee loop, but nothing hid it above the tablet breakpoint so it just wrapped into view
* Fix the WooCommerce breadcrumb rendering twice on the shop/category and single product pages: WooCommerce core hooks `woocommerce_breadcrumb()` to `woocommerce_before_main_content` (priority 20) by default, on top of the explicit `woocommerce_breadcrumb()` call each CANUT template already makes at its own spot in the layout, so remove the default hook (`inc/hooks/woocommerce.php`); also restyle the base `.woocommerce-breadcrumb` with CANUT tokens instead of the old `air-light` defaults
* Make every marketing section on the single product page configurable per-product via a new "Página de producto CANUT" ACF field group (`inc/acf-fields/product-canut.php`, 9 tabs: encabezado, cómo funciona, lo que incluye, FAQ, comparación, Familia CANUT, reseñas, FAQ extendida, CTA final) - each field falls back to the original Figma copy when left empty, same pattern as `template-parts/front-page/*.php`
* Add CANUT-designed WooCommerce category/shop archive template (`woocommerce/archive-product.php`, `content-product.php`) from Figma: hero banner, filter bar (visual only for now, no product attribute data to filter by yet), 2-column product grid, lifestyle break, trust banner and WhatsApp help CTA
* Add CANUT-designed single product template (`woocommerce/single-product.php`) from Figma: sticky gallery, purchase column ("¿Cómo funciona?" steps, "Lo que incluye"/"Especificaciones" tabs, FAQ accordion, "Comprar ahora"/WhatsApp buttons), comparison, lifestyle gallery, reviews and extended FAQ sections; both templates reuse the existing `header.php`/`footer.php` and only CANUT design-system tokens
* Extend `card-product-canut` with a meta line and a circular add-to-cart action button; add `icon-arrow-counter-clockwise.svg`, `icon-hand-coins.svg`, `modules/tabs-canut.js` and `modules/product-gallery.js`
* Change the shop loop from 3 to 2 columns per row to match the CANUT product grid design
* Center the footer brand lockup, social icons, and each column's heading/links (plus the bottom copyright/payments row) when the grid collapses to one column on mobile (`_site-footer.scss`)
* Shrink the header `logo.svg` further on mobile (`.site-title svg`, down to `1.125rem`) on top of the existing fluid `clamp()` sizing
* On mobile, hide the "WhatsApp Us" text CTA and reorder the header's flex row so the hamburger toggle (not the cart/account icons) sits flush right; the WhatsApp/cart/account icons are hidden from the mobile header entirely below `$breakpoint-nav`, still reachable elsewhere on the site
* Add `Historia` custom post type (title = autor, foto destacada, calificación 1-5, testimonio, y un flag "Aparece en portada") so historias/testimonios ya no están limitadas a 6 entradas por un repeater ACF
* Replace the homepage "Historias CANUT" ACF repeater with a query for featured (`is_featured`) historias, add a "Ver todas" button linking to a new paginated `archive-historia.php` (capped at 24 per page) and a `single-historia.php` fallback template
* Extract the historia card/list markup into `template-parts/historia/card.php`, shared by the homepage carousel and the archive
* Add `_historia-canut.scss` component with a mobile-only `flex-basis` calc so the horizontal swipe carousel always shows exactly one full card plus a consistent sliver of the next, instead of the previous `85vw` sizing which could hide the next card entirely on some phone widths
* Give the final CTA section (`.home-final-cta`, "El futuro del cuidado está aquí") a `min-height` so it doesn't collapse to the height of its text/button and clip the background image, matching Figma
* Enlarge trust band icons (1.1em to 1.75rem, fixed size) and widen the gap between items; on mobile the list now renders twice in the markup (second copy `aria-hidden`) and loops as an auto-scrolling marquee (`@keyframes` translate to -50%, 16s linear infinite), respecting `prefers-reduced-motion`
* Make footer `Compañía`/`Soporte` link columns configurable from WordPress menus (Appearance > Menus) instead of hardcoded arrays in `footer.php`: register `footer_company`/`footer_support` menu locations, wire up the previously unused `Footer_Nav_Walker` class, skip a column entirely if its menu location has no assigned menu
* Replace the "Canut Website" site title text in the footer brand column with the inline `logo.svg` (same pattern as the header), sized via a fluid `clamp()` height in `.site-footer-canut-logo`
* Add `homepage-canut` ACF block with fields for every homepage section (hero, trust band, lifestyle, featured product, how it works, emotional branding, reviews, warranty band, final CTA), so the whole Front page is editable from the block editor in one place
* Add `front-page.php` and 9 `template-parts/front-page/*.php` sections that render the block's fields, each falling back to the original CANUT homepage design copy/imagery when a field is left empty
* Add sticky header: transparent overlay on the front page hero, solid `primary-dark` background once scrolled (`modules/sticky-header.js` toggles `.is-scrolled`), rebuild `header.php`/`_site-header.scss`, add WhatsApp CTA + cart/account icons (`template-parts/header/actions.php`)
* Fix header logo rendering at `logo.svg`'s native 344x75 size: constrain `.site-title svg` to a fluid `clamp()` height (22px-28px) with `width: auto`, so it scales smoothly and reads correctly at every viewport instead of overflowing the header
* Rebuild `footer.php`/`_site-footer.scss` to match the CANUT design: brand/social column, Compañía/Soporte/Ubicación link columns, copyright and payment icons row
* Add `button-canut-ghost` and `button-canut-light` button variants for CTAs placed on top of photography
* Add `.wrap-canut` layout helper (1440px content wrap for full-width sections) and `--canut-tint-soft`/`--canut-tint-deep` derived background tints to `_general.scss`
* Add Phosphor icons for the trust band, header actions, reviews and footer (shield-check, truck, chat-circle, wrench, shopping-bag, user, star, instagram, tiktok, whatsapp)
* Remove buttons, badges, product card, tabs, FAQ accordion, checkout summary and cart popup sections from the `/sistema-de-diseno` reference page, keeping only color, typography, blog/content preview, spacing and radius/shadow tokens
* Pull paragraphs tight to a preceding `h3`/`h4` in `.design-system-canut-prose` (`space-xs` margin-top instead of the default `space-md`), so minor headings read as attached to their text while `h1`/`h2` keep the normal paragraph gap
* Adjust CANUT prose heading weights (h1 light to medium, h2 light to regular, h3 regular to medium) and switch `.design-system-canut-prose` to single-direction margins (`margin-bottom: 0`, `margin-top` per heading level: h1 xl, h2 lg, h3/h4 md) so heading spacing and weight both read as a clear hierarchy instead of relying on browser default bottom margins
* Replace variable-font Inter with static `Inter_18pt` weight files (`Inter_18pt-Thin.ttf` through `Inter_18pt-Black.ttf`) in theme.json, matching the optical size tuned for CANUT's body/small/caption text sizes; remove the now-unused `intervf.woff2`
* Fix CANUT `h1`-`h4` typography tokens not applying: WordPress kebab-cases theme.json slugs, turning `h1` into the CSS custom property suffix `h-1`, so rename the `fontSizes` slugs and `custom.canut.lineHeight` keys to `h-1`-`h-4` in theme.json and update the matching references in `_design-system.scss` and `page-sistema-de-diseno.php`
* Add Phosphor Icons (`@phosphor-icons/core`) as a dev dependency for sourcing SVG icons, replace `icon-check.svg`, `icon-chevron-down.svg`, `icon-copy.svg` and `icon-lock.svg` with their Phosphor regular-weight equivalents
* Replace variable-font Manrope with static weight files (`Manrope-ExtraLight.ttf` through `Manrope-ExtraBold.ttf`) in theme.json so each CANUT type token renders its exact designed weight, and add matching `extra-light`/`extra-bold` entries to `custom.fontWeight`
* Add internal design-system reference page at `/sistema-de-diseno` (page-sistema-de-diseno.php), noindexed and excluded from the sitemap and robots.txt, documenting all CANUT colors, typography, spacing, radius, shadow tokens, a blog/content preview (headings, paragraphs, lists, tables) and live components (buttons, badges, product card, tabs, FAQ accordion, checkout summary, cart popup)
* Add CANUT design tokens to theme.json: color palette, Manrope/Inter fluid typography scale, fixed spacing scale, shadow presets and a `custom.canut` namespace for radius/line-height/letter-spacing, self-hosted alongside existing Mona Sans
* Add `_dev/assets/src/sass/variables/_canut-tokens.scss` with `text-style`, `canut-radius` and `canut-shadow` mixins so every CANUT component references theme.json tokens instead of hardcoded values
* Add `inc/acf-fields/` and `inc/acf-fields.php` for registering ACF field groups via PHP, kept separate and only loaded when ACF is active
* Move all development tooling, source assets and configs into `_dev/`, keeping only runtime files at the theme root for production deploys
* Rebrand theme metadata in `style.css` for Canut
* Add WooCommerce support: theme support declaration, `woocommerce/global/wrapper-start.php` and `wrapper-end.php` template overrides matching the theme's `<main>` markup, 3-column shop loop, and basic product grid styles

### 10.2.0: 2026-06-23

* Remove wysiwyg(tinymce) editor specific styles. Move default link css to theme.json, ref: DEV-1032
* Remove unused SVG icons (breadcrumbs arrow, chevrons, slider arrows), ref: DEV-1032
* Fallback to page allowed blocks if post type blocks are not configured. Allow all blocks on pages. Add prettierignore, ref: DEV-1032
* Fix submenu width, remove clamp helper, ref: DEV-1032
* Update button styles used outside of block content to match theme.json button styles, ref: DEV-1032
* Fix nav flickering around responsive breakpoint, ref: DEV-1032
* Add support for responsive embeds. Hide embed types from inserter instead of disabling them. Drop now redundant reframe.js depedency, Ref: DEV-1032
* Theme cleanup. Remove redundant styling, update single.php template, Ref: DEV-1032
* Remove custom `gridBase`, `spacing`, `color` and `typography` variables from theme.json and replace usages with WordPress preset, Ref: DEV-1032
* Delete `_spacings.scss` responsive override file, spacing now flows from `block-gap`, root padding and preset clamps, Ref: DEV-1032
* Fix indentation: convert tabs to 2-space indent in several files, Ref: DEV-906
* Add filter to disable remote block patterns, Ref: DEV-985
* Enable minHeight for Vertical Alignment support, Ref DEV-1039
* Fix responsive image height override for object-fit cover images, Ref: DEV-1040
* Fix HTML/a11y workflow vnu.jar download by pinning to release `20.6.30` and extracting from zip, Ref: DEV-986
* Fix click navigation bugs, Ref: DEV-984
* Replace `moveto` dependency with native `Element.scrollIntoView({ behavior: 'smooth' })` in `top.js`, `anchors.js` and `a11y-skip-link.js`, Ref: DEV-86

### 10.1.1: 2026-04-08

* Update code-quality-checks to 2.2.0, Ref: DEV-887
* Generate default GitHub Actions code quality workflows in newtheme, Ref: DEV-737
* Fix husky hooks not initializing in new projects by running setup after air-light `.git` removal, Ref: DEV-885
* Clean up air-light CHANGELOGs from root and theme when running newtheme, Ref: DEV-886
* Fix newtheme comments removal using stale `@import` instead of `@use`, Ref: DEV-883
* Show commit ID and date in newtheme script header when version is unreleased, Ref: DEV-882
* Clean up font pipeline: move fonts to `assets/fonts/` as static files, remove unused SCSS font mixins, stale Parcel font artifacts and unused Inter fonts, Ref: DEV-881
* Update code-quality-checks version to 2.1.12, Ref: DEV-771
* Add blocks-watch browserSync live inject runner, Ref: DUDE-2319
* Bump @digitoimistodude/stylelint-config to 1.0.8

### 10.1.0: 2026-02-20

* Migrate all design tokens (colors, spacing, typography) from SCSS to theme.json as single source of truth with fluid typography, WordPress preset and custom variables, Ref: DEV-740
* Remove normalization styles that prevented theme.json preset variables from applying to block editor, Ref: DEV-750
* Update .eslintrc.js for native blocks, Ref: DEV-747
* Update code-quality-checks to 2.1.9

### 10.0.2: 2026-02-06

* Explicitly run husky setup after npm install to ensure pre-commit hooks are configured for all developers, Ref: DEV-742
* Fix changed filename for main scss file for replaces.sh in newtheme script, Ref: DEV-720
* Release newtheme.sh 1.1.7
* Bump tested WordPress version to 6.9.1

### 10.0.1: 2026-02-03

* Fix air-pack.sh to exclude .parcel-cache directory from WordPress.org package, Ref: DEV-735
* Add @wordpress/scripts as dev dependency for custom block development, Ref: DEV-735
* Fix newtheme scripts for Parcel structure by updating sass paths to assets/src/sass in cleanups.sh and replaces scripts, Ref: DEV-735

### 10.0.0: 2026-02-03

* Update air-move-out and air-move-in scripts for Parcel build system, Ref: DEV-720
* Simplify single.php and index.php based on Twenty Twenty structure with proper content width constraint, Ref: DEV-733
* Rename gutenberg-editor.js to editor.js and modularize by moving embed disabling to separate module, Ref: DEV-725
* Move block styles to separate file with PHP server-side registration and scalable array-based approach, Ref: DEV-732, DEV-722
* Configure media-text block with spacing presets (None, Medium 5rem, Large 8rem), hide typography/text color/gradient/media width controls, add alignwide support and fix responsive styles, Ref: DEV-722
* Disable border, dimensions and text color controls in theme.json, Ref: DEV-722
* Limit color palette to single light background color, disable gradients and default colors, Ref: DEV-722
* Update README badges to modern style with for-the-badge format, Ref: DEV-720
* Remove default form styles, will use Gravity Forms Orbit theme, Ref: DEV-721
* Simplify styles to minimal black and white design with square corners, Ref: DEV-718
* Fix submenu current item visibility with white color and underline, Ref: DEV-718
* Remove theme phpcs.xml in newtheme script to use project root config, Ref: DEV-624
* Delete maybe_show_error_block function, Ref: DEV-226
* Enable field validation for ACF blocks, Ref: DEV-372
* Add modular block variations system for customizing core blocks, Ref: DEV-245
* Add Hero block variation using core/cover with full-height defaults, Ref: DEV-720
* Add CTA block variation using core/group with call-to-action defaults, Ref: DEV-720
* Add Columns block variation using core/columns with 3-column layout, Ref: DEV-720
* Add `wp_localize_script` to pass theme URL to block editor JS, Ref: DEV-245
* Restructure SCSS folders, remove gutenberg/ directory and move to block-variations/ at root, Ref: DEV-719
* Remove ACF-specific styles and outdated ACF block initialization code, Ref: DEV-719
* Update theme.json with element styles, useRootPaddingAwareAlignments, and sync colors with SCSS, Ref: DEV-245
* Change theme.json wideSize from 1200px to 1440px to match container width, Ref: DEV-722
* Fix editor post title font by adding fontFamily to theme.json, Ref: DEV-720
* Remove separate editor stylesheet, use global.css in editor for consistent styling, Ref: DEV-720, DEV-723
* Replace `article-content` with WordPress standard `entry-content`, Ref: DEV-723
* Remove opinionated Gutenberg-specific styles and trust theme.json for layout, Ref: DEV-723
* Fix button alignment and add important declarations for editor inline style overrides, Ref: DEV-722
* Consolidate stylelint disable comments to top of files with explanations, Ref: DEV-720
* Fix undefined CSS custom properties in media-text block styles, Ref: DEV-723
* Remove global.css from editor to prevent style leaks, trust theme.json for editor styling, Ref: DEV-723
* Restructure stylesheets: rename global.scss to front-end.scss, create editor.scss for editor-only styles, Ref: DEV-723
* Create block-variations/_index.scss for centralized block variation imports, Ref: DEV-723
* Update @digitoimistodude/code-quality-checks to 2.1.8, Ref: DEV-720
* Add typography to editor.scss, WordPress auto-scopes to prevent admin leaks, Ref: DEV-723
* Update @digitoimistodude/stylelint-config to 1.0.6 with front-end.css support, Ref: DEV-720
* Enable media-text block variation for pages, Ref: DEV-720

### 9.7.0: 2026-01-09

* Migrate from Gulp to Parcel build system, Ref: DEV-439
* Restructure assets to `assets/src/` and `assets/dist/` pattern, Ref: DEV-444
* Fix SCSS import and font paths for Parcel, Ref: DEV-439
* Update README for Parcel build system, Ref: DEV-439
* Update to `@digitoimistodude/dude-coding-standards` for standalone use, Ref: DEV-674
* Use `@digitoimistodude/code-quality-checks@2.1.2` npm package for husky hooks, Ref: DEV-672
* Use `@digitoimistodude/stylelint-config` npm package, #271, Ref: DEV-638
* Remove .scss-lint and update stylelint, #262, Ref: DEV-440 (thanks @nadyahakkinen!)
* Fix incorrect path in newtheme.sh ACTION REQUIRED message, Ref: DEV-450
* .stylelintrc: Change to `"declaration-empty-line-before": "never",`, Ref: DEV-449
* Bump tested WordPress version to 6.9.0
* Update GitHub Actions workflows for Parcel build system, Ref: DEV-672
* Fix embedded PHP formatting to comply with DCS standards, Ref: DEV-672
* Add translators comments and phpcs:ignore for nav-walker complexity, Ref: DEV-672
* Simplify `phpcs.xml` to use DCS standard directly, Ref: DEV-672
* Update ESLint to 8.x and stylelint to 15.x for compatibility, Ref: DEV-672
* Update bin/ scripts to use npm commands instead of gulp, Ref: DEV-672
* Remove native Gutenberg block registration for Theme Directory compliance, Refs: DEV-10, DEV-672
* Fix Parcel watch not recovering from SCSS errors, Ref: DEV-676
* Improve BrowserSync hot reload consistency for CSS/JS, Ref: DEV-677

### 9.6.2: 2025-08-29

* Replace outdated stylelint plugins with modern alternatives for accessibility and custom properties validation, DEV-446
* Remove mentions of archived devpackages, use theme package.json in unit tests, Ref: DEV-130
* Bump axios and browser-sync #260
* Fix replace scripts not removing scss imports, Ref: DEV-433
* Add new logo header to scripts, Ref: DEV-436
* Add `--test-branch <branch>` flag for newtheme.sh for testing, Ref: DEV-437

### 9.6.1: 2025-08-08

* Fix regression in 9.6.0, fonts and colors leaking to admin, Ref: DEV-381
* Fix regression in 9.6.0, links are underlined and not formatted from formatting SCSS
* Fix ul and ol styles leaking to navigation, regression to DEV-93
* Merge new minimalistic mobile nav styles, Ref: DEV-95 (old refs: T-25883, T-25856)
* Deprecate little used animations helper, Ref: DEV-382
* Add minimalistic styles and footer, prepare for 10.0.0, Refs: DEV-94, DEV-90, DEV-379
* Fix too low contrast in dropdown-toggles, inherit dropdown toggle color from the item, Ref: DEV-383
* Prevent longer dropdowns from cutting out on too low pages, Ref: DEV-384
* Fix build, prepare for native blocks, fix dependencies, Refs: DEV-10, DEV-385

### 9.6.0: 2025-08-08

* Bump form-data from 4.0.2 to 4.0.4 #254
* Bump eazy-logger from 4.0.1 to 4.1.0 #251
* Skip HTML validator, Ref: DEV-347
* Use acf_block_defaults preview mode by default, Ref: DEV-155
* Add Husky code quality tests, Ref: DEV-373
* Update dart-sass to 1.86.3, Ref: DEV-149 #253 (thanks @nadyahakkinen!)
* Fix regression with 404 template not having clamp-calc, Ref: DEV-377
* Add helper wrapper for site-header, make it container width, #245, Refs: T-25879, DEV-93 (thanks @nadyahakkinen!)
* Make sure husky has executable permissions after each branch switch, Ref: DEV-378
* Code quality checks: Fix deprecated install command, update version for tests, Ref: DEV-373
* Run gulp from theme instead of project from now on, Ref: DEV-83, DEV-127
* Bump tested WordPress version to 6.8.2
* From now on run gulp and tools from the theme instead of external devpackages repo, Ref: DEV-83
* Simplify starter color variables, #243, Ref: DEV-379
* Update release build scripts, do not pack vendor/, Ref: DEV-83
* Fix editor styles for native blocks, T-25877 #242, Ref: DEV-380

### 9.5.1: 2025-05-23

* Fix inconsistent heading variables, T-25878
* Fix anchors.js not working with #hash in url, DEV-262

### 9.5.0: 2025-02-26

* Disable core/code block by default, T-25121
* Add stripped down tinymce toolbars to acf wysiwyg field, T-19418
* Enhance styles for blockquote and lists added via wysiwyg, T-17639
* Add nav walker for simplified footer navs, T-25859
* Fix JS breakpoint navigation variable, T-25787
* Delete redundant \_box-with-shadow.scss, T-24776
* Delete obsolete aspect-ratio mixin, T-24777
* Add parts folder, T-24778
* Force minimal selection of formatting in tinymce WYSIWYG editor, T-19418
* Rewrite allowed_blocks, make it possible to use single blocks, T-17645
* Fix: Mobile navigation is wrongly offset with WP admin bar, T-25557
* Remove obsolete airbnb-browser-shims, T-23391
* Avoid serving legacy JS bundles, T-23391
* Bump tested up to WordPress 6.7.2
* Silence deprecation warnings, fix build T-20953

### 9.4.8: 2025-02-03

* Add debug function to print all available blocks
* Fix page including all allowed blocks by default (default: none)
* Fix cssnano stripping out font-family declarations
* Remove root font-size 62.5% from editor styles, T-24605
* Add limited amount of core blocks for articles by default, remove extra embed blocks, T-25121
* Bump tested up to WordPress 6.7.1

### 9.4.7: 2024-12-04

* Fix body styles leaking to wp-admin
* Fix img proportions fallback for images that have width and height set, T-23188
* Fix image proportions for overlay images due to air-helper 3.1.1, T-23188
* Change HTML build CI to use vnu-jar instead of outdated html-validator-cli
* Remove polyfills, T-14767

### 9.4.6: 2024-11-19

* Demo: Prevent spaces in theme-info link
* Fix newtheme-popos.sh script location detection
* Check if pll_translatable is set
* Fix gulp task stripping out @font-face declaration
* Remove postcss-discard-unused and postcss-minify-font-values
* Make black truly black
* Add Mona Sans variable font
* Re-design placeholder content
* Remove root font-size 62.5%, add rem-over-px formula, Fixes #192

### 9.4.5: 2024-10-25

* Add global variable for current block during `render_acf_block`, T-17629
* Add default styles for 404.scss
* Make navigation to wrap automatically if there are too many links, T-20918
* Add default column-gap to header, T-20918
* Rewrite: allowed_block_types * Change logic for allowed blocks: 'none', 'all', 'all-core-blocks', 'all-acf-blocks', Fixes #226 (thanks @villekujansuu)
* Allow options + specific blocks for allowed blocks
* Prepare for air-blocks-buildtool
* Remove stylelint-file-max-lines, T-20765
* Add more breakpoints like $container-desktop, T-20758
* Fix burger navigation sometimes not being centered vertically, T-20918
* Change to new dev.docs.dude.fi way of naming conventions, combine font partials under variables to one \_typography.scss file, T-20761
* Move font-face include under \_typography.scss, T-20761
* Change typography variables to headings, T-20761

### 9.4.4: 2024-09-13

* Add unit tests for gulp devstyles
* Fix nesting deprecation, fix build
* Remove from sanitize scss: `overflow-wrap: break-word;`
* Bump tested WordPress version to 6.6.2

### 9.4.3: 2024-09-06

* Fix navigation rules leaking to other navs, Fixes T-1644
* Remove obsolete fileheader information from all files for consistency, Fixes T-13958
* Fix phpcs errors
* Exclude vendor dir in gulp-phpcs
* Bump tested WordPress version to 6.6.1
* Upgrade to node v20.17.0

### 9.4.2: 2024-06-13

* Fix typos #216 (thanks @szepeviktor!)
* Add is-external-link helper class to external links
* Bump tested WordPress version to 6.5.4

### 9.4.1: 2024-04-18

* Clarify sticky nav functionality presented in 7.9.1, Fixes #213 (thanks @semidivine!)
* Release version 1.1.4 of the `newtheme` starting script: Use phpcs.xml from devpackages 2.5.6 (2024-04-18)
* Bump tested WordPress version to 6.5.2

### 9.4.0: 2024-02-26

* Update unit tests for WordPress Coding Standards 3.0.1
* Prepare for PHP version 8.3
* Allow short array syntax for WPCS 3.0.1
* Fix accessibility strings always getting english versions if set to be translated via polylang
* Merge pull request #212 from digitoimistodude/dependabot/npm_and_yarn/ip-2.0.1
* Add unit tests for PHPCompatibility
* Upgrade PHP version in use to PHP 8.3
* Unit tests: Separately test for PHP 8.3 support
* Fix PHPCompatibility.TextStrings.RemovedDollarBraceStringEmbeds.DeprecatedVariableSyntax
* Bump WordPress to 6.4.2

### 9.3.6: 2024-01-10

* Fix the permission issue with self-updater
* Fix ACF element colors in Gutenberg editor
* Add WOFF2 to variablefont mixin #194 (thanks @raikasdev!)
* Fix the permission issue with self-updater
* Fix ACF element colors in Gutenberg editor
* Add WOFF2 to variablefont mixin #194 (thanks @raikasdev!)
* Remove duplicate gulp dependency #204 (thanks @Nostalginen!)
* Pop!\_OS support for newtheme script #202 (thanks @raikasdev!)
* Fix footer colors on WordPress.org theme preview, Closes #182 #200 (thanks @raikasdev!)
* Clean up ACF Block load script if expression #199 (thanks @raikasdev!)
* Fix PHP styling with new guidelines and fixed allowed blocks #198 (thanks @raikasdev!)
* Remove package-lock.json from gitignore #197 (thanks @raikasdev!)
* Add translation support for Custom Post Types and taxonomies #201 (thanks @raikasdev!)
* Update package: follow-redirects from 1.15.3 to 1.15.4
* Merge pull request #208 from digitoimistodude/dependabot/npm_and_yarn/babel/traverse-7.23.5
* Merge pull request #207 from digitoimistodude/dependabot/npm_and_yarn/postcss-8.4.31
* Merge pull request #210 from digitoimistodude/dependabot/npm_and_yarn/follow-redirects-1.15.4
* Merge pull request #211 from digitoimistodude/dependabot/npm_and_yarn/axios-and-browser-sync--removed
* Remove deprecated number-leading-zero rule
* Add reset for img
* Fix a regression with anchors not working when target not found, use in all hashes not just with js-trigger class
* Bump WordPress to 6.4.2

### 9.3.5: 2023-09-12

* Use semver + date in newtheme.sh startscript
* Add self-updater to the start script

### 9.3.4: 2023-09-07

* Fix focus to item when pressing back to top indicator
* Fix Gutenberg admin to use the correct variable font
* Update devpackages to 2.5.4
* Exclude WordPress.DB.SlowDBQuery.slow_db_query_meta_query
* Do not show skip link when focused via mouse from back to top
* Fix external link aria-label getting replaced #193 (kudos to @EliasKau!)
* Improvements and fixes to top scroll button #191 (kudos to @raikasdev!)

### 9.3.3: 2023-05-18

* Reset core-list bullet size
* Remove un-used `image_sizes` array from theme settings
* Run `theme_setup` on `after_setup_theme` hook
* Add custom `pll_translatable` setting to cpt and tax registration, use that to automatically register that content type for Polylang
* Fix ACF button color
* Remove deprecated number-leading-zero rule
* Add support for navigation version where main item is a clickable <button> (kudos to @Tumppex)
* If nav link anchor link, add class js-trigger for moveTo
* Fix JS error with link label not existing
* Close navigation if anchor item is nav-link
* Fix focus to target in anchors.js
* Bump tested up WordPress version to 6.2.1

### 9.3.2: 2023-03-21

* When page is loaded with "s" in url parameters try to set focus to search input field (kudos to @EliasKau)
* Calculate mobile nav top and height in js when there are air notificans present (kudos to @Tumppex)
* Fix irregularities with keyboard navigation #175 (kudos to @michaelbourne!)
* Fix navigation focus trap #175 (kudos to @michaelbourne!)
* Stylelint: Add number-leading-zero from devpackages
* Fix navigation issues #177 (kudos to @michaelbourne!)

### 9.3.1: 2023-02-24

* Remove nav-toggle styles not in use
* Add air-notification support to navigation.js
* Simplify button component even more by removing unused arrow conditional

### 9.3.0: 2023-02-17

* Change nav toggle position to static by default (kudos to @Tumppex)
* Add wrapper to navigation (kudos to @Tumppex)
* Mobile nav variable to change nav width (kudos to @Tumppex)

### 9.2.9: 2023-02-15

* Start script: Copy .nvmrc to project root from devpackages
* Improve block cache bypass logic and show bypass reason on log
* Try to determine if block has Gravity Forms form and always bypass cache if
* Stylelint fixes
* Simplify button SCSS component
* Remove Travis
* Add GitHub workflows for styles, html, php and js
* Fix version in .nvmrc
* Add badges for GitHub workflow build statuses
* Fix stylelint errors for v15
* Add smaller font-sizes 12 and 13
* Replace all suitable px sizes with rem as per stylelint-rem-over-px
* Update .stylelintrc from devpackages 2.5.3

### 9.2.8: 2023-01-30

* Add gap for categories
* Change default mobile breakpoint to 600px
* Remove breakpoints not in use
* Fix iPad navigation overlapping blocks when closed

### 9.2.7: 2023-01-06

* Remove unused no-js styles
* Remove unused CSS variables
* Simplify defining CSS variables in single use cases
* Remove `<kbd>` styles that are rarely needed
* Fix entry-footer
* Simplify category styles
* Simplify tag styles
* Remove \_blog.scss
* Add views/\_single.scss
* Use list element in categories instead of a paragraph

### 9.2.6: 2022-12-31

* Fix undefined body class
* Fix nav styles leaking to pagination
* Drop sanitize.css dependency
* Use sanitize.css/reduce-motion baked-in instead from npm

### 9.2.5: 2022-12-31

* Major navigation.js rewrite
* Modularize navigation.js
* Make navigation dependency free, completely dropping jQuery
* Perfected arrow and keyboard navigation patterns for A11y
* Fix aria-labels and aria-expanded
* Move nav-toggle inside nav landmark
* Fix focus trap
* Rewrite nav-toggle and 🍔
* Use clever way to visually present attr(aria-label) for 🍔
* Define mobile breakpoint more modern way with getPropertyValue
* Drop IE10-IE11 support for navigation.js
* Drop hamburgers npm module dependency
* Fix focusable elements if last item is a button
* Fix checkForSubmenuOverflow() function
* Fix resize functions
* Remove useless vars and functions
* Add plus and minus icons for mobile as default as inlined SVG
* Drop devDependencies from theme package.json, add instructions on contributing
* Fix acf-button color inside a Gutenberg block
* Navigation.js: Bail if navigation doesn't exist
* Remove leftover deprecated gutenberg-helpers.js
* Allow :not notations where needed

### 9.2.4: 2022-12-02

* Upgrade to [devpackages 2.4.9](https://github.com/digitoimistodude/devpackages/releases/tag/2.4.9)
* Disable no-descending-specificity rule in stylelint
* Ignore some obvious phpcs nags

### 9.2.3: 2022-11-28

* Add core/list-item to allowed blocks due to WP 6.1 and latest Gutenberg editor
* Remove extraneous padding in the first block on Gutenberg editor
* Make heading CSS variable naming more consistent
* Button component: Do not use border-radius on ghost button as it's inherited from button
* Button component: Use own border radius instead of shared with form fields
* Update generated README.md in the start script
* Fix ACF relation field color in Gutenberg blocks
* Simplify \_font-family.scss
* Deprecate core/code, core/columns, core/cover, core/embed, core/preformatted and code/verse CSS from Air-light, use Gutenberg official styles instead
* Add Inter as variable font

### 9.2.2: 2022-11-04

* Fix incorrect textdomain in external-link JS module
* Simplify CPT and taxonomy registration by using the class name also as a slug (Merge pull request #158 from digitoimistodude/cpt-tax-register-simplification, kudos to @timiwahalahti)
* Remove what-input dependency, use :focus and :focus-within instead, remove forced focus outline-color
* Bump tested up WordPress version to 6.1

### 9.2.1: 2022-10-28

* Ensure all blocks get default alignment styles despite core styles or Autoptimize order
* Increase default $transition-duration

### 9.2.0: 2022-10-12

* Add check for a11ySkipLink
* Add missing external-link-icon class
* Fix aspect-ratio mixin supports query
* Fix mobile nav-toggle one side is longer than others while button focused

### 9.1.9: 2022-10-06

* Disable stylelint max-line-length
* Remove default list color conflicting with ACF fields
* Remove global link focus color that is no longer used
* Improve focus ring style, add variable for color: `--color-focus-outline`
* Improve accessible Finnish translation of back to top
* Improve back to top keyboard accessibility

### 9.1.8: 2022-09-29

* Remove leftover styles from default block
* Remove outdated scripts in gutenberg-editor.js that causes an error on wp-admin

### 9.1.7: 2022-09-28

* Add external-link-icon styles for mobile
* Remove setup_editor_styles function because it breaks stage+prod and block styles still load correctly without that
* Improve anchors.js formatting
* Add a11y-skip-link JS module for ensuring that skip links always focus to first heading

### 9.1.6: 2022-09-14

* Fix font-size not inheriting to Gutenberg editor
* Fix rich text font size in Gutenberg editor
* Fix Gutenberg editor font-sizing issues
* Deprecate responsive-font() mixin in favor of clamp-calc() function

### 9.1.5: 2022-09-12

* Fix: Use ACF defined font styles in ACF fields
* Fix acf icons visibility issue
* Fix link colors in sidebar
* Add default font-family for form select items
* Remove opinionated label font-weight
* Remove opinionated nav default dropshadow from sub menus
* Remove opinionated nav default border-color from sub menus
* Remove opinionated bubble tip from sub menus

### 9.1.4: 2022-09-07

* Fix: Viewport padding/white-space on mobile devices
* Fix: Leftover variable not defined

### 9.1.3: 2022-09-07

* Consistency for variable `--font-weight-paragraph`
* Remove `has-light-bg` and `has-dark-bg` classes that are no longer used by back to top feature
* Increase padding on mid-sized screens to prevent container sticking to sides

### 9.1.2: 2022-09-07

* Clean up is-external-link from CSS that is no longer used
* Consistency in variables: `--font-size-paragraphs` -> `--font-size-paragraph`
* Remove unused `--font-size-default` and combine with `--font-size-paragraph`
* Fix list font-sizes not inheriting from paragraph styles
* Define default font sizes and line-heights on body level instead in separate elements

### 9.1.1: 2022-09-06

* Improve form checkbox and radiob button styles
* Form checkboxes and radio buttons: Add bouncy check animation
* Clarify reset for checkboxes and radio buttons for gravity forms
* Combine `--line-height-paragraphs-blog` and `--line-height-paragraphs` to one unified CSS variable: `--line-height-paragraph`

### 9.1.0: 2022-09-01

* Improve post title color and size accessibility in Gutenberg editor
* Remove unnecessary float reset from nav item
* Add image-background-layer to image helper classes
* Fix enumerating grid in columns
* Bump tested up WordPress version to 6.0.3

### 9.0.9: 2022-08-25

* Add custom version of sanitize.css
* Improve external-links.js JavaScript
* Ignore external link arrows for links that have only imgs in them
* Add ignore classes for external links
* Generate SVG arrow for external links via JS instead of CSS
* Clean up old different versions of external-link.svg files

### 9.0.8: 2022-08-23

* Deprecate lazyload from CSS as it is no longer needed after loading="lazy"
* Add image-background helper class for imgs as backgrounds
* Add CSS clamp function as helper for responsive font sizes
* Add CSS aspect-ratio helper for responsive images

### 9.0.7: 2022-08-22

* Add box-model helper
* Remove leftover getLocalization declaration
* Remove block-file-slug.svg placeholder, replace with .gitkeep
* Improvement: Consistent $transition-duration, less movement

### 9.0.6: 2022-06-30

* Upgrade to [devpackages 2.4.7](https://github.com/digitoimistodude/devpackages/releases/tag/2.4.7)
* Remove air_helper_custom_settings_post_ids hook that is no longer used (kudos to latenssi @ wpfi Slack for spotting it!)

### 9.0.5: 2022-06-09

* Prevent mobile navigation flickering before JS has been loaded

### 9.0.4: 2022-06-09

* Add default localization for accessible carousels to support Air-blocks 1.1.2

### 9.0.3: 2022-06-08

* Remove leftover %screen-reader-text from editor styles
* Move editor styles outside of the global gutenberg-editor-styles.scss
* Fix form elements in ACF block previews
* A11y: Localizations for possible carousel blocks from Air-blocks

### 9.0.2: 2022-05-27

* Fix critical regression about ACF blocks not outputting, Fixes #153

### 9.0.1: 2022-05-26

* Disable dropdown box-shadow by default
* Improve placeholder for new project
* Fix typo in CSS variable name
* Fix critical JS error with calling getChildAltText() in vain
* Fix outdated release command

### 9.0.0: 2022-05-24

#### Major update

* Rebranding: Update default logo
* Deprecate %screen-reader-text, use mixin instead
* Deprecate old lazy load styles from future themes
* Add var(--box-shadow-sub-menu) for desktop navigation
* Add more clear starting point with demo content placeholder
* Add support for anchors in the block (thanks @timiwahalahti)
* Disable support for additional CSS classes in blocks (thanks @timiwahalahti)
* Bump tested up WordPress version to 6.0
* Fix back to top label for screen readers
* Add data-version attribute to footer indicating Air-light version in use

### 8.4.7: 2022-05-24

* Remove \_slick.scss, deprecate slick-carousel slider (carousel block is part of [air-blocks](https://github.com/digitoimistodude/air-blocks) now)

### 8.4.6: 2022-05-23

* Combine media queries
* Deprecate heading-hero component

### 8.4.5: 2022-05-20

* Use only one burger animation, revert burger style on mobile

### 8.4.4: 2022-05-20

* Simplify external link selector
* Add js-trigger to skip-link
* Combine nav-mobile media queries
* Update stylelintrc rules block-closing-brace-newline-after and at-rule-empty-line-before
* Fix cubic-bezier mixin $duration variable
* Fix iOS 15 nav width style bug
* Fix iOS 15 scrolling bug with `height: -webkit-fill-available;`
* New navigation animation: slide (default), change to fade by setting `$nav-slide-animation: false;`

### 8.4.3: 2022-05-13

* Fixes #134, nav-toggle menu position shifting on iOS 15.1

### 8.4.2: 2022-05-12

* Add anchors as JS module
* Deprecate vanilla-lazyload and support for older JS-based lazy loading methods
* Drop jQuery
* Rewrite accessible back to top functionality and styles
* Combine external link labels with external link JS module
* Refactor front-end.js

### 8.4.1: 2022-05-12

* Fix paddings on editor
* Remove deprecated text columns block
* Remove default list-type from typography scss and use core-list instead
* Fix responsive lists on gutenberg editor

### 8.4.0: 2022-05-12

* Fix cover block default text color
* Fix overflow in stacked blocks
* Fix core-list not working properly on Gutenberg editor preview
* Fix navigation dropdown menus opening inside viewport after resizing window
* Fix code block style bugs
* Fix pullquote blockquote alignment
* Fix naming of core-button to core-buttons
* Use var(--color-blockquote) color variable in pull quotes
* Simplify core-list block
* Smaller external link icon
* Remove deprecated core-gallery styles, use Gutenberg core styles instead
* Scroll to top when triggering mobile navigation to ensure no gaps are between header and navigation (thanks @Tumppex!)
* Simplify link formatting

### 8.3.8: 2022-04-29

* Fix variable name in link formatting (--color-link-text-hover)
* Add proxyUrl (devpackages 2.4.5)
* Fix config.styles.src that causes gutenberg styles not to compile (devpackages 2.4.6)

### 8.3.7: 2022-04-11

* Remove leftover code from Gutenberg editor
* Move externalLinkDomains list to theme settings for consistency (devpackages 2.4.4)
* Add cssnano and related postcss-plugins, deprecate gulp-clean-css that is in maintenance-mode (devpackages 2.4.4)
* Improve watch task to be more performant (devpackages 2.4.4)
* Add gulp-size and verbose information to console (devpackages 2.4.4)
* Add instructions on how to contribute (devpackages 2.4.4)
* Update stylelint-config-standard, stylelint-config-recommended-css, caniuse-lite and js related packages (devpackages 2.4.4)
* Get phpcs task from devpackages 2.4.4
* Bump tested up WordPress version to 5.9.3

### 8.3.6: 2022-03-31

* Update browser-sync to 2.27.9 (devpackages 2.4.3)
* Fix main title width in article editor
* Add fonts and font-smoothing in article view of the Gutenberg editor

### 8.3.5: 2022-03-25

* Fix: Margin reset breaks article blocks in editor, limit it for ACF blocks only

### 8.3.4: 2022-03-16

* Make edit-post-visual-editor\_\_post-title-wrapper background to match WordPress brand color
* Bump tested up WordPress version to 5.9.2

### 8.3.3: 2022-03-08

* Upgrade to [devpackages 2.4.2](https://github.com/digitoimistodude/devpackages/releases/tag/2.4.2) (details below)
* Upgrade stylelint to 14.5.3
* Update .stylelintrc rules as per the [official recommendations](https://github.com/stylelint/stylelint-config-recommended/issues/157#issuecomment-1056967465)
* Use kebab-kase in fontface mixin from now on
* Bump tested up WordPress version to 5.9.1
* Upgrade eslint to 8.10.0
* Upgrade eslint-config-airbnb to 19.0.4
* Remove deprecated babel-eslint and use @babel/eslint-parser instead
* Revove outdated and unmaintained gulp-eslint and use gulp-eslint-new instead
* Allow js/src/front-end.js to be linted, fix file for JS warnings

### 8.3.2: 2022-02-10

* Reset blockquote margins as it breaks the auto layout in some situations
* Deprecate hiddentext() mixin that is not used anymore

### 8.3.1: 2022-02-08

#### Major update

* Support new native_lazyload_tag() from air-helper
* Add edit link to page
* Move edit link to its own template tag function
* Clean up page.php
* Always allow loading ACF block on preview
* Add a filter to allow modidying ACF block cache keys
* Deprecate default hero template part as it's never used (it's recommended to use a Gutenberg block for it)
* Remove archive.php (it's rarely used, it's similar to index.php and when used, it's always customized anyway)
* Change var(--form-gap) to support both grid-row-gap and grid-column-gap

### 8.3.0: 2022-02-02

* This updates removes some fonts that should not be there, sorry about that

### 8.2.9: 2022-02-02

* Add more weights for Inter font family, fix issue with font file capitalizations and rendering

### 8.2.8: 2022-02-02

* Modified functions.php custom settings to work with Air setting groups plugin.
* Dropped CSS grid mixin as all major browsers support it and we no longer need fallbacks for Edge and Safari

### 8.2.7: 2022-01-26

* Add new styles for page/post title
* Remove page/post title input styles that are no longer in Gutenberg of 5.9
* Fix title edit styles for Gutenberg in 5.9
* Add separate locales for title related instruction text
* Fix remove_gutenberg_inline_styles hook, add extra check, Fixes #144
* Update deprecated filters and their parameters #141 (thanks @dylanelliott27!)
* Update file sizes to README.md
* Fix maybe_show_error_block function title not showing if manually set
* Fix archive template single post item
* Fix acf block cache check
* Added filter air_acf_block_cache_lifetime to adjust blocks cache lifetime
* Bump tested up WordPress version to 5.9.0

### 8.2.6: 2022-01-07

* Fixes for wp-block-list
* Auto-indent multi-line list blocks
* Bump tested up WordPress version to 5.8.3

### 8.2.5: 2022-01-03

* Open submenus on the left if the nav items go over the viewport, Fixes #5
* Added $is_preview and $post_id to be always handed over to block template
* Create empty inc/functions directory for custom functions

### 8.2.4: 2021-12-03

* Fix gallery and image block wide and full width version paddings in mobile editor
* Fix aligned image paddings in gutenberg editor
* Fix wide gallery mobile padding issue in Gutenberg editor
* Fix standard view gallery block alignment issue in Gutenberg Editor
* Fix block gallery full width padding issues in mobile gutenberg editor
* Fix for gutenberg edirot alignleft/alignright images: Do not force widths via var for galleries in gutenberg-editor
* Fix padding for post title block in article view
* Fix padding in mobile view of the gutenberg editor

### 8.2.3: 2021-12-01

* Reset default gap in .wp-block in Gutenberg editor
* Fix align wide paddings for certain breakpoints and blocks
* Fix core/list with is-style-default set
* Better default core/separator styles

### 8.2.2: 2021-11-26

* Add default gform_confirmation_message styles

### 8.2.1: 2021-11-25

* Fix `newtheme` starting script not generating a README.md
* Fix `newtheme` starting script not building theme JS and CSS for the first time

### 8.2.0: 2021-11-24

* Fix acf-reset interfering with WYSIWYG editor toolbar icon fonts
* Update to devpackages 2.4.1

### 8.1.9: 2021-11-19

* Add missing strip-unit SCSS function

### 8.1.8: 2021-11-18

* Fix `var(--gap-between-dropdown-toggle)` defined two times

### 8.1.7: 2021-11-18

* Improve `var(--padding-sub-menu-vertical)` behaviour in animations
* Change `var(--padding-sub-menu-horizontal)` to more describing `var(--padding-sub-menu-link-horizontal)`
* Add out animation for desktop navigation
* Remove aspect-ratio() mixin that is no longer needed (CSS has `aspect-ratio`) natively
* Remove hex-to-rgb() mixin that is no longer used

### 8.1.6: 2021-11-17

* Fix ACF field reset specificity that aws affecting block styles

### 8.1.5: 2021-11-15

* Remove unused CSS from \_typography.scss
* Move blockquote styles inside core-blockquote block

### 8.1.4: 2021-11-11

* Replace static contributors with Repobeats analytics image
* Bump tested WordPress version to 5.8.3
* Fix ACF element font styles by unsetting them
* Add default form styles for Gutenberg editor
* Fix line breaks and formatting in the generated default README.md

### 8.1.3: 2021-11-09

* Add has-unified-padding-if-stacked helper class for stacked blocks

### 8.1.2: 2021-11-09

* Devpackages 2.3.7: Change back from @ronilaukkarinen/stylelint-declaration-strict-value@1.7.13 to official stylelint-declaration-strict-value@1.8.0
* Devpackages 2.3.7: Fix rule for declaration-strict-value
* Devpackages 2.3.7: Upgrade to webpack 5
* Devpackages 2.3.7: Update packages

### 8.1.1: 2021-11-05

* Nav-toggle label text improvements
* Replace nav toggle margin with more bulletproof `gap` attribute

### 8.1.0: 2021-11-04

* Fix var(--padding-sub-menu-vertical), add separate var for var(--padding-sub-menu-link-vertical)
* Fix sub-menu of sub-menu vertical alignment based on the variable and not fixed amount
* Fix dropdown alignment to left, remove deprecated fixed 5%
* Add simpler link component with text-underline-offset
* Update .browserslistrc, ignore older Edge and Samsung Browser

### 8.0.9: 2021-11-04

* Update to devpackages 2.3.5

### 8.0.8: 2021-11-04

* Update to devpackages 2.3.4

### 8.0.7: 2021-11-04

* Fix safe space visibility before triggering submenu with mouse

### 8.0.6: 2021-11-03

* Update to devpackages 2.3.2

### 8.0.5: 2021-11-02

* Remove outdated breadcrumbs scss and hooks that are no longer supported in-theme

### 8.0.4: 2021-11-02

* Update to devpackages 2.3.1 (switch to @ronilaukkarinen/gulp-stylelint, watch task improvements)

### 8.0.3: 2021-10-29

* Update to devpackages 2.3.0 (switches gulp-stylelint to stylelint from exec/cli)

### 8.0.2: 2021-10-28

* Update sass to 1.43.4
* Sass speed improvements with dart-sass, Fiber and sass.sync()
* Fix deprecations in helper scss mixins

### 8.0.1: 2021-10-28

* Remove outdated printer-for-errors-of-gulp-plugins, trade-off with performance and watch task crash on SCSS errors
* Theme file structure up to date, update font-sizes file to be more consistent as singular file name

### 8.0.0: 2021-10-28

* Upgrade stylelint to v14
* Drop nodejs 12 support
* Drop node-sass and fibers support
* Lint styles with new rules
* Add new stylelint rules
* Improve watch task, speed it up, inject CSS first in dev environment
* Change stylelint-disable exceptions per line and with double slash scss syntax
* Add doiuse exceptions with doiuse-disable
* Update .browserslistrc
* Require node 14+ and PHP 7.4+

### 7.9.9: 2021-10-27

* Increase default gap between main level nav items
* Remove leftover margin-right from nav item that has sub items

### 7.9.8: 2021-10-25

* Add max-width variable for navigation dropdown helper pseudo element, in some situations causes unwanted dropdown triggering
* Update default README.md boilerplate for project

### 7.9.7: 2021-10-20

* Replace margin-right with more flexible `gap: var(--gap-between-dropdown-toggle);`
* Use gap in desktop main level menu-items instead of margin

### 7.9.6: 2021-10-13

* Fix: Floating back to top element has too much z-index priority

### 7.9.5: 2021-10-07

* Fix: Reusable Blocks are not available #116

### 7.9.4: 2021-10-06

This release includes only form style and accessibility improvements.

* Consistency in form variables, fix issues with required labels
* Add support for gravity forms baked in required labels
* Fix select icon gap
* Bigger default font-size for inputs in demo
* Consistent label font size
* Add consistent variables and sizes for form elements
* Fix conflicting select styles
* Add consistent --form-gap for gravity form elements
* Improve form styles

### 7.9.3: 2021-09-29

* Group accessible color variables for error, success and warning states

### 7.9.2: 2021-09-28

* Fix a bug on Firefox in sticky nav in situations where user refreshes the browser in the middle of the page before any scrolling done
* Fix extra gap in Gutenberg editor's .wp-block

### 7.9.1: 2021-09-24

* Simplify sticky navigation, rewrite in Vanilla JS

### 7.9.0: 2021-09-24

* Fix typography line-height leaking from core heading block (kudos to @Tumppex for noticing this!)
* Change gutenberg-content div to HTML5 article element with class article-content to be more clear (since all content are now Gutenberg)

### 7.8.9: 2021-09-23

* Ensure Gravity Forms honeypot is hidden
* Add support for native columns in Gravity Forms 2.5+

### 7.8.8: 2021-09-15

* Fixes for alignwide and alignfull logic: Make them behave exactly the same in breakpoints other than desktop
* Add missing .wp-block-list class to \_core-list.scss
* Bump tested WordPress version to 5.8.1

### 7.8.7: 2021-09-03

* Accessibility: Trigger closing the dropdown to ESC key only if we are already browsing the dropdown menus in sub navigation. Fixes the bug where focus point jumps also in all other situations as well for example when no dropdowns are open.

### 7.8.6: 2021-09-02

* Accessibility: Switch order of external link label text so that the actual link text comes first, then "External site" and "opens in a new window" (thanks @samikeijonen), Fixes #125

### 7.8.5: 2021-09-01

* Fix eslint picking up the wrong module .eslintrc
* Fix build, lint JS

### 7.8.4: 2021-09-01

* Accessibility: Prevent navigation dropdown toggle to be opened by mouse since we already use hover event and don't want it to get stuck open without consent

### 7.8.3: 2021-09-01

* Accessibility: Navigation patterns: Make it possible to close with ESC while hovering
* Accessibility: Add hover event via JS instead of CSS (to be able to untrigger)
* Navigation JS: Add missing class
* Navigation JS: Remove preventdefaults as there are no defaults for these items
* Accessibility: Fix keyboard navigation for NVDA screen reader
* Add aria labels to links with img inside, Fixes #111 (PR #124)

### 7.8.2: 2021-08-26

* [devpackages 2.2.7](https://github.com/digitoimistodude/devpackages/releases/tag/2.2.7): Stylelint/order: Add order rule for @import

### 7.8.1: 2021-08-26

* Set root size for rems for Gutenberg editor
* Fix CSS leak to wp-core-ui items #123

### 7.8.0: 2021-08-25

* Fix setFigureWidths in gutenberg-editor.js
* Aligned single gallery fixes for Gutenberg

### 7.7.9: 2021-08-25

* Set non lazyloaded figure widths #121

### 7.7.8: 2021-08-25

* Fix --width-child-img CSS variable name
* Fix core-gallery block width
* Fix typo in script comment

### 7.7.7: 2021-08-06

* Don't force outline-color for general items which colors we don't know

### 7.7.6: 2021-08-03

* Consistency update for accessibility: Use currentColor in :focus and skip-link outline, add outline-offset for global area links to make their outlines more visible
* Bump tested WordPress version to 5.8

### 7.7.5: 2021-06-24

* Update to devpackages 2.2.5.

### 7.7.4: 2021-06-23

* Updates from [devpackages](https://github.com/digitoimistodude/devpackages) 2.2.4: Speed up styles tasks by adding a separate watch for prodstyles

### 7.7.3: 2021-06-22

* Fix order/order errors, fix build

### 7.7.2: 2021-06-22

* Attempt to fix build: Lint theme SCSS with new rules
* Fix stylelint syntax error in \_nav-desktop.scss
* Update html-validator-cli for travis unit tests

### 7.7.1: 2021-06-22

* Update to devpackages 2.2.1

### 7.7.0: 2021-06-22

* Add editor-post-title styles for Gutenberg

### 7.6.9: 2021-06-22

* Remove plugin territory hooks in favor of [air-helper#42](https://github.com/digitoimistodude/air-helper/issues/42)

### 7.6.8: 2021-06-22

* Fix broken js after generating air as mentioned in #119
* Incorporate nu-html-checker to be part of travis unit tests

### 7.6.7: 2021-06-22

* Navigation accessibility: Remove incorrect attributes from dropdown link
* Navigation accessibility: Fix screen reader class
* Navigation accessibility: Fix invalid aria-expanded
* Navigation accessibility: Fix crucial landmark role issues, revert from 6.3.4 c93af48d5e793bc24b9932b72069da1b21c1af83
* Clean up <head> for valid HTML
* Fix gravity forms styles reset
* Disable printing Gravity Forms js straight after <head> (invalid HTML)

### 7.6.6: 2021-06-17

* Improve nav layout with in between breakpoints: Do not wrap main level menu items by default on desktop

### 7.6.5: 2021-06-15

* Allow list items to have external links
* Fix list widths in gutenberg, add default style for ::marker

### 7.6.4: 2021-06-09

* Show message if no search results found
* Add default localization for "No results for your search"

### 7.6.3: 2021-06-09

* Fix too quick transition-duration (.08, one zero too much, thx @Tumppex for spotting this!)
* Add WCAG AAA+ compliant validation error colors for gravity forms 2.5

### 7.6.2: 2021-06-09

* Gravity Forms 2.5 base styles
* Fix main navigation link item having only hash instead of link

### 7.6.1: 2021-06-01

* Reset wp-admin list styles on metabox UI

### 7.6.0: 2021-05-31

* Update devpackages to [2.2.0](https://github.com/digitoimistodude/devpackages/releases/tag/2.2.0)

### 7.5.9: 2021-05-28

* Add --grid-gap for grid mixin defaults

### 7.5.8: 2021-05-28

* Add missing --padding-main-level-vertical-mobile, fix incorrect --padding-sub-menu-vertical-mobile
* Use git version of sanitize.css, fix navigation white space

### 7.5.7: 2021-05-28

* Fixes for gutenberg blocks on mobile, fix table block width
* Fix core gutenberg block widths/paddings on small mobile screens

### 7.5.6: 2021-05-28

* Add $container-macbook-air breakpoint and --padding-container-horizontal-large for better fit to smaller screens

### 7.5.5: 2021-05-26

* Bypass the file_get_contents restriction [#112](https://github.com/digitoimistodude/air-light/pull/112)

### 7.5.4: 2021-05-24

* Updates from [devpackages 2.1.8](https://github.com/digitoimistodude/devpackages/releases/tag/2.1.8)

### 7.5.3: 2021-05-24

* Updates from [devpackages 2.1.7](https://github.com/digitoimistodude/devpackages/releases/tag/2.1.7)

### 7.5.2: 2021-05-24

* Fix unexpected flexbox style bug with sub menu items

### 7.5.1: 2021-05-24

* Include sanitize.css 12.0.1 and reduce motion from repo
* Make possible to change active mobile nav bg color
* Change nav desktop font sizes as vars

### 7.5.0: 2021-05-21

#### Major update

* Add social media accounts to theme settings #109
* Add custom setting groups support #108
* Automate acf block icon from svg file #107
* Check if it's allowed to show this block in the context #106
* Add error block functionality #105
* Wrap theme settings to after_setup_theme action, fix filter name #104
* Update nav walker to 4.3.0: Accessibility, schema and security updates #103
* Remove deprecated blocks, Fixes #93 (thanks @LukaszJaro!)
* Fix too specific paragraph styles (moved from gutenberg/block/core-paragraph.scss to gutenberg/formatting/\_paragraph.scss)
* Add cubic-brezier() mixin
* Bump tested WP version to 5.7.2

### 7.4.5: 2021-05-20

* Add example block icon SVG for ACF Gutenberg block init
* Wrap theme settings to after_setup_theme action, fix filter name #104

### 7.4.4: 2021-05-19

* Fix alignwide and alignfull variables in front end

### 7.4.3: 2021-05-19

* Add variables $width-full and $width-wide for default Gutenberg blocks styling
* Fix typo in code comment
* Fix preformatted text alignment in Gutenberg

### 7.4.2: 2021-05-19

* Improve block functionality #102

### 7.4.1: 2021-05-18

* Deprecate modules SCSS
* Example of your next ACF Gutenberg block

### 7.4.0: 2021-05-18

#### Major Gutenberg update

* This version adds ACF-Gutenberg blocks (PR: Moving on from modular content to ACF-Gutenberg blocks #100)
* Fixes for stylelint (most notably getting rid of vendor prefixes in styles)
* Fully working editor styles
* Editor JS and editor lazyload support
* Deprecate modular-content.php
* Remove demo content from the theme (from now on it's a separate WordPress plugin)
* Bunch of small fixes

### 7.3.1: 2021-05-12

* Fix: Add missing autoprefixer for dev styles gulp task

### 7.3.0: 2021-05-12

* Fix select dropdowns showing toggle icon twice (`appearance: none;` is well supported by Safari 14)

### 7.2.9: 2021-05-11

* Fix 404, Fixes #99

### 7.2.8: 2021-05-04

* Consistency in file headers
* Fix replaces by exporting the variables in the start script

### 7.2.7: 2021-05-04

* Fix NEWEST_AIR_VERSION variable in additions script

### 7.2.6: 2021-05-04

* Fix gulp command syntax for development and production environments in new theme starting script

### 7.2.5: 2021-04-29

* Make sure children don't have list bullets
* Accessibility: Make focus ring a little more engaging ([source](https://twitter.com/argyleink/status/1387072095159406596))

### 7.2.4: 2021-04-23

* Fix navigation #94 (huge thanks to @michaelbourne!)
* Fix focus trap problems in mobile navigation if last nav item is a dropdown #26
* Fix issue with setLazyLoadedFigureWidth not imported properly
* Contributing instructions up to date according to the latest version of dudestack

### 7.3.1: 2021-04-22

* Mobile nav overflow fix #95 (thanks @Tumppex and congrats for the first pull request!)
* Documenting code: Add comment about Gravity Forms with ajax
* Remove deprecated vagrant instructions from the Read me file
* Theme metadata in style.css: Add more clear instructions, update date format

### 7.2.2: 2021-04-21

* Include missing heading-hero component
* Add default core/table styles

### 7.2.1: 2021-04-21

* Refine heading levels
* Refine button components
* Add wide core/separator block
* Add function end comments and few minor PHP styling changes
* Truncate too long button texts
* Components more presentable way in demo, fix button styles and sizes

### 7.2.0: 2021-04-21

* Fix critical style bug with default button hover and ghost button colors
* Fix phpcs task src

### 7.1.9: 2021-04-20

* Change font-family attributes from SCSS variables to CSS variables

### 7.1.8: 2021-04-20

* Support for SCSS source maps [(devpackages 2.1.5)](https://github.com/digitoimistodude/devpackages/releases/tag/2.1.5)
* Fix error reporting on watch. Awaiting [Pull Request](https://github.com/wulechuan/wulechuan-js-printer-for-errors-of-gulp-plugins/pull/3). [(devpackages 2.1.5)](https://github.com/digitoimistodude/devpackages/releases/tag/2.1.5)
* Bump tested WP version to 5.7.1

### 7.1.7: 2021-04-20

* Get updates from [devpackages 2.1.3](https://github.com/digitoimistodude/devpackages/releases/tag/2.1.3)

### 7.1.6: 2021-04-20

* Styles related performance update for gulp from [devpackages 2.1.2](https://github.com/digitoimistodude/devpackages/releases/tag/2.1.2)

### 7.1.5: 2021-04-16

* More consistency in form variables
* Add --gap-between-dropdown-toggle as CSS variable
* Color links for AA and AAA
* Move theme documentation to Wiki

### 7.1.4: 2021-04-16

* Add Laragon support #92 (thank you @divn!)
* New theme script dependencies partial: Gulp styles to later stage
* Release version 1.0.5 of newtheme starting scripts

### 7.1.3: 2021-04-15

* Fix failing build with missing color variable

### 7.1.2: 2021-04-15

* Add more element color variables
* Remove unused magnific popup styles from the theme
* More sensible link variables
* Do not depend on brand color pool variables, use element variables instead in partials

### 7.1.1: 2021-04-15

* Remove auto-color function, transform all the rest of the color variables as CSS variables

### 7.1.0: 2021-04-15

* Font size variables to be more precise and fail-proof #91

### 7.0.9: 2021-04-15

* Fix conflicting duplicate CSS variable `var(--color-dropdown-toggle)`.

### 7.0.8: 2021-04-14

* Fix skip link color on focus
* Fix sub-navigation padding when toggled on keyboard
* CSS variablize nav-mobile
* Add CSS variables to desktop nav, add font-weights as vars
* Fix editor font paths
* More CPU friendly php watch
* Set figure widths for gutenberg
* Helper variables $font-sans and $font-serif
* Add \_font-face for defining webfont files, clarify font-family

### 7.0.7: 2021-04-14

See [pull request #89](https://github.com/digitoimistodude/air-light/pull/89).

Project start scripts have been refactored completely same manner than in this [dudestack PR](https://github.com/digitoimistodude/dudestack/pull/11).

### Changes:

* Add working WSL support, linked also to [windows-lemp-setup](https://github.com/digitoimistodude/windows-lemp-setup)
* Modularize bash scripts, add files as partials. From now on we need to edit files under bin/tasks. Only couple of files are different, for example sed commands for debian and macOS vary, that's why
* Beautiful formatting and instructions for the start script (header.sh, footer.sh)
* Prevent running directly with bash or sh to prevent possible issues with them
* Variable all the things * no need to search and replace stuff
* Fix typos
* Add checks for missing packages (WSL mainly)
* Add extra notifications that these scripts need Air-light and dev servers to work

### 7.0.6: 2021-04-07

* Fix color variable inside conditional

### 7.0.5: 2021-04-07

* CSS variables #85
* Add default link within content styles
* Fix Gutenberg formatting

### 7.0.4: 2021-04-07

* Remove PHP 7.0 from unit tests
* Migrate from travis-ci.org to travis-ci.com
* Fix build, add escapes and missing npm package

### 7.0.3: 2021-04-06

* Merge PR #82: Gutenberg improvements
* Merge PR #84, Fixes #83
* Major Gutenberg updates as proposed in #82

### 7.0.2: 2021-04-06

* Add .hintrc for webhint
* Update .browserslistrc, deprecate Internet Explorer and Opera mobile browsers
* Update packages and scripts

### 7.0.1: 2021-04-05

* Fix Gutenberg editor styles not loading: #78: Remove dependencies from style enqueue fixes #77

### 7.0.0: 2021-04-01

* Prevent FOUT when loading webfonts
* Allow email notifications in Travis unit tests
* Fix stylelint error reporting in Travis unit tests

### 6.9.9: 2021-04-01

* Implement [devpackages 2.0.7](https://github.com/digitoimistodude/devpackages/releases/tag/2.0.7): Fix stylelint task not showing report
* Implement [devpackages 2.0.7](https://github.com/digitoimistodude/devpackages/releases/tag/2.0.7): Remove unused browsersync watch

### 6.9.8: 2021-03-30

* Improve documentation
* Fix navigation sub-menu hover animation: Hover padding should only emerge on sub-menu items, not to main menu items

### 6.9.7: 2021-03-30

* Add space after blockquote cite
* Update @font-face for Slightly Deeper Browser Support
* Remove excess amount of fonts

### 6.9.6: 2021-03-30

* Change default demo font to Inter
* Fix problem with blockquote overflow on desktop

### 6.9.5: 2021-03-29

* Disable sub-menu "out"-animation for slicker navigation experience (.hover-intent)

### 6.9.4: 2021-03-29

* Smooth and fast animation update for desktop sub-menus
* Use 20ms padding animation instead of opacity
* Add vars for $dropdown-toggle-size and $use-dropdown-toggle-animation

### 6.9.3: 2021-03-23

* Add reminder note about responsive navigation breakpoint width in JS
* Fix demo content navigation padding

### 6.9.2: 2021-03-18

* Fix nav toggle visibility on desktop
* Fix sub menu link hover color

### 6.9.1: 2021-03-18

* Fix gulp watch task causing CPU hogging

### 6.9.0: 2021-03-18

* Remove mqpacker that is causing problems with media queries
* Update packages (linters, postcss and browsersync)
* Bump tested WP version to 5.7

### 6.8.9: 2021-02-26

* Cleaner HTML markup for branding (thanks [@samikeijonen](https://github.com/samikeijonen))

### 6.8.8: 2021-02-26

* Add themeDir to all asset file paths/names to match devpackages #75

### 6.8.7: 2021-02-25

* Accessibility and best practices: Move skip link to right after body tag
* Add consistent arguments for default button style components
* Off-repository: Add ghost button to the examples

### 6.8.6: 2021-02-25

* Fix js task signal async completion

### 6.8.5: 2021-02-25

* Fix newtheme.sh theme generate script clean ups for new CSS structure
* Documentation updates for the release cycle

### 6.8.4: 2021-02-24

* No default_featured_image by default, improvements to hero #73, Fixes #72

### 6.8.3: 2021-02-24

* Fix burger margin that should be only present with label

### 6.8.2: 2021-02-23

* Update release cycle steps to documentation
* Update bash alias `release_new_air_version`
* Add packing and cleaning up files part of the same process

### 6.8.1: 2021-02-23

* Fix block class post type prefix #71

### 6.8.0: 2021-02-22

* Localize add a menu label, Fixes #64
* Change static breakpoints as variables
* Fix styles not injecting to the browser on save
* Fix issue with blockquote flowing out of viewport, Fixes #66
* Fix all relative paths due structure change
* Add missing default title for a blog post
* Fix get_asset_file calls on gutenberg script enqueues
* Check if img element has clientWidth before setting CSS variable

### 6.7.3: 2021-02-20

* Fix mistake in CSS class name

### 6.7.2: 2021-02-19

* Build production and development JS builds in gulp watch, [#63](https://github.com/digitoimistodude/air-light/pull/63)
* Make us of new wp_get_environment_type function in modular content, [#65](https://github.com/digitoimistodude/air-light/pull/65)

### 6.7.1: 2021-02-18

* Accessibility: Add required outline parameters for global links, [ab54efc](https://github.com/digitoimistodude/air-light/commit/ab54efc820bfd94a6a15ea492e143971c54ea684)
* Add button components, [#62](https://github.com/digitoimistodude/air-light/pull/62)

### 6.7.0: 2021-02-18

* Add correct protocol to newtheme script final output

### 6.6.9: 2021-02-16

* Update documentation about installing Air-light on any development environment
* Update clean up script bin/air-move-out.sh to clean up cpt files that are not allowed in theme directory because they are "plugin-territory functionality". Also documenting this in this point.

### 6.6.8: 2021-02-16

* Fix cpt files not included in official version
* Open changelog to be more consistent with the releases

### 6.6.8-alpha: 2021-02-16

* Open changelog to be more consistent with the releases
