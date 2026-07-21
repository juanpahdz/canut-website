/**
 * Centro de ayuda (page-centro-de-ayuda.php): live search + sidebar scrollspy.
 *
 * Every FAQ item is already rendered server-side
 * (template-parts/centro-de-ayuda/category-section.php) - search doesn't
 * re-render anything, it just asks canut_help_search (inc/hooks/help-center.php,
 * a real WP_Query + ACF `palabras_clave` + category-name match) which
 * resource IDs match and shows/hides the corresponding `[data-help-center-resource]`
 * elements. Debounced, and an in-flight request is aborted if the visitor
 * keeps typing so a slow, stale response can't overwrite a newer one.
 */
const DEBOUNCE_MS = 250;
const MIN_QUERY_LENGTH = 2;

let searchAbortController = null;

/**
 * @param {number[]|null} matchingIds Resource IDs to keep visible, or null
 * to clear the search state and show everything again.
 */
const applyResults = (matchingIds) => {
  const categories = document.querySelectorAll('[data-help-center-category]');
  let anyVisible = false;

  categories.forEach((category) => {
    let categoryHasVisible = false;

    category.querySelectorAll('[data-help-center-resource]').forEach((item) => {
      const isMatch = matchingIds === null || matchingIds.includes(Number(item.dataset.helpCenterResource));

      item.classList.toggle('is-hidden', !isMatch);
      if (isMatch) categoryHasVisible = true;
    });

    category.hidden = !categoryHasVisible;
    if (categoryHasVisible) anyVisible = true;
  });

  const emptyState = document.querySelector('[data-help-center-empty]');

  if (emptyState) emptyState.hidden = matchingIds === null || anyVisible;
};

const requestSearch = async (query) => {
  const searchData = window.air_light_helpCenterSearch;

  if (!searchData) return;

  searchAbortController?.abort();
  searchAbortController = new AbortController();

  const body = new FormData();
  body.append('action', 'canut_help_search');
  body.append('nonce', searchData.nonce);
  body.append('s', query);

  try {
    const response = await fetch(searchData.ajax_url, { method: 'POST', body, signal: searchAbortController.signal });
    const { success, data } = await response.json();

    if (success) applyResults(data.ids);
  } catch (error) {
    if (error.name !== 'AbortError') throw error;
  }
};

const debounce = (callback, wait) => {
  let timeoutId;

  return (...args) => {
    clearTimeout(timeoutId);
    timeoutId = setTimeout(() => callback(...args), wait);
  };
};

const initHelpCenterSearch = () => {
  const form = document.querySelector('[data-help-center-search]');
  const input = form?.querySelector('[data-help-center-search-input]');

  if (!form || !input) return;

  const handleInput = debounce(() => {
    const query = input.value.trim();

    if (query.length < MIN_QUERY_LENGTH) {
      searchAbortController?.abort();
      applyResults(null);

      return;
    }

    requestSearch(query);
  }, DEBOUNCE_MS);

  input.addEventListener('input', handleInput);

  // A real search term submits straight to the live filter, not a full
  // page navigation - below the minimum length it's left alone so Enter
  // still falls back to native WordPress search (progressive enhancement).
  form.addEventListener('submit', (event) => {
    if (input.value.trim().length >= MIN_QUERY_LENGTH) event.preventDefault();
  });
};

const initHelpCenterScrollspy = () => {
  const categories = document.querySelectorAll('[data-help-center-category]');
  const links = document.querySelectorAll('[data-help-center-sidebar-link]');

  if (!categories.length || !links.length || typeof IntersectionObserver === 'undefined') return;

  const setActive = (slug) => {
    links.forEach((link) => link.classList.toggle('is-active', link.dataset.helpCenterSidebarLink === slug));
  };

  const observer = new IntersectionObserver((entries) => {
    const visible = entries.find((entry) => entry.isIntersecting);

    if (visible) setActive(visible.target.dataset.helpCenterCategory);
  }, { rootMargin: '-20% 0px -70% 0px' });

  categories.forEach((category) => observer.observe(category));
};

const initHelpCenterCanut = () => {
  initHelpCenterSearch();
  initHelpCenterScrollspy();
};

export default initHelpCenterCanut;
