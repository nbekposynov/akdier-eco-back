<?php

namespace App\Models;

use App\Models\Refactoring\WasteRecord;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FinalProcessing extends Model
{
    use HasFactory;
    protected $table = 'final_processing';

    protected $fillable = [
        'waste_record_id',
        'company_id',
        'name_othod',
        'value',
        'type_operation',
        'created_at',
        'updated_at',
    ];

    public function wasteRecord()
    {
        return $this->belongsTo(WasteRecord::class, 'waste_record_id');
    }

    public function company()
    {
        return $this->belongsTo(User::class, 'company_id');
    }

    public function moderator()
    {
        return $this->belongsTo(User::class, 'moderator_id');
    }
}
