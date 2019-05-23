(function ($) {

    $.fn.brakes = function () {
        return this.each(function () {
            let $input = $(this);

            // Cloning a table row
            $input.on('click', '[data-toggle="add"]', function () {
                let $this = $(this);
                let $table = $this.parents('table');
                let $clone = $table.find('tr:last').clone();
                let max = $table.find('tbody>tr').length;
                $clone.find('input:text').each(function () {
                    let $this = $(this);
                    let name = $this.attr('name').replace(/\[\d+]/g, '[' + max + ']');
                    $this.attr('name', name);
                    $this.removeAttr('id');
                });

                $clone.appendTo($table);
                return false;
            });

            // Removing a table row
            $input.on('click', '[data-toggle="remove"]', function () {
                if ($input.find('table>tbody>tr').length > 1) {
                    $(this).parents('tr').remove();

                    // Recalculate indexes
                    $input.find('table>tbody>tr').each(function (index, element) {
                        $(element).find('input:text').each(function () {
                            let $this = $(this);
                            let name = $this.attr('name').replace(/\[\d+]/g, '[' + index + ']');
                            $this.attr('name', name);
                            $this.removeAttr('id');
                        });
                    });
                }
                else {
                    alert('Нельзя удалить единственный брейк');
                }
                return false;
            });
        });
    };

})(window.jQuery);
