import {state} from "../../state.js";
import { pad } from "../../utils.js";

export function renderOverlay(){
  if(!state.overlay) return '';
  const ov = state.overlay;
  const processing = ov.stage === 'processing';

  return `
    <div class="pay-overlay" role="alert" aria-live="assertive">
      <div class="lock-wrap">
        ${processing ? '<div class="spinner-ring"></div>' : ''}
        <svg class="lock-svg ${processing?'':'unlocked'}" viewBox="0 0 120 140" width="100" height="118">
          <path class="lock-shackle" d="M35 60 V38 a25 25 0 0 1 50 0 V60" fill="none" stroke="#EAE2CF" stroke-width="10" stroke-linecap="round"/>
          <rect class="lock-body" x="18" y="58" width="84" height="66" rx="10" fill="${processing?'#EAE2CF':'#9FC7AE'}"/>
          <circle cx="60" cy="86" r="7" fill="#163829"/>
          <rect x="56" y="90" width="8" height="18" rx="3" fill="#163829"/>
        </svg>
        ${!processing ? '<div class="check-badge">✓</div>' : ''}
      </div>
      <div class="pay-status-text">${processing ? 'Przetwarzanie płatności…' : 'Płatność zakończona sukcesem!'}</div>
      <div class="pay-status-sub">
        ${processing
          ? `${ov.courtName}, ${pad(ov.from)}:00–${pad(ov.to)}:00 · ${ov.price} zł`
          : `Rezerwacja — ${ov.courtName}, ${pad(ov.from)}:00–${pad(ov.to)}:00 została potwierdzona.`}
      </div>
      ${!processing ? '<button class="overlay-cta" data-action="skip-to-details">Zobacz szczegóły rezerwacji</button>' : ''}
    </div>
  `;
}