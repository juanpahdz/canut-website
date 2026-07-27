/**
 * CANUT checkout (classic [woocommerce_checkout] shortcode - server-rendered
 * PHP templates under woocommerce/checkout/*.php, not the Checkout block).
 *
 * Everything CANUT-specific (numbered steps, payment method icons, banners,
 * the real #place_order button) is already part of that server-rendered
 * markup - see inc/hooks/checkout.php and woocommerce/checkout/*.php. The
 * only thing left for JS is the mobile sticky summary bar
 * (checkout-summary-canut, footer.php, hidden from $breakpoint-mobile up):
 * forwarding its click to the real #place_order button, keeping its
 * total/count in sync with classic checkout's own AJAX-updated order review,
 * scrolling down to the real order-review card at the end of the page (in
 * normal flow on mobile - see views/_checkout-canut.scss) when its arrow is
 * clicked, and wiring the order-summary card's own quantity stepper -
 * WooCommerce's bundled checkout.js fires a jQuery 'updated_checkout' event
 * on document.body after every address/shipping/payment-method/quantity
 * change, so no MutationObserver/polling is needed to catch any of it.
 */
import { withButtonCanutLoading } from './button-canut';

// #order_review's content is fully replaced on every 'updated_checkout'
// (review-order.php's card carries .woocommerce-checkout-review-order-table,
// the class core's checkout.js swaps fragments by), so both the total and
// the item-count text just get copied fresh off it each time -
// data-checkout-summary-canut-count is the same pre-pluralised string
// (_n(), inc/hooks/checkout.php) rather than duplicating that logic in JS.
const syncMobileSummaryBar = () => {
  const card = document.querySelector( '#order_review .woocommerce-checkout-review-order-table' );
  const totalEl = card?.querySelector( '.order-total .amount' );
  const totalTarget = document.querySelector( '[data-checkout-summary-canut-total]' );

  if ( totalEl && totalTarget ) {
    totalTarget.textContent = totalEl.textContent;
  }

  const countTarget = document.querySelector( '.checkout-summary-canut-count' );

  if ( card?.dataset.checkoutSummaryCanutCount && countTarget ) {
    countTarget.textContent = card.dataset.checkoutSummaryCanutCount;
  }
};

// Cart-item quantity stepper (.cart-drawer-canut-stepper, reused from the
// cart drawer's own component styling). Mutates WC()->cart via a dedicated
// AJAX action (checkout_update_item_qty(), inc/hooks/checkout.php) then
// re-triggers WooCommerce's own 'update_checkout' event - same AJAX cycle an
// address/payment-method change already goes through, so shipping quotes,
// fees (including the quantity discount) and totals all recalculate in that
// one already-correct place instead of a second, parallel re-render.
const requestCheckoutQtyUpdate = async ( cartItemKey, cartAction ) => {
  const summaryData = window.air_light_checkoutSummary;

  if ( ! summaryData ) return;

  const body = new FormData();
  body.append( 'action', 'canut_checkout_update_qty' );
  body.append( 'nonce', summaryData.nonce );
  body.append( 'cart_item_key', cartItemKey );
  body.append( 'cart_action', cartAction );

  const response = await fetch( summaryData.ajax_url, { method: 'POST', body } );
  const { success } = await response.json();

  if ( success && window.jQuery ) {
    window.jQuery( document.body ).trigger( 'update_checkout' );
  }
};

const wireMobileSummaryQtyStepper = () => {
  const orderReview = document.querySelector( '#order_review' );

  if ( ! orderReview ) return;

  // Delegated on #order_review itself (never replaced wholesale - only its
  // .woocommerce-checkout-review-order-table child is, on every update), so
  // this stays wired across re-renders without needing to re-bind.
  orderReview.addEventListener( 'click', ( event ) => {
    const qtyButton = event.target.closest( '[data-checkout-summary-canut-qty]' );
    const item = qtyButton?.closest( '[data-cart-item-key]' );

    if ( ! qtyButton || ! item ) return;

    withButtonCanutLoading( qtyButton, () => requestCheckoutQtyUpdate(
      item.dataset.cartItemKey,
      qtyButton.dataset.checkoutSummaryCanutQty,
    ) );
  } );
};

// Its `disabled` attribute (server-rendered on, footer.php) is managed
// entirely by wirePlaceOrderButtonState() (modules/checkout-steps-canut.js) -
// that's the single source of truth for whether the wizard is actually
// complete, kept in sync with the real #place_order button it forwards
// clicks to below. Forcing it enabled here regardless of that state would
// let this button finalize checkout before every step is confirmed, even
// with #place_order itself correctly disabled.
const wireMobileSummaryBar = () => {
  const button = document.querySelector( '[data-checkout-summary-canut-button]' );
  const placeOrderButton = document.querySelector( '#place_order' );

  if ( ! button || ! placeOrderButton ) return;

  button.addEventListener( 'click', () => placeOrderButton.click() );
};

// Jump link to the order-review card (same prefers-reduced-motion handling
// as modules/top.js's own back-to-top button), not a toggle - there's
// nothing to open/close, the card already sits in normal page flow.
const wireMobileSummaryScrollTo = () => {
  const toggle = document.querySelector( '[data-checkout-summary-canut-toggle]' );
  const target = document.querySelector( '#order_review' );

  if ( ! toggle || ! target ) return;

  toggle.addEventListener( 'click', ( event ) => {
    event.preventDefault();

    const prefersReducedMotion = window.matchMedia( '(prefers-reduced-motion: reduce)' ).matches;
    target.scrollIntoView( { behavior: prefersReducedMotion ? 'auto' : 'smooth', block: 'start' } );
  } );
};

// Exposes the fixed bar's real rendered height as a custom property so the
// order-review sheet (views/_checkout-canut.scss) can sit flush above it
// instead of a hardcoded offset - the bar's height isn't fixed, it changes
// with font loading and content wrapping.
const setSummaryBarHeightProperty = () => {
  const bar = document.querySelector( '.checkout-summary-canut' );

  if ( ! bar ) return;

  document.documentElement.style.setProperty( '--checkout-summary-canut-bar-height', `${ bar.offsetHeight }px` );
};

const initCheckoutCanut = () => {
  if ( ! document.querySelector( 'form.checkout' ) ) return;

  wireMobileSummaryBar();
  wireMobileSummaryScrollTo();
  wireMobileSummaryQtyStepper();
  syncMobileSummaryBar();
  setSummaryBarHeightProperty();

  window.addEventListener( 'resize', setSummaryBarHeightProperty );
  document.fonts?.ready.then( setSummaryBarHeightProperty );

  if ( window.jQuery ) {
    window.jQuery( document.body ).on( 'updated_checkout', syncMobileSummaryBar );
  }
};

export default initCheckoutCanut;
