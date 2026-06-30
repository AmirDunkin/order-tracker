document.addEventListener('DOMContentLoaded', function () {
    const form = document.getElementById('shopper-items-form');

    if (!form) {
        return;
    }

    const submitBtn = document.getElementById('shopper-items-submit');
    const spinner = document.getElementById('shopper-items-spinner');
    const submitText = submitBtn.querySelector('.submit-text');

    function notify(type, message) {
        if (window.AppToast) {
            window.AppToast.show(message, type);
            return;
        }

        alert(message);
    }

    form.addEventListener('submit', function (e) {
        e.preventDefault();

        submitBtn.disabled = true;
        spinner.classList.remove('d-none');
        submitText.textContent = 'Saving…';

        fetch(form.dataset.url, {
            method: 'POST',
            body: new FormData(form),
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
            },
        })
            .then(function (res) {
                return res.json().then(function (data) {
                    return { ok: res.ok, data: data };
                });
            })
            .then(function (result) {
                if (!result.ok || !result.data.success) {
                    throw new Error(result.data.message || 'Save failed.');
                }

                notify('success', result.data.message);
            })
            .catch(function (err) {
                notify('danger', err.message || 'Something went wrong.');
            })
            .finally(function () {
                submitBtn.disabled = false;
                spinner.classList.add('d-none');
                submitText.textContent = 'Save Item Updates';
            });
    });
});
