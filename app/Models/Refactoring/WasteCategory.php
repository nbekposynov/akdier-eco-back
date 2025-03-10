<?php

namespace App\Models\Refactoring;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WasteCategory extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
    ];

    public function wastes()
    {
        return $this->hasMany(Waste::class, 'category_id');
    }
}
