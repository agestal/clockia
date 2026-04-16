import { h, clearElement, formatPrice } from '../utils.js';

export function renderExperienceList({ container, services, selectedServiceId, selectedTime, onSelectService, onSelectTime }) {
    clearElement(container);

    if (!services.length) {
        container.appendChild(h('div', { class: 'ck-muted' }, 'No hay experiencias disponibles para este d\u00eda.'));
        return;
    }

    const list = h('div', { class: 'ck-experience-list' });

    services.forEach((service) => {
        const wrapper = h('div', {
            class: 'ck-experience' + (service.id === selectedServiceId ? ' ck-selected' : ''),
            onclick: (e) => {
                if (e.target.closest('.ck-timeslot')) return;
                onSelectService(service.id);
            },
        });

        const head = h('div', { class: 'ck-exp-head' }, [
            h('div', { class: 'ck-exp-name' }, service.name || 'Experiencia'),
            h('div', { class: 'ck-exp-price' }, service.price ? formatPrice(service.price, service.currency) : ''),
        ]);
        wrapper.appendChild(head);

        const metaParts = [];
        if (service.duration_minutes) metaParts.push(`${service.duration_minutes} min`);
        if (service.min_participants) metaParts.push(`M\u00edn ${service.min_participants} pers.`);
        if (service.max_participants) metaParts.push(`M\u00e1x ${service.max_participants} pers.`);
        if (metaParts.length) wrapper.appendChild(h('div', { class: 'ck-exp-meta' }, metaParts.join(' \u00b7 ')));

        if (service.description) wrapper.appendChild(h('div', { class: 'ck-exp-desc' }, service.description));

        if (service.id === selectedServiceId && service.requires_timeslot && service.timeslots && service.timeslots.length) {
            const slotsEl = h('div', { class: 'ck-timeslots' });
            service.timeslots.forEach((slot) => {
                const btn = h('button', {
                    type: 'button',
                    class: 'ck-timeslot' + (selectedTime === slot.time ? ' ck-selected' : ''),
                    onclick: (e) => {
                        e.stopPropagation();
                        onSelectTime(slot);
                    },
                }, slot.time);
                slotsEl.appendChild(btn);
            });
            wrapper.appendChild(slotsEl);
        } else if (service.id === selectedServiceId && !service.requires_timeslot) {
            wrapper.appendChild(h('div', { class: 'ck-muted', style: { marginTop: '8px' } }, 'Este servicio no requiere hora concreta.'));
        }

        list.appendChild(wrapper);
    });

    container.appendChild(list);
}
