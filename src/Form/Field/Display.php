<?php

namespace ExmentAdminCore\Admin\Form\Field;

use ExmentAdminCore\Admin\Form\Field;

class Display extends Field
{
    /**
     * Display text
     *
     * @var mixed
     */
    protected $displayText;

    /**
     * Display class
     *
     * @var mixed
     */
    protected $displayClass;

    /**
     * escape value
     *
     * @var bool
     */
    protected $escape = true;

    /**
     * @param mixed $displayText
     * @return $this
     */
    public function displayText($displayText)
    {
        $this->displayText = $displayText;

        return $this;
    }

    /**
     * @param mixed $displayClass
     * @return $this
     */
    public function displayClass($displayClass)
    {
        $this->displayClass = $displayClass;

        return $this;
    }

    /**
     * @param bool $escape
     * @return $this
     */
    public function escape(bool $escape = true)
    {
        $this->escape = $escape;

        return $this;
    }

    /**
     * Render this filed.
     *
     * @return string
     */
    public function render()
    {
        if ($this->displayText instanceof \Closure) {
            $this->displayText = $this->displayText->call($this, $this->value);
        }

        return parent::fieldRender([
            'displayText' => $this->displayText,
            'displayClass' => $this->displayClass,
            'escape' => $this->escape,
        ]);
    }

    public function prepare($value)
    {
        return $this->original() ?: $value;
    }
}
