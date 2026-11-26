<?php

namespace ExmentAdminCore\Admin\Widgets\Grid\Displayers;

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

        // 2.0: native check boxes, the change event (no iCheck)
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
