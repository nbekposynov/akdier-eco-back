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

    public function getFactorAttribute($value)
    {
        // Конвертируем значение в float
        $floatValue = (float) $value;

        // Удаляем десятичную часть, если она равна нулю
        if ($floatValue == (int)$floatValue) {
            return (int)$floatValue;
        }

        // Удаляем лишние нули после запятой
        return rtrim(rtrim(number_format($floatValue, 6, '.', ''), '0'), '.');
    }

    public function waste()
    {
        return $this->belongsTo(Waste::class, 'waste_id');
    }
}
