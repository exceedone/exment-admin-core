<?php

use ExmentAdminCore\Admin\Admin;
use ExmentAdminCore\Admin\Grid;
use ExmentAdminCore\Admin\Grid\Column;
use ExmentAdminCore\Admin\Grid\Displayers\Editable;
use Tests\Models\User as UserModel;

/**
 * 2.1: bootstrap5-editable (x-editable) is gone. ->editable() stays for the code that calls it,
 * drawn by the inline edit of the grid (it sends `_edit_inline`, as ->text() does).
 */
class EditableIsInlineEditTest extends TestCase
{
    public function testTheFilesOfXEditableAreGone(): void
    {
        $assets = dirname(__DIR__) . '/resources/assets';
        $this->assertFileDoesNotExist($assets . '/bootstrap5-editable');
        $this->assertFileDoesNotExist($assets . '/open-admin/js/editable-init.js');

        foreach (array_merge(Admin::$baseJs, Admin::$baseCss) as $asset) {
            $this->assertStringNotContainsString('editable', $asset);
        }
    }

    /**
     * @param array<mixed> $arguments the arguments of ->editable()
     */
    protected function render(array $arguments): string
    {
        $grid = new Grid(new UserModel());
        $grid->setResource('admin/users');
        $column = new Column('username', 'Username');
        $column->setGrid($grid);

        $row = new UserModel();
        $row->id = 3;

        Admin::$script = [];
        $html = (new Editable('alice', $grid, $column, $row))->display(...$arguments);

        // the scripts of a component are collected for the page
        return $html . implode("\n", Admin::$script);
    }

    public function testATextIsTheInlineEditor(): void
    {
        foreach ([[], ['text'], ['number'], ['year']] as $arguments) {
            $html = $this->render($arguments);
            $this->assertStringContainsString('class="ie"', $html);
            $this->assertStringContainsString('data-value="alice"', $html);
            $this->assertStringContainsString('data-key="3"', $html);
            $this->assertStringContainsString('data-name="username"', $html);
            $this->assertStringContainsString('admin/users', $html);
            $this->assertStringContainsString('<input class="form-control ie-input"', $html);
            $this->assertStringNotContainsString('.editable(', $html);
        }
    }

    public function testTheOtherTypes(): void
    {
        $this->assertStringContainsString('<textarea', $this->render(['textarea']));
        $this->assertStringContainsString('flatpickr(field', $this->render(['date']));
        $this->assertStringContainsString('"noCalendar":true', $this->render(['time']));
        $this->assertStringContainsString('"enableSeconds":true', $this->render(['combodate', 'YYYY-MM-DD HH:mm:ss']));
    }
}
