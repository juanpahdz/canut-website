/**
 * Cycles the messages inside the animated bubble above the floating
 * WhatsApp button (see .whatsapp-float-bubble in footer.php), one at a
 * time: fade/slide in, hold, fade/slide out, wait, repeat. Messages
 * themselves come from Ajustes > WhatsApp (inc/acf-fields/ajustes-whatsapp.php).
 * No-ops entirely under prefers-reduced-motion, since the bubble is a
 * decorative nudge rather than essential content.
 */
const FIRST_DELAY = 4000;
const SHOW_DURATION = 6000;
const HIDE_DURATION = 10000;

const initWhatsappFloatBubble = () => {
  const bubble = document.querySelector('[data-whatsapp-float-bubble]');

  if (!bubble || window.matchMedia('(prefers-reduced-motion: reduce)').matches) {
    return;
  }

  const messages = [...bubble.querySelectorAll('.whatsapp-float-bubble-message')];

  if (!messages.length) {
    return;
  }

  let index = 0;

  const showNext = () => {
    messages.forEach((message, messageIndex) => {
      message.classList.toggle('is-current', messageIndex === index);
    });
    bubble.classList.add('is-visible');

    setTimeout(() => {
      bubble.classList.remove('is-visible');
      index = (index + 1) % messages.length;
      setTimeout(showNext, HIDE_DURATION);
    }, SHOW_DURATION);
  };

  setTimeout(showNext, FIRST_DELAY);
};

export default initWhatsappFloatBubble;
