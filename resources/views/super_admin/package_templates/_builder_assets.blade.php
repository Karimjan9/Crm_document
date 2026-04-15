<style>
.package-admin-shell { font-family:'Manrope',sans-serif; color:#0f172a; background:linear-gradient(180deg,#f8fafc,#eef2ff); min-height:100vh; padding-bottom:48px; }
.package-admin-hero { padding:28px; border-radius:28px; color:#fff; background:linear-gradient(135deg,#0f172a,#0f766e 48%,#2563eb); box-shadow:0 30px 70px rgba(15,23,42,.22); }
.package-admin-hero__eyebrow { font-size:12px; font-weight:800; letter-spacing:.18em; text-transform:uppercase; color:rgba(226,232,240,.88); }
.package-admin-hero h1 { margin:10px 0 8px; font-size:2rem; font-weight:800; }
.template-builder { display:grid; grid-template-columns:minmax(0,1.85fr) minmax(320px,.9fr); gap:24px; margin-top:24px; }
.template-builder__main,.template-builder__aside { display:flex; flex-direction:column; gap:20px; }
.template-builder__card { border:1px solid rgba(148,163,184,.18); border-radius:24px; background:rgba(255,255,255,.94); box-shadow:0 20px 45px rgba(15,23,42,.08); padding:24px; }
.template-builder__card--sticky { position:sticky; top:100px; }
.template-builder__section-head { display:flex; align-items:flex-start; justify-content:space-between; gap:16px; margin-bottom:18px; }
.template-builder__section-head h3,.item-card__head strong,.preview-item strong { margin:0; font-weight:800; }
.template-builder__section-head p,.item-card__meta span,.preview-item small,.empty-state { color:#64748b; }
.template-grid { display:grid; gap:16px; }
.template-grid--double { grid-template-columns:repeat(2,minmax(0,1fr)); }
.field { display:flex; flex-direction:column; gap:8px; }
.field__label { font-size:.92rem; font-weight:700; }
.field__control { min-height:46px; width:100%; border-radius:14px; border:1px solid rgba(148,163,184,.35); background:#fff; padding:0 14px; }
.field__control--textarea { min-height:126px; padding:14px; resize:vertical; }
.field__error { color:#dc2626; font-size:.83rem; font-weight:700; }
.toggle-field { display:inline-flex; align-items:center; gap:10px; font-weight:700; margin-bottom:18px; }
.price-stack,.item-list,.preview-list,.addon-list { display:grid; gap:12px; }
.price-chip,.preview-item,.item-card,.addon-item { border:1px solid rgba(148,163,184,.18); border-radius:18px; background:#fff; }
.price-chip { padding:16px 18px; }
.price-chip span { display:block; font-size:.82rem; font-weight:700; color:#64748b; }
.price-chip strong { display:block; margin-top:6px; font-size:1.3rem; font-weight:800; }
.price-chip--accent { background:linear-gradient(135deg,#0f766e,#2563eb); border-color:transparent; color:#fff; }
.price-chip--accent span,.price-chip--accent strong { color:#fff; }
.item-card { padding:18px; background:linear-gradient(180deg,#f8fafc,#fff); }
.item-card__head,.item-card__footer,.form-actions { display:flex; align-items:flex-start; justify-content:space-between; gap:12px; }
.item-card__meta { display:flex; align-items:center; gap:10px; }
.chip { display:inline-flex; align-items:center; border-radius:999px; padding:7px 12px; font-size:.82rem; font-weight:800; background:rgba(15,118,110,.1); color:#0f766e; }
.btn-pill,.btn-danger-soft,.btn-primary-strong,.btn-secondary-soft,.mode-btn { border:0; border-radius:14px; font-weight:800; text-decoration:none; }
.btn-primary-strong { min-height:46px; padding:0 16px; background:linear-gradient(135deg,#0f766e,#2563eb); color:#fff; }
.btn-secondary-soft { min-height:46px; padding:0 16px; background:rgba(241,245,249,.96); color:#334155; }
.btn-danger-soft { min-width:40px; min-height:40px; background:rgba(254,226,226,.92); color:#b91c1c; }
.mode-row { display:flex; flex-wrap:wrap; gap:8px; margin-top:14px; }
.mode-btn { min-height:40px; padding:0 14px; background:rgba(241,245,249,.96); color:#475569; }
.mode-btn.is-active { background:linear-gradient(135deg,#0f766e,#2563eb); color:#fff; }
.item-card__process { display:none; margin-top:14px; }
.addon-grid { display:grid; grid-template-columns:repeat(3,minmax(0,1fr)); gap:12px; margin-top:14px; }
.addon-panel { border:1px solid rgba(148,163,184,.18); border-radius:18px; background:rgba(255,255,255,.96); padding:12px; }
.addon-panel__head { display:flex; align-items:center; justify-content:space-between; gap:10px; margin-bottom:10px; }
.addon-panel__head h4 { margin:0; font-size:.88rem; font-weight:800; }
.addon-item { display:flex; gap:10px; padding:10px; align-items:flex-start; }
.addon-item.is-selected { border-color:rgba(14,165,164,.36); background:rgba(240,253,250,.96); }
.addon-item__copy { display:flex; flex-direction:column; gap:3px; min-width:0; }
.addon-item__copy strong { font-size:.86rem; font-weight:800; }
.addon-item__copy span { color:#64748b; font-size:.76rem; }
.addon-item__meta { margin-left:auto; text-align:right; min-width:78px; }
.addon-item__meta strong { display:block; font-size:.8rem; }
.addon-item__meta span { display:block; font-size:.74rem; color:#0f766e; }
.preview-item { padding:12px 14px; display:flex; align-items:center; justify-content:space-between; gap:12px; }
.form-actions { margin-top:20px; }
.empty-state { padding:14px; border-radius:16px; border:1px dashed rgba(148,163,184,.45); background:rgba(248,250,252,.92); }
@media (max-width:1199px){ .template-builder{grid-template-columns:1fr}.template-builder__card--sticky{position:static} }
@media (max-width:767px){ .template-grid--double,.addon-grid{grid-template-columns:1fr}.item-card__head,.item-card__footer,.form-actions{flex-direction:column;align-items:stretch}.btn-primary-strong,.btn-secondary-soft{width:100%} }
</style>

<script>
(function () {
    const data = window.packageBuilderData;
    if (!data) return;

    const list = document.getElementById('packageItemBuilderList');
    const payloadInput = document.getElementById('itemsPayloadInput');
    const promoInput = document.getElementById('promoPriceInput');
    const addButton = document.getElementById('addPackageItemButton');
    const basePriceValue = document.getElementById('basePriceValue');
    const promoPricePreview = document.getElementById('promoPricePreview');
    const savingValue = document.getElementById('savingValue');
    const includedItemsPreview = document.getElementById('includedItemsPreview');
    const includedItemsCount = document.getElementById('includedItemsCount');

    const state = {
        items: Array.isArray(data.initial?.items) && data.initial.items.length
            ? data.initial.items.map(normalizeItem)
            : [emptyItem()],
    };

    const optionHtml = (items, label) => items.map(item => `<option value="${item.id}">${label(item)}</option>`).join('');
    const money = value => `${Number(value || 0).toLocaleString()} so'm`;
    const addonKey = item => `${item.sourceType}:${item.id}`;

    function emptyItem() {
        return normalizeItem({});
    }

    function normalizeItem(item) {
        return {
            document_type_id: item.document_type_id || '',
            service_id: item.service_id || '',
            process_mode: item.process_mode || 'service',
            selection_mode: item.selection_mode || '',
            direction_type_id: item.direction_type_id || '',
            apostil_group1_id: item.apostil_group1_id || '',
            apostil_group2_id: item.apostil_group2_id || '',
            consul_id: item.consul_id || '',
            consulate_type_id: item.consulate_type_id || '',
            selected_addons: Array.isArray(item.selected_addons)
                ? item.selected_addons.map(entry => ({ sourceType: entry.sourceType || entry.type, id: Number(entry.id) })).filter(entry => entry.sourceType && entry.id)
                : [],
        };
    }

    function render() {
        if (!state.items.length) {
            list.innerHTML = `<div class="empty-state">Paketga kamida bitta element qo'shing.</div>`;
            sync();
            return;
        }

        list.innerHTML = state.items.map((item, index) => renderCard(item, index)).join('');
        list.querySelectorAll('.item-card').forEach(bindCard);
        sync();
    }

    function renderCard(item, index) {
        return `
            <div class="item-card" data-index="${index}">
                <div class="item-card__head">
                    <div>
                        <strong>Element #${index + 1}</strong>
                        <div class="item-card__meta"><span class="item-summary">${buildSummary(item)}</span></div>
                    </div>
                    <div class="item-card__meta">
                        <span class="chip item-price">${money(calculate(item).basePrice)}</span>
                        <button type="button" class="btn-danger-soft remove-item">×</button>
                    </div>
                </div>

                <div class="template-grid template-grid--double">
                    <label class="field">
                        <span class="field__label">Hujjat turi</span>
                        <select class="field__control field-document">
                            <option value="">Tanlang...</option>
                            ${optionHtml(data.documentTypes || [], item => item.name)}
                        </select>
                    </label>
                    <label class="field">
                        <span class="field__label">Xizmat</span>
                        <select class="field__control field-service">
                            <option value="">Tanlang...</option>
                            ${optionHtml(data.services || [], item => `${item.name} (${money(item.price)})`)}
                        </select>
                    </label>
                </div>

                <div class="mode-row">
                    ${[['service','Xizmat'],['apostil','Apostil'],['consul','Legalizatsiya']].map(([mode,label]) => `<button type="button" class="mode-btn ${item.process_mode === mode ? 'is-active' : ''}" data-mode="${mode}">${label}</button>`).join('')}
                </div>

                <div class="item-card__process process-apostil">
                    <div class="template-grid template-grid--double">
                        <label class="field">
                            <span class="field__label">Yo'nalish</span>
                            <select class="field__control field-direction">
                                <option value="">Tanlang...</option>
                                ${optionHtml(data.directions || [], entry => entry.name)}
                            </select>
                        </label>
                        <div></div>
                    </div>
                    <div class="template-grid template-grid--double">
                        <label class="field">
                            <span class="field__label">Apostil 1-guruh</span>
                            <select class="field__control field-apostil1">
                                <option value="">Tanlang...</option>
                                ${optionHtml((data.apostilStatics || []).filter(entry => Number(entry.group_id) === 1), entry => `${entry.name} (${money(entry.price)})`)}
                            </select>
                        </label>
                        <label class="field">
                            <span class="field__label">Apostil 2-guruh</span>
                            <select class="field__control field-apostil2">
                                <option value="">Tanlang...</option>
                                ${optionHtml((data.apostilStatics || []).filter(entry => Number(entry.group_id) === 2), entry => `${entry.name} (${money(entry.price)})`)}
                            </select>
                        </label>
                    </div>
                </div>

                <div class="item-card__process process-consul">
                    <div class="mode-row">
                        ${[['consul','Konsullik'],['legalization','Legalizatsiya'],['mixed','Mix']].map(([mode,label]) => `<button type="button" class="mode-btn ${item.selection_mode === mode ? 'is-active' : ''}" data-selection="${mode}">${label}</button>`).join('')}
                    </div>
                    <div class="template-grid template-grid--double" style="margin-top:12px;">
                        <label class="field field-consul-wrap">
                            <span class="field__label">Konsullik</span>
                            <select class="field__control field-consul">
                                <option value="">Tanlang...</option>
                                ${optionHtml(data.consuls || [], entry => `${entry.name} (${money(entry.amount)})`)}
                            </select>
                        </label>
                        <label class="field field-consulate-wrap">
                            <span class="field__label">Legalizatsiya turi</span>
                            <select class="field__control field-consulate">
                                <option value="">Tanlang...</option>
                                ${optionHtml(data.consulateTypes || [], entry => `${entry.name} (${money(entry.amount)})`)}
                            </select>
                        </label>
                    </div>
                </div>

                <div class="addon-grid">
                    ${renderAddonPanel('document',"Hujjat qo'shimchalari")}
                    ${renderAddonPanel('direction',"Yo'nalish qo'shimchalari")}
                    ${renderAddonPanel('service',"Xizmat qo'shimchalari")}
                </div>

                <div class="item-card__footer">
                    <div class="item-card__meta"><span class="item-deadline">${calculate(item).deadline ? `Deadline: ${calculate(item).deadline} kun` : 'Deadline tanlangan itemlardan olinadi'}</span></div>
                    <span class="chip">${money(calculate(item).basePrice)}</span>
                </div>
            </div>
        `;
    }

    function renderAddonPanel(type, title) {
        return `
            <div class="addon-panel">
                <div class="addon-panel__head">
                    <h4>${title}</h4>
                    <span class="meta-${type}">0 ta</span>
                </div>
                <div class="addon-list list-${type}"></div>
            </div>
        `;
    }

    function bindCard(card) {
        const index = Number(card.dataset.index);
        const item = state.items[index];

        setValue(card, '.field-document', item.document_type_id);
        setValue(card, '.field-service', item.service_id);
        setValue(card, '.field-direction', item.direction_type_id);
        setValue(card, '.field-apostil1', item.apostil_group1_id);
        setValue(card, '.field-apostil2', item.apostil_group2_id);
        setValue(card, '.field-consul', item.consul_id);
        setValue(card, '.field-consulate', item.consulate_type_id);

        card.querySelector('.remove-item').addEventListener('click', () => {
            state.items.splice(index, 1);
            render();
        });

        card.querySelectorAll('[data-mode]').forEach(button => button.addEventListener('click', () => {
            item.process_mode = button.dataset.mode;
            if (item.process_mode !== 'apostil') {
                item.direction_type_id = '';
                item.apostil_group1_id = '';
                item.apostil_group2_id = '';
            }
            if (item.process_mode !== 'consul') {
                item.selection_mode = '';
                item.consul_id = '';
                item.consulate_type_id = '';
            }
            render();
        }));

        card.querySelectorAll('[data-selection]').forEach(button => button.addEventListener('click', () => {
            item.selection_mode = button.dataset.selection;
            if (!['consul','mixed'].includes(item.selection_mode)) item.consul_id = '';
            if (!['legalization','mixed'].includes(item.selection_mode)) item.consulate_type_id = '';
            render();
        }));

        [['.field-document','document_type_id'],['.field-service','service_id'],['.field-direction','direction_type_id'],['.field-apostil1','apostil_group1_id'],['.field-apostil2','apostil_group2_id'],['.field-consul','consul_id'],['.field-consulate','consulate_type_id']]
            .forEach(([selector,key]) => card.querySelector(selector).addEventListener('change', event => {
                item[key] = event.target.value;
                render();
            }));

        updateVisibility(card, item);
        renderAddons(card, item);
    }

    function setValue(card, selector, value) {
        const element = card.querySelector(selector);
        if (element) element.value = value || '';
    }

    function updateVisibility(card, item) {
        card.querySelector('.process-apostil').style.display = item.process_mode === 'apostil' ? 'block' : 'none';
        card.querySelector('.process-consul').style.display = item.process_mode === 'consul' ? 'block' : 'none';
        card.querySelector('.field-consul-wrap').style.display = item.process_mode === 'consul' && ['consul','mixed'].includes(item.selection_mode) ? 'flex' : 'none';
        card.querySelector('.field-consulate-wrap').style.display = item.process_mode === 'consul' && ['legalization','mixed'].includes(item.selection_mode) ? 'flex' : 'none';
    }

    function renderAddons(card, item) {
        const groups = {
            document: (data.documentAddons || []).filter(entry => Number(entry.document_type_id) === Number(item.document_type_id || 0)),
            direction: (data.directionAddons || []).filter(entry => Number(entry.document_direction_id) === Number(item.direction_type_id || 0)),
            service: (data.serviceAddons || []).filter(entry => Number(entry.service_id) === Number(item.service_id || 0)),
        };

        Object.entries(groups).forEach(([type, entries]) => {
            const list = card.querySelector(`.list-${type}`);
            card.querySelector(`.meta-${type}`).textContent = `${entries.length} ta`;
            const allowed = new Set(entries.map(entry => `${type}:${Number(entry.id)}`));
            item.selected_addons = item.selected_addons.filter(entry => entry.sourceType !== type || allowed.has(addonKey(entry)));

            if (!entries.length) {
                list.innerHTML = `<div class="empty-state">Mos qo'shimcha topilmadi.</div>`;
                return;
            }

            list.innerHTML = entries.map(entry => {
                const selected = item.selected_addons.some(current => addonKey(current) === `${type}:${Number(entry.id)}`);
                return `
                    <label class="addon-item ${selected ? 'is-selected' : ''}">
                        <input type="checkbox" data-addon-group="${type}" data-addon-id="${entry.id}" ${selected ? 'checked' : ''}>
                        <div class="addon-item__copy">
                            <strong>${entry.name}</strong>
                            <span>${entry.description || 'Izoh kiritilmagan'}</span>
                        </div>
                        <div class="addon-item__meta">
                            <strong>${money(entry.amount ?? entry.price ?? 0)}</strong>
                            <span>${Number(entry.day ?? entry.deadline ?? 0) ? `${Number(entry.day ?? entry.deadline ?? 0)} kun` : 'Muddatsiz'}</span>
                        </div>
                    </label>
                `;
            }).join('');

            list.querySelectorAll('[data-addon-group]').forEach(input => input.addEventListener('change', () => {
                const key = `${input.dataset.addonGroup}:${Number(input.dataset.addonId)}`;
                item.selected_addons = item.selected_addons.filter(entry => addonKey(entry) !== key);
                if (input.checked) item.selected_addons.push({ sourceType: input.dataset.addonGroup, id: Number(input.dataset.addonId) });
                render();
            }));
        });
    }

    function resolveAddon(item, addon) {
        const groups = {
            document: (data.documentAddons || []).filter(entry => Number(entry.document_type_id) === Number(item.document_type_id || 0)),
            direction: (data.directionAddons || []).filter(entry => Number(entry.document_direction_id) === Number(item.direction_type_id || 0)),
            service: (data.serviceAddons || []).filter(entry => Number(entry.service_id) === Number(item.service_id || 0)),
        };
        return (groups[addon.sourceType] || []).find(entry => Number(entry.id) === Number(addon.id)) || null;
    }

    function calculate(item) {
        const service = (data.services || []).find(entry => Number(entry.id) === Number(item.service_id || 0));
        let basePrice = Number(service?.price || 0);
        let deadline = Number(service?.deadline || 0);

        item.selected_addons.forEach(addon => {
            const model = resolveAddon(item, addon);
            if (!model) return;
            basePrice += Number(model.amount ?? model.price ?? 0);
            deadline += Number(model.day ?? model.deadline ?? 0);
        });

        if (item.process_mode === 'apostil') {
            [item.apostil_group1_id, item.apostil_group2_id].forEach(id => {
                const entry = (data.apostilStatics || []).find(model => Number(model.id) === Number(id || 0));
                if (!entry) return;
                basePrice += Number(entry.price || 0);
                deadline += Number(entry.days || 0);
            });
        }

        if (item.process_mode === 'consul' && ['consul','mixed'].includes(item.selection_mode)) {
            const entry = (data.consuls || []).find(model => Number(model.id) === Number(item.consul_id || 0));
            if (entry) { basePrice += Number(entry.amount || 0); deadline += Number(entry.day || 0); }
        }

        if (item.process_mode === 'consul' && ['legalization','mixed'].includes(item.selection_mode)) {
            const entry = (data.consulateTypes || []).find(model => Number(model.id) === Number(item.consulate_type_id || 0));
            if (entry) { basePrice += Number(entry.amount || 0); deadline += Number(entry.day || 0); }
        }

        return { basePrice, deadline };
    }

    function buildSummary(item) {
        const documentName = (data.documentTypes || []).find(entry => Number(entry.id) === Number(item.document_type_id || 0))?.name || 'Hujjat';
        const serviceName = (data.services || []).find(entry => Number(entry.id) === Number(item.service_id || 0))?.name || 'Xizmat';
        return item.process_mode === 'apostil'
            ? `${documentName} / Apostil / ${serviceName}`
            : item.process_mode === 'consul'
                ? `${documentName} / Legalizatsiya / ${serviceName}`
                : `${documentName} / ${serviceName}`;
    }

    function sync() {
        const payload = state.items.map(item => ({
            document_type_id: item.document_type_id || '',
            service_id: item.service_id || '',
            process_mode: item.process_mode || 'service',
            selection_mode: item.process_mode === 'consul' ? (item.selection_mode || '') : '',
            direction_type_id: item.process_mode === 'apostil' ? (item.direction_type_id || '') : '',
            apostil_group1_id: item.process_mode === 'apostil' ? (item.apostil_group1_id || '') : '',
            apostil_group2_id: item.process_mode === 'apostil' ? (item.apostil_group2_id || '') : '',
            consul_id: item.process_mode === 'consul' && ['consul','mixed'].includes(item.selection_mode) ? (item.consul_id || '') : '',
            consulate_type_id: item.process_mode === 'consul' && ['legalization','mixed'].includes(item.selection_mode) ? (item.consulate_type_id || '') : '',
            selected_addons: item.selected_addons.slice().sort((left, right) => addonKey(left).localeCompare(addonKey(right))),
        }));

        payloadInput.value = JSON.stringify(payload);

        const total = state.items.reduce((carry, item) => carry + calculate(item).basePrice, 0);
        const promo = Number(promoInput?.value || 0);
        basePriceValue.textContent = money(total);
        promoPricePreview.textContent = money(promo);
        savingValue.textContent = money(Math.max(total - promo, 0));
        includedItemsCount.textContent = `${state.items.length} ta element`;
        includedItemsPreview.innerHTML = state.items.map(item => {
            const result = calculate(item);
            return `
                <div class="preview-item">
                    <div>
                        <strong>${buildSummary(item)}</strong>
                        <small>${result.deadline ? `Deadline: ${result.deadline} kun` : 'Deadline xizmatlardan olinadi'}</small>
                    </div>
                    <span class="chip">${money(result.basePrice)}</span>
                </div>
            `;
        }).join('');
    }

    addButton?.addEventListener('click', () => {
        state.items.push(emptyItem());
        render();
    });

    promoInput?.addEventListener('input', sync);
    render();
})();
</script>
