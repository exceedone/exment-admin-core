<?php

namespace ExmentAdminCore\Admin\Form\Field;

use Illuminate\Support\Arr;
use ExmentAdminCore\Admin\Admin;
use ExmentAdminCore\Admin\Form\Field;
use ExmentAdminCore\Admin\Form\Field\Traits\Sortable;

class ListField extends Field
{
    /**
     * Max list size.
     *
     * @var int|null
     */
    protected $max;

    /**
     * Minimum list size.
     *
     * @var int|null
     */
    protected $min = 0;

    /**
     * @var array<string>
     */
    protected $value = [''];

    /**
     * Set Max list size.
     *
     * @param int $size
     *
     * @return $this
     */
    public function max(int $size)
    {
        $this->max = $size;

        return $this;
    }

    /**
     * Set Minimum list size.
     *
     * @param int $size
     *
     * @return $this
     */
    public function min(int $size)
    {
        $this->min = $size;

        return $this;
    }

    /**
     * Fill data to the field.
     *
     * @param array<mixed> $data
     *
     * @return void
     */
    public function fill($data)
    {
        $this->data = $data;

        $this->value = Arr::get($data, $this->column, $this->value);
        if (!is_array($this->value)) {
            $this->value = json_decode($this->value);
        }
        if (empty($this->value)) {
            $this->value = [''];
        }

        $this->formatValue();
    }

    /**
     * {@inheritdoc}
     * @param array<mixed> $input
     * @return bool|\Illuminate\Contracts\Validation\Validator
     */
    public function getValidator(array $input)
    {
        if ($this->validator) {
            return $this->validator->call($this, $input);
        }

        if (!is_string($this->column)) {
            return false;
        }

        $rules = $attributes = [];

        if (!$fieldRules = $this->getRules()) {
            return false;
        }

        if (!Arr::has($input, $this->column)) {
            return false;
        }

        $rules["{$this->column}.*"] = $fieldRules;
        $attributes["{$this->column}.*"] = __('Value');

        $rules["{$this->column}"][] = 'array';

        $attributes["{$this->column}"] = $this->label;

        return validator($input, $rules, $this->getValidationMessages(), $attributes);
    }

    /**
     * {@inheritdoc}
     * @return  void
     */
    protected function setupScript()
    {
        $this->script = <<<SCRIPT

$('.{$this->column}-add').on('click', function () {
    var tpl = $('template.{$this->column}-tpl').html();
    $('tbody.list-{$this->column}-table').append(tpl);
});

$('tbody').on('click', '.{$this->column}-remove', function () {
    $(this).closest('tr').remove();
});

SCRIPT;
    }

    /**
     * {@inheritdoc}
     * @param mixed $value
     * @return array<mixed>
     */
    public function prepare($value)
    {
        $value = (array) parent::prepare($value);

        $values = array_values($value);
        if (count($values) == 1 && empty($values[0])) {
            return [];
        }

        return $values;
    }

    /**
     * {@inheritdoc}
     * @return string
     */
    public function render()
    {
        $this->addSortable('tbody.list-', '-table');
        view()->share('options', $this->options);

        $this->setupScript();

        Admin::style('td .form-group {margin-bottom: 0 !important;}');

        return parent::render();
    }
}
