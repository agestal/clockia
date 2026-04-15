export const DAYS_ES = ['Lun', 'Mar', 'Mié', 'Jue', 'Vie', 'Sáb', 'Dom'];
export const MONTHS_ES = [
    'Enero', 'Febrero', 'Marzo', 'Abril', 'Mayo', 'Junio',
    'Julio', 'Agosto', 'Septiembre', 'Octubre', 'Noviembre', 'Diciembre',
];

export function pad(n) {
    return n < 10 ? '0' + n : String(n);
}

export function formatDateISO(year, month, day) {
    return `${year}-${pad(month)}-${pad(day)}`;
}

export function formatDateHuman(isoString) {
    const [y, m, d] = isoString.split('-').map(Number);
    return `${d} de ${MONTHS_ES[m - 1].toLowerCase()} de ${y}`;
}

export function formatPrice(value, currency = 'EUR') {
    const num = typeof value === 'number' ? value : parseFloat(value);
    if (!isFinite(num)) return '';
    try {
        return new Intl.NumberFormat('es-ES', { style: 'currency', currency }).format(num);
    } catch (e) {
        return num.toFixed(2) + ' ' + currency;
    }
}

export function h(tag, attrs = {}, children = []) {
    const el = document.createElement(tag);
    Object.entries(attrs || {}).forEach(([key, value]) => {
        if (value === null || value === undefined || value === false) return;
        if (key === 'class') {
            el.className = value;
        } else if (key === 'style' && typeof value === 'object') {
            Object.assign(el.style, value);
        } else if (key.startsWith('on') && typeof value === 'function') {
            el.addEventListener(key.slice(2).toLowerCase(), value);
        } else if (key === 'html') {
            el.innerHTML = value;
        } else {
            el.setAttribute(key, value);
        }
    });
    (Array.isArray(children) ? children : [children]).forEach((child) => {
        if (child === null || child === undefined || child === false) return;
        el.appendChild(typeof child === 'string' ? document.createTextNode(child) : child);
    });
    return el;
}

export function clearElement(el) {
    while (el.firstChild) el.removeChild(el.firstChild);
}
