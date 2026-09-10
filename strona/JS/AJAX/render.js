import { state } from '../state.js';

import { renderHeader } from './podstrony/header.js';
import { renderOverlay } from './podstrony/overlay.js';
import { renderAuthOverlay } from './podstrony/authOverlay.js';
import { renderPayment } from './podstrony/payments.js';

import { renderKorty } from './podstrony/courts/courts.js';
import { renderRezerwacje } from './podstrony/reservations/reservations.js';
import { renderReservationDetail } from './podstrony/reservations/reservationDetail.js';

import { renderKonto } from './podstrony/profile/account.js';

import { renderObsluga } from './podstrony/admin/service.js';

export async function render(){
    if(
        state.tab === 'obsluga' &&
        !(state.auth.loggedIn && state.auth.user.isAdmin)
    ){
        state.tab = 'rezerwacje';
    }

    let body;

    if(state.pendingPayment){
        body = await renderPayment();
    }
    else if(state.tab === 'korty'){
        body = await renderKorty();
    }
    else if(state.tab === 'konto'){
        body = renderKonto();
    }
    else if(state.tab === 'obsluga'){
        body = renderObsluga();
    }
    else{
        body = state.viewingReservationId
            ? renderReservationDetail(state.viewingReservationId)
            : renderRezerwacje();

    }

    document.getElementById('app').innerHTML = `
        ${renderHeader()}
        <div class="app">
            ${state.toast
                ? `<div class="toast">${state.toast}</div>`
                : ''
            }
            ${body}
        </div>

        ${renderOverlay()}

        ${state.auth.view
            ? renderAuthOverlay()
            : ''
        }
    `;
}