<?php

namespace App\Models\Refactoring;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FinalWasteType extends Model
{
    use HasFactory;

    protected $table = 'final_waste_types';

    protected $fillable = [
        'final_name', // Финальное имя отхода
        'type_operation', // Тип операции
        'factor'
    ];

    public function waste()
    {
        return $this->belongsTo(Waste::class, 'waste_id');
    }
}
