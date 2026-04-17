import { h, clearElement, formatDateHuman } from '../utils.js';

export function renderCancelLookup({ container, onLookup, onBack, searching, errorMessage }) {
    clearElement(container);

    const root = h('div');

    root.appendChild(h('div', { class: 'ck-section-title' }, [
        h('h3', {}, 'Cancelar reserva'),
        h('p', { class: 'ck-subtitle' }, 'Introduce tu localizador o email para buscar tu reserva.'),
    ]));

    const form = h('form', {
        class: 'ck-form',
        onsubmit: (e) => {
            e.preventDefault();
            const data = new FormData(form);
            onLookup({
                locator: data.get('locator')?.toString().trim() || '',
                email: data.get('email')?.toString().trim() || '',
            });
        },
    });

    form.appendChild(h('div', {}, [
        h('label', { for: 'ck-cancel-locator' }, 'Localizador'),
        h('input', { id: 'ck-cancel-locator', name: 'locator', type: 'text', placeholder: 'Ej: ABC12345', autocomplete: 'off' }),
    ]));

    form.appendChild(h('div', { class: 'ck-divider-text' }, [
        h('span', {}, 'o busca por email'),
    ]));

    form.appendChild(h('div', {}, [
        h('label', { for: 'ck-cancel-email' }, 'Email'),
        h('input', { id: 'ck-cancel-email', name: 'email', type: 'email', placeholder: 'tu@email.com', autocomplete: 'email' }),
    ]));

    if (errorMessage) {
        form.appendChild(h('div', { class: 'ck-error' }, errorMessage));
    }

    const actions = h('div', { class: 'ck-actions' }, [
        h('button', { type: 'button', class: 'ck-btn ck-back', onclick: onBack }, '\u2039 Volver'),
        h('button', {
            type: 'submit',
            class: 'ck-btn-primary',
            disabled: searching ? 'disabled' : null,
        }, searching ? 'Buscando\u2026' : 'Buscar reserva'),
    ]);
    form.appendChild(actions);

    root.appendChild(form);
    container.appendChild(root);
}

export function renderCancelResults({ container, bookings, onRequestCancel, onBack, cancelling, successMessage, errorMessage }) {
    clearElement(container);

    const root = h('div');

    if (successMessage) {
        root.appendChild(h('div', { class: 'ck-success-box' }, [
            h('p', { class: 'ck-success-title' }, 'Solicitud enviada'),
            h('p', {}, successMessage),
        ]));

        root.appendChild(h('div', { class: 'ck-actions' }, [
            h('button', { type: 'button', class: 'ck-btn ck-back', onclick: onBack }, '\u2039 Volver al inicio'),
        ]));

        container.appendChild(root);
        return;
    }

    root.appendChild(h('div', { class: 'ck-section-title' }, [
        h('h3', {}, 'Tus reservas'),
        h('p', { class: 'ck-subtitle' }, 'Selecciona la reserva que quieres cancelar.'),
    ]));

    if (bookings.length === 0) {
        root.appendChild(h('div', { class: 'ck-empty' }, 'No se encontraron reservas activas.'));
        root.appendChild(h('div', { class: 'ck-actions' }, [
            h('button', { type: 'button', class: 'ck-btn ck-back', onclick: onBack }, '\u2039 Volver'),
        ]));
        container.appendChild(root);
        return;
    }

    if (errorMessage) {
        root.appendChild(h('div', { class: 'ck-error' }, errorMessage));
    }

    const list = h('div', { class: 'ck-booking-list' });

    bookings.forEach((b) => {
        const card = h('div', { class: 'ck-booking-card' }, [
            h('div', { class: 'ck-booking-header' }, [
                h('strong', {}, b.service_name || 'Reserva'),
                h('span', { class: `ck-badge ${b.cancellable ? 'ck-badge-ok' : 'ck-badge-warn'}` },
                    b.cancellable ? 'Cancelable' : 'Fuera de plazo'),
            ]),
            h('div', { class: 'ck-booking-details' }, [
                h('span', {}, `${formatDateHuman(b.date)} a las ${b.time}`),
                h('span', {}, `${b.participants} persona${b.participants !== 1 ? 's' : ''}`),
                h('span', { class: 'ck-booking-locator' }, b.locator),
            ]),
            b.cancellable
                ? h('button', {
                    class: 'ck-btn-danger',
                    disabled: cancelling ? 'disabled' : null,
                    onclick: () => onRequestCancel(b.locator),
                }, cancelling ? 'Procesando\u2026' : 'Cancelar esta reserva')
                : (b.min_hours_cancellation
                    ? h('p', { class: 'ck-hint' }, `Requiere cancelar con ${b.min_hours_cancellation}h de antelacion.`)
                    : null),
        ]);

        list.appendChild(card);
    });

    root.appendChild(list);

    root.appendChild(h('div', { class: 'ck-actions' }, [
        h('button', { type: 'button', class: 'ck-btn ck-back', onclick: onBack }, '\u2039 Volver'),
    ]));

    container.appendChild(root);
}
