/* eslint-disable max-len, no-param-reassign, no-unused-vars */
/**
 * Air theme JavaScript.
 */

// Import modules
import {
  styleExternalLinks,
  initExternalLinkLabels,
} from './modules/external-link';
import initAnchors from './modules/anchors';
import backToTop from './modules/top';
import initA11ySkipLink from './modules/a11y-skip-link';
import initA11yFocusSearchField from './modules/a11y-focus-search-field';
import {
  navSticky,
  navClick,
  navDesktop,
  navMobile,
} from './modules/navigation';
import initDesignSystem from './modules/design-system';
import initStickyHeader from './modules/sticky-header';
import initTabsCanut from './modules/tabs-canut';
import initProductGallery from './modules/product-gallery';
import initGalleryStripVideoFallback from './modules/gallery-strip-video-fallback';
import initFaqImageSync from './modules/faq-image-sync';
import initAccordionCanut from './modules/accordion-canut';
import initCartDrawer from './modules/cart-drawer';
import initIframeModalCanut from './modules/iframe-modal-canut';
import initPaginaInformativaToc from './modules/pagina-informativa';
import initHelpCenterCanut from './modules/help-center-canut';
import initShopFilters from './modules/shop-filters';
import initWhatsappFloatBubble from './modules/whatsapp-float-bubble';
import initCheckoutCanut from './modules/checkout-canut';
import initCheckoutStepsCanut from './modules/checkout-steps-canut';
import initThankYouCanut from './modules/thank-you-canut';
import initCookieNoticeCanut from './modules/cookie-notice-canut';
// Define Javascript is active by changing the body class
document.body.classList.remove('no-js');
document.body.classList.add('js');

document.addEventListener('DOMContentLoaded', () => {
  initAnchors();
  backToTop();
  styleExternalLinks();
  initExternalLinkLabels();
  initA11ySkipLink();
  initA11yFocusSearchField();

  // Init navigation
  // If you want to enable click based navigation, comment navDesktop() and uncomment navClick()
  // Remember to enable styles in assets/src/sass/navigation/navigation.scss
  navDesktop();
  // navClick();
  navMobile();

  initDesignSystem();
  initStickyHeader();
  initTabsCanut();
  initProductGallery();
  initGalleryStripVideoFallback();
  initFaqImageSync();
  initAccordionCanut();
  initCartDrawer();
  initIframeModalCanut();
  initPaginaInformativaToc();
  initHelpCenterCanut();
  initShopFilters();
  initWhatsappFloatBubble();
  initCheckoutCanut();
  initCheckoutStepsCanut();
  initThankYouCanut();
  initCookieNoticeCanut();

  // Uncomment if you like to use a sticky navigation
  // navSticky();
});
