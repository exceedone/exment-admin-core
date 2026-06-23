<?php

namespace ExmentAdminCore\Admin\Form\Field;


use ExmentAdminCore\Admin\Form\Field;
use ExmentAdminCore\Admin\Form\Field\Traits\HasValuePicker;
use ExmentAdminCore\Admin\Form\Field\Traits\PlainInput;

class Text extends Field
{
    use PlainInput;
    use HasValuePicker;

    /**
     * @var string|null
     */
    protected $icon = 'fa-pencil';
    /**
     * @var bool
     */
    protected $withoutIcon = false;

    /**
     * Set custom fa-icon.
     *
     * @param string $icon
     *
     * @return $this
     */
    public function icon($icon)
    {
        $this->icon = $icon;

        return $this;
    }

    /**
     * Render this filed.
     *
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function render()
{
    $this->initPlainInput();

    if (!isset($this->withoutIcon) || !$this->withoutIcon) {
        if (isset($this->icon)) {
            $this->prepend('<i class="fa '.$this->icon.' fa-fw"></i>');
        }
    }

    $this->defaultAttribute('type', 'text')
        ->defaultAttribute('id', $this->id)
        ->defaultAttribute('name', $this->elementName ?: $this->formatName($this->column))
        ->defaultAttribute('value', $this->getOld()) // xử lý value thống nhất
        ->defaultAttribute('class', 'form-control '.$this->getElementClassString())
        ->defaultAttribute('placeholder', $this->getPlaceholder());

        if (method_exists($this, 'mountPicker')) {
            $this->mountPicker();
        }
    $this->addVariables([
        'prepend' => $this->prepend,
        'append'  => $this->append,
    ]);

    return parent::render();
}

    /**
     * Add inputmask to an elements.
     *
     * @param array<mixed> $options
     *
     * @return $this
     */
    public function inputmask($options)
    {
        $options = json_encode_options($options);

        //$this->script = "$('{$this->getElementClassSelector()}').inputmask($options);";
        $this->script = "Inputmask({$options}).mask(document.querySelector(\"{$this->getElementClassSelector()}\"));";

        return $this;
    }

    /**
     * Add datalist element to Text input.
     *
     * @param array $entries
     *
     * @return $this
     */
    public function datalist($entries = [])
    {
        $this->defaultAttribute('list', "list-{$this->id}");

        $datalist = "<datalist id=\"list-{$this->id}\">";
        foreach ($entries as $k => $v) {
            $datalist .= "<option value=\"{$k}\">{$v}</option>";
        }
        $datalist .= '</datalist>';

        return $this->append($datalist);
    }

    /**
     * show no icon in font of input.
     *
     * @return $this
     */
    public function withoutIcon()
    {
        $this->withoutIcon = true;

        return $this;
    }
}
