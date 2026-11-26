<?php

use ExmentAdminCore\Admin\Admin;
use ExmentAdminCore\Admin\Form\Field\Date;
use ExmentAdminCore\Admin\Form\Field\DatePicker;
use ExmentAdminCore\Admin\Form\Field\DateRange;
use ExmentAdminCore\Admin\Form\Field\Datetime;
use ExmentAdminCore\Admin\Form\Field\Time;
use ExmentAdminCore\Admin\Grid\Filter\Between;
use ExmentAdminCore\Admin\Grid\Filter\Presenter\DateTime as DateTimePresenter;

/**
 * 2.0 (B2): the date and time pickers are flatpickr, not eonasdan's bootstrap-datetimepicker on
 * moment. The fields keep taking moment formats.
 */
class FlatpickrDatePickerTest extends TestCase
{
    /**
     * The script of a field. It is set before the view is drawn; the view itself needs a form
     * and a request, which a unit test does not have.
     *
     * @param \ExmentAdminCore\Admin\Form\Field $field
     */
    private function scriptOf($field): string
    {
        try {
            $field->render();
        } catch (\Throwable $e) {
            // the view, not the script
        }
        $script = $field->getScript();
        return is_array($script) ? implode("
", $script) : $script;
    }

    public function testMomentFormatsBecomeFlatpickrFormats(): void
    {
        $this->assertSame('Y-m-d', DatePicker::format('YYYY-MM-DD'));
        $this->assertSame('Y-m-d H:i:S', DatePicker::format('YYYY-MM-DD HH:mm:ss'));
        $this->assertSame('H:i:S', DatePicker::format('HH:mm:ss'));
        $this->assertSame('H:i', DatePicker::format('HH:mm'));
        $this->assertSame('Y/n/j', DatePicker::format('YYYY/M/D'));
        $this->assertSame('Y', DatePicker::format('YYYY'));
        $this->assertSame('m', DatePicker::format('MM'));
        $this->assertSame('d', DatePicker::format('DD'));
        $this->assertSame('G:i K', DatePicker::format('hh:mm A'));
        $this->assertSame('F Y', DatePicker::format('MMMM YYYY'));
        $this->assertSame('Y年m月', DatePicker::format('YYYY[年]MM[月]'), 'a literal stays');
        $this->assertSame('Y-m-d\\TH:i', DatePicker::format('YYYY-MM-DD[T]HH:mm'));
    }

    public function testTheOptionsFollowTheFormat(): void
    {
        $date = DatePicker::options([], 'YYYY-MM-DD');
        $this->assertSame('Y-m-d', $date['dateFormat']);
        $this->assertFalse($date['enableTime']);
        $this->assertFalse($date['noCalendar']);
        $this->assertTrue($date['allowInput']);
        $this->assertArrayNotHasKey('enableSeconds', $date);

        $datetime = DatePicker::options(['format' => 'YYYY-MM-DD HH:mm:ss'], 'YYYY-MM-DD');
        $this->assertSame('Y-m-d H:i:S', $datetime['dateFormat']);
        $this->assertTrue($datetime['enableTime']);
        $this->assertTrue($datetime['enableSeconds']);
        $this->assertTrue($datetime['time_24hr']);

        $time = DatePicker::options([], 'HH:mm');
        $this->assertTrue($time['noCalendar']);
        $this->assertTrue($time['enableTime']);
        $this->assertFalse($time['enableSeconds']);
        $this->assertSame(0, $time['defaultHour'], 'a time starts at 00:00, as the old field did');

        // the options of the old picker: kept when flatpickr has them, dropped otherwise
        $old = DatePicker::options([
            'format' => 'YYYY-MM-DD', 'minDate' => '2026-01-01', 'useCurrent' => false, 'locale' => 'ja',
            'icons' => Date::PICKER_ICONS, 'widgetParent' => 'body', 'allowInputToggle' => true,
        ]);
        $this->assertSame('2026-01-01', $old['minDate']);
        foreach (['useCurrent', 'icons', 'widgetParent', 'allowInputToggle', 'format', 'locale'] as $key) {
            $this->assertArrayNotHasKey($key, $old, $key);
        }
    }

    public function testTheScriptUsesFlatpickrAndTheLocale(): void
    {
        $script = DatePicker::script('.created_at', ['dateFormat' => 'Y-m-d'], 'ja');
        $this->assertStringContainsString('window.flatpickr(el, c)', $script);
        $this->assertStringContainsString('document.querySelectorAll(".created_at")', $script);
        $this->assertStringContainsString('l = "ja"', $script);
        $this->assertStringContainsString('if (!el._flatpickr)', $script, 'once an input');
        $this->assertStringNotContainsString('datetimepicker', $script);

        $this->assertStringContainsString('l = "zh-cn"', DatePicker::script('.x', [], 'zh_CN'));
    }

    public function testTheFieldsUseFlatpickr(): void
    {
        foreach ([new Date('d'), new Datetime('dt'), new Time('t')] as $field) {
            $script = $this->scriptOf($field);
            $this->assertStringContainsString('window.flatpickr(el, c)', $script, get_class($field));
            $this->assertStringNotContainsString('datetimepicker', $script, get_class($field));
        }
        foreach ([Date::class, DateRange::class] as $class) {
            $css = (new ReflectionProperty($class, 'css'))->getValue();
            $js = (new ReflectionProperty($class, 'js'))->getValue();
            $this->assertSame([], $css, $class);
            $this->assertSame([], $js, $class);
        }
    }

    public function testTheRangeKeepsTheEndAfterTheStart(): void
    {
        $range = new DateRange('start_at', ['end_at', 'period']);
        $script = $this->scriptOf($range);
        $this->assertStringContainsString('end.set("minDate"', $script);
        $this->assertStringContainsString('start.set("maxDate"', $script);
        $this->assertStringNotContainsString('dp.change', $script);
    }

    public function testTheGridFiltersUseFlatpickr(): void
    {
        Admin::$script = [];
        (new Between('created_at'))->datetime();
        $script = implode("\n", Admin::$script);
        $this->assertStringContainsString('"dateFormat":"Y-m-d H:i:S"', $script);
        $this->assertStringContainsString('end.set("minDate"', $script);
        $this->assertStringNotContainsString('datetimepicker', $script);
    }

    public function testTheDateTimePresenterKeepsItsOptions(): void
    {
        $presenter = new DateTimePresenter(['format' => 'YYYY-MM-DD']);
        $options = (new ReflectionProperty($presenter, 'options'))->getValue($presenter);
        $this->assertSame('YYYY-MM-DD', $options['format']);
        $this->assertArrayNotHasKey('icons', $options);
    }
}
