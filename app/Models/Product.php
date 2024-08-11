<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    use HasFactory;

    public const TYPE_KERNEL = 'kernel';
    public const TYPE_MAIN   = 'main';

    public function productIngredients()
    {
        return $this->belongsToMany(Ingredient::class, 'product_ingredients')->withPivot('quantity');;
    }

    public function doughIngredients()
    {
        return $this->belongsToMany(Ingredient::class, 'dough_ingredients')->withPivot('quantity');;
    }
}
