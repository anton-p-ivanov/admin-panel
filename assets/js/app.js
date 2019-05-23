import "jquery";
import "jquery-pjax";
import "./confirm";

$(function () {

    // First tab is always loaded
    $('[data-toggle="tab"]:first').data('loaded', true);

    // Set tabs `onShow` event handler
    $(document).on('show.bs.tab', 'a[data-toggle="tab"]', function (e) {
        let toggle = $(e.target),
            pane = $(toggle.data('target') || toggle.attr('href'));

        if (pane.is(':empty') && toggle.data('loaded') !== true) {
            pane.trigger('load.bs.tab');
            pane.load(toggle.attr('href'), function () {
                toggle.data('loaded', true);
                pane.trigger('loaded.bs.tab')
            });
        }
    });

    let modalShown = [], showPreviousModal = true;

    $(document).on('show.bs.modal', '.modal', function (e) {
        let modal = $(this),
            trigger = $(e.relatedTarget),
            isRemote = modal.data('remote') === true;

        if (isRemote && trigger.length) {
            modal.trigger('load.bs.modal');
            modal.html('');
            modal.load(trigger.attr('href'), function () {
                modal.trigger('loaded.bs.modal');
            });
        }

        $(document).find('.modal.show').each(function () {
            showPreviousModal = false;
            $('#' + this.id).modal('hide');
            modalShown.push(this.id);
        });
    });

    $(document).on('hidden.bs.modal', '.modal', function () {
        if (modalShown.length && showPreviousModal) {
            $('#' + modalShown.pop()).modal('show');
        }

        showPreviousModal = true;
    });

    if ($.support.pjax) {
        $(document).on('click', '[data-pjax="true"] a:not([data-pjax="false"])', function (e) {
            let container = $(this).closest('[data-pjax="true"]');
            $.pjax.click(e, {
                container: '#' + container.get(0).id,
                push: container.get(0).hasAttribute('data-push') ? container.data('push') : true
            })
        });

        $(document).on('submit', '[data-pjax="true"] form', function (e) {
            e.preventDefault();

            let container = $(this).closest('[data-pjax="true"]');
            $.pjax.submit(e, '#' + container.attr('id'), {'push': container.get(0).hasAttribute('data-push') ? container.data('push') : true});
        });
    }
});