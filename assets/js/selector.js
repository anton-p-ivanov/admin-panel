(function ($) {

    $.fn.selector = function () {
        let $input,
            $modal = $('#selector-modal'),
            selector = '.modal-body input[name^="selection"]',
            selected;

        $modal.on('click', '[data-trigger="link"]', function (e) {
            e.preventDefault();

            let $selector = $modal.data('selector'),
                selected = $modal.data('selected');

            if (selected.title) {
                $selector.next('input:hidden').val(selected.value);
                $selector.find('input:text').val(selected.title);
            }
            else {
                let keys = Object.keys(selected);
                $selector.next('input:hidden').val(JSON.stringify(keys));
                $selector.find('input:text').val('Выбрано элементов: ' + keys.length);

                $selector.siblings('ul').remove();
                let $list = $('<ul class="small">');
                for (let i in selected) {
                    if (selected.hasOwnProperty(i)) {
                        $list.append($('<li>').data('uuid', i).text(selected[i]));
                    }
                }
                $selector.parent().append($list);
            }

            $modal.modal('hide');
        });

        $modal.on('change', selector, function () {
            let $input = $(this),
                state = $input.is(':checked'),
                value = $input.val(),
                selected = $modal.data('selected');

            if (typeof selected === 'string') {
                selected = {'value': value, 'title': $input.data('title')};
            }
            else {
                if (state) {
                    selected[value] = $input.data('title');
                }
                else {
                    delete selected[value];
                }
            }

            $modal.data('selected', selected);
            $modal.find('[data-trigger="link"]').attr('disabled', selected.length === 0);
        });

        $modal.on('pjax:complete', function() {
            if (typeof selected === 'string') {
                $modal.find(selector).filter('[value="' + selected + '"]').prop('checked', true);
            }
            else {
                for (let i in selected) {
                    $modal.find(selector).filter('[value="' + i + '"]').prop('checked', true);
                }
            }
        });

        return this.each(function () {
            $input = $(this);

            $input.on('click', '[data-toggle="selector"]', function (e) {
                e.preventDefault();

                let $selector = $(this).closest('[data-selector]');
                selected = $selector.next('input:hidden').val();

                if ($selector.data('multiple')) {
                    selected = {};
                    $selector.siblings('ul').find('li').each(function () {
                        selected[$(this).data('uuid')] = $(this).text();
                    })
                }

                $modal.load($(this).attr('href'), function () {
                    $modal.modal('show');
                    $modal.data('selector', $selector);
                    $modal.data('selected', selected);
                    $modal.find('[data-trigger="link"]').attr('disabled', selected && selected.length === 0);

                    if (typeof selected === 'string') {
                        $modal.find(selector).filter('[value="' + selected + '"]').prop('checked', true);
                    }
                    else {
                        for (let i in selected) {
                            $modal.find(selector).filter('[value="' + i + '"]').prop('checked', true);
                        }
                    }
                });
            });

            $input.on('click', '[data-trigger="clear"]', function (e) {
                e.preventDefault();

                let $selector = $(this).closest('.input-selector');

                $selector.find('input').val('');
                $selector.find('ul').remove();
            });
        });
    };

})(window.jQuery);
