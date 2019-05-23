import "./form.basic"
import "../selector"

$(function () {

    $(document).on('loaded.bs.modal', '.modal', function () {
        $(this).find('[data-selector]').selector();
    });

    $(this).find('[data-selector]').selector();
});