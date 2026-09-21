import {
    DAY_NAMES,
    MONTH_NAMES,
    DATES,
    NOW_HOUR
} from '../utils.js';

import {
    state,
    HOURS,
    isOccupiedByOthers
} from '../state.js';

function getCookie(name) {
  const value = `; ${document.cookie}`;
  const parts = value.split(`; ${name}=`);
  if (parts.length === 2) return parts.pop().split(';').shift();
}

function fmtDate(d){
    return DAY_NAMES[d.getDay()]
        + ', '
        + d.getDate()
        + ' '
        + MONTH_NAMES[d.getMonth()];
}


function fmtShortDate(d){
    return d.getDate()
        + ' '
        + MONTH_NAMES[d.getMonth()];
}

function courtTag(surface){
    return surface === 'clay'
        ? 'Mączka'
        : surface === 'hard'
            ? 'Twarda'
            : 'Hala';
}


function returnReasonLabel(key){

    const map = {
        plans: 'Zmiana planów',
        injury: 'Kontuzja lub choroba',
        weather: 'Warunki pogodowe',
        mistake: 'Błąd przy rezerwacji terminu',
        other: 'Inny powód'
    };

    return map[key] || key;
}


function myReservationAt(courtId, dateIndex, hour){

    return state.reservations.find(r =>
        r.courtId === courtId &&
        r.dateIndex === dateIndex &&
        hour >= r.startHour &&
        hour < r.endHour
    );
}

function isHourFree(courtId, dateIndex, hour){
    if(dateIndex === 0 && hour <= NOW_HOUR)
        return false;

    if(myReservationAt(courtId, dateIndex, hour))
        return false;

    // Sprawdzamy, czy godzina jest w tablicy pobranej z bazy danych przez PHP
    if(state.bookedHours && state.bookedHours.includes(hour))
        return false;

    return true;
}

function nextFreeSlotLabel(court){

    for(let di = 0; di < DATES.length; di++){

        for(const h of HOURS){

            if(isHourFree(court.id, di, h)){

                return (
                    di === 0
                        ? 'dziś'
                        : di === 1
                            ? 'jutro'
                            : fmtShortDate(DATES[di])
                ) + ', ' + h + ':00';
            }
        }
    }

    return 'brak wolnych terminów';
}

async function checkAuth(sessionID, email){
    try {
        const res = await fetch('PHP/db_getters/auth.php', {
          method: 'POST',
          headers: { 'Content-Type': 'application/json' },
          body: JSON.stringify({
            email: email,
            sessionID: sessionID,
          })
        });
        const data = await res.json();
        return data; // Zwracamy wynik do startup.js!
    } catch (e) {
        console.error("Błąd checkAuth:", e);
        return { success: false };
    }
}


export {
    fmtDate,
    fmtShortDate,
    courtTag,
    returnReasonLabel,
    myReservationAt,
    isHourFree,
    nextFreeSlotLabel,
    checkAuth,
    getCookie
};