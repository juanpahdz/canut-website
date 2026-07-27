/**
 * CANUT checkout sequential wizard (Información de contacto / Envío / Método
 * de pago / Método de envío - see woocommerce/checkout/form-checkout.php and
 * inc/hooks/checkout.php's own docblocks for why this order/locking exists).
 *
 * Each lockable step (`[data-lockable]`) starts with `is-locked` baked into
 * the server-rendered markup (views/_checkout-canut.scss hides its body and
 * shows a placeholder message for that state) until the step before it is
 * confirmed via its own "Continuar" button. Confirming a step collapses it
 * to a read-only summary (`is-confirmed`) built from the fields' own current
 * values - no server round trip - and unlocks the next step; its "Modificar"
 * button reopens it and, for Envío/Método de pago, re-locks every step after
 * it too - cascading forward so a stale confirmation (payment/shipping
 * picked against an address that just changed) can't linger.
 * "Información de contacto" is the one step exempt from that cascade (see
 * wireEditButtons() below) - name/phone don't feed any later calculation, so
 * reopening it doesn't need to disturb anything already confirmed after it.
 *
 * Confirming "Método de pago" is the one step that DOES need a server round
 * trip before unlocking the next: Dropi's freight quote depends on whether
 * the order is COD (JPIODFW_Shipping::order_is_cod(), reads the chosen
 * payment method from session), so the shipping cards need a fresh
 * `update_checkout` (WooCommerce core's own AJAX cycle, already wired to
 * refresh those cards via checkout_shipping_methods_fragment() in
 * inc/hooks/checkout.php) before "Método de envío" unlocks - otherwise it'd
 * briefly show pricing quoted for the previous payment method.
 *
 * The coupon field inside "Método de pago" (checkout_render_coupon_field(),
 * inc/hooks/checkout.php) isn't a real <form> - it can't be, nested inside
 * <form class="checkout"> - so its toggle/apply is wired here too, posting
 * straight to WooCommerce's own apply_coupon AJAX endpoint (same request
 * core's own form-bound handler would make).
 */
import { withButtonCanutLoading } from './button-canut';

const UPDATE_CHECKOUT_FALLBACK_MS = 8000;

const getStep = (number) => document.querySelector(`.checkout-step-canut[data-step="${number}"]`);

const scrollToStep = (step) => {
  step.scrollIntoView({ behavior: 'smooth', block: 'start' });
};

/**
 * Re-locks every lockable step after `number`, un-confirming any that were
 * already confirmed - used both on init (nothing to cascade from, so a
 * no-op) and whenever a "Modificar" button reopens an earlier step.
 */
const lockStepsAfter = (number) => {
  document.querySelectorAll('.checkout-step-canut[data-lockable]').forEach((step) => {
    if (Number(step.dataset.step) > number) {
      step.classList.remove('is-confirmed', 'is-loading');
      step.classList.add('is-locked');
    }
  });
};

/**
 * Validates a step's own required fields the same way WooCommerce's bundled
 * checkout.js already tags them (`.form-row.validate-required`, toggling the
 * same `woocommerce-invalid`/`woocommerce-invalid-required-field`/
 * `woocommerce-validated` classes it uses - see views/_form-canut.scss's
 * error-border rule) - reused rather than reinvented so a field already
 * flagged by core's own inline validation (email format, etc.) doesn't get
 * silently overridden by a laxer check here.
 * @param {Element} step
 * @returns {HTMLElement|null} the first invalid field, or null if all valid
 */
const validateStepFields = (step) => {
  const rows = step.querySelectorAll('.checkout-step-canut-body .form-row.validate-required');
  let firstInvalidField = null;

  rows.forEach((row) => {
    const field = row.querySelector('input, select, textarea');
    if (!field) return;

    const isEmpty = 'checkbox' === field.type ? !field.checked : !field.value.trim();

    row.classList.toggle('woocommerce-invalid', isEmpty);
    row.classList.toggle('woocommerce-invalid-required-field', isEmpty);
    row.classList.toggle('woocommerce-validated', !isEmpty);

    if (isEmpty && !firstInvalidField) {
      firstInvalidField = field;
    }
  });

  return firstInvalidField;
};

const fieldValue = (id) => (document.getElementById(id)?.value || '').trim();

const buildContactSummary = (step) => {
  const summary = step.querySelector('[data-step-summary="1"]');
  if (!summary) return;

  const email = fieldValue('billing_email');
  const code = fieldValue('billing_phone_country_code');
  const phone = fieldValue('billing_phone');
  const phoneDisplay = phone ? `+${code} ${phone}` : '';

  const detailField = summary.querySelector('[data-summary-field="contact"]');
  if (detailField) detailField.textContent = [email, phoneDisplay].filter(Boolean).join(' · ');
};

const buildAddressSummary = (step) => {
  const summary = step.querySelector('[data-step-summary="2"]');
  if (!summary) return;

  const name = [fieldValue('billing_first_name'), fieldValue('billing_last_name')]
    .filter(Boolean)
    .join(' ');
  const address = [
    fieldValue('billing_address_1'),
    fieldValue('billing_address_2'),
    fieldValue('billing_neighborhood'),
    fieldValue('billing_city'),
    fieldValue('billing_state'),
  ]
    .filter(Boolean)
    .join(', ');

  const nameField = summary.querySelector('[data-summary-field="name"]');
  const addressField = summary.querySelector('[data-summary-field="address"]');
  if (nameField) nameField.textContent = name;
  if (addressField) addressField.textContent = address;
};

const buildPaymentSummary = (step) => {
  const summary = step.querySelector('[data-step-summary="3"]');
  const checked = step.querySelector('.payment-option-canut-input:checked');
  const title = checked
    ?.closest('.payment-option-canut')
    ?.querySelector('.payment-option-canut-title')
    ?.textContent?.trim() || '';

  const detailField = summary?.querySelector('[data-summary-field="payment"]');
  if (detailField) detailField.textContent = title;
};

const confirmStep = (step) => {
  step.classList.remove('is-locked');
  step.classList.add('is-confirmed');
};

const handleContinueContact = (step) => {
  const firstInvalid = validateStepFields(step);

  if (firstInvalid) {
    firstInvalid.focus();
    return;
  }

  buildContactSummary(step);
  confirmStep(step);

  const nextStep = getStep(2);
  if (!nextStep) return;

  nextStep.classList.remove('is-locked');
  scrollToStep(nextStep);
};

const handleContinueAddress = (step) => {
  const firstInvalid = validateStepFields(step);

  if (firstInvalid) {
    firstInvalid.focus();
    return;
  }

  buildAddressSummary(step);
  confirmStep(step);

  const nextStep = getStep(3);
  if (!nextStep) return;

  nextStep.classList.remove('is-locked');
  scrollToStep(nextStep);
};

/**
 * Confirms the payment step, then waits for a real `update_checkout` AJAX
 * round trip (so the next step's shipping cards re-quote against the just-
 * chosen payment method) before unlocking "Método de envío". Falls back to
 * unlocking after UPDATE_CHECKOUT_FALLBACK_MS regardless, so a dropped
 * request can't strand the customer on a permanently "loading" step.
 */
const handleContinuePayment = (step, button) => {
  const checked = step.querySelector('.payment-option-canut-input:checked');
  if (!checked) return;

  buildPaymentSummary(step);
  confirmStep(step);

  const nextStep = getStep(4);
  if (!nextStep) return;

  if (!window.jQuery) {
    nextStep.classList.remove('is-locked');
    return;
  }

  nextStep.classList.add('is-loading');

  withButtonCanutLoading(button, () => new Promise((resolve) => {
    let settled = false;
    const finish = () => {
      if (settled) return;
      settled = true;
      nextStep.classList.remove('is-loading', 'is-locked');
      scrollToStep(nextStep);
      resolve();
    };

    window.jQuery(document.body).one('updated_checkout', finish);
    window.jQuery(document.body).trigger('update_checkout');
    setTimeout(finish, UPDATE_CHECKOUT_FALLBACK_MS);
  }));
};

const wireContinueButtons = () => {
  document.querySelectorAll('[data-step-continue]').forEach((button) => {
    const stepNumber = Number(button.dataset.stepContinue);
    const step = getStep(stepNumber);
    if (!step) return;

    button.addEventListener('click', () => {
      if (1 === stepNumber) handleContinueContact(step);
      if (2 === stepNumber) handleContinueAddress(step);
      if (3 === stepNumber) handleContinuePayment(step, button);
    });
  });
};

/**
 * "Modificar" reopens a confirmed step. Steps whose values feed a later
 * calculation (Envío -> shipping cost/quote; Método de pago -> COD vs.
 * prepaid pricing) re-lock everything after them via lockStepsAfter(),
 * cascading forward so a stale confirmation can't linger against changed
 * data. "Información de contacto" is the one exception - name/phone don't
 * affect address, payment, or shipping-method calculations at all, so
 * reopening it leaves every later step exactly as it was.
 */
const wireEditButtons = () => {
  document.querySelectorAll('[data-step-edit]').forEach((button) => {
    button.addEventListener('click', () => {
      const stepNumber = Number(button.dataset.stepEdit);
      const step = getStep(stepNumber);
      if (!step) return;

      step.classList.remove('is-confirmed');

      if (1 !== stepNumber) {
        lockStepsAfter(stepNumber);
      }

      scrollToStep(step);
    });
  });
};

/**
 * Inline "have a coupon?" control inside the payment step
 * (checkout_render_coupon_field(), inc/hooks/checkout.php) - not a real
 * <form>, so posts straight to WooCommerce's own apply_coupon AJAX endpoint
 * (wc_checkout_params, already localized by core on every checkout page)
 * instead of relying on core's own form-submit-bound coupon handler.
 */
const wireCouponField = () => {
  const container = document.querySelector('.checkout-coupon-canut');
  if (!container || !window.jQuery || 'undefined' === typeof window.wc_checkout_params) return;

  const toggle = container.querySelector('[data-coupon-toggle]');
  const panel = container.querySelector('[data-coupon-panel]');
  const input = container.querySelector('[data-coupon-input]');
  const applyButton = container.querySelector('[data-coupon-apply]');

  if (!toggle || !panel || !input || !applyButton) return;

  toggle.addEventListener('click', () => {
    const isHidden = panel.hasAttribute('hidden');
    panel.toggleAttribute('hidden', !isHidden);
    toggle.setAttribute('aria-expanded', isHidden ? 'true' : 'false');
    if (isHidden) input.focus();
  });

  const applyCoupon = () => withButtonCanutLoading(applyButton, () => new Promise((resolve) => {
    window.jQuery.ajax({
      type: 'POST',
      url: window.wc_checkout_params.wc_ajax_url.toString().replace('%%endpoint%%', 'apply_coupon'),
      data: {
        security: window.wc_checkout_params.apply_coupon_nonce,
        coupon_code: input.value,
        billing_email: fieldValue('billing_email'),
      },
      dataType: 'html',
      complete: () => resolve(),
      success: (response) => {
        window.jQuery('.woocommerce-error, .woocommerce-message, .is-error, .is-success').remove();

        if (!response) return;

        const isError = -1 !== response.indexOf('woocommerce-error') || -1 !== response.indexOf('is-error');

        if (!isError) {
          panel.setAttribute('hidden', '');
          toggle.setAttribute('aria-expanded', 'false');
          input.value = '';
        }

        container.insertAdjacentHTML('beforebegin', response);

        window.jQuery(document.body).trigger('applied_coupon_in_checkout');
        window.jQuery(document.body).trigger('update_checkout', { update_shipping_method: false });
      },
    });
  }));

  applyButton.addEventListener('click', applyCoupon);
  input.addEventListener('keydown', (event) => {
    if ('Enter' !== event.key) return;
    event.preventDefault();
    applyCoupon();
  });
};

const MAX_ADDITIONAL_PHONES = 3;

/**
 * "Add another phone number" repeater in the contact step
 * (checkout_render_additional_phones(), inc/hooks/checkout.php) - clones the
 * server-rendered <template> (single source of truth for the country-code
 * option list, so it doesn't get duplicated here) for each new row, capped
 * at MAX_ADDITIONAL_PHONES so a customer can't spam the add button
 * indefinitely.
 */
const wireAdditionalPhones = () => {
  const repeater = document.querySelector('[data-phone-repeater]');
  const template = document.querySelector('[data-phone-repeater-template]');
  const list = repeater?.querySelector('[data-phone-repeater-list]');
  const addButton = repeater?.querySelector('[data-phone-repeater-add]');

  if (!repeater || !template || !list || !addButton) return;

  const updateAddButtonVisibility = () => {
    const count = list.querySelectorAll('.form-canut-phone-additional-row').length;
    addButton.hidden = count >= MAX_ADDITIONAL_PHONES;
  };

  addButton.addEventListener('click', () => {
    const row = template.content.firstElementChild.cloneNode(true);
    list.appendChild(row);
    row.querySelector('input')?.focus();
    updateAddButtonVisibility();
  });

  list.addEventListener('click', (event) => {
    const removeButton = event.target.closest('[data-phone-repeater-remove]');
    if (!removeButton) return;

    removeButton.closest('.form-canut-phone-additional-row')?.remove();
    updateAddButtonVisibility();
  });
};

const initCheckoutStepsCanut = () => {
  if (!document.querySelector('form.checkout')) return;

  wireContinueButtons();
  wireEditButtons();
  wireCouponField();
  wireAdditionalPhones();
};

export default initCheckoutStepsCanut;
