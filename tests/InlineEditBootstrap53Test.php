<?php

/**
 * The inline edit of the grid on Bootstrap 5.3 (2.x): the popover makes its tip after
 * show.bs.popover, and a new one at each show, so the buttons are bound on inserted.bs.popover.
 * The form answers the editor with JSON although it sends the pjax header too.
 *
 * The script is javascript the test runner does not execute; this reads the sources.
 */
class InlineEditBootstrap53Test extends TestCase
{
    public function testTheButtonsAreBoundWhenTheTipIsInThePage(): void
    {
        $js = str_replace("\r\n", "\n", (string) file_get_contents(dirname(__DIR__) . '/resources/assets/open-admin/js/open-admin-grid-inline-edit.js'));
        $this->assertStringContainsString("el.addEventListener('inserted.bs.popover', function (event) {", $js);
        $this->assertStringContainsString("if (tip && typeof(tip.dataset.ieEvents) == 'undefined'){", $js);
        $this->assertStringNotContainsString('popover.eventsAdded', $js);
        // show.bs.popover has no tip yet
        $show = substr($js, (int) strpos($js, "'show.bs.popover'"));
        $show = substr($show, 0, (int) strpos($show, "'inserted.bs.popover'"));
        $this->assertStringNotContainsString('popover.tip', $show);
        $this->assertStringContainsString('result.data.message', $js);
    }

    public function testTheFormAnswersTheInlineEditWithJson(): void
    {
        $form = (string) file_get_contents(dirname(__DIR__) . '/src/Form.php');
        $this->assertStringContainsString("if (\$request->ajax() && (!\$request->pjax() || \$request->has('_edit_inline'))) {", $form);
    }
}
