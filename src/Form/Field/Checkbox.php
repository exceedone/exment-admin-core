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
    protected static $css = [
        '/vendor/open-admin/AdminLTE/plugins/iCheck/all.css',
    ];

    /**
     * @var array<string>
     */
    protected static $js = [
        '/vendor/open-admin/AdminLTE/plugins/iCheck/icheck.min.js',
    ];

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

        $this->script = "if ($.fn.iCheck) { $('{$this->getElementClassSelector()}').iCheck({checkboxClass:'icheckbox_minimal-blue'}); }\n";

        $this->addVariables([
            'checked'     => $this->checked,
            'inline'      => $this->inline,
            'canCheckAll' => $this->canCheckAll,
        ]);

        if ($this->canCheckAll) {
            $checkAllClass = uniqid('check-all-');

            // iCheck events with iCheck, native change without it: only one is bound, so it never runs
            // twice (iCheck 1.0.1 never triggers a native change event).
            $this->script .= <<<SCRIPT
if ($.fn.iCheck) {
    $('.{$checkAllClass}').iCheck({checkboxClass:'icheckbox_minimal-blue'}).on('ifChanged', function () {
        if (this.checked) {
            $('{$this->getElementClassSelector()}').iCheck('check');
        } else {
            $('{$this->getElementClassSelector()}').iCheck('uncheck');
        }
    });
} else {
    $('.{$checkAllClass}').on('change', function () {
        $('{$this->getElementClassSelector()}').prop('checked', this.checked).trigger('change');
    });
}
SCRIPT;
            $this->addVariables(['checkAllClass' => $checkAllClass]);
        }

        return parent::render();
    }
}
