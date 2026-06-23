<?php

namespace ExmentAdminCore\Admin\Auth;

use ExmentAdminCore\Admin\Facades\Admin;
use ExmentAdminCore\Admin\Middleware\Pjax;

class Permission
{
    /**
     * Check permission.
     *
     * @param mixed $permission
     * @return true|void
     */
    public static function check($permission)
    {
        if (static::isAdministrator()) {
            return true;
        }

        if (is_array($permission)) {
            collect($permission)->each(function ($permission) {
                call_user_func([self::class, 'check'], $permission);
            });

            return;
        }

        if (Admin::user()->cannot($permission)) {
            static::error();
        }
    }

    /**
     * Roles allowed to access.
     *
     * @param array<mixed> $roles
     * @return true|void
     */
    public static function allow($roles)
    {
        if (static::isAdministrator()) {
            return true;
        }

        if (!Admin::user()->inRoles($roles)) {
            static::error();
        }
    }

    /**
     * Don't check permission.
     *
     * @return bool
     */
    public static function free()
    {
        return true;
    }

    /**
     * Roles denied to access.
     *
     * @param array<mixed> $roles
     * @return true|void
     */
    public static function deny($roles)
    {
        if (static::isAdministrator()) {
            return true;
        }

        if (Admin::user()->inRoles($roles)) {
            static::error();
        }
    }

    /**
     * Send error response page.
     * @param string|null $message
     *
     * @return void
     */
    public static function error($message = null)
    {
        if(empty($message)){
            $message = trans('admin.deny');
        }
        
        // move to after ajax
        //$response = response(Admin::content()->withError($message));

        if (!request()->pjax() && request()->ajax()) {
            abort(403, $message);
        }

        /** @phpstan-ignore-next-line Parameter #1 $content of function response expects array|Illuminate\Contracts\View\View|string|null, ExmentAdminCore\Admin\Layout\Content given. */
        $response = response(Admin::content()->withError($message));

        Pjax::respond($response);
    }

    /**
     * If current user is administrator.
     *
     * @return mixed
     */
    public static function isAdministrator()
    {
        return Admin::user()->isRole('administrator');
    }
}
