const DAY_NAMES = ['Ndz','Pon','Wt','Śr','Czw','Pt','Sob'];

const MONTH_NAMES = ['sty','lut','mar','kwi','maj','cze','lip','sie','wrz','paź','lis','gru'];

function pad(n){
    return n < 10 ? '0' + n : '' + n;
}

function toDateStr(d){
    return d.getFullYear() + '-' + pad(d.getMonth() + 1) + '-' + pad(d.getDate());
}

function addDays(base, n){
    const d = new Date(base);
    d.setDate(d.getDate() + n);
    return d;
}

const TODAY = new Date();

TODAY.setHours(0, 0, 0, 0);

const NOW_HOUR = new Date().getHours();

const DATES = Array.from(
    { length: 7 },
    (_, i) => addDays(TODAY, i)
);

export {
    DAY_NAMES,
    MONTH_NAMES,
    pad,
    toDateStr,
    addDays,
    TODAY,
    NOW_HOUR,
    DATES
};