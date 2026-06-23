<?php

namespace ExmentAdminCore\Admin;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Spatie\EloquentSortable\SortableTrait;

/**
 * Eloquent Model used SoftDeletes trait.
 * For phpstan reference
 * @deprecated
 * @phpstan-ignore-next-line Class ExmentAdminCore\Admin\SoftDeletableModel extends generic class Illuminate\Database\Eloquent\Builder but does not specify its types: TModelClass
 */
class SortableModel extends Builder
{
    use SortableTrait;
}
