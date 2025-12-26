/**
 * Developed by eBrook Group.
 * Copyright © 2026 eBrook Group (https://www.ebrook.com.tw)
 */

import './bootstrap';

function setupFlashAutoDismiss() {
    document.querySelectorAll('[data-flash]').forEach((el) => {
        const timeout = Number(el.getAttribute('data-timeout') || '3500');
        if (Number.isNaN(timeout) || timeout <= 0) return;

        window.setTimeout(() => {
            el.classList.add('opacity-0', 'translate-y-1');
            window.setTimeout(() => el.remove(), 250);
        }, timeout);
    });
}

function setupConfirmButtons() {
    document.querySelectorAll('[data-confirm]').forEach((el) => {
        el.addEventListener('click', (e) => {
            const message = el.getAttribute('data-confirm') || '确认执行该操作？';
            if (!window.confirm(message)) {
                e.preventDefault();
                e.stopPropagation();
            }
        });
    });
}

document.addEventListener('DOMContentLoaded', () => {
    setupFlashAutoDismiss();
    setupConfirmButtons();
});
