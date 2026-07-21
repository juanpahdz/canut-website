/**
 * Behaviour for the internal /sistema-de-diseno reference page:
 * tabs switching, the cart-popup "copy code" button, and the button loading
 * demo (the reusable withButtonCanutLoading lock itself lives in button-canut.js).
 */
import initButtonCanutLoadingDemo from './button-canut';

const initDesignSystemTabs = () => {
  const tabGroups = document.querySelectorAll('[data-canut-tabs]');

  tabGroups.forEach((group) => {
    const triggers = group.querySelectorAll('[data-tab]');
    const panels = group.querySelectorAll('[data-tab-panel]');

    triggers.forEach((trigger) => {
      trigger.addEventListener('click', () => {
        const { tab } = trigger.dataset;

        triggers.forEach((item) => {
          item.classList.toggle('is-active', item === trigger);
          item.setAttribute('aria-selected', item === trigger ? 'true' : 'false');
        });

        panels.forEach((panel) => {
          panel.classList.toggle('is-active', panel.dataset.tabPanel === tab);
        });
      });
    });
  });
};

// Delegated on document (not queried once and bound per-button) so it keeps
// working for [data-canut-copy] buttons injected later - e.g. the cart
// drawer's discount code, whose content is replaced wholesale via AJAX
// (modules/cart-drawer.js) after every quantity change.
const initDesignSystemCopyCode = () => {
  document.addEventListener('click', (event) => {
    const button = event.target.closest('[data-canut-copy]');

    if (!button) return;

    const code = button.dataset.canutCopy;
    const label = button.querySelector('[data-canut-copy-label]');

    navigator.clipboard.writeText(code).then(() => {
      button.classList.add('is-copied');
      if (label) label.textContent = 'Copiado';

      setTimeout(() => {
        button.classList.remove('is-copied');
        if (label) label.textContent = 'Copiar';
      }, 2000);
    });
  });
};

const initDesignSystem = () => {
  initDesignSystemTabs();
  initDesignSystemCopyCode();
  initButtonCanutLoadingDemo();
};

export default initDesignSystem;
