import { state } from "../../../state.js";
import { 
  fmtDate,
  fmtShortDate,
  courtClass,
  courtTag,
  returnReasonLabel,
  myReservationAt,
  isHourFree,
  nextFreeSlotLabel 
} from "../../helpers.js";

export function renderKortyGrid(){
  return `
    <h2 class="section-title">Korty</h2>
    <p class="section-sub">Wybierz kort, aby zobaczyć dostępne terminy.</p>
    <div class="court-grid">
      ${state.courts.map(c => `
        <div class="court-card ${courtClass(c.surface)}">
          <div class="court-num">${c.id}</div>
          <div class="court-name">${c.name}</div>
          <div class="court-surface">${c.surfaceLabel} · ${c.price} zł/h</div>
          <div class="court-next">Najbliższy wolny termin: ${nextFreeSlotLabel(c)}</div>
          <button class="court-cta" data-action="open-court" data-id="${c.id}">Zobacz terminy</button>
        </div>
      `).join('')}
    </div>
  `;
}