document.addEventListener('DOMContentLoaded', function () {
    const form = document.getElementById('status-update-form');

    if (!form) {
        return;
    }

    const alertBox = document.getElementById('status-alert');
    const submitBtn = document.getElementById('status-submit-btn');
    const spinner = document.getElementById('status-spinner');
    const submitText = submitBtn.querySelector('.submit-text');
    const statusBadge = document.getElementById('order-status-badge');
    const timeline = document.getElementById('status-timeline');
    const statusSelect = document.getElementById('new_status');
    const noteField = document.getElementById('status_note');
    const actionHint = document.getElementById('status-action-hint');
    const statusNotice = document.getElementById('status-notice');

    let statusMeta = {};

    try {
        statusMeta = JSON.parse(form.dataset.statusMeta || '{}');
    } catch (err) {
        statusMeta = {};
    }

    function notify(type, message) {
        if (window.AppToast) {
            window.AppToast.show(message, type);
            return;
        }

        if (alertBox) {
            alertBox.className = 'alert alert-' + type;
            alertBox.textContent = message;
            alertBox.classList.remove('d-none');
        }
    }

    function hideAlert() {
        if (alertBox) {
            alertBox.classList.add('d-none');
        }
    }

    function formatDate(dateStr) {
        const d = new Date(dateStr.replace(' ', 'T'));
        return d.toLocaleString('en-GB', {
            day: '2-digit',
            month: 'short',
            year: 'numeric',
            hour: '2-digit',
            minute: '2-digit',
        });
    }

    function capitalize(str) {
        return str.charAt(0).toUpperCase() + str.slice(1);
    }

    function escapeHtml(text) {
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }

    function renderBadge(status) {
        const meta = statusMeta[status] || {};
        const badgeClass = meta.badge_class || 'secondary';
        const label = meta.label || capitalize(status);
        const tooltip = meta.tooltip || label;

        return '<span class="badge bg-' + badgeClass + ' status-badge" title="' + escapeHtml(tooltip) + '">' +
            escapeHtml(label) + '</span>';
    }

    function updateBadge(status) {
        statusBadge.innerHTML = renderBadge(status);
    }

    function updateStatusNotice(status) {
        if (!statusNotice) {
            return;
        }

        const meta = statusMeta[status];
        if (!meta) {
            return;
        }

        statusNotice.className = 'status-notice alert alert-' + meta.alert + ' d-flex align-items-start gap-2 mb-4';
        statusNotice.dataset.status = status;

        const icon = statusNotice.querySelector('.bi');
        if (icon) {
            icon.className = 'bi ' + meta.icon + ' flex-shrink-0 mt-1';
        }

        const labelEl = statusNotice.querySelector('strong');
        if (labelEl) {
            labelEl.textContent = meta.label || capitalize(status);
        }

        const textEl = statusNotice.querySelector('.status-notice-text');
        if (textEl) {
            textEl.textContent = meta.shopper || '';
        }
    }

    function updateActionHint() {
        if (!actionHint || !statusSelect) {
            return;
        }

        const selected = statusSelect.options[statusSelect.selectedIndex];
        const hint = selected ? selected.dataset.hint : '';

        if (!hint) {
            actionHint.textContent = '';
            actionHint.classList.add('d-none');
            return;
        }

        actionHint.innerHTML = '<i class="bi bi-lightbulb me-1" aria-hidden="true"></i>' + escapeHtml(hint);
        actionHint.classList.remove('d-none');
    }

    function appendTimelineEntry(entry) {
        let timelineInner = timeline.querySelector('.timeline');

        if (!timelineInner) {
            timeline.innerHTML = '<p class="small text-muted mb-3">Status changes are logged here so customers can follow progress.</p><div class="timeline"></div>';
            timelineInner = timeline.querySelector('.timeline');
        }

        const prevLast = timelineInner.querySelector('.timeline-item-last');
        if (prevLast) {
            prevLast.classList.remove('timeline-item-last');
            const content = prevLast.querySelector('.timeline-content');
            if (content) {
                content.classList.add('pb-4');
            }
        }

        const noteHtml = entry.note
            ? '<p class="small mb-1 timeline-note">' + escapeHtml(entry.note) + '</p>'
            : '';

        const html =
            '<div class="timeline-item timeline-item-last" data-log-id="' + entry.id + '">' +
            '<div class="timeline-marker bg-' + entry.badge_class + '"></div>' +
            '<div class="timeline-content pb-4">' +
            '<div class="d-flex flex-wrap align-items-center gap-2 mb-1">' +
            renderBadge(entry.new_status) +
            '<span class="text-muted small timeline-date">' + formatDate(entry.created_at) + '</span>' +
            '</div>' +
            noteHtml +
            '<p class="small text-muted mb-0">by ' + escapeHtml(entry.changed_by_name) + '</p>' +
            '</div></div>';

        timelineInner.insertAdjacentHTML('beforeend', html);
    }

    function refreshStatusOptions(allowedNext, currentStatus) {
        statusSelect.innerHTML = '<option value="">Select next status…</option>';

        allowedNext.forEach(function (status) {
            const meta = statusMeta[status] || {};
            const opt = document.createElement('option');
            opt.value = status;
            opt.textContent = meta.shopper_action || capitalize(status);
            opt.dataset.hint = meta.transition_hint || '';
            statusSelect.appendChild(opt);
        });

        updateActionHint();

        if (allowedNext.length === 0) {
            const meta = statusMeta[currentStatus] || {};
            form.closest('.card').innerHTML =
                '<div class="card-body text-center text-muted py-4">' +
                '<i class="bi bi-check-circle fs-2 d-block mb-2"></i>' +
                escapeHtml(meta.shopper || 'No further status updates available.') +
                '</div>';
        }
    }

    if (statusSelect) {
        statusSelect.addEventListener('change', updateActionHint);
    }

    form.addEventListener('submit', function (e) {
        e.preventDefault();
        hideAlert();

        const status = statusSelect.value;

        if (!status) {
            notify('warning', 'Please select a status.');
            return;
        }

        submitBtn.disabled = true;
        spinner.classList.remove('d-none');
        submitText.textContent = 'Updating…';

        const formData = new FormData();
        formData.append('status', status);
        formData.append('note', noteField.value);

        fetch(form.dataset.url, {
            method: 'POST',
            body: formData,
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
            },
        })
            .then(function (res) {
                return res.json().then(function (data) {
                    return { ok: res.ok, status: res.status, data: data };
                });
            })
            .then(function (result) {
                if (!result.ok || !result.data.success) {
                    throw new Error(result.data.message || 'Update failed.');
                }

                const data = result.data;
                notify('success', data.message);
                updateBadge(data.order.status);
                updateStatusNotice(data.order.status);

                if (data.timeline_entry) {
                    appendTimelineEntry(data.timeline_entry);
                }

                noteField.value = '';
                statusSelect.value = '';
                refreshStatusOptions(data.allowed_next || [], data.order.status);
            })
            .catch(function (err) {
                notify('danger', err.message || 'Something went wrong.');
            })
            .finally(function () {
                submitBtn.disabled = false;
                spinner.classList.add('d-none');
                submitText.textContent = 'Update Status';
            });
    });
});
