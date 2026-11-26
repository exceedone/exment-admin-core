<?php

namespace ExmentAdminCore\Admin\Form\Field;

class Date extends Text
{
    /**
     * The icons of the old picker (eonasdan's bootstrap-datetimepicker, before 2.0). flatpickr
     * draws its own arrows; kept for the code that still passes them.
     *
     * @deprecated 2.0 the pickers are flatpickr (DatePicker)
     * @var array<string, string>
     */
    public const PICKER_ICONS = [
        'time' => 'fa fa-clock-o',
        'date' => 'fa fa-calendar',
        'up' => 'fa fa-chevron-up',
        'down' => 'fa fa-chevron-down',
        'previous' => 'fa fa-chevron-left',
        'next' => 'fa fa-chevron-right',
        'today' => 'fa fa-calendar-check-o',
        'clear' => 'fa fa-trash',
        'close' => 'fa fa-times'
    ];

    /**
     * flatpickr is in the base assets.
     *
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
     * @param string $format
     * @return $this
     */
    public function format($format)
    {
        $this->format = $format;

        return $this;
    }

    /**
     * @param string $value
     * @return mixed|null
     */
    public function prepare($value)
    {
        $value = parent::prepare($value);

        // allows the value to be empty
        if (empty($value)) {
            $value = null;
        }

        return $value;
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
     * @return string
     */
    public function render()
    {
        $this->options['format'] = $this->format;

        // flatpickr (2.0): the format stays a moment one, DatePicker turns it into flatpickr's
        $this->script = DatePicker::script($this->getElementClassSelector(), DatePicker::options($this->options, $this->format, $this->defaults));

        $this->prepend('<i class="fa fa-calendar fa-fw"></i>')
            ->defaultAttribute('style', 'width: 110px !important; flex: 0 0 auto !important;')
            ->defaultAttribute('autocomplete', 'off');

        return parent::render();
    }
}
