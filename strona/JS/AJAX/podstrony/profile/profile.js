import { state } from "../../../state.js";


export function renderProfil() {

    const p =
        state.profile;

    const editing =
        state.editingProfile;


    // =====================================================
    // POLE PROFILU
    // =====================================================

    const field = (
        label,
        key,
        value
    ) => `

        <div class="profile-row">

            <div class="profile-label">
                ${label}
            </div>

            <div class="profile-value">

                ${
                    editing

                        ? `
                            <input
                                data-field="${key}"
                                value="${value ?? ''}"
                            >
                        `

                        : (
                            value ?? ''
                        )
                }

            </div>

        </div>

    `;


    // =====================================================
    // UŻYTKOWNIK ZALOGOWANY
    // =====================================================

    if (state.auth.user != null) {


        const twoFAEnabled =
            Number(
                p?.twoFactorEnabled
            ) === 1;


        return `

        <div class="profile-card">


            ${field(
                'Imię i nazwisko',
                'name',
                p?.name
            )}


            ${field(
                'E-mail',
                'email',
                state.auth.user.email
            )}


            ${field(
                'Telefon',
                'phone',
                p?.phone
            )}


            <!-- =========================================
                 CZŁONKOSTWO
            ========================================== -->

            <div class="profile-row">

                <div class="profile-label">
                    Członkostwo
                </div>


                <div class="profile-value">

                    ${p?.member ?? ''}

                </div>

            </div>


            <!-- =========================================
                 2FA
            ========================================== -->

            <div class="profile-row">

                <div class="profile-label">
                    Weryfikacja dwuetapowa
                </div>


                <div class="profile-value">


                    <!-- =================================
                         STATUS + PRZYCISK
                    ================================== -->

                    <div
                        style="
                            display:flex;
                            align-items:center;
                            gap:12px;
                            flex-wrap:wrap;
                            margin-bottom:15px;
                        "
                    >


                        <span>

                            ${
                                twoFAEnabled
                                    ? 'Włączona'
                                    : 'Wyłączona'
                            }

                        </span>


                        <button
                            type="button"
                            class="twofa-btn"
                            data-action="start-2fa"
                        >

                            ${
                                twoFAEnabled
                                    ? 'Wyłącz'
                                    : 'Włącz'
                            }

                        </button>


                    </div>


                    <!-- =================================
                         PANEL 2FA
                    ================================== -->

                    <div
                        class="twofa-panel"
                        style="
                            margin-top:10px;
                            padding:15px;
                            border:1px solid #ddd;
                            border-radius:8px;
                        "
                    >


                        <!-- =============================
                             INFORMACJA
                        ============================== -->

                        <div
                            style="
                                margin-bottom:10px;
                            "
                        >

                            Aby

                            ${
                                twoFAEnabled
                                    ? 'wyłączyć'
                                    : 'włączyć'
                            }

                            weryfikację dwuetapową,
                            wyślij kod na swój adres e-mail.

                        </div>


                        <!-- =============================
                             WYŚLIJ KOD
                        ============================== -->

                        <button
                            type="button"
                            class="twofa-send-btn"
                            data-action="send-2fa-code"
                        >

                            Wyślij kod

                        </button>


                        <!-- =============================
                             KOMUNIKAT WYSYŁANIA
                        ============================== -->

                        <div
                            class="twofa-send-error"
                            style="
                                margin-top:8px;
                                color:red;
                            "
                        >
                        </div>


                        <!-- =============================
                             KOD
                        ============================== -->

                        <div
                            class="twofa-code-section"
                            style="
                                margin-top:15px;
                            "
                        >


                            <label
                                style="
                                    display:block;
                                    margin-bottom:6px;
                                "
                            >

                                Kod weryfikacyjny

                            </label>


                            <input
                                type="text"
                                class="twofa-code-input"
                                inputmode="numeric"
                                maxlength="6"
                                placeholder="000000"
                                autocomplete="one-time-code"
                                style="
                                    width:120px;
                                    padding:8px;
                                "
                            >


                            <button
                                type="button"
                                class="twofa-confirm-btn"
                                data-action="confirm-2fa"
                                style="
                                    margin-left:8px;
                                "
                            >

                                Potwierdź

                            </button>


                            <!-- =========================
                                 BŁĄD KODU
                            ========================== -->

                            <div
                                class="twofa-code-error"
                                style="
                                    margin-top:8px;
                                    color:red;
                                "
                            >
                            </div>


                        </div>


                    </div>


                </div>

            </div>


        </div>


        <!-- =============================================
             AKCJE PROFILU
        ============================================== -->

        <div class="profile-actions">


            ${
                editing

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


    // =====================================================
    // NIEZALOGOWANY
    // =====================================================

    return `

        <div class="profile-card">

            <span>
                Nie jesteś zalogowany/a.
            </span>

        </div>

    `;

}