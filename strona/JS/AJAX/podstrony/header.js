import {state} from "../../state.js";
export function renderHeader(){
  const tabs = [
    {id:'korty', label:'Korty'},
    {id:'rezerwacje', label:'Moje rezerwacje'},
    {id:'konto', label:'Konto'}
  ];
  if(state.auth.loggedIn && state.auth.user.isAdmin){
    tabs.push({id:'obsluga', label:'Obsługa'});
  }
  return `
    <div class="topbar">
      <nav class="tabs">
        ${tabs.map(t => `<button class="tab-btn ${state.tab===t.id?'active':''}" data-action="set-tab" data-tab="${t.id}">${t.label}</button>`).join('')}
      </nav>
    </div>
    <header class="site-header">
      <div class="header-top">
        <div class="wordmark">SET<span>POINT</span></div>
        <div class="header-auth">
          ${state.auth.loggedIn
            ? `<span class="header-auth-user">Zalogowano: ${state.auth.user.login}${state.auth.user.isAdmin ? ' (obsługa)' : ''}</span><button class="header-auth-btn" data-action="logout">Wyloguj</button>`
            : `<button class="header-auth-btn" data-action="open-auth" data-mode="login">Zaloguj się</button>`}
        </div>
      </div>
      <p class="tagline" style="max-width:920px;margin:0 auto 18px;">Zarezerwuj kort tenisowy na wybraną godzinę.</p>
    </header>
  `;
}