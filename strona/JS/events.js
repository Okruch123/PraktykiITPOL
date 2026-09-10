import { handleRegister } from './AJAX/actions.js';
import { state } from './state.js';
import { render } from './AJAX/render.js';

document.addEventListener('click', (e) => {
  const actionBtn = e.target.closest('[data-action]');
  if (!actionBtn) return;

  const action = actionBtn.dataset.action;

  if (action === 'do-register') {
    e.preventDefault();
    handleRegister(e);
  }

  if (action === 'switch-auth') {
    e.preventDefault();
    state.auth.view = actionBtn.dataset.mode;
    state.auth.error = null;
    render();
  }

  if (action === 'close-auth') {
    e.preventDefault();
    state.auth.view = null;
    state.auth.error = null;
    render();
  }
});