import Alpine from 'alpinejs';

window.Alpine = Alpine;
Alpine.start();

/**
 * Изпраща събитие за анализ. Използва sendBeacon, за да работи и при
 * напускане на страницата (изоставена форма).
 */
window.ntTrack = (name, properties = {}) => {
    const token = document.querySelector('meta[name="csrf-token"]')?.content;
    const payload = JSON.stringify({ name, properties });

    if (navigator.sendBeacon && !token) {
        return;
    }

    fetch('/sabitie', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': token },
        body: payload,
        keepalive: true,
    }).catch(() => {});
};
