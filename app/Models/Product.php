<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    use HasFactory;
    protected $table = 'products';

    protected $fillable = [
        'url',
        'country',
        'price',
        'name',
        'default_image',
        'rotate_image',
    ];


    /**
     * @return string[]
     */
    public function getFillable(): array
    {
        return $this->fillable;
    }
}
