<?php

namespace ExmentAdminCore\Admin\Grid\Filter;

use ExmentAdminCore\Admin\Admin;
use ExmentAdminCore\Admin\Form\Field\DatePicker;
use Illuminate\Support\Arr;

class Between extends AbstractFilter
{
    /**
     * {@inheritdoc}
     * @var string
     */
    protected $view = 'admin::filter.between';

    /**
     * Format id.
     *
     * @param string $column
     *
     * @return array<mixed>|string
     */
    public function formatId($column)
    {
        $id = str_replace('.', '_', $column);

        return ['start' => "{$id}_start", 'end' => "{$id}_end"];
    }

    /**
     * Format two field names of this filter.
     * @phpstan-ignore-next-line Return type (array<string, string>) of method ExmentAdminCore\Admin\Grid\Filter\Between::formatName() should be compatible with return type (string|null) of method
     * ExmentAdminCore\Admin\Grid\Filter\AbstractFilter::formatName()
     * @param string $column
     *
     * @return array<string, string>
     */
    protected function formatName($column)
    {
        $columns = explode('.', $column);

        if (count($columns) == 1) {
            $name = $columns[0];
        } else {
            $name = array_shift($columns);

            foreach ($columns as $column) {
                $name .= "[$column]";
            }
        }

        return ['start' => "{$name}[start]", 'end' => "{$name}[end]"];
    }

    /**
     * Get condition of this filter.
     *
     * @param array<mixed> $inputs
     *
     * @return mixed
     */
    public function condition($inputs)
    {
        if ($this->ignore) {
            return;
        }

        if (!Arr::has($inputs, $this->column)) {
            return;
        }

        $this->value = Arr::get($inputs, $this->column);

        $value = array_filter($this->value, function ($val) {
            return $val !== '';
        });

        if (empty($value)) {
            return;
        }

        if (!isset($value['start'])) {
            return $this->buildCondition($this->column, '<=', $value['end']);
        }

        if (!isset($value['end'])) {
            return $this->buildCondition($this->column, '>=', $value['start']);
        }

        $this->query = 'whereBetween';

        return $this->buildCondition($this->column, $this->value);
    }

    /**
     * @param array<string, mixed> $options
     *
     * @return $this
     */
    public function datetime($options = [])
    {
        $this->view = 'admin::filter.betweenDatetime';

        $this->setupDatetime($options);

        return $this;
    }

    /**
     * @param array<string, mixed> $options
     *
     * @return void
     */
    protected function setupDatetime($options = [])
    {
        $options['format'] = Arr::get($options, 'format', 'YYYY-MM-DD HH:mm:ss');
        $options['locale'] = Arr::get($options, 'locale', config('app.locale'));

        // flatpickr (2.0): the end cannot be before the start, and the start not after the end
        $config = DatePicker::options($options, $options['format']);
        $script = DatePicker::rangeScript('#' . $this->id['start'], '#' . $this->id['end'], $config, $options['locale']);

        Admin::script($script);
    }
}
