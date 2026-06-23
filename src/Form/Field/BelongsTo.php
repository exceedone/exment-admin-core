<?php

namespace ExmentAdminCore\Admin\Form\Field;

use ExmentAdminCore\Admin\Form\Field\Traits\BelongsToRelation;

class BelongsTo extends Select
{
    use BelongsToRelation;

    protected $relation_prefix = 'belongsto-';
    protected $relation_type = 'one';

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

        if ($value = $this->value()) {
            $options = [$value => $value];
        }

        return $options;
    }
}
