<?php

namespace ExmentAdminCore\Admin\Form\Field;

use ExmentAdminCore\Admin\Form\Field\Traits\BelongsToRelation;

class BelongsToMany extends MultipleSelect
{
    use BelongsToRelation;

    protected $relation_prefix = 'belongstomany-';
    protected $relation_type = 'many';
    protected $multiple = true;

    /**
     * Get options.
     * *Not set $this->options*
     * @param mixed|null $value
     *
     * @return array<mixed>
     */
    public function getOptions($value = null) : array
    {
        $options = [];

        if ($this->value()) {
            $options = array_combine($this->value(), $this->value());
        }

        return $options;
    }
}
