import {state} from "../../../state.js";
import { fmtShortDate } from "../../helpers.js";

export function renderHistoria(){
  const txs = state.transactions.slice().sort((a,b)=> b.dateStr.localeCompare(a.dateStr));
  if(state.auth.user != null){
    return `
    <div>
      ${txs.map(t => `
        <div class="tx-row">
          <div class="tx-date">${fmtShortDate(new Date(t.dateStr))}</div>
          <div class="tx-desc">${t.desc}</div>
          <div class="tx-status ${t.status}">${t.status==='done' ? 'Zrealizowana' : 'Anulowana'}</div>
          <div class="tx-amount">${t.amount} zł</div>
        </div>
      `).join('')}
    </div>
  `;
  }
  else{
    return `
    <div>
     <span>Brak historii rezerwacji</span>
    </div>
  `;
  }
}