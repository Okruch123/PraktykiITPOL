import {state} from "../../../state.js";
import {renderProfil} from "./profile.js";
import {renderHistoria} from "./history.js";

export function renderKonto(){
  return `
    <h2 class="section-title">Konto</h2>
    <div class="konto-subtabs">
      <button class="konto-subtab ${state.kontoSub==='profil'?'active':''}" data-action="set-konto-sub" data-sub="profil">Profil</button>
      <button class="konto-subtab ${state.kontoSub==='historia'?'active':''}" data-action="set-konto-sub" data-sub="historia">Historia transakcji</button>
    </div>
    ${state.kontoSub==='profil' ? renderProfil() : renderHistoria()}
  `;
}