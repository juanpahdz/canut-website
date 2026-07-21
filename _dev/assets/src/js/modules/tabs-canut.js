/**
 * Generic CANUT tabs component (see sass/components/_tabs-canut.scss).
 * Scope markup with `data-tabs-canut` on the wrapper, `data-tabs-canut-trigger`
 * on each `.tabs-canut-trigger` and `data-tabs-canut-panel` on each
 * `.tabs-canut-panel`, both sharing the same value to pair them up.
 */
const initTabsCanut = () => {
  const groups = document.querySelectorAll('[data-tabs-canut]');

  groups.forEach((group) => {
    const triggers = group.querySelectorAll('.tabs-canut-trigger');
    const panels = group.querySelectorAll('.tabs-canut-panel');

    triggers.forEach((trigger) => {
      trigger.addEventListener('click', () => {
        const { tabsCanutTrigger: target } = trigger.dataset;

        triggers.forEach((item) => item.classList.toggle('is-active', item === trigger));
        panels.forEach((panel) => panel.classList.toggle('is-active', panel.dataset.tabsCanutPanel === target));
      });
    });
  });
};

export default initTabsCanut;
