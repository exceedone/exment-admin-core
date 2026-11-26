<?php

namespace ExmentAdminCore\Admin\Grid\Filter\Presenter;

use ExmentAdminCore\Admin\Facades\Admin;

class Checkbox extends Radio
{
    protected function prepare()
    {
        // 2.0: native check boxes (no iCheck)
        $script = '';

        Admin::script($script);
    }
}
