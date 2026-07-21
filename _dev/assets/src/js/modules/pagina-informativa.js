/**
 * "Página informativa" table of contents: highlights the link for whichever
 * numbered section is currently in view, so the sidebar always shows where
 * the reader is (see .informational-page-toc-link.is-active).
 */
const initPaginaInformativaToc = () => {
  const links = document.querySelectorAll('.informational-page-toc-link');

  if (!links.length) return;

  const sections = [...links]
    .map((link) => document.getElementById(link.getAttribute('href').slice(1)))
    .filter(Boolean);

  const setActiveLink = (id) => {
    links.forEach((link) => {
      link.classList.toggle('is-active', link.getAttribute('href') === `#${id}`);
    });
  };

  // Highlight the first section by default, before the reader has scrolled
  // far enough for the observer below to report anything as intersecting.
  setActiveLink(sections[0]?.id);

  const observer = new IntersectionObserver((entries) => {
    const visible = entries.find((entry) => entry.isIntersecting);

    if (visible) setActiveLink(visible.target.id);
  }, {
    // Treat a section as "current" once it's crossed into the top third of
    // the viewport, so the highlight changes before the reader scrolls past it.
    rootMargin: '0px 0px -70% 0px',
  });

  sections.forEach((section) => observer.observe(section));
};

export default initPaginaInformativaToc;
