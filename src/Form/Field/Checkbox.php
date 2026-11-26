<?php

namespace ExmentAdminCore\Admin\Form\Field;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Support\Arr;

class Checkbox extends MultipleSelect
{
    /**
     * @var bool
     */
    protected $inline = true;

    /**
     * @var bool
     */
    protected $canCheckAll = false;

    /**
     * @var array<string>
     */
    protected static $css = [];

    /**
     * 2.0: native check boxes, no iCheck.
     *
     * @var array<string>
     */
    protected static $js = [];

    /**
     * Set options.
     *
     * @param array<mixed>|callable|string $options
     *
     * @return $this|mixed
     */
    public function options($options = [])
    {
        if ($options instanceof Arrayable) {
            $options = $options->toArray();
        }

        if (is_callable($options)) {
            $this->options = $options;
        } else {
            $this->options = (array) $options;
        }

        return $this;
    }

    /**
     * Add a checkbox above this component, so you can select all checkboxes by click on it.
     *
     * @return $this
     */
    public function canCheckAll()
    {
        $this->canCheckAll = true;

        return $this;
    }

    /**
     * Set checked.
     *
     * @param array<mixed>|callable|string $checked
     *
     * @return $this
     */
    public function checked($checked = [])
    {
        if ($checked instanceof Arrayable) {
            $checked = $checked->toArray();
        }

        $this->checked = (array) $checked;

        return $this;
    }

    /**
     * Draw inline checkboxes.
     *
     * @return $this
     */
    public function inline()
    {
        $this->inline = true;

        return $this;
    }

    /**
     * Draw stacked checkboxes.
     *
     * @return $this
     */
    public function stacked()
    {
        $this->inline = false;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function render()
    {
        // remove required rule
        Arr::forget($this->attributes, 'required');
        $this->removeRules(['required']);

        // 2.0: native check boxes (no iCheck)
        $this->script = '';

        $this->addVariables([
            'checked'     => $this->checked,
            'inline'      => $this->inline,
            'canCheckAll' => $this->canCheckAll,
        ]);

        if ($this->canCheckAll) {
            $checkAllClass = uniqid('check-all-');

            $this->script .= <<<SCRIPT
$('.{$checkAllClass}').on('change', function () {
    $('{$this->getElementClassSelector()}').prop('checked', this.checked).trigger('change');
});
SCRIPT;
            $this->addVariables(['checkAllClass' => $checkAllClass]);
        }

        return parent::render();
    }
}
