@extends('layouts.app')

@section('title', 'Chat Test')

@section('content_header_extra')
    <div class="d-flex justify-content-between align-items-center">
        <div>
            <h1 class="mb-0" style="font-size:1.4rem;">Chat Test</h1>
        </div>
    </div>
@stop

@section('content_body')
    <style>
        /* ─── Layout ─── */
        .chat-wrapper { display: flex; gap: 16px; height: calc(100vh - 180px); min-height: 500px; }
        .chat-main { flex: 1; display: flex; flex-direction: column; min-width: 0; }
        .chat-sidebar { width: 340px; flex-shrink: 0; display: flex; flex-direction: column; overflow: hidden; }

        /* ─── Top bar ─── */
        .chat-topbar { background: #fff; border: 1px solid #e0e0e0; border-radius: 12px 12px 0 0; padding: 12px 16px; display: flex; align-items: center; justify-content: space-between; gap: 12px; }
        .chat-topbar-left { display: flex; align-items: center; gap: 10px; flex: 1; min-width: 0; }
        .negocio-select { appearance: none; background: #f5f6f8 url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' viewBox='0 0 12 12'%3E%3Cpath fill='%23666' d='M6 8L1 3h10z'/%3E%3C/svg%3E") no-repeat right 10px center; border: 1px solid #ddd; border-radius: 8px; padding: 6px 30px 6px 12px; font-size: 0.85rem; font-weight: 500; color: #333; cursor: pointer; max-width: 240px; text-overflow: ellipsis; transition: border-color 0.15s; }
        .negocio-select:focus { outline: none; border-color: #007bff; box-shadow: 0 0 0 3px rgba(0,123,255,0.1); }
        .mcp-toggle { display: flex; align-items: center; gap: 6px; font-size: 0.75rem; color: #888; user-select: none; cursor: pointer; white-space: nowrap; }
        .mcp-toggle input { display: none; }
        .mcp-toggle .toggle-track { width: 34px; height: 18px; background: #ccc; border-radius: 9px; position: relative; transition: background 0.2s; flex-shrink: 0; }
        .mcp-toggle .toggle-track::after { content: ''; position: absolute; top: 2px; left: 2px; width: 14px; height: 14px; background: #fff; border-radius: 50%; transition: transform 0.2s; box-shadow: 0 1px 2px rgba(0,0,0,0.15); }
        .mcp-toggle input:checked + .toggle-track { background: #007bff; }
        .mcp-toggle input:checked + .toggle-track::after { transform: translateX(16px); }
        .btn-new-chat { background: none; border: 1px solid #ddd; border-radius: 8px; padding: 5px 12px; font-size: 0.78rem; color: #666; cursor: pointer; transition: all 0.15s; display: flex; align-items: center; gap: 5px; white-space: nowrap; }
        .btn-new-chat:hover { background: #f5f5f5; border-color: #bbb; color: #333; }

        /* ─── Messages ─── */
        .chat-body { flex: 1; overflow-y: auto; padding: 20px 16px; display: flex; flex-direction: column; gap: 8px; background: #f5f6f8; border-left: 1px solid #e0e0e0; border-right: 1px solid #e0e0e0; }
        .msg { max-width: 82%; padding: 10px 14px; font-size: 0.88rem; line-height: 1.65; white-space: pre-wrap; word-break: break-word; animation: msgIn 0.2s ease-out; }
        @keyframes msgIn { from { opacity: 0; transform: translateY(6px); } to { opacity: 1; transform: translateY(0); } }
        .msg-user { background: #007bff; color: #fff; align-self: flex-end; border-radius: 16px 16px 4px 16px; }
        .msg-assistant { background: #fff; color: #1a1a1a; align-self: flex-start; border-radius: 16px 16px 16px 4px; border: 1px solid #e8e8e8; box-shadow: 0 1px 2px rgba(0,0,0,0.04); }
        .msg-clarification { background: #fffcf0; border-color: #f0e6c0; }
        .msg-error { background: #fff5f5; border-color: #f5c6cb; color: #721c24; }
        .msg-tool-tag { font-size: 0.62rem; color: #aaa; margin-top: 5px; letter-spacing: 0.02em; }
        .msg-typing { color: #999; font-style: italic; font-size: 0.82rem; }
        .chat-empty-state { text-align: center; padding: 60px 20px; color: #bbb; }
        .chat-empty-state i { font-size: 2.5rem; opacity: 0.25; }
        .chat-empty-state p { font-size: 0.85rem; margin-top: 12px; }

        /* ─── Input ─── */
        .chat-input-bar { background: #fff; border: 1px solid #e0e0e0; border-radius: 0 0 12px 12px; padding: 10px 12px; display: flex; gap: 8px; align-items: flex-end; }
        .chat-input-bar textarea { flex: 1; border: 1px solid #e8e8e8; border-radius: 10px; padding: 8px 14px; font-size: 0.88rem; resize: none; background: #fafafa; transition: border-color 0.15s, background 0.15s; line-height: 1.5; }
        .chat-input-bar textarea:focus { outline: none; border-color: #007bff; background: #fff; box-shadow: 0 0 0 3px rgba(0,123,255,0.08); }
        .chat-input-bar textarea::placeholder { color: #bbb; }
        .btn-send { width: 38px; height: 38px; border-radius: 50%; background: #007bff; color: #fff; border: none; display: flex; align-items: center; justify-content: center; cursor: pointer; transition: background 0.15s, transform 0.1s; flex-shrink: 0; }
        .btn-send:hover { background: #0069d9; }
        .btn-send:active { transform: scale(0.93); }
        .btn-send:disabled { background: #a0cfff; cursor: not-allowed; }

        /* ─── Quick actions ─── */
        .quick-actions { padding: 8px 12px; background: #fff; border-left: 1px solid #e0e0e0; border-right: 1px solid #e0e0e0; display: flex; flex-wrap: wrap; gap: 4px; }
        .quick-btn { background: #f0f2f5; border: none; border-radius: 14px; padding: 4px 12px; font-size: 0.73rem; color: #555; cursor: pointer; transition: all 0.15s; }
        .quick-btn:hover { background: #e2e6ea; color: #222; }

        /* ─── Debug sidebar ─── */
        .debug-card { background: #fff; border: 1px solid #e0e0e0; border-radius: 12px; flex: 1; overflow-y: auto; display: flex; flex-direction: column; }
        .debug-header { padding: 12px 14px; border-bottom: 1px solid #eee; display: flex; align-items: center; justify-content: space-between; }
        .debug-header h3 { font-size: 0.82rem; font-weight: 600; color: #666; margin: 0; }
        .debug-badges { display: flex; gap: 4px; }
        .dbg-badge { font-size: 0.65rem; padding: 2px 7px; border-radius: 4px; font-weight: 600; }
        .dbg-badge-mode { background: #d4edda; color: #155724; }
        .dbg-badge-mcp { background: #cce5ff; color: #004085; }
        .dbg-badge-direct { background: #e9ecef; color: #495057; }
        .dbg-badge-error { background: #f8d7da; color: #721c24; }
        .dbg-badge-clarification { background: #fff3cd; color: #856404; }
        .debug-body { flex: 1; overflow-y: auto; padding: 0; }
        .debug-section { border-bottom: 1px solid #f0f0f0; }
        .debug-section summary { padding: 8px 14px; cursor: pointer; font-weight: 600; font-size: 0.76rem; color: #888; list-style: none; display: flex; justify-content: space-between; align-items: center; user-select: none; }
        .debug-section summary::-webkit-details-marker { display: none; }
        .debug-section summary::after { content: '\f078'; font-family: 'Font Awesome 5 Free'; font-weight: 900; font-size: 0.6rem; color: #ccc; transition: transform 0.15s; }
        .debug-section[open] summary::after { transform: rotate(180deg); }
        .debug-section .debug-inner { padding: 6px 14px 12px; }
        .json-block { background: #f8f9fb; border: 1px solid #eee; border-radius: 6px; padding: 8px 10px; font-family: 'SFMono-Regular', Consolas, monospace; font-size: 0.7rem; line-height: 1.4; white-space: pre-wrap; word-break: break-word; overflow: auto; max-height: 280px; color: #333; }
        .debug-empty { text-align: center; padding: 40px 16px; color: #ccc; }
        .debug-empty i { font-size: 1.5rem; opacity: 0.3; }
        .debug-empty p { font-size: 0.78rem; margin-top: 8px; }

        /* ─── Responsive ─── */
        @media (max-width: 1100px) {
            .chat-wrapper { flex-direction: column; height: auto; }
            .chat-sidebar { width: 100%; max-height: 350px; }
            .chat-body { min-height: 350px; }
        }
    </style>

    <div class="chat-wrapper">
        {{-- Main chat area --}}
        <div class="chat-main">
            <div class="chat-topbar">
                <div class="chat-topbar-left">
                    <select id="negocio_id" class="negocio-select">
                        @foreach($negocios as $negocio)
                            <option value="{{ $negocio->id }}">{{ $negocio->nombre }}</option>
                        @endforeach
                    </select>
                    <label class="mcp-toggle" title="Ejecutar tools a través del MCP bridge">
                        <input type="checkbox" id="mcp_toggle" checked>
                        <span class="toggle-track"></span>
                        <span>MCP</span>
                    </label>
                </div>
                <button type="button" id="btn-clear" class="btn-new-chat">
                    <i class="fas fa-plus"></i> Nueva
                </button>
            </div>

            <div class="chat-body" id="chat-messages">
                <div class="chat-empty-state" id="chat-empty">
                    <i class="fas fa-comments"></i>
                    <p>Escribe un mensaje para empezar la conversación</p>
                </div>
            </div>

            <div class="quick-actions">
                <button class="quick-btn js-example" data-msg="Hola, queremos reservar">Reservar</button>
                <button class="quick-btn js-example" data-msg="Quiero cenar mañana, somos 4">Cena 4p</button>
                <button class="quick-btn js-example" data-msg="¿Qué servicios tenéis?">Servicios</button>
                <button class="quick-btn js-example" data-msg="¿Cuánto cuesta?">Precio</button>
                <button class="quick-btn js-example" data-msg="¿Puedo cancelar?">Cancelar</button>
                <button class="quick-btn js-example" data-msg="¿Dónde estáis?">Dirección</button>
                <button class="quick-btn js-example" data-msg="Me da igual, la que puedas">Delegar</button>
            </div>

            <div class="chat-input-bar">
                <textarea id="message" rows="1" placeholder="Escribe tu mensaje..."></textarea>
                <button type="button" id="btn-send" class="btn-send"><i class="fas fa-paper-plane" style="font-size:0.85rem;margin-left:1px;"></i></button>
            </div>
        </div>

        {{-- Debug sidebar --}}
        <div class="chat-sidebar">
            <div class="debug-card">
                <div class="debug-header">
                    <h3><i class="fas fa-bug mr-1"></i> Debug</h3>
                    <div class="debug-badges" id="debug-badges"></div>
                </div>
                <div class="debug-body">
                    <div class="debug-empty" id="debug-empty">
                        <i class="fas fa-terminal"></i>
                        <p>Envía un mensaje para ver el debug</p>
                    </div>
                    <div id="debug-content-area" class="d-none">
                        <details class="debug-section" open>
                            <summary>Tool + Params</summary>
                            <div class="debug-inner">
                                <div class="mb-2"><strong style="font-size:0.75rem;">Tool:</strong> <code id="d-tool" style="font-size:0.75rem;"></code></div>
                                <pre id="d-params" class="json-block"></pre>
                            </div>
                        </details>
                        <details class="debug-section">
                            <summary>Resultado</summary>
                            <div class="debug-inner"><pre id="d-result" class="json-block"></pre></div>
                        </details>
                        <details class="debug-section">
                            <summary>Debug completo</summary>
                            <div class="debug-inner"><pre id="d-debug" class="json-block"></pre></div>
                        </details>
                    </div>
                </div>
            </div>
        </div>
    </div>
@stop

@push('js')
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const msgEl = document.getElementById('message');
            const chatEl = document.getElementById('chat-messages');
            const emptyEl = document.getElementById('chat-empty');
            const btnSend = document.getElementById('btn-send');
            const btnClear = document.getElementById('btn-clear');
            const negocioEl = document.getElementById('negocio_id');
            const mcpToggle = document.getElementById('mcp_toggle');
            const debugEmpty = document.getElementById('debug-empty');
            const debugArea = document.getElementById('debug-content-area');
            const debugBadges = document.getElementById('debug-badges');
            let conversationId = null;

            const fmt = (o) => { try { return JSON.stringify(o, null, 2); } catch { return String(o); } };
            const modeLabels = { tool_result: 'Tool', clarification: 'Aclaración', error: 'Error', respond: 'Respuesta', greeting: 'Saludo', confirmation: 'Confirmación', confirmed: 'Confirmado' };

            // Auto-resize textarea
            msgEl.addEventListener('input', () => {
                msgEl.style.height = 'auto';
                msgEl.style.height = Math.min(msgEl.scrollHeight, 100) + 'px';
            });

            function addMessage(role, text, meta) {
                emptyEl.classList.add('d-none');
                const el = document.createElement('div');
                const modeClass = meta?.mode === 'clarification' ? ' msg-clarification' : (meta?.mode === 'error' ? ' msg-error' : '');
                el.className = `msg msg-${role}${role === 'assistant' ? modeClass : ''}`;
                el.textContent = text;
                if (role === 'assistant' && meta?.tool) {
                    const tag = document.createElement('div');
                    tag.className = 'msg-tool-tag';
                    tag.textContent = meta.tool + (meta.execMode === 'mcp' ? ' · MCP' : '');
                    el.appendChild(tag);
                }
                chatEl.appendChild(el);
                chatEl.scrollTop = chatEl.scrollHeight;
            }

            function updateDebug(data) {
                debugEmpty.classList.add('d-none');
                debugArea.classList.remove('d-none');

                const execMode = data.execution_mode || 'direct';
                const mode = data.mode || 'tool_result';

                const badgeClass = mode === 'error' ? 'dbg-badge-error' : (mode === 'clarification' ? 'dbg-badge-clarification' : 'dbg-badge-mode');
                debugBadges.innerHTML = `
                    <span class="dbg-badge ${execMode === 'mcp' ? 'dbg-badge-mcp' : 'dbg-badge-direct'}">${execMode === 'mcp' ? 'MCP' : 'Direct'}</span>
                    <span class="dbg-badge ${badgeClass}">${modeLabels[mode] || mode}</span>
                `;

                document.getElementById('d-tool').textContent = data.tool || '—';
                document.getElementById('d-params').textContent = data.params ? fmt(data.params) : '—';
                document.getElementById('d-result').textContent = data.tool_result ? fmt(data.tool_result) : (mode === 'clarification' ? 'Pendiente de datos' : '—');
                document.getElementById('d-debug').textContent = data.debug ? fmt(data.debug) : '—';
            }

            document.querySelectorAll('.js-example').forEach(b => b.addEventListener('click', () => { msgEl.value = b.dataset.msg; msgEl.focus(); msgEl.dispatchEvent(new Event('input')); }));

            btnClear.addEventListener('click', async () => {
                await fetch('{{ route("admin.chat-test.clear-context") }}', { method: 'DELETE', headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Accept': 'application/json' } });
                chatEl.innerHTML = ''; emptyEl.classList.remove('d-none'); chatEl.appendChild(emptyEl);
                debugEmpty.classList.remove('d-none'); debugArea.classList.add('d-none'); debugBadges.innerHTML = '';
                conversationId = null;
            });

            negocioEl.addEventListener('change', () => btnClear.click());

            async function send() {
                const text = msgEl.value.trim();
                if (!text) return;

                addMessage('user', text);
                msgEl.value = '';
                msgEl.style.height = 'auto';
                btnSend.disabled = true;

                const typing = document.createElement('div');
                typing.className = 'msg msg-assistant msg-typing';
                typing.innerHTML = '<i class="fas fa-circle-notch fa-spin mr-1"></i> Pensando...';
                typing.id = 'typing';
                chatEl.appendChild(typing);
                chatEl.scrollTop = chatEl.scrollHeight;

                try {
                    const payload = { message: text, negocio_id: parseInt(negocioEl.value), mode: mcpToggle.checked ? 'mcp' : 'direct' };
                    if (conversationId) payload.conversation_id = conversationId;

                    const res = await fetch('{{ route("admin.chat-test.execute") }}', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'X-Requested-With': 'XMLHttpRequest' },
                        body: JSON.stringify(payload),
                    });

                    const rawText = await res.text();
                    let data;
                    try {
                        data = rawText ? JSON.parse(rawText) : {};
                    } catch {
                        throw new Error(/^\s*</.test(rawText) ? 'Sesión expirada o error interno.' : 'Respuesta no válida del servidor.');
                    }
                    if (!res.ok) throw new Error(data.message || 'Error del servidor');

                    conversationId = data.conversation_id || conversationId;
                    document.getElementById('typing')?.remove();
                    addMessage('assistant', data.response || '—', { mode: data.mode, tool: data.tool, execMode: data.execution_mode });
                    updateDebug(data);
                } catch (err) {
                    document.getElementById('typing')?.remove();
                    addMessage('assistant', 'Error: ' + err.message, { mode: 'error' });
                } finally {
                    btnSend.disabled = false;
                    msgEl.focus();
                }
            }

            btnSend.addEventListener('click', send);
            msgEl.addEventListener('keydown', (e) => { if (e.key === 'Enter' && !e.shiftKey) { e.preventDefault(); send(); } });
        });
    </script>
@endpush
