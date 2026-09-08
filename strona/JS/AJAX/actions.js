import { state } from '../state.js';

import {
    DATES,
    TODAY,
    pad,
    toDateStr
} from '../utils.js';

import { render } from './render.js';

let overlayTimer = null;

function goToReservationDetail(id){
  state.overlay = null;
  state.tab = 'rezerwacje';
  state.viewingReservationId = id;
  state.selectedCourtId = null;
  state.pick = { from:null, to:null };
  state.showReturnForm = false;
  state.returnFormError = null;
  render();
}

function showToast(msg){
  state.toast = msg;
  render();
  setTimeout(()=>{ state.toast = null; render(); }, 3200);
}

function goToPaymentFlow(){
  const court = state.courts.find(c=>c.id===state.selectedCourtId);
  const from = state.pick.from;
  const to = state.pick.to;
  state.pendingPayment = {
    courtId: court.id,
    dateIndex: state.selectedDateIndex,
    from, to,
    price: (to-from) * court.price
  };
  state.selectedBank = null;
}

document.getElementById('app').addEventListener('click', (e) => {
  const btn = e.target.closest('[data-action]');
  if(!btn) return;
  const action = btn.dataset.action;

  if(action==='set-tab'){
    state.tab = btn.dataset.tab;
    if(state.tab==='korty') state.selectedCourtId = null;
    state.pendingPayment = null;
    state.selectedBank = null;
    state.viewingReservationId = null;
    state.showReturnForm = false;
    state.returnFormError = null;
  }
  else if(action==='open-court'){
    state.selectedCourtId = Number(btn.dataset.id);
    state.selectedDateIndex = 0;
    state.pick = { from:null, to:null };
  }
  else if(action==='back-to-korty'){
    state.selectedCourtId = null;
    state.pick = { from:null, to:null };
  }
  else if(action==='set-date'){
    state.selectedDateIndex = Number(btn.dataset.index);
    state.pick = { from:null, to:null };
  }
  else if(action==='quick-pick'){
    const h = Number(btn.dataset.hour);
    state.pick.from = h;
    state.pick.to = h+1;
  }
  else if(action==='go-to-payment'){
    if(!state.auth.loggedIn){
      state.auth.view = 'login';
      state.auth.error = null;
      state.auth.resumeToPayment = true;
    } else {
      goToPaymentFlow();
    }
  }
  else if(action==='cancel-payment'){
    state.pendingPayment = null;
    state.selectedBank = null;
  }
  else if(action==='select-bank'){
    state.selectedBank = btn.dataset.bank;
  }
  else if(action==='pay'){
    const pb = state.pendingPayment;
    const court = state.courts.find(c=>c.id===pb.courtId);
    const bank = state.selectedBank;

    state.overlay = {
      stage:'processing',
      courtName: court.name, from: pb.from, to: pb.to, price: pb.price
    };
    render();

    clearTimeout(overlayTimer);
    overlayTimer = setTimeout(() => {
      const id = 'r'+Date.now();
      state.reservations.push({
        id, courtId: pb.courtId, dateStr: toDateStr(DATES[pb.dateIndex]), dateIndex: pb.dateIndex,
        startHour: pb.from, endHour: pb.to, price: pb.price, bank
      });
      state.transactions.unshift({
        id:'t'+Date.now(),
        dateStr: toDateStr(TODAY),
        desc:`Rezerwacja — ${court.name}, ${pad(pb.from)}:00–${pad(pb.to)}:00`,
        amount: pb.price,
        status:'done'
      });
      state.pendingPayment = null;
      state.selectedBank = null;
      state.overlay = { stage:'success', courtName: court.name, from: pb.from, to: pb.to, price: pb.price, reservationId: id };
      render();

      overlayTimer = setTimeout(() => goToReservationDetail(id), 1600);
    }, 1500);
    return;
  }
  else if(action==='skip-to-details'){
    clearTimeout(overlayTimer);
    goToReservationDetail(state.overlay.reservationId);
    return;
  }
  else if(action==='view-details'){
    state.viewingReservationId = btn.dataset.id;
    state.showReturnForm = false;
    state.returnFormError = null;
  }
  else if(action==='back-to-list'){
    state.viewingReservationId = null;
    state.showReturnForm = false;
    state.returnFormError = null;
  }
  else if(action==='goto-rezerwacje'){
    state.tab = 'rezerwacje';
  }
  else if(action==='open-return'){
    state.viewingReservationId = btn.dataset.id;
    state.showReturnForm = true;
    state.returnFormError = null;
  }
  else if(action==='request-return'){
    state.showReturnForm = true;
    state.returnFormError = null;
  }
  else if(action==='cancel-return-form'){
    state.showReturnForm = false;
    state.returnFormError = null;
  }
  else if(action==='submit-return'){
    const reason = document.getElementById('returnReasonSelect').value;
    const note = document.getElementById('returnNoteInput').value.trim();
    if(!reason){
      state.returnFormError = 'Wybierz powód zwrotu.';
    } else {
      const r = state.reservations.find(x=>x.id===btn.dataset.id);
      if(r){
        r.returnRequest = { reason, note, status:'pending', requestedAt: toDateStr(new Date()) };
      }
      state.showReturnForm = false;
      state.returnFormError = null;
      showToast('Prośba o zwrot została wysłana do obsługi.');
      return;
    }
  }
  else if(action==='approve-return'){
    const r = state.reservations.find(x=>x.id===btn.dataset.id);
    if(r){
      const court = state.courts.find(c=>c.id===r.courtId);
      state.reservations = state.reservations.filter(x=>x.id!==r.id);
      if(state.viewingReservationId===r.id) state.viewingReservationId = null;
      state.transactions.unshift({
        id:'t'+Date.now(),
        dateStr: toDateStr(TODAY),
        desc:`Zwrot zatwierdzony — ${court.name}, ${pad(r.startHour)}:00–${pad(r.endHour)}:00`,
        amount: r.price,
        status:'cancelled'
      });
      showToast('Zwrot został zatwierdzony.');
      return;
    }
  }
  else if(action==='reject-return'){
    const r = state.reservations.find(x=>x.id===btn.dataset.id);
    if(r && r.returnRequest){
      r.returnRequest.status = 'rejected';
      showToast('Prośba o zwrot została odrzucona.');
      return;
    }
  }
  else if(action==='open-auth'){
    state.auth.view = btn.dataset.mode || 'login';
    state.auth.error = null;
  }
  else if(action==='switch-auth'){
    state.auth.view = btn.dataset.mode;
    state.auth.error = null;
  }
  else if(action==='close-auth'){
    state.auth.view = null;
    state.auth.error = null;
    state.auth.resumeToPayment = false;
  }
  else if(action==='do-login'){
    const login = document.getElementById('authLoginInput').value.trim();
    const password = document.getElementById('authPasswordInput').value;
    const user = state.users.find(u => u.login.toLowerCase()===login.toLowerCase() && u.password===password);
    if(!login || !password){
      state.auth.error = 'Podaj login i hasło.';
    } else if(!user){
      state.auth.error = 'Nieprawidłowy login lub hasło.';
    } else {
      state.auth.loggedIn = true;
      state.auth.user = { login: user.login, email: user.email, isAdmin: !!user.isAdmin };
      state.auth.error = null;
      const resume = state.auth.resumeToPayment;
      state.auth.view = null;
      state.auth.resumeToPayment = false;
      if(resume) goToPaymentFlow();
      showToast('Zalogowano jako ' + user.login + '.');
      return;
    }
  }
  else if(action==='do-register'){
    const email = document.getElementById('authEmailInput').value.trim();
    const login = document.getElementById('authRegLoginInput').value.trim();
    const password = document.getElementById('authRegPasswordInput').value;
    const confirm = document.getElementById('authRegConfirmInput').value;
    if(!email || !login || !password || !confirm){
      state.auth.error = 'Wypełnij wszystkie pola.';
    } else if(password !== confirm){
      state.auth.error = 'Hasła nie są identyczne.';
    } else if(state.users.some(u => u.login.toLowerCase()===login.toLowerCase())){
      state.auth.error = 'Ten login jest już zajęty.';
    } else {
      state.users.push({ login, password, email, isAdmin:false });
      state.auth.loggedIn = true;
      state.auth.user = { login, email, isAdmin:false };
      state.auth.error = null;
      const resume = state.auth.resumeToPayment;
      state.auth.view = null;
      state.auth.resumeToPayment = false;
      if(resume) goToPaymentFlow();
      showToast('Konto zostało utworzone. Witaj, ' + login + '!');
      return;
    }
  }
  else if(action==='logout'){
    state.auth.loggedIn = false;
    state.auth.user = null;
    showToast('Wylogowano.');
    return;
  }
  else if(action==='set-konto-sub'){
    state.kontoSub = btn.dataset.sub;
    state.editingProfile = false;
  }
  else if(action==='edit-profile'){
    state.editingProfile = true;
  }
  else if(action==='save-profile'){
    document.querySelectorAll('[data-field]').forEach(input=>{
      state.profile[input.dataset.field] = input.value;
    });
    state.editingProfile = false;
    showToast('Zmiany w profilu zostały zapisane.');
    return;
  }

  render();
});

document.getElementById('app').addEventListener('change', (e) => {
  const el = e.target.closest('[data-action]');
  if(!el) return;
  const action = el.dataset.action;

  if(action==='pick-from'){
    const v = el.value;
    state.pick.from = v==='' ? null : Number(v);
    state.pick.to = v==='' ? null : Number(v)+1;
  }
  else if(action==='pick-to'){
    state.pick.to = Number(el.value);
  }

  render();
});

document.getElementById('app').addEventListener('keydown', (e) => {
  if(e.key !== 'Enter') return;
  const id = e.target.id;
  if(id==='authLoginInput' || id==='authPasswordInput'){
    e.preventDefault();
    document.querySelector('[data-action="do-login"]')?.click();
  } else if(id==='authEmailInput' || id==='authRegLoginInput' || id==='authRegPasswordInput' || id==='authRegConfirmInput'){
    e.preventDefault();
    document.querySelector('[data-action="do-register"]')?.click();
  }
});