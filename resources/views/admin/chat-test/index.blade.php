@extends('layouts.app')

@section('title', 'Chat Test')

@section('content_header_extra')
    <div>
        <h1 class="mb-1">Chat Test</h1>
        <p class="text-muted mb-0">Prueba conversacional del motor de reservas.</p>
    </div>
@stop

@section('content_body')
    <style>
        .chat-container { display: flex; flex-direction: column; height: 520px; border: 1px solid #dee2e6; border-radius: 6px; background: #f8f9fa; overflow: hidden; }
        .chat-messages { flex: 1; overflow-y: auto; padding: 16px; display: flex; flex-direction: column; gap: 10px; }
        .chat-input-area { border-top: 1px solid #dee2e6; padding: 12px; background: #fff; display: flex; gap: 8px; }
        .chat-input-area textarea { flex: 1; border: 1px solid #dee2e6; border-radius: 6px; padding: 8px 12px; font-size: 0.9rem; resize: none; }
        .chat-input-area button { border-radius: 6px; white-space: nowrap; }

        .msg { max-width: 85%; padding: 10px 14px; border-radius: 12px; font-size: 0.9rem; line-height: 1.6; white-space: pre-wrap; word-break: break-word; }
        .msg-user { background: #007bff; color: #fff; align-self: flex-end; border-bottom-right-radius: 4px; }
        .msg-assistant { background: #fff; color: #1a1a1a; align-self: flex-start; border: 1px solid #e0e0e0; border-bottom-left-radius: 4px; }
        .msg-clarification { background: #fff8e1; border-color: #ffe082; }
        .msg-error { background: #ffeef0; border-color: #f5c6cb; color: #721c24; }
        .msg-tool-tag { font-size: 0.65rem; color: #999; margin-top: 4px; }

        .debug-panel { border: 1px solid #dee2e6; border-radius: 4px; margin-top: 0.75rem; background: #fff; }
        .debug-panel summary { padding: 8px 12px; cursor: pointer; font-weight: 600; font-size: 0.8rem; color: #6c757d; list-style: none; display: flex; justify-content: space-between; align-items: center; }
        .debug-panel summary::-webkit-details-marker { display: none; }
        .debug-panel summary::after { content: '\f078'; font-family: 'Font Awesome 5 Free'; font-weight: 900; font-size: 0.65rem; color: #adb5bd; transition: transform 0.2s; }
        .debug-panel[open] summary { border-bottom: 1px solid #dee2e6; }
        .debug-panel[open] summary::after { transform: rotate(180deg); }
        .debug-panel .debug-body { padding: 8px 12px; }
        .json-block { background: #f4f6f9; border: 1px solid #dee2e6; border-radius: 4px; padding: 10px; font-family: 'SFMono-Regular', Consolas, monospace; font-size: 0.75rem; line-height: 1.4; white-space: pre-wrap; word-break: break-word; overflow: auto; max-height: 350px; color: #212529; }
        .exec-badge { font-size: 0.65rem; padding: 2px 6px; border-radius: 3px; font-weight: 600; }
        .exec-badge-direct { background: #e2e3e5; color: #383d41; }
        .exec-badge-mcp { background: #cce5ff; color: #004085; }
    </style>

    <div class="row">
        {{-- Left: Chat --}}
        <div class="col-lg-7">
            <div class="card shadow-sm border-0 mb-2">
                <div class="card-header bg-white d-flex justify-content-between align-items-center py-2">
                    <div class="d-flex align-items-center">
                        <select id="negocio_id" class="form-control form-control-sm mr-2" style="width:auto;">
                            @foreach($negocios as $negocio)
                                <option value="{{ $negocio->id }}">{{ $negocio->nombre }}</option>
                            @endforeach
                        </select>
                        <select id="exec_mode" class="form-control form-control-sm" style="width:auto;">
                            <option value="mcp" selected>MCP</option>
                            <option value="direct">Directo</option>
                        </select>
                    </div>
                    <button type="button" id="btn-clear" class="btn btn-xs btn-outline-secondary"><i class="fas fa-eraser"></i> Nueva conversación</button>
                </div>
                <div class="chat-container">
                    <div class="chat-messages" id="chat-messages">
                        <div class="text-center text-muted py-4" id="chat-empty">
                            <i class="fas fa-comments" style="font-size:2rem;opacity:0.3;"></i>
                            <p class="mt-2 mb-0" style="font-size:0.85rem;">Escribe un mensaje para empezar</p>
                        </div>
                    </div>
                    <div class="chat-input-area">
                        <textarea id="message" rows="2" placeholder="Escribe tu mensaje..."></textarea>
                        <button type="button" id="btn-send" class="btn btn-primary btn-sm"><i class="fas fa-paper-plane"></i></button>
                    </div>
                </div>
                <div class="card-footer bg-white py-2">
                    <div class="d-flex flex-wrap">
                        <button class="btn btn-xs btn-light border mr-1 mb-1 js-example" data-msg="Hola, queremos reservar una mesa">Reservar</button>
                        <button class="btn btn-xs btn-light border mr-1 mb-1 js-example" data-msg="Quiero cenar mañana, somos 4">Cena 4p</button>
                        <button class="btn btn-xs btn-light border mr-1 mb-1 js-example" data-msg="¿Qué servicios tenéis?">Servicios</button>
                        <button class="btn btn-xs btn-light border mr-1 mb-1 js-example" data-msg="¿Cuánto cuesta la cena para 4?">Precio</button>
                        <button class="btn btn-xs btn-light border mr-1 mb-1 js-example" data-msg="¿Puedo cancelar?">Cancelar</button>
                        <button class="btn btn-xs btn-light border mr-1 mb-1 js-example" data-msg="¿Dónde estáis?">Dirección</button>
                    </div>
                </div>
            </div>
        </div>

        {{-- Right: Debug --}}
        <div class="col-lg-5">
            <div id="debug-area">
                <div class="text-muted text-center py-4" id="debug-empty" style="font-size:0.85rem;">
                    <i class="fas fa-bug" style="font-size:1.5rem;opacity:0.2;"></i>
                    <p class="mt-2 mb-0">El debug aparecerá aquí tras enviar un mensaje</p>
                </div>
                <div id="debug-content-area" class="d-none">
                    <div class="d-flex align-items-center mb-2">
                        <span class="font-weight-bold mr-2" style="font-size:0.8rem;">Última ejecución</span>
                        <span id="exec-badge" class="exec-badge"></span>
                        <span id="mode-badge" class="exec-badge ml-1"></span>
                    </div>

                    <details class="debug-panel">
                        <summary>Tool + Params</summary>
                        <div class="debug-body">
                            <div class="mb-2"><strong style="font-size:0.8rem;">Tool:</strong> <code id="d-tool" style="font-size:0.8rem;"></code></div>
                            <pre id="d-params" class="json-block"></pre>
                        </div>
                    </details>
                    <details class="debug-panel">
                        <summary>Resultado tool</summary>
                        <div class="debug-body"><pre id="d-result" class="json-block"></pre></div>
                    </details>
                    <details class="debug-panel">
                        <summary>Debug completo</summary>
                        <div class="debug-body"><pre id="d-debug" class="json-block"></pre></div>
                    </details>
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
            const modeEl = document.getElementById('exec_mode');
            const debugEmpty = document.getElementById('debug-empty');
            const debugArea = document.getElementById('debug-content-area');
            let conversationId = null;

            const fmt = (o) => { try { return JSON.stringify(o, null, 2); } catch { return String(o); } };
            const modeLabels = { tool_result: 'Tool ejecutada', clarification: 'Aclaración', error: 'Error', ready: 'Lista', respond: 'Respuesta' };

            function addMessage(role, text, meta) {
                emptyEl.classList.add('d-none');
                const el = document.createElement('div');
                const modeClass = meta?.mode === 'clarification' ? ' msg-clarification' : (meta?.mode === 'error' ? ' msg-error' : '');
                el.className = `msg msg-${role}${role === 'assistant' ? modeClass : ''}`;
                el.textContent = text;
                if (role === 'assistant' && meta?.tool) {
                    const tag = document.createElement('div');
                    tag.className = 'msg-tool-tag';
                    tag.textContent = `${meta.tool}${meta.execMode === 'mcp' ? ' [MCP]' : ''}`;
                    el.appendChild(tag);
                }
                chatEl.appendChild(el);
                chatEl.scrollTop = chatEl.scrollHeight;
            }

            function renderHistory(history) {
                chatEl.innerHTML = '';
                if (!history || history.length === 0) { emptyEl.classList.remove('d-none'); chatEl.appendChild(emptyEl); return; }
                emptyEl.classList.add('d-none');
                history.forEach(h => addMessage(h.role, h.text, { mode: h.mode, tool: h.tool }));
            }

            function updateDebug(data) {
                debugEmpty.classList.add('d-none');
                debugArea.classList.remove('d-none');
                const execMode = data.execution_mode || 'direct';
                const execBadge = document.getElementById('exec-badge');
                execBadge.textContent = execMode === 'mcp' ? 'MCP' : 'Directo';
                execBadge.className = `exec-badge exec-badge-${execMode}`;
                const modeBadge = document.getElementById('mode-badge');
                const mode = data.mode || 'tool_result';
                modeBadge.textContent = modeLabels[mode] || mode;
                modeBadge.className = 'exec-badge';
                modeBadge.style.background = mode === 'clarification' ? '#fff3cd' : (mode === 'error' ? '#f8d7da' : '#d4edda');
                modeBadge.style.color = mode === 'clarification' ? '#856404' : (mode === 'error' ? '#721c24' : '#155724');
                document.getElementById('d-tool').textContent = data.tool || '—';
                document.getElementById('d-params').textContent = data.params ? fmt(data.params) : '—';
                document.getElementById('d-result').textContent = data.tool_result ? fmt(data.tool_result) : (mode === 'clarification' ? 'No ejecutada — pendiente de datos' : '—');
                document.getElementById('d-debug').textContent = data.debug ? fmt(data.debug) : '—';
            }

            document.querySelectorAll('.js-example').forEach(b => b.addEventListener('click', () => { msgEl.value = b.dataset.msg; msgEl.focus(); }));

            btnClear.addEventListener('click', async () => {
                await fetch('{{ route("admin.chat-test.clear-context") }}', { method: 'DELETE', headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Accept': 'application/json' } });
                chatEl.innerHTML = ''; emptyEl.classList.remove('d-none'); chatEl.appendChild(emptyEl);
                debugEmpty.classList.remove('d-none'); debugArea.classList.add('d-none');
                conversationId = null;
            });

            negocioEl.addEventListener('change', () => btnClear.click());

            async function send() {
                const text = msgEl.value.trim();
                if (!text) return;

                addMessage('user', text);
                msgEl.value = '';
                btnSend.disabled = true;

                // Typing indicator
                const typing = document.createElement('div');
                typing.className = 'msg msg-assistant';
                typing.innerHTML = '<i class="fas fa-circle-notch fa-spin"></i> Pensando...';
                typing.id = 'typing';
                chatEl.appendChild(typing);
                chatEl.scrollTop = chatEl.scrollHeight;

                try {
                    const payload = {
                        message: text,
                        negocio_id: parseInt(negocioEl.value),
                        mode: modeEl.value,
                    };

                    if (conversationId) {
                        payload.conversation_id = conversationId;
                    }

                    const res = await fetch('{{ route("admin.chat-test.execute") }}', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'X-Requested-With': 'XMLHttpRequest' },
                        body: JSON.stringify(payload),
                    });
                    const rawText = await res.text();
                    let data = null;

                    try {
                        data = rawText ? JSON.parse(rawText) : {};
                    } catch (parseError) {
                        const looksLikeHtml = /^\s*</.test(rawText || '');
                        throw new Error(
                            looksLikeHtml
                                ? 'El servidor devolvió HTML en vez de JSON. Revisa si la sesión expiró o si hubo un error interno.'
                                : ('Respuesta no válida del servidor: ' + (rawText || parseError.message))
                        );
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
