import "../../views/form.basic"

$(function () {

    $(document).on('loaded.bs.modal', '#form-modal', function () {
        let questions = $('#training_test_questions');

        questions.find('.card-body').each(function () {
            let checkboxes = $(this).find('input:checkbox'),
                checked = checkboxes.filter(':checked');

            if (checkboxes.length === checked.length) {
                $(this).prev('.card-header').find('input:checkbox').prop('checked', true);
            }
        });

        questions.on('change', '[data-select]', function (e) {
            e.preventDefault();

            let state = $(this).is(':checked'),
                selector = $(this).data('select');

            $(selector).find('input:checkbox').prop('checked', state);
        });

        questions.on('change', '.card-body input:checkbox', function (e) {
            e.preventDefault();

            let checkboxes = $(this).closest('.card-body').find('input:checkbox'),
                checked = checkboxes.filter(':checked'),
                condition = (checkboxes.length === checked.length);

            $(this).closest('.card-body').prev('.card-header').find('input:checkbox').prop('checked', condition);
        });
    });

});