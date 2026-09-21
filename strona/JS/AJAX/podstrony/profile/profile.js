import { state } from "../../../state.js";
import { getCookie } from "../../helpers.js";
import { getProfileDetails } from "../../../state.js";

export function renderProfil() {
    const p = state.profile;
    const editing = state.editingProfile;

    const field = (label, key, value) => `
        <div class="profile-row">
            <div class="profile-label">
                ${label}
            </div>
            <div class="profile-value">
                ${editing
                    ? `
                        <input
                            data-field="${key}"
                            value="${value ?? ''}"
                        >
                    `
                    : (value ?? '')
                }
            </div>
        </div>
    `;

    if (state.auth.user != null) {
        const twoFAEnabled = Number(p?.twoFactorEnabled) === 1;

        return `
        <div class="profile-card">
            ${field('Imię i nazwisko', 'name', p?.name)}
            ${field('E-mail', 'email', state.auth.user.email)}
            ${field('Telefon', 'phone', p?.phone)}

            <div class="profile-row">
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

                    <div class="twofa-panel" style="margin-top: 0; padding: 18px; border: 1px solid var(--sand-line, #ddd); border-radius: 8px;">
                        <div style="margin-bottom: 12px; color: var(--muted, #666); font-size: 13px;">
                            Aby ${twoFAEnabled ? 'wyłączyć' : 'włączyć'} weryfikację dwuetapową, wyślij kod na swój adres e-mail.
                        </div>

                        <button
                            type="button"
                            class="twofa-send-btn"
                            data-action="send-2fa-code"
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
                                    style="width: 120px; padding: 8px;"
                                >

                                <button
                                    type="button"
                                    class="twofa-confirm-btn"
                                    data-action="confirm-2fa"
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

        <div class="profile-actions">
            ${editing
                ? `
                    <button
                        type="button"
                        class="save-btn"
                        data-action="save-profile"
                    >
                        Zapisz zmiany
                    </button>
                `
                : `
                    <button
                        type="button"
                        class="edit-btn"
                        data-action="edit-profile"
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