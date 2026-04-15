import { ClockiaWidget } from './clockia-widget.js';

const Clockia = {
    init(options = {}) {
        if (!options.businessId || !options.widgetKey) {
            console.error('[Clockia] init() requires businessId and widgetKey.');
            return null;
        }

        const container = typeof options.container === 'string'
            ? document.querySelector(options.container)
            : options.container;

        if (!container) {
            console.error('[Clockia] container not found:', options.container);
            return null;
        }

        const widget = document.createElement('clockia-widget');
        widget.setAttribute('business-id', String(options.businessId));
        widget.setAttribute('widget-key', String(options.widgetKey));
        if (options.apiBase) widget.setAttribute('api-base', options.apiBase);

        const mapping = {
            primaryColor: 'primary-color',
            secondaryColor: 'secondary-color',
            textColor: 'text-color',
            backgroundColor: 'background-color',
            fontFamily: 'font-family',
            fontSizeBase: 'font-size-base',
            borderRadius: 'border-radius',
            locale: 'locale',
        };
        Object.entries(mapping).forEach(([camel, attr]) => {
            if (options[camel] !== undefined && options[camel] !== null) {
                widget.setAttribute(attr, String(options[camel]));
            }
        });

        container.innerHTML = '';
        container.appendChild(widget);
        return widget;
    },
    Widget: ClockiaWidget,
};

if (typeof window !== 'undefined') {
    const existing = window.Clockia;
    window.Clockia = existing && typeof existing === 'object' ? Object.assign(existing, Clockia) : Clockia;
}

export default Clockia;
export { ClockiaWidget };
