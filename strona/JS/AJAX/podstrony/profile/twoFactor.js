import { state } from '../../../state.js';
import { render } from '../../render.js';


const TWO_FACTOR_URL =
    '/PraktykiITPOL/strona/PHP/login/two_factor.php';


// =====================================================
// START 2FA
// =====================================================

export function startTwoFactor() {

    console.log(
        '[2FA] Wybrano zmianę ustawienia 2FA.'
    );

}


// =====================================================
// WYŚLIJ KOD
// =====================================================

export async function sendTwoFactorCode() {

    console.log(
        '[2FA] Wysyłanie kodu...'
    );


    const button =
        document.querySelector(
            '[data-action="send-2fa-code"]'
        );


    /*
     * Szukamy panelu 2FA.
     */

    const panel =
        document.querySelector(
            '.twofa-panel'
        );


    if (!panel) {

        console.error(
            '[2FA] Nie znaleziono panelu 2FA.'
        );

        return;
    }


    const error =
        panel.querySelector(
            '.twofa-send-error'
        );


    if (error) {

        error.style.color = 'red';

        error.textContent = '';
    }


    if (button) {

        button.disabled = true;

        button.textContent =
            'Wysyłanie...';
    }


    try {

        const response =
            await fetch(
                TWO_FACTOR_URL,
                {
                    method: 'POST',

                    headers: {
                        'Content-Type':
                            'application/json'
                    },

                    credentials: 'include',

                    body: JSON.stringify({
                        action: 'send'
                    })
                }
            );


        const data =
            await response.json();


        console.log(
            '[2FA] SEND:',
            data
        );


        if (!data.success) {

            if (error) {

                error.style.color = 'red';

                error.textContent =
                    data.message ||
                    'Nie udało się wysłać kodu.';
            }


            if (button) {

                button.disabled = false;

                button.textContent =
                    'Wyślij kod';
            }


            return;
        }


        // =================================================
        // SUKCES
        // =================================================

        if (error) {

            error.style.color = 'green';

            error.textContent =
                'Kod został wysłany na Twój adres e-mail.';
        }


        /*
         * Czyścimy pole kodu.
         */

        const codeInput =
            panel.querySelector(
                '.twofa-code-input'
            );


        if (codeInput) {

            codeInput.value = '';

            codeInput.focus();
        }


        if (button) {

            button.disabled = false;

            button.textContent =
                'Wyślij ponownie';
        }

    }
    catch (err) {

        console.error(
            '[2FA] SEND ERROR:',
            err
        );


        if (error) {

            error.style.color = 'red';

            error.textContent =
                'Wystąpił błąd połączenia z serwerem.';
        }


        if (button) {

            button.disabled = false;

            button.textContent =
                'Wyślij kod';
        }
    }

}


// =====================================================
// POTWIERDZENIE KODU
// =====================================================

export async function confirmTwoFactor(
    input,
    panel
) {

    console.log(
        '[2FA] Potwierdzanie kodu...'
    );


    // =================================================
    // SPRAWDZENIE INPUTA
    // =================================================

    if (!input) {

        console.error(
            '[2FA] Nie znaleziono pola kodu.'
        );

        return;
    }


    // =================================================
    // SPRAWDZENIE PANELU
    // =================================================

    if (!panel) {

        console.error(
            '[2FA] Nie znaleziono panelu 2FA.'
        );

        return;
    }


    // =================================================
    // ELEMENTY
    // =================================================

    const error =
        panel.querySelector(
            '.twofa-code-error'
        );


    if (!error) {

        console.error(
            '[2FA] Nie znaleziono komunikatu błędu.'
        );

        return;
    }


    const button =
        panel.querySelector(
            '[data-action="confirm-2fa"]'
        );


    // =================================================
    // CZYSZCZENIE BŁĘDU
    // =================================================

    error.style.color = 'red';

    error.textContent = '';


    // =================================================
    // ODCZYT KODU
    // =================================================

    const code =
        input.value.trim();


    console.log(
        '[2FA] WARTOŚĆ INPUT:',
        JSON.stringify(code)
    );


    console.log(
        '[2FA] DŁUGOŚĆ:',
        code.length
    );


    // =================================================
    // WALIDACJA
    // =================================================

    if (!/^\d{6}$/.test(code)) {

        console.error(
            '[2FA] WALIDACJA ODRZUCONA:',
            JSON.stringify(code)
        );


        error.textContent =
            'Wpisz poprawny 6-cyfrowy kod.';


        input.focus();

        return;
    }


    // =================================================
    // BLOKADA PRZYCISKU
    // =================================================

    if (button) {

        button.disabled = true;

        button.textContent =
            'Sprawdzanie...';
    }


    // =================================================
    // WYSŁANIE DO PHP
    // =================================================

    try {

        const response =
            await fetch(
                TWO_FACTOR_URL,
                {
                    method: 'POST',

                    headers: {
                        'Content-Type':
                            'application/json'
                    },

                    credentials: 'include',

                    body: JSON.stringify({
                        action: 'confirm',
                        code: code
                    })
                }
            );


        console.log(
            '[2FA] CONFIRM HTTP:',
            response.status
        );


        // =================================================
        // ODCZYT JSON
        // =================================================

        const data =
            await response.json();


        console.log(
            '[2FA] CONFIRM PHP:',
            data
        );


        // =================================================
        // BŁĄD PHP
        // =================================================

        if (!data.success) {

            error.style.color = 'red';

            error.textContent =
                data.message ||
                'Nieprawidłowy kod.';


            if (button) {

                button.disabled = false;

                button.textContent =
                    'Potwierdź';
            }


            input.focus();

            return;
        }


        // =================================================
        // SUKCES
        // =================================================

        if (state.profile) {

            state.profile.twoFactorEnabled =
                Number(
                    data.twoFactorEnabled
                );
        }


        console.log(
            '[2FA] Kod poprawny.'
        );


        console.log(
            '[2FA] Nowy status 2FA:',
            data.twoFactorEnabled
        );


        /*
         * Odświeżamy widok profilu.
         */

        await render();

    }
    catch (err) {

        console.error(
            '[2FA] CONFIRM ERROR:',
            err
        );


        error.style.color = 'red';

        error.textContent =
            'Wystąpił błąd połączenia z serwerem.';


        if (button) {

            button.disabled = false;

            button.textContent =
                'Potwierdź';
        }


        input.focus();
    }

}