const config = document.querySelector('#app').dataset;
let csrf = document.querySelector('meta[name="csrf-token"]').content;
export class ApiError extends Error {
    constructor(message, status, errors = {}) { super(message); this.status = status; this.errors = errors; }
}
export async function refreshCsrf() {
    const response = await fetch(`${config.api}/csrf`, { credentials: 'same-origin', headers: { Accept: 'application/json' } });
    if (!response.ok) throw new ApiError('Could not establish a secure session. Refresh the page and try again.', response.status);
    csrf = (await response.json()).token;
}
export async function api(path, { method = 'GET', body, signal } = {}) {
    const headers = { Accept: 'application/json' };
    if (method !== 'GET') headers['X-CSRF-TOKEN'] = csrf;
    if (body && !(body instanceof FormData)) { headers['Content-Type'] = 'application/json'; body = JSON.stringify(body); }
    let response;
    try { response = await fetch(`${config.api}/${path}`, { method, body, headers, credentials: 'same-origin', signal }); }
    catch (error) { if (error.name === 'AbortError') throw error; throw new ApiError('Connection interrupted. Check your connection and try again.', 0); }
    const data = response.status === 204 ? null : await response.json().catch(() => ({}));
    if (!response.ok) {
        const message = response.status === 419 ? 'Your form session expired. Please try again.' : response.status === 413 ? 'The file exceeds the server upload limit. Choose a smaller file.' : response.status === 429 ? 'Too many attempts. Please wait a minute and try again.' : response.status >= 500 ? 'The server could not complete this action. Please try again.' : data.message || 'This action could not be completed.';
        if (response.status === 419) await refreshCsrf();
        if (response.status === 401) window.dispatchEvent(new Event('session-expired'));
        throw new ApiError(message, response.status, data.errors);
    }
    return data;
}
export { config };
