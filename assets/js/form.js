(function ($) {

    $.fn.form = function () {
        return this.each(function () {
            let $form = $(this);

            $.ajax({
                url: $form.attr('action'),
                type: $form.attr('method'),
                data: $form.serializeArray(),
                success: function (response, status, xhr) {
                    if (xhr.status === 206 && typeof response === "object") {
                        $form.find('.invalid-feedback').remove();
                        $form.find('.is-invalid').toggleClass('is-invalid', false);

                        for (let name in response) {
                            if (response.hasOwnProperty(name)) {
                                let input = $form.find('#' + name),
                                    error = $('<div>').addClass('invalid-feedback').html(response[name].join('<br>'));

                                if (input.is('div')) {
                                    input.find('.form-control').toggleClass('is-invalid', true);
                                }

                                input.toggleClass('is-invalid', true).after(error);
                            }
                        }

                        $form.find('button:submit').toggleClass('btn-danger');
                    }
                    else {
                        // Close current modal
                        $form.closest('.modal').modal('hide');

                        // Reload container data
                        if ($.support.pjax) {
                            $.pjax.reload(response.container, {
                                push: false,
                                replace: false,
                                url: response.url,
                            });
                        }
                    }
                }
            });
        });
    };

})(window.jQuery);
