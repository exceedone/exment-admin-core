<?php

namespace OpenAdminCore\Admin\Form\Field;

use OpenAdminCore\Admin\Form\Field\Traits\HasNumberModifiers;
use OpenAdminCore\Admin\Validator\DigitBetweenRule;
use OpenAdminCore\Admin\Validator\DigitMinRule;
use OpenAdminCore\Admin\Validator\DigitMaxRule;

class Number extends Text
{
    use HasNumberModifiers;

    protected $rules = ['nullable', 'numeric'];
    protected static $js = [
        '/vendor/open-admin/number-input/bootstrap-number-input.js',
    ];

    public function render()
    {
        $value = $this->getOld();
        $isNumericValue = !isset($value) || $value === '' || is_numeric($value);

        if ($isNumericValue) {
            // Only apply as default; callers may override with another type.
            $this->defaultAttribute('type', 'number');
        } else {
            // Force type="text" so the browser doesn't discard the non-numeric value.
            // Using attribute() (not defaultAttribute()) so this always wins.
            $this->attribute('type', 'text');
            $this->attribute('data-allow-nonnumeric', '1');
        }
        // $this->append("<i class='fa fa-plus plus'></i>");
        // $this->prepend("<i class='fa fa-minus minus'></i>");
        $this->default($this->default);

        $this->script = <<<EOT

$('{$this->getElementClassSelector()}:not(.initialized)')
    .addClass('initialized')
    .bootstrapNumber({
        upClass: 'success',
        downClass: 'primary',
        center: true
    });

EOT;

        $this->prepend('')->defaultAttribute('style', 'width: 100px');

        return parent::render();
    }

    /**
     * Set min value of number field.
     *
     * @param int $value
     *
     * @return $this
     */
    public function min($value)
    {
        $this->attribute('min', $value);

        $this->rules([new DigitMinRule($value)]);

        return $this;
    }

    /**
     * Set max value of number field.
     *
     * @param int $value
     *
     * @return $this
     */
    public function max($value)
    {
        $this->attribute('max', $value);

        $this->rules([new DigitMaxRule($value)]);

        return $this;
    }



    /**
     * Set min and max value of number field. 
     * *And set validation.*
     *
     * @param int $min
     * @param int $max
     *
     * @return $this
     */
    public function between($min, $max)
    {
        $this->attribute('min', $min);
        $this->attribute('max', $max);

        $this->rules([new DigitBetweenRule($min, $max)]);

        return $this;
    }


}
