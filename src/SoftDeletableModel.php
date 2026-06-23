<?php

namespace ExmentAdminCore\Admin;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Eloquent Model used SoftDeletes trait.
 * For phpstan reference
 * @deprecated
 * @phpstan-ignore-next-line Class ExmentAdminCore\Admin\SoftDeletableModel extends generic class Illuminate\Database\Eloquent\Builder but does not specify its types: TModelClass
 */
class SoftDeletableModel extends Builder
{
    use SoftDeletes;
}
