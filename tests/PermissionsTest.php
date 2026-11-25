<?php

use ExmentAdminCore\Admin\Auth\Database\Administrator;
use ExmentAdminCore\Admin\Auth\Database\Permission;
use ExmentAdminCore\Admin\Auth\Database\Role;

class PermissionsTest extends TestCase
{
    public function setUp(): void
    {
        parent::setUp();

        $this->be(Administrator::first(), 'admin');
    }

    public function testPermissionsIndex()
    {
        $this->assertTrue(Administrator::first()->isAdministrator());

        $this->visit('admin/auth/permissions')
            ->see('Permissions');
    }
}
