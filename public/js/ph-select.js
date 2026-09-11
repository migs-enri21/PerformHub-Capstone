document.addEventListener('DOMContentLoaded', () => {
    const selects = document.querySelectorAll('[data-ph-select]');

    if (!selects.length) {
        return;
    }

    function closeAll(except) {
        selects.forEach((wrap) => {
            if (wrap === except) {
                return;
            }

            wrap.classList.remove('is-open');
            wrap.querySelector('.ph-select-toggle')?.setAttribute('aria-expanded', 'false');
            const menu = wrap.querySelector('.ph-select-menu');
            if (menu) {
                menu.hidden = true;
            }
        });
    }

    function selectedValues(native) {
        return Array.from(native.selectedOptions).map((option) => option.value).filter(Boolean);
    }

    function setSingleValue(wrap, value, label) {
        const native = wrap.querySelector('.ph-select-native');
        const text = wrap.querySelector('.ph-select-label');
        const items = wrap.querySelectorAll('.ph-select-menu [role="option"]');

        if (native) {
            native.value = value;
            native.dispatchEvent(new Event('change', { bubbles: true }));
        }

        if (text) {
            text.textContent = label;
            text.classList.toggle('is-placeholder', value === '');
        }

        items.forEach((item) => {
            item.classList.toggle('is-selected', item.getAttribute('data-value') === value);
        });
    }

    function syncMultiple(wrap) {
        const native = wrap.querySelector('.ph-select-native');
        const text = wrap.querySelector('.ph-select-label');
        const items = wrap.querySelectorAll('.ph-select-menu [role="option"]');
        const placeholder = wrap.dataset.placeholder || 'Select';
        const values = native ? selectedValues(native) : [];

        items.forEach((item) => {
            item.classList.toggle('is-selected', values.includes(item.getAttribute('data-value') || ''));
        });

        if (text) {
            text.textContent = values.length ? values.join(' · ') : placeholder;
            text.classList.toggle('is-placeholder', values.length === 0);
        }

        if (native) {
            native.dispatchEvent(new Event('change', { bubbles: true }));
        }
    }

    function toggleMultipleValue(wrap, value) {
        const native = wrap.querySelector('.ph-select-native');
        if (!native || !value) {
            return;
        }

        const option = Array.from(native.options).find((item) => item.value === value);
        if (!option) {
            return;
        }

        option.selected = !option.selected;
        syncMultiple(wrap);
    }

    selects.forEach((wrap) => {
        const toggle = wrap.querySelector('.ph-select-toggle');
        const menu = wrap.querySelector('.ph-select-menu');
        const isMultiple = wrap.dataset.multiple === '1';

        if (!toggle || !menu) {
            return;
        }

        toggle.addEventListener('click', (event) => {
            event.preventDefault();
            const willOpen = !wrap.classList.contains('is-open');
            closeAll(wrap);
            wrap.classList.toggle('is-open', willOpen);
            toggle.setAttribute('aria-expanded', willOpen ? 'true' : 'false');
            menu.hidden = !willOpen;
        });

        menu.addEventListener('click', (event) => {
            const option = event.target.closest('[role="option"]');
            if (!option) {
                return;
            }

            const value = option.getAttribute('data-value') || '';

            if (isMultiple) {
                event.preventDefault();
                toggleMultipleValue(wrap, value);
                return;
            }

            setSingleValue(wrap, value, option.textContent.trim());
            closeAll();
        });
    });

    document.addEventListener('click', (event) => {
        if (!event.target.closest('[data-ph-select]')) {
            closeAll();
        }
    });

    document.addEventListener('keydown', (event) => {
        if (event.key === 'Escape') {
            closeAll();
        }
    });
});
