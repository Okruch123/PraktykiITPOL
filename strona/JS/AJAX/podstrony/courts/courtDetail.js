import {
  state,
    HOURS,
    BANKS,
    isOccupiedByOthers
  } from "../../../state.js";
import {
  DAY_NAMES,
    MONTH_NAMES,
    pad,
    toDateStr,
    addDays,
    TODAY,
    NOW_HOUR,
    DATES
  } from "../../../utils.js"
  import {
    fmtDate,
    fmtShortDate,
    courtClass,
    courtTag,
    returnReasonLabel,
    myReservationAt,
    isHourFree,
    nextFreeSlotLabel
  } from "../../helpers.js"

export function renderCourtDetail(){
  const court = state.courts.find(c=>c.id===state.selectedCourtId);
  const di = state.selectedDateIndex;
  const date = DATES[di];

  const fromOptions = HOURS.filter(h => isHourFree(court.id, di, h));
  if(state.pick.from!==null && !fromOptions.includes(state.pick.from)){
    state.pick.from = null;
    state.pick.to = null;
  }

  let toOptions = [];
  if(state.pick.from!==null){
    let h = state.pick.from;
    while(HOURS.includes(h) && isHourFree(court.id, di, h)){ toOptions.push(h+1); h++; }
    if(!toOptions.includes(state.pick.to)) state.pick.to = toOptions[0];
  }

  const rows = HOURS.map(h => {
    const isPast = di===0 && h<=NOW_HOUR;
    const mine = myReservationAt(court.id, di, h);
    const free = !isPast && !mine && isHourFree(court.id, di, h);
    const takenByOther = !isPast && !mine && !free;
    const inRange = state.pick.from!==null && state.pick.to!==null && h>=state.pick.from && h<state.pick.to;

    let statusHtml, btnHtml;
    if(isPast){
      statusHtml = `<span class="slot-status taken">minął</span>`;
      btnHtml = `<button class="slot-btn disabled" disabled>Niedostępne</button>`;
    } else if(mine){
      statusHtml = `<span class="slot-status mine">Twoja rezerwacja</span>`;
      btnHtml = `<button class="slot-btn mine" data-action="goto-rezerwacje">Zobacz</button>`;
    } else if(takenByOther){
      statusHtml = `<span class="slot-status taken">zajęte</span>`;
      btnHtml = `<button class="slot-btn disabled" disabled>Zajęte</button>`;
    } else {
      statusHtml = `<span class="slot-status free">wolne</span>`;
      btnHtml = `<button class="slot-btn book" data-action="quick-pick" data-hour="${h}">Wybierz</button>`;
    }

    return `
      <div class="slot-row ${inRange?'in-range':''}">
        <div class="slot-time">${pad(h)}:00–${pad(h+1)}:00${isPast?'<span class="past-flag">dziś, minęło</span>':''}</div>
        <div class="slot-price">${court.price} zł</div>
        ${statusHtml}
        ${btnHtml}
      </div>
    `;
  }).join('');

  const hours = (state.pick.from!==null && state.pick.to!==null) ? state.pick.to - state.pick.from : 0;
  const price = hours * court.price;

  const pickerHtml = `
    <div class="picker-panel">
      <div class="picker-title">Zarezerwuj termin</div>
      <div class="picker-fields">
        <div class="picker-field">
          <label for="fromSelect">Od godziny</label>
          <select id="fromSelect" data-action="pick-from">
            <option value="" ${state.pick.from===null?'selected':''}>Wybierz</option>
            ${fromOptions.map(h=>`<option value="${h}" ${state.pick.from===h?'selected':''}>${pad(h)}:00</option>`).join('')}
          </select>
        </div>
        <div class="picker-field">
          <label for="toSelect">Do godziny</label>
          <select id="toSelect" data-action="pick-to" ${state.pick.from===null?'disabled':''}>
            ${toOptions.map(h=>`<option value="${h}" ${state.pick.to===h?'selected':''}>${pad(h)}:00</option>`).join('')}
          </select>
        </div>
      </div>
      ${state.pick.from!==null ? `
        <div class="picker-summary">
          <div class="picker-summary-text">
            ${court.name}, ${fmtDate(date)}, ${pad(state.pick.from)}:00–${pad(state.pick.to)}:00 · ${hours} godz.
            <strong>${price} zł</strong>
          </div>
          <button class="picker-cta" data-action="go-to-payment">Przejdź do płatności</button>
        </div>
      ` : `<p class="picker-hint" style="margin:14px 0 0;">Wybierz godzinę rozpoczęcia — z listy powyżej albo klikając wolny termin poniżej.</p>`}
    </div>
  `;

  return `
    <button class="back-link" data-action="back-to-korty">← Wszystkie korty</button>
    <div class="detail-head">
      <div class="detail-num">${court.id}</div>
      <div>
        <h2>${court.name}</h2>
        <div class="detail-meta">${court.surfaceLabel} · ${court.price} zł za godzinę</div>
      </div>
    </div>

    <div class="date-tabs">
      ${DATES.map((d,i) => `
        <button class="date-tab ${i===di?'active':''}" data-action="set-date" data-index="${i}">
          <span class="dname">${DAY_NAMES[d.getDay()]}</span>
          <span class="dnum">${d.getDate()}</span>
        </button>
      `).join('')}
    </div>
    <div class="detail-meta" style="margin-top:6px;">${fmtDate(date)}</div>

    ${pickerHtml}

    <div class="slot-list">
      ${rows}
    </div>
  `;
}