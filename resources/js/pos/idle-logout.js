/**
 * Client-side companion to the server-side session timeout: watches for user
 * activity and redirects to logout after `minutes` of inactivity, so an
 * unattended register doesn't sit logged in indefinitely.
 */
export function initIdleLogout(minutes) {
    const timeoutMs = minutes * 60 * 1000;
    let timer = null;

    function logout() {
        const token = document.querySelector('meta[name="csrf-token"]')?.content;
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = '/logout';
        form.innerHTML = `<input type="hidden" name="_token" value="${token}">`;
        document.body.appendChild(form);
        form.submit();
    }

    function reset() {
        clearTimeout(timer);
        timer = setTimeout(logout, timeoutMs);
    }

    ['mousedown', 'keydown', 'touchstart', 'scroll'].forEach((event) => {
        window.addEventListener(event, reset, { passive: true });
    });

    reset();
}
