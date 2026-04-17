import { createApiClient } from './api.js';
import { buildStyles } from './styles.js';
import { renderCalendar } from './components/calendar.js';
import { renderExperienceList } from './components/experience-list.js';
import { renderBookingForm } from './components/booking-form.js';
import { h, clearElement, formatDateHuman, formatPrice, pad } from './utils.js';

const DEFAULT_API_BASE = '/api/widget';

const OVERRIDE_ATTRS = [
    'primary-color', 'secondary-color', 'text-color', 'background-color',
    'font-family', 'font-size-base', 'border-radius', 'locale',
];

export class ClockiaWidget extends HTMLElement {
    static get observedAttributes() {
        return ['business-id', 'widget-key', 'api-base', ...OVERRIDE_ATTRS];
    }

    constructor() {
        super();
        this.attachShadow({ mode: 'open' });
        this.state = this.initialState();
    }

    initialState() {
        const now = new Date();
        return {
            loading: true,
            error: null,
            theme: null,
            business: null,
            api: null,
            year: now.getFullYear(),
            month: now.getMonth() + 1,
            days: [],
            selectedDate: null,
            services: [],
            loadingDate: false,
            selectedServiceId: null,
            selectedTime: null,
            selectedSlotKey: null,
            participants: 2,
            pricing: null,
            submitting: false,
            bookingError: null,
            confirmation: null,
            view: 'calendar',
        };
    }

    connectedCallback() {
        this.init();
    }

    attributeChangedCallback() {
        if (this.shadowRoot.querySelector('.ck-root')) {
            this.init();
        }
    }

    async init() {
        const businessId = this.getAttribute('business-id');
        const widgetKey = this.getAttribute('widget-key');
        const apiBase = this.getAttribute('api-base') || DEFAULT_API_BASE;

        if (!businessId || !widgetKey) {
            this.renderFatal('Faltan atributos business-id y/o widget-key en <clockia-widget>.');
            return;
        }

        const api = createApiClient({ apiBase, businessId, widgetKey });
        this.state.api = api;
        this.state.loading = true;
        this.state.error = null;
        this.renderRoot();

        try {
            const config = await api.config();
            const theme = this.applyAttributeOverrides(config.widget);
            this.state.theme = theme;
            this.state.business = config.business;
            this.state.loading = false;
            this.renderRoot();
            await this.loadMonth(this.state.year, this.state.month);
        } catch (err) {
            this.state.loading = false;
            this.state.error = err.message || 'No se pudo cargar el widget.';
            this.renderRoot();
        }
    }

    applyAttributeOverrides(theme) {
        const mapping = {
            'primary-color': 'primary_color',
            'secondary-color': 'secondary_color',
            'text-color': 'text_color',
            'background-color': 'background_color',
            'font-family': 'font_family',
            'font-size-base': 'font_size_base',
            'border-radius': 'border_radius',
            'locale': 'locale',
        };
        const result = { ...theme };
        OVERRIDE_ATTRS.forEach((attr) => {
            const value = this.getAttribute(attr);
            if (value !== null && value !== '') {
                result[mapping[attr]] = value;
            }
        });
        return result;
    }

    async loadMonth(year, month) {
        this.state.loading = true;
        this.state.error = null;
        this.renderRoot();
        try {
            const data = await this.state.api.calendar(year, month, {
                participants: this.state.participants,
            });
            this.state.year = data.year;
            this.state.month = data.month;
            this.state.days = data.days;
            this.state.loading = false;
            this.renderRoot();
        } catch (err) {
            this.state.loading = false;
            this.state.error = err.message;
            this.renderRoot();
        }
    }

    async loadDate(dateIso) {
        this.state.selectedDate = dateIso;
        this.state.services = [];
        this.state.selectedServiceId = null;
        this.state.selectedTime = null;
        this.state.selectedSlotKey = null;
        this.state.pricing = null;
        this.state.loadingDate = true;
        this.state.view = 'date';
        this.renderRoot();

        try {
            const data = await this.state.api.date(dateIso, this.state.participants);
            this.state.services = data.services || [];
            this.state.loadingDate = false;
            this.renderRoot();
        } catch (err) {
            this.state.loadingDate = false;
            this.state.error = err.message;
            this.renderRoot();
        }
    }

    selectService(serviceId) {
        this.state.selectedServiceId = serviceId;
        this.state.selectedTime = null;
        this.state.selectedSlotKey = null;
        this.state.pricing = null;
        const service = this.state.services.find((s) => s.id === serviceId);
        if (service && !service.requires_timeslot) {
            this.state.selectedTime = null;
            this.state.selectedSlotKey = null;
        }
        this.renderRoot();
    }

    async selectTime(slot) {
        this.state.selectedTime = slot.time;
        this.state.selectedSlotKey = slot.slot_key || null;
        this.renderRoot();
    }

    async goToForm() {
        const service = this.state.services.find((s) => s.id === this.state.selectedServiceId);
        if (!service) return;

        if (service.min_participants && this.state.participants < service.min_participants) {
            this.state.error = `Este servicio requiere al menos ${service.min_participants} participantes.`;
            this.renderRoot();
            return;
        }
        if (service.max_participants && this.state.participants > service.max_participants) {
            this.state.error = `Este servicio admite como m\u00e1ximo ${service.max_participants} participantes.`;
            this.renderRoot();
            return;
        }
        if (service.requires_timeslot && !this.state.selectedTime) {
            this.state.error = 'Selecciona una hora disponible.';
            this.renderRoot();
            return;
        }

        this.state.error = null;
        this.state.bookingError = null;

        try {
            const check = await this.state.api.check({
                service_id: service.id,
                date: this.state.selectedDate,
                time: this.state.selectedTime,
                participants: this.state.participants,
            });
            if (!check.available) {
                this.state.bookingError = check.error || 'El hueco elegido ya no est\u00e1 disponible.';
                this.renderRoot();
                return;
            }
            this.state.pricing = {
                total_price: check.summary?.total_price,
                currency: check.currency || 'EUR',
            };
            if (!this.state.selectedSlotKey && check.slot?.slot_key) {
                this.state.selectedSlotKey = check.slot.slot_key;
            }
            this.state.view = 'form';
            this.renderRoot();
        } catch (err) {
            this.state.bookingError = err.message;
            this.renderRoot();
        }
    }

    async submitBooking(formData) {
        this.state.submitting = true;
        this.state.bookingError = null;
        this.renderRoot();

        try {
            const response = await this.state.api.book({
                service_id: this.state.selectedServiceId,
                date: this.state.selectedDate,
                time: this.state.selectedTime,
                slot_key: this.state.selectedSlotKey,
                participants: this.state.participants,
                customer: {
                    name: formData.name,
                    last_name: formData.last_name,
                    email: formData.email,
                    phone: formData.phone,
                },
                notes: formData.notes,
            });
            this.state.submitting = false;
            this.state.confirmation = response.booking;
            this.state.view = 'done';
            this.renderRoot();
        } catch (err) {
            this.state.submitting = false;
            this.state.bookingError = err.message || 'No se pudo crear la reserva.';
            this.renderRoot();
        }
    }

    reset() {
        const theme = this.state.theme;
        const business = this.state.business;
        const api = this.state.api;
        this.state = this.initialState();
        this.state.theme = theme;
        this.state.business = business;
        this.state.api = api;
        this.loadMonth(this.state.year, this.state.month);
    }

    renderFatal(message) {
        const style = document.createElement('style');
        style.textContent = ':host { display:block; font-family: system-ui; color:#b91c1c; padding:12px; border:1px solid #fecaca; border-radius:8px; background:#fef2f2; }';
        clearElement(this.shadowRoot);
        this.shadowRoot.appendChild(style);
        const div = document.createElement('div');
        div.textContent = message;
        this.shadowRoot.appendChild(div);
    }

    renderRoot() {
        if (!this.state.theme && !this.state.loading && !this.state.error) return;

        clearElement(this.shadowRoot);

        const theme = this.state.theme || {
            primary_color: '#7B3F00', secondary_color: '#EAD7C5',
            text_color: '#2B2B2B', background_color: '#FFFFFF',
            font_family: 'Inter, system-ui, sans-serif',
            font_size_base: '14px', border_radius: '10px',
        };

        const style = document.createElement('style');
        style.textContent = buildStyles(theme);
        this.shadowRoot.appendChild(style);

        const root = h('div', { class: 'ck-root' });

        const header = h('div', { class: 'ck-header' }, [
            h('div', {}, [
                h('h2', {}, 'Reserva tu experiencia'),
                this.state.business ? h('div', { class: 'ck-business' }, this.state.business.name) : null,
            ]),
        ]);
        root.appendChild(header);

        if (this.state.loading) {
            root.appendChild(h('div', {}, [h('span', { class: 'ck-loader' }), 'Cargando\u2026']));
            this.shadowRoot.appendChild(root);
            return;
        }

        if (this.state.error) {
            root.appendChild(h('div', { class: 'ck-error' }, this.state.error));
            const retry = h('div', { style: { marginTop: '12px' } }, [
                h('button', { class: 'ck-btn-primary', type: 'button', onclick: () => this.init() }, 'Reintentar'),
            ]);
            root.appendChild(retry);
            this.shadowRoot.appendChild(root);
            return;
        }

        if (this.state.view === 'done' && this.state.confirmation) {
            this.renderDone(root);
            this.shadowRoot.appendChild(root);
            return;
        }

        const calendarContainer = h('div');
        const today = new Date();
        const todayIso = `${today.getFullYear()}-${pad(today.getMonth() + 1)}-${pad(today.getDate())}`;
        renderCalendar({
            container: calendarContainer,
            year: this.state.year,
            month: this.state.month,
            days: this.state.days,
            selectedDate: this.state.selectedDate,
            todayIso,
            onPrev: () => {
                let y = this.state.year, m = this.state.month - 1;
                if (m < 1) { m = 12; y -= 1; }
                this.loadMonth(y, m);
            },
            onNext: () => {
                let y = this.state.year, m = this.state.month + 1;
                if (m > 12) { m = 1; y += 1; }
                this.loadMonth(y, m);
            },
            onSelect: (dateIso) => this.loadDate(dateIso),
        });
        root.appendChild(calendarContainer);

        if (this.state.view === 'date' && this.state.selectedDate) {
            const section = h('div', { class: 'ck-section' }, [
                h('h3', {}, `Experiencias disponibles \u2013 ${formatDateHuman(this.state.selectedDate)}`),
            ]);

            const partsRow = h('div', { class: 'ck-row', style: { marginTop: '8px', marginBottom: '8px' } }, [
                h('div', {}, [
                    h('label', { for: 'ck-parts' }, 'Participantes'),
                    h('input', {
                        id: 'ck-parts',
                        type: 'number',
                        min: '1',
                        value: String(this.state.participants),
                        onchange: (e) => {
                            const val = parseInt(e.target.value, 10);
                            if (!isNaN(val) && val > 0) {
                                this.state.participants = val;
                                this.loadDate(this.state.selectedDate);
                            }
                        },
                    }),
                ]),
            ]);
            section.appendChild(partsRow);

            if (this.state.loadingDate) {
                section.appendChild(h('div', {}, [h('span', { class: 'ck-loader' }), 'Cargando experiencias\u2026']));
            } else {
                const listContainer = h('div');
                renderExperienceList({
                    container: listContainer,
                    services: this.state.services,
                    selectedServiceId: this.state.selectedServiceId,
                    selectedTime: this.state.selectedTime,
                    onSelectService: (id) => this.selectService(id),
                    onSelectTime: (slot) => this.selectTime(slot),
                });
                section.appendChild(listContainer);

                if (this.state.bookingError) {
                    section.appendChild(h('div', { class: 'ck-error', style: { marginTop: '12px' } }, this.state.bookingError));
                }

                if (this.state.selectedServiceId) {
                    const service = this.state.services.find((s) => s.id === this.state.selectedServiceId);
                    const canContinue = service && (!service.requires_timeslot || this.state.selectedTime);
                    section.appendChild(h('div', { class: 'ck-actions', style: { marginTop: '14px' } }, [
                        h('button', {
                            class: 'ck-btn-primary',
                            type: 'button',
                            disabled: canContinue ? null : 'disabled',
                            onclick: () => this.goToForm(),
                        }, 'Continuar →'),
                    ]));
                }
            }

            root.appendChild(section);
        }

        if (this.state.view === 'form' && this.state.selectedServiceId) {
            const service = this.state.services.find((s) => s.id === this.state.selectedServiceId);
            const formContainer = h('div', { class: 'ck-section' });
            formContainer.appendChild(h('h3', {}, 'Confirma tu reserva'));
            renderBookingForm({
                container: formContainer,
                service,
                date: this.state.selectedDate,
                time: this.state.selectedTime,
                participants: this.state.participants,
                pricing: this.state.pricing,
                submitting: this.state.submitting,
                errorMessage: this.state.bookingError,
                onBack: () => {
                    this.state.view = 'date';
                    this.state.bookingError = null;
                    this.renderRoot();
                },
                onSubmit: (data) => this.submitBooking(data),
            });
            root.appendChild(formContainer);
        }

        this.shadowRoot.appendChild(root);
    }

    renderDone(root) {
        const booking = this.state.confirmation;
        root.appendChild(h('div', { class: 'ck-success' }, '\u00a1Reserva confirmada!'));
        const summary = h('div', { class: 'ck-summary' }, [
            h('div', { class: 'ck-summary-row' }, [h('span', {}, 'Localizador'), h('strong', {}, booking.reference || '-')]),
            h('div', { class: 'ck-summary-row' }, [h('span', {}, 'Experiencia'), h('strong', {}, booking.service_name || '-')]),
            h('div', { class: 'ck-summary-row' }, [h('span', {}, 'Fecha'), h('strong', {}, formatDateHuman(booking.date))]),
            booking.time ? h('div', { class: 'ck-summary-row' }, [h('span', {}, 'Hora'), h('strong', {}, booking.time)]) : null,
            h('div', { class: 'ck-summary-row' }, [h('span', {}, 'Participantes'), h('strong', {}, String(booking.participants))]),
            booking.total_price ? h('div', { class: 'ck-summary-row ck-summary-total' }, [h('span', {}, 'Total estimado'), h('strong', {}, formatPrice(booking.total_price, booking.currency || 'EUR'))]) : null,
        ]);
        root.appendChild(summary);
        const actions = h('div', { class: 'ck-actions' }, [
            h('button', { class: 'ck-btn', type: 'button', onclick: () => this.reset() }, 'Hacer otra reserva'),
        ]);
        root.appendChild(actions);
    }
}

if (!customElements.get('clockia-widget')) {
    customElements.define('clockia-widget', ClockiaWidget);
}
