/**
 * Safe, lightweight client-side HTTP request helper.
 * Automatically fetches the CSRF token cookie for non-GET requests.
 */
export async function apiRequest<T = any>(
    route: { url: string; method: string },
    data?: any,
): Promise<T> {
    const headers: Record<string, string> = {
        Accept: 'application/json',
    };

    if (data) {
        headers['Content-Type'] = 'application/json';
    }

    const method = route.method.toUpperCase();

    if (method !== 'GET') {
        const token = document.cookie
            .split('; ')
            .find((row) => row.startsWith('XSRF-TOKEN='))
            ?.split('=')[1];

        if (token) {
            headers['X-XSRF-TOKEN'] = decodeURIComponent(token);
        }
    }

    const options: RequestInit = {
        method,
        headers,
    };

    if (data) {
        options.body = JSON.stringify(data);
    }

    const response = await fetch(route.url, options);

    if (!response.ok) {
        let errorMessage = 'An error occurred';
        try {
            const errJson = await response.json();
            errorMessage = errJson.message || errorMessage;
        } catch {
            errorMessage = await response.text();
        }
        throw new Error(errorMessage);
    }

    if (response.status === 204) {
        return null as any;
    }

    const json = await response.json();
    return (json.data !== undefined ? json.data : json) as T;
}
