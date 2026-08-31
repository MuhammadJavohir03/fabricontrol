

/* ---- Accordion ---- */
function toggleAcc(btn){
    const acc = btn.closest('.acc');
    acc.classList.toggle('is-open');
}
document.querySelectorAll('.page-toc a[href^="#"]').forEach(a => {
    a.addEventListener('click', e => {
        const id = a.getAttribute('href').slice(1);
        const el = document.getElementById(id);
        if (!el) return;
        e.preventDefault();
        el.classList.add('is-open');
        el.scrollIntoView({ behavior: 'smooth', block: 'start' });
    });
});

/* ---- Search Select: native <select data-searchable> -> searchable ---- */
function enhanceSelects(root){
    (root || document).querySelectorAll('select[data-searchable]').forEach(sel => {
        if (sel.dataset.ssDone) return;
        sel.dataset.ssDone = '1';
        sel.style.display = 'none';

        const wrap = document.createElement('div');
        wrap.className = 'search-select';
        const input = document.createElement('input');
        input.type = 'text';
        input.className = 'ss-input';
        input.placeholder = sel.dataset.placeholder || 'Yozib qidiring...';
        input.autocomplete = 'off';
        const list = document.createElement('div');
        list.className = 'ss-list';

        const options = [...sel.options].map(o => ({
            value: o.value,
            label: o.textContent.trim(),
            disabled: o.disabled,
            selected: o.selected
        }));

        function render(q){
            const qq = (q || '').toLowerCase().trim();
            list.innerHTML = '';
            let n = 0;
            options.forEach(o => {
                if (!o.value && o.disabled) return; // skip placeholder empty disabled
                if (qq && !o.label.toLowerCase().includes(qq)) return;
                const div = document.createElement('div');
                div.className = 'ss-item' + (o.value === sel.value ? ' is-active' : '');
                div.textContent = o.label;
                div.dataset.value = o.value;
                div.addEventListener('mousedown', e => {
                    e.preventDefault();
                    sel.value = o.value;
                    input.value = o.label;
                    list.classList.remove('is-open');
                    sel.dispatchEvent(new Event('change', { bubbles: true }));
                });
                list.appendChild(div);
                n++;
            });
            if (!n) {
                const empty = document.createElement('div');
                empty.className = 'ss-empty';
                empty.textContent = 'Topilmadi';
                list.appendChild(empty);
            }
        }

        const selected = options.find(o => o.value === sel.value && o.value);
        if (selected) input.value = selected.label;

        input.addEventListener('focus', () => { render(input.value); list.classList.add('is-open'); });
        input.addEventListener('input', () => { render(input.value); list.classList.add('is-open'); });
        input.addEventListener('keydown', e => {
            if (e.key === 'Escape') list.classList.remove('is-open');
        });

        wrap.appendChild(input);
        wrap.appendChild(list);
        sel.parentNode.insertBefore(wrap, sel.nextSibling);
    });
}
document.addEventListener('click', e => {
    if (!e.target.closest('.search-select')) {
        document.querySelectorAll('.ss-list.is-open').forEach(l => l.classList.remove('is-open'));
    }
});
document.addEventListener('DOMContentLoaded', () => enhanceSelects(document));

/* Modal open/close */
function openModal(id){ const el = document.getElementById(id); if (el){ el.classList.add('is-open'); enhanceSelects(el); } }
function closeModal(id){ document.getElementById(id)?.classList.remove('is-open'); }

