@if(($deadlineBell['visible'] ?? false))
  @php
    $deadlineItems = $deadlineBell['items'] ?? [];
    $deadlineTotal = (int) ($deadlineBell['total'] ?? 0);
    $deadlineOverdue = (int) ($deadlineBell['overdue_count'] ?? 0);
    $deadlineToday = (int) ($deadlineBell['today_count'] ?? 0);
    $deadlineHasCritical = (bool) ($deadlineBell['has_critical'] ?? false);
    $deadlineSummary = $deadlineOverdue > 0
      ? $deadlineOverdue . ' kechikkan'
      : ($deadlineToday > 0 ? $deadlineToday . ' bugun' : ($deadlineTotal > 0 ? $deadlineTotal . ' aktiv' : 'Hammasi joyida'));
    $deadlineRemainingCount = max($deadlineTotal - count($deadlineItems), 0);
  @endphp

  <div class="deadline-bell {{ $deadlineHasCritical ? 'is-critical' : '' }}" id="deadlineBell">
    <button
      type="button"
      class="deadline-bell__trigger"
      id="deadlineBellTrigger"
      aria-haspopup="dialog"
      aria-expanded="false"
      aria-controls="deadlineBellPanel"
    >
      <span class="deadline-bell__orb"></span>
      <span class="deadline-bell__icon-wrap">
        <i class='bx bxs-bell-ring deadline-bell__icon'></i>
      </span>
      <span class="deadline-bell__copy">
        <span class="deadline-bell__eyebrow">Deadline</span>
        <strong class="deadline-bell__summary">{{ $deadlineSummary }}</strong>
      </span>
      @if($deadlineTotal > 0)
        <span class="deadline-bell__badge">{{ $deadlineTotal > 99 ? '99+' : $deadlineTotal }}</span>
      @endif
    </button>

    <div class="deadline-bell__panel" id="deadlineBellPanel" hidden>
      <div class="deadline-bell__panel-glow"></div>

      <div class="deadline-bell__panel-head">
        <div class="deadline-bell__panel-copy">
          <div class="deadline-bell__panel-kicker">Smart reminder</div>
          <h3>{{ $deadlineBell['title'] ?? 'Deadline' }}</h3>
          <p>{{ $deadlineBell['subtitle'] ?? '' }}</p>
        </div>

        <button type="button" class="deadline-bell__close" id="deadlineBellClose" aria-label="Deadline panelini yopish">
          <i class='bx bx-x'></i>
        </button>
      </div>

      <div class="deadline-bell__stats">
        <div class="deadline-bell__stat is-overdue">
          <span class="deadline-bell__stat-label">Kechikkan</span>
          <strong>{{ $deadlineOverdue }}</strong>
        </div>
        <div class="deadline-bell__stat is-today">
          <span class="deadline-bell__stat-label">Bugun</span>
          <strong>{{ $deadlineToday }}</strong>
        </div>
        <div class="deadline-bell__stat">
          <span class="deadline-bell__stat-label">Jami</span>
          <strong>{{ $deadlineTotal }}</strong>
        </div>
      </div>

      @if($deadlineItems)
        <div class="deadline-bell__list">
          @foreach($deadlineItems as $item)
            @php
              $urgency = $item['urgency'] ?? 'normal';
              $itemUrl = $item['url'] ?? ($deadlineBell['index_url'] ?? '#');
            @endphp

            <button
              type="button"
              class="deadline-bell__item urgency-{{ $urgency }}"
              data-deadline-url="{{ $itemUrl }}"
              data-deadline-code="{{ $item['doc_code'] ?? '-' }}"
              data-deadline-flag="{{ $item['flag'] ?? '' }}"
              data-deadline-title="{{ $item['title'] ?? 'Mijoz' }}"
              data-deadline-subtitle="{{ $item['subtitle'] ?? 'Xizmat' }}"
              data-deadline-meta="{{ $item['meta'] ?? '' }}"
              data-deadline-due-label="{{ $item['due_label'] ?? '' }}"
              data-deadline-due-at="{{ $item['due_at'] ?? '' }}"
              data-deadline-remaining="{{ $item['remaining'] ?? '' }}"
              data-deadline-urgency="{{ $urgency }}"
            >
              <span class="deadline-bell__item-rail"></span>

              <div class="deadline-bell__item-main">
                <div class="deadline-bell__item-top">
                  <span class="deadline-bell__code">{{ $item['doc_code'] ?? '-' }}</span>
                  @if(!empty($item['flag']))
                    <span class="deadline-bell__flag">{{ $item['flag'] }}</span>
                  @endif
                </div>

                <h4>{{ $item['title'] ?? 'Mijoz' }}</h4>
                <p>{{ $item['subtitle'] ?? 'Xizmat' }}</p>
                <div class="deadline-bell__meta">{{ $item['meta'] ?? '' }}</div>
              </div>

              <div class="deadline-bell__item-side">
                <span class="deadline-bell__due">{{ $item['due_label'] ?? '' }}</span>
                <span class="deadline-bell__date">{{ $item['due_at'] ?? '' }}</span>
                <span class="deadline-bell__remaining">{{ $item['remaining'] ?? '' }}</span>
              </div>
            </button>
          @endforeach
        </div>
      @else
        <div class="deadline-bell__empty">
          <div class="deadline-bell__empty-icon">
            <i class='bx bx-check-shield'></i>
          </div>
          <div>
            <strong>Deadline nazorat ostida</strong>
            <p>{{ $deadlineBell['empty_message'] ?? "Deadline yo'q." }}</p>
          </div>
        </div>
      @endif

      <div class="deadline-bell__footer">
        @if(!empty($deadlineBell['index_url']))
          <a href="{{ $deadlineBell['index_url'] }}" class="deadline-bell__footer-link">
            <span>To'liq ro'yxatni ochish</span>
            <i class='bx bx-right-arrow-alt'></i>
          </a>
        @endif

        @if($deadlineRemainingCount > 0)
          <div class="deadline-bell__footer-note">Yana {{ $deadlineRemainingCount }} ta deadline kutmoqda</div>
        @endif
      </div>
    </div>

    <div class="deadline-bell__detail-modal" id="deadlineBellDetail" hidden aria-hidden="true">
      <div class="deadline-bell__detail-backdrop" data-deadline-detail-close></div>

      <div class="deadline-bell__detail-dialog" id="deadlineBellDetailDialog" role="dialog" aria-modal="true" aria-labelledby="deadlineBellDetailTitle">
        <button type="button" class="deadline-bell__detail-close" id="deadlineBellDetailClose" aria-label="Deadline tafsilotini yopish" data-deadline-detail-close>
          <i class='bx bx-x'></i>
        </button>

        <div class="deadline-bell__detail-hero" id="deadlineBellDetailHero">
          <div class="deadline-bell__detail-badges">
            <span class="deadline-bell__code" id="deadlineBellDetailCode">DOC</span>
            <span class="deadline-bell__flag" id="deadlineBellDetailFlag" hidden></span>
          </div>

          <h3 id="deadlineBellDetailTitle">Deadline tafsiloti</h3>
          <p id="deadlineBellDetailSubtitle"></p>
        </div>

        <div class="deadline-bell__detail-grid">
          <div class="deadline-bell__detail-card">
            <span class="deadline-bell__detail-label">Holat</span>
            <strong id="deadlineBellDetailDueLabel">-</strong>
          </div>
          <div class="deadline-bell__detail-card">
            <span class="deadline-bell__detail-label">Deadline vaqti</span>
            <strong id="deadlineBellDetailDueAt">-</strong>
          </div>
          <div class="deadline-bell__detail-card">
            <span class="deadline-bell__detail-label">Qolgan vaqt</span>
            <strong id="deadlineBellDetailRemaining">-</strong>
          </div>
        </div>

        <div class="deadline-bell__detail-block">
          <span class="deadline-bell__detail-label">Qo'shimcha ma'lumot</span>
          <p id="deadlineBellDetailMeta">-</p>
        </div>

        <div class="deadline-bell__detail-actions">
          <a href="#" class="deadline-bell__detail-link" id="deadlineBellDetailLink">
            <span>Tegishli sahifani ochish</span>
            <i class='bx bx-right-arrow-alt'></i>
          </a>
        </div>
      </div>
    </div>
  </div>
@endif
