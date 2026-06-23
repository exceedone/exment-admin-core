<?php

namespace ExmentAdminCore\Admin\Form\Field;

use Illuminate\Support\Arr;
use ExmentAdminCore\Admin\Form\Field;
use ExmentAdminCore\Admin\Form\Field\Traits\Sortable;
use Illuminate\Contracts\Validation\Factory;
use Illuminate\Contracts\Validation\Validator;

class KeyValue extends Field
{
    use Sortable;

    /**
     * @var array<string, string>
     */
    protected $value = ['' => ''];

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

        $this->formatValue();
    }

    /**
     * {@inheritdoc}
     * @param array<mixed> $input
     * @return bool|Validator|Factory
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

        $rules["{$this->column}.keys.*"] = 'distinct';
        $rules["{$this->column}.values.*"] = $fieldRules;
        $attributes["{$this->column}.keys.*"] = __('Key');
        $attributes["{$this->column}.values.*"] = __('Value');

        return validator($input, $rules, $this->getValidationMessages(), $attributes);
    }

    /**
     * @return void
     */
    protected function setupScript()
    {
        $this->script = <<<SCRIPT

$('.{$this->column}-add').on('click', function () {
    var tpl = $('template.{$this->column}-tpl').html();
    $('tbody.kv-{$this->column}-table').append(tpl);
});

$('tbody').on('click', '.{$this->column}-remove', function () {
    $(this).closest('tr').remove();
});

SCRIPT;
    }

    /**
     * @param array<mixed> $value
     * @return array<mixed>
     */
    public function prepare($value)
    {
        $value = parent::prepare($value);
        if (empty($value)) {
            return [];
        }

        return array_combine($value['keys'], $value['values']);
    }

    /**
     * @return string
     */
    public function render()
    {
        $this->addSortable('.kv-', '-table');
        view()->share('options', $this->options);

        $this->setupScript();

        return parent::render();
    }
}
