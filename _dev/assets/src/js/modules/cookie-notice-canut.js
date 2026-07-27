/**
 * Site-wide cookie notice (.cookie-notice-canut, footer.php's render_cookie_notice()).
 * Starts `hidden` in the server-rendered markup - shown here only if the
 * visitor hasn't already accepted, checked via document.cookie rather than
 * server-side so this stays correct under page caching (see
 * inc/hooks/cookie-notice.php's own docblock for why). Accepting sets a
 * 1-year cookie and hides the bar; nothing else on the page reacts to it.
 */
const COOKIE_NAME = 'canut_cookie_consent';
const COOKIE_MAX_AGE_DAYS = 365;

const hasAcceptedCookies = () => document.cookie
  .split(';')
  .some((cookie) => cookie.trim().startsWith(`${COOKIE_NAME}=`));

const acceptCookies = () => {
  const maxAge = COOKIE_MAX_AGE_DAYS * 24 * 60 * 60;
  document.cookie = `${COOKIE_NAME}=1; max-age=${maxAge}; path=/; SameSite=Lax`;
};

const initCookieNoticeCanut = () => {
  const notice = document.querySelector('[data-cookie-notice]');
  const acceptButton = document.querySelector('[data-cookie-notice-accept]');

  if (!notice || !acceptButton || hasAcceptedCookies()) {
    return;
  }

  notice.hidden = false;

  acceptButton.addEventListener('click', () => {
    acceptCookies();
    notice.hidden = true;
  });
};

export default initCookieNoticeCanut;
