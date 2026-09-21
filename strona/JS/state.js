import {
    toDateStr,
    addDays,
    DATES
} from './utils.js';

import { getCookie } from './AJAX/helpers.js';

const state = {
    tab: 'rezerwacje',
    selectedCourtId: null,
    selectedDateIndex: 0,
    kontoSub: 'profil',
    editingProfile: false,
    toast: null,

    bookedHours: [],

    pick: {
        from: null,
        to: null
    },

    pendingPayment: null,
    selectedBank: null,
    overlay: null,
    viewingReservationId: null,
    showReturnForm: false,
    returnFormError: null,

    auth: {
        loggedIn: false,
        user: null,
        view: null,
        error: null,
        resumeToPayment: false
    },

    profile: {},

    // Startujemy z pustą tablicą – dane załadujemy funkcją initCourts()
    courts: [],
    reservations: [],

    transactions: [
        {
            id: 't1',
            dateStr: toDateStr(addDays(new Date(), -11)),
            desc: 'Rezerwacja — Kort 2, 18:00–19:00',
            amount: 55,
            status: 'done'
        },
        {
            id: 't2',
            dateStr: toDateStr(addDays(new Date(), -6)),
            desc: 'Rezerwacja — Kort 4, 20:00–22:00',
            amount: 160,
            status: 'done'
        },
        {
            id: 't3',
            dateStr: toDateStr(addDays(new Date(), -3)),
            desc: 'Rezerwacja — Kort 1, 09:00–10:00',
            amount: 60,
            status: 'cancelled'
        }
    ]
};

// Funkcja pobierająca korty i zapisująca je do stanu jako zwykłą tablicę
export async function initCourts() {
    try {
        const response = await fetch('PHP/db_getters/courtsData.php');
        const data = await response.json();
        state.courts = Array.isArray(data) ? data : [];
    } catch (e) {
        console.error("Nie udało się pobrać kortów:", e);
        state.courts = [];
    }
}

const HOURS = Array.from(
    { length: 15 },
    (_, i) => 7 + i
);

const BANKS = [
    'mBank',
    'PKO BP',
    'ING Bank Śląski',
    'Santander Bank Polska',
    'Millennium',
    'Pekao'
];

function isOccupiedByOthers(courtId, dateIndex, hour){
    return (courtId * 13 + dateIndex * 7 + hour * 3) % 11 === 0;
}

export async function getProfileDetails(email){
    try {
        const response = await fetch("PHP/db_getters/profile.php", {
             method: 'POST',
             headers: { 'Content-Type': 'application/json' },
             body: JSON.stringify({ email: email })
        });
        state.profile = await response.json();
    } catch (e) {
        console.error("Nie udało się pobrać profilu", e);
    }
}

export async function fetchBookedHoursFromServer(courtId, dateStr) {
    try {
        const response = await fetch(`PHP/db_getters/get_booked_hours.php?courtId=${courtId}&dateStr=${dateStr}`);
        const data = await response.json();
        return data.bookedHours || [];
    } catch (e) {
        console.error("Nie udało się pobrać zajętych godzin", e);
        return [];
    }
}

export {
    state,
    HOURS,
    BANKS,
    isOccupiedByOthers
};