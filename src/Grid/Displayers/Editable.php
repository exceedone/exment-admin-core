<?php

namespace ExmentAdminCore\Admin\Grid\Displayers;

use Illuminate\Support\Arr;

/**
 * ->editable(), kept for the code that still calls it.
 *
 * Until 2.1 it was x-editable (bootstrap5-editable). Since 2.1 it is drawn by the inline edit
 * of the grid, the same as ->text(), ->textarea(), ->select() and ->datetime(): the editor
 * sends `_edit_inline`, which Form::update() takes like `_editable`. The types of x-editable
 * map to the inline editors below; one without an editor of its own (year, month, day) is a
 * text box.
 */
class Editable extends AbstractDisplayer
{
    /**
     * The flatpickr options of the date types (as Column::datetime(), date() and time()).
     */
    protected const DATE_OPTIONS = [
        'date'     => ['format' => 'YYYY-MM-DD'],
        'datetime' => ['format' => 'YYYY-MM-DD HH:mm:ss', 'enableTime' => true, 'enableSeconds' => true],
        'time'     => ['format' => 'HH:mm:ss', 'enableTime' => true, 'enableSeconds' => true, 'noCalendar' => true],
    ];

    /**
     * @return string
     */
    public function display()
    {
        $arguments = func_get_args();
        $type = strval(Arr::get($arguments, 0, 'text'));
        $rest = array_slice($arguments, 1);

        if ($type === 'combodate') {
            $format = strval(Arr::get($rest, 0, 'YYYY-MM-DD'));
            $type = $format === 'YYYY-MM-DD HH:mm:ss' ? 'datetime' : ($format === 'HH:mm:ss' ? 'time' : ($format === 'YYYY-MM-DD' ? 'date' : 'text'));
        }

        switch ($type) {
            case 'textarea':
                return $this->inline(Textarea::class, []);
            case 'select':
                return $this->inline(Select::class, [Arr::get($rest, 0, [])]);
            case 'date':
            case 'datetime':
            case 'time':
                return $this->inline(Datetime::class, [['inline' => true, 'time_24hr' => true] + static::DATE_OPTIONS[$type]]);
        }

        return $this->inline(Input::class, ['']);
    }

    /**
     * @param class-string<AbstractDisplayer> $class
     * @param array<mixed> $arguments
     * @return string
     */
    protected function inline(string $class, array $arguments)
    {
        $displayer = new $class($this->value, $this->grid, $this->column, $this->row);

        return strval(call_user_func_array([$displayer, 'display'], $arguments));
    }
}
