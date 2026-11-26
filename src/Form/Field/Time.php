<?php

namespace ExmentAdminCore\Admin\Form\Field;

class Time extends Date
{
    /**
     * @var string
     */
    protected $format = 'HH:mm:ss';

    /**
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\View\Factory|\Illuminate\Contracts\View\View|\Illuminate\Foundation\Application|string
     */
    public function render()
    {
        // flatpickr does not fill the time in by itself, and a time alone starts at 00:00:00
        // (DatePicker::options), as the old picker did with useCurrent false
        $this->prepend('<i class="fa fa-clock-o fa-fw"></i>')
            ->defaultAttribute('style', 'width:150px !important; flex:0 0 150px;');

        return parent::render();
    }
}
