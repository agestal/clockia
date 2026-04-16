export function createChatApi({ apiBase, businessId, widgetKey }) {
    const base = apiBase.replace(/\/$/, '') + `/businesses/${businessId}/chat`;

    async function request(path, { method = 'GET', body = null, query = null } = {}) {
        let url = base + path;
        if (query) {
            const params = new URLSearchParams();
            Object.entries(query).forEach(([k, v]) => {
                if (v !== null && v !== undefined && v !== '') params.append(k, v);
            });
            const qs = params.toString();
            if (qs) url += (url.includes('?') ? '&' : '?') + qs;
        }

        const response = await fetch(url, {
            method,
            headers: {
                'Accept': 'application/json',
                'Content-Type': 'application/json',
                'X-Widget-Key': widgetKey,
            },
            body: body ? JSON.stringify(body) : undefined,
            mode: 'cors',
            credentials: 'omit',
        });

        const contentType = response.headers.get('content-type') || '';
        const payload = contentType.includes('application/json') ? await response.json().catch(() => ({})) : {};

        if (!response.ok) {
            const error = new Error(payload.error || payload.message || 'No se pudo contactar con el chat.');
            error.status = response.status;
            throw error;
        }

        return payload;
    }

    return {
        greeting: () => request('/greeting'),
        history: (conversationId) => request('/history', { query: { conversation_id: conversationId } }),
        send: (message, conversationId) => request('/message', {
            method: 'POST',
            body: { message, conversation_id: conversationId || null },
        }),
    };
}
