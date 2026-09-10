import { state, HOURS, BANKS, isOccupiedByOthers } from "../../../state.js";
import { DAY_NAMES, MONTH_NAMES, pad, toDateStr, addDays, TODAY, NOW_HOUR, DATES } from "../../../utils.js";
import {
    fmtDate,
    fmtShortDate,
    courtTag,
    returnReasonLabel,
    myReservationAt,
    isHourFree,
    nextFreeSlotLabel
} from "../../helpers.js";

export function renderRezerwacje(){
  const upcoming = state.reservations
    .slice()
    .sort((a,b) => (a.dateStr+pad(a.startHour)).localeCompare(b.dateStr+pad(b.startHour)));

  const list = upcoming.map(r => {
    const court = state.courts.find(c=>c.id===r.courtId);
    const date = DATES[r.dateIndex];
    return `
      <div class="res-card">
        <div class="res-date-badge">
          <span class="dn">${date.getDate()}</span>
          <span class="mn">${MONTH_NAMES[date.getMonth()]}</span>
        </div>
        <div class="res-info">
          <div class="res-court">${court.name} · ${court.surfaceLabel}</div>
          <div class="res-hour">${pad(r.startHour)}:00–${pad(r.endHour)}:00, ${fmtDate(date)}</div>
          <div class="res-price">${r.price} zł</div>
        </div>
        <div class="res-actions">
          <button class="res-details-btn" data-action="view-details" data-id="${r.id}">Szczegóły</button>
          ${r.returnRequest && r.returnRequest.status==='pending'
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
        Nie masz jeszcze żadnych rezerwacji.
        <div><button class="go-btn" data-action="set-tab" data-tab="korty">Przeglądaj korty</button></div>
      </div>
    `}
  `;
}