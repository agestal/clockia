import { h, clearElement, DAYS_ES, MONTHS_ES, formatDateISO, pad } from '../utils.js';

export function renderCalendar({ container, year, month, days, selectedDate, onPrev, onNext, onSelect, todayIso }) {
    clearElement(container);

    const nav = h('div', { class: 'ck-calendar-nav' }, [
        h('button', { class: 'ck-btn', type: 'button', onclick: onPrev, 'aria-label': 'Mes anterior' }, '\u2039'),
        h('div', { class: 'ck-month-label' }, `${MONTHS_ES[month - 1]} ${year}`),
        h('button', { class: 'ck-btn', type: 'button', onclick: onNext, 'aria-label': 'Mes siguiente' }, '\u203a'),
    ]);
    container.appendChild(nav);

    const grid = h('div', { class: 'ck-calendar', role: 'grid' });
    DAYS_ES.forEach((dow) => grid.appendChild(h('div', { class: 'ck-dow' }, dow)));

    const firstDay = new Date(year, month - 1, 1);
    // JS: Sunday = 0. We want Monday = 0.
    const offset = (firstDay.getDay() + 6) % 7;
    const lastDay = new Date(year, month, 0).getDate();

    for (let i = 0; i < offset; i++) {
        grid.appendChild(h('div', { class: 'ck-day ck-empty' }));
    }

    const daysByDate = Object.fromEntries(days.map((d) => [d.date, d]));

    for (let d = 1; d <= lastDay; d++) {
        const iso = formatDateISO(year, month, d);
        const info = daysByDate[iso] || { available: false, is_past: false };
        const classes = ['ck-day'];
        if (info.is_past || !info.available) classes.push('ck-unavailable');
        else classes.push('ck-available');
        if (iso === selectedDate) classes.push('ck-selected');
        if (iso === todayIso) classes.push('ck-today');

        const attrs = { class: classes.join(' '), 'data-date': iso, role: 'gridcell' };
        if (info.available && !info.is_past) {
            attrs.onclick = () => onSelect(iso);
            attrs.tabindex = '0';
        }
        grid.appendChild(h('div', attrs, String(d)));
    }

    container.appendChild(grid);
}
