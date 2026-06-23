<?php

namespace ExmentAdminCore\Admin\Form\Field;

use Illuminate\Contracts\Support\Arrayable;
use ExmentAdminCore\Admin\Form\Field;
use ExmentAdminCore\Admin\Form\Field\Traits\CanCascadeFields;
use ExmentAdminCore\Admin\Validator\HasOptionRule;

class Radio extends Field
{
    use CanCascadeFields;

    protected $stacked = false;
    /**
     * @var bool
     */
    protected $inline = true;

    /**
     * @var array<string>
     */
    protected static $css = [
        '/vendor/open-admin/AdminLTE/plugins/iCheck/all.css',
    ];

    /**
     * @var array<string>
     */
    protected $cascadeEvent = 'change';
    protected static $js = [
        '/vendor/open-admin/AdminLTE/plugins/iCheck/icheck.min.js',
    ];

    /**
     * Field constructor.
     *
     * @param string $column
     * @param array<mixed> $arguments
     */
    public function __construct($column = '', $arguments = [])
    {
        parent::__construct($column, $arguments);

        $this->rules([new HasOptionRule($this)]);
    }
    
    /**
     * Set options.
     *
     * @param array<mixed>|callable|string $options
     *
     * @return $this
     */
    public function options($options = [])
    {
        if ($options instanceof Arrayable) {
            $options = $options->toArray();
        }

        $this->options = (array) $options;

        return $this;
    }

    /**
     * @return array|mixed
     */
    public function getOptions(){
        return $this->options;
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

        // input radio checked should be unique
        $this->checked = is_array($checked) ? (array) end($checked) : (array) $checked;

        return $this;
    }

    /**
     * Draw inline radios.
     *
     * @return $this
     */
    public function inline()
    {
        $this->stacked = false;

        return $this;
    }

    /**
     * Draw stacked radios.
     *
     * @return $this
     */
    public function stacked()
    {
        $this->stacked = true;

        return $this;
    }

    /**
     * Set options.
     *
     * @param array<mixed>|callable|string $values
     *
     * @return $this
     */
    public function values($values)
    {
        return $this->options($values);
    }

    /**
     * {@inheritdoc}
     */
    public function render()
    {
        $this->script = "$('{$this->getElementClassSelector()}').iCheck({radioClass:'iradio_minimal-blue'});";

        $this->addCascadeScript();

        $this->addVariables(['options' => $this->options, 'checked' => $this->checked, 'stacked' => $this->stacked]);

        return parent::render();
    }
}
