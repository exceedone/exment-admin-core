<?php

namespace ExmentAdminCore\Admin\Grid\Actions;

use ExmentAdminCore\Admin\Actions\RowAction;

class Show extends RowAction
{
    public $icon = 'icon-eye';

    /**
     * @return array|null|string
     */
    public function name()
    {
        return __('admin.show');
    }

    /**
     * @return string
     */
    public function href()
    {
        return "{$this->getResource()}/{$this->getKey()}";
    }
}
