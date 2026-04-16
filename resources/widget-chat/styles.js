export function buildChatStyles(theme) {
    return `
:host {
    --ck-primary: ${theme.primary_color};
    --ck-secondary: ${theme.secondary_color};
    --ck-text: ${theme.text_color};
    --ck-bg: ${theme.background_color};
    --ck-font: ${theme.font_family};
    --ck-font-size: ${theme.font_size_base};
    --ck-radius: ${theme.border_radius};
    --ck-border: #e5e7eb;
    --ck-muted: #6b7280;
    --ck-shadow: 0 10px 30px rgba(0,0,0,0.18);
    --ck-user-bg: var(--ck-primary);
    --ck-user-fg: #ffffff;
    --ck-bot-bg: #f3f4f6;
    --ck-bot-fg: var(--ck-text);
    font-family: var(--ck-font);
    font-size: var(--ck-font-size);
    color: var(--ck-text);
    position: fixed;
    right: 20px;
    bottom: 20px;
    z-index: 2147483000;
}
*, *::before, *::after { box-sizing: border-box; }

.ck-chat-bubble {
    width: 60px;
    height: 60px;
    border-radius: 50%;
    background: var(--ck-primary);
    color: #fff;
    border: none;
    cursor: pointer;
    box-shadow: var(--ck-shadow);
    display: flex;
    align-items: center;
    justify-content: center;
    transition: transform .12s ease, box-shadow .12s ease;
    padding: 0;
    font-family: inherit;
}
.ck-chat-bubble:hover { transform: translateY(-2px); box-shadow: 0 14px 34px rgba(0,0,0,0.22); }
.ck-chat-bubble:focus-visible { outline: 3px solid var(--ck-secondary); outline-offset: 3px; }
.ck-chat-bubble svg { width: 28px; height: 28px; }

.ck-chat-panel {
    position: fixed;
    right: 20px;
    bottom: 90px;
    width: 380px;
    max-width: calc(100vw - 24px);
    height: 560px;
    max-height: calc(100vh - 120px);
    background: var(--ck-bg);
    border: 1px solid var(--ck-border);
    border-radius: var(--ck-radius);
    box-shadow: var(--ck-shadow);
    display: flex;
    flex-direction: column;
    overflow: hidden;
    opacity: 0;
    transform: translateY(14px) scale(.98);
    transform-origin: bottom right;
    pointer-events: none;
    transition: opacity .16s ease, transform .16s ease;
}
.ck-chat-panel.ck-open {
    opacity: 1;
    transform: translateY(0) scale(1);
    pointer-events: auto;
}

.ck-chat-header {
    display: flex;
    align-items: center;
    gap: 10px;
    padding: 14px 16px;
    background: var(--ck-primary);
    color: #fff;
}
.ck-chat-header .ck-chat-avatar {
    width: 34px; height: 34px; border-radius: 50%;
    background: rgba(255,255,255,0.22);
    display: flex; align-items: center; justify-content: center;
    font-weight: 700; font-size: calc(var(--ck-font-size) * 1.05);
}
.ck-chat-header .ck-chat-title { font-weight: 600; flex: 1; line-height: 1.15; }
.ck-chat-header .ck-chat-title small { display: block; font-weight: 400; opacity: 0.85; font-size: calc(var(--ck-font-size) * 0.78); }
.ck-chat-header button {
    background: transparent;
    border: 0;
    color: #fff;
    cursor: pointer;
    padding: 6px;
    border-radius: 6px;
    font-family: inherit;
    font-size: calc(var(--ck-font-size) * 1.1);
    line-height: 1;
}
.ck-chat-header button:hover { background: rgba(255,255,255,0.15); }

.ck-chat-body {
    flex: 1;
    padding: 14px;
    overflow-y: auto;
    background: var(--ck-bg);
    display: flex;
    flex-direction: column;
    gap: 8px;
    scroll-behavior: smooth;
}
.ck-chat-body::-webkit-scrollbar { width: 8px; }
.ck-chat-body::-webkit-scrollbar-thumb { background: var(--ck-border); border-radius: 4px; }

.ck-msg {
    max-width: 82%;
    padding: 10px 12px;
    border-radius: calc(var(--ck-radius) * 1.2);
    font-size: var(--ck-font-size);
    line-height: 1.4;
    white-space: pre-wrap;
    word-wrap: break-word;
}
.ck-msg.ck-msg-user {
    align-self: flex-end;
    background: var(--ck-user-bg);
    color: var(--ck-user-fg);
    border-bottom-right-radius: 4px;
}
.ck-msg.ck-msg-bot {
    align-self: flex-start;
    background: var(--ck-bot-bg);
    color: var(--ck-bot-fg);
    border-bottom-left-radius: 4px;
}
.ck-msg.ck-msg-error {
    align-self: center;
    background: #fee2e2;
    color: #b91c1c;
    font-size: calc(var(--ck-font-size) * 0.9);
}

.ck-typing {
    align-self: flex-start;
    background: var(--ck-bot-bg);
    color: var(--ck-muted);
    padding: 10px 14px;
    border-radius: calc(var(--ck-radius) * 1.2);
    border-bottom-left-radius: 4px;
    display: inline-flex;
    gap: 4px;
    align-items: center;
}
.ck-typing span {
    width: 6px; height: 6px; border-radius: 50%;
    background: var(--ck-muted);
    animation: ck-bounce 1s infinite ease-in-out;
}
.ck-typing span:nth-child(2) { animation-delay: .15s; }
.ck-typing span:nth-child(3) { animation-delay: .3s; }
@keyframes ck-bounce {
    0%, 80%, 100% { transform: translateY(0); opacity: .4; }
    40% { transform: translateY(-4px); opacity: 1; }
}

.ck-chat-footer {
    border-top: 1px solid var(--ck-border);
    padding: 10px 12px;
    background: var(--ck-bg);
}
.ck-chat-form { display: flex; gap: 8px; align-items: flex-end; }
.ck-chat-form textarea {
    flex: 1;
    min-height: 38px;
    max-height: 120px;
    resize: none;
    border: 1px solid var(--ck-border);
    border-radius: calc(var(--ck-radius) * 0.9);
    padding: 9px 12px;
    font-family: inherit;
    font-size: inherit;
    color: var(--ck-text);
    background: var(--ck-bg);
    line-height: 1.3;
    box-sizing: border-box;
}
.ck-chat-form textarea:focus { outline: 2px solid var(--ck-primary); outline-offset: 1px; border-color: var(--ck-primary); }
.ck-chat-form button {
    background: var(--ck-primary);
    color: #fff;
    border: none;
    border-radius: calc(var(--ck-radius) * 0.9);
    padding: 0 16px;
    height: 38px;
    cursor: pointer;
    font-family: inherit;
    font-weight: 600;
}
.ck-chat-form button:disabled { opacity: 0.55; cursor: not-allowed; }
.ck-chat-form button:hover:not(:disabled) { filter: brightness(0.95); }

.ck-chat-footer .ck-chat-branding {
    text-align: center;
    font-size: calc(var(--ck-font-size) * 0.75);
    color: var(--ck-muted);
    margin-top: 6px;
}

@media (max-width: 480px) {
    .ck-chat-panel {
        right: 8px;
        left: 8px;
        bottom: 78px;
        width: auto;
        max-width: none;
        height: calc(100vh - 100px);
    }
    :host {
        right: 12px;
        bottom: 12px;
    }
    .ck-chat-bubble {
        width: 54px;
        height: 54px;
    }
}
`;
}
