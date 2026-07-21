/**
 * Shop filter bar (woocommerce/archive-product.php, sass/views/_shop.scss).
 * Each attribute dropdown holds checkboxes plus one hidden input per
 * taxonomy - WooCommerce's native filter_{attribute} query var only reads a
 * single comma-separated string, never an array, so on submit this joins
 * whichever checkboxes are checked into that hidden input. Also closes any
 * other open dropdown when one opens, since they're independent <details>
 * elements with no built-in mutual exclusivity.
 */
const initShopFilters = () => {
  const bar = document.querySelector('[data-shop-filter-bar]');

  if (!bar) {
    return;
  }

  const dropdowns = bar.querySelectorAll('[data-shop-filter]');

  dropdowns.forEach((dropdown) => {
    dropdown.addEventListener('toggle', () => {
      if (!dropdown.open) {
        return;
      }

      dropdowns.forEach((other) => {
        if (other !== dropdown) {
          other.open = false;
        }
      });
    });
  });

  bar.addEventListener('submit', () => {
    const hiddenInputs = bar.querySelectorAll('[data-filter-hidden]');

    hiddenInputs.forEach((hidden) => {
      const { filterHidden: taxonomy } = hidden.dataset;
      const checked = bar.querySelectorAll(`[data-filter-taxonomy="${taxonomy}"]:checked`);

      hidden.value = Array.from(checked).map((checkbox) => checkbox.value).join(',');
    });
  });
};

export default initShopFilters;
