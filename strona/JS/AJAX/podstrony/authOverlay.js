import {state} from "../../state.js";

export function renderAuthOverlay(){
  const mode = state.auth.view; // może być 'login', 'register', 'forgot', 'reset-code'
  const err = state.auth.error;
  const requires2FA = state.auth.requires2FA;
  const isLoading = state.auth.loading;

  // Nagłówki i opisy w zależności od trybu
  let title = 'Zaloguj się';
  let subtitle = state.auth.resumeToPayment ? 'Zaloguj się lub załóż konto, aby dokończyć rezerwację.' : 'Dostęp do rezerwacji kortów SETPOINT.';

  if (requires2FA) {
    title = 'Weryfikacja dwuetapowa';
    subtitle = 'Wprowadź 6-cyfrowy kod wysłany na Twój adres e-mail.';
  } else if (mode === 'register') {
    title = 'Załóż konto';
  } else if (mode === 'forgot') {
    title = 'Resetowanie hasła';
    subtitle = 'Wprowadź swój e-mail, aby otrzymać kod resetujący.';
  } else if (mode === 'reset-code') {
    title = 'Nowe hasło';
    subtitle = 'Wprowadź kod z e-maila oraz nowe hasło.';
  }

  return `
    <div class="auth-overlay" role="dialog" aria-modal="true">
      <div class="auth-card">
        <button class="auth-close" data-action="close-auth" aria-label="Zamknij">×</button>
        <h2 class="auth-title">${title}</h2>
        <p class="auth-sub">${subtitle}</p>

        ${(!requires2FA && mode !== 'forgot' && mode !== 'reset-code') ? `
          <div class="auth-tabs">
            <button class="auth-tab ${mode!=='register'?'active':''}" data-action="switch-auth" data-mode="login">Logowanie</button>
            <button class="auth-tab ${mode==='register'?'active':''}" data-action="switch-auth" data-mode="register">Rejestracja</button>
          </div>
        ` : ''}

        ${err ? `<div class="auth-error">${err}</div>` : ''}

        ${requires2FA ? `
          <div class="auth-field">
            <label for="auth2FACodeInput">Kod weryfikacyjny</label>
            <input id="auth2FACodeInput" type="text" maxlength="6" placeholder="123456" class="auth-code-input">
          </div>
          <button class="auth-submit" data-action="verify-login-2fa">Potwierdź kod</button>
          <p class="auth-note"><button class="auth-link-btn" data-action="cancel-login-2fa">Wróć do logowania</button></p>
        ` : mode === 'forgot' ? `
          <div class="auth-field">
            <label for="authForgotEmailInput">E-mail</label>
            <input id="authForgotEmailInput" type="email" autocomplete="email" value="${state.auth.email || ''}">
          </div>
          <button class="auth-submit ${isLoading ? 'loading' : ''}" data-action="send-forgot-code" ${isLoading ? 'disabled' : ''}>
            ${isLoading ? 'Wysyłanie...' : 'Wyślij kod resetujący'}
          </button>
          <p class="auth-note"><button class="auth-link-btn" data-action="switch-auth" data-mode="login">Wróć do logowania</button></p>
        ` : mode === 'reset-code' ? `
          <div class="auth-field">
            <label for="authResetCodeInput">Kod z e-maila</label>
            <input id="authResetCodeInput" type="text" maxlength="6" placeholder="123456">
          </div>
          <div class="auth-field">
            <label for="authNewPasswordInput">Nowe hasło</label>
            <input id="authNewPasswordInput" type="password" autocomplete="new-password">
          </div>
          <button class="auth-submit ${isLoading ? 'loading' : ''}" data-action="submit-new-password" ${isLoading ? 'disabled' : ''}>
            ${isLoading ? 'Zapisywanie...' : 'Zmień hasło'}
          </button>
          <p class="auth-note"><button class="auth-link-btn" data-action="switch-auth" data-mode="login">Wróć do logowania</button></p>
        ` : mode === 'register' ? `
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
          <button class="auth-submit ${isLoading ? 'loading' : ''}" data-action="do-register" ${isLoading ? 'disabled' : ''}>
            ${isLoading ? 'Rejestracja...' : 'Załóż konto'}
          </button>
          <p class="auth-note">Masz już konto? <button class="auth-link-btn" data-action="switch-auth" data-mode="login">Zaloguj się</button></p>
        ` : `
          <div class="auth-field">
            <label for="authEmailInput">E-mail</label>
            <input id="authEmailInput" type="text" autocomplete="username" value="${state.auth.email || ''}">
          </div>
          <div class="auth-field">
            <label for="authPasswordInput">Hasło</label>
            <input id="authPasswordInput" type="password" autocomplete="current-password" value="${state.auth.password || ''}">
          </div>
          <div class="auth-remember-container" style="margin: 10px 0; font-size: 14px; display: flex; align-items: center; justify-content: space-between;">
            <div style="display: flex; align-items: center; gap: 8px;">
              <input type="checkbox" id="authRememberCheckbox" style="cursor: pointer;" ${state.auth.rememberMe ? 'checked' : ''}>
              <label for="authRememberCheckbox" style="cursor: pointer;">Zapamiętaj mnie</label>
            </div>
            <button class="auth-link-btn" data-action="switch-auth" data-mode="forgot" style="font-size: 13px;">Zapomniałeś hasło?</button>
          </div>
          <button class="auth-submit ${isLoading ? 'loading' : ''}" data-action="do-login" ${isLoading ? 'disabled' : ''}>
            ${isLoading ? 'Logowanie...' : 'Zaloguj się'}
          </button>
          <p class="auth-note">Nie masz konta? <button class="auth-link-btn" data-action="switch-auth" data-mode="register">Zarejestruj się</button></p>
        `}
      </div>
    </div>
  `;
}