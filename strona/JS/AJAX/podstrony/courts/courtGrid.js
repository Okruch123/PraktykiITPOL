import { state } from "../../../state.js";
import { 
  fmtDate,
  fmtShortDate,
  courtTag,
  returnReasonLabel,
  myReservationAt,
  isHourFree,
  nextFreeSlotLabel 
} from "../../helpers.js";

export async function renderKortyGrid(){
  const courts = await state.courts;

  return `
    <h2 class="section-title">Korty</h2>
    <p class="section-sub">Wybierz kort, aby zobaczyć dostępne terminy.</p>
    <div class="court-grid">
      ${courts.map( court => `
        <div class="court-card ${court.surface}">
          <div class="court-num">${court.id}</div>
          <div class="court-name">${court.name}</div>
          <div class="court-surface">${court.surfaceLabel} · ${court.price} zł/h</div>
          <div class="court-next">Najbliższy wolny termin: ${nextFreeSlotLabel(court)}</div>
          <button class="court-cta" data-action="open-court" data-id="${court.id}">Zobacz terminy</button>
        </div>
      `).join('')
    }
    </div>
  `;
}