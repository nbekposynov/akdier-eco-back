<?php

namespace App\Models\Refactoring;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Waste extends Model
{
    use HasFactory;

    protected $fillable = [
        'category_id',
        'name',
        'slug',
        'final_waste_type_id',
    ];

    public function category()
    {
        return $this->belongsTo(WasteCategory::class, 'category_id');
    }

    public function items()
    {
        return $this->hasMany(WasteRecordItem::class, 'waste_id');
    }

    public function finalWasteType()
    {
        return $this->belongsTo(FinalWasteType::class, 'final_waste_type_id');
    }
}
