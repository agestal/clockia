import { createChatApi } from './api.js';
import { buildChatStyles } from './styles.js';

const DEFAULT_API_BASE = '/api/widget';

const OVERRIDE_ATTRS = [
    'primary-color', 'secondary-color', 'text-color', 'background-color',
    'font-family', 'font-size-base', 'border-radius',
];

const ATTR_TO_KEY = {
    'primary-color': 'primary_color',
    'secondary-color': 'secondary_color',
    'text-color': 'text_color',
    'background-color': 'background_color',
    'font-family': 'font_family',
    'font-size-base': 'font_size_base',
    'border-radius': 'border_radius',
};

const DEFAULT_THEME = {
    primary_color: '#7B3F00',
    secondary_color: '#EAD7C5',
    text_color: '#2B2B2B',
    background_color: '#FFFFFF',
    font_family: 'Inter, system-ui, sans-serif',
    font_size_base: '14px',
    border_radius: '12px',
};

function el(tag, attrs = {}, children = []) {
    const node = document.createElement(tag);
    Object.entries(attrs || {}).forEach(([key, value]) => {
        if (value === null || value === undefined || value === false) return;
        if (key === 'class') node.className = value;
        else if (key === 'html') node.innerHTML = value;
        else if (key.startsWith('on') && typeof value === 'function') {
            node.addEventListener(key.slice(2).toLowerCase(), value);
        } else {
            node.setAttribute(key, value);
        }
    });
    (Array.isArray(children) ? children : [children]).forEach((child) => {
        if (child === null || child === undefined || child === false) return;
        node.appendChild(typeof child === 'string' ? document.createTextNode(child) : child);
    });
    return node;
}

export class ClockiaChatWidget extends HTMLElement {
    static get observedAttributes() {
        return ['business-id', 'widget-key', 'api-base', 'title', ...OVERRIDE_ATTRS];
    }

    constructor() {
        super();
        this.attachShadow({ mode: 'open' });
        this.state = {
            open: false,
            theme: { ...DEFAULT_THEME },
            businessName: null,
            api: null,
            messages: [],
            conversationId: null,
            sending: false,
            error: null,
            ready: false,
            storageKey: null,
        };
        this.bodyRef = null;
        this.panelRef = null;
    }

    connectedCallback() {
        this.init();
    }

    async init() {
        const businessId = this.getAttribute('business-id');
        const widgetKey = this.getAttribute('widget-key');
        const apiBase = this.getAttribute('api-base') || DEFAULT_API_BASE;

        if (!businessId || !widgetKey) {
            console.error('[Clockia chat] Faltan atributos business-id y/o widget-key.');
            return;
        }

        this.state.theme = this.applyOverrides(DEFAULT_THEME);
        this.state.api = createChatApi({ apiBase, businessId, widgetKey });
        this.state.storageKey = `clockia_chat_cid_${businessId}`;
        this.state.conversationId = this.restoreConversationId();

        this.render();

        try {
            const info = await this.state.api.greeting();
            this.state.businessName = info.business?.name || null;
            const customTitle = this.getAttribute('title');
            if (customTitle) this.state.businessName = customTitle;
            if (info.greeting && this.state.messages.length === 0) {
                this.state.messages.push({ role: 'assistant', text: info.greeting });
            }
            this.state.ready = true;
            this.render();

            if (this.state.conversationId) {
                try {
                    const histResp = await this.state.api.history(this.state.conversationId);
                    if (Array.isArray(histResp.history) && histResp.history.length > 0) {
                        this.state.messages = histResp.history.map((m) => ({
                            role: m.role === 'user' ? 'user' : 'assistant',
                            text: m.text || '',
                        }));
                        this.render();
                    }
                } catch (_) { /* ignore history fetch errors */ }
            }
        } catch (err) {
            this.state.error = err.message || 'No se pudo iniciar el chat.';
            this.state.ready = true;
            this.render();
        }
    }

    applyOverrides(baseTheme) {
        const result = { ...baseTheme };
        OVERRIDE_ATTRS.forEach((attr) => {
            const value = this.getAttribute(attr);
            if (value !== null && value !== '') result[ATTR_TO_KEY[attr]] = value;
        });
        return result;
    }

    restoreConversationId() {
        try {
            return localStorage.getItem(this.state.storageKey) || null;
        } catch (_) {
            return null;
        }
    }

    persistConversationId(cid) {
        try {
            if (cid) localStorage.setItem(this.state.storageKey, cid);
        } catch (_) { /* ignore storage errors */ }
    }

    clearConversation() {
        this.state.messages = [];
        this.state.conversationId = null;
        try { localStorage.removeItem(this.state.storageKey); } catch (_) {}
        this.state.error = null;
        this.render();
    }

    toggleOpen() {
        this.state.open = !this.state.open;
        this.render();
        if (this.state.open) {
            setTimeout(() => {
                const input = this.shadowRoot?.querySelector('textarea');
                if (input) input.focus();
                this.scrollToBottom();
            }, 120);
        }
    }

    scrollToBottom() {
        const body = this.shadowRoot?.querySelector('.ck-chat-body');
        if (body) body.scrollTop = body.scrollHeight;
    }

    async sendMessage(text) {
        const trimmed = (text || '').trim();
        if (!trimmed || this.state.sending) return;

        this.state.messages.push({ role: 'user', text: trimmed });
        this.state.sending = true;
        this.state.error = null;
        this.render();
        this.scrollToBottom();

        try {
            const response = await this.state.api.send(trimmed, this.state.conversationId);
            if (response.conversation_id) {
                this.state.conversationId = response.conversation_id;
                this.persistConversationId(response.conversation_id);
            }
            this.state.messages.push({
                role: 'assistant',
                text: response.reply || '(Sin respuesta)',
            });
        } catch (err) {
            this.state.error = err.message || 'Error al enviar el mensaje.';
        } finally {
            this.state.sending = false;
            this.render();
            this.scrollToBottom();
        }
    }

    render() {
        const root = this.shadowRoot;
        while (root.firstChild) root.removeChild(root.firstChild);

        const style = document.createElement('style');
        style.textContent = buildChatStyles(this.state.theme);
        root.appendChild(style);

        const bubble = el('button', {
            class: 'ck-chat-bubble',
            type: 'button',
            'aria-label': this.state.open ? 'Cerrar chat' : 'Abrir chat',
            onclick: () => this.toggleOpen(),
        });
        bubble.innerHTML = this.state.open
            ? '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="6" x2="6" y2="18"></line><line x1="6" y1="6" x2="18" y2="18"></line></svg>'
            : '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 11.5a8.38 8.38 0 0 1-.9 3.8 8.5 8.5 0 0 1-7.6 4.7 8.38 8.38 0 0 1-3.8-.9L3 21l1.9-5.7a8.38 8.38 0 0 1-.9-3.8 8.5 8.5 0 0 1 4.7-7.6 8.38 8.38 0 0 1 3.8-.9h.5a8.48 8.48 0 0 1 8 8v.5z"></path></svg>';
        root.appendChild(bubble);

        const panel = el('div', { class: 'ck-chat-panel' + (this.state.open ? ' ck-open' : '') });

        const name = this.state.businessName || 'Chat';
        const initial = name.trim().charAt(0).toUpperCase() || 'C';

        const header = el('div', { class: 'ck-chat-header' }, [
            el('div', { class: 'ck-chat-avatar' }, initial),
            el('div', { class: 'ck-chat-title' }, [
                document.createTextNode(name),
                el('small', {}, 'Responde en unos segundos'),
            ]),
            el('button', {
                type: 'button',
                title: 'Iniciar nueva conversación',
                'aria-label': 'Iniciar nueva conversación',
                onclick: () => this.clearConversation(),
            }, '↻'),
            el('button', {
                type: 'button',
                title: 'Cerrar',
                'aria-label': 'Cerrar chat',
                onclick: () => this.toggleOpen(),
            }, '×'),
        ]);

        const body = el('div', { class: 'ck-chat-body' });

        this.state.messages.forEach((msg) => {
            const cls = 'ck-msg ' + (msg.role === 'user' ? 'ck-msg-user' : 'ck-msg-bot');
            body.appendChild(el('div', { class: cls }, msg.text));
        });

        if (this.state.sending) {
            const typing = el('div', { class: 'ck-typing' }, [
                el('span'), el('span'), el('span'),
            ]);
            body.appendChild(typing);
        }

        if (this.state.error) {
            body.appendChild(el('div', { class: 'ck-msg ck-msg-error' }, this.state.error));
        }

        const footer = el('div', { class: 'ck-chat-footer' });
        const form = el('form', {
            class: 'ck-chat-form',
            onsubmit: (e) => {
                e.preventDefault();
                const ta = form.querySelector('textarea');
                const value = ta.value;
                ta.value = '';
                ta.style.height = 'auto';
                this.sendMessage(value);
            },
        });
        const textarea = el('textarea', {
            rows: '1',
            placeholder: 'Escribe tu mensaje…',
            'aria-label': 'Mensaje',
            disabled: this.state.sending ? 'disabled' : null,
            onkeydown: (e) => {
                if (e.key === 'Enter' && !e.shiftKey) {
                    e.preventDefault();
                    form.requestSubmit();
                }
            },
            oninput: (e) => {
                e.target.style.height = 'auto';
                e.target.style.height = Math.min(120, e.target.scrollHeight) + 'px';
            },
        });
        const sendBtn = el('button', {
            type: 'submit',
            disabled: this.state.sending ? 'disabled' : null,
            'aria-label': 'Enviar',
        }, 'Enviar');

        form.appendChild(textarea);
        form.appendChild(sendBtn);
        footer.appendChild(form);

        panel.appendChild(header);
        panel.appendChild(body);
        panel.appendChild(footer);
        root.appendChild(panel);
    }
}

if (!customElements.get('clockia-chat-widget')) {
    customElements.define('clockia-chat-widget', ClockiaChatWidget);
}
