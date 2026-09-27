import { api, refreshCsrf, config } from './api.js';
import { esc, field, errors, submit } from './ui.js';
export function authentication(mode, onLogin) {
    const reset = mode === 'reset'; const register = mode === 'register'; const forgot = mode === 'forgot';
    const title = reset ? 'Reset your CDTrack password' : register ? 'Create your account' : forgot ? 'Forgot your password?' : 'Welcome back';
    const description = reset ? 'Choose a new password with at least 12 characters.' : register ? 'Create a requestor account to submit and track your documents.' : forgot ? 'Enter your email to request a password reset link.' : 'Sign in to manage your documents.';
    return {
        html: `<main id="main" class="login-layout"><section class="login-story"><img src="${esc(config.logo)}" alt="College of Accounting Education emblem"><h1>Documents, accounted for.</h1><p>CAE CDTrack keeps your Dean’s Office requests, faculty routing, and collection details together.</p><p class="small">College of Accounting Education<br>University of Mindanao</p></section><section class="card auth-card"><h2>${title}</h2><p class="help-copy">${description}</p><form id="auth-form">${register ? field('name', 'Full name', '', 'text', 'required maxlength="255" autocomplete="name"') : ''}${field('email', 'Email address', reset ? config.resetEmail : '', 'email', 'required maxlength="255" autocomplete="email"')}${!forgot ? field('password', reset || register ? 'Password · at least 12 characters' : 'Password', '', 'password', `required ${reset || register ? 'minlength="12"' : ''} autocomplete="${reset || register ? 'new-password' : 'current-password'}"`) : ''}${register || reset ? field('password_confirmation', 'Confirm password', '', 'password', 'required minlength="12" autocomplete="new-password"') : ''}${!forgot && !reset && !register ? '<label class="check-row mb-3"><input class="form-check-input" name="remember" type="checkbox"> Keep me signed in</label>' : ''}${errors()}<button class="btn btn-primary full" type="submit">${reset ? 'Save new password' : register ? 'Create account' : forgot ? 'Send reset link' : 'Sign in'}</button></form><div class="auth-links">${mode === 'login' ? '<a href="#register">Create an account</a><a href="#forgot">Forgot password?</a>' : `<a href="${esc(config.home)}#login">Back to sign in</a>`}</div></section></main>`,
        bind() {
            document.querySelector('#auth-form').addEventListener('submit', event => {
                event.preventDefault(); submit(event.currentTarget, async data => {
                    const payload = Object.fromEntries(data); payload.remember = data.has('remember');
                    if (reset) payload.token = config.resetToken;
                    const user = await api(reset ? 'reset-password' : register ? 'register' : forgot ? 'forgot-password' : 'login', { method: 'POST', body: payload });
                    if (reset || forgot) {
                        document.querySelector('#auth-form').innerHTML = `<div class="alert alert-success" role="status">${reset ? 'Your password has been reset. You can now sign in.' : 'If an account matches this email, you’ll receive a password reset link.'}</div>`;
                    } else { await refreshCsrf(); onLogin(user); }
                });
            });
        }
    };
}
