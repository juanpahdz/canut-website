/**
 * Single product gallery.
 *
 * Desktop: clicking a thumbnail shows that slide (thumbs/slides toggle
 * `.is-active` via CSS, see views/_product.scss).
 * Mobile: the track itself is a native horizontal scroll-snap carousel
 * (swipes like Instagram, no library needed); dots below stay in sync with
 * whichever slide is scrolled into view, and clicking a dot scrolls there.
 * Both: clicking a slide opens it full-size in a shared lightbox overlay
 * (video slides are excluded - they play in place instead).
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
      slides.forEach((slide, i) => {
        slide.classList.toggle('is-active', i === index);

        // Pause any playing video left behind on a slide that's no longer
        // the active one, so it doesn't keep running (and making noise)
        // off-screen once another slide is shown.
        if (i !== index) slide.querySelector('video')?.pause();
      });
      thumbs.forEach((thumb, i) => thumb.classList.toggle('is-active', i === index));
      dots.forEach((dot, i) => dot.classList.toggle('is-active', i === index));

      // Keep the active thumb in view in its own scrollable rail (mobile:
      // horizontal strip, desktop: vertical column) whenever it changes,
      // whether from a thumb/dot click below or a swipe on the main track
      // (via the IntersectionObserver further down) - so a gallery with
      // more thumbs than fit on screen never settles mid-thumb.
      thumbs[index]?.scrollIntoView({ behavior: 'smooth', inline: 'center', block: 'nearest' });
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

    // Prev/next arrows over the main slide (see .product-gallery-nav) - just
    // another way to reach goToSlide, wrapping around at either end like a
    // regular slider.
    const mainPrev = gallery.querySelector('[data-product-gallery-prev]');
    const mainNext = gallery.querySelector('[data-product-gallery-next]');
    const activeIndex = () => slides.findIndex((slide) => slide.classList.contains('is-active'));

    mainPrev?.addEventListener('click', () => {
      goToSlide((activeIndex() - 1 + slides.length) % slides.length);
    });

    mainNext?.addEventListener('click', () => {
      goToSlide((activeIndex() + 1) % slides.length);
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

    // Thumbnail rail scroll arrows (desktop only - see views/_product.scss):
    // the rail scrolls internally with a fixed height instead of growing
    // past the main image and pushing the rest of the page down when there
    // are more thumbs than fit on screen.
    const thumbsRail = gallery.querySelector('[data-product-gallery-thumbs]');
    const thumbsWrap = gallery.querySelector('[data-product-gallery-thumbs-wrap]');
    const thumbsPrev = gallery.querySelector('[data-product-gallery-thumbs-prev]');
    const thumbsNext = gallery.querySelector('[data-product-gallery-thumbs-next]');

    if (thumbsRail && thumbsWrap && thumbsPrev && thumbsNext) {
      const updateThumbsArrows = () => {
        const { scrollTop, scrollHeight, clientHeight } = thumbsRail;
        const hasOverflow = scrollHeight > clientHeight + 1;

        thumbsWrap.classList.toggle('has-overflow', hasOverflow);
        thumbsPrev.disabled = scrollTop <= 0;
        thumbsNext.disabled = scrollTop + clientHeight >= scrollHeight - 1;
      };

      thumbsPrev.addEventListener('click', () => {
        thumbsRail.scrollBy({ top: -thumbsRail.clientHeight * 0.8, behavior: 'smooth' });
      });

      thumbsNext.addEventListener('click', () => {
        thumbsRail.scrollBy({ top: thumbsRail.clientHeight * 0.8, behavior: 'smooth' });
      });

      thumbsRail.addEventListener('scroll', updateThumbsArrows);
      window.addEventListener('resize', updateThumbsArrows);
      updateThumbsArrows();
    }
  });

  const lightbox = document.querySelector('[data-product-gallery-lightbox]');
  const lightboxImage = lightbox?.querySelector('[data-product-gallery-lightbox-image]');
  const lightboxClose = lightbox?.querySelector('[data-product-gallery-lightbox-close]');
  const lightboxPrev = lightbox?.querySelector('[data-product-gallery-lightbox-prev]');
  const lightboxNext = lightbox?.querySelector('[data-product-gallery-lightbox-next]');

  if (!lightbox || !lightboxImage) return;

  // The other zoomable (non-video) slides from whichever gallery is open,
  // so prev/next can step through them - repopulated on each open in case
  // the page ever has more than one gallery.
  let lightboxSlides = [];
  let lightboxIndex = 0;

  const showLightboxSlide = (index) => {
    const slide = lightboxSlides[index];

    if (!slide) return;

    lightboxIndex = index;

    const image = slide.querySelector('img');

    lightboxImage.src = slide.dataset.zoom || image.src;
    lightboxImage.alt = image.alt;

    const hasMultiple = lightboxSlides.length > 1;

    if (lightboxPrev) lightboxPrev.disabled = !hasMultiple;
    if (lightboxNext) lightboxNext.disabled = !hasMultiple;
  };

  const closeLightbox = () => {
    lightbox.hidden = true;
    lightboxImage.src = '';
  };

  document.querySelectorAll('[data-product-gallery-slide]:not(.is-video)').forEach((slide) => {
    slide.addEventListener('click', () => {
      lightboxSlides = Array.from(
        slide.closest('[data-product-gallery]')?.querySelectorAll('[data-product-gallery-slide]:not(.is-video)') || [slide],
      );
      showLightboxSlide(lightboxSlides.indexOf(slide));
      lightbox.hidden = false;
    });
  });

  lightboxPrev?.addEventListener('click', () => {
    showLightboxSlide((lightboxIndex - 1 + lightboxSlides.length) % lightboxSlides.length);
  });

  lightboxNext?.addEventListener('click', () => {
    showLightboxSlide((lightboxIndex + 1) % lightboxSlides.length);
  });

  lightboxClose?.addEventListener('click', closeLightbox);

  lightbox.addEventListener('click', (event) => {
    if (event.target === lightbox) closeLightbox();
  });

  document.addEventListener('keydown', (event) => {
    if (lightbox.hidden) return;

    if (event.key === 'Escape') closeLightbox();
    if (event.key === 'ArrowLeft') showLightboxSlide((lightboxIndex - 1 + lightboxSlides.length) % lightboxSlides.length);
    if (event.key === 'ArrowRight') showLightboxSlide((lightboxIndex + 1) % lightboxSlides.length);
  });
};

export default initProductGallery;
