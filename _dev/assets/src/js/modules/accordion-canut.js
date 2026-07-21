/**
 * Animates open/close on the CANUT accordion (.accordion-canut-item, a
 * native <details>/<summary>). Native details/summary jump open/closed
 * instantly with no way to transition `height: auto`, so this intercepts
 * the toggle, animates height with the Web Animations API, and cross-fades
 * the panel content at the same time.
 *
 * Exclusive groups (only one item open at a time, e.g. the extended FAQ)
 * are opted into via `data-accordion-group="some-name"` on each item,
 * grouped together below - NOT the native <details name="..."> attribute:
 * that closes other group members synchronously and instantly the moment
 * `.open` is set, before this module ever gets a chance to animate them,
 * which is exactly the abrupt jump this file exists to avoid.
 */
const DURATION = 250;
const EASING = 'ease-out';

class AnimatedAccordion {
  constructor(details) {
    this.details = details;
    this.summary = details.querySelector('summary');
    this.panel = details.querySelector('.accordion-canut-panel');
    this.group = null; // assigned by initAccordionCanut for grouped items
    this.animation = null;
    this.isClosing = false;
    this.isExpanding = false;

    if (!this.summary || !this.panel) return;

    this.summary.addEventListener('click', (event) => this.onClick(event));
  }

  onClick(event) {
    event.preventDefault();

    if (this.isClosing || !this.details.open) {
      this.group?.forEach((other) => {
        if (other !== this && (other.details.open || other.isExpanding)) other.shrink();
      });
      this.open();
    } else if (this.isExpanding || this.details.open) {
      this.shrink();
    }
  }

  open() {
    this.details.style.height = `${this.details.offsetHeight}px`;
    this.details.open = true;
    window.requestAnimationFrame(() => this.expand());
  }

  expand() {
    this.isExpanding = true;
    const startHeight = `${this.details.offsetHeight}px`;

    // Measure the true natural open height by momentarily lifting the
    // pinned height rather than summing summary.offsetHeight + panel.offsetHeight:
    // that sum silently drops the panel <p>'s default browser margin (and any
    // other spacing between the two), which is exactly what produced a visible
    // jump at the end of the animation once the real height turned out taller
    // than the animated target. Read-then-restore happens before the next
    // paint, so nothing flashes to full size.
    //
    // This has to happen BEFORE `overflow: hidden` is applied below: that
    // establishes a new block formatting context, which stops the panel's
    // margin from collapsing the way it normally does and would otherwise
    // inflate this exact measurement.
    this.details.style.height = 'auto';
    const endHeight = `${this.details.offsetHeight}px`;
    this.details.style.height = startHeight;
    this.details.style.overflow = 'hidden';

    this.panel.style.opacity = '0';

    if (this.animation) this.animation.cancel();

    this.animation = this.details.animate(
      { height: [startHeight, endHeight] },
      { duration: DURATION, easing: EASING },
    );
    this.animation.onfinish = () => this.onAnimationFinish(true);
    this.animation.oncancel = () => { this.isExpanding = false; };

    window.requestAnimationFrame(() => {
      this.panel.style.opacity = '1';
    });
  }

  shrink() {
    this.isClosing = true;
    // Measured before `overflow: hidden` is applied below, same reasoning
    // as in expand(): it would otherwise inflate this reading too.
    const startHeight = `${this.details.offsetHeight}px`;
    // Closed state has no panel in flow, but the details element's own
    // border still counts toward its rendered height - add it so this lands
    // exactly on the natural closed size instead of a couple px short.
    const border = getComputedStyle(this.details);
    const borderHeight = parseFloat(border.borderTopWidth) + parseFloat(border.borderBottomWidth);
    const endHeight = `${this.summary.offsetHeight + borderHeight}px`;

    this.details.style.overflow = 'hidden';
    this.panel.style.opacity = '0';

    if (this.animation) this.animation.cancel();

    this.animation = this.details.animate(
      { height: [startHeight, endHeight] },
      { duration: DURATION, easing: EASING },
    );
    this.animation.onfinish = () => this.onAnimationFinish(false);
    this.animation.oncancel = () => { this.isClosing = false; };
  }

  onAnimationFinish(open) {
    this.details.open = open;
    this.animation = null;
    this.isClosing = false;
    this.isExpanding = false;
    this.details.style.height = '';
    this.details.style.overflow = '';
    this.panel.style.opacity = '';
  }
}

const initAccordionCanut = () => {
  const groups = new Map();

  document.querySelectorAll('.accordion-canut-item').forEach((details) => {
    const instance = new AnimatedAccordion(details);
    const groupName = details.dataset.accordionGroup;

    if (!groupName) return;

    if (!groups.has(groupName)) groups.set(groupName, []);
    groups.get(groupName).push(instance);
  });

  groups.forEach((group) => {
    group.forEach((instance) => { instance.group = group; });
  });
};

export default initAccordionCanut;
