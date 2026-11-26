<?php

namespace ExmentAdminCore\Admin\Grid\Displayers;

use ExmentAdminCore\Admin\Admin;
use Illuminate\Support\Arr;

class Editable extends AbstractDisplayer
{
    /**
     * @var array<mixed>
     */
    protected $arguments = [];

    /**
     * Type of editable.
     *
     * @var string
     */
    protected $type = '';

    /**
     * Options of editable function.
     *
     * @var array<string, string>
     */
    protected $options = [
        'emptytext'  => '<i class="fa fa-pencil"></i>',
    ];

    /**
     * @var array<mixed>
     */
    protected $attributes = [];

    /**
     * Add options for editable.
     *
     * @param array<mixed> $options
     *
     * @return void
     */
    public function addOptions($options = [])
    {
        $this->options = array_merge($this->options, $options);
    }

    /**
     * Add attributes for editable.
     *
     * @param array<mixed> $attributes
     *
     * @return void
     */
    public function addAttributes($attributes = [])
    {
        $this->attributes = array_merge($this->attributes, $attributes);
    }

    /**
     * Text type editable.
     * @return void
     */
    public function text()
    {
    }

    /**
     * Textarea type editable.
     * @return void
     */
    public function textarea()
    {
    }

    /**
     * number type editable.
     * @return void
     */
    public function number()
    {
    }

    /**
     * Select type editable.
     *
     * @param array<mixed>|\Closure $options
     *
     * @return void
     */
    public function select($options = [])
    {
        $useClosure = false;

        if ($options instanceof \Closure) {
            $useClosure = true;
            $options = $options->call($this, $this->row);
        }

        $source = [];

        foreach ($options as $value => $text) {
            $source[] = compact('value', 'text');
        }

        if ($useClosure) {
            $this->addAttributes(['data-source' => json_encode($source)]);
        } else {
            $this->addOptions(compact('source'));
        }
    }

    /**
     * Date type editable.
     *
     * @return void
     */
    public function date()
    {
        $this->combodate();
    }

    /**
     * Datetime type editable.
     *
     * @return void
     */
    public function datetime()
    {
        $this->combodate('YYYY-MM-DD HH:mm:ss');
    }

    /**
     * Year type editable.
     *
     * @return void
     */
    public function year()
    {
        $this->combodate('YYYY');
    }

    /**
     * Month type editable.
     *
     * @return void
     */
    public function month()
    {
        $this->combodate('MM');
    }

    /**
     * Day type editable.
     *
     * @return void
     */
    public function day()
    {
        $this->combodate('DD');
    }

    /**
     * Time type editable.
     *
     * @return void
     */
    public function time()
    {
        $this->combodate('HH:mm:ss');
    }

    /**
     * Combodate type editable.
     *
     * @param string $format
     *
     * @return void
     */
    public function combodate($format = 'YYYY-MM-DD')
    {
        // 2.0: a text box with a flatpickr picker (the combodate type needed moment)
        $this->type = 'text';
        $this->datePickerFormat = $format;
    }

    /**
     * The moment format of the date picker of the editor, null: no picker.
     *
     * @var string|null
     */
    protected $datePickerFormat = null;

    /**
     * @param array<mixed> $arguments
     * @return void
     */
    protected function buildEditableOptions(array $arguments = [])
    {
        $this->type = Arr::get($arguments, 0, 'text');

        call_user_func_array([$this, $this->type], array_slice($arguments, 1));
    }

    /**
     * @return string
     */
    public function display()
    {
        $this->options['name'] = $column = $this->column->getName();

        $class = 'grid-editable-'.str_replace(['.', '#', '[', ']'], '-', $column);

        $this->buildEditableOptions(func_get_args());

        $options = json_encode($this->options);

        Admin::script("$('.$class').editable($options);");
        if (isset($this->datePickerFormat)) {
            // the input of the editor is made when it opens
            $config = json_encode(\ExmentAdminCore\Admin\Form\Field\DatePicker::options([], $this->datePickerFormat)) ?: '{}';
            Admin::script("$('.$class').on('shown', function (e, editable) { var el = editable && editable.input && editable.input.\$input ? editable.input.\$input[0] : null; if (el && typeof window.flatpickr === 'function' && !el._flatpickr) { window.flatpickr(el, $config); } });");
        }

        $this->value = htmlentities($this->value);

        $attributes = [
            'href'       => '#',
            'class'      => "$class",
            'data-type'  => $this->type,
            'data-pk'    => "{$this->getKey()}",
            'data-url'   => url("{$this->grid->resource()}/{$this->getKey()}"),
            'data-value' => "{$this->value}",
        ];

        if (!empty($this->attributes)) {
            $attributes = array_merge($attributes, $this->attributes);
        }

        $attributes = collect($attributes)->map(function ($attribute, $name) {
            return "$name='$attribute'";
        })->implode(' ');

        $html = $this->type === 'select' ? '' : $this->value;

        return "<a $attributes>{$html}</a>";
    }
}
