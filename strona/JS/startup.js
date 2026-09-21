import { render } from './AJAX/render.js';
import { state, getProfileDetails, initCourts } from './state.js';
import { checkAuth, getCookie } from './AJAX/helpers.js';

export async function initializeApp() {
  await initCourts();
  const savedEmail = getCookie('email');
  const savedSessionID = getCookie('PHPSESSID') || '';
  await getProfileDetails(savedEmail);

  const urlParams = new URLSearchParams(window.location.search);
  const verifyStatus = urlParams.get('verify_status');
  const msg = urlParams.get('msg');

  if (verifyStatus) {
    if (verifyStatus === 'success') {
      state.auth.view = 'login';
      state.auth.error = null;
      setTimeout(() => {
        alert(msg || 'Konto zostało aktywowane! Możesz się zalogować.');
      }, 200);
    } else if (verifyStatus === 'error') {
      state.auth.view = 'login';
      state.auth.error = msg || 'Wystąpił błąd podczas weryfikacji konta.';
    }
    window.history.replaceState({}, document.title, window.location.pathname);
  }

  if (savedEmail) {
    try {
      const authResult = await checkAuth(savedSessionID, savedEmail);
      
      if (authResult && authResult.success) {
        state.auth.loggedIn = true;
        state.auth.user = { email: savedEmail };
      } else {
        console.warn("[DEBUG startup] Sesja odrzucona przez serwer. Czyszczę ciastko.");
        document.cookie = "email=; path=/; max-age=0";
      }
    } catch (err) {
      console.error('[DEBUG startup] Krytyczny błąd weryfikacji sesji:', err);
      document.cookie = "email=; path=/; max-age=0";
    }
  }

  render();
}