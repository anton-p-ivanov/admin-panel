import "../../app";
import "../../form";
import "../uploader";

$(function () {

    $(document).on('submit', 'form[data-container]', function (e) {
        e.preventDefault();
        $(this).form();
    });

    $(document).on('keyup', '#node-search', function () {
        let search = $(this).val(),
            selector = '#storage_parentNodes > option';

        if (search.length < 3) {
            $(selector).attr('class', false);
            return;
        }

        let re = new RegExp(search, 'i');
        $(selector).each(function () {
            let force = re.test(this.text);
            $(this).toggleClass('text-black-50', !force);
        })
    });
});