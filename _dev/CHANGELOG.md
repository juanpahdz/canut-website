### [Unreleased]: 2026-08-12

* Darken the homepage hero's media overlay (`.home-hero-media::after`, `_front-page.scss`) so the title/subtitle read more clearly over bright photos/video - black tint raised from 20% to 35%, the bottom-up `primary-dark` gradient from 70% to 75%
* Animate the homepage hero title (`.home-hero-title`, `template-parts/front-page/hero.php`) in letter by letter on page load - each letter is now its own `<span class="home-hero-letter">` (grouped into `.home-hero-word` spans so words don't break mid-letter), fading and sliding up with a slight overshoot/bounce (`easeOutBack` timing), staggered via an inline `--letter-index` custom property. The visible spans are `aria-hidden`, with the full title kept as the `<h1>`'s `aria-label` for screen readers; disabled under `prefers-reduced-motion: reduce`. Word spacing is drawn with a CSS margin instead of relying on the literal space character between `.home-hero-word` spans, since that whitespace-only text node was being collapsed away

* Remove the "Con CANUT, esto ya no pasa." comparison card from the product page (`woocommerce/single-product.php`, inside `.product-comparison`) - the large image + title/text/"Ver tecnología" link block that followed the "¿Se te hace familiar?" scenarios gallery. Dropped its five ACF fields (`comparison_card_image`/`_title`/`_text`/`_link_label`/`_link_url`, `inc/acf-fields/product-canut.php`) and `.product-comparison-card*` styles (`_product.scss`) - the scenarios gallery and section title above it are untouched

* Copy the full `@phosphor-icons/core` `fill` weight set (1512 icons) into `assets/svg/icon-<name>.svg` so any icon in the library is available to use without pulling it from `_dev/node_modules` first. Pre-existing icons already in `assets/svg/` were left untouched, including the handful (`icon-check.svg`, `icon-x.svg`, `icon-copy.svg`, `icon-lock.svg`, `icon-user.svg`, `icon-shopping-bag.svg`, `icon-arrow-right.svg`) that turned out to be sourced from the `regular` weight instead of `fill`
* Make every ACF icon-picker field read its `choices` from `assets/svg/` instead of a hardcoded PHP array - new `icon_choices()` (`inc/acf-fields.php`) globs `assets/svg/icon-*.svg` and auto-generates a label from each slug. Replaces the six separate hand-maintained choice arrays across `inc/acf-fields/homepage-canut.php`, `nosotros-canut.php`, `garantia-canut.php`, `contacto-canut.php`, `product-canut.php` and `categoria-ayuda.php`, so adding an SVG to `assets/svg/` is now enough to make it pickable everywhere. Added `'ui' => 1` (Select2 search) to every one of those select fields, needed now that each lists 1500+ icons instead of a handful

* Unify "Nuestros Clientes" (product-page reviews) and "Nuestras Historias" into a single CPT: `Historia` (`inc/post-types/historia.php`) now gets a new `product` field (`inc/acf-fields/historia.php`) that optionally links a story to one WooCommerce product. The product page's "Nuestros Clientes" section (`woocommerce/single-product.php`) is no longer backed by its own hardcoded `reviews` ACF repeater - it now reads real Historia posts linked to that product via `historia_query_for_product()`/`historia_rating_summary_for_product()` (new `inc/hooks/historia.php`), same visual design, just data-driven (aggregate score/star count/review count, and each card's own star count, all computed from real linked stories instead of a hardcoded "4.9 (124 reseñas)"/5 stars). A product with no linked stories yet shows an empty-state invite instead of placeholder reviews. Removed the now-superseded `reviews`/`reviews_rating_score`/`reviews_rating_count` fields from `inc/acf-fields/product-canut.php` - `reviews_title` stays as the only per-product override. A one-off `wp canut migrate-historia-reviews` WP-CLI command (`historia_migrate_product_reviews()`) migrates every product's existing `reviews` repeater rows into real, published Historia posts (rating defaults to 5, since the old repeater never captured one) and cleans up the old postmeta
* Add a public "Cuenta tu historia" submission dialog (`template-parts/historia/submit-form.php`, a `<dialog>` included once site-wide from `footer.php`, same `showModal()` convention as the cart drawer/iframe modal, driven by new `modules/historia-submit-canut.js`) gated by Cloudflare Turnstile (new "Ajustes de Historias" options page, `inc/acf-fields/ajustes-historias.php`, rendered explicitly and lazily since the dialog is hidden at page load) and by a required order number - `historia_form_submit()` (`inc/hooks/historia.php`) now rejects the submission outright unless `wc_get_order()` resolves it to a real order, so only a verified purchase can create a story; the order id is saved on the post (new `order_id` field, `inc/acf-fields/historia.php`) for reference. Submissions land as a `draft` Historia post for an editor to review and publish, with an admin email notification. The product page's previously-dead "Escribir reseña" button now opens this dialog directly (pre-selecting and naming that product) instead of linking anywhere, and the historias archive gets its own brand-green "¿Ya tienes un CANUT?" CTA band that opens it too
* Replace the homepage's `is_featured`-checkbox-curated historias carousel with 5 random, well-rated (`rating >= 4`) published stories (`historia_query_random_for_homepage()`, `template-parts/blocks/homepage-canut.php`) - removed the now-unused `is_featured` field from `inc/acf-fields/historia.php`. Also extracted `historia_get_card_args()` to de-duplicate the identical card-args-building code previously repeated in `archive-historia.php`, `single-historia.php` and the homepage block

* Fix `InitiateCheckout` (`inc/eventos/initiate-checkout.php`) sometimes reaching Meta twice for a single visit to the checkout page, a second apart with two different `event_id`s and identical cart contents - `woocommerce_before_checkout_form` was assumed to fire exactly once per real page load, but a double-click on the "go to checkout" link/button or a browser prefetch of it can trigger it twice in quick succession. Now passes a 15-second `dedup_key`/`dedup_ttl` (same short-window guard `lead.php` already uses for an accidental double form submit) keyed on the WooCommerce session/customer id, so a genuine revisit to checkout minutes later still counts as its own InitiateCheckout

* Restyle the "pay for order" / retry-payment page (new `woocommerce/checkout/form-pay.php` override, reached from `template-parts/order/failed.php`'s "Reintentar pago"/"Cambiar método de pago" links and from the Wompi gateway's own `process_payment()` redirect) - it was still rendering WooCommerce's bare default `<table>` + unstyled terms checkbox + generic button, completely inconsistent with the rest of the CANUT checkout. Now reuses the existing design system throughout: `.thank-you-canut-card`/`-order-table` for the order summary (same components `template-parts/order/success.php` already uses), the payment step's own `.payment-option-canut` cards, and `.button-canut-base` for the submit button. New `views/_order-pay-canut.scss` covers only the page's own header/spacing, everything else is reused as-is

* Fix the checkout wizard letting "Finalizar compra" go through without the customer actually clicking every step's own "Continuar" button, when the browser's autofill had already populated a still-`is-locked` (hidden, not disabled) step's fields on its own. `blockSubmitUntilStepsConfirmed()` (`modules/checkout-steps-canut.js`) used to recover from a blocked submit by synthesizing a click on the incomplete step's own "Continuar" button - which confirms it exactly as if the customer had reviewed and clicked through, satisfying both the client-side `is-confirmed` gate and the server-side `canut_confirmed_steps` session flag from a submit attempt alone. It now only scrolls to the incomplete step and stops there; `is-confirmed`/`canut_confirmed_steps` can now only ever be set from a real click on `[data-step-continue]`, however many times "Finalizar compra" is retried
* Fix four Facebook Conversions API checkout events firing wrong or not at all. `InitiateCheckout` (`inc/eventos/initiate-checkout.php`) never reached Meta because the theme's `woocommerce/checkout/form-checkout.php` rewrite had dropped core's own `do_action( 'woocommerce_before_checkout_form', $checkout )` entirely - restored at the same position core fires it (before the registration check, before `<form>` opens). `AddContactInfo`, `AddShippingInfo` and `AddPaymentInfo` (`inc/eventos/add-contact-info.php`/`add-shipping-info.php`/`add-payment-info.php`) all used to hook `woocommerce_checkout_update_order_review`, core's AJAX hook that fires on any field edit/blur anywhere in the form - so `AddContactInfo` and `AddPaymentInfo` could fire the moment a valid-looking value existed, often before the customer had confirmed anything (a payment gateway is already force-checked to a default by core itself on the very first AJAX call right after page load, so `AddPaymentInfo` was firing at checkout start every time). All three now hook `Air_Light\checkout_step_continued` (`inc/hooks/checkout-step-actions.php`), the wizard's own step-confirmation signal `inc/hooks/data-consent.php` already relies on, so each fires exactly once the customer clicks "Continuar" on its matching step (1/2/3) and the wizard advances to the next one
* Add a temporary, `WP_DEBUG`-only console.log for the four events above: `send_facebook_event()` (`inc/eventos/facebook-conversions-api.php`) records whether each attempt actually reached Meta via `facebook_capi_debug_log()`, surfaced inline for `InitiateCheckout` (`inc/eventos/initiate-checkout.php`) and via the step-confirm AJAX response for `AddContactInfo`/`AddShippingInfo`/`AddPaymentInfo`, logged client-side by `postStepContinued()` (`modules/checkout-steps-canut.js`). Safe to remove once all four are confirmed firing correctly

* Fix "Reintentar pago"/"Cambiar método de pago" on the failed-order thank-you page (`template-parts/order/failed.php`) doing nothing for a declined/voided order: both buttons link to `$order->get_checkout_payment_url()`, but WooCommerce's own order-pay endpoint only allows paying for orders whose status passes `WC_Order::needs_payment()`, which defaults to `['pending', 'failed']` only - a Wompi DECLINED transaction sets the order to `'cancelled'` and a VOIDED one to `'voided'` (`wompi-portal-de-pagos/includes/class-wompi-portal-pagos-webhook-handler.php`), so both hit `order_pay()`'s own "El estado de este pedido es «Cancelado». No se ha podido pagar." exception instead of the payment form. New `woocommerce_valid_order_statuses_for_payment` filter (`inc/hooks/woocommerce.php`) adds `cancelled` and `voided` to the allowed list - `refunded` deliberately left out, since that status means a charge already succeeded once
* Add a `PaymentFailed` Facebook Conversions API custom event (`inc/eventos/purchase-failed.php`), the counterpart to `purchase.php`'s `Purchase` event: fires on `woocommerce_order_status_failed`/`_cancelled`/`_voided` (server-side, whenever the Wompi webhook marks an order as such) and on `woocommerce_thankyou` (customer actually reaching `template-parts/order/failed.php`), guarded by a permanent order-meta flag so only the first failure on a given order gets tracked. Event name configurable under Ajustes > Facebook Pixel > Eventos personalizados, falling back to `PaymentFailed` when left blank

* Link the homepage's featured product image and title (`template-parts/front-page/featured-product.php`) to the product's own page - previously only the "Especificaciones" button did, so clicking the image or title itself did nothing

* Fix the mobile fixed bar's own "Finalizar compra" button (`data-checkout-summary-canut-button`, `footer.php`) never actually disabling either - `wireMobileSummaryBar()` (`modules/checkout-canut.js`) force-enabled it unconditionally on init regardless of wizard state, fighting the very state `wirePlaceOrderButtonState()` (`modules/checkout-steps-canut.js`) was trying to set. That button only ever forwards its click to the real `#place_order`, so leaving it always enabled meant it could finalize checkout that way even with `#place_order` itself correctly disabled. `wirePlaceOrderButtonState()` now owns both buttons' `disabled` state together as one source of truth; `wireMobileSummaryBar()` no longer touches it at all

* Fix the real "Finalizar compra" button (`#place_order`) never actually ending up disabled despite `wirePlaceOrderButtonState()` (`modules/checkout-steps-canut.js`) running correctly and disabling it - found by driving the real checkout with Playwright rather than guessing further from screenshots: WooCommerce's own checkout.js replaces `#order_review`'s entire contents (a fresh, non-disabled button included) on every `updated_checkout` AJAX fragment swap, including the automatic one it fires once right after page load to quote initial shipping/totals - the disabled state was being set on that first, now-detached button element the whole time, while the new, real, visible one sailed past untouched. `refresh()` now re-queries `#place_order` fresh every time instead of closing over one reference grabbed at init, and also re-runs on `updated_checkout` (same event `modules/checkout-canut.js`'s own `syncMobileSummaryBar()` already re-queries fresh on, for the same reason)
* Harden `checkout_validate_steps_complete()` (`inc/hooks/checkout.php`) against cached/autofilled field values alone satisfying it without the customer ever actually confirming a step - a locked step's fields sitting in the DOM with a browser-remembered value was never proof of anything. New `checkout_track_confirmed_step()` records each step's confirmation in the WooCommerce session (piggybacking on the existing `Air_Light\checkout_step_continued` hook `modules/checkout-steps-canut.js`'s `postStepContinued()` already fires per step), and the validator now requires that session record alongside each step's own `$_POST` fields, not either alone

* Hide the mobile fixed checkout summary bar's round jump-link arrow (`.checkout-summary-canut-toggle`) below 500px - too narrow there to fit alongside the product count/total and the real "Finalizar compra" button without cramping them (`_checkout-summary-canut.scss`)
* Fix the checkout page's horizontal padding being doubled: `page.php`'s `<main class="... has-global-padding ...">` already carries WordPress's own global-styles rule padding it left/right with `--wp--style--root--padding-left/right` (`class-wp-theme-json.php`), and `.wrap-canut` around the form (`form-checkout.php`) applies the exact same tokens again as its own padding - a checkout-only issue, since every other WooCommerce template (archive-product.php, single-product.php) renders through the template hierarchy directly rather than page.php and never carries `has-global-padding` at all. Zeroed on `body.woocommerce-checkout .site-main.has-global-padding` (`_checkout-canut.scss`) so `.wrap-canut` is the only one supplying it

* Fix the checkout's mobile layout (single column, order-summary sidebar reflowed to the end, fixed mobile summary bar) only kicking in below `$breakpoint-mobile` (600px) - between there and `$breakpoint-tablet-landscape` (1024px) the numbered-step column was squeezed uncomfortably narrow next to a full 27.5rem sidebar it never had room for. New `$breakpoint-checkout-mobile` (1034px, `_breakpoints.scss`) replaces `$breakpoint-mobile` in the four checkout-specific rules that actually gate this switch: `.checkout-form-canut-columns`/`.checkout-form-canut-sidebar` (`_checkout-canut.scss`), the fixed mobile summary bar's own `display` (`_checkout-summary-canut.scss`), and its reserved bottom padding on `.checkout-form-canut`. Left the smaller internal grids (step field pairs, payment method cards) on the sitewide `$breakpoint-mobile`, since they still have plenty of room once the sidebar itself stops squeezing them

* Fix the checkout's data-processing consent checkbox ("Acepto la política de tratamiento de datos personales") never linking to a policy page: `checkout_render_data_consent_field()` (`inc/hooks/checkout.php`) looked for a hardcoded `politica-de-tratamiento-de-datos` page slug that doesn't exist on this site. Now uses WordPress' own Privacy Policy page setting instead (`get_privacy_policy_url()`, Settings > Privacy in wp-admin) so the link stays correct whenever that setting changes, without a code change - still falls back to plain text until that setting points to a published page
* Restrict the checkout's phone number inputs (`billing_phone` and the "Agregar otro número" repeater's additional rows) to digits only - `wirePhoneNumberInputs()` (`modules/checkout-steps-canut.js`) strips any non-digit character on input (typing, paste, autofill), delegated on `document` so it also covers repeater rows added after page load; `inputmode="numeric"`/`pattern="[0-9]*"` added to both inputs (`inc/hooks/checkout.php`) for the matching mobile keyboard
* Add a site-wide cookie notice, shown on every page including checkout (unlike the WhatsApp float, which stays hidden there for a distraction-free flow): a fixed bottom bar (`footer.php`'s `render_cookie_notice()`, `inc/hooks/cookie-notice.php`) with configurable text and button label from a new "Ajustes de Cookies" options page (`inc/acf-fields/ajustes-cookies.php`), auto-linking to a `politica-de-cookies` page if one exists (same fallback pattern as the checkout's data-processing consent field). Starts `hidden` in the server-rendered markup; `modules/cookie-notice-canut.js` only shows it if the visitor hasn't already accepted (checked via `document.cookie`, not server-side, so this stays correct under page caching), and sets a 1-year `canut_cookie_consent` cookie on accept (`features/_cookie-notice-canut.scss`)

* Fix the consent log's "Pedido" column staying blank even after the customer completes the order: `link_data_consent_to_order()` (`inc/hooks/data-consent.php`) only matched on the WooCommerce session id, which can occasionally not match a genuinely-the-same checkout; it now falls back to the order's own billing email against the most recent still-unlinked row from the last 24 hours when the session match misses
* Add an order-details popup to the consent log admin screen: clicking a linked order number (`inc/admin/class-data-consent-list-table.php`) now opens a `<dialog>` (`inc/admin/consent-log.php`) showing that order's line items (with SKU), status, total, payment method and shipping address via a new `canut_consent_order_details` AJAX action, instead of only linking straight to the full edit screen (still one click away, at the bottom of the popup)

* Keep the checkout's real "Finalizar compra" button (`#place_order`, always visible in the order-summary sidebar - `review-order.php` - entirely outside the step wizard) visibly disabled (same `:disabled` treatment as the mobile fixed bar's own placeholder button - `.checkout-summary-canut-submit`, `_checkout-canut.scss`) until every wizard step (Información de contacto/Envío/Método de pago/Método de envío) is actually complete, and hard-block the submit itself as a second line of defence in case that attribute ever gets removed (dev tools) or the button is missing entirely. A locked step's fields stay in the DOM just hidden, not disabled (`is-locked` rule), with a pre-selected default payment/shipping method already in them - so nothing previously stopped the order from submitting before any step was actually completed. `isStepIncomplete()` (`modules/checkout-steps-canut.js`) is step-4-aware: "Método de envío" never gets its own "Continuar"/`is-confirmed` (it's the last step before the sidebar button), so it counts as complete once merely unlocked instead. `wirePlaceOrderButtonState()` keeps the button's `disabled` attribute in sync via a `MutationObserver` on each step's class attribute (so it can't drift out of sync with whichever code path toggled it); `blockSubmitUntilStepsConfirmed()` intercepts the form's real `submit` event on `document` with `capture: true` (fires before WooCommerce's own bundled `checkout.js` bubble-phase handler ever sees it) and either clicks the first incomplete step's own "Continuar" button (reusing its existing validation/focus behaviour) or scrolls to it if still locked. `checkout_validate_steps_complete()` (`inc/hooks/checkout.php`, `woocommerce_checkout_process`) is the backend counterpart for anything that reaches the server without any of that script having run at all (JS disabled, dev tools, a direct POST) - re-checks each step's own `$_POST` keys directly (contact fields + consent, address fields, `payment_method`, `shipping_method`) rather than relying on WooCommerce core's own validation, which a pre-populated default would otherwise satisfy without the customer ever having visited that step
* Add a generic checkout-wizard step-confirmation hook: `modules/checkout-steps-canut.js`'s `postStepContinued()` now posts (fire-and-forget, never blocking the wizard from advancing) to a new `canut_checkout_step_continue` AJAX action every time a customer confirms "Información de contacto", "Envío" or "Método de pago" via their own "Continuar" button - steps that previously never made a server round trip at all. That action fires a plain `Air_Light\checkout_step_continued` hook (`inc/hooks/checkout-step-actions.php`) any other code can listen to without touching the JS again
* Add a data-processing consent audit log (Colombia's Ley 1581 de 2012, Habeas Data), the first listener on the hook above: `inc/hooks/data-consent.php` logs the exact moment a customer confirms step 1 with the consent checkbox ticked (email, phone, IP, user agent, WooCommerce session id, logged-in user id if any, plus both the client-reported "accepted at" moment and the server's own "stored at" moment) into a new `{prefix}canut_data_consent` database table - independent of whether the order is ever completed, which `checkout_save_data_consent()`'s existing order-meta timestamp (`inc/hooks/checkout.php`) couldn't cover since it only ever saves once the whole checkout form is actually submitted. Once/if an order is created, the matching still-unlinked row (by WooCommerce session) gets its `order_id` filled in. Viewable read-only from its own top-level "Consentimientos" admin menu item (`inc/admin/consent-log.php`, `inc/admin/class-data-consent-list-table.php`), a `WP_List_Table` with search-by-email, sorting and pagination. Extracted the shared `get_client_ip_address()` helper (previously Facebook-Pixel-specific) into `inc/hooks/general.php` since this log needed the same real-visitor-IP logic
* Add each line item's real SKU (`sku`) alongside its Meta-matched `id` (which falls back to the product id when there's no SKU) to the `Purchase` event's `contents` (`inc/eventos/purchase.php`), so the raw SKU is always visible in the payload for your own reporting even when a product has none and `id` had to fall back

* Add a required "Acepto la política de tratamiento de datos personales" checkbox to the checkout's contact step, needed before continuing to the address step (Ley 1581 de 2012/Habeas Data requires explicit consent before collecting personal data). Not a core WooCommerce field, so like Barrio/the phone country code it's validated and saved by hand: `checkout_render_data_consent_field()` renders it reusing the same `.form-row.validate-required` convention every other custom field here relies on (so `modules/checkout-steps-canut.js`'s existing step validation picks it up with no JS changes), `checkout_validate_data_consent()` rejects the order server-side if unchecked (`woocommerce_checkout_process`), and `checkout_save_data_consent()` records a `_data_processing_consent_at` timestamp on the order as a consent trail. Links to a `politica-de-tratamiento-de-datos` page when one exists, falls back to plain text otherwise. New `.form-canut-checkbox` component (`_form-canut.scss`) - the theme's first custom-styled checkbox - built from scratch via `appearance: none` plus a mask-drawn checkmark (same technique as the existing select chevron)
* Fix the mobile fixed checkout summary bar ("Finalizar compra") also showing on the thank-you/order-received page, since `is_checkout()` (`footer.php`) is true there too - the bar's arrow jumps to `#order_review` and its button/total sync against an active checkout, neither of which exist on that page. Now also excluded via `is_order_received_page()`
* Fix WooCommerce's own "Ver carrito" link showing up next to the single product page's "Comprar ahora"/"Finalizar compra" button after a successful add-to-cart, redundant alongside the big button which already flips to "Finalizar compra" itself. Same fix already existed for the shop/archive product cards (`.card-product-canut-body a.added_to_cart.wc-forward`) and the front page featured product (`.home-featured-product-actions a.added_to_cart.wc-forward`) but was never added here; add the matching `.product-purchase a.added_to_cart.wc-forward { display: none; }` rule (`_product.scss`)
* Add server-side Facebook Conversions API events (`inc/eventos/`, one PHP file per event: `view-content.php`, `add-to-cart.php`, `initiate-checkout.php`, `add-contact-info.php`, `add-shipping-info.php`, `add-payment-info.php`, `initiate-payment.php`, `purchase.php`, `lead.php`, `complete-registration.php`), each sending through a shared `send_facebook_event()` in `inc/eventos/facebook-conversions-api.php`. Deliberately backend-only - no browser-side `fbq()` pixel calls anywhere in the theme - so there's only ever one source sending each event to Meta. `view-content.php`/`add-to-cart.php`/`initiate-checkout.php` intentionally carry no dedup guard: their hooks (`template_redirect`, `woocommerce_add_to_cart`, `woocommerce_before_checkout_form`) already fire exactly once per real action, so a visitor leaving and revisiting a product, or adding - removing - re-adding the same item, correctly counts as separate events rather than being suppressed. `add-contact-info.php`/`add-shipping-info.php`/`add-payment-info.php` dedup against the WooCommerce session instead (`facebook_capi_session_value_unchanged()`/`facebook_capi_mark_session_value_sent()`, storing the last-sent value's hash in `WC()->session` rather than a transient with a fixed TTL), since their underlying hook genuinely fires more than once for what's still a single real action (every checkout field edit) - tying the guard to the session's own real lifetime instead of an arbitrary time window means it can't send a spurious duplicate just because the customer took a while filling in the rest of the form, and still refires the moment the value actually changes; `lead.php` keeps the short `dedup_key`/`facebook_event_already_sent()` transient guard (a double form submit, not a checkout session); `initiate-payment.php`/`purchase.php`/`complete-registration.php` use a permanent order/user meta flag, since those conversions must never resend for the same order or account, ever. `purchase.php` hooks both `woocommerce_thankyou` (reusing the exact order-status list `woocommerce/checkout/thankyou.php` already uses to pick its success/failure view, so it fires exactly when the customer sees the success page) and `woocommerce_payment_complete` - the latter needed because Wompi's webhook handler (`wompi-portal-de-pagos/includes/class-wompi-portal-pagos-webhook-handler.php`) calls `$order->payment_complete()` server-to-server the moment it confirms an APPROVED transaction, independent of whether the customer's browser ever makes it back to the thank-you page (closed tab, dropped connection, failed redirect) - without it, a real Wompi sale could go untracked entirely. Both call the same guarded function, so whichever fires first sends the event and the order never fires twice; not on failed/cancelled/refunded/voided orders, and each new order fires its own Purchase. `add-contact-info.php`, `add-shipping-info.php` and `initiate-payment.php` are custom events (Meta has no standard event for "checkout contact/shipping step filled" or "order created, payment about to start", distinct from `AddPaymentInfo`/`Purchase`); their event names are configurable in a new "Eventos personalizados" tab on Ajustes > Facebook Pixel, falling back to `AddContactInfo`/`AddShippingInfo`/`InitiatePayment` when left blank. Credentials (Pixel ID, Conversions API access token, optional test event code) are set in a new "Ajustes de Facebook Pixel" options page (`inc/acf-fields/ajustes-facebook-pixel.php`)
* Fix the "Método de pago" checkout step always showing "Contra reembolso" pre-selected on page load instead of no card selected: WooCommerce itself (`WC_Payment_Gateways::set_current_gateway()`) auto-picks the first available gateway into the session the moment gateways are listed at all, even for a visitor who hasn't touched the form yet, so `$gateway->chosen` can't tell that apart from a real choice. `woocommerce/checkout/payment-method.php` now only marks a card checked when `$_POST['payment_method']` actually matches it (a real re-render after a validation error elsewhere in the form), never as a default - the step's own "Continuar" button already refused to proceed without a `:checked` card (`modules/checkout-steps-canut.js`), so this was the only piece missing
* Add a product description, quantity stepper and quantity-discount upsell to the checkout order-summary card (`woocommerce/checkout/review-order.php`), matching what the cart drawer already offers instead of a static, uneditable line item. The short description is hard-capped to 80 characters with an ellipsis (`checkout_truncate_description()`, trimmed back to the last full word, `inc/hooks/checkout.php`) at the smallest size in the type scale (`caption`, 12px) with a tighter `line-height: 1.35` than that size's own paragraph-oriented default (1.7 reads far too loose over 2-3 short lines). The `-`/`+` stepper reuses the cart drawer's own `.cart-drawer-canut-stepper`/`.cart-drawer-canut-item-row` component styling verbatim rather than rebuilding it, wired to a new `checkout_update_item_qty()` AJAX action that mutates `WC()->cart` then re-triggers WooCommerce's own `update_checkout` event (`modules/checkout-canut.js`) - the same AJAX cycle an address/payment-method change already goes through, so shipping/fees/totals all recalculate through that one already-correct path. That refresh only actually reached `#order_review` once `review-order.php`'s card also carries `.woocommerce-checkout-review-order-table` - the class core's `checkout.js` looks for to swap in fragments, missing until now since the card was restructured away from core's default `<table>`. The "¡Lleva 2 y ahorra 10%!" upsell card (shown until the line reaches the product's configured quantity-discount threshold) is now a shared `render_qty_discount_upsell()` helper (`inc/hooks/quantity-discount.php`) instead of duplicated inline markup, called from both the drawer and checkout; its button carries both scripts' data attributes since each only ever looks for its own, on buttons scoped to its own container. Fixed the upsell card's icon+title+button row cramming its text into a 2-3-word-wide column on narrow phones (present in the drawer too, just less visible there) by forcing the button onto its own full-width line below (`flex: 1 1 100%` inside a now-`flex-wrap: wrap` parent, `_cart-popup-canut.scss`). Also removes the item thumbnail's small quantity badge, now redundant with the stepper's own live count
* Add top spacing to the checkout page between `.site-header-checkout` and the form below it, matching the breathing room `.checkout-form-canut` already had at the bottom before the footer (`padding-top`, `_checkout-canut.scss`) - it previously only had `padding-bottom`, so the first step ran flush against the header
* Turn all 4 checkout steps ("Información de contacto" / "Envío" / "Método de pago" / new "Método de envío") into a sequential wizard, Amazon-style: "Información de contacto" starts open, the other three start locked (`is-locked`, `form-checkout.php` - fields hidden, a placeholder message shown instead) since each depends on the one before it - a shipping method in particular can't be quoted correctly until both an address AND a payment method are known (`JPIODFW_Shipping::order_is_cod()` reads the chosen payment method to decide COD vs. prepaid pricing). Every step but the last has its own "Continuar" button (`modules/checkout-steps-canut.js`) that validates its required fields the same way WooCommerce's own inline validation already tags them (`.validate-required`/`.woocommerce-invalid`, now actually styled - see `_form-canut.scss` - since the theme dequeues WC's default stylesheet), collapses into a read-only summary ("Enviando a [nombre] / [dirección]", "Modificar" button - same pattern as Amazon's address-confirmation card) and unlocks the next step; "Modificar" reopens a confirmed step and, for "Envío"/"Método de pago" (whose values actually feed a later calculation), re-locks everything after it too, cascading forward - "Información de contacto" is exempt from that cascade, since editing a name/phone doesn't affect address, payment, or shipping-method calculations at all. Confirming "Método de pago" additionally triggers a real `update_checkout` (WooCommerce's own AJAX cycle) before unlocking "Método de envío", so its shipping cards re-quote against the just-chosen payment method instead of momentarily showing stale pricing (falls back to unlocking after 8s regardless, so a dropped request can't strand the customer). Fixed a related latent bug found while adding contact-step validation: `checkout_render_phone_field()` never added the `validate-required` class core's own field renderer normally injects, so an empty `billing_phone` silently passed both core's inline validation and this new step-level check
* Add an "Agregar otro número" repeater to the contact step so customers can leave one or more alternate phone numbers (family member, reception desk) in case the courier can't reach the primary one - each row is the same country-code-select + number-input pair as the primary phone field, cloned client-side from a server-rendered `<template>` (`checkout_render_additional_phones()`, `inc/hooks/checkout.php`, capped at 3 rows in `modules/checkout-steps-canut.js`) so the country list isn't duplicated in JS. Saved as a single `_billing_phone_additional` order meta array (`checkout_save_additional_phones()`) and surfaced under the billing address on the admin order screen (`checkout_admin_display_additional_phones()`), same pattern as the existing Barrio field
* Add the new "Método de envío" step itself (final step, after "Método de pago"): full-width selectable cards (one per available shipping rate for the entered address), reusing the payment step's existing `.payment-option-canut` card component instead of a new one. No "is Dropi active" branching needed - `checkout_render_shipping_methods()` (`inc/hooks/checkout.php`) just renders whatever rates WooCommerce's own shipping-zone matching resolves for the package, same as core's own `wc_cart_totals_shipping_html()` already did: if wc-dropi-integration is active and the cart needs a freight quote, its "Dropi - Cotizador de flete" rates appear (it already hides "Envío gratis" itself in that case); otherwise whatever else is assigned to the zone (typically free shipping) shows instead. Since the cards live outside `#order_review` (the only container classic checkout's own AJAX response replaces automatically), they're kept in sync on address/city/payment-method change via a new `.checkout-shipping-methods-canut` fragment hooked to `woocommerce_update_order_review_fragments` - the same mechanism cart-count badges use. The sidebar's totals table no longer renders `wc_cart_totals_shipping_html()` (same radio inputs, would've just duplicated the new step's picker) - it now shows the chosen method as a plain read-only row via a new `checkout_render_chosen_shipping_method_label()` instead, same un-pairing "Método de pago" already got from the order-review sidebar. New `.shipping-methods-canut-list` (`_checkout-canut.scss`) keeps the cards single-column/full-width at every breakpoint, unlike the payment step's 2-column `ul.payment_methods`
* Move the "¿Tienes un cupón?" toggle/form fully inside the "Método de pago" step, right under the payment cards, instead of a separate section after the whole form. Core's own coupon widget is itself a `<form>`, which still can't nest inside `<form class="checkout">` without the browser silently dropping its `<form>` tag (see `review-order.php`'s docblock) - exactly where this step lives - so its default hook stays removed (`checkout_unhook_coupon_form()`, `inc/hooks/checkout.php`) and a new `checkout_render_coupon_field()` renders plain (non-`<form>`) toggle/input/button markup instead, which can sit inside the step without that problem. `modules/checkout-steps-canut.js` wires its toggle and posts the code straight to WooCommerce's own `apply_coupon` AJAX endpoint (same request core's form-submit-bound handler would make) rather than needing the real `<form class="checkout_coupon">` that approach depends on. Already-applied coupons still show/remove themselves the normal way in the order-summary sidebar (`wc_cart_totals_coupon_html()`, untouched) - this only covers entering a new one
* Make "Resumen del pedido" (products, subtotal, shipping, discounts, total) reachable on mobile checkout instead of being fully hidden - previously `.checkout-form-canut-sidebar` (the desktop order-review column) was just `display: none` below `$breakpoint-mobile`, which also made the required terms checkbox unreachable there. It now renders in normal page flow at the end of the form instead (single-column grid already puts it after the numbered steps), with the fixed summary bar's arrow (`checkout-summary-canut-toggle`, `footer.php`, now a plain `<a href="#order_review">` jump link) smooth-scrolling down to it (`modules/checkout-canut.js`, same `prefers-reduced-motion` handling as the existing back-to-top button). `.checkout-form-canut` reserves bottom padding equal to the fixed bar's real rendered height (`--checkout-summary-canut-bar-height` custom property, same pattern as `--site-header-height`) so the card's own place-order button/trust badges end up clear of the bar instead of covered by it (`_checkout-canut.scss`)
* Add a country-code selector to the checkout phone field, Colombia (`+57`) listed and selected first by default, alongside México, Estados Unidos, Ecuador, Perú, Panamá, Venezuela, Chile, Argentina and España. `woocommerce_form_field()` can't render a compound select+input control on its own, so `billing_phone` is rendered by a new `checkout_render_phone_field()` (`inc/hooks/checkout.php`) instead - still driven by WooCommerce's own field config (label/required/placeholder) via `$checkout->get_checkout_fields( 'billing' )`, just with different markup, visually merged into one control via `.form-canut-phone-group`/`.form-canut-phone-code` (`_form-canut.scss`). `billing_phone` itself keeps storing exactly what the customer types, unprefixed, so it stays exactly what wc-dropi-integration's order sync and `template-parts/order/success.php` already expect; the selected dial code is saved separately to `_billing_phone_country_code` order meta, same pattern as the existing Barrio field (`woocommerce/checkout/form-checkout.php`)
* Fix the checkout footer running edge to edge instead of aligning with the rest of the site, and the checkout form sitting flush against the footer with no breathing room. `.site-footer-checkout` (`footer.php`) never got the `.wrap-canut` inner wrap the regular footer's own `.site-footer-canut-inner` uses, so its horizontal padding came only from its own smaller `padding` value instead of the sitewide gutter; split it into an outer bar (background/border/vertical padding) plus a `.wrap-canut site-footer-checkout-inner` wrapper for the horizontal alignment, matching `.site-footer-canut`'s existing outer/inner split (`layout/_site-footer.scss`). Separately, nothing between the checkout form and the footer carried any bottom spacing - `.wrap-canut` and `<main class="site-main has-global-padding ...">` (`page.php`) are both horizontal-only here, since `theme.json`'s root padding is left/right only - so add `padding-bottom` to `.checkout-form-canut` (`_checkout-canut.scss`)
* Fix checkout regressions found while reviewing the classic `[woocommerce_checkout]` migration (previous entry) against the Figma design again. The whole checkout was rendering at WordPress's own 800px `content-size` instead of the theme's 1440px `wide-size` - the shortcode's own output wraps itself in a plain, un-aligned `<div class="woocommerce">`, a direct child of the page's `is-layout-constrained` `<main>`, unlike every other WooCommerce template (which renders through the template hierarchy directly and never hits this); fixed with `body.woocommerce-checkout .woocommerce { max-width: none }` plus a `.wrap-canut` (the same sitewide wide-content-container utility `archive-product.php`/`single-product.php` already use) around the form itself (`woocommerce/checkout/form-checkout.php`), which is what was actually squeezing card padding/text enough to wrap "Pago contraentrega" onto two lines. The coupon form silently broke (always shown open, "Aplicar cupón" doing nothing) because core's own coupon widget is itself a `<form>`, and it was being rendered from `woocommerce/checkout/review-order.php` *inside* the main `<form class="checkout">` - nested forms are invalid HTML, so the browser silently drops the inner `<form>` tag (and with it, the `id`/`style="display:none"` WooCommerce's own `wc_checkout_coupons` JS - `assets/js/frontend/checkout.js` - depends on for the toggle and the AJAX apply); moved back to its default hook (`woocommerce_before_checkout_form`, before the main `<form>` opens) instead, in its own grid row sharing `.checkout-form-canut-columns`' column template so it still lines up with the sidebar. Field spacing was roughly double what it should've been - `.form-canut-group` (`_form-canut.scss`) never zeroed out `margin` on the `<p class="form-row">` WooCommerce renders it on, so the browser's default paragraph margin was stacking with the checkout grid's own `row-gap`. The `Localidad / Ciudad` field (wc-dropi-integration's own `woocommerce_form_field_city` override, `StatesPlaces.php`) rebuilds its field HTML from scratch and skips the `<span class="woocommerce-input-wrapper">` every other field gets wrapped in, so it fell back to an unstyled native `<select>` next to every other custom-styled one - added a fallback `.form-canut-group > select` rule matching the chevron/border/height every select2-enhanced field (country/state) already had. Also made `billing_country` full-width (`FULL_WIDTH_CHECKOUT_FIELDS` in `inc/hooks/checkout.php`) instead of sitting alone in a 2-column row with empty space beside it
* Migrate checkout off the WooCommerce Checkout block (Store API, React) onto the classic `[woocommerce_checkout]` shortcode, so the CANUT design lives in theme-owned PHP templates instead of a reskin layered on top of markup/CSS the theme doesn't control. The block approach kept hitting hard walls fighting `wp-content/plugins/woocommerce/assets/client/blocks/checkout.css`'s own cascade (numbered-step badges reset under internal `.is-mobile`/`.is-small` classes, a floating-label input pattern needing a full rebuild to become a plain static label, native-radio styling limits) and outright blockers - the Wompi gateway's blocks integration hardcodes its own label ("Pagas por Wompi") via a separate compiled JS bundle, and WooCommerce never exposes a gateway's `description` to the block's payment option at all, so neither gateway could show real copy. New template overrides under `woocommerce/checkout/` (`form-checkout.php`, `review-order.php`, `payment.php`, `payment-method.php` - same override convention already used by the existing `thankyou.php`) restructure core's default single-column, hook-driven layout into the numbered 3-step left column (contact/shipping/payment) plus a sticky single-card order summary on the right, with the terms checkbox and real `#place_order` button relocated from `payment.php` into the summary card (core normally pairs `woocommerce_checkout_payment()` with `woocommerce_order_review()` on the same `woocommerce_checkout_order_review` action - unpaired in `inc/hooks/checkout.php` so payment renders once, in its own left-column step, matching the design). Payment method cards reuse the design system's already-built `.payment-option-canut` component (`_dev/assets/src/sass/components/_payment-option-canut.scss`, previously only wired up on `/sistema-de-diseno`); every checkout field gets the matching `.form-canut-group` treatment via a new `woocommerce_checkout_fields` filter. The Barrio (neighborhood) field moves off the Blocks-only `woocommerce_register_additional_checkout_field()` API onto classic's `woocommerce_billing_fields`/`woocommerce_shipping_fields` filters + a `woocommerce_checkout_update_order_meta` save handler (also shown on the admin order screen via `woocommerce_admin_billing_fields`/`shipping_fields`). Since WooCommerce's own default frontend stylesheet was never actually relevant to checkout before (the block used a separate plugin stylesheet) but now applies to classic markup and fights the CANUT components, it's dequeued sitewide (`woocommerce_enqueue_styles` filter, `inc/hooks/woocommerce.php`) in favour of a proper `.woocommerce-message`/`.woocommerce-error`/`.woocommerce-info` notice style and select2/SelectWoo widget styling (country/state/city-once-Dropi-swaps-it dropdowns) to match. `modules/checkout-canut.js` shrinks from grafting CANUT markup onto a repeatedly-remounting React tree down to just keeping the mobile sticky summary bar's total in sync with classic checkout's own AJAX-updated order total (`updated_checkout`, WooCommerce core's own event - no more MutationObserver). Verified end to end with a real placed order (COD, Dropi-quoted address) reaching the existing, untouched `checkout/thankyou.php`

* Redesign the thank-you/order-received page to match the CANUT design system, with distinct success and failure states based on the order's status (`failed`/`cancelled`/`refunded`/`voided` render the failure view, matching the Wompi gateway plugin's own extended status list already used for its `woocommerce_thankyou_order_received_text` filter). Overrides `checkout/thankyou.php` (kept minimal - just the same success/failure branch WooCommerce core itself uses, plus the `woocommerce_before_thankyou`/`woocommerce_thankyou`/`woocommerce_thankyou_{gateway}` hooks core fires there for gateway/plugin compatibility), with the actual markup split into `template-parts/order/success.php` (order metadata grid, COD/online payment notice, order details table with product thumbnails and totals, billing address + delivery status card, "volver a la tienda"/"contactar soporte" actions) and `template-parts/order/failed.php` (possible-reasons card, "reintentar pago"/"cambiar método de pago" buttons linking to `get_checkout_payment_url()`, a WhatsApp-soporte CTA prefilled with the order number, and a concierge support callout). New `_thank-you-canut.scss` (reuses existing `button-canut`/`banner-canut` components and design tokens throughout - no new hardcoded colours besides the same WhatsApp brand-green exception already used by the floating WhatsApp button); the page already gets the minimal `site-header-checkout`/`site-footer-checkout` shell for free since `is_checkout()` covers the order-received endpoint. Adds four new Phosphor fill icons (`icon-check-circle.svg`, `icon-warning-circle.svg`, `icon-wifi-slash.svg`, `icon-clock.svg`)
* Fix WooCommerce's own "Ver carrito" link showing up next to the front page featured product's "Comprar ahora" button after a successful add-to-cart, redundant alongside the big button which already flips to "Ver carrito" itself. Same fix already existed for the shop/archive product cards (`.card-product-canut-body a.added_to_cart.wc-forward`) but was never added for this block; add the matching `.home-featured-product-actions a.added_to_cart.wc-forward { display: none; }` rule (`_front-page.scss`)
* Darken the Nosotros hero manifesto banner's left-to-right gradient overlay (60% to 88% opacity, fading out to 75% width instead of 60%) so the white eyebrow/title/text read clearly over `hero-manifesto.png`'s bright, light-toned photo instead of blending into it (`_nosotros.scss`)
* Make the front page featured product's "Comprar ahora"/"Ver carrito" and "Especificaciones" buttons full width and stacked on mobile instead of side by side at their natural text width (`.home-featured-product-actions` in `_front-page.scss`)
* Fix the front page header logo losing its orange accent while floating over the hero: it was forced fully white via `.home .site-title svg path { fill: white }`, which also overwrote `logo.svg`'s orange path (`#B16B3A`), not just the dark green one. Swap in a dedicated `assets/svg/logo-white.svg` (same paths, orange kept, dark green swapped to white) on the front page instead, loaded conditionally via `is_front_page()` (`template-parts/header/branding.php`, new `logo_white` entry in `THEME_SETTINGS['logo']` group in `functions.php`), and drop the now-redundant CSS override (`_site-header.scss`)
* Change the front page's featured product button from "Lo quiero ya" (linking straight to checkout with the product pre-added) to "Comprar ahora": now an `ajax_add_to_cart` button that adds the product without leaving the page and opens the cart drawer, same behaviour as the shop loop's card and the single-product page's buy button. If the product is already in the cart, it renders directly as "Ver carrito" (`.wc-interactive`, no `data-added-href`) so a click just reopens the drawer instead of adding a second unit (`inc/acf-fields/homepage-canut.php`, `template-parts/blocks/homepage-canut.php`, `template-parts/front-page/featured-product.php`). Also turn its spec list (Cámara HD, WiFi, etc.) into gray mini cards (`--wp--preset--color--light` background) instead of plain icon+text rows, and fix `.home-featured-product-specs` sitting indented from the title/price/buttons above and below it - it's a `<ul>` with no `padding: 0`/`list-style: none` of its own, so it was rendering with the browser's default list indent (`_front-page.scss`)
* Add the CANUT isotype as the site favicon: `assets/svg/favicon.svg` served directly as a modern SVG icon (crisp at any size, no separate ICO needed), with `favicon-16x16.png`/`favicon-32x32.png`/`apple-touch-icon.png` (`assets/images/favicon/`, rasterized from the same SVG) as fallbacks for browsers/iOS that don't support SVG favicons - all four wired via `<link>` tags in `header.php`'s `<head>`
* Add an optional background video to the home, Nosotros and Garantía hero banners as an alternative to the existing background image: a new "Video de fondo" field (`hero_video`, `type: file`, `mp4`/`webm`/`mov`) next to each `hero_image` field takes priority when filled in, rendering `<video autoplay muted loop playsinline>` with no controls and the image used as its `poster` while it loads; leaving it empty keeps the plain `<img>` as before (`inc/acf-fields/{homepage,nosotros,garantia}-canut.php`, `template-parts/blocks/{homepage,nosotros,garantia}-canut.php`, `template-parts/{front-page,nosotros,garantia}/hero.php`, `_front-page.scss`, `_nosotros.scss`, `_garantia.scss`)
* Fix a video added to a product's gallery silently disappearing again as soon as the product is saved: WooCommerce's own admin metabox (`WC_Meta_Box_Product_Images::output()`) rebuilds `_product_image_gallery` on every render from `wp_get_attachment_image( $id, 'thumbnail' )`, dropping any attachment that call returns empty for - which a video always does, since it has no image representation unless it was given a poster frame. Filter `wp_get_attachment_image_src` in the admin to fall back to the generic mime-type icon for videos (the same fallback core itself offers via `$icon = true`, a flag WooCommerce's metabox never passes), so the src comes back non-empty and the attachment survives the save (`inc/hooks/woocommerce.php`)
* Block pinch/double-tap zoom gestures on mobile: `maximum-scale=1, user-scalable=no` on the viewport meta tag (`header.php`) plus `touch-action: pan-x pan-y` on `html` (`_general.scss`) so a stray double-tap or pinch doesn't zoom the layout - note Safari on iOS ignores `user-scalable=no` for accessibility, so its pinch-zoom gesture still works regardless; only the double-tap zoom is actually suppressed there. Form field font sizes were already `>= 16px` on mobile (`_form-canut.scss`'s `body` type scale), so Safari's zoom-on-input-focus was never triggering
* Add prev/next arrows to the product gallery's main slide (`.product-gallery-nav`) and to the zoomed-in lightbox (`.product-gallery-lightbox-nav`, also wired to the keyboard's Left/Right arrows), on top of the existing thumbnail/dot/swipe ways to move through a product's photos; both reuse `icon-caret-right.svg` rotated for "prev". The lightbox steps through the same gallery's other (non-video) photos independently of the main slide underneath, hiding its arrows entirely when there's nothing to step to (`woocommerce/single-product.php`, `modules/product-gallery.js`, `_product.scss`)
* Make the product gallery's main image scale with a real `aspect-ratio: 3 / 4` (matching the actual photos, portrait) instead of a fixed height per breakpoint, so it grows/shrinks proportionally with the column's width at any screen size instead of cropping to whatever shape a hardcoded height happened to leave. Since flexbox can't just `align-items: stretch` the desktop thumbnail rail to match a sibling whose height is no longer a fixed value (a flex item's own hypothetical size is still content-based, so with many thumbs the *rail* would end up stretching the row instead), `modules/product-gallery.js` now measures the track's real rendered height via `ResizeObserver` and feeds it to the rail as a `--gallery-track-height` custom property - same pattern already used for `--site-header-height` (`_product.scss`)
* Complete the single-product page's JSON-LD for Google's Product rich result requirements: WooCommerce's own `class-wc-structured-data.php` already covers name, description, image, sku/gtin, offers and reviews, but was missing `brand`, `itemCondition`, `size`/`color`/`material` (read from the product's real WooCommerce attributes, never fabricated), extra gallery images, and the two nested types needed for full Merchant listing eligibility - `hasMerchantReturnPolicy` and `shippingDetails`. Added via a new `woocommerce_structured_data_product` filter (`inc/hooks/structured-data.php`); brand and the return/shipping terms are new per-product fields on "Página de producto CANUT" (tab "10. Datos estructurados" in `inc/acf-fields/product-canut.php`) rather than a single sitewide value, since they can legitimately differ product to product, each falling back to what's already shown on the page (5-day returns, 1-3 day shipping) when left empty
* Add an "Ajustes de Scripts" options page (`inc/acf-fields/ajustes-scripts.php`, registered in `inc/hooks/custom-scripts.php`) with three fields - Head, Body and Footer - to insert custom markup (Google Tag Manager, tracking pixels, verification meta tags) on every page without a code deploy, output verbatim on `wp_head`/`wp_body_open`/`wp_footer` respectively
* Fix the sticky site header/product gallery's `top` offset silently resolving to `auto` (making the whole `position: sticky` a no-op) whenever a calc() using `--admin-bar-offset` ran: its `:root` default was a unitless `0`, and adding that to another length (`--site-header-height`, in `px`) inside `calc()` is an invalid `<number>` + `<length>` operation, which invalidates the entire expression - give it an explicit `0px` instead (`_site-header.scss`)
* Fix the product gallery overflowing past the right edge of the viewport on mobile for products with many photos, dragging the main image, dots and thumbnail strip along with it into one wide unscrollable block instead of each behaving as its own contained, swipeable strip: `.product-gallery` is a grid item, whose automatic min-width defaults to its content's min-content size rather than 0 - the thumbnail strip's full unscrolled width (every thumb laid out side by side) was winning out over its own `overflow-x: auto` and forcing the whole column past the screen edge. Add `min-width: 0` (`_product.scss`)
* Fix the product gallery's desktop thumbnail rail growing past the main image and pushing the rest of the page down on products with many photos: cap it to the track's own height (`40rem`) and scroll internally instead, with up/down arrow buttons (`.product-gallery-thumbs-arrow`) that only appear once the rail actually overflows (`modules/product-gallery.js` measures `scrollHeight` vs `clientHeight` and toggles `.has-overflow`); mobile's horizontal strip is unaffected. Also add video support to the main gallery: a gallery attachment whose mime type is `video/*` (WooCommerce's own gallery media frame allows picking any file type, not just images) now renders as a playable `<video>` on the main slide instead of an `<img>`, with a play-icon overlay on its thumbnail (`icon-play-circle.svg`, Phosphor fill); video slides are excluded from the click-to-zoom lightbox and pause automatically when another slide becomes active (`woocommerce/single-product.php`, `_product.scss`)
* Make the single product page's gallery a bit more vertical for a better crop: track height `24rem`/`34rem` (mobile/desktop) to `28rem`/`40rem` (`_product.scss`)
* Fix a blank white layer covering the site on every page: the iframe modal and cart drawer (`_iframe-modal-canut.scss`, `_cart-drawer-canut.scss`) each set `display: flex` directly on their own `.iframe-modal-canut`/`.cart-drawer-canut` class, with no `[open]` qualification - a class selector outranks the browser's own `dialog { display: none }` UA rule (a bare type selector) regardless of the `[open]` attribute, so both `<dialog>`s stayed visibly rendered (the cart drawer just happened to be masked off-screen by its own closed-state `transform: translateX(100%)`; the iframe modal has no such transform, so it sat centred over the page, always). Add `&:not([open]) { display: none; }` to both so they're only ever visible after `showModal()`, as intended
* Fix the front page featured product image cropping the actual product out of frame: briefly tried a full portrait `aspect-ratio: 3/4` there, but these are wide lifestyle photos (product + pet in a room) with the product often sitting off to one edge rather than centred, so a centre-cropped portrait slice could cut it out entirely and show mostly floor/background instead - settled on `4/3` (still taller than the original `654/357`, ≈1.83:1, without cropping that aggressively) and reverted the grid column back to `7fr 5fr` (`_front-page.scss`)
* Fix the product gallery's thumbnail rail settling mid-thumb (clipping one half-cut instead of a full thumb with its margin intact) when a product has more photos than fit on screen, and the active thumbnail not scrolling into view on its own when swiping the main image or clicking a dot - only clicking a thumb directly kept it visible before. Add `scroll-snap-type: x proximity` to the rail and `scroll-snap-align: center` per thumb so it always rests on a full one, and scroll the active thumb into view from the same `setActiveIndex()` every trigger (thumb click, dot click, swipe) already goes through (`modules/product-gallery.js`, `_product.scss`)
* Turn the checkout footer's Soporte / Política de devoluciones / Envíos links into a popup instead of navigating away: each now opens a shared `<dialog>` (`template-parts/modal/iframe-modal.php`) that loads its target page in an `<iframe>`, closing on Escape, backdrop click or its own close button (`modules/iframe-modal-canut.js`, `_iframe-modal-canut.scss`) - same native-`<dialog>`/`showModal()` idiom as the cart drawer. Fixes the links themselves while at it: they previously pointed at the Shop page, a bare `https://wa.me/` and the homepage instead of their actual `soporte`/`politica-de-devoluciones-y-reembolsos`/`politica-de-envios` pages (`footer.php`)
* Remove the physical showroom address from the site footer and the Contacto page, replacing both with a simple "Disponibles en toda Colombia" coverage note - the footer's address paragraph now just states this instead of a street address (`footer.php`), and the Contacto page's Google Maps embed/"view on Maps" button is replaced with a small coverage card (`template-parts/contacto/info.php`, `.contacto-coverage` in `_contacto.scss`). Removes the now-unused Dirección/map URL ACF fields and their "5. Ubicación" tab (`inc/acf-fields/contacto-canut.php`)
* Turn the product page's "Comprar ahora" button into an AJAX add-to-cart CTA that opens the cart drawer on success instead of navigating straight to checkout, matching the shop grid's own quick-add button - same `added_to_cart`/`ajax_add_to_cart` lifecycle (`modules/cart-drawer.js`), server-rendered "already in cart" state (`WC()->cart->find_product_in_cart()`) and fallback to the old direct-to-checkout link for products that don't support AJAX add-to-cart. Once the product is in the cart the button relabels to "Finalizar compra" and its href becomes the checkout URL, so a second click actually completes the purchase rather than reopening the drawer again; the drawer's click handler tells the two "added" buttons apart via a new `data-added-href` attribute (`woocommerce/single-product.php`). Generalises the label-swap selector shared with the shop grid's button from a fixed class to `[data-action-label]` (`woocommerce/content-product.php`)
* Add an animated speech-bubble nudge above the floating WhatsApp button, cycling through a set of short messages/questions ("¿Tienes dudas sobre tu pedido?" etc.) to invite clicks - editable from Ajustes > WhatsApp as a new "Mensajes animados" toggle + repeater (`field_whatsapp_bubble_enabled`/`field_whatsapp_bubble_messages` in `inc/acf-fields/ajustes-whatsapp.php`, read via new `get_whatsapp_bubble_messages()` in `inc/hooks/whatsapp.php`). Each message is pre-rendered server-side (`footer.php`) and `modules/whatsapp-float-bubble.js` just toggles which one is current, fading/sliding the bubble in and out on a loop - no messages configured means no bubble markup at all, and the whole thing no-ops under `prefers-reduced-motion: reduce` since it's a decorative nudge, not essential content. Moves `.whatsapp-float`'s fixed positioning onto a new `.whatsapp-float-wrap` so the bubble can anchor off the same box (`_whatsapp-float.scss`)
* Fix every WhatsApp link (floating button, header CTA, product/garantía/centro de ayuda CTAs, contacto page channels) still showing a diagonal arrow icon after the `is-no-arrow`/colour fixes below: that arrow wasn't `button-canut-base`'s hover caret but a second, separate one - the base theme's `external-link.js` auto-appends an "opens externally" arrow SVG to any link pointing off-site, and `wa.me` was never in `external_link_domains_exclude` (`functions.php`), so every WhatsApp link got flagged as external and picked up its own indicator on top of the WhatsApp icon it already carries
* Fix the Nosotros hero manifesto banner ("Por qué existe CANUT") rendering centered on the page instead of left-aligned with the header logo and rest of the site's content: `.wrap-canut` and `.nosotros-hero-content` were combined on the same element, so the content's own `max-width: 36rem` (from `.nosotros-hero-content`) overrode `.wrap-canut`'s wider constraint and got centered by the hero's `justify-content: center`; split them into nested elements so `.wrap-canut` keeps constraining the row while `.nosotros-hero-content` just sizes the text block inside it (`template-parts/nosotros/hero.php`). Also make the active top-level nav item (e.g. "Nosotros" while on that page) show in the theme's green (`--wp--preset--color--success`) instead of plain black, since both previously used the same `black` token and gave no visual indication of the current page (`_nav-desktop.scss`)
* Fix the floating WhatsApp button using the theme's own dark green (`--wp--preset--color--primary-dark`) instead of WhatsApp's own brand green, making it read as a generic site CTA rather than a recognisable WhatsApp button; hardcode WhatsApp's official green (`#25d366`, `#1da851` on hover) as local custom properties since it's a third-party brand colour, not a theme palette choice (`_whatsapp-float.scss`)
* Fix the shop "Consultar por WhatsApp", product page "Hablar con un asesor" and centro de ayuda category WhatsApp CTAs showing `button-canut-base`'s hover-reveal caret next to the WhatsApp icon; add the existing `is-no-arrow` opt-out class (already used by the contacto page's own WhatsApp CTA) so only the icon shows (`woocommerce/archive-product.php`, `woocommerce/single-product.php`, `template-parts/centro-de-ayuda/category-section.php`)
* Add a floating WhatsApp button (`.whatsapp-float`, `features/_whatsapp-float.scss`), fixed bottom-right on every page, linking to the global Ventas number (`get_whatsapp_url( 'ventas' )`); hidden on checkout to match its existing distraction-free header/footer (`footer.php`)
* Add an animated glow to the front page hero CTA ("Comprar ahora"): a conic-gradient ring continuously rotates around the button (angle animated via a registered `@property` so the browser can interpolate it smoothly instead of jumping), switching from green to orange on hover/focus. Fixed an earlier attempt that used the `background-clip: padding-box, border-box` two-layer trick, which needs an opaque inner layer to visually fake the ring and so cannot support a truly empty interior - with a transparent inner layer (as this button needs, so the hero photo always shows straight through with no fill) that trick just paints the gradient across the whole button instead of a thin ring. Replaced with a `::before` overlay that actually cuts the ring shape out of a full-size conic-gradient via `mask-composite: exclude` (the overlay's own `padding` carves its content-box out of its border-box, and XOR-ing those two mask layers leaves only that gap - the ring - visible), leaving `button-canut-ghost`'s own fill completely untouched in every state (`home-hero-cta` in `template-parts/front-page/hero.php`, `_front-page.scss`)
* Change the mobile drawer's "Contáctanos" CTA from a solid orange button to the existing green outline style (`button-canut-secondary` instead of `button-canut-primary`), matching the plain bordered look already used elsewhere in the design system, including its hover-reveal arrow (`template-parts/header/navigation.php`)
* Fix the mobile drawer's "Contáctanos" button floating with a large empty gap underneath it instead of sitting near the bottom edge: `.menu-items-wrapper` had a `padding-bottom: 7.5rem` meant to keep the last nav item visible past iOS Safari's chrome on long menus, but since the button is already pinned to the bottom via `margin-top: auto`, that padding just added dead space after it; remove it and give the button's own wrapper a flat `20px` bottom padding instead (`_nav-mobile.scss`)
* Add an "Ajustes de WhatsApp" ACF options page (`inc/acf-fields/ajustes-whatsapp.php`, registered in `inc/hooks/whatsapp.php`) with two fields - Número de Ventas and Número de Soporte - as the single place to edit the site's WhatsApp numbers; every WhatsApp link in the theme (header CTA, footer social icon, shop "Consultar por WhatsApp", product page "Comprar por WhatsApp" and help CTA, garantía CTA, centro de ayuda category CTAs, contacto page channels) now reads from these two fields via new `get_whatsapp_url()`/`get_whatsapp_number()` helpers instead of a hardcoded `https://wa.me/` link or a number re-entered per page/product. Removes the now-redundant per-field WhatsApp number fields this replaces (`help_cta_whatsapp_number` on the product page, `cta_whatsapp_number` on garantía); the contacto page's WhatsApp channels repeater keeps its own per-channel number for a "Personalizado" option but defaults each row to the new global Ventas/Soporte numbers. Also fixes the centro de ayuda category CTA, which had no phone number in its `wa.me` link at all before this
* Increase the size of the header cart/account icons for better visibility: `1.25rem` to `1.75rem` on desktop, `1.5rem` to `1.875rem` on mobile (`_site-header.scss`)
* Fix the sticky product gallery still starting under the site header when the WP admin bar is visible: its sticky `top` only offset by `--site-header-height`, not by `--admin-bar-offset` (the amount the header itself is already pushed down by while logged in), so the header covered the top of the gallery by exactly that height as it scrolled (`_product.scss`)
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
