import { state, HOURS, BANKS, isOccupiedByOthers } from "../../../state.js";
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

export async function renderRezerwacje(email) {
  let responseData;
  try {
    const res = await fetch("PHP/db_getters/get_reservations.php", {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ email: email })
    });
    responseData = await res.json();
  } catch (err) {
    console.error("Błąd pobierania rezerwacji:", err);
    responseData = [];
  }

  const upcoming = Array.isArray(responseData) ? responseData : [];

  // Zapisujemy pobrane rezerwacje do stanu
  state.reservations = upcoming.map(r => ({
    id: String(r.id),
    codeID: r.codeID || r.codeid,
    courtId: Number(r.court_id),
    dateStr: r.date,
    startHour: parseInt(r.begin || r.start_time),
    endHour: parseInt(r.end || r.end_time),
    price: r.price,
    returnRequest: r.returnRequest || null
  }));
  console.log(state.reservations);

  // BEZPIECZNE POBRANIE KORTÓW (obsługa, gdyby state.courts był promise lub tablicą)
  let courtsArray = [];
  try {
    const resolvedCourts = await state.courts;
    courtsArray = Array.isArray(resolvedCourts) ? resolvedCourts : [];
  } catch (e) {
    courtsArray = Array.isArray(state.courts) ? state.courts : [];
  }

  const list = upcoming.map(r => {
    const date = r.date ? new Date(r.date) : new Date();
    const court = courtsArray.find(c => Number(c.id) === Number(r.court_id)) || { name: 'Kort', surfaceLabel: '' };
    
    // Konwertujemy bezpiecznie na string, żeby .substring() nigdy nie wyrzucił błędu
    const rawStart = r.begin ?? r.start_time ?? '00:00:00';
    const rawEnd = r.end ?? r.end_time ?? '00:00:00';
    
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
        Nie masz jeszcze żadnych rezerwacji.
        <div><button class="go-btn" data-action="set-tab" data-tab="korty">Przeglądaj korty</button></div>
      </div>
    `}
  `;
}