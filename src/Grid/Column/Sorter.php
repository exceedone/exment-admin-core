<?php

namespace ExmentAdminCore\Admin\Grid\Column;

use Illuminate\Contracts\Support\Renderable;
use ExmentAdminCore\Admin\Grid\Column;
use ExmentAdminCore\Admin\Grid\Model;

class Sorter implements Renderable
{
    /**
     * Sort arguments.
     *
     * @var array
     */
    protected $sort;

    /**
     * Cast Name.
     *
     * @var array
     */
    protected $cast;

    /**
     * @var string
     */
    protected $sortName;

    /**
     * @var string
     */
    protected $columnName;

    /**
     * Column this sorter belongs to, when known.
     *
     * @var Column|null
     */
    protected $column;

    /**
     * Sorter constructor.
     *
     * @param string      $sortName
     * @param string      $columnName
     * @param string|null $cast
     * @param Column|null $column
     */
    public function __construct($sortName, $columnName, $cast = null, ?Column $column = null)
    {
        $this->sortName = $sortName;
        $this->columnName = $columnName;
        $this->cast = $cast;
        $this->column = $column;
    }

    /**
     * Determine if this column is currently sorted.
     *
     * @return bool
     */
    protected function isSorted()
    {
        $this->sort = \request()->get($this->sortName);

        if (empty($this->sort)) {
            return false;
        }

        return isset($this->sort['column']) && $this->sort['column'] == $this->columnName;
    }

    /**
     * @return string
     */
    public function render()
    {
        // Column::sorter() is the single implementation of the sort url contract
        // that Grid\Model reads back, so defer to it whenever the column is known.
        if ($this->column) {
            return $this->column->sorter();
        }

        $icon = 'icon-sort';
        $type = -1;

        if ($this->isSorted()) {
            $current = Model::normalizeSortDirection($this->sort['type'] ?? null);
            $type = $current === 'asc' ? -1 : 1;
            $icon .= $current === 'asc' ? '-amount-up' : '-amount-down';
        }

        // set sort value. Grid\Model expects the numeric 1/-1 form.
        $sort = ['column' => $this->columnName, 'type' => $type];

        if ($this->cast) {
            $sort['cast'] = $this->cast;
        }

        $query = \request()->all();
        $query = array_merge($query, [$this->sortName => $sort]);

        $url = url()->current().'?'.http_build_query($query);

        return "<a class=\"icon-fw " . e($icon) . "\" href=\"" . e($url) . "\"></a>";
    }
}
