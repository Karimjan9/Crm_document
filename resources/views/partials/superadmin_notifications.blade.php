@role('super_admin')
    <div id="superadmin-notifications" class="superadmin-notifications" hidden></div>

    <style>
        .superadmin-notifications {
            position: fixed;
            right: 22px;
            bottom: 22px;
            z-index: 1080;
            display: grid;
            gap: 14px;
            width: min(390px, calc(100vw - 28px));
            pointer-events: none;
        }

        .superadmin-notification {
            position: relative;
            overflow: hidden;
            border: 1px solid rgba(30, 41, 59, 0.12);
            border-radius: 8px;
            background: linear-gradient(135deg, #ffffff 0%, #f8fbff 100%);
            color: #172033;
            box-shadow: 0 18px 45px rgba(15, 23, 42, 0.2);
            padding: 16px 16px 15px;
            pointer-events: auto;
            transform: translateX(24px);
            opacity: 0;
            animation: superadmin-notification-in 420ms cubic-bezier(0.2, 0.85, 0.25, 1) forwards;
        }

        .superadmin-notification::before {
            content: '';
            position: absolute;
            inset: 0 auto 0 0;
            width: 5px;
            background: linear-gradient(180deg, #0d6efd, #16a34a);
        }

        .superadmin-notification::after {
            content: '';
            position: absolute;
            right: -36px;
            top: -42px;
            width: 112px;
            height: 112px;
            border-radius: 50%;
            background: rgba(13, 110, 253, 0.08);
        }

        .superadmin-notification.is-leaving {
            animation: superadmin-notification-out 240ms ease forwards;
        }

        .superadmin-notification__head {
            position: relative;
            z-index: 1;
            display: flex;
            align-items: center;
            gap: 11px;
            margin-bottom: 8px;
        }

        .superadmin-notification__icon {
            flex: 0 0 38px;
            width: 38px;
            height: 38px;
            border-radius: 8px;
            display: grid;
            place-items: center;
            background: #e8f1ff;
            color: #0d6efd;
            box-shadow: inset 0 0 0 1px rgba(13, 110, 253, 0.1);
        }

        .superadmin-notification__icon i {
            font-size: 20px;
        }

        .superadmin-notification__title {
            position: relative;
            z-index: 1;
            color: #101828;
            font-size: 15.5px;
            font-weight: 700;
            line-height: 1.25;
            margin: 0;
        }

        .superadmin-notification__message {
            position: relative;
            z-index: 1;
            color: #475467;
            font-size: 14px;
            line-height: 1.5;
            margin: 0 0 15px 49px;
        }

        .superadmin-notification__actions {
            position: relative;
            z-index: 1;
            display: flex;
            justify-content: flex-end;
        }

        .superadmin-notification__button {
            border: 0;
            border-radius: 6px;
            background: linear-gradient(135deg, #0d6efd, #0b5ed7);
            color: #ffffff;
            font-size: 13px;
            font-weight: 600;
            min-height: 36px;
            padding: 8px 15px;
            box-shadow: 0 8px 18px rgba(13, 110, 253, 0.26);
            transition: transform 160ms ease, box-shadow 160ms ease, opacity 160ms ease;
        }

        .superadmin-notification__button:hover {
            transform: translateY(-1px);
            box-shadow: 0 10px 24px rgba(13, 110, 253, 0.32);
        }

        .superadmin-notification__button:focus {
            outline: 3px solid rgba(13, 110, 253, 0.22);
            outline-offset: 2px;
        }

        .superadmin-notification__button:disabled {
            opacity: 0.7;
            transform: none;
            box-shadow: none;
        }

        @keyframes superadmin-notification-in {
            to {
                transform: translateX(0);
                opacity: 1;
            }
        }

        @keyframes superadmin-notification-out {
            to {
                transform: translateX(18px) scale(0.98);
                opacity: 0;
            }
        }

        @media (max-width: 575.98px) {
            .superadmin-notifications {
                right: 14px;
                bottom: 14px;
                width: calc(100vw - 28px);
            }

            .superadmin-notification {
                padding: 14px;
            }

            .superadmin-notification__message {
                margin-left: 0;
            }

            .superadmin-notification__actions {
                justify-content: stretch;
            }

            .superadmin-notification__button {
                width: 100%;
            }
        }
    </style>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const container = document.getElementById('superadmin-notifications');
            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');

            if (!container || !csrfToken) {
                return;
            }

            function escapeHtml(value) {
                const div = document.createElement('div');
                div.textContent = value || '';
                return div.innerHTML;
            }

            function renderNotifications(items) {
                container.hidden = items.length === 0;
                container.innerHTML = items.map(function (item) {
                    return `
                        <div class="superadmin-notification" data-id="${item.id}">
                            <div class="superadmin-notification__head">
                                <span class="superadmin-notification__icon" aria-hidden="true">
                                    <i class="bx bx-bell"></i>
                                </span>
                                <h4 class="superadmin-notification__title">${escapeHtml(item.title)}</h4>
                            </div>
                            <p class="superadmin-notification__message">${escapeHtml(item.message)}</p>
                            <div class="superadmin-notification__actions">
                                <button type="button" class="superadmin-notification__button" data-read-id="${item.id}">
                                    Tushunarli
                                </button>
                            </div>
                        </div>
                    `;
                }).join('');
            }

            function loadNotifications() {
                fetch(@json(route('superadmin.monthly_notifications.index')), {
                    headers: {
                        'Accept': 'application/json'
                    }
                })
                    .then(function (response) {
                        if (!response.ok) {
                            return [];
                        }

                        return response.json();
                    })
                    .then(renderNotifications)
                    .catch(function () {});
            }

            container.addEventListener('click', function (event) {
                const button = event.target.closest('[data-read-id]');

                if (!button) {
                    return;
                }

                const id = button.getAttribute('data-read-id');
                button.disabled = true;

                fetch(@json(url('/superadmin/monthly-notifications')) + '/' + id + '/read', {
                    method: 'POST',
                    headers: {
                        'Accept': 'application/json',
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrfToken
                    }
                })
                    .then(function (response) {
                        if (!response.ok) {
                            button.disabled = false;
                            return;
                        }

                        const notification = button.closest('.superadmin-notification');

                        if (!notification) {
                            return;
                        }

                        notification.classList.add('is-leaving');
                        notification.addEventListener('animationend', function () {
                            notification.remove();
                            container.hidden = !container.querySelector('.superadmin-notification');
                        }, { once: true });
                    })
                    .catch(function () {
                        button.disabled = false;
                    });
            });

            loadNotifications();
        });
    </script>
@endrole
