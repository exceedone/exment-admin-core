<?php

use ExmentAdminCore\Admin\Admin;

/**
 * 2.0 (B1): the Bootstrap 3 files nothing loads are gone, and nothing points at them.
 */
class DeadAssetsRemovedTest extends TestCase
{
    private function assets(): string
    {
        return dirname(__DIR__) . '/resources/assets';
    }

    public function testTheDeadFilesAreGone(): void
    {
        foreach ([
            'bootstrap3-editable',
            'AdminLTE/bootstrap',
            'AdminLTE/plugins/slimScroll',
            'AdminLTE/dist/img/boxed-bg.png',
            'AdminLTE/dist/img/default-50x50.gif',
            'AdminLTE/dist/img/icons.png',
        ] as $path) {
            $this->assertFileDoesNotExist($this->assets() . '/' . $path, $path);
        }

        // still used: the default avatar and the background of the boxed layout (AdminLTE.min.css)
        $this->assertFileExists($this->assets() . '/AdminLTE/dist/img/user2-160x160.jpg');
        $this->assertFileExists($this->assets() . '/AdminLTE/dist/img/boxed-bg.jpg');
    }

    public function testSlimScrollIsNotLoaded(): void
    {
        foreach (array_merge(Admin::$baseJs, Admin::$baseCss) as $asset) {
            $this->assertStringNotContainsStringIgnoringCase('slimscroll', $asset);
            $this->assertStringNotContainsString('bootstrap3-editable', $asset);
        }
    }

    public function testTheLoginPageUsesTheBootstrap5Css(): void
    {
        $view = (string) file_get_contents(dirname(__DIR__) . '/resources/views/login.blade.php');
        $this->assertStringContainsString('vendor/open-admin/AdminLTE4/css/bootstrap.min.css', $view);
        $this->assertStringNotContainsString('AdminLTE/bootstrap/', $view);
        $this->assertStringNotContainsString('glyphicon', $view);
        $this->assertFileExists($this->assets() . '/AdminLTE4/css/bootstrap.min.css');
    }
}
