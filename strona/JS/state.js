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
        loading: false,
        requires2FA: false,
        resumeToPayment: false,
        email: '',
        password: '',
        rememberMe: false
    },

    profile: {},

    courts: [],
    reservations: [],

    transactions: []
};

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

export async function fetchReservations(email) {
    try {
        const response = await fetch("PHP/db_getters/get_reservations.php", {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ email: email })
        });
        const data = await response.json();
        state.transactions = Array.isArray(data) ? data.map(r => ({
            id: String(r.id),
            codeID: r.codeID || r.codeid,
            courtId: Number(r.court_id),
            dateStr: r.date,
            startHour: parseInt(r.begin || r.start_time),
            endHour: parseInt(r.end || r.end_time),
            price: r.price,
            returnRequest: r.returnRequest || null
        })) : [];
    } catch (e) {
        console.error("Nie udało się pobrać rezerwacji do stanu", e);
        state.transactions = [];
    }
}

export {
    state,
    HOURS,
    BANKS,
    isOccupiedByOthers
};