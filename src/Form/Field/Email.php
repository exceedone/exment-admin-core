<?php

namespace ExmentAdminCore\Admin\Form\Field;

use ExmentAdminCore\Admin\Form;

class Email extends Text
{
    protected $rules = 'nullable|email';

    public function setForm($form = null)
    {
        $this->form = $form;
        // field type url has a default browser validation
        $this->form->enableValidate();

        return $this;
    }

    public function render()
    {
        $this->prepend('<i class="fa fa-envelope fa-fw"></i>')
            ->defaultAttribute('type', 'email');

        return parent::render();
    }
}
