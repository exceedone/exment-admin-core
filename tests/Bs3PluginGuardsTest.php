<?php

use ExmentAdminCore\Admin\Widgets\Box;

/**
 * The scripts keep working when a Bootstrap 3 plugin (slimScroll) is not there.
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
}
