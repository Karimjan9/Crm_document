@extends('template')

@section('style')
<link rel="stylesheet" href="{{ asset('assets/css/bot-requests.css') }}">
<style>
    .ui-editor-grid { display: grid; grid-template-columns: minmax(0, 1.65fr) minmax(300px, 1fr); gap: 24px; align-items: start; }
    .ui-topic-title-row { display: grid; grid-template-columns: 90px minmax(0, 1fr); gap: 14px; margin-bottom: 18px; }
    .ui-preview-panel { position: sticky; top: 108px; }
    .ui-preview-chat { background: #e7f0f4; padding: 24px; max-height: 75vh; overflow-y: auto; }
    .ui-preview-bubble { background: #fff; color: #213347; padding: 20px; border-radius: 16px 16px 16px 4px; box-shadow: 0 2px 8px #18374b0d; margin-bottom: 16px; }
    .ui-preview-bubble h3 { color: #213347; font-size: 17px; line-height: 1.5; font-weight: 700; margin: 0 0 14px; }
    .ui-preview-bubble p, .ui-preview-bubble li { font-size: 13px; line-height: 1.7; white-space: pre-wrap; overflow-wrap: anywhere; }
    .ui-preview-bubble ul { padding-left: 22px; }
    .ui-preview-summary { color: #64768c; font-style: italic; }
    .ui-preview-tip { border-left: 3px solid #edbf57; padding: 12px; background: #fff8e7; border-radius: 5px; margin-top: 16px; }
    .ui-preview-buttons { display: grid; gap: 7px; }
    .ui-preview-button { background: #d8e9f5; color: #316084; font-size: 13px; padding: 10px 12px; text-align: center; border-radius: 8px; }
    .ui-editor-actions { display: flex; gap: 12px; justify-content: space-between; flex-wrap: wrap; padding: 20px 0; }
    .ui-save-hint { font-size: 12px; color: var(--br-muted); }
    html.dark-theme .ui-preview-chat { background: #192737; }
    html.dark-theme .ui-preview-bubble { background: #293c51; color: #e0e8f6; }
    html.dark-theme .ui-preview-bubble h3 { color: #e0e8f6; }
    html.dark-theme .ui-preview-summary { color: #a2b1c9; }
    html.dark-theme .ui-preview-tip { background: #3d3d32; }
    html.dark-theme .ui-preview-button { background: #354d68; color: #c4ddf4; }
    @media (max-width: 1100px) { .ui-editor-grid { grid-template-columns: minmax(0, 1fr); } .ui-preview-panel { position: static; } .ui-preview-chat { max-height: none; } }
    @media (max-width: 576px) { .ui-topic-title-row { grid-template-columns: 75px minmax(0, 1fr); gap: 10px; } .ui-preview-chat { padding: 16px; } }
</style>
@endsection

@section('body')
@php
    $topics = old('topics', $catalog['topics']);
@endphp
<div class="page-wrapper bot-requests-shell useful-info-shell">
    <div class="page-content">
        <div class="br-heading">
            <div><h1>Foydali ma’lumotlar</h1><p class="br-muted mb-0">Mijoz uchun qisqa, tushunarli va amaliy bilimlar kutubxonasi.</p></div>
            <a href="{{ route('bot-content.index') }}" class="btn btn-outline-primary"><i class="bx bx-message-detail me-1" aria-hidden="true"></i>Xabar matnlari</a>
        </div>
        @if(session('success'))<div class="alert alert-success" role="status">{{ session('success') }}</div>@endif
        @if($errors->any())<div class="alert alert-danger" role="alert"><ul class="mb-0">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>@endif

        <div class="ui-editor-grid">
            <form method="POST" action="{{ route('bot-useful-information.update') }}" id="useful-information-form">
                @csrf
                @method('PUT')
                <div class="br-stack">
                    <section class="br-panel">
                        <div class="br-card-head"><h2><i class="bx bx-book-open me-2" aria-hidden="true"></i>Kutubxona bosh sahifasi</h2><span class="br-source"><i class="bx bxl-telegram" aria-hidden="true"></i>Bot uchun</span></div>
                        <div class="br-card-body">
                            <label for="library-title">Sarlavha</label>
                            <input id="library-title" name="title" value="{{ old('title', $catalog['title']) }}" class="form-control mb-3" maxlength="80" required>
                            <label for="library-intro">Kirish matni</label>
                            <textarea id="library-intro" name="intro" rows="3" class="form-control" maxlength="4000">{{ old('intro', $catalog['intro']) }}</textarea>
                            <p class="br-muted small mt-2 mb-0">Mijoz birinchi ochganda shu matn va faol mavzu tugmalarini ko‘radi.</p>
                        </div>
                    </section>
                    <div id="information-topics" class="br-stack">
                        @foreach($topics as $index => $topic)
                            @include('bot-useful-information._topic', ['index' => $index, 'topic' => $topic])
                        @endforeach
                    </div>
                    <div id="no-topics" class="br-panel br-card-body" @if(count($topics)) hidden @endif><p class="br-muted mb-0">Mavzu qo‘shing. Mavzusiz kutubxonada faqat kirish matni chiqadi.</p></div>
                </div>
                <div class="ui-editor-actions">
                    <button type="button" id="add-topic" class="btn btn-outline-primary"><i class="bx bx-plus me-1" aria-hidden="true"></i>Mavzu qo‘shish</button>
                    <button type="submit" class="btn btn-primary"><i class="bx bx-save me-1" aria-hidden="true"></i>O‘zgarishlarni saqlash</button>
                </div>
                <p class="ui-save-hint"><span id="active-topic-count">{{ collect($topics)->where('published', true)->count() }}</span> ta faol mavzu · 12 tagacha mavzu. Saqlangach, botdagi keyingi ochishda yangilanadi.</p>
            </form>

            <aside class="br-panel ui-preview-panel" aria-label="Telegram ko‘rinishi">
                <div class="br-card-head"><h2><i class="bx bxl-telegram me-2" aria-hidden="true"></i>Telegram ko‘rinishi</h2><span class="br-muted small">Jonli namuna</span></div>
                <div class="px-4 py-3"><label for="preview-topic">Ko‘rish uchun mavzu</label><select id="preview-topic" class="form-select"></select></div>
                <div class="ui-preview-chat" id="telegram-preview" aria-live="polite"></div>
            </aside>
        </div>
    </div>
</div>
<template id="topic-template">
    @include('bot-useful-information._topic', ['index' => '__INDEX__', 'topic' => ['published' => true]])
</template>
@endsection

@section('script')
<script>
(() => {
    const form = document.getElementById('useful-information-form');
    const container = document.getElementById('information-topics');
    const preview = document.getElementById('telegram-preview');
    const topicSelect = document.getElementById('preview-topic');
    const icons = @json($icons);
    const cards = () => Array.from(container.querySelectorAll('[data-topic]'));
    const value = (card, field) => card.querySelector(`[data-field="${field}"]`).value.trim();
    const node = (tag, text, className) => {
        const element = document.createElement(tag);
        if (text !== undefined) element.textContent = text;
        if (className) element.className = className;
        return element;
    };
    function sync() {
        const rows = cards();
        rows.forEach((card, index) => {
            card.querySelector('[data-topic-number]').textContent = index + 1;
            card.querySelectorAll('[data-field]').forEach(input => {
                const field = input.dataset.field;
                input.name = `topics[${index}][${field}]`;
                if (input.type !== 'hidden') input.id = `topic-${index}-${field}`;
            });
            card.querySelectorAll('[data-label]').forEach(label => label.htmlFor = `topic-${index}-${label.dataset.label}`);
            card.querySelector('[data-move="up"]').disabled = index === 0;
            card.querySelector('[data-move="down"]').disabled = index === rows.length - 1;
        });
        document.getElementById('add-topic').disabled = rows.length >= 12;
        document.getElementById('no-topics').hidden = rows.length > 0;
        const active = rows.filter(card => card.querySelector('[type="checkbox"]').checked);
        const selected = topicSelect.value;
        topicSelect.replaceChildren(...active.map(card => {
            const option = node('option', value(card, 'title') || 'Yangi mavzu');
            option.value = value(card, 'id');
            return option;
        }));
        if (active.some(card => value(card, 'id') === selected)) topicSelect.value = selected;
        topicSelect.disabled = active.length === 0;
        document.getElementById('active-topic-count').textContent = active.length;
        const title = document.getElementById('library-title').value.trim() || 'Foydali ma’lumotlar';
        const intro = document.getElementById('library-intro').value.trim();
        const overview = node('div', undefined, 'ui-preview-bubble');
        overview.append(node('h3', `📚 ${title}`), node('p', intro));
        const buttons = node('div', undefined, 'ui-preview-buttons');
        active.forEach(card => buttons.append(node('div', `${icons[value(card, 'icon')]} ${value(card, 'title') || 'Yangi mavzu'}`, 'ui-preview-button')));
        buttons.append(node('div', '📝 Yangi murojaat', 'ui-preview-button'));
        overview.append(buttons);
        preview.replaceChildren(overview);
        const card = active.find(card => value(card, 'id') === topicSelect.value);
        if (card) {
            const sample = node('div', undefined, 'ui-preview-bubble');
            sample.append(node('h3', `${icons[value(card, 'icon')]} ${value(card, 'title') || 'Yangi mavzu'}`));
            if (value(card, 'summary')) sample.append(node('p', value(card, 'summary'), 'ui-preview-summary'));
            sample.append(node('p', value(card, 'body')));
            const lines = value(card, 'checklist_text').split(/\r?\n/).map(line => line.trim()).filter(Boolean);
            if (lines.length) {
                sample.append(node('p', '✅ Amaliy ro‘yxat'));
                const list = node('ul');
                lines.forEach(line => list.append(node('li', line)));
                sample.append(list);
            }
            if (value(card, 'tip')) sample.append(node('p', `💡 Eslatma\n${value(card, 'tip')}`, 'ui-preview-tip'));
            preview.append(sample);
            const navigation = node('div', undefined, 'ui-preview-buttons');
            navigation.append(node('div', '📝 Yangi murojaat', 'ui-preview-button'), node('div', '◀️ Mavzularga qaytish', 'ui-preview-button'));
            preview.append(navigation);
        }
    }
    form.addEventListener('input', sync);
    form.addEventListener('change', sync);
    form.addEventListener('submit', sync);
    topicSelect.addEventListener('change', sync);
    form.addEventListener('focusin', event => {
        const card = event.target.closest('[data-topic]');
        if (card && card.querySelector('[type="checkbox"]').checked) {
            topicSelect.value = value(card, 'id');
            sync();
        }
    });
    document.getElementById('add-topic').addEventListener('click', () => {
        if (cards().length >= 12) return;
        const fragment = document.getElementById('topic-template').content.cloneNode(true);
        fragment.querySelector('[data-field="id"]').value = crypto.randomUUID();
        container.append(fragment);
        sync();
        cards().at(-1).querySelector('[data-field="title"]').focus();
    });
    container.addEventListener('click', event => {
        const button = event.target.closest('[data-remove], [data-move]');
        if (!button) return;
        const card = button.closest('[data-topic]');
        if (button.hasAttribute('data-remove')) card.remove();
        else if (button.dataset.move === 'up' && card.previousElementSibling) container.insertBefore(card, card.previousElementSibling);
        else if (button.dataset.move === 'down' && card.nextElementSibling) container.insertBefore(card.nextElementSibling, card);
        sync();
    });
    sync();
})();
</script>
@endsection
