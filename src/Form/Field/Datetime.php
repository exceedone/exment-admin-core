<?php

namespace ExmentAdminCore\Admin\Form\Field;

class Datetime extends Date
{
    /**
     * @var string
     */
    protected $format = 'YYYY-MM-DD HH:mm:ss';

    /**
     * @return string
     */
    public function render()
    {
        $this->defaultAttribute('style', 'width: 160px !important; flex: 0 0 auto !important;');

        return parent::render();
    }
}
