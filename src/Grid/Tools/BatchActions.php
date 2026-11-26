<?php

namespace ExmentAdminCore\Admin\Grid\Tools;

use ExmentAdminCore\Admin\Admin;
use Illuminate\Support\Collection;

class BatchActions extends AbstractTool
{
    /**
     * deleteBatchClassName
     *
     * @var string
     */
    public static $deleteBatchClassName = \ExmentAdminCore\Admin\Grid\Tools\BatchDelete::class;


    /**
     * @var Collection<int|string, mixed>
     */
    protected $actions;

    /**
     * @var bool
     */
    protected $enableDelete = true;

    /**
     * @var bool
     */
    private $isHoldSelectAllCheckbox = false;

    /**
     * BatchActions constructor.
     */
    public function __construct()
    {
        $this->actions = new Collection();

        $this->appendDefaultAction();
    }

    /**
     * Append default action(batch delete action).
     *
     * @return void
     */
    protected function appendDefaultAction()
    {
        $this->add(new static::$deleteBatchClassName(trans('admin.batch_delete')));
    }

    /**
     * Disable delete.
     *
     * @return $this
     */
    public function disableDelete(bool $disable = true)
    {
        $this->enableDelete = !$disable;

        return $this;
    }

    /**
     * Disable delete And Hode SelectAll Checkbox.
     *
     * @return $this
     */
    public function disableDeleteAndHodeSelectAll()
    {
        $this->enableDelete = false;

        $this->isHoldSelectAllCheckbox = true;

        return $this;
    }

    /**
     * Add a batch action.
     *
     * @param BatchAction|string $title
     * @param BatchAction|null $action
     *
     * @return $this
     */
    public function add($title, BatchAction $action = null)
    {
        $id = $this->actions->count();

        if (func_num_args() == 1) {
            $action = $title;
            $action->setId($id);
        } elseif (func_num_args() == 2) {
            $action->setId($id);
            $action->setTitle($title);
        }

        $this->actions->push($action);

        return $this;
    }

    /**
     * Setup scripts of batch actions.
     *
     * @return void
     */
    protected function setUpScripts()
    {
        Admin::script($this->script());

        foreach ($this->actions as $action) {
            $action->setGrid($this->grid);

            Admin::script($action->script());
        }
    }

    /**
     * Scripts of BatchActions button groups.
     *
     * @return string
     */
    protected function script()
    {
        $allName = $this->grid->getSelectAllName();
        $rowName = $this->grid->getGridRowName();

        $selected = trans('admin.grid_items_selected');

        // 2.0: native check boxes, the change event (no iCheck)
        return <<<EOT
        $.admin.grid.selects = {};
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

    $('.{$allName}').on('change', function () {
        // change comes after the state changes: this.checked is the new state.
        var checked = this.checked;

        if (!checked) {
            $.admin.grid.selects = {};
        }

        // The row checkboxes select / unselect themselves on their own change event (RowSelector).
        $('.{$rowName}-checkbox').filter(function () {
            return this.checked !== checked;
        }).prop('checked', checked).trigger('change');

        updateSelectedButtons();
    });
})();

EOT;
    }

    /**
     * Render BatchActions button groups.
     *
     * @return string
     */
    public function render()
    {
        if (!$this->enableDelete) {
            $this->actions->shift();
        }

        if ($this->actions->isEmpty()) {
            return '';
        }

        $this->setUpScripts();

        $data = [
            'actions'                 => $this->actions,
            'selectAllName'           => $this->grid->getSelectAllName(),
            'isHoldSelectAllCheckbox' => $this->isHoldSelectAllCheckbox,
        ];

        return view('admin::grid.batch-actions', $data)->render();
    }
}
