$(function () {

    let selector = '#confirm-modal',
        $trigger = null,
        $modal = null,
        methods = {
            submit: function () {
                let method = $trigger.data('method'),
                    data = $modal.find('form').serializeArray();

                $.ajax({
                    url: $modal.find('form').attr('action'),
                    type: method ? method : 'post',
                    data: data,
                    dataType: 'json',
                    success: function (response) {
                        $modal.modal('hide');
                        if (response.hasOwnProperty('container')) {
                            $(response.container).load(response.url);
                        }
                    },
                    error: function (response) {
                        let json = JSON.parse(response.responseText);
                        $modal.find('.alert').remove();
                        $modal.find('[type="password"]').val('').focus();
                        $modal.find('.modal-body').prepend(
                            '<div class="alert alert-danger">' + json.error + '</div>');
                    }
                });
            }
        };

    $(document).on('click.confirm', '[data-toggle="confirm"]', function (e) {
        e.preventDefault();
        $trigger = $(this);

        if (!$trigger.hasClass('disabled')) {
            $modal = $(selector);
            $modal.find('form').attr('action', $trigger.prop('href'));
            $modal.modal('show');
        }
    });

    $(document).on('shown.bs.modal', selector, function () {
        $(this).find('input:password').val('').focus();
    });

    $(document).on('hidden.bs.modal', selector, function () {
        $(this).find('.alert').remove();
    });

    $(document).on('submit.confirm', selector + ' form', function (e) {
        e.preventDefault();
        methods.submit();
    });
});