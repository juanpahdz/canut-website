/**
 * Swaps a shared image when a FAQ accordion item opens (see the extended
 * FAQ section on woocommerce/single-product.php). Mutual exclusivity
 * ("opening one closes the others") is native <details name="..."> behaviour
 * and needs no JS - this only handles the image swap on top of that.
 *
 * The swap is cross-faded: the image fades out, the src changes once it's
 * invisible, then it fades back in (see the opacity transition on
 * .product-faq-extended-media img in views/_product.scss).
 *
 * Scope with `data-faq-image-sync` on the wrapper, `data-faq-image-target`
 * on the <img>, and `data-faq-image` (the URL to swap to) on each <details>.
 */
const FADE_DURATION = 200;

const initFaqImageSync = () => {
  const sections = document.querySelectorAll('[data-faq-image-sync]');

  sections.forEach((section) => {
    const image = section.querySelector('[data-faq-image-target]');
    const items = section.querySelectorAll('[data-faq-image]');

    if (!image) return;

    items.forEach((item) => {
      item.addEventListener('toggle', () => {
        if (!item.open) return;

        const nextSrc = item.dataset.faqImage;

        if (!nextSrc || image.src === nextSrc) return;

        image.style.opacity = '0';

        window.setTimeout(() => {
          image.src = nextSrc;
          image.style.opacity = '1';
        }, FADE_DURATION);
      });
    });
  });
};

export default initFaqImageSync;
