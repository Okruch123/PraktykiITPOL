import { state } from "../../../state.js";
import { getCookie } from "../../helpers.js";
import { getProfileDetails } from "../../../state.js";

export function renderProfil() {
    const p = state.profile || {};
    const editing = state.editingProfile;

    const field = (label, key, value, type = "text", disabled = false) => `
        <div class="profile-row">
            <div class="profile-label">
                ${label}
            </div>
            <div class="profile-value">
                ${editing
                    ? `
                        <input
                            type="${type}"
                            data-field="${key}"
                            value="${value ?? ''}"
                            ${disabled ? 'disabled style="width: 100%; padding: 8px; border: 1px solid #444; border-radius: 4px; background: #151515; color: #777; cursor: not-allowed;"' : 'style="width: 100%; padding: 8px; border: 1px solid #444; border-radius: 4px; background: #222; color: #fff;"'}
                        >
                    `
                    : (value ?? '<span style="color: #777; font-style: italic;">Brak</span>')
                }
            </div>
        </div>
    `;

    if (state.auth.user != null) {
        const twoFAEnabled = Number(p?.twoFactorEnabled) === 1;

        return `
        <div class="profile-card" style="display: flex; flex-direction: column; gap: 15px;">
            ${field('Imię i nazwisko', 'name', p?.name)}
            ${field('E-mail', 'email', state.auth.user.email, 'email', true)}
            ${field('Telefon', 'phone', p?.phone, 'tel')}

            ${editing ? `
                <div class="profile-row" style="background: rgba(255,255,255,0.03); padding: 12px; border-radius: 6px; margin-top: 10px; display: flex; flex-direction: column; gap: 8px;">
                    <div class="profile-label" style="font-weight: 600; margin-bottom: 4px;">Zmiana hasła</div>
                    <div class="profile-value" style="display: flex; flex-direction: column; gap: 8px; width: 100%; max-width: none;">
                        <input 
                            type="password" 
                            id="profileOldPassword" 
                            placeholder="Stare hasło (wymagane tylko przy zmianie hasła)" 
                            style="padding: 8px; border: 1px solid #444; border-radius: 4px; background: #222; color: #fff;"
                        >
                        <input 
                            type="password" 
                            id="profileNewPassword" 
                            placeholder="Nowe hasło" 
                            style="padding: 8px; border: 1px solid #444; border-radius: 4px; background: #222; color: #fff;"
                        >
                        <input 
                            type="password" 
                            id="profileConfirmPassword" 
                            placeholder="Powtórz nowe hasło" 
                            style="padding: 8px; border: 1px solid #444; border-radius: 4px; background: #222; color: #fff;"
                        >
                    </div>
                </div>
            ` : ''}

            ${state.profileError ? `<div style="color: #e74c3c; font-size: 13px; margin-top: 5px;">${state.profileError}</div>` : ''}

            <div class="profile-row" style="border-top: 1px solid #333; padding-top: 15px; margin-top: 5px;">
                <div class="profile-label">
                    <div>Weryfikacja dwuetapowa</div>
                    <div style="margin-top: 6px;">
                        <span style="
                            font-size: 11px;
                            font-weight: 600;
                            padding: 3px 8px;
                            border-radius: 4px;
                            letter-spacing: 0.5px;
                            text-transform: uppercase;
                            display: inline-block;
                            background: ${twoFAEnabled ? 'rgba(46, 204, 113, 0.15)' : 'rgba(231, 76, 60, 0.15)'};
                            color: ${twoFAEnabled ? '#2ecc71' : '#e74c3c'};
                            border: 1px solid ${twoFAEnabled ? 'rgba(46, 204, 113, 0.3)' : 'rgba(231, 76, 60, 0.3)'};
                        ">
                            ${twoFAEnabled ? 'Włączona' : 'Wyłączona'}
                        </span>
                    </div>
                </div>

                <div class="profile-value" style="width: 100%; max-width: none;">
                    <div style="margin-bottom: 15px;">
                        <button class="btn-secondary" data-action="disable-remember-me" style="padding: 10px 18px; font-size: 13px; cursor: pointer; border-radius: 6px; background-color: #2b2b2b; color: #ffffff; border: 1px solid #444444; transition: background 0.2s;">
                            Wyłącz automatyczne logowanie na tym urządzeniu
                        </button>
                    </div>

                    <div class="twofa-panel" style="margin-top: 0; padding: 18px; border: 1px solid #333; border-radius: 8px; background: rgba(0,0,0,0.1);">
                        <div style="margin-bottom: 12px; color: #aaa; font-size: 13px;">
                            Aby ${twoFAEnabled ? 'wyłączyć' : 'włączyć'} weryfikację dwuetapową, wyślij kod na swój adres e-mail.
                        </div>

                        <button
                            type="button"
                            class="twofa-send-btn"
                            data-action="send-2fa-code"
                            style="padding: 8px 14px; background: #333; color: #fff; border: 1px solid #555; border-radius: 4px; cursor: pointer;"
                        >
                            Wyślij kod
                        </button>

                        <div class="twofa-send-error" style="margin-top: 8px; color: red;"></div>

                        <div class="twofa-code-section" style="margin-top: 15px;">
                            <label style="display: block; margin-bottom: 6px; font-size: 13px;">
                                Kod weryfikacyjny
                            </label>

                            <div style="display: flex; align-items: center; gap: 8px; flex-wrap: wrap;">
                                <input
                                    type="text"
                                    class="twofa-code-input"
                                    inputmode="numeric"
                                    maxlength="6"
                                    placeholder="000000"
                                    autocomplete="one-time-code"
                                    style="width: 120px; padding: 8px; background: #222; border: 1px solid #444; color: #fff; border-radius: 4px;"
                                >

                                <button
                                    type="button"
                                    class="twofa-confirm-btn"
                                    data-action="confirm-2fa"
                                    style="padding: 8px 14px; background: #2ecc71; color: #000; border: none; border-radius: 4px; cursor: pointer; font-weight: 600;"
                                >
                                    Potwierdź
                                </button>
                            </div>

                            <div class="twofa-code-error" style="margin-top: 8px; color: red;"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="profile-actions" style="margin-top: 20px;">
            ${editing
                ? `
                    <div style="display: flex; gap: 10px;">
                        <button
                            type="button"
                            class="save-btn"
                            data-action="save-profile"
                            style="padding: 10px 20px; background: #2ecc71; color: #000; border: none; border-radius: 6px; font-weight: 600; cursor: pointer;"
                        >
                            Zapisz zmiany
                        </button>
                        <button
                            type="button"
                            class="cancel-btn"
                            data-action="cancel-profile-edit"
                            style="padding: 10px 20px; background: #444; color: #fff; border: none; border-radius: 6px; cursor: pointer;"
                        >
                            Anuluj
                        </button>
                    </div>
                `
                : `
                    <button
                        type="button"
                        class="edit-btn"
                        data-action="edit-profile"
                        style="padding: 10px 20px; background: #3498db; color: #fff; border: none; border-radius: 6px; font-weight: 600; cursor: pointer;"
                    >
                        Edytuj profil
                    </button>
                `
            }
        </div>
        `;
    }

    return `
        <div class="profile-card">
            <span>
                Nie jesteś zalogowany/a.
            </span>
        </div>
    `;
}