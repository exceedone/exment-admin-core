<?php

namespace ExmentAdminCore\Admin\Form\Field;

use ExmentAdminCore\Admin\Form\Field;

class DateRange extends Field
{
    /**
     * @var array<string>
     */
    protected static $css = [];

    /**
     * @var array<string>
     */
    protected static $js = [];

    /**
     * @var string
     */
    protected $format = 'YYYY-MM-DD';

    protected $defaults = [
        'weekNumbers'   => true,
        'time_24hr'     => true,
        'enableSeconds' => true,
        'enableTime'    => false,
        'allowInput'    => true,
        'noCalendar'    => false,
    ];

    /**
     * Column name.
     *
     * @var array<mixed>
     */
    protected $column = [];

    /**
     * @param mixed $column
     * @param array<int, mixed> $arguments
     */
    public function __construct($column, $arguments)
    {
        $this->column['start'] = $column;
        $this->column['end'] = $arguments[0];

        array_shift($arguments);
        $this->label = $this->formatLabel($arguments);
        $this->id = $this->formatId($this->column);

        $this->options(['format' => $this->format]);
    }

    /**
     * {@inheritdoc}
     * @param mixed $value
     * @return $this
     */
    public function value($value = null)
    {
        if (is_null($value)) {
            if (!isset($this->value['start']) && !isset($this->value['end'])) {
                return $this->getDefault();
            }

            return $this->value;
        }

        $this->value = $value;

        return $this;
    }

    public function check_format_options()
    {
        $format = $this->options['format'];
        if (substr($format, -2) != 'ss') {
            $this->options['enableSeconds'] = false;
        }
        if (strpos($format, 'H') !== false) {
            $this->options['enableTime'] = true;
        }
    }

    /**
     * {@inheritdoc}
     * @param mixed $value
     * @return mixed
     */
    public function prepare($value)
    {
        $value = parent::prepare($value);
        if ($value === '') {
            $value = null;
        }

        return $value;
    }

    /**
     * {@inheritdoc}
     * @return string
     */
    public function render()
    {
        // flatpickr (2.0): the end cannot be before the start, and the start not after the end
        $class = $this->getElementClassSelector();
        $config = DatePicker::options($this->options, $this->format, $this->defaults);
        $this->script = DatePicker::rangeScript($class['start'], $class['end'], $config);

        return parent::render();
    }
}
