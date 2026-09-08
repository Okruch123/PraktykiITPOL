import { state } from "../../../state.js";
import { fmtDate, returnReasonLabel } from "../../helpers.js";

import{
  DAY_NAMES,
  MONTH_NAMES,
  pad,
  toDateStr,
  addDays,
  TODAY,
  NOW_HOUR,
  DATES
} from "../../../utils.js";

export function renderObsluga(){
  const pending = state.reservations.filter(r => r.returnRequest && r.returnRequest.status==='pending');

  const rows = pending.map(r => {
    const court = state.courts.find(c=>c.id===r.courtId);
    const date = DATES[r.dateIndex];
    return `
      <div class="obsluga-row">
        <div class="obsluga-main">
          <div class="obsluga-court">${court.name} · ${court.surfaceLabel}</div>
          <div class="obsluga-meta">${fmtDate(date)}, ${pad(r.startHour)}:00–${pad(r.endHour)}:00 · ${r.price} zł</div>
          <div class="obsluga-reason"><strong>Powód:</strong> ${returnReasonLabel(r.returnRequest.reason)}${r.returnRequest.note ? ' — '+r.returnRequest.note : ''}</div>
          <div class="obsluga-id">Nr rezerwacji: ${r.id}</div>
        </div>
        <div class="obsluga-actions">
          <button class="edit-btn" data-action="approve-return" data-id="${r.id}">Zatwierdź zwrot</button>
          <button class="res-cancel" data-action="reject-return" data-id="${r.id}">Odrzuć</button>
        </div>
      </div>
    `;
  }).join('');

  return `
    <h2 class="section-title">Obsługa — prośby o zwrot</h2>
    <p class="section-sub">Zatwierdź lub odrzuć prośby graczy o zwrot rezerwacji.</p>
    ${pending.length ? rows : `<div class="empty-state">Brak oczekujących próśb o zwrot.</div>`}
  `;
}