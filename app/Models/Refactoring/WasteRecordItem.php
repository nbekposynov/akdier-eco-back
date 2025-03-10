<?php

namespace App\Models\Refactoring;

use App\Models\Refactoring\WasteRecord;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WasteRecordItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'waste_record_id',
        'waste_id',
        'amount',
    ];

    public function waste()
    {
        return $this->belongsTo(Waste::class, 'waste_id');
    }

    public function wasteRecord()
    {
        return $this->belongsTo(WasteRecord::class, 'waste_record_id');
    }
}
