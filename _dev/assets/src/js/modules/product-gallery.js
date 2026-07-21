/**
 * Single product gallery.
 *
 * Desktop: clicking a thumbnail shows that slide (thumbs/slides toggle
 * `.is-active` via CSS, see views/_product.scss).
 * Mobile: the track itself is a native horizontal scroll-snap carousel
 * (swipes like Instagram, no library needed); dots below stay in sync with
 * whichever slide is scrolled into view, and clicking a dot scrolls there.
 * Both: clicking a slide opens it full-size in a shared lightbox overlay.
 */
const initProductGallery = () => {
  const galleries = document.querySelectorAll('[data-product-gallery]');

  galleries.forEach((gallery) => {
    const track = gallery.querySelector('[data-product-gallery-track]');
    const slides = Array.from(gallery.querySelectorAll('[data-product-gallery-slide]'));
    const thumbs = Array.from(gallery.querySelectorAll('[data-product-gallery-thumb]'));
    const dots = Array.from(gallery.querySelectorAll('[data-product-gallery-dot]'));

    if (!track || !slides.length) return;

    const setActiveIndex = (index) => {
      slides.forEach((slide, i) => slide.classList.toggle('is-active', i === index));
      thumbs.forEach((thumb, i) => thumb.classList.toggle('is-active', i === index));
      dots.forEach((dot, i) => dot.classList.toggle('is-active', i === index));
    };

    // Scrolling the track is what actually switches slides on mobile (the
    // IntersectionObserver below then calls setActiveIndex to match); on
    // desktop the track doesn't scroll, so this is a harmless no-op there
    // and setActiveIndex alone is what swaps the visible slide.
    const goToSlide = (index) => {
      setActiveIndex(index);
      slides[index].scrollIntoView({ behavior: 'smooth', inline: 'start', block: 'nearest' });
    };

    thumbs.forEach((thumb, index) => {
      thumb.addEventListener('click', () => goToSlide(index));
    });

    dots.forEach((dot, index) => {
      dot.addEventListener('click', () => goToSlide(index));
    });

    if (dots.length) {
      const observer = new IntersectionObserver(
        (entries) => {
          entries.forEach((entry) => {
            if (entry.isIntersecting) {
              setActiveIndex(slides.indexOf(entry.target));
            }
          });
        },
        { root: track, threshold: 0.6 },
      );

      slides.forEach((slide) => observer.observe(slide));
    }
  });

  const lightbox = document.querySelector('[data-product-gallery-lightbox]');
  const lightboxImage = lightbox?.querySelector('[data-product-gallery-lightbox-image]');
  const lightboxClose = lightbox?.querySelector('[data-product-gallery-lightbox-close]');

  if (!lightbox || !lightboxImage) return;

  const closeLightbox = () => {
    lightbox.hidden = true;
    lightboxImage.src = '';
  };

  document.querySelectorAll('[data-product-gallery-slide]').forEach((slide) => {
    slide.addEventListener('click', () => {
      const image = slide.querySelector('img');

      lightboxImage.src = slide.dataset.zoom || image.src;
      lightboxImage.alt = image.alt;
      lightbox.hidden = false;
    });
  });

  lightboxClose?.addEventListener('click', closeLightbox);

  lightbox.addEventListener('click', (event) => {
    if (event.target === lightbox) closeLightbox();
  });

  document.addEventListener('keydown', (event) => {
    if (event.key === 'Escape' && !lightbox.hidden) closeLightbox();
  });
};

export default initProductGallery;
