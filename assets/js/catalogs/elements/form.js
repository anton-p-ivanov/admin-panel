import "../../views/form.selector";
import "../../brakes";

$(function () {

    $('#form-modal').on('loaded.bs.modal', function () {
        $('#element_price_brakes').brakes();
    });

});