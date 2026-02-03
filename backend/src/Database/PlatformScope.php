<?php
/**
 * Created by PhpStorm.
 * User: Notandi
 * Date: 10/05/2020
 * Time: 16:40
 */

namespace Karolina\Database;

use Illuminate\Database\Eloquent\Scope;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;

class PlatformScope implements Scope
{

    public function apply(Builder $builder, Model $model)
    {
        $builder->where('platform', '=', getenv('platform_key'))->orWhere('platform', 'global');

    }

}