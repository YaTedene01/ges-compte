<?php

namespace App\Scopes;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;

class NonDeletedScope implements Scope
{
     /**
      * Apply the scope to a given Eloquent query builder.
      */
     public function apply(Builder $builder, Model $model): void
     {
         $builder->whereNull($model->getQualifiedDeletedAtColumn())
                 ->whereIn('type', ['cheque', 'epargne'])
                 ->where('statut', 'actif');
     }
}