<?php

use ExmentAdminCore\Admin\Admin;
use ExmentAdminCore\Admin\Form\Field\Date;
use ExmentAdminCore\Admin\Grid\Filter\Between;
use ExmentAdminCore\Admin\Grid\Filter\Presenter\DateTime;
use ExmentAdminCore\Admin\Widgets\Box;

/**
 * The scripts keep working when a Bootstrap 3 plugin (slimScroll, datetimepicker icons) is not there.
 */
class Bs3PluginGuardsTest extends TestCase
{
    /**
     * @param object $object
     * @param string $name
     *
     * @return mixed
     */
    private function readProperty($object, $name)
    {
        return (new ReflectionProperty($object, $name))->getValue($object);
    }

    public function testBoxScrollableUsesSlimScrollWhenLoadedAndCssOtherwise(): void
    {
        $box = (new Box('title', 'content'))->scrollable(['height' => '300px']);

        $script = $this->readProperty($box, 'script');

        $this->assertStringContainsString('if ($.fn.slimScroll)', $script);
        $this->assertStringContainsString('.slimScroll(options)', $script);
        $this->assertStringContainsString("'overflow': 'auto'", $script);
        $this->assertStringContainsString("'max-height': options.height || '250px'", $script);
        $this->assertStringContainsString('{"height":"300px"}', $script);
    }

    public function testBetweenDatetimeHasFontAwesomeIcons(): void
    {
        Admin::$script = [];

        (new Between('created_at'))->datetime();

        $script = implode("\n", Admin::$script);

        $this->assertStringContainsString('"icons":'.json_encode(Date::PICKER_ICONS), $script);
    }

    public function testBetweenDatetimeKeepsGivenIcons(): void
    {
        Admin::$script = [];

        (new Between('created_at'))->datetime(['icons' => ['up' => 'my-up']]);

        $script = implode("\n", Admin::$script);

        $this->assertStringContainsString('"icons":{"up":"my-up"}', $script);
    }

    public function testDateTimePresenterHasFontAwesomeIcons(): void
    {
        $presenter = new DateTime();

        $options = $this->readProperty($presenter, 'options');

        $this->assertSame(Date::PICKER_ICONS, $options['icons']);
    }
}
