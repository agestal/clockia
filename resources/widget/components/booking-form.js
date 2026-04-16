import { h, clearElement, formatDateHuman, formatPrice } from '../utils.js';

export function renderBookingForm({ container, service, date, time, participants, pricing, onBack, onSubmit, submitting, errorMessage }) {
    clearElement(container);

    const root = h('div');

    root.appendChild(h('div', { class: 'ck-summary' }, [
        h('div', { class: 'ck-summary-row' }, [
            h('span', {}, 'Experiencia'),
            h('strong', {}, service?.name || ''),
        ]),
        h('div', { class: 'ck-summary-row' }, [
            h('span', {}, 'Fecha'),
            h('strong', {}, formatDateHuman(date)),
        ]),
        time ? h('div', { class: 'ck-summary-row' }, [
            h('span', {}, 'Hora'),
            h('strong', {}, time),
        ]) : null,
        h('div', { class: 'ck-summary-row' }, [
            h('span', {}, 'Participantes'),
            h('strong', {}, String(participants)),
        ]),
        pricing ? h('div', { class: 'ck-summary-row ck-summary-total' }, [
            h('span', {}, 'Total estimado'),
            h('strong', {}, formatPrice(pricing.total_price, pricing.currency || 'EUR')),
        ]) : null,
    ]));

    const form = h('form', {
        class: 'ck-form',
        onsubmit: (e) => {
            e.preventDefault();
            const data = new FormData(form);
            onSubmit({
                name: data.get('name')?.toString().trim() || '',
                last_name: data.get('last_name')?.toString().trim() || '',
                email: data.get('email')?.toString().trim() || '',
                phone: data.get('phone')?.toString().trim() || '',
                notes: data.get('notes')?.toString().trim() || '',
            });
        },
    });

    const row1 = h('div', { class: 'ck-row' }, [
        h('div', {}, [
            h('label', { for: 'ck-name' }, 'Nombre *'),
            h('input', { id: 'ck-name', name: 'name', type: 'text', required: 'required', autocomplete: 'given-name' }),
        ]),
        h('div', {}, [
            h('label', { for: 'ck-lastname' }, 'Apellidos'),
            h('input', { id: 'ck-lastname', name: 'last_name', type: 'text', autocomplete: 'family-name' }),
        ]),
    ]);

    const row2 = h('div', { class: 'ck-row' }, [
        h('div', {}, [
            h('label', { for: 'ck-email' }, 'Email'),
            h('input', { id: 'ck-email', name: 'email', type: 'email', autocomplete: 'email' }),
        ]),
        h('div', {}, [
            h('label', { for: 'ck-phone' }, 'Tel\u00e9fono *'),
            h('input', { id: 'ck-phone', name: 'phone', type: 'tel', required: 'required', autocomplete: 'tel' }),
        ]),
    ]);

    const notes = h('div', {}, [
        h('label', { for: 'ck-notes' }, 'Observaciones'),
        h('textarea', { id: 'ck-notes', name: 'notes', rows: '3', placeholder: 'Alergias, ni\u00f1os, necesidades especiales...' }),
    ]);

    form.appendChild(row1);
    form.appendChild(row2);
    form.appendChild(notes);

    if (errorMessage) {
        form.appendChild(h('div', { class: 'ck-error' }, errorMessage));
    }

    const actions = h('div', { class: 'ck-actions' }, [
        h('button', { type: 'button', class: 'ck-btn ck-back', onclick: onBack }, '\u2039 Atr\u00e1s'),
        h('button', {
            type: 'submit',
            class: 'ck-btn-primary',
            disabled: submitting ? 'disabled' : null,
        }, submitting ? 'Reservando\u2026' : 'Confirmar reserva'),
    ]);
    form.appendChild(actions);

    root.appendChild(form);
    container.appendChild(root);
}
