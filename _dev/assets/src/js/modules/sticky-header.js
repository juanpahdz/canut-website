/**
 * Toggles `.is-scrolled` on `.site-header` once the page scrolls past the
 * top. On the front page the header starts transparent, overlaying the
 * hero (see layout/_site-header.scss `.home .site-header`); this class is
 * what switches it to a solid, fixed header while scrolling.
 */
const initStickyHeader = () => {
  const header = document.querySelector('.site-header');

  if (!header) return;

  const toggleScrolledState = () => {
    header.classList.toggle('is-scrolled', window.scrollY > 8);
  };

  // Exposes the header's real rendered height as a custom property so other
  // sticky elements (e.g. .product-gallery) can offset below it instead of
  // overlapping - the header's height isn't a fixed value, it changes with
  // font loading and breakpoint-driven padding/logo size.
  const setHeaderHeightProperty = () => {
    document.documentElement.style.setProperty('--site-header-height', `${header.offsetHeight}px`);
  };

  toggleScrolledState();
  setHeaderHeightProperty();
  window.addEventListener('scroll', toggleScrolledState, { passive: true });
  window.addEventListener('resize', setHeaderHeightProperty);
  document.fonts?.ready.then(setHeaderHeightProperty);
};

export default initStickyHeader;
