/**
 * Reusable loading/lock behaviour for .button-canut-base buttons.
 * Import `withButtonCanutLoading` anywhere a button triggers an async action
 * (add to cart, checkout submit, etc.) to show the spinner state and stop a
 * second click from firing the action again while it's in flight.
 */

/**
 * Runs `task` while the button is locked in its loading state, restoring the
 * button afterwards regardless of whether `task` resolves or rejects.
 * @param {HTMLButtonElement} button
 * @param {() => (void | Promise<void>)} task
 * @returns {Promise<void>}
 */
export const withButtonCanutLoading = async (button, task) => {
  if (button.disabled) return;

  button.classList.add('is-loading');
  button.disabled = true;
  button.setAttribute('aria-busy', 'true');

  try {
    await task();
  } finally {
    button.classList.remove('is-loading');
    button.disabled = false;
    button.removeAttribute('aria-busy');
  }
};

const initButtonCanutLoadingDemo = () => {
  const buttons = document.querySelectorAll('[data-canut-loading-demo]');

  buttons.forEach((button) => {
    button.addEventListener('click', () => {
      withButtonCanutLoading(button, () => new Promise((resolve) => {
        setTimeout(resolve, 1500);
      }));
    });
  });
};

export default initButtonCanutLoadingDemo;
