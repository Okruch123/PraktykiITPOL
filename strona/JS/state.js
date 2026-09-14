import {
    toDateStr,
    addDays,
    DATES
} from './utils.js';

const state = {
    tab: 'rezerwacje',
    selectedCourtId: null,
    selectedDateIndex: 0,
    kontoSub: 'profil',
    editingProfile: false,
    toast: null,

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

    profile: {
        name: 'Anna Kowalska',
        email: 'anna.kowalska@przyklad.pl',
        phone: '512 345 678',
        cardNo: 'SP-2381',
        member: 'W klubie od 2023'
    },

    courts: fetch('PHP/db_getters/courtsData.php').then(r => r.json()),
    // [
    //     {
    //         id: 1,
    //         name: 'Kort 1',
    //         surface: 'clay',
    //         surfaceLabel: 'Nawierzchnia ceglana, zewnętrzny',
    //         price: 60
    //     },
    //     {
    //         id: 2,
    //         name: 'Kort 2',
    //         surface: 'hard',
    //         surfaceLabel: 'Nawierzchnia twarda, zewnętrzny',
    //         price: 55
    //     },
    //     {
    //         id: 3,
    //         name: 'Kort 3',
    //         surface: 'clay',
    //         surfaceLabel: 'Nawierzchnia ceglana, zewnętrzny',
    //         price: 60
    //     },
    //     {
    //         id: 4,
    //         name: 'Kort 4',
    //         surface: 'hala',
    //         surfaceLabel: 'Hala, nawierzchnia dywanowa',
    //         price: 80
    //     }
    // ],

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

export {
    state,
    HOURS,
    BANKS,
    isOccupiedByOthers
};