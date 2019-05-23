import "../app"
import "../form"

$(function () {

    $(document).on('submit', 'form[data-container]', function (e) {
        e.preventDefault();
        $(this).form();
    });

});