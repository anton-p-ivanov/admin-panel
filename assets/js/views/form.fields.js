import "./form.basic"

$(function () {

    let
        $isMultiple,
        $isExpanded,
        $tab,

        formID = '#form-field-form',
        tabSelector = '.nav-tabs > .nav-item:eq(2) > .nav-link',

        m = {
            toggleDisableState: function (state) {
                $isMultiple.attr('disabled', state);
                $isExpanded.attr('disabled', state);
                $tab.toggleClass('disabled', state);
            }
        };

    $(document).on('change', '#field_type', function () {
        let type = $(this).val();

        $isMultiple = $('#field_isMultiple');
        $isExpanded = $('#field_isExpanded');
        $tab = $(this).closest(formID).find(tabSelector);

        m.toggleDisableState(type !== 'C' && type !== 'E');
    });

    $(document).on('loaded.bs.modal', '#form-modal', function () {
        let type = $('#field_type').val();

        $isMultiple = $('#field_isMultiple');
        $isExpanded = $('#field_isExpanded');
        $tab = $(this).find(formID).find(tabSelector);

        m.toggleDisableState(type !== 'C' && type !== 'E');
    });
});