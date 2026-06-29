document.addEventListener('DOMContentLoaded', function () {
    const btn = document.getElementById('ai-suggest-btn');

    if (!btn) {
        return;
    }

    const spinner = document.getElementById('ai-suggest-spinner');
    const btnText = btn.querySelector('.btn-text');
    const alertEl = document.getElementById('ai-suggestion-alert');
    const textEl = document.getElementById('ai-suggestion-text');
    const sourceEl = document.getElementById('ai-suggestion-source');

    btn.addEventListener('click', function () {
        let items;

        try {
            items = JSON.parse(btn.dataset.items || '[]');
        } catch (e) {
            items = [];
        }

        btn.disabled = true;
        spinner.classList.remove('d-none');
        btnText.textContent = 'Generating…';
        alertEl.classList.add('d-none');

        fetch(btn.dataset.url, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
            },
            body: JSON.stringify({
                items: items,
                priority: btn.dataset.priority || 'normal',
                delivery_address: btn.dataset.address || '',
            }),
        })
            .then(function (res) {
                return res.json().then(function (data) {
                    return { ok: res.ok, data: data };
                });
            })
            .then(function (result) {
                if (!result.ok || !result.data.success) {
                    throw new Error(result.data.message || 'Could not generate suggestion.');
                }

                textEl.textContent = result.data.suggestion;

                if (result.data.source) {
                    sourceEl.textContent = result.data.source === 'openai' ? 'AI' : 'Smart Rules';
                    sourceEl.classList.remove('d-none');
                }

                alertEl.classList.remove('d-none');
                alertEl.classList.add('show');

                if (window.AppToast) {
                    window.AppToast.show('Shopping tip ready!', 'success');
                }
            })
            .catch(function (err) {
                if (window.AppToast) {
                    window.AppToast.show(err.message || 'Failed to get suggestion.', 'danger');
                } else {
                    alert(err.message || 'Failed to get suggestion.');
                }
            })
            .finally(function () {
                btn.disabled = false;
                spinner.classList.add('d-none');
                btnText.textContent = 'Get AI Suggestion';
            });
    });
});
