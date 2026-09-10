import { render } from './AJAX/render.js';
import { state } from './state.js';
import './AJAX/actions.js';
import './events.js';

// Obsługa parametrów weryfikacji z adresu URL po powrocie z maila
const urlParams = new URLSearchParams(window.location.search);
const verifyStatus = urlParams.get('verify_status');
const msg = urlParams.get('msg');

if (verifyStatus) {
  if (verifyStatus === 'success') {
    // Automatycznie otwiera okno logowania
    state.auth.view = 'login';
    state.auth.error = null;
    
    // Wyświetlamy komunikat o sukcesie
    setTimeout(() => {
      alert(msg || 'Konto zostało aktywowane! Możesz się zalogować.');
    }, 200);
  } else if (verifyStatus === 'error') {
    state.auth.view = 'login';
    state.auth.error = msg || 'Wystąpił błąd podczas weryfikacji konta.';
  }
  
  // Usuwa parametry verify_status z paska adresu, żeby nie mieszały przy odświeżaniu
  window.history.replaceState({}, document.title, window.location.pathname);
}

render();