import { state, BANKS } from "../../state.js";
import { DATES, pad } from "../../utils.js";
import { fmtDate } from "../helpers.js";

export function renderPayment(){
  const pb = state.pendingPayment;
  const court = state.courts.find(c=>c.id===pb.courtId);
  const date = DATES[pb.dateIndex];

  return `
    <button class="back-link" data-action="cancel-payment">← Wróć do wyboru terminu</button>
    <h2 class="section-title">Płatność</h2>
    <p class="section-sub">Dokończ rezerwację, płacąc szybkim przelewem.</p>

    <div class="payment-summary">
      <div class="payment-row"><span>Kort</span><strong>${court.name} · ${court.surfaceLabel}</strong></div>
      <div class="payment-row"><span>Termin</span><strong>${fmtDate(date)}, ${pad(pb.from)}:00–${pad(pb.to)}:00</strong></div>
      <div class="payment-row"><span>Czas trwania</span><strong>${pb.to-pb.from} godz.</strong></div>
      <div class="payment-row total"><span>Do zapłaty</span><strong>${pb.price} zł</strong></div>
    </div>

    <h3 class="pay-heading">Szybkie przelewy</h3>
    <p class="pay-sub">Wybierz swój bank, aby dokonać płatności online.</p>
    <div class="bank-grid">
      ${BANKS.map(b => `
        <button class="bank-btn ${state.selectedBank===b?'active':''}" data-action="select-bank" data-bank="${b}">${b}</button>
      `).join('')}
    </div>

    <button class="pay-btn" data-action="pay" ${state.selectedBank?'':'disabled'}>Zapłać ${pb.price} zł</button>
    <p class="pay-note">To symulacja płatności na potrzeby demo — środki nie zostaną pobrane.</p>
  `;
}