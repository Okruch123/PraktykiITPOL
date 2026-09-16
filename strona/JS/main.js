import { render } from './AJAX/render.js';
import { state } from './state.js';
import './AJAX/actions.js';
import './events.js';

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

render();