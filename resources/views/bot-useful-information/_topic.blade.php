@php
    $prefix = 'topics['.$index.']';
    $checklistText = $topic['checklist_text'] ?? implode("\n", $topic['checklist'] ?? []);
@endphp
<article class="br-panel ui-topic" data-topic>
    <div class="br-card-head">
        <h2><span data-topic-number>{{ is_numeric($index) ? $index + 1 : '' }}</span>. Mavzu</h2>
        <div class="d-flex gap-1">
            <button type="button" class="btn btn-sm btn-outline-secondary" data-move="up" aria-label="Mavzuni yuqoriga ko‘chirish"><i class="bx bx-up-arrow-alt" aria-hidden="true"></i></button>
            <button type="button" class="btn btn-sm btn-outline-secondary" data-move="down" aria-label="Mavzuni pastga ko‘chirish"><i class="bx bx-down-arrow-alt" aria-hidden="true"></i></button>
            <button type="button" class="btn btn-sm btn-outline-danger" data-remove aria-label="Mavzuni o‘chirish"><i class="bx bx-trash" aria-hidden="true"></i></button>
        </div>
    </div>
    <div class="br-card-body">
        <input type="hidden" data-field="id" name="{{ $prefix }}[id]" value="{{ $topic['id'] ?? '' }}">
        <div class="ui-topic-title-row">
            <div>
                <label for="topic-{{ $index }}-icon" data-label="icon">Belgi</label>
                <select id="topic-{{ $index }}-icon" name="{{ $prefix }}[icon]" data-field="icon" class="form-select">
                    @foreach($icons as $key => $emoji)<option value="{{ $key }}" @selected(($topic['icon'] ?? 'info') === $key)>{{ $emoji }}</option>@endforeach
                </select>
            </div>
            <div>
                <label for="topic-{{ $index }}-title" data-label="title">Mavzu sarlavhasi</label>
                <input id="topic-{{ $index }}-title" name="{{ $prefix }}[title]" data-field="title" value="{{ $topic['title'] ?? '' }}" maxlength="70" class="form-control" placeholder="Masalan, hujjatni sifatli yuborish" required>
            </div>
        </div>
        <label for="topic-{{ $index }}-summary" data-label="summary">Qisqa tavsif</label>
        <input id="topic-{{ $index }}-summary" name="{{ $prefix }}[summary]" data-field="summary" value="{{ $topic['summary'] ?? '' }}" maxlength="120" class="form-control mb-3" placeholder="Mavzular menyusida ko‘rinadigan bir jumla">
        <label for="topic-{{ $index }}-body" data-label="body">Asosiy tushuntirish</label>
        <textarea id="topic-{{ $index }}-body" name="{{ $prefix }}[body]" data-field="body" maxlength="1600" rows="3" class="form-control mb-3" required>{{ $topic['body'] ?? '' }}</textarea>
        <label for="topic-{{ $index }}-checklist_text" data-label="checklist_text">Amaliy ro‘yxat</label>
        <textarea id="topic-{{ $index }}-checklist_text" name="{{ $prefix }}[checklist_text]" data-field="checklist_text" maxlength="1300" rows="3" class="form-control" placeholder="Har bir bandni yangi qatordan yozing">{{ $checklistText }}</textarea>
        <p class="br-muted small mt-1 mb-3">6 tagacha band, har biri 200 belgigacha. Bot ularni alohida belgilar bilan ko‘rsatadi.</p>
        <label for="topic-{{ $index }}-tip" data-label="tip">💡 Eslatma</label>
        <textarea id="topic-{{ $index }}-tip" name="{{ $prefix }}[tip]" data-field="tip" maxlength="250" rows="2" class="form-control mb-3" placeholder="Mijoz unutmasligi kerak bo‘lgan maslahat">{{ $topic['tip'] ?? '' }}</textarea>
        <input type="hidden" data-field="published" name="{{ $prefix }}[published]" value="0">
        <div class="form-check form-switch">
            <input id="topic-{{ $index }}-published" type="checkbox" name="{{ $prefix }}[published]" data-field="published" value="1" class="form-check-input" @checked($topic['published'] ?? true)>
            <label for="topic-{{ $index }}-published" data-label="published" class="form-check-label mb-0">Botda ko‘rsatish</label>
        </div>
    </div>
</article>
