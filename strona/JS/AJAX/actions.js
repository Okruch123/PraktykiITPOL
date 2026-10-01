import { state, fetchBookedHoursFromServer } from '../state.js';

import {
  DATES,
  TODAY,
  pad,
  toDateStr
} from '../utils.js';

import { render } from './render.js';
import { checkAuth, getCookie } from './helpers.js';

let overlayTimer = null;

function goToReservationDetail(id) {
  state.overlay = null;
  state.tab = 'rezerwacje';
  state.viewingReservationId = id;
  state.selectedCourtId = null;
  state.pick = { from: null, to: null };
  state.showReturnForm = false;
  state.returnFormError = null;
  render();
}

function showToast(msg) {
  state.toast = msg;
  render();

  setTimeout(() => {
    state.toast = null;
    render();
  }, 3200);
}

async function goToPaymentFlow() {
  const courts = await state.courts;

  const court = courts.find(
    c => c.id === state.selectedCourtId
  );

  const from = state.pick.from;
  const to = state.pick.to;

  state.pendingPayment = {
    courtId: court.id,
    dateIndex: state.selectedDateIndex,
    from,
    to,
    price: (to - from) * court.price
  };

  state.selectedBank = null;
}

document.getElementById('app').addEventListener('click', async (e) => {

  const btn = e.target.closest('[data-action]');
  if (!btn) return;

  const action = btn.dataset.action;

  if(action === 'pick-from' || action === 'pick-to'){
      return;
  }

  if (action === 'set-tab') {

    state.tab = btn.dataset.tab;

    if (state.tab === 'korty') {
      state.selectedCourtId = null;
    }

    state.pendingPayment = null;
    state.selectedBank = null;
    state.viewingReservationId = null;
    state.showReturnForm = false;
    state.returnFormError = null;
  }

  else if (action === 'open-court') {

    state.selectedCourtId = Number(btn.dataset.id);
    state.selectedDateIndex = 0;
    state.pick = {
      from: null,
      to: null
    };

    const dateStr = toDateStr(
      DATES[state.selectedDateIndex]
    );

    state.bookedHours =
      await fetchBookedHoursFromServer(
        state.selectedCourtId,
        dateStr
      );
  }

  else if (action === 'set-date') {

    state.selectedDateIndex =
      Number(btn.dataset.index);

    state.pick = {
      from: null,
      to: null
    };

    const dateStr = toDateStr(
      DATES[state.selectedDateIndex]
    );

    state.bookedHours =
      await fetchBookedHoursFromServer(
        state.selectedCourtId,
        dateStr
      );
  }

  else if (action === 'back-to-korty') {

    state.selectedCourtId = null;

    state.pick = {
      from: null,
      to: null
    };
  }

  else if (action === 'quick-pick') {

    const h = Number(btn.dataset.hour);

    state.pick.from = h;
    state.pick.to = h + 1;
  }

  else if (action === 'go-to-payment') {

    if (!state.auth.loggedIn) {

      state.auth.view = 'login';
      state.auth.error = null;
      state.auth.resumeToPayment = true;

    } else {

      await goToPaymentFlow();
    }
  }

  else if (action === 'cancel-payment') {

    state.pendingPayment = null;
    state.selectedBank = null;
  }

  else if (action === 'select-bank') {

    state.selectedBank = btn.dataset.bank;
  }

  else if (action === 'pay') {

    const pb = state.pendingPayment;

    const court =
      state.courts.find(
        c => c.id === pb.courtId
      );

    const bank = state.selectedBank;

    state.overlay = {
      stage: 'processing',
      courtName: court.name,
      from: pb.from,
      to: pb.to,
      price: pb.price
    };

    render();

    clearTimeout(overlayTimer);

    overlayTimer = setTimeout(() => {

      const id = 'r' + Date.now();

      state.reservations.push({
        id,
        courtId: pb.courtId,
        dateStr: toDateStr(
          DATES[pb.dateIndex]
        ),
        dateIndex: pb.dateIndex,
        startHour: pb.from,
        endHour: pb.to,
        price: pb.price,
        bank
      });

      state.transactions.unshift({
        id: 't' + Date.now(),
        dateStr: toDateStr(TODAY),
        desc:
          `Rezerwacja — ${court.name}, ` +
          `${pad(pb.from)}:00–${pad(pb.to)}:00`,
        amount: pb.price,
        status: 'done'
      });

      state.pendingPayment = null;
      state.selectedBank = null;

      state.overlay = {
        stage: 'success',
        courtName: court.name,
        from: pb.from,
        to: pb.to,
        price: pb.price,
        reservationId: id
      };

      render();

      overlayTimer = setTimeout(
        () => goToReservationDetail(id),
        1600
      );

    }, 1500);

    return;
  }

  else if (action === 'skip-to-details') {

    clearTimeout(overlayTimer);

    goToReservationDetail(
      state.overlay.reservationId
    );

    return;
  }

  else if (action === 'view-details') {

    state.viewingReservationId =
      btn.dataset.id;

    state.showReturnForm = false;
    state.returnFormError = null;
  }

  else if (action === 'back-to-list') {

    state.viewingReservationId = null;
    state.showReturnForm = false;
    state.returnFormError = null;
  }

  else if (action === 'goto-rezerwacje') {

    state.tab = 'rezerwacje';
  }

  else if (action === 'open-return') {

    state.viewingReservationId =
      btn.dataset.id;

    state.showReturnForm = true;
    state.returnFormError = null;
  }

  else if (action === 'request-return') {

    state.showReturnForm = true;
    state.returnFormError = null;
  }

  else if (action === 'cancel-return-form') {

    state.showReturnForm = false;
    state.returnFormError = null;
  }

  else if (action === 'submit-return') {

    const reason =
      document.getElementById(
        'returnReasonSelect'
      ).value;

    const note =
      document.getElementById(
        'returnNoteInput'
      ).value.trim();

    if (!reason) {

      state.returnFormError =
        'Wybierz powód zwrotu.';

    } else {

      const r =
        state.reservations.find(
          x => x.id === btn.dataset.id
        );

      if (r) {

        r.returnRequest = {
          reason,
          note,
          status: 'pending',
          requestedAt:
            toDateStr(new Date())
        };
      }

      state.showReturnForm = false;
      state.returnFormError = null;

      showToast(
        'Prośba o zwrot została wysłana do obsługi.'
      );

      return;
    }
  }

  else if (action === 'approve-return') {

    const r =
      state.reservations.find(
        x => x.id === btn.dataset.id
      );

    if (r) {

      const court =
        state.courts.find(
          c => c.id === r.courtId
        );

      state.reservations =
        state.reservations.filter(
          x => x.id !== r.id
        );

      if (
        state.viewingReservationId === r.id
      ) {
        state.viewingReservationId = null;
      }

      state.transactions.unshift({
        id: 't' + Date.now(),
        dateStr: toDateStr(TODAY),
        desc:
          `Zwrot zatwierdzony — ${court.name}, ` +
          `${pad(r.startHour)}:00–${pad(r.endHour)}:00`,
        amount: r.price,
        status: 'cancelled'
      });

      showToast(
        'Zwrot został zatwierdzony.'
      );

      return;
    }
  }

  else if (action === 'reject-return') {

    const r =
      state.reservations.find(
        x => x.id === btn.dataset.id
      );

    if (r && r.returnRequest) {

      r.returnRequest.status = 'rejected';

      showToast(
        'Prośba o zwrot została odrzucona.'
      );

      return;
    }
  }

  else if (action === 'open-auth') {

    state.auth.view =
      btn.dataset.mode || 'login';

    state.auth.error = null;
  }

  else if (action === 'switch-auth') {

    state.auth.view =
      btn.dataset.mode;

    state.auth.error = null;
  }

  else if (action === 'close-auth') {

    state.auth.view = null;
    state.auth.error = null;
    state.auth.resumeToPayment = false;
  }

  else if (action === 'verify-login-2fa') {

    const input =
      document.getElementById(
        'auth2FACodeInput'
      );

    const code =
      input ? input.value.trim() : '';

    if (!code || code.length !== 6) {

      state.auth.error =
        'Wprowadź poprawny 6-cyfrowy kod.';

      render();

      return;
    }

    try {

      const res = await fetch(
        'PHP/login/verify_login_2fa.php',
        {
          method: 'POST',
          headers: {
            'Content-Type':
              'application/json'
          },
          body: JSON.stringify({
            code
          })
        }
      );

      const data = await res.json();

      if (data.success) {

        state.auth.requires2FA = false;
        state.auth.loggedIn = true;

        state.auth.user = {
          id: data.user.id,
          email: data.user.email,
          is_admin: data.user.is_admin
        };

        document.cookie = "email=" + data.user.email + "; max-age=" + (30 * 24 * 60 * 60) + "; path=/;";

        state.auth.view = null;
        state.auth.error = null;

        if (typeof checkAuth === 'function') {
          const sessionID = getCookie('PHPSESSID') || '';
          await checkAuth(sessionID, data.user.email);
        }

        showToast(
          'Zalogowano pomyślnie.'
        );

        render();

      } else {

        state.auth.error =
          data.message;

        render();
      }

    } catch (err) {

      console.error(
        '[DEBUG] Błąd weryfikacji 2FA:',
        err
      );

      state.auth.error =
        'Błąd serwera. Spróbuj ponownie.';

      render();
    }

    return;
  }

  else if (action === 'cancel-login-2fa') {

    state.auth.requires2FA = false;
    state.auth.error = null;

    render();

    return;
  }

  else if (action === 'send-forgot-code') {
    const emailInput = document.getElementById('authForgotEmailInput');
    const email = emailInput ? emailInput.value.trim() : '';

    if (!email) {
      state.auth.error = 'Wprowadź adres e-mail.';
      render();
      return;
    }

    state.auth.loading = true;
    state.auth.error = null;
    state.auth.email = email;
    render();

    try {
      const res = await fetch('PHP/login/forgot_password.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ email })
      });
      const data = await res.json();
      state.auth.loading = false;

      if (data.success) {
        state.auth.view = 'reset-code';
        state.auth.error = null;
        showToast('Kod resetujący został wysłany na e-mail.');
      } else {
        state.auth.error = data.message;
      }
    } catch (err) {
      state.auth.loading = false;
      state.auth.error = 'Błąd serwera. Spróbuj ponownie.';
    }
    render();
    return;
  }

  else if (action === 'submit-new-password') {
    const codeInput = document.getElementById('authResetCodeInput');
    const passInput = document.getElementById('authNewPasswordInput');
    
    const code = codeInput ? codeInput.value.trim() : '';
    const password = passInput ? passInput.value : '';

    if (!code || !password) {
      state.auth.error = 'Wypełnij wszystkie pola.';
      render();
      return;
    }

    state.auth.loading = true;
    state.auth.error = null;
    render();

    try {
      const res = await fetch('PHP/login/reset_password.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ email: state.auth.email, code, password })
      });
      const data = await res.json();
      state.auth.loading = false;

      if (data.success) {
        state.auth.view = 'login';
        state.auth.error = 'Hasło zostało pomyślnie zmienione. Zaloguj się nowym hasłem.';
        showToast('Hasło zaktualizowane.');
      } else {
        state.auth.error = data.message;
      }
    } catch (err) {
      state.auth.loading = false;
      state.auth.error = 'Błąd serwera. Spróbuj ponownie.';
    }
    render();
    return;
  }

  else if (action === 'do-register') {

    const email =
      document.getElementById(
        'authRegEmailInput'
      ).value.trim();

    const login =
      document.getElementById(
        'authRegLoginInput'
      )?.value?.trim() || '';

    const password =
      document.getElementById(
        'authRegPasswordInput'
      ).value;

    const confirm =
      document.getElementById(
        'authRegConfirmInput'
      ).value;

    if (
      !email ||
      !password ||
      !confirm
    ) {

      state.auth.error =
        'Wypełnij wszystkie pola.';

    } else if (password !== confirm) {

      state.auth.error =
        'Hasła nie są identyczne.';

    } else {
    }
  }

  else if (action === 'logout') {

    try {
      await fetch('PHP/login/logout.php', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json'
        }
      });
    } catch (err) {
      console.error('[DEBUG] Błąd podczas wylogowywania na serwerze:', err);
    }

    state.auth.loggedIn = false;
    state.auth.user = null;

    document.cookie =
      "email=; path=/; max-age=0";

    document.cookie =
      "PHPSESSID=; path=/; max-age=0";

    showToast(
      'Wylogowano.'
    );

    render();

    return;
  }

  else if (action === 'disable-remember-me') {

    document.cookie =
      "email=; path=/; max-age=0";

    document.cookie =
      "PHPSESSID=; path=/; max-age=0";

    showToast(
      'Wyłączono automatyczne logowanie na tym urządzeniu.'
    );

    render();

    return;
  }

  else if (action === 'set-konto-sub') {

    state.kontoSub =
      btn.dataset.sub;

    state.editingProfile = false;
  }

  else if (action === 'edit-profile') {
    state.editingProfile = true;
    state.profileError = null;
  }

  else if (action === 'cancel-profile-edit') {
    state.editingProfile = false;
    state.profileError = null;
  }

  else if (action === 'save-profile') {
    const nameInput = document.querySelector('[data-field="name"]');
    const phoneInput = document.querySelector('[data-field="phone"]');
    
    const oldPasswordInput = document.getElementById('profileOldPassword');
    const newPasswordInput = document.getElementById('profileNewPassword');
    const confirmPasswordInput = document.getElementById('profileConfirmPassword');

    const name = nameInput ? nameInput.value.trim() : '';
    const phone = phoneInput ? phoneInput.value.trim() : '';
    const oldPassword = oldPasswordInput ? oldPasswordInput.value : '';
    const newPassword = newPasswordInput ? newPasswordInput.value : '';
    const confirmPassword = confirmPasswordInput ? confirmPasswordInput.value : '';

    if (newPassword && newPassword !== confirmPassword) {
      state.profileError = 'Nowe hasła nie są identyczne.';
      render();
      return;
    }

    try {
      const res = await fetch('PHP/profile/update_profile.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({
          name,
          phone,
          oldPassword,
          newPassword
        })
      });

      const data = await res.json();

      if (data.success) {
        state.profile.name = name;
        state.profile.phone = phone;
        state.editingProfile = false;
        state.profileError = null;
        showToast('Zmiany w profilu zostały zapisane.');
      } else {
        state.profileError = data.message || 'Wystąpił błąd podczas zapisu.';
      }
    } catch (err) {
      console.error('[DEBUG] Błąd zapisu profilu:', err);
      state.profileError = 'Błąd połączenia z serwerem.';
    }

    render();
    return;
  }

  render();
});

document.getElementById('app').addEventListener('change', (e) => {
    const el = e.target.closest('[data-action]');
    if(!el) return;

    const action = el.dataset.action;

    if(action === 'pick-from'){
        const v = el.value;

        state.pick.from = v === ''
            ? null
            : Number(v);

        state.pick.to = v === ''
            ? null
            : Number(v) + 1;
    }
    else if(action === 'pick-to'){
        const v = el.value;

        state.pick.to = v === ''
            ? null
            : Number(v);
    }

    render();
});


document.getElementById('app').addEventListener(
  'keydown',
  (e) => {

    if (e.key !== 'Enter') return;

    const id = e.target.id;

    if (
      id === 'authLoginInput' ||
      id === 'authPasswordInput'
    ) {

      e.preventDefault();

      document
        .querySelector(
          '[data-action="do-login"]'
        )
        ?.click();

    }

    else if (
      id === 'authEmailInput' ||
      id === 'authRegLoginInput' ||
      id === 'authRegPasswordInput' ||
      id === 'authRegConfirmInput'
    ) {

      e.preventDefault();

      document
        .querySelector(
          '[data-action="do-register"]'
        )
        ?.click();
    }
  }
);


export function checkP24Status() {

  const urlParams =
    new URLSearchParams(
      window.location.search
    );

  const status =
    urlParams.get('status');

  if (!status) return;

  const savedPayment =
    sessionStorage.getItem(
      'p24_pending_payment'
    );

  const pb =
    savedPayment
      ? JSON.parse(savedPayment)
      : null;

  sessionStorage.removeItem(
    'p24_pending_payment'
  );

  if (status === 'success') {

    const resId =
      'r' + Date.now();

    const courtName =
      pb?.courtName || 'Kort';

    if (pb) {

      state.reservations.push({

        id: resId,

        courtId: pb.courtId,

        dateStr:
          toDateStr(
            DATES[pb.dateIndex] ||
            TODAY
          ),

        dateIndex:
          pb.dateIndex || 0,

        startHour: pb.from,

        endHour: pb.to,

        price: pb.price
      });

      state.transactions.unshift({

        id:
          't' + Date.now(),

        dateStr:
          toDateStr(TODAY),

        desc:
          `Rezerwacja — ${courtName}, ` +
          `${pad(pb.from)}:00–${pad(pb.to)}:00`,

        amount: pb.price,

        status: 'done'
      });
    }

    state.overlay = {

      stage: 'success',

      courtName,

      reservationId: resId
    };

    setTimeout(
      () => {
        goToReservationDetail(state.reservations.at(0).id);
      },
      1800
    );

  }

  else if (
    status === 'error' ||
    status === 'fail'
  ) {

    state.overlay = {
      stage: 'error'
    };
  }

  window.history.replaceState(
    {},
    document.title,
    window.location.pathname
  );

  render();
}


export async function handleRegister(e) {

  const container =
    e
      ? e.target.closest('.auth-card')
      : document;

  const email =
    container?.querySelector(
      '#authRegEmailInput'
    )?.value?.trim()
    ||
    document.getElementById(
      'authRegEmailInput'
    )?.value?.trim();

  const password =
    container?.querySelector(
      '#authRegPasswordInput'
    )?.value
    ||
    document.getElementById(
      'authRegPasswordInput'
    )?.value;

  const confirmPassword =
    container?.querySelector(
      '#authRegConfirmInput'
    )?.value
    ||
    document.getElementById(
      'authRegConfirmInput'
    )?.value;

  if (!email || !password) {

    state.auth.error =
      'Wypełnij adres e-mail oraz hasło.';

    render();

    return;
  }

  try {

    const res = await fetch(
      'PHP/login/register.php',
      {
        method: 'POST',

        headers: {
          'Content-Type':
            'application/json'
        },

        body: JSON.stringify({

          email,

          password,

          confirmPassword,

          first_name: 'Użytkownik',

          surname: 'Brak',

          phone_number: ''
        })
      }
    );

    const rawText =
      await res.text();

    const data =
      JSON.parse(rawText);

    if (data.success) {

      state.auth.view = 'login';

      state.auth.error =
        data.message;

      state.user = null;

      render();

    } else {

      state.auth.error =
        data.message;

      render();
    }

  } catch (err) {

    console.error(
      '[DEBUG] Błąd przetworzenia odpowiedzi:',
      err
    );

    state.auth.error =
      'Błąd serwera. Spróbuj ponownie.';

    render();
  }
}


export async function handleLogin(e) {

  const container =
    e
      ? e.target.closest('.auth-card')
      : document;

  const emailInput =
    container?.querySelector(
      '#authEmailInput'
    )
    ||
    document.getElementById(
      'authEmailInput'
    );

  const passwordInput =
    container?.querySelector(
      '#authPasswordInput'
    )
    ||
    document.getElementById(
      'authPasswordInput'
    );

  const rememberCheckbox =
    container?.querySelector(
      '#authRememberCheckbox'
    )
    ||
    document.getElementById(
      'authRememberCheckbox'
    );

  const email =
    emailInput?.value?.trim() || '';

  const password =
    passwordInput?.value || '';

  const rememberMe =
    rememberCheckbox
      ? rememberCheckbox.checked
      : false;

  state.auth.email = email;
  state.auth.password = password;
  state.auth.rememberMe = rememberMe;

  if (!email || !password) {

    state.auth.error =
      'Wypełnij adres e-mail oraz hasło.';

    render();

    return;
  }

  state.auth.loading = true;
  state.auth.error = null;
  render();

  try {

    const res = await fetch(
      'PHP/login/login.php',
      {
        method: 'POST',

        headers: {
          'Content-Type':
            'application/json'
        },

        body: JSON.stringify({

          email,

          password
        })
      }
    );

    const rawText =
      await res.text();

    const data =
      JSON.parse(rawText);

    state.auth.loading = false;

    if (data.success) {

      if (data.requires_2fa) {

        state.auth.requires2FA = true;

        state.auth.error =
          data.message;

        render();

        return;
      }

      const maxAgeStr = rememberMe ? "; max-age=" + (30 * 24 * 60 * 60) : "";
      document.cookie = "email=" + data.user.email + maxAgeStr + "; path=/;";

      state.auth.error =
        data.message;

      state.auth.loggedIn = true;

      state.auth.user = {
        email: data.user.email
      };

      state.auth.email = '';
      state.auth.password = '';
      state.auth.rememberMe = false;

      if (typeof checkAuth === 'function') {
        const sessionID = getCookie('PHPSESSID') || '';
        await checkAuth(sessionID, data.user.email);
      }

      showToast(
        'Zalogowano jako ' +
        data.user.email +
        '.'
      );

      state.auth.view = null;

      render();

    } else {

      state.auth.error =
        data.message;
      state.auth.password = '';

      render();
    }

  } catch (err) {

    console.error(
      '[DEBUG] Błąd przetworzenia odpowiedzi:',
      err
    );

    state.auth.loading = false;
    state.auth.password = '';

    state.auth.error =
      'Błąd serwera. Spróbuj ponownie.';

    render();
  }
}

checkP24Status();