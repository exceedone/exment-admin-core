<?php

use ExmentAdminCore\Admin\Auth\Database\Administrator;
use ExmentAdminCore\Admin\Auth\Database\Menu;

class MenuTest extends TestCase
{
    public function setUp(): void
    {
        parent::setUp();

        $this->be(Administrator::first(), 'admin');
    }

    public function testMenuIndex()
    {
        $this->visit('admin/auth/menu')
            ->see('Menu')
            ->see('Auth')
            ->see('Users')
            ->see('Roles')
            ->see('Permission')
            ->see('Menu')
            ->see('Submit');
    }

    public function testAddMenu()
    {
        $client_mock = \Mockery::mock('overload:\ExmentDB');
        $client_mock->shouldReceive('transaction')->once();

        $item = ['parent_id' => '0', 'title' => 'Test', 'uri' => 'test'];

        $this->visit('admin/auth/menu')
            ->seePageIs('admin/auth/menu')
            ->see('Menu')
            ->submitForm('Submit', $item)
            ->seePageIs('admin/auth/menu');

//        $this->expectException(\Laravel\BrowserKitTesting\HttpException::class);
//
//        $this->visit('admin')
//            ->see('Test')
//            ->click('Test');
    }

    public function testDeleteMenu()
    {
        $this->delete('admin/auth/menu/8')
            ->assertEquals(7, Menu::count());
    }

    public function testEditMenuParent()
    {
        // A menu cannot be its own parent: the save is refused and nothing changes.
        $before = Menu::find(5)->parent_id;

        $this->put('admin/auth/menu/5', ['parent_id' => 5]);

        $this->assertResponseStatus(500);
        $this->assertInstanceOf(Exception::class, $this->response->exception);
        $this->assertSame(trans('admin.parent_select_error'), $this->response->exception->getMessage());
        $this->assertNotEquals(5, $before);
        $this->assertEquals($before, Menu::find(5)->parent_id);
    }
}
