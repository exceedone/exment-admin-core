<?php

namespace ExmentAdminCore\Admin\Grid\Displayers;

use ExmentAdminCore\Admin\Admin;

class RowSelector extends AbstractDisplayer
{
    /**
     * @return string
     */
    public function display()
    {
        Admin::script($this->script());

        return <<<EOT
<input type="checkbox" class="{$this->grid->getGridRowName()}-checkbox" data-id="{$this->getKey()}" />
EOT;
    }

    /**
     * @return string
     */
    protected function script()
    {
        $allName = $this->grid->getSelectAllName();
        $rowName = $this->grid->getGridRowName();

        $selected = trans('admin.grid_items_selected');

        // With iCheck the handlers are the iCheck events, as before. Without iCheck they are the native
        // change event. Only one set is bound, so nothing runs twice (iCheck 1.0.1 never triggers a
        // native change event: it sets `checked` itself and cancels the click on the input).
        return <<<EOT
(function () {
    var updateSelectedButtons = function () {
        var selected = $.admin.grid.selected().length;

        if (selected > 0) {
            $('.{$allName}-btn').show();
        } else {
            $('.{$allName}-btn').hide();
        }

        $('.{$allName}-btn .selected').html("{$selected}".replace('{n}', selected));
    };

    if ($.fn.iCheck) {
        $('.{$rowName}-checkbox').iCheck({checkboxClass:'icheckbox_minimal-blue'}).on('ifChanged', function () {

            var id = $(this).data('id');

            if (this.checked) {
                \$.admin.grid.select(id);
                $(this).closest('tr').css('background-color', '#ffffd5');
            } else {
                \$.admin.grid.unselect(id);
                $(this).closest('tr').css('background-color', '');
            }
        }).on('ifClicked', function () {

            var id = $(this).data('id');

            // ifClicked comes before the state changes: this.checked is still the old state.
            if (this.checked) {
                $.admin.grid.unselect(id);
            } else {
                $.admin.grid.select(id);
            }

            updateSelectedButtons();
        });
        return;
    }

    $('.{$rowName}-checkbox').on('change', function () {

        var id = $(this).data('id');

        // change comes after the state changes: this.checked is the new state.
        if (this.checked) {
            \$.admin.grid.select(id);
            $(this).closest('tr').css('background-color', '#ffffd5');
        } else {
            \$.admin.grid.unselect(id);
            $(this).closest('tr').css('background-color', '');
        }

        updateSelectedButtons();
    });
})();

EOT;
    }
}
