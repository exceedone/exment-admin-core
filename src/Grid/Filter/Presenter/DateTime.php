<?php

namespace ExmentAdminCore\Admin\Grid\Filter\Presenter;

use ExmentAdminCore\Admin\Admin;
use ExmentAdminCore\Admin\Form\Field\DatePicker;
use Illuminate\Support\Arr;

class DateTime extends Presenter
{
    /**
     * @var array<string, mixed>
     */
    protected $options = [];

    /**
     * @var string
     */
    protected $format = 'YYYY-MM-DD HH:mm:ss';

    /**
     * DateTime constructor.
     *
     * @param array<string, mixed> $options
     */
    public function __construct($options = [])
    {
        $this->options = $this->getOptions($options);
    }

    /**
     * @param array<string, mixed> $options
     *
     * @return array<string, mixed>
     */
    protected function getOptions(array  $options) : array
    {
        $options['format'] = Arr::get($options, 'format', $this->format);
        $options['locale'] = Arr::get($options, 'locale', config('app.locale'));

        return $options;
    }


    /**
     * @return void
     */
    protected function prepare()
    {
        // flatpickr (2.0)
        $config = DatePicker::options($this->options, $this->format);
        $script = DatePicker::script('#' . $this->filter->getId(), $config, strval($this->options['locale']));

        Admin::script($script);
    }

    /**
     * @return array<string, mixed>
     */
    public function variables() : array
    {
        $this->prepare();

        return [
            'group' => $this->filter->group,
        ];
    }
}
