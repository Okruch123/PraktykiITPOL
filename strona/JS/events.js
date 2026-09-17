import { handleRegister, handleLogin } from './AJAX/actions.js';

import {
    startTwoFactor,
    sendTwoFactorCode,
    confirmTwoFactor
} from './AJAX/podstrony/profile/twoFactor.js';

import { state } from './state.js';
import { render } from './AJAX/render.js';


document.addEventListener('click', async (e) => {

    const actionBtn =
        e.target.closest('[data-action]');


    if (!actionBtn) {
        return;
    }


    const action =
        actionBtn.dataset.action;


    // =====================================================
    // REJESTRACJA
    // =====================================================

    if (action === 'do-register') {

        e.preventDefault();

        await handleRegister(e);

        return;
    }


    // =====================================================
    // LOGOWANIE
    // =====================================================

    if (action === 'do-login') {

        e.preventDefault();

        await handleLogin(e);

        return;
    }


    // =====================================================
    // PRZEŁĄCZANIE LOGOWANIE / REJESTRACJA
    // =====================================================

    if (action === 'switch-auth') {

        e.preventDefault();

        state.auth.view =
            actionBtn.dataset.mode;

        state.auth.error = null;

        render();

        return;
    }


    // =====================================================
    // ZAMKNIĘCIE AUTORYZACJI
    // =====================================================

    if (action === 'close-auth') {

        e.preventDefault();

        state.auth.view = null;

        state.auth.error = null;

        render();

        return;
    }


    // =====================================================
    // START 2FA
    // =====================================================

    if (action === 'start-2fa') {

        e.preventDefault();

        console.log(
            '[2FA] Kliknięto Włącz/Wyłącz'
        );

        startTwoFactor();

        return;
    }


    // =====================================================
    // WYŚLIJ KOD 2FA
    // =====================================================

    if (action === 'send-2fa-code') {

        e.preventDefault();

        console.log(
            '[2FA] Kliknięto Wyślij kod'
        );

        await sendTwoFactorCode();

        return;
    }


    // =====================================================
    // POTWIERDŹ KOD 2FA
    // =====================================================

    if (action === 'confirm-2fa') {

        e.preventDefault();

        console.log(
            '[2FA] Kliknięto Potwierdź'
        );


        /*
         * Znajdujemy dokładnie ten panel 2FA,
         * w którym znajduje się kliknięty przycisk.
         */

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


        /*
         * Szukamy inputa wyłącznie wewnątrz
         * tego panelu.
         */

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