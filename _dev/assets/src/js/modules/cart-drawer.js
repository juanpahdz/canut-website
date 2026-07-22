/**
 * Cart drawer (<dialog id="cart-drawer-canut">, template-parts/cart/drawer.php).
 *
 * Opens from the header cart icon (template-parts/header/actions.php). The
 * quantity stepper and remove buttons hit the canut_cart_drawer_update AJAX
 * action (inc/hooks/cart-drawer.php), which re-renders the whole drawer
 * content server-side from WC()->cart and sends it back - simplest way to
 * stay correct with stock limits, fees and the payment discount all at once.
 */
import { withButtonCanutLoading } from './button-canut';

const updateDrawer = (content, count) => {
  const contentEl = document.getElementById('cart-drawer-canut-content');
  const countEl = document.getElementById('cart-drawer-canut-count');
  const badgeEl = document.querySelector('.header-actions-canut-cart-count');

  if (contentEl) contentEl.innerHTML = content;
  if (countEl) countEl.textContent = `(${count})`;

  if (badgeEl) {
    badgeEl.textContent = count;
    badgeEl.classList.toggle('is-empty', count === 0);
  }
};

const requestCartUpdate = async (cartItemKey, cartAction) => {
  const cartDrawerData = window.air_light_cartDrawer;

  if (!cartDrawerData) return;

  const body = new FormData();
  body.append('action', 'canut_cart_drawer_update');
  body.append('nonce', cartDrawerData.nonce);
  body.append('cart_item_key', cartItemKey);
  body.append('cart_action', cartAction);

  const response = await fetch(cartDrawerData.ajax_url, { method: 'POST', body });
  const { success, data } = await response.json();

  if (success) updateDrawer(data.content, data.count);
};

const initCartDrawer = () => {
  const dialog = document.getElementById('cart-drawer-canut');

  if (!dialog || typeof dialog.showModal !== 'function') return;

  document.querySelectorAll('[data-canut-cart-open]').forEach((trigger) => {
    trigger.addEventListener('click', () => dialog.showModal());
  });

  // WooCommerce's core add-to-cart.js (jQuery, always loaded alongside any
  // .ajax_add_to_cart button) fires this on document.body once its own AJAX
  // request succeeds and has already swapped in the fresh drawer content via
  // the woocommerce_add_to_cart_fragments filter (inc/hooks/cart-drawer.php).
  // Besides opening the dialog, this turns the button itself into its
  // "added" state (data-added-label/data-added-aria-label/data-added-href,
  // set in woocommerce/content-product.php and woocommerce/single-product.php)
  // rather than leaving WooCommerce's own "Ver carrito" text it inserts right
  // after the button (sass hides that one - a second, separate link next to
  // the button would be redundant). Adding .wc-interactive opts the button
  // out of add-to-cart.js's own delegated click handler
  // (`.add_to_cart_button:not(.wc-interactive)`), so the click listener below
  // can take over for anything after this point.
  if (window.jQuery) {
    window.jQuery(document.body).on('added_to_cart', (event, fragments, cartHash, $button) => {
      if (!dialog.open) dialog.showModal();

      const button = $button && $button[0];

      if (!button || !button.dataset.addedLabel) return;

      const label = button.querySelector('[data-action-label]');

      if (label) label.textContent = button.dataset.addedLabel;
      if (button.dataset.addedAriaLabel) button.setAttribute('aria-label', button.dataset.addedAriaLabel);
      if (button.dataset.addedHref) button.href = button.dataset.addedHref;

      button.classList.add('wc-interactive');
    });
  }

  // Once a button has flipped to its "added" state (above), clicking it
  // again should just reopen the drawer (card-product-canut's "Ver
  // carrito") - unless it carries data-added-href (single-product.php's
  // "Finalizar compra"), in which case it should navigate to checkout via
  // its own href instead of being intercepted.
  document.body.addEventListener('click', (event) => {
    const button = event.target.closest('.wc-interactive[data-added-label]');

    if (!button || button.dataset.addedHref) return;

    event.preventDefault();
    dialog.showModal();
  });

  // Click on the ::backdrop (the dialog element itself receives the click
  // when it lands outside the content box) dismisses it, same as
  // checkout-summary-canut.
  dialog.addEventListener('click', (event) => {
    if (event.target === dialog) dialog.close();
  });

  // Delegated - the close button, stepper and remove buttons are all
  // replaced wholesale whenever the drawer content re-renders.
  dialog.addEventListener('click', (event) => {
    if (event.target.closest('[data-canut-cart-close]')) {
      dialog.close();

      return;
    }

    const qtyButton = event.target.closest('[data-canut-cart-qty]');
    const item = qtyButton?.closest('[data-cart-item-key]');

    if (!qtyButton || !item) return;

    withButtonCanutLoading(qtyButton, () => requestCartUpdate(
      item.dataset.cartItemKey,
      qtyButton.dataset.canutCartQty,
    ));
  });
};

export default initCartDrawer;
