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

    function formatMultipleLabel(values, placeholder) {
        if (!values.length) {
            return placeholder;
        }

        if (values.length <= 2) {
            return values.join(' · ');
        }

        return values[0] + ' + ' + (values.length - 1) + ' more';
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
            text.textContent = formatMultipleLabel(values, placeholder);
            text.classList.toggle('is-placeholder', values.length === 0);
        }

        const toggle = wrap.querySelector('.ph-select-toggle');
        if (toggle) {
            toggle.title = values.length ? values.join(' · ') : '';
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

    function flattenGroups(groups) {
        const seen = new Set();
        const options = [];

        (groups || []).forEach((group) => {
            (group.options || []).forEach((value) => {
                if (!value || seen.has(value)) {
                    return;
                }

                seen.add(value);
                options.push(value);
            });
        });

        return options;
    }

    function setGroups(wrap, groups) {
        const native = wrap.querySelector('.ph-select-native');
        const menu = wrap.querySelector('.ph-select-menu');
        const isMultiple = wrap.dataset.multiple === '1';
        const placeholder = wrap.dataset.placeholder || 'Select';
        const emptyMessage = wrap.dataset.emptyMessage || 'Select a category first';

        if (!native || !menu) {
            return;
        }

        const optionValues = flattenGroups(groups);
        const keep = new Set(selectedValues(native).filter((value) => optionValues.includes(value)));

        native.innerHTML = '';

        if (!isMultiple) {
            const blank = document.createElement('option');
            blank.value = '';
            blank.textContent = placeholder;
            native.appendChild(blank);
        }

        optionValues.forEach((value) => {
            const option = document.createElement('option');
            option.value = value;
            option.textContent = value;
            option.selected = keep.has(value);
            native.appendChild(option);
        });

        menu.innerHTML = '';

        if (!optionValues.length) {
            const empty = document.createElement('li');
            empty.className = 'ph-select-empty';
            empty.textContent = emptyMessage;
            menu.appendChild(empty);
        } else {
            if (!isMultiple) {
                const blankItem = document.createElement('li');
                blankItem.setAttribute('role', 'option');
                blankItem.dataset.value = '';
                blankItem.textContent = placeholder;
                menu.appendChild(blankItem);
            }

            (groups || []).forEach((group) => {
                const groupOptions = (group.options || []).filter(Boolean);

                if (!groupOptions.length) {
                    return;
                }

                if (group.label) {
                    const header = document.createElement('li');
                    header.className = 'ph-select-group';
                    header.setAttribute('aria-hidden', 'true');
                    header.textContent = group.label;
                    menu.appendChild(header);
                }

                groupOptions.forEach((value) => {
                    const item = document.createElement('li');
                    item.setAttribute('role', 'option');
                    item.dataset.value = value;
                    item.textContent = value;
                    menu.appendChild(item);
                });
            });
        }

        if (isMultiple) {
            syncMultiple(wrap);
        } else {
            const current = native.value || '';
            const selectedItem = menu.querySelector(`[role="option"][data-value="${CSS.escape(current)}"]`);
            setSingleValue(wrap, current, selectedItem ? selectedItem.textContent.trim() : placeholder);
        }
    }

    function openOptionRequest(wrap, value) {
        if (value.toLowerCase() !== 'other') {
            return false;
        }
    
        const modalEl = document.getElementById('optionRequestModal');
        if (!modalEl || typeof bootstrap === 'undefined') {
            return false;
        }
    
        const native = wrap.querySelector('.ph-select-native');
        const fieldName = native ? native.getAttribute('name') || '' : '';
        const type = fieldName.startsWith('genre') ? 'genre' : (fieldName.startsWith('specialty') ? 'specialty' : '');
    
        if (!type) {
            return false;
        }
    
        const otherOption = native
            ? Array.from(native.options).find((item) => item.value.toLowerCase() === 'other')
            : null;
    
        if (otherOption) {
            otherOption.selected = false;
        }
    
        if (wrap.dataset.multiple === '1') {
            syncMultiple(wrap);
        }
    
        closeAll();
    
        const typeInput = document.getElementById('optionRequestType');
        const title = document.getElementById('optionRequestModalLabel');
        const nameInput = document.getElementById('optionRequestName');
        const categoryWrap = document.getElementById('optionRequestCategoryWrap');
        const categorySelect = document.getElementById('optionRequestCategory');
        const isGenre = type === 'genre';
    
        if (typeInput) {
            typeInput.value = type;
        }
    
        if (title) {
            title.textContent = isGenre ? 'Request a genre' : 'Request a specialty';
        }
    
        if (nameInput) {
            nameInput.value = '';
            nameInput.placeholder = isGenre ? 'e.g. OPM' : 'e.g. Ukulele';
        }
    
        if (categoryWrap) {
            categoryWrap.classList.toggle('d-none', !isGenre);
        }
    
        if (categorySelect) {
            categorySelect.required = isGenre;
            const checked = document.querySelector('#profileCategoryGrid input[name="category_ids[]"]:checked');
            categorySelect.value = checked ? checked.value : '';
        }
    
        bootstrap.Modal.getOrCreateInstance(modalEl).show();
    
        return true;
    }

    window.PhSelect = { setGroups };

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
        
            if (openOptionRequest(wrap, value)) {
                event.preventDefault();
                return;
            }
        
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
