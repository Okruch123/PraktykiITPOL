import { state } from "../../state.js";
import { DATES, pad } from "../../utils.js";
import { fmtDate } from "../helpers.js";
import { render } from "../render.js";

// Udostępniamy funkcję globalnie w oknie przeglądarki
window.handlePayP24 = async function handlePayP24() {
  const pb = state.pendingPayment;

  if (!pb || !pb.price) {
    alert("Brak danych o płatności.");
    return;
  }
  const courts = await state.courts;
  const court = courts.find(c => c.id === pb.courtId);

  // Zapisujemy dane transakcji w pamięci sesji przeglądarki
  sessionStorage.setItem('p24_pending_payment', JSON.stringify({
    ...pb,
    courtName: court ? court.name : 'Kort'
  }));

  // Włączamy overlay przetwarzania
  state.overlay = {
    stage: 'processing',
    courtName: court ? court.name : 'Kort',
    from: pb.from,
    to: pb.to,
    price: pb.price
  };

  render();

  const MIN_SHOW_TIME = 1200;
  const timerPromise = new Promise(resolve => setTimeout(resolve, MIN_SHOW_TIME));

  try {
    const fetchPromise = fetch('PHP/p24/create_payment.php', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ amount: pb.price })
    }).then(res => res.json());

    const [_, data] = await Promise.all([timerPromise, fetchPromise]);

    if (data.url) {
      window.location.href = data.url;
    } else {
      state.overlay = null;
      render();
      alert("Błąd płatności: " + (data.error || "Nieznany błąd"));
    }
  } catch (err) {
    state.overlay = null;
    render();
    console.error("Błąd połączenia z PHP:", err);
    alert("Nie udało się połączyć z serwerem płatności.");
  }
};

export async function renderPayment(){
  const pb = state.pendingPayment;
  const courts = await state.courts;
  const court = courts.find(c => c.id === pb.courtId);
  const date = DATES[pb.dateIndex];

  return `
    <button class="back-link" data-action="cancel-payment">← Wróć do wyboru terminu</button>
    <h2 class="section-title">Płatność</h2>
    <p class="section-sub">Dokończ rezerwację, płacąc przez Przelewy24.</p>

    <div class="payment-summary">
      <div class="payment-row"><span>Kort</span><strong>${court.name} · ${court.surfaceLabel}</strong></div>
      <div class="payment-row"><span>Termin</span><strong>${fmtDate(date)}, ${pad(pb.from)}:00–${pad(pb.to)}:00</strong></div>
      <div class="payment-row"><span>Czas trwania</span><strong>${pb.to - pb.from} godz.</strong></div>
      <div class="payment-row total"><span>Do zapłaty</span><strong>${pb.price} zł</strong></div>
    </div>

    <div class="p24-container">
      <p class="pay-sub">Zostaniesz przekierowany do bezpiecznej płatności (BLIK, przelew online, karta).</p>
      <button class="pay-btn" onclick="window.handlePayP24()">Zapłać ${pb.price} zł przez Przelewy24</button>
    </div>
  `;
}