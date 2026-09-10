import { state } from "../../state.js";
import { pad } from "../../utils.js";

export function renderOverlay(){
  if(!state.overlay) return '';
  const ov = state.overlay;
  
  const isProcessing = ov.stage === 'processing';
  const isSuccess = ov.stage === 'success';
  const isError = ov.stage === 'error';

  return `
    <div class="pay-overlay" role="alert" aria-live="assertive">
      <div class="lock-wrap">
        ${isProcessing ? '<div class="spinner-ring"></div>' : ''}
        
        <svg class="lock-svg ${isProcessing ? '' : 'unlocked'}" viewBox="0 0 120 140" width="100" height="118">
          <path class="lock-shackle" d="M35 60 V38 a25 25 0 0 1 50 0 V60" fill="none" stroke="#EAE2CF" stroke-width="10" stroke-linecap="round"/>
          <rect class="lock-body" x="18" y="58" width="84" height="66" rx="10" fill="${isProcessing ? '#EAE2CF' : (isSuccess ? '#9FC7AE' : '#E74C3C')}"/>
          <circle cx="60" cy="86" r="7" fill="#163829"/>
          <rect x="56" y="90" width="8" height="18" rx="3" fill="#163829"/>
        </svg>

        ${isSuccess ? '<div class="check-badge">✓</div>' : ''}
        ${isError ? '<div class="check-badge error-badge">✕</div>' : ''}
      </div>

      <div class="pay-status-text">
        ${isProcessing ? 'Przetwarzanie płatności…' : ''}
        ${isSuccess ? 'Płatność zakończona sukcesem!' : ''}
        ${isError ? 'Płatność nie powiodła się' : ''}
      </div>

      <div class="pay-status-sub">
        ${isProcessing ? `${ov.courtName || 'Kort'}, ${pad(ov.from || 0)}:00–${pad(ov.to || 0)}:00 · ${ov.price || 0} zł` : ''}
        ${isSuccess ? `Rezerwacja — ${ov.courtName || 'Kort'} została pomyślnie potwierdzona.` : ''}
        ${isError ? 'Transakcja została anulowana lub odrzucona. Spróbuj ponownie.' : ''}
      </div>

      ${!isProcessing ? `
        <button class="overlay-cta" data-action="${isSuccess ? 'skip-to-details' : 'close-overlay'}">
          ${isSuccess ? 'Zobacz szczegóły rezerwacji' : 'Zamknij i spróbuj ponownie'}
        </button>
      ` : ''}
    </div>
  `;
}