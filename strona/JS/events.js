import { handleRegister, handleLogin } from './AJAX/actions.js';

import {
    startTwoFactor,
    sendTwoFactorCode,
    confirmTwoFactor
} from './AJAX/podstrony/profile/twoFactor.js';

import { state, getProfileDetails, fetchReservations } from './state.js';
import { render } from './AJAX/render.js';
import { checkAuth } from './AJAX/helpers.js';


document.addEventListener('click', async (e) => {

    const actionBtn =
        e.target.closest('[data-action]');


    if (!actionBtn) {
        return;
    }

    const action =
        actionBtn.dataset.action;

    if (action === 'do-register') {

        e.preventDefault();
        await handleRegister(e);
        return;
    }

    if (action === 'do-login') {

        e.preventDefault();
        await handleLogin(e);
        
        // Jeśli logowanie przebiegło pomyślnie i mamy ustawiony email / sesję
        const savedEmail = getCookie ? getCookie('email') : null;
        if (savedEmail && state.auth.loggedIn) {
            await getProfileDetails(savedEmail);
            await fetchReservations(savedEmail);
        }
        
        render();
        return;
    }

    if (action === 'verify-login-2fa') {
        e.preventDefault();

        const input = document.getElementById('auth2FACodeInput');
        const code = input ? input.value.trim() : '';

        if (!code || code.length !== 6) {
            state.auth.error = 'Wprowadź poprawny 6-cyfrowy kod.';
            render();
            return;
        }

        try {
            const res = await fetch('PHP/login/verify_login_2fa.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ code })
            });
            const data = await res.json();

            if (data.success) {
                const userEmail = data.user.email;

                state.auth.requires2FA = false;
                state.auth.loggedIn = true;
                state.auth.user = { email: userEmail };
                state.auth.view = null;
                state.auth.error = null;
                
                // Sprawdzenie autoryzacji i pobranie danych profilu oraz rezerwacji natychmiast po 2FA
                const savedSessionID = getCookie('PHPSESSID') || '';
                try {
                    await checkAuth(savedSessionID, userEmail);
                } catch (authErr) {
                    console.warn('[DEBUG events] checkAuth po 2FA zwróciło ostrzeżenie:', authErr);
                }

                await getProfileDetails(userEmail);
                await fetchReservations(userEmail);
                
                render();
            } else {
                state.auth.error = data.message;
                render();
            }
        } catch (err) {
            state.auth.error = 'Błąd serwera. Spróbuj ponownie.';
            render();
        }

        return;
    }

    if (action === 'cancel-login-2fa') {
        e.preventDefault();
        state.auth.requires2FA = false;
        state.auth.error = null;
        render();
        return;
    }

    if (action === 'switch-auth') {

        e.preventDefault();

        state.auth.view =
        actionBtn.dataset.mode;

        state.auth.error = null;
        state.auth.requires2FA = false;

        render();

        return;
    }

    if (action === 'close-auth') {

        e.preventDefault();

        state.auth.view = null;
        state.auth.error = null;       
        state.auth.requires2FA = false;   
        state.auth.resumeToPayment = false;

        render();

        return;
    }

    if (action === 'start-2fa') {

        e.preventDefault();
        console.log(
            '[2FA] Kliknięto Włącz/Wyłącz'
        );
        startTwoFactor();

        return;
    }

    if (action === 'send-2fa-code') {

        e.preventDefault();

        console.log(
            '[2FA] Kliknięto Wyślij kod'
        );

        await sendTwoFactorCode();

        return;
    }

    if (action === 'confirm-2fa') {

        e.preventDefault();

        console.log(
            '[2FA] Kliknięto Potwierdź'
        );

        const panel =
            actionBtn.closest('.twofa-panel');

        console.log(
            '[2FA] PANEL:',
            panel
        );

        if (!panel) {

            console.error(
                '[2FA] Nie znaleziono panelu 2FA.'
            );

            return;
        }

        const input =
            panel.querySelector(
                '.twofa-code-input'
            );

        console.log(
            '[2FA] INPUT Z PANELU:',
            input
        );

        console.log(
            '[2FA] WARTOŚĆ:',
            input
                ? JSON.stringify(input.value)
                : 'BRAK'
        );

        await confirmTwoFactor(
            input,
            panel
        );

        return;
    }

});