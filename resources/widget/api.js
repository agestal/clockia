export function createApiClient({ apiBase, businessId, widgetKey }) {
    const base = apiBase.replace(/\/$/, '') + `/businesses/${businessId}`;

    async function request(path, { method = 'GET', body = null, query = null } = {}) {
        let url = base + path;
        if (query) {
            const params = new URLSearchParams();
            Object.entries(query).forEach(([key, value]) => {
                if (value !== null && value !== undefined && value !== '') {
                    params.append(key, value);
                }
            });
            const qs = params.toString();
            if (qs) {
                url += (url.includes('?') ? '&' : '?') + qs;
            }
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
            const error = new Error(payload.error || payload.message || 'Error al contactar con el servidor.');
            error.status = response.status;
            error.payload = payload;
            throw error;
        }

        return payload;
    }

    return {
        config: () => request('/config'),
        calendar: (year, month, extras = {}) => request('/availability/calendar', { query: { year, month, ...extras } }),
        date: (date, participants = null) => request('/availability/date', { query: { date, participants } }),
        check: (payload) => request('/availability/check', { method: 'POST', body: payload }),
        book: (payload) => request('/bookings', { method: 'POST', body: payload }),
        lookupBooking: (payload) => request('/bookings/lookup', { method: 'POST', body: payload }),
        requestCancellation: (payload) => request('/bookings/cancel', { method: 'POST', body: payload }),
    };
}
