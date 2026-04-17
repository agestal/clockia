export function buildStyles(theme) {
    return `
:host {
    --ck-primary: ${theme.primary_color};
    --ck-secondary: ${theme.secondary_color};
    --ck-text: ${theme.text_color};
    --ck-bg: ${theme.background_color};
    --ck-font: ${theme.font_family};
    --ck-font-size: ${theme.font_size_base};
    --ck-radius: ${theme.border_radius};
    --ck-border: #e5e5e5;
    --ck-muted: #6b7280;
    --ck-danger: #b91c1c;
    --ck-success: #15803d;
    display: block;
    font-family: var(--ck-font);
    font-size: var(--ck-font-size);
    color: var(--ck-text);
    box-sizing: border-box;
}
*, *::before, *::after { box-sizing: border-box; }

.ck-root {
    background: var(--ck-bg);
    border: 1px solid var(--ck-border);
    border-radius: var(--ck-radius);
    padding: 20px;
    max-width: 720px;
    margin: 0 auto;
}
.ck-header {
    display: flex;
    align-items: baseline;
    justify-content: space-between;
    margin-bottom: 16px;
    gap: 12px;
}
.ck-header h2 {
    margin: 0;
    font-size: calc(var(--ck-font-size) * 1.35);
    font-weight: 600;
}
.ck-header .ck-business {
    color: var(--ck-muted);
    font-size: calc(var(--ck-font-size) * 0.9);
}

button.ck-btn, button.ck-btn-primary, button.ck-btn-ghost {
    font-family: inherit;
    font-size: inherit;
    border-radius: var(--ck-radius);
    padding: 10px 16px;
    border: 1px solid var(--ck-border);
    background: var(--ck-bg);
    color: var(--ck-text);
    cursor: pointer;
    transition: transform .05s ease, background .15s ease, border-color .15s ease;
}
button.ck-btn-primary {
    background: var(--ck-primary);
    color: #fff;
    border-color: var(--ck-primary);
    font-weight: 600;
}
button.ck-btn-primary:hover { filter: brightness(0.95); }
button.ck-btn-primary:disabled { opacity: 0.55; cursor: not-allowed; filter: none; }
button.ck-btn-ghost { background: transparent; border-color: transparent; color: var(--ck-muted); }
button.ck-btn-ghost:hover { color: var(--ck-text); }
button.ck-btn:hover { border-color: var(--ck-primary); }

.ck-calendar-nav {
    display: flex;
    align-items: center;
    justify-content: space-between;
    margin-bottom: 12px;
}
.ck-calendar-nav .ck-month-label {
    font-weight: 600;
    text-transform: capitalize;
}
.ck-calendar {
    display: grid;
    grid-template-columns: repeat(7, 1fr);
    gap: 6px;
}
.ck-calendar .ck-dow {
    text-align: center;
    font-size: calc(var(--ck-font-size) * 0.8);
    color: var(--ck-muted);
    padding: 4px 0;
    text-transform: uppercase;
    letter-spacing: 0.03em;
}
.ck-day {
    aspect-ratio: 1 / 1;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    gap: 2px;
    border-radius: calc(var(--ck-radius) * 0.8);
    border: 1px solid var(--ck-border);
    background: var(--ck-bg);
    cursor: pointer;
    color: var(--ck-text);
    font-weight: 500;
    transition: all .12s ease;
    user-select: none;
    padding: 6px 2px;
    text-align: center;
}
.ck-day.ck-empty { visibility: hidden; }
.ck-day.ck-unavailable { background: #f3f4f6; color: #9ca3af; cursor: not-allowed; border-color: transparent; }
.ck-day.ck-available:hover { border-color: var(--ck-primary); background: var(--ck-secondary); }
.ck-day.ck-selected { background: var(--ck-primary); color: #fff; border-color: var(--ck-primary); }
.ck-day.ck-today { outline: 2px solid var(--ck-primary); outline-offset: -2px; }
.ck-day-number { font-size: calc(var(--ck-font-size) * 1.05); line-height: 1; }
.ck-day-occupancy { font-size: calc(var(--ck-font-size) * 0.7); line-height: 1; opacity: 0.85; }

.ck-section { margin-top: 24px; }
.ck-section h3 { margin: 0 0 10px 0; font-size: calc(var(--ck-font-size) * 1.1); }

.ck-experience-list { display: flex; flex-direction: column; gap: 10px; }
.ck-experience {
    border: 1px solid var(--ck-border);
    border-radius: var(--ck-radius);
    padding: 14px;
    cursor: pointer;
    transition: border-color .15s ease, background .15s ease;
}
.ck-experience:hover { border-color: var(--ck-primary); background: var(--ck-secondary); }
.ck-experience.ck-selected { border-color: var(--ck-primary); background: var(--ck-secondary); }
.ck-experience .ck-exp-head { display: flex; justify-content: space-between; gap: 12px; align-items: baseline; }
.ck-experience .ck-exp-name { font-weight: 600; font-size: calc(var(--ck-font-size) * 1.05); }
.ck-experience .ck-exp-price { color: var(--ck-primary); font-weight: 600; }
.ck-experience .ck-exp-meta { color: var(--ck-muted); font-size: calc(var(--ck-font-size) * 0.9); margin-top: 4px; }
.ck-experience .ck-exp-stats { color: var(--ck-primary); font-size: calc(var(--ck-font-size) * 0.85); margin-top: 6px; font-weight: 600; }
.ck-experience .ck-exp-desc { color: var(--ck-text); font-size: calc(var(--ck-font-size) * 0.95); margin-top: 8px; }

.ck-timeslots { display: flex; flex-wrap: wrap; gap: 8px; margin-top: 12px; }
.ck-timeslot {
    display: inline-flex;
    flex-direction: column;
    align-items: flex-start;
    gap: 2px;
    padding: 8px 14px;
    border-radius: calc(var(--ck-radius) * 0.8);
    border: 1px solid var(--ck-border);
    background: var(--ck-bg);
    cursor: pointer;
    font-weight: 500;
}
.ck-timeslot:hover { border-color: var(--ck-primary); }
.ck-timeslot.ck-selected { background: var(--ck-primary); color: #fff; border-color: var(--ck-primary); }
.ck-timeslot-time { line-height: 1.1; }
.ck-timeslot-meta { font-size: calc(var(--ck-font-size) * 0.78); opacity: 0.85; line-height: 1; }

.ck-form { display: grid; gap: 12px; margin-top: 12px; }
.ck-form .ck-row { display: grid; grid-template-columns: 1fr 1fr; gap: 12px; }
@media (max-width: 520px) { .ck-form .ck-row { grid-template-columns: 1fr; } }
.ck-root label { display: block; font-size: calc(var(--ck-font-size) * 0.9); font-weight: 500; margin-bottom: 4px; color: var(--ck-text); }
.ck-root input, .ck-root textarea, .ck-root select {
    width: 100%;
    padding: 10px 12px;
    border: 1px solid var(--ck-border);
    border-radius: calc(var(--ck-radius) * 0.8);
    background: var(--ck-bg);
    color: var(--ck-text);
    font-family: inherit;
    font-size: inherit;
    box-sizing: border-box;
}
.ck-root input:focus, .ck-root textarea:focus, .ck-root select:focus { outline: 2px solid var(--ck-primary); outline-offset: 1px; border-color: var(--ck-primary); }
.ck-root textarea { min-height: 80px; resize: vertical; }
.ck-root input[type="number"] { max-width: 160px; }
.ck-form .ck-actions { display: flex; justify-content: flex-end; gap: 8px; margin-top: 4px; }

.ck-summary {
    background: var(--ck-secondary);
    border-radius: var(--ck-radius);
    padding: 12px 14px;
    margin: 12px 0;
    font-size: calc(var(--ck-font-size) * 0.95);
}
.ck-summary .ck-summary-row { display: flex; justify-content: space-between; gap: 12px; padding: 2px 0; }
.ck-summary .ck-summary-total { font-weight: 700; margin-top: 6px; padding-top: 6px; border-top: 1px solid rgba(0,0,0,0.1); }

.ck-error { color: var(--ck-danger); background: #fee2e2; padding: 10px 12px; border-radius: calc(var(--ck-radius) * 0.8); font-size: calc(var(--ck-font-size) * 0.95); }
.ck-success { color: var(--ck-success); background: #dcfce7; padding: 10px 12px; border-radius: calc(var(--ck-radius) * 0.8); font-size: calc(var(--ck-font-size) * 0.95); }
.ck-muted { color: var(--ck-muted); font-size: calc(var(--ck-font-size) * 0.9); }

.ck-loader {
    display: inline-block;
    width: 16px; height: 16px;
    border: 2px solid var(--ck-border);
    border-top-color: var(--ck-primary);
    border-radius: 50%;
    animation: ck-spin 0.8s linear infinite;
    vertical-align: -3px;
    margin-right: 8px;
}
@keyframes ck-spin { to { transform: rotate(360deg); } }

.ck-back { margin-right: auto; }

/* Cancel link in header */
.ck-link-cancel {
    background: none;
    border: none;
    color: var(--ck-muted);
    font-size: 0.8em;
    cursor: pointer;
    text-decoration: underline;
    padding: 4px 0;
    font-family: var(--ck-font);
}
.ck-link-cancel:hover { color: #dc3545; }

/* Cancel components */
.ck-divider-text {
    text-align: center;
    margin: 12px 0;
    color: var(--ck-muted);
    font-size: 0.85em;
}
.ck-booking-list { display: flex; flex-direction: column; gap: 10px; }
.ck-booking-card {
    border: 1px solid var(--ck-border);
    border-radius: var(--ck-radius);
    padding: 14px;
    background: var(--ck-bg);
}
.ck-booking-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 8px;
}
.ck-booking-details {
    display: flex;
    flex-wrap: wrap;
    gap: 6px 14px;
    font-size: 0.88em;
    color: var(--ck-muted);
    margin-bottom: 10px;
}
.ck-booking-locator { font-family: monospace; font-weight: 600; color: var(--ck-text); }
.ck-badge {
    font-size: 0.75em;
    padding: 2px 8px;
    border-radius: 999px;
    font-weight: 600;
}
.ck-badge-ok { background: #d1fae5; color: #065f46; }
.ck-badge-warn { background: #fef3c7; color: #92400e; }
.ck-btn-danger {
    width: 100%;
    padding: 8px 16px;
    background: #dc3545;
    color: #fff;
    border: none;
    border-radius: var(--ck-radius);
    font-weight: 600;
    cursor: pointer;
    font-family: var(--ck-font);
    font-size: 0.88em;
}
.ck-btn-danger:hover { background: #c82333; }
.ck-btn-danger:disabled { opacity: 0.6; cursor: not-allowed; }
.ck-hint { font-size: 0.82em; color: var(--ck-muted); margin: 4px 0 0; }
.ck-empty { text-align: center; padding: 24px; color: var(--ck-muted); }
.ck-success-box {
    background: #d1fae5;
    border: 1px solid #6ee7b7;
    border-radius: var(--ck-radius);
    padding: 16px;
    margin-bottom: 16px;
}
.ck-success-title { font-weight: 700; color: #065f46; margin-bottom: 6px; }
.ck-success-box p { color: #064e3b; font-size: 0.9em; line-height: 1.5; }
.ck-subtitle { color: var(--ck-muted); font-size: 0.9em; margin-top: 4px; }
`;
}
