import { ClockiaChatWidget } from './clockia-chat-widget.js';

const ClockiaChat = {
    init(options = {}) {
        if (!options.businessId || !options.widgetKey) {
            console.error('[ClockiaChat] init() requires businessId and widgetKey.');
            return null;
        }

        const widget = document.createElement('clockia-chat-widget');
        widget.setAttribute('business-id', String(options.businessId));
        widget.setAttribute('widget-key', String(options.widgetKey));
        if (options.apiBase) widget.setAttribute('api-base', options.apiBase);
        if (options.title) widget.setAttribute('title', options.title);

        const mapping = {
            primaryColor: 'primary-color',
            secondaryColor: 'secondary-color',
            textColor: 'text-color',
            backgroundColor: 'background-color',
            fontFamily: 'font-family',
            fontSizeBase: 'font-size-base',
            borderRadius: 'border-radius',
        };
        Object.entries(mapping).forEach(([camel, attr]) => {
            if (options[camel] !== undefined && options[camel] !== null) {
                widget.setAttribute(attr, String(options[camel]));
            }
        });

        (document.body || document.documentElement).appendChild(widget);
        return widget;
    },
    Widget: ClockiaChatWidget,
};

if (typeof window !== 'undefined') {
    const existing = window.Clockia;
    const merged = existing && typeof existing === 'object'
        ? Object.assign(existing, { Chat: ClockiaChat, initChat: ClockiaChat.init })
        : { Chat: ClockiaChat, initChat: ClockiaChat.init };
    window.Clockia = merged;
}

export default ClockiaChat;
export { ClockiaChatWidget };
