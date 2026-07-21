/**
 * "Familia CANUT" gallery strip (product page): most browsers besides Safari
 * can't decode QuickTime/HEVC `.mov` clips inside a <video> element, which
 * would otherwise leave that slot blank. When a video's source can't be
 * played, swap it for its own `poster` image instead.
 */
const initGalleryStripVideoFallback = () => {
  const videos = document.querySelectorAll('[data-gallery-strip-video]');

  videos.forEach((video) => {
    const showFallbackImage = () => {
      const image = document.createElement('img');
      image.src = video.getAttribute('poster');
      image.alt = '';
      image.loading = 'lazy';
      video.replaceWith(image);
    };

    // By the time this runs the browser has already parsed the <source>
    // and may have already rejected it (e.g. a `type` it doesn't support),
    // so check the persisted state first instead of only listening forward.
    if (video.error || video.networkState === video.NETWORK_NO_SOURCE) {
      showFallbackImage();
      return;
    }

    video.addEventListener('error', showFallbackImage, { once: true });
  });
};

export default initGalleryStripVideoFallback;
