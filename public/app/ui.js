export const esc = value => String(value ?? '').replace(/[&<>"']/g, character => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' })[character]);
export const statusNames = { awaiting_dropoff: 'Awaiting drop-off', received: 'Received', processing: 'Processing', ready: 'Ready for claim', partially_ready: 'Partially ready', partially_claimed: 'Partially claimed', claimed: 'Claimed', completed: 'Completed' };
export const date = value => value ? new Intl.DateTimeFormat('en-PH', { dateStyle: 'medium' }).format(new Date(value.length === 10 ? `${value}T00:00:00` : value)) : '—';
export const timestamp = value => value ? new Intl.DateTimeFormat('en-PH', { dateStyle: 'medium', timeStyle: 'short' }).format(new Date(value)) : '—';
export const link = (text, route, primary = false) => `<a class="btn ${primary ? 'btn-primary' : 'btn-outline-secondary'}" href="#${route}">${esc(text)}</a>`;
export const badge = status => `<span class="badge ${status.includes('ready') ? 'ready' : status.includes('claim') || status === 'completed' ? 'claimed' : status === 'awaiting_dropoff' ? 'awaiting' : esc(status)}">${esc(statusNames[status] || status)}</span>`;
export const head = (title, description = '', action = '') => `<div class="page-head"><div><h1>${esc(title)}</h1><p>${esc(description)}</p></div>${action}</div>`;
export const panel = (title, body, action = '') => `<section class="card"><div class="card-header"><h2>${esc(title)}</h2>${action}</div><div class="card-body">${body}</div></section>`;
export const empty = (title, message, action = '') => `<div class="blank-state"><h2>${esc(title)}</h2><p>${esc(message)}</p>${action}</div>`;
export const field = (name, label, value = '', type = 'text', attrs = '') => `<div class="field"><label class="form-label" for="${esc(name)}">${esc(label)}</label><input class="form-control" id="${esc(name)}" name="${esc(name)}" type="${type}" value="${esc(value)}" ${attrs}></div>`;
export const textarea = (name, label, value = '', max = 2000) => `<div class="field"><label class="form-label" for="${name}">${esc(label)}</label><textarea class="form-control" id="${name}" name="${name}" rows="3" maxlength="${max}">${esc(value)}</textarea></div>`;
export const options = (items, selected, label = 'name') => items.map(item => `<option value="${item.id}" ${String(selected) === String(item.id) ? 'selected' : ''}>${esc(item[label])}</option>`).join('');
export const select = (name, label, content, attrs = '') => `<div class="field"><label class="form-label" for="${name}">${esc(label)}</label><select class="form-select" name="${name}" id="${name}" ${attrs}>${content}</select></div>`;
export const errors = () => '<div class="error-box" role="alert" tabindex="-1"></div>';
export const pager = page => `<div class="page-controls"><span>${page.total} record${page.total === 1 ? '' : 's'} · Page ${page.current_page} of ${page.last_page}</span><div class="actions"><button class="btn btn-sm" data-page="${page.current_page - 1}" ${page.current_page <= 1 ? 'disabled' : ''}>Previous</button><button class="btn btn-sm" data-page="${page.current_page + 1}" ${page.current_page >= page.last_page ? 'disabled' : ''}>Next</button></div></div>`;
export function showError(element, error) {
    const messages = Object.values(error.errors || {}).flat();
    element.innerHTML = `<div class="alert alert-danger">${esc(messages.length ? messages.join(' ') : error.message)}</div>`;
    element.focus();
}
let timer;
export function toast(message) {
    const element = document.querySelector('#toast'); element.textContent = message; element.classList.add('visible', 'show');
    clearTimeout(timer); timer = setTimeout(() => element.classList.remove('visible', 'show'), 4500);
}
export async function submit(form, action) {
    if (form.dataset.busy) return;
    form.dataset.busy = 'true';
    const buttons = [...form.querySelectorAll('button[type="submit"],button:not([type])')];
    const previous = buttons.map(button => button.textContent);
    buttons.forEach(button => { button.disabled = true; button.textContent = 'Saving…'; });
    const errorBox = form.querySelector('.error-box'); if (errorBox) errorBox.innerHTML = '';
    try { await action(new FormData(form)); }
    catch (error) { if (errorBox) showError(errorBox, error); else toast(error.message); }
    finally { delete form.dataset.busy; buttons.forEach((button, index) => { button.disabled = false; button.textContent = previous[index]; }); }
}
const paths = { dashboard: '<rect x="3" y="3" width="7" height="7"/><rect x="14" y="3" width="7" height="7"/><rect x="3" y="14" width="7" height="7"/><rect x="14" y="14" width="7" height="7"/>', requests: '<path d="M7 3h7l4 4v14H5V3h2m7 0v5h4M8 12h7M8 16h7"/>', new: '<path d="M12 5v14M5 12h14"/>', profile: '<circle cx="12" cy="8" r="4"/><path d="M4 21v-2a8 8 0 0 1 16 0v2"/>', storage: '<rect x="3" y="3" width="18" height="18" rx="2"/><path d="M3 12h18M9 7h6M9 17h6"/>', history: '<path d="M3 11a9 9 0 1 1 3 7M3 4v7h7M12 7v5l3 2"/>', logout: '<path d="M10 4H4v16h6M10 12h11m-4-4 4 4-4 4"/>', menu: '<path d="M4 6h16M4 12h16M4 18h16"/>' };
export const icon = name => `<svg class="icon" viewBox="0 0 24 24" aria-hidden="true">${paths[name] || paths.requests}</svg>`;
