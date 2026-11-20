<?php

use ExmentAdminCore\Admin\Auth\Database\Administrator;
use ExmentAdminCore\Admin\Auth\Database\Role;

class RolesTest extends TestCase
{
    public function setUp(): void
    {
        parent::setUp();

        $this->be(Administrator::first(), 'admin');
    }

    public function testRolesIndex()
    {
        $this->visit('admin/auth/roles')
            ->see('Roles')
            ->see('administrator');
    }
}
