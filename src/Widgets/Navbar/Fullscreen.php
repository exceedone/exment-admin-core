<?php

namespace ExmentAdminCore\Admin\Widgets\Navbar;

use Illuminate\Contracts\Support\Renderable;
use ExmentAdminCore\Admin\Admin;

/**
 * Class FullScreen.
 *
 * @see  https://javascript.ruanyifeng.com/htmlapi/fullscreen.html
 */
class Fullscreen implements Renderable
{
    public function render()
    {
        return Admin::component('admin::components.fullscreen');
    }
}
