import {state} from "../../state.js";

export function renderAuthOverlay(){
  const mode = state.auth.view;
  const err = state.auth.error;

  return `
    <div class="auth-overlay" role="dialog" aria-modal="true">
      <div class="auth-card">
        <button class="auth-close" data-action="close-auth" aria-label="Zamknij">×</button>
        <h2 class="auth-title">${mode==='register' ? 'Załóż konto' : 'Zaloguj się'}</h2>
        <p class="auth-sub">${state.auth.resumeToPayment ? 'Zaloguj się lub załóż konto, aby dokończyć rezerwację.' : 'Dostęp do rezerwacji kortów SETPOINT.'}</p>

        <div class="auth-tabs">
          <button class="auth-tab ${mode!=='register'?'active':''}" data-action="switch-auth" data-mode="login">Logowanie</button>
          <button class="auth-tab ${mode==='register'?'active':''}" data-action="switch-auth" data-mode="register">Rejestracja</button>
        </div>

        ${err ? `<div class="auth-error">${err}</div>` : ''}

        ${mode==='register' ? `
          <div class="auth-field">
            <label for="authRegEmailInput">E-mail</label>
            <input id="authRegEmailInput" type="email" autocomplete="email">
          </div>
          <div class="auth-field">
            <label for="authRegPasswordInput">Hasło</label>
            <input id="authRegPasswordInput" type="password" autocomplete="new-password">
          </div>
          <div class="auth-field">
            <label for="authRegConfirmInput">Powtórz hasło</label>
            <input id="authRegConfirmInput" type="password" autocomplete="new-password">
          </div>
          <button class="auth-submit" data-action="do-register">Zarejestruj się</button>
          <p class="auth-note">Masz już konto? <button class="auth-link-btn" data-action="switch-auth" data-mode="login">Zaloguj się</button></p>
        ` : `
          <div class="auth-field">
            <label for="authEmailInput">E-mail</label>
            <input id="authEmailInput" type="text" autocomplete="username">
          </div>
          <div class="auth-field">
            <label for="authPasswordInput">Hasło</label>
            <input id="authPasswordInput" type="password" autocomplete="current-password">
          </div>
          <div class="auth-hint">Konto testowe: login <strong>admin</strong>, hasło <strong>admin</strong>.</div>
          <button class="auth-submit" data-action="do-login">Zaloguj się</button>
          <p class="auth-note">Nie masz konta? <button class="auth-link-btn" data-action="switch-auth" data-mode="register">Zarejestruj się</button></p>
        `}
      </div>
    </div>
  `;
}