import { state } from "../../../state.js";
import {
  DAY_NAMES,
  MONTH_NAMES,
  pad,
  toDateStr,
  addDays,
  TODAY,
  NOW_HOUR,
  DATES
} from "../../../utils.js";
import {
  fmtDate,
  fmtShortDate,
  courtTag,
  returnReasonLabel,
  myReservationAt,
  isHourFree,
  nextFreeSlotLabel
} from "../../helpers.js";

import { renderRezerwacje } from "./reservations.js";

export function renderReservationDetail(id){
  const r = state.reservations.find(x => x.id === id);
  if(!r) return renderRezerwacje(state.profile.email);
  
  const courts = Array.isArray(state.courts) ? state.courts : [];
  const court = courts.find(c => c.id === r.courtId) || { name: 'Kort', surfaceLabel: '' };
  
  // Bezpieczne pobranie daty (z bazy tekstowej lub tablicy DATES)
  const date = r.dateStr ? new Date(r.dateStr) : (DATES[r.dateIndex] || new Date());
  const rr = r.returnRequest;
  const qrPayload = `SETPOINT|${r.id}|${court.name}|${fmtDate(date)}|${pad(r.startHour)}:00-${pad(r.endHour)}:00`;

  let actionHtml;
  if(state.showReturnForm){
    actionHtml = `
      <div class="return-panel">
        <div class="return-panel-title">Poproś o zwrot rezerwacji</div>
        <div class="auth-field">
          <label for="returnReasonSelect">Powód zwrotu</label>
          <select id="returnReasonSelect">
            <option value="">Wybierz powód</option>
            <option value="plans">Zmiana planów</option>
            <option value="injury">Kontuzja lub choroba</option>
            <option value="weather">Warunki pogodowe</option>
            <option value="mistake">Błąd przy rezerwacji terminu</option>
            <option value="other">Inny powód</option>
          </select>
        </div>
        <div class="auth-field">
          <label for="returnNoteInput">Dodatkowe informacje (opcjonalnie)</label>
          <textarea id="returnNoteInput" rows="3" placeholder="Opisz szczegóły, jeśli to pomoże obsłudze..."></textarea>
        </div>
        ${state.returnFormError ? `<div class="auth-error">${state.returnFormError}</div>` : ''}
        <div class="return-panel-actions">
          <button class="res-cancel" data-action="cancel-return-form">Wróć</button>
          <button class="edit-btn" data-action="submit-return" data-id="${r.id}">Wyślij prośbę o zwrot</button>
        </div>
      </div>
    `;
  } else if(rr && rr.status==='pending'){
    actionHtml = `
      <div class="return-status pending">
        <strong>Zwrot w trakcie rozpatrywania</strong>
        <div style="margin-top:6px;">Powód: ${returnReasonLabel(rr.reason)}${rr.note ? ' — '+rr.note : ''}</div>
        <div class="return-status-note">Obsługa klubu musi potwierdzić zwrot środków.</div>
      </div>
    `;
  } else {
    actionHtml = `
      ${rr && rr.status==='rejected' ? `<div class="return-status rejected"><strong>Poprzednia prośba o zwrot została odrzucona przez obsługę.</strong></div>` : ''}
      <button class="res-cancel" style="margin-top:20px;" data-action="request-return">Zwróć rezerwację</button>
    `;
  }

  return `
    <button class="back-link" data-action="back-to-list">← Moje rezerwacje</button>
    <div><span class="detail-success-badge">✓ Opłacona</span></div>
    <h2 class="section-title" style="margin-top:6px;">Szczegóły rezerwacji</h2>
    <div class="payment-summary">
      <div class="payment-row"><span>Kort</span><strong>${court.name} · ${court.surfaceLabel}</strong></div>
      <div class="payment-row"><span>Termin</span><strong>${fmtDate(date)}, ${pad(r.startHour)}:00–${pad(r.endHour)}:00</strong></div>
      <div class="payment-row"><span>Czas trwania</span><strong>${r.endHour - r.startHour} godz.</strong></div>
      <div class="payment-row"><span>Metoda płatności</span><strong>Szybki przelew${r.bank ? ' — '+r.bank : ''}</strong></div>
      <div class="payment-row"><span>Numer rezerwacji</span><strong>${r.id}</strong></div>
      <div class="payment-row total"><span>Zapłacono</span><strong>${r.price} zł</strong></div>
    </div>

    <div class="qr-section">
      <div class="qr-label">Kod wstępu na kort — zeskanuj przy wejściu</div>
      <img class="qr-code" src="https://api.qrserver.com/v1/create-qr-code/?size=200x200&margin=10&data=${encodeURIComponent(qrPayload)}" width="200" height="200" alt="Kod QR rezerwacji ${r.id}">
      <div class="qr-id">#${r.id}</div>
    </div>

    ${actionHtml}
  `;
}