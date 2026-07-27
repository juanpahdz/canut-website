/**
 * Thank-you page "Descargar PDF"/"Compartir" buttons (template-parts/order/success.php).
 * No server-side PDF generation: "download" just triggers the browser's own
 * print dialog (window.print(), styled by @media print rules in
 * _thank-you-canut.scss) so the visitor picks "Save as PDF" themselves.
 * "Share" uses the Web Share API where available, falling back to copying
 * the page URL to the clipboard - the button hides itself entirely if
 * neither is supported.
 */
const initThankYouCanut = () => {
  const downloadButton = document.querySelector('[data-thank-you-download-pdf]');

  if (downloadButton) {
    downloadButton.addEventListener('click', () => {
      window.print();
    });
  }

  const shareButton = document.querySelector('[data-thank-you-share]');

  if (!shareButton) {
    return;
  }

  const canShare = typeof navigator.share === 'function';
  const canCopy = navigator.clipboard && typeof navigator.clipboard.writeText === 'function';

  if (!canShare && !canCopy) {
    shareButton.remove();
    return;
  }

  const shareLabel = shareButton.querySelector('[data-thank-you-share-label]');
  const defaultLabel = shareLabel ? shareLabel.textContent : '';

  shareButton.addEventListener('click', async () => {
    const shareData = {
      title: shareButton.dataset.shareTitle || document.title,
      text: shareButton.dataset.shareText || '',
      url: window.location.href,
    };

    if (canShare) {
      try {
        await navigator.share(shareData);
      } catch {
        // Ignore: user cancelled the native share sheet, nothing to recover.
      }
      return;
    }

    await navigator.clipboard.writeText(shareData.url);

    if (shareLabel) {
      shareLabel.textContent = shareButton.dataset.shareCopiedLabel || defaultLabel;
      setTimeout(() => {
        shareLabel.textContent = defaultLabel;
      }, 2000);
    }
  });
};

export default initThankYouCanut;
