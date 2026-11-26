<?php

use ExmentAdminCore\Admin\Form\Field\Checkbox;
use ExmentAdminCore\Admin\Form\Field\Radio;

/**
 * 2.0 (B3): the check boxes and radio buttons are native, iCheck is not loaded or set up.
 */
class NativeCheckboxesTest extends TestCase
{
    public function testTheFieldsLoadNoICheck(): void
    {
        foreach ([Checkbox::class, Radio::class] as $class) {
            $this->assertSame([], (new ReflectionProperty($class, 'css'))->getValue(), $class);
            $this->assertSame([], (new ReflectionProperty($class, 'js'))->getValue(), $class);
        }
    }

    public function testNoScriptSetsUpICheck(): void
    {
        $files = new RecursiveIteratorIterator(new RecursiveDirectoryIterator(dirname(__DIR__) . '/src', FilesystemIterator::SKIP_DOTS));
        foreach ($files as $file) {
            if ($file->getExtension() !== 'php') {
                continue;
            }
            $code = (string) file_get_contents($file->getPathname());
            foreach (['.iCheck(', '$.fn.iCheck', "'ifChanged'", "'ifClicked'", "'ifChecked'", 'plugins/iCheck/'] as $needle) {
                $this->assertStringNotContainsString($needle, $code, $file->getPathname() . ': ' . $needle);
            }
        }
    }

    public function testTheGridSelectsWithTheChangeEvent(): void
    {
        foreach ([
            \ExmentAdminCore\Admin\Grid\Displayers\RowSelector::class,
            \ExmentAdminCore\Admin\Grid\Tools\BatchActions::class,
            \ExmentAdminCore\Admin\Widgets\Grid\Displayers\RowSelector::class,
            \ExmentAdminCore\Admin\Widgets\Grid\Tools\BatchActions::class,
        ] as $class) {
            $code = (string) file_get_contents((string) (new ReflectionClass($class))->getFileName());
            $this->assertStringContainsString(".on('change', function", $code, $class);
        }
    }
}
