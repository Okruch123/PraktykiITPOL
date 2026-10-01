import { state, HOURS, BANKS, isOccupiedByOthers, fetchReservations } from "../../../state.js";
import { DAY_NAMES, MONTH_NAMES, pad, toDateStr, addDays, TODAY, NOW_HOUR, DATES } from "../../../utils.js";
import {
  fmtDate,
  fmtShortDate,
  courtTag,
  returnReasonLabel,
  myReservationAt,
  isHourFree,
  nextFreeSlotLabel,
} from "../../helpers.js";

function getEmailFromCookies() {
  const cookies = document.cookie.split(';');
  for (let cookie of cookies) {
    const [name, value] = cookie.trim().split('=');
    if (value && (name.toLowerCase().includes('email') || name.toLowerCase().includes('user') || value.includes('@'))) {
      return decodeURIComponent(value);
    }
  }
  return null;
}

export async function renderRezerwacje(email) {
  let userEmail = email || state.auth?.user?.email || state.auth?.email || getEmailFromCookies();

  if (!userEmail) {
    return `
      <h2 class="section-title">Moje rezerwacje</h2>
      <div class="empty-state">
        Musisz się zalogować, aby zobaczyć swoje rezerwacje.
      </div>
    `;
  }

  if (!state.transactions || state.transactions.length === 0) {
    await fetchReservations(userEmail);
  }

  const now = new Date();

  const upcoming = (state.transactions || []).filter(r => {
     if (!r.dateStr) return false;
     const datePart = String(r.dateStr).trim().split(' ')[0].split('T')[0];
     const resDate = new Date(datePart);
     
     const endHourNum = parseInt(r.endHour ?? r.end_time ?? 0);
     resDate.setHours(endHourNum, 0, 0, 0);

     return resDate.getTime() >= now.getTime();
   });

  state.reservations = upcoming.map(r => ({
    id: String(r.id),
    codeID: r.codeID || r.codeid,
    courtId: r.courtId,
    dateStr: r.dateStr,
    startHour: parseInt(r.startHour || r.start_time),
    endHour: parseInt(r.endHour || r.end_time),
    price: r.price,
    returnRequest: r.returnRequest || null
  }));

  let courtsArray = [];
  try {
    const resolvedCourts = await state.courts;
    courtsArray = Array.isArray(resolvedCourts) ? resolvedCourts : [];
  } catch (e) {
    courtsArray = Array.isArray(state.courts) ? state.courts : [];
  }

  const list = upcoming.map(r => {
    const date = r.dateStr ? new Date(r.dateStr) : new Date();
    const court = courtsArray.find(c => Number(c.id) === Number(r.courtId)) || { name: 'Kort', surfaceLabel: '' };
    
    const rawStart = r.startHour ?? r.start_time ?? '00:00:00';
    const rawEnd = r.endHour ?? r.end_time ?? '00:00:00';
    
    const startHour = String(rawStart);
    const endHour = String(rawEnd);

    return `
      <div class="res-card">
        <div class="res-date-badge">
          <span class="dn">${date.getDate()}</span>
          <span class="mn">${MONTH_NAMES[date.getMonth()]}</span>
        </div>
        <div class="res-info">
          <div class="res-court">${court.name} · ${court.surfaceLabel}</div>
          <div class="res-hour">${startHour.substring(0, 5)}–${endHour.substring(0, 5)}, ${fmtDate(date)}</div>
          <div class="res-price">${r.price || '0.00'} zł</div>
        </div>
        <div class="res-actions">
          <button class="res-details-btn" data-action="view-details" data-id="${r.id}">Szczegóły</button>
          ${r.returnRequest && r.returnRequest.status === 'pending'
            ? `<span class="return-badge pending">Zwrot w trakcie</span>`
            : `<button class="res-cancel" data-action="open-return" data-id="${r.id}">Zwróć</button>`}
        </div>
      </div>
    `;
  }).join('');

  return `
    <h2 class="section-title">Moje rezerwacje</h2>
    <p class="section-sub">Nadchodzące rezerwacje kortów.</p>
    ${upcoming.length ? list : `
      <div class="empty-state">
        Nie masz jeszcze żadnych nadchodzących rezerwacji.
        <div><button class="go-btn" data-action="set-tab" data-tab="korty">Przeglądaj korty</button></div>
      </div>
    `}
  `;
}