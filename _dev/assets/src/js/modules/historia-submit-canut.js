/**
 * "Cuenta tu historia" submission dialog
 * (<dialog id="historia-submit-canut">, template-parts/historia/submit-form.php).
 *
 * Any element with data-canut-historia-open shows the dialog. When it also
 * carries data-canut-historia-product (the product page's "Escribir reseña"
 * button, woocommerce/single-product.php), that product is preselected in
 * the form and named in the dialog's title; otherwise the dialog opens
 * generic (product left up to the visitor, or none).
 *
 * Cloudflare Turnstile is rendered explicitly (render=explicit in the
 * script's src, template-parts/historia/submit-form.php) rather than
 * letting it auto-render on script load, since the dialog - and therefore
 * the widget's container - is hidden until showModal() runs; rendering into
 * a hidden container sizes the widget's iframe incorrectly.
 */
const initHistoriaSubmitCanut = () => {
  const dialog = document.getElementById('historia-submit-canut');
  const title = document.getElementById('historia-submit-canut-title');
  const form = document.getElementById('historia-submit-canut-form');
  const productField = document.getElementById('canut_historia_product');

  if (!dialog || typeof dialog.showModal !== 'function') return;

  const defaultTitle = title ? title.textContent : '';

  let turnstileReady = false;
  let turnstileWidgetId = null;

  const renderTurnstile = () => {
    if (!window.turnstile || turnstileWidgetId !== null) return;

    const container = document.getElementById('historia-submit-canut-turnstile');
    if (!container || !container.dataset.sitekey) return;

    turnstileWidgetId = window.turnstile.render(container, {
      sitekey: container.dataset.sitekey,
    });
  };

  document.addEventListener('canut-turnstile-ready', () => {
    turnstileReady = true;
    if (dialog.open) renderTurnstile();
  });

  const openDialog = (productId, productName) => {
    if (productField) productField.value = productId || '';

    if (title) {
      title.textContent = productName
        ? `${defaultTitle} sobre ${productName}`
        : defaultTitle;
    }

    dialog.showModal();
    if (turnstileReady) renderTurnstile();
  };

  document.querySelectorAll('[data-canut-historia-open]').forEach((trigger) => {
    trigger.addEventListener('click', () => {
      openDialog(
        trigger.dataset.canutHistoriaProduct,
        trigger.dataset.canutHistoriaProductName,
      );
    });
  });

  // Click on the ::backdrop (the dialog element itself receives the click
  // when it lands outside the content box) dismisses it, same as
  // cart-drawer/iframe-modal-canut.
  dialog.addEventListener('click', (event) => {
    if (event.target === dialog) dialog.close();
  });

  dialog.addEventListener('click', (event) => {
    if (event.target.closest('[data-canut-historia-close]')) dialog.close();
  });

  dialog.addEventListener('close', () => {
    form?.reset();

    if (turnstileWidgetId !== null && window.turnstile) {
      window.turnstile.reset(turnstileWidgetId);
    }
  });

  // Reopen automatically after the redirect back from a submission
  // (?historia=exito|error) - otherwise the confirmation/error banner
  // rendered inside the dialog would be invisible behind a closed dialog.
  if (new URLSearchParams(window.location.search).has('historia')) {
    dialog.showModal();
    if (turnstileReady) renderTurnstile();
  }
};

export default initHistoriaSubmitCanut;
