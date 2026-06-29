$(function () {
    const $container = $('#items-container');
    const $template = $('#item-row-template');

  function updateRemoveButtons() {
        const $rows = $container.find('.item-row');
        $rows.find('.remove-item').prop('disabled', $rows.length <= 1);
    }

    $('#add-item-btn').on('click', function () {
        const $row = $($template.html());
        $container.append($row);
        $row.find('.item-name').focus();
        updateRemoveButtons();
    });

    $container.on('click', '.remove-item', function () {
        if ($container.find('.item-row').length <= 1) {
            return;
        }
        $(this).closest('.item-row').remove();
        updateRemoveButtons();
    });

    $('#order-form').on('submit', function (e) {
        let valid = false;

        $container.find('.item-row').each(function () {
            const name = $(this).find('.item-name').val().trim();
            const qty = parseInt($(this).find('.item-qty').val(), 10);
            if (name !== '' && qty >= 1) {
                valid = true;
            }
        });

        if (!valid) {
            e.preventDefault();
            alert('Please add at least one item with a name and quantity.');
        }
    });

    updateRemoveButtons();
});
