/**
 * Iframe modal (<dialog id="iframe-modal-canut">, template-parts/modal/iframe-modal.php).
 *
 * Any link with data-canut-iframe-modal opens its href inside the modal's
 * iframe instead of navigating away - e.g. the checkout footer's Soporte /
 * Política de devoluciones / Envíos links (footer.php).
 */
const initIframeModalCanut = () => {
  const dialog = document.getElementById('iframe-modal-canut');
  const frame = document.getElementById('iframe-modal-canut-frame');
  const title = document.getElementById('iframe-modal-canut-title');

  if (!dialog || !frame || typeof dialog.showModal !== 'function') return;

  document.querySelectorAll('[data-canut-iframe-modal]').forEach((trigger) => {
    trigger.addEventListener('click', (event) => {
      event.preventDefault();

      const label = trigger.textContent.trim();

      frame.src = trigger.href;
      frame.title = label;
      title.textContent = label;
      dialog.showModal();
    });
  });

  // Click on the ::backdrop (the dialog element itself receives the click
  // when it lands outside the content box) dismisses it, same as cart-drawer.
  dialog.addEventListener('click', (event) => {
    if (event.target === dialog) dialog.close();
  });

  dialog.addEventListener('click', (event) => {
    if (event.target.closest('[data-canut-iframe-close]')) dialog.close();
  });

  // Fires on Escape and both dialog.close() paths above - stop the iframe's
  // content (and any media/audio in it) rather than leaving it loaded in
  // the background.
  dialog.addEventListener('close', () => {
    frame.src = 'about:blank';
  });
};

export default initIframeModalCanut;
